<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreItemVariantRequest;
use App\Http\Requests\Admin\UpdateItemVariantRequest;
use App\Models\ItemVariant;
use Illuminate\Http\JsonResponse;

class ItemVariantController extends Controller
{
    public function index(): JsonResponse
    {
        $variants = ItemVariant::query()
            ->with('item')
            ->orderBy('sort_order')
            ->get();

        return response()->json(['data' => $variants]);
    }

    public function store(StoreItemVariantRequest $request): JsonResponse
    {
        $variant = ItemVariant::create($request->validated());

        return response()->json([
            'message' => 'تم إنشاء الخيار بنجاح',
            'data' => $variant,
        ], 201);
    }

    public function show(ItemVariant $itemVariant): JsonResponse
    {
        $itemVariant->load('item');

        return response()->json(['data' => $itemVariant]);
    }

    public function update(UpdateItemVariantRequest $request, ItemVariant $itemVariant): JsonResponse
    {
        $itemVariant->update($request->validated());

        return response()->json([
            'message' => 'تم تحديث الخيار بنجاح',
            'data' => $itemVariant,
        ]);
    }

    public function destroy(ItemVariant $itemVariant): JsonResponse
    {
        $usedInOrders = $itemVariant->orderItems()->exists();

        if ($usedInOrders) {
            return response()->json([
                'message' => 'لا يمكن حذف هذا الخيار لأنه مرتبط بطلبات سابقة. يرجى استخدام خيار التعطيل بدلاً من الحذف.',
            ], 422);
        }

        $itemVariant->delete();

        return response()->json([
            'message' => 'تم حذف الخيار بنجاح',
        ]);
    }
}
