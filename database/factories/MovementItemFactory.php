<?php

namespace Database\Factories;

use App\Models\MovementItem;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MovementItem> */
class MovementItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'stock_movement_id' => StockMovement::factory(),
            'product_id' => Product::factory(),
            'quantity' => fake()->numberBetween(1, 12),
            'unit_price' => fake()->randomFloat(2, 10, 2500),
        ];
    }
}
