<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_include_category_and_suppliers(): void
    {
        $category = Category::factory()->create(['name' => 'Електроніка']);
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->for($category)->create(['name' => 'Бездротова миша']);
        $product->suppliers()->attach($supplier->id);

        $this->getJson('/products')->assertOk()
            ->assertJsonFragment(['name' => 'Бездротова миша'])
            ->assertJsonFragment(['name' => 'Електроніка'])
            ->assertJsonFragment(['name' => $supplier->name]);
    }

    public function test_create_product_saves_pivot_relations(): void
    {
        config()->set('services.scanner.token', 'test-scanner-token');
        $category = Category::factory()->create();
        $suppliers = Supplier::factory()->count(2)->create();
        $res = $this->withHeader('X-Scanner-Token', 'test-scanner-token')
            ->postJson('/products', [
                'category_id' => $category->id,
                'sku' => 'NEW-100', 'name' => 'Тестовий товар', 'unit' => 'шт',
                'minimumStock' => 5, 'supplier_ids' => $suppliers->modelKeys(),
            ])->assertCreated();
        $this->assertDatabaseHas('products', ['id' => $res->json('data.id'), 'category_id' => $category->id, 'minimum_stock' => 5]);
        foreach ($suppliers as $supplier) {
            $this->assertDatabaseHas('product_supplier', ['product_id' => $res->json('data.id'), 'supplier_id' => $supplier->id]);
        }
    }

    public function test_update_syncs_suppliers(): void
    {
        config()->set('services.scanner.token', 'test-scanner-token');
        $product = Product::factory()->create();
        $suppliers = Supplier::factory()->count(2)->create();
        $product->suppliers()->attach($suppliers[0]);
        $this->withHeader('X-Scanner-Token', 'test-scanner-token')
            ->putJson('/products/'.$product->id, ['supplier_ids' => [$suppliers[1]->id]])->assertOk();
        $this->assertDatabaseMissing('product_supplier', ['product_id' => $product->id, 'supplier_id' => $suppliers[0]->id]);
        $this->assertDatabaseHas('product_supplier', ['product_id' => $product->id, 'supplier_id' => $suppliers[1]->id]);
    }
}
