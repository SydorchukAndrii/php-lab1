<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class ProductApiController extends Controller
{
    public function __construct(private readonly ProductService $products) {}

    #[OA\Get(path: '/products', summary: 'Каталог товарів', tags: ['Products'], responses: [new OA\Response(response: 200, description: 'Список товарів')])]
    public function index(): mixed
    {
        return ProductResource::collection($this->products->getPaginatedList());
    }

    #[OA\Get(path: '/products/{product}', summary: 'Перегляд товару', tags: ['Products'], parameters: [new OA\Parameter(name: 'product', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'Товар'), new OA\Response(response: 404, description: 'Не знайдено')])]
    public function show(Product $product): ProductResource
    {
        return new ProductResource($product->load(['category', 'suppliers', 'stocks']));
    }

    #[OA\Post(path: '/products', summary: 'Створити товар', security: [['bearerAuth' => []]], tags: ['Products'], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['category_id', 'sku', 'name', 'unit', 'minimum_stock'], properties: [new OA\Property(property: 'category_id', type: 'integer', example: 1), new OA\Property(property: 'sku', type: 'string', example: 'LAB8-101'), new OA\Property(property: 'name', type: 'string', example: 'Монітор'), new OA\Property(property: 'unit', type: 'string', example: 'шт'), new OA\Property(property: 'minimum_stock', type: 'integer', example: 5), new OA\Property(property: 'supplier_ids', type: 'array', items: new OA\Items(type: 'integer'), example: [1, 2])])), responses: [new OA\Response(response: 201, description: 'Створено'), new OA\Response(response: 401, description: 'Неавтентифікований запит'), new OA\Response(response: 422, description: 'Помилка валідації')])]
    public function store(StoreProductRequest $request): JsonResponse
    {
        $this->authorize('create', Product::class);
        $product = $this->products->createProduct($request->validated(), $request->user());

        return (new ProductResource($product))->response()->setStatusCode(201);
    }

    #[OA\Put(path: '/products/{product}', summary: 'Редагувати власний товар', security: [['bearerAuth' => []]], tags: ['Products'], parameters: [new OA\Parameter(name: 'product', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [new OA\Property(property: 'name', type: 'string', example: 'Оновлений монітор'), new OA\Property(property: 'supplier_ids', type: 'array', items: new OA\Items(type: 'integer'), example: [1])])), responses: [new OA\Response(response: 200, description: 'Оновлено'), new OA\Response(response: 401, description: 'Без токена'), new OA\Response(response: 403, description: 'Чужий товар')])]
    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $this->authorize('update', $product);

        return new ProductResource($this->products->updateProduct($product, $request->validated()));
    }

    #[OA\Delete(path: '/products/{product}', summary: 'Видалити власний товар', security: [['bearerAuth' => []]], tags: ['Products'], parameters: [new OA\Parameter(name: 'product', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 204, description: 'Видалено'), new OA\Response(response: 401, description: 'Без токена'), new OA\Response(response: 403, description: 'Чужий товар')])]
    public function destroy(Product $product): Response
    {
        $this->authorize('delete', $product);
        $this->products->deleteProduct($product);

        return response()->noContent();
    }
}
