<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\SendProductCreatedNotificationJob;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductCrudRefactoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_creation_saves_pivot_and_dispatches_job(): void
    {
        Queue::fake();
        $user = User::factory()->create(['role' => 'worker', 'is_active' => 1]);
        $category = Category::factory()->create();
        $suppliers = Supplier::factory()->count(2)->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/products', [
            'category_id' => $category->id,
            'sku' => 'REF9-API-1',
            'name' => 'Товар через API',
            'unit' => 'шт',
            'minimum_stock' => 5,
            'supplier_ids' => $suppliers->modelKeys(),
        ])->assertCreated()->assertJsonPath('data.owner_id', $user->id);

        $product = Product::where('sku', 'REF9-API-1')->firstOrFail();
        $this->assertDatabaseCount('product_supplier', 2);
        Queue::assertPushed(SendProductCreatedNotificationJob::class, 1);
        $this->assertSame($user->id, $product->user_id);
    }

    public function test_web_and_api_use_the_same_business_rules(): void
    {
        Queue::fake();
        $user = User::factory()->create(['role' => 'worker', 'is_active' => 1]);
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();

        $this->actingAs($user)->post('/ui/products', [
            'category_id' => $category->id, 'sku' => 'REF9-WEB-1',
            'name' => 'Товар через Web', 'unit' => 'шт', 'minimum_stock' => 5,
            'is_active' => 1, 'supplier_ids' => [$supplier->id],
        ])->assertRedirect();

        Sanctum::actingAs($user);
        $this->postJson('/api/v1/products', [
            'category_id' => $category->id, 'sku' => 'REF9-API-2',
            'name' => 'Товар через API', 'unit' => 'шт', 'minimum_stock' => 5,
            'supplier_ids' => [$supplier->id],
        ])->assertCreated();

        $this->assertDatabaseCount('products', 2);
        $this->assertDatabaseCount('product_supplier', 2);
        Queue::assertPushed(SendProductCreatedNotificationJob::class, 2);
    }
}
