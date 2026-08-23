<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class MenuController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::query()
            ->where('is_available', true)
            ->orderBy('sort_order')
            ->with(['items' => function ($query) {
                $query->where('is_available', true)
                    ->orderBy('sort_order')
                    ->with(['variants' => function ($variantQuery) {
                        $variantQuery->where('is_available', true)
                            ->orderBy('sort_order');
                    }]);
            }])
            ->get();

        return response()->json([
            'data' => $categories,
        ]);
    }
}
