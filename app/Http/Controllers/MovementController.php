<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\StockMovement;
use App\Services\StockMovementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MovementController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => StockMovement::with(['user:id,name', 'warehouse:id,name', 'items.product:id,name,sku'])->latest()->paginate(15)]);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => StockMovement::with(['user', 'warehouse', 'items.product'])->findOrFail($id)]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'type' => ['required', 'in:receipt,shipment'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['sometimes', 'numeric', 'min:0'],
        ]);
        $movement = DB::transaction(function () use ($data): StockMovement {
            $movement = StockMovement::create([
                'user_id' => $data['user_id'], 'warehouse_id' => $data['warehouse_id'],
                'type' => $data['type'], 'status' => 'draft',
            ]);
            foreach ($data['items'] as $item) {
                $movement->items()->create([
                    'product_id' => $item['product_id'], 'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'] ?? 0,
                ]);
            }

            return $movement;
        });

        return response()->json(['status' => 'created', 'data' => $movement->load('items')], 201);
    }

    public function post(int $id, StockMovementService $service): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => $service->post(StockMovement::findOrFail($id))]);
    }

    public function cancel(int $id, StockMovementService $service): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => $service->cancel(StockMovement::findOrFail($id))]);
    }
}
