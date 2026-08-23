<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreItemRequest;
use App\Http\Requests\Admin\UpdateItemRequest;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    public function index(): JsonResponse
    {
        $items = Item::query()
            ->with(['category', 'variants'])
            ->orderBy('sort_order')
            ->get();

        return response()->json(['data' => $items]);
    }

    public function store(StoreItemRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('items', 'public');
        }

        $item = Item::create($validated);

        return response()->json([
            'message' => 'تم إنشاء الصنف بنجاح',
            'data' => $item,
        ], 201);
    }

    public function show(Item $item): JsonResponse
    {
        $item->load(['category', 'variants']);

        return response()->json(['data' => $item]);
    }

    public function update(UpdateItemRequest $request, Item $item): JsonResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            if ($item->image) {
                Storage::disk('public')->delete($item->image);
            }

            $validated['image'] = $request->file('image')->store('items', 'public');
        }

        $item->update($validated);

        return response()->json([
            'message' => 'تم تحديث الصنف بنجاح',
            'data' => $item,
        ]);
    }

    public function destroy(Item $item): JsonResponse
    {
        $usedInOrders = $item->variants()
            ->whereHas('orderItems')
            ->exists();

        if ($usedInOrders) {
            return response()->json([
                'message' => 'لا يمكن حذف هذا الصنف لأنه مرتبط بطلبات سابقة. يرجى استخدام خيار التعطيل بدلاً من الحذف.',
            ], 422);
        }

        if ($item->image) {
            Storage::disk('public')->delete($item->image);
        }

        $item->delete();

        return response()->json([
            'message' => 'تم حذف الصنف بنجاح',
        ]);
    }
}
