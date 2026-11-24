<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class SystemInfoController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json(['application' => 'Warehouse Inventory', 'framework' => 'Laravel']);
    }
}
