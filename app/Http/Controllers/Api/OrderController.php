<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Models\ItemVariant;
use App\Models\Order;
use App\Models\Table;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Enums\UserRole;
use App\Events\OrderStatusUpdated;
use App\Events\NewOrderCreated;

class OrderController extends Controller
{

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $ordersQuery = Order::query()
            ->with(['items', 'table'])
            ->orderByDesc('created_at');

        if ($user->role === UserRole::Kitchen) {
            $ordersQuery->whereIn('status', [
                OrderStatus::Pending,
                OrderStatus::Accepted,
                OrderStatus::Preparing,
            ]);
        }

        $ordersQuery->when($request->filled('status'), function ($query) use ($request) {
            $query->where('status', $request->string('status'));
        });

        $orders = $ordersQuery->get();

        return response()->json([
            'data' => $orders,
        ]);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $table = Table::query()
            ->where('qr_token', $validated['qr_token'])
            ->where('is_active', true)
            ->first();

        if (! $table) {
            return response()->json([
                'message' => 'الطاولة غير موجودة أو غير مفعّلة',
            ], 404);
        }

        $variantIds = collect($validated['items'])->pluck('item_variant_id');

        $variants = ItemVariant::query()
            ->whereIn('id', $variantIds)
            ->where('is_available', true)
            ->get()
            ->keyBy('id');

        // نتأكد إنو كل الأصناف المطلوبة لسا متوفرة فعلاً (مش بس موجودة، بل available)
        if ($variants->count() !== $variantIds->unique()->count()) {
            return response()->json([
                'message' => 'أحد الأصناف المطلوبة لم يعد متوفراً حالياً',
            ], 422);
        }

        $order = DB::transaction(function () use ($validated, $table, $variants) {
            $order = Order::create([
                'table_id' => $table->id,
                'status' => OrderStatus::Pending,
                'notes' => $validated['notes'] ?? null,
                'total_price' => 0,
            ]);

            $totalPrice = 0;

            foreach ($validated['items'] as $requestedItem) {
                $variant = $variants->get($requestedItem['item_variant_id']);

                $lineTotal = $variant->price * $requestedItem['quantity'];
                $totalPrice += $lineTotal;

                $order->items()->create([
                    'item_variant_id' => $variant->id,
                    'item_name' => $variant->item->name,
                    'variant_name' => $variant->name,
                    'quantity' => $requestedItem['quantity'],
                    'unit_price' => $variant->price,
                    'notes' => $requestedItem['notes'] ?? null,
                ]);
            }

            $order->update(['total_price' => $totalPrice]);

            return $order;
        });

        $order->load(['items', 'table']);

        broadcast(new NewOrderCreated($order));

        return response()->json([
            'message' => 'تم استلام الطلب بنجاح',
            'data' => $order,
        ], 201);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        $user = $request->user();
        $newStatus = OrderStatus::from($request->validated('status'));

        if (! $order->status->canTransitionTo($newStatus)) {
            return response()->json([
                'message' => "لا يمكن تغيير حالة الطلب من \"{$order->status->label()}\" إلى \"{$newStatus->label()}\"",
            ], 422);
        }

        $allowedRoles = $order->status->allowedRolesForTransitionTo($newStatus);

        if (! in_array($user->role, $allowedRoles)) {
            return response()->json([
                'message' => 'ليس لديك صلاحية لتنفيذ هذا الإجراء',
            ], 403);
        }

        $order->update(['status' => $newStatus]);
        $order->load(['items', 'table']);

        broadcast(new OrderStatusUpdated($order));

        return response()->json([
            'message' => 'تم تحديث حالة الطلب بنجاح',
            'data' => $order,
        ]);
    }
}
