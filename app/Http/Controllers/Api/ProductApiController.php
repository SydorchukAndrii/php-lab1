<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class ProductApiController extends Controller
{
    #[OA\Get(path: '/products', summary: 'Каталог товарів', tags: ['Products'], responses: [new OA\Response(response: 200, description: 'Список товарів')])]
    public function index(): mixed
    {
        return ProductResource::collection(Product::with(['category', 'suppliers', 'stocks'])->latest('id')->paginate(10));
    }

    #[OA\Get(path: '/products/{product}', summary: 'Перегляд товару', tags: ['Products'], parameters: [new OA\Parameter(name: 'product', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'Товар'), new OA\Response(response: 404, description: 'Не знайдено')])]
    public function show(Product $product): ProductResource
    {
        return new ProductResource($product->load(['category', 'suppliers', 'stocks']));
    }

    #[OA\Post(path: '/products', summary: 'Створити товар', security: [['bearerAuth' => []]], tags: ['Products'], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['category_id', 'sku', 'name', 'unit', 'minimum_stock'], properties: [new OA\Property(property: 'category_id', type: 'integer', example: 1), new OA\Property(property: 'sku', type: 'string', example: 'LAB8-101'), new OA\Property(property: 'name', type: 'string', example: 'Монітор'), new OA\Property(property: 'unit', type: 'string', example: 'шт'), new OA\Property(property: 'minimum_stock', type: 'integer', example: 5), new OA\Property(property: 'supplier_ids', type: 'array', items: new OA\Items(type: 'integer'), example: [1, 2])])), responses: [new OA\Response(response: 201, description: 'Створено'), new OA\Response(response: 401, description: 'Неавтентифікований запит'), new OA\Response(response: 422, description: 'Помилка валідації')])]
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Product::class);
        $data = $this->validateInput($request);
        $data['user_id'] = $request->user()->id;
        $product = DB::transaction(function () use ($data): Product {
            $product = Product::create(Arr::except($data, ['supplier_ids']));
            $product->suppliers()->sync($data['supplier_ids'] ?? []);

            return $product;
        });

        return (new ProductResource($product->load(['category', 'suppliers', 'stocks'])))->response()->setStatusCode(201);
    }

    #[OA\Put(path: '/products/{product}', summary: 'Редагувати власний товар', security: [['bearerAuth' => []]], tags: ['Products'], parameters: [new OA\Parameter(name: 'product', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [new OA\Property(property: 'name', type: 'string', example: 'Оновлений монітор'), new OA\Property(property: 'supplier_ids', type: 'array', items: new OA\Items(type: 'integer'), example: [1])])), responses: [new OA\Response(response: 200, description: 'Оновлено'), new OA\Response(response: 401, description: 'Без токена'), new OA\Response(response: 403, description: 'Чужий товар')])]
    public function update(Request $request, Product $product): ProductResource
    {
        $this->authorize('update', $product);
        $data = $this->validateInput($request, $product);
        DB::transaction(function () use ($data, $product): void {
            $product->update(Arr::except($data, ['supplier_ids']));
            if (array_key_exists('supplier_ids', $data)) {
                $product->suppliers()->sync($data['supplier_ids']);
            }
        });

        return new ProductResource($product->refresh()->load(['category', 'suppliers', 'stocks']));
    }

    #[OA\Delete(path: '/products/{product}', summary: 'Видалити власний товар', security: [['bearerAuth' => []]], tags: ['Products'], parameters: [new OA\Parameter(name: 'product', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 204, description: 'Видалено'), new OA\Response(response: 401, description: 'Без токена'), new OA\Response(response: 403, description: 'Чужий товар')])]
    public function destroy(Product $product): Response
    {
        $this->authorize('delete', $product);
        $product->delete();

        return response()->noContent();
    }

    private function validateInput(Request $request, ?Product $product = null): array
    {
        $required = $product ? 'sometimes' : 'required';

        return $request->validate([
            'category_id' => [$required, 'integer', 'exists:categories,id'],
            'sku' => [$required, 'string', 'min:2', 'max:255', Rule::unique('products', 'sku')->ignore($product?->id)],
            'name' => [$required, 'string', 'min:3', 'max:255'],
            'unit' => [$required, 'string', 'max:20'],
            'minimum_stock' => [$required, 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'supplier_ids' => ['sometimes', 'array'],
            'supplier_ids.*' => ['integer', 'distinct', 'exists:suppliers,id'],
        ]);
    }
}
