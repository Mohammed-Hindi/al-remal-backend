<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Table;
use Illuminate\Http\JsonResponse;

class TableController extends Controller
{
    public function show(string $qrToken): JsonResponse
    {
        $table = Table::where('qr_token', $qrToken)->firstOrFail();

        return response()->json(['data' => $table]);
    }
}
