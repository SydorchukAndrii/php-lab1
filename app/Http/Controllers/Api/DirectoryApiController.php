<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Directory\StoreDirectoryRequest;
use App\Http\Requests\Directory\UpdateDirectoryRequest;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\DirectoryRecordService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

/** JSON CRUD для трьох довідників; змінення записів дозволено manager/admin. */
class DirectoryApiController extends Controller
{
    public function __construct(private readonly DirectoryRecordService $records) {}

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
    public function store(StoreDirectoryRequest $request): JsonResponse
    {
        Gate::authorize('manage-directories');
        $kind = (string) $request->segment(3);

        return response()->json(['data' => $this->records->createRecord($kind, $request->validated())], 201);
    }

    public function update(UpdateDirectoryRequest $request, int $record): JsonResponse
    {
        Gate::authorize('manage-directories');
        $kind = (string) $request->segment(3);
        $class = $this->records->model($kind);
        $result = $this->records->updateRecord($kind, $class::findOrFail($record), $request->validated());

        return response()->json(['data' => $result]);
    }

    public function destroy(Request $request, int $record): Response
    {
        Gate::authorize('manage-directories');
        $class = $this->model($request);
        $this->records->deleteRecord($class::findOrFail($record));

        return response()->noContent();
    }
}
