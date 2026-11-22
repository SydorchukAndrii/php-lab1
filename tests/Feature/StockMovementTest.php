<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockMovementTest extends TestCase
{
    use RefreshDatabase;

    private function createDraft(string $type, int $qty, int $initial): array
    {
        $product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $user = User::factory()->create();
        Stock::create(['product_id' => $product->id, 'warehouse_id' => $warehouse->id, 'quantity' => $initial]);
        $movement = StockMovement::create(['user_id' => $user->id, 'warehouse_id' => $warehouse->id, 'type' => $type, 'status' => 'draft']);
        $movement->items()->create(['product_id' => $product->id, 'quantity' => $qty, 'unit_price' => 100]);

        return [$movement, $product, $warehouse];
    }

    public function test_receipt_increases_stock(): void
    {
        [$movement, $product, $warehouse] = $this->createDraft('receipt', 8, 3);
        config()->set('services.scanner.token', 'test-scanner-token');
        $this->withHeader('X-Scanner-Token', 'test-scanner-token')
            ->postJson('/movements/'.$movement->id.'/post')->assertOk()->assertJsonPath('data.status', 'posted');
        $this->assertDatabaseHas('stocks', ['product_id' => $product->id, 'warehouse_id' => $warehouse->id, 'quantity' => 11]);
    }

    public function test_shipment_rejects_insufficient_stock(): void
    {
        [$movement, $product, $warehouse] = $this->createDraft('shipment', 8, 3);
        config()->set('services.scanner.token', 'test-scanner-token');
        $this->withHeader('X-Scanner-Token', 'test-scanner-token')
            ->postJson('/movements/'.$movement->id.'/post')->assertUnprocessable();
        $this->assertDatabaseHas('stocks', ['product_id' => $product->id, 'warehouse_id' => $warehouse->id, 'quantity' => 3]);
        $this->assertDatabaseHas('stock_movements', ['id' => $movement->id, 'status' => 'draft']);
    }

    public function test_repeated_post_is_rejected(): void
    {
        [$movement] = $this->createDraft('receipt', 2, 3);
        config()->set('services.scanner.token', 'test-scanner-token');
        $client = $this->withHeader('X-Scanner-Token', 'test-scanner-token');
        $client->postJson('/movements/'.$movement->id.'/post')->assertOk();
        $client->postJson('/movements/'.$movement->id.'/post')->assertUnprocessable();
    }
}
