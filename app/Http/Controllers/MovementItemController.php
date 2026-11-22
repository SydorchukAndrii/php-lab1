<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\MovementItem;
use Illuminate\Http\JsonResponse;

class MovementItemController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => MovementItem::with(['movement:id,type,status,warehouse_id', 'product:id,name,sku'])->paginate(20)]);
    }
}
