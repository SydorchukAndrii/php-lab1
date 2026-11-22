<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()->with(['category:id,name,code', 'suppliers:id,name', 'stocks:id,product_id,warehouse_id,quantity']);
        if ($request->filled('q')) {
            $term = (string) $request->query('q');
            $query->where(fn ($q) => $q->where('sku', 'like', '%'.$term.'%')->orWhere('name', 'like', '%'.$term.'%'));
        }
        if ($request->boolean('activeOnly')) {
            $query->where('is_active', true);
        }

        return response()->json(['status' => 'success', 'data' => $query->orderBy('id')->paginate(15)]);
    }

    public function show(int $id): JsonResponse
    {
        $product = Product::with(['category', 'suppliers', 'stocks.warehouse'])->findOrFail($id);

        return response()->json(['status' => 'success', 'data' => $product]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $product = DB::transaction(function () use ($data): Product {
            $product = Product::create(Arr::except($data, ['supplier_ids', 'minimumStock']));
            if (array_key_exists('supplier_ids', $data)) {
                $product->suppliers()->sync($data['supplier_ids']);
            }

            return $product;
        });

        return response()->json(['status' => 'created', 'data' => $product->load(['category', 'suppliers'])], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $data = $this->validated($request, $product->id, true);
        DB::transaction(function () use ($product, $data): void {
            $product->update(Arr::except($data, ['supplier_ids', 'minimumStock']));
            if (array_key_exists('supplier_ids', $data)) {
                $product->suppliers()->sync($data['supplier_ids']);
            }
        });

        return response()->json(['status' => 'success', 'data' => $product->refresh()->load(['category', 'suppliers'])]);
    }

    private function validated(Request $request, ?int $id = null, bool $partial = false): array
    {
        $prefix = $partial ? 'sometimes' : 'required';
        $data = $request->validate([
            'category_id' => [$prefix, 'integer', 'exists:categories,id'],
            'sku' => [$prefix, 'string', 'min:2', 'max:64', Rule::unique('products', 'sku')->ignore($id)],
            'name' => [$prefix, 'string', 'min:2', 'max:255'],
            'unit' => [$prefix, 'string', 'max:20'],
            'minimumStock' => [$prefix, 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'supplier_ids' => ['sometimes', 'array'],
            'supplier_ids.*' => ['integer', 'distinct', 'exists:suppliers,id'],
        ]);
        if (array_key_exists('minimumStock', $data)) {
            $data['minimum_stock'] = $data['minimumStock'];
        }

        return $data;
    }
}
