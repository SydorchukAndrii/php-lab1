<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\ProductService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $products) {}

    public function index(Request $request): View
    {
        $items = $this->products->getPaginatedList($request->query('q'));

        return view('ui.products.index', ['products' => $items->withQueryString()]);
    }

    public function create(): View
    {
        $this->authorize('create', Product::class);

        return $this->form(new Product(['unit' => 'шт', 'minimum_stock' => 0, 'is_active' => true]));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->authorize('create', Product::class);
        $product = $this->products->createProduct($request->validated(), $request->user());

        return redirect()->route('ui.products.show', $product)->with('success', 'Товар успішно створено.');
    }

    public function show(Product $product): View
    {
        return view('ui.products.show', [
            'product' => $product->load(['category', 'suppliers', 'stocks.warehouse']),
        ]);
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        return $this->form($product->load('suppliers'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);
        $this->products->updateProduct($product, $request->validated());

        return redirect()->route('ui.products.show', $product)->with('success', 'Товар успішно оновлено.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);
        try {
            $this->products->deleteProduct($product);
        } catch (QueryException $e) {
            return back()->with('error', 'Товар використовується у залишках або документах.');
        }

        return redirect()->route('ui.products.index')->with('success', 'Товар видалено.');
    }

    private function form(Product $product): View
    {
        return view('ui.products.form', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }
}
