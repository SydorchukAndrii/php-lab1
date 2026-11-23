<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

/** JSON CRUD для трьох довідників; змінення записів дозволено manager/admin. */
class DirectoryApiController extends Controller
{
    private function model(Request $request): string
    {
        return match ($request->segment(3)) {
            'categories' => Category::class,
            'suppliers' => Supplier::class,
            'warehouses' => Warehouse::class,
            default => abort(404),
        };
    }

    #[OA\Get(path: '/categories', summary: 'Список: categories', tags: ['Directories'], responses: [new OA\Response(response: 200, description: 'Список записів')])]
    #[OA\Get(path: '/suppliers', summary: 'Список: suppliers', tags: ['Directories'], responses: [new OA\Response(response: 200, description: 'Список записів')])]
    #[OA\Get(path: '/warehouses', summary: 'Список: warehouses', tags: ['Directories'], responses: [new OA\Response(response: 200, description: 'Список записів')])]
    public function index(Request $request): JsonResponse
    {
        $model = $this->model($request);

        return response()->json(['data' => $model::query()->latest('id')->paginate(15)]);
    }

    public function show(Request $request, int $record): JsonResponse
    {
        $model = $this->model($request);

        return response()->json(['data' => $model::findOrFail($record)]);
    }

    #[OA\Post(path: '/categories', summary: 'Створити запис: categories', tags: ['Directories'], security: [['bearerAuth' => []]], responses: [new OA\Response(response: 201, description: 'Створено'), new OA\Response(response: 401, description: 'Без токена'), new OA\Response(response: 403, description: 'Бракує прав')])]
    #[OA\Post(path: '/suppliers', summary: 'Створити запис: suppliers', tags: ['Directories'], security: [['bearerAuth' => []]], responses: [new OA\Response(response: 201, description: 'Створено'), new OA\Response(response: 401, description: 'Без токена'), new OA\Response(response: 403, description: 'Бракує прав')])]
    #[OA\Post(path: '/warehouses', summary: 'Створити запис: warehouses', tags: ['Directories'], security: [['bearerAuth' => []]], responses: [new OA\Response(response: 201, description: 'Створено'), new OA\Response(response: 401, description: 'Без токена'), new OA\Response(response: 403, description: 'Бракує прав')])]
    public function store(Request $request): JsonResponse
    {
        Gate::authorize('manage-directories');
        $model = $this->model($request);

        return response()->json(['data' => $model::create($this->validated($request))], 201);
    }

    public function update(Request $request, int $record): JsonResponse
    {
        Gate::authorize('manage-directories');
        $model = $this->model($request);
        $instance = $model::findOrFail($record);
        $instance->update($this->validated($request, $record, true));

        return response()->json(['data' => $instance->refresh()]);
    }

    public function destroy(Request $request, int $record): Response
    {
        Gate::authorize('manage-directories');
        $model = $this->model($request);
        $model::findOrFail($record)->delete();

        return response()->noContent();
    }

    private function validated(Request $request, ?int $id = null, bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return $request->validate(match ($request->segment(3)) {
            'categories' => [
                'name' => [$required, 'string', 'max:255', Rule::unique('categories', 'name')->ignore($id)],
                'code' => [$required, 'string', 'max:255', Rule::unique('categories', 'code')->ignore($id)],
                'description' => ['sometimes', 'nullable', 'string'],
                'is_active' => ['sometimes', 'boolean'],
            ],
            'suppliers' => [
                'name' => [$required, 'string', 'max:255'],
                'contact_person' => ['sometimes', 'nullable', 'string', 'max:255'],
                'phone' => ['sometimes', 'nullable', 'string', 'max:255'],
                'email' => ['sometimes', 'nullable', 'email', 'max:255', Rule::unique('suppliers', 'email')->ignore($id)],
            ],
            'warehouses' => [
                'name' => [$required, 'string', 'max:255'],
                'code' => [$required, 'string', 'max:255', Rule::unique('warehouses', 'code')->ignore($id)],
                'address' => [$required, 'string', 'max:255'],
                'is_active' => ['sometimes', 'boolean'],
            ],
            default => [],
        });
    }
}
