<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\ProductService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ProductServiceTest extends TestCase
{
    public function test_calculates_stock_deficit(): void
    {
        $service = new ProductService;
        $this->assertSame(3, $service->calculateStockDeficit(2, 5));
        $this->assertSame(0, $service->calculateStockDeficit(6, 5));
    }

    public function test_rejects_negative_stock_quantity(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new ProductService)->calculateStockDeficit(-1, 5);
    }
}
