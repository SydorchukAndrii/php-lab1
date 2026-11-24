<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class MovementDraftService
{
    public function createDraft(array $data): StockMovement
    {
        return DB::transaction(function () use ($data): StockMovement {
            $movement = StockMovement::create([
                'user_id' => $data['user_id'], 'warehouse_id' => $data['warehouse_id'],
                'type' => $data['type'], 'status' => 'draft',
            ]);
            foreach ($data['items'] as $item) {
                $movement->items()->create($item);
            }

            return $movement;
        });
    }

    public function updateDraft(StockMovement $movement, array $data): StockMovement
    {
        return DB::transaction(function () use ($movement, $data): StockMovement {
            abort_unless($movement->status === 'draft', 403);
            $movement->update([
                'user_id' => $data['user_id'], 'warehouse_id' => $data['warehouse_id'], 'type' => $data['type'],
            ]);
            $movement->items()->delete();
            foreach ($data['items'] as $item) {
                $movement->items()->create($item);
            }

            return $movement->refresh();
        });
    }
}
