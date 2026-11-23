<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::with(['category', 'suppliers', 'stocks']);
        if ($request->filled('q')) {
            $term = (string) $request->query('q');
            $query->where(fn ($q) => $q->where('name', 'like', '%'.$term.'%')
                ->orWhere('sku', 'like', '%'.$term.'%'));
        }

        return view('ui.products.index', ['products' => $query->latest('id')->paginate(10)->withQueryString()]);
    }

    public function create(): View
    {
        $this->authorize('create', Product::class);

        return view('ui.products.form', [
            'product' => new Product(['unit' => 'шт', 'minimum_stock' => 0, 'is_active' => true]),
            'categories' => Category::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Product::class);
        $data = $this->validateInput($request);
        $data['user_id'] = $request->user()->id;
        $product = DB::transaction(function () use ($data) {
            $product = Product::create(Arr::except($data, ['supplier_ids']));
            $product->suppliers()->sync($data['supplier_ids'] ?? []);

            return $product;
        });

        return redirect()->route('ui.products.show', $product)->with('success', 'Товар успішно створено.');
    }

    public function show(Product $product): View
    {
        return view('ui.products.show', ['product' => $product->load(['category', 'suppliers', 'stocks.warehouse'])]);
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        return view('ui.products.form', [
            'product' => $product->load('suppliers'),
            'categories' => Category::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);
        $data = $this->validateInput($request, $product);
        DB::transaction(function () use ($product, $data): void {
            $product->update(Arr::except($data, ['supplier_ids']));
            $product->suppliers()->sync($data['supplier_ids'] ?? []);
        });

        return redirect()->route('ui.products.show', $product)->with('success', 'Товар успішно оновлено.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);
        try {
            $product->delete();
        } catch (QueryException $e) {
            return back()->with('error', 'Товар використовується у залишках або документах. Спочатку видаліть залежні записи.');
        }

        return redirect()->route('ui.products.index')->with('success', 'Товар видалено.');
    }

    private function validateInput(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'sku' => ['required', 'string', 'min:2', 'max:255', Rule::unique('products', 'sku')->ignore($product?->id)],
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'unit' => ['required', 'string', 'max:20'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'supplier_ids' => ['sometimes', 'array'],
            'supplier_ids.*' => ['integer', 'distinct', 'exists:suppliers,id'],
        ]);
    }
}
