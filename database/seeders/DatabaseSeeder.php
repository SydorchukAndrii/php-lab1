<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Test Warehouse User',
            'email' => 'warehouse@example.com',
            'role' => 'manager',
        ]);
        $user->profile()->create(['phone' => '+380500000000', 'position' => 'Менеджер складу']);

        $suppliers = Supplier::factory()->count(4)->create();
        $warehouses = Warehouse::factory()->count(2)->create();
        $categories = Category::factory()->count(5)->create();

        foreach ($categories as $category) {
            $products = Product::factory()->count(8)->for($category)->create();
            foreach ($products as $product) {
                $product->suppliers()->sync($suppliers->random(2)->modelKeys());
                foreach ($warehouses as $warehouse) {
                    Stock::create([
                        'product_id' => $product->id,
                        'warehouse_id' => $warehouse->id,
                        'quantity' => fake()->numberBetween(0, 45),
                    ]);
                }
            }
        }

        $movement = StockMovement::create([
            'user_id' => $user->id,
            'warehouse_id' => $warehouses->first()->id,
            'type' => 'receipt',
            'status' => 'draft',
        ]);
        $movement->items()->create([
            'product_id' => Product::query()->firstOrFail()->id,
            'quantity' => 3,
            'unit_price' => 100,
        ]);
    }
}
