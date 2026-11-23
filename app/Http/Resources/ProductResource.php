<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'unit' => $this->unit,
            'minimum_stock' => $this->minimum_stock,
            'is_active' => (bool) $this->is_active,
            'owner_id' => $this->user_id,
            'category' => $this->whenLoaded('category', fn () => ['id' => $this->category->id, 'name' => $this->category->name]),
            'suppliers' => $this->whenLoaded('suppliers', fn () => $this->suppliers->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])),
            'stocks' => $this->whenLoaded('stocks', fn () => $this->stocks->map(fn ($s) => ['warehouse_id' => $s->warehouse_id, 'quantity' => $s->quantity])),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
