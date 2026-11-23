<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_and_catalog_render_html(): void
    {
        $category = Category::factory()->create();
        Product::factory()->for($category)->create(['name' => 'Тестовий ноутбук']);
        $this->get('/ui')->assertOk()->assertSee('Система обліку товарів на складі');
        $this->get('/ui/products')->assertOk()->assertSee('Тестовий ноутбук')->assertSee($category->name);
    }

    public function test_form_uses_csrf_and_shows_validation_errors(): void
    {
        Supplier::factory()->create();
        $this->loginAsUser();
        $this->get('/ui/products/create')->assertOk()
            ->assertSee('name="_token"', false)->assertSee('name="supplier_ids[]"', false);
        $this->post('/ui/products', ['sku' => '', 'name' => ''])->assertSessionHasErrors(['sku', 'name', 'category_id']);
    }

    public function test_create_update_delete_product_and_pivot(): void
    {
        $user = $this->loginAsUser();
        $category = Category::factory()->create();
        $supplierA = Supplier::factory()->create();
        $supplierB = Supplier::factory()->create();
        $this->post('/ui/products', [
            'sku' => 'WEB7-A',
            'name' => 'Товар для браузера',
            'unit' => 'шт',
            'category_id' => $category->id,
            'minimum_stock' => 5,
            'is_active' => 1,
            'supplier_ids' => [$supplierA->id],
        ])->assertRedirect();
        $product = Product::where('sku', 'WEB7-A')->firstOrFail();
        $this->assertEquals($user->id, $product->user_id);
        $this->assertDatabaseHas('product_supplier', ['product_id' => $product->id, 'supplier_id' => $supplierA->id]);
        $this->put('/ui/products/'.$product->id, [
            'sku' => 'WEB7-A',
            'name' => 'Оновлений товар',
            'unit' => 'шт',
            'category_id' => $category->id,
            'minimum_stock' => 7,
            'is_active' => 1,
            'supplier_ids' => [$supplierB->id],
        ])->assertRedirect();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Оновлений товар']);
        $this->assertDatabaseHas('product_supplier', ['product_id' => $product->id, 'supplier_id' => $supplierB->id]);
        $this->assertDatabaseMissing('product_supplier', ['product_id' => $product->id, 'supplier_id' => $supplierA->id]);
        $this->delete('/ui/products/'.$product->id)->assertRedirect('/ui/products');
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_directory_forms_and_user_profile(): void
    {
        $this->loginAsUser(['role' => 'admin']);
        $this->get('/ui/categories/create')->assertOk()->assertSee('Зберегти');
        $this->post('/ui/categories', ['name' => 'Побутова техніка', 'code' => 'HOUSE', 'is_active' => 1])->assertRedirect('/ui/categories');
        $this->assertDatabaseHas('categories', ['code' => 'HOUSE']);
        $this->post('/ui/users', [
            'name' => 'Тестовий менеджер',
            'email' => 'ui7@example.com',
            'password' => 'pass123456',
            'role' => 'manager',
            'is_active' => 1,
            'profile_phone' => '+380501234567',
            'profile_position' => 'Менеджер',
        ])->assertRedirect('/ui/users');
        $user = User::where('email', 'ui7@example.com')->firstOrFail();
        $this->assertDatabaseHas('user_profiles', ['user_id' => $user->id, 'position' => 'Менеджер']);
    }

    public function test_movement_form_creates_and_posts_receipt(): void
    {
        $user = $this->loginAsUser(['role' => 'manager']);
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();
        $this->get('/ui/movements/create')->assertOk()->assertSee('Зберегти чернетку');
        $this->post('/ui/movements', [
            'user_id' => $user->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'receipt',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5, 'unit_price' => 12.50],
            ],
        ])->assertRedirect();
        $movement = StockMovement::firstOrFail();
        $this->assertDatabaseHas('movement_items', [
            'stock_movement_id' => $movement->id,
            'product_id' => $product->id,
            'quantity' => 5,
        ]);
        $this->post('/ui/movements/'.$movement->id.'/post')->assertRedirect();
        $this->assertDatabaseHas('stocks', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 5,
        ]);
    }

    private function loginAsUser(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);

        if (! $user instanceof User) {
            throw new \LogicException('Expected User model.');
        }

        $this->actingAs($user);

        return $user;
    }
}
