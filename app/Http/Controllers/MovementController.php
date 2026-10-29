<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class MovementController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                ['id' => 1, 'type' => 'receipt', 'productId' => 1, 'quantity' => 10],
                ['id' => 2, 'type' => 'shipment', 'productId' => 2, 'quantity' => 2],
            ],
        ], 200);
    }
}
