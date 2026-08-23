<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::query()
            ->orderBy('sort_order')
            ->get();

        return response()->json(['data' => $categories]);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = Category::create($request->validated());

        return response()->json([
            'message' => 'تم إنشاء التصنيف بنجاح',
            'data' => $category,
        ], 201);
    }

    public function show(Category $category): JsonResponse
    {
        return response()->json(['data' => $category]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $category->update($request->validated());

        return response()->json([
            'message' => 'تم تحديث التصنيف بنجاح',
            'data' => $category,
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        $itemsCount = $category->items()->count();

        if ($itemsCount > 0) {
            return response()->json([
                'message' => "لا يمكن حذف هذا التصنيف لأنه يحتوي على {$itemsCount} صنف. يرجى حذف أو نقل هذه الأصناف أولاً، أو استخدام خيار التعطيل بدلاً من الحذف.",
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'تم حذف التصنيف بنجاح',
        ]);
    }
}
