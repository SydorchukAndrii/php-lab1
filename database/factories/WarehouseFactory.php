<?php

namespace Database\Factories;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Warehouse> */
class WarehouseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Склад '.fake()->unique()->numberBetween(1, 9999),
            'address' => fake()->address(),
            'code' => fake()->unique()->bothify('WH-####'),
            'is_active' => true,
        ];
    }
}
