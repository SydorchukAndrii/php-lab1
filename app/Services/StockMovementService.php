<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockMovementService
{
    public function post(StockMovement $movement): StockMovement
    {
        return DB::transaction(function () use ($movement): StockMovement {
            $movement = StockMovement::query()->lockForUpdate()->findOrFail($movement->id);
            if ($movement->status !== 'draft') {
                throw ValidationException::withMessages(['status' => 'Підтверджувати можна лише чернетку.']);
            }
            $movement->load('items');
            if ($movement->items->isEmpty()) {
                throw ValidationException::withMessages(['items' => 'Документ не містить позицій.']);
            }
            foreach ($movement->items as $item) {
                $stock = Stock::query()->firstOrCreate(
                    ['warehouse_id' => $movement->warehouse_id, 'product_id' => $item->product_id],
                    ['quantity' => 0]
                );
                $stock = Stock::query()->whereKey($stock->id)->lockForUpdate()->firstOrFail();
                if ($movement->type === 'receipt') {
                    $stock->increment('quantity', $item->quantity);
                } elseif ($movement->type === 'shipment') {
                    if ($stock->quantity < $item->quantity) {
                        throw ValidationException::withMessages(['quantity' => 'Недостатній залишок для товару '.$item->product_id]);
                    }
                    $stock->decrement('quantity', $item->quantity);
                } else {
                    throw ValidationException::withMessages(['type' => 'Невідомий тип документа.']);
                }
            }
            $movement->update(['status' => 'posted']);

            return $movement->fresh(['items.product', 'warehouse', 'user']);
        });
    }

    public function cancel(StockMovement $movement): StockMovement
    {
        return DB::transaction(function () use ($movement): StockMovement {
            $movement = StockMovement::query()->lockForUpdate()->findOrFail($movement->id);
            if ($movement->status === 'cancelled') {
                throw ValidationException::withMessages(['status' => 'Документ уже скасовано.']);
            }
            if ($movement->status === 'posted') {
                foreach ($movement->items as $item) {
                    $stock = Stock::query()->where('warehouse_id', $movement->warehouse_id)
                        ->where('product_id', $item->product_id)->lockForUpdate()->firstOrFail();
                    if ($movement->type === 'receipt') {
                        if ($stock->quantity < $item->quantity) {
                            throw ValidationException::withMessages(['quantity' => 'Скасування надходження призведе до від’ємного залишку.']);
                        }
                        $stock->decrement('quantity', $item->quantity);
                    } else {
                        $stock->increment('quantity', $item->quantity);
                    }
                }
            }
            $movement->update(['status' => 'cancelled']);

            return $movement->fresh(['items.product', 'warehouse', 'user']);
        });
    }
}
