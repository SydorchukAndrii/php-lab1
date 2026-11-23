<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MovementItem;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class InventoryApiController extends Controller
{
    #[OA\Get(path: '/stocks', summary: 'Залишки за складами', tags: ['Inventory'], security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'Список даних'), new OA\Response(response: 401, description: 'Без токена'), new OA\Response(response: 403, description: 'Бракує прав')])]
    public function stocks(): JsonResponse
    {
        Gate::authorize('manage-stocks');

        return response()->json(['data' => Stock::with(['product', 'warehouse'])->paginate(15)]);
    }

    #[OA\Get(path: '/movements', summary: 'Складські документи', tags: ['Inventory'], security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'Список даних'), new OA\Response(response: 401, description: 'Без токена'), new OA\Response(response: 403, description: 'Бракує прав')])]
    public function movements(Request $request): JsonResponse
    {
        Gate::authorize('manage-movements');
        $query = StockMovement::with(['user', 'warehouse', 'items.product']);
        if ($request->user()->role === 'worker') {
            $query->where('user_id', $request->user()->id);
        }

        return response()->json(['data' => $query->paginate(15)]);
    }

    #[OA\Get(path: '/movement-items', summary: 'Позиції складських документів', tags: ['Inventory'], security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'Список даних'), new OA\Response(response: 401, description: 'Без токена'), new OA\Response(response: 403, description: 'Бракує прав')])]
    public function items(Request $request): JsonResponse
    {
        Gate::authorize('manage-movements');
        $query = MovementItem::with(['movement', 'product']);
        if ($request->user()->role === 'worker') {
            $query->whereHas('movement', fn ($q) => $q->where('user_id', $request->user()->id));
        }

        return response()->json(['data' => $query->paginate(15)]);
    }

    #[OA\Get(path: '/users', summary: 'Список користувачів', tags: ['Inventory'], security: [['bearerAuth' => []]], responses: [new OA\Response(response: 200, description: 'Список даних'), new OA\Response(response: 401, description: 'Без токена'), new OA\Response(response: 403, description: 'Бракує прав')])]
    public function users(): JsonResponse
    {
        Gate::authorize('manage-users');

        return response()->json(['data' => User::with('profile')->paginate(15)]);
    }

    public function profile(User $user): JsonResponse
    {
        Gate::authorize('manage-users');

        return response()->json(['data' => $user->profile]);
    }
}
