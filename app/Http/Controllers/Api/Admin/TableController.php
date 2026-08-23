<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTableRequest;
use App\Http\Requests\Admin\UpdateTableRequest;
use App\Models\Table;
use Illuminate\Http\JsonResponse;

class TableController extends Controller
{
    public function index(): JsonResponse
    {
        $tables = Table::query()
            ->orderBy('table_number')
            ->get();

        return response()->json(['data' => $tables]);
    }

    public function store(StoreTableRequest $request): JsonResponse
    {
        // qr_token بيتولد تلقائياً من الـ Model (boot method) - ما بنحتاج نبعته هون
        $table = Table::create($request->validated());

        return response()->json([
            'message' => 'تم إنشاء الطاولة بنجاح',
            'data' => $table,
        ], 201);
    }

    public function show(Table $table): JsonResponse
    {
        return response()->json(['data' => $table]);
    }

    public function update(UpdateTableRequest $request, Table $table): JsonResponse
    {
        $table->update($request->validated());

        return response()->json([
            'message' => 'تم تحديث الطاولة بنجاح',
            'data' => $table,
        ]);
    }

    public function destroy(Table $table): JsonResponse
    {
        $hasOrders = $table->orders()->exists();

        if ($hasOrders) {
            return response()->json([
                'message' => 'لا يمكن حذف هذه الطاولة لأنها مرتبطة بطلبات سابقة. يرجى استخدام خيار التعطيل بدلاً من الحذف.',
            ], 422);
        }

        $table->delete();

        return response()->json([
            'message' => 'تم حذف الطاولة بنجاح',
        ]);
    }

    public function regenerateQrToken(Table $table): JsonResponse
    {
        $table->update([
            'qr_token' => \Illuminate\Support\Str::random(32),
        ]);

        return response()->json([
            'message' => 'تم توليد كود QR جديد بنجاح. الكود القديم لن يعمل بعد الآن.',
            'data' => $table,
        ]);
    }
}
