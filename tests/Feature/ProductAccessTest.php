<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class ProductAccessTest extends TestCase
{
    private function payload(): array
    {
        return ['sku' => 'SKU-003', 'name' => 'Монітор', 'unit' => 'шт', 'minimumStock' => 4];
    }

    public function test_index_returns_200(): void
    {
        $this->getJson('/products')->assertOk()->assertJsonPath('status', 'success');
    }

    public function test_missing_product_returns_404(): void
    {
        $this->getJson('/products/999')->assertNotFound();
    }

    public function test_missing_scanner_token_returns_403(): void
    {
        $this->postJson('/products', $this->payload())
            ->assertForbidden()->assertJsonPath('status', 'access_denied');
    }

    public function test_valid_scanner_token_returns_201_and_policy_header(): void
    {
        config()->set('services.scanner.token', 'test-scanner-token');
        $this->withHeader('X-Scanner-Token', 'test-scanner-token')
            ->postJson('/products', $this->payload())
            ->assertCreated()->assertHeader('X-Security-Policy', 'Enforced')
            ->assertJsonPath('data.sku', 'SKU-003');
    }

    public function test_validation_requires_four_fields(): void
    {
        config()->set('services.scanner.token', 'test-scanner-token');
        $this->withHeader('X-Scanner-Token', 'test-scanner-token')
            ->postJson('/products', [])
            ->assertUnprocessable()->assertJsonValidationErrors(['sku', 'name', 'unit', 'minimumStock']);
    }
}
