<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductApiSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_read_but_cannot_create(): void
    {
        $this->getJson('/api/v1/products')->assertOk();
        $this->postJson('/api/v1/products', ['name' => 'Тест'])->assertUnauthorized();
    }

    public function test_owner_can_create_and_update_and_other_user_cannot_delete(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $category = Category::factory()->create();
        Sanctum::actingAs($owner);
        $this->postJson('/api/v1/products', [
            'category_id' => $category->id, 'sku' => 'API8-101', 'name' => 'Лабораторний товар',
            'unit' => 'шт', 'minimum_stock' => 5,
        ])->assertCreated()->assertJsonPath('data.owner_id', $owner->id);
        $product = Product::where('sku', 'API8-101')->firstOrFail();
        $this->putJson('/api/v1/products/'.$product->id, ['name' => 'Оновлений товар'])->assertOk();
        Sanctum::actingAs($intruder);
        $this->deleteJson('/api/v1/products/'.$product->id)->assertForbidden();
        $this->getJson('/api/v1/products/'.$product->id)->assertOk();
    }

    public function test_guest_cannot_open_web_create_and_intruder_cannot_edit(): void
    {
        $this->get('/ui/products/create')->assertRedirect('/login');
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $product = Product::factory()->create(['user_id' => $owner->id]);
        $this->actingAs($intruder)->get('/ui/products/'.$product->id.'/edit')->assertForbidden();
    }

    public function test_role_controls_directory_and_user_access(): void
    {
        $worker = User::factory()->create(['role' => 'worker']);
        $manager = User::factory()->create(['role' => 'manager']);
        $this->actingAs($worker)->get('/ui/users')->assertForbidden();
        $this->actingAs($worker)->get('/ui/categories/create')->assertForbidden();
        $this->actingAs($manager)->get('/ui/categories/create')->assertOk();
    }
}
