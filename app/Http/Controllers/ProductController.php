<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /** @var array<int, array<string, int|string>> */
    private array $products = [
        1 => ['id' => 1, 'sku' => 'SKU-001', 'name' => 'Ноутбук', 'unit' => 'шт', 'minimumStock' => 3],
        2 => ['id' => 2, 'sku' => 'SKU-002', 'name' => 'Клавіатура', 'unit' => 'шт', 'minimumStock' => 5],
    ];

    public function index(): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => array_values($this->products)], 200);
    }

    public function show(string $id): JsonResponse
    {
        $product = $this->products[(int) $id] ?? null;
        if ($product === null) {
            return response()->json(['status' => 'error', 'message' => 'Товар не знайдено'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $product], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'min:2', 'max:64'],
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'unit' => ['required', 'string', 'max:20'],
            'minimumStock' => ['required', 'integer', 'min:0'],
        ]);

        // Демонстраційне сховище лише в межах одного HTTP-запиту.
        $product = ['id' => max(array_keys($this->products)) + 1, ...$validated];
        $this->products[$product['id']] = $product;

        return response()->json(['status' => 'created', 'data' => $product], 201);
    }
}
