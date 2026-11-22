<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Stock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = Stock::with(['product:id,name,sku,minimum_stock', 'warehouse:id,name,code']);
        if ($request->filled('warehouse_id')) {
            $q->where('warehouse_id', $request->integer('warehouse_id'));
        }

        return response()->json(['status' => 'success', 'data' => $q->orderBy('id')->paginate(20)]);
    }

    public function low(): JsonResponse
    {
        $items = Stock::with(['product', 'warehouse'])
            ->whereHas('product', fn ($q) => $q->where('is_active', true))
            ->get()->filter(fn (Stock $stock) => $stock->quantity < $stock->product->minimum_stock)->values();

        return response()->json(['status' => 'success', 'data' => $items]);
    }
}
