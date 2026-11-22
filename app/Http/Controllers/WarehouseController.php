<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => Warehouse::withCount('stocks')->orderBy('id')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:64', 'unique:warehouses,code'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        return response()->json(['status' => 'created', 'data' => Warehouse::create($data)], 201);
    }
}
