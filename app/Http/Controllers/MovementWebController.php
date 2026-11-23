<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\StockMovementService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MovementWebController extends Controller
{
    public function index(): View
    {
        return view('ui.movements.index', [
            'movements' => StockMovement::with(['user', 'warehouse'])->latest('id')->paginate(10),
        ]);
    }

    public function create(): View
    {
        return $this->form(new StockMovement(['type' => 'receipt', 'status' => 'draft']));
    }

    public function show(StockMovement $movement): View
    {
        return view('ui.movements.show', ['movement' => $movement->load(['user', 'warehouse', 'items.product'])]);
    }

    public function edit(StockMovement $movement): View
    {
        abort_unless($movement->status === 'draft', 403, 'Редагувати можна лише чернетку.');

        return $this->form($movement->load('items'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateInput($request);
        $movement = DB::transaction(function () use ($data): StockMovement {
            $movement = StockMovement::create([
                'user_id' => $data['user_id'], 'warehouse_id' => $data['warehouse_id'],
                'type' => $data['type'], 'status' => 'draft',
            ]);
            foreach ($data['items'] as $item) {
                $movement->items()->create($item);
            }

            return $movement;
        });

        return redirect()->route('ui.movements.show', $movement)->with('success', 'Створено чернетку документа.');
    }

    public function update(Request $request, StockMovement $movement): RedirectResponse
    {
        abort_unless($movement->status === 'draft', 403);
        $data = $this->validateInput($request);
        DB::transaction(function () use ($movement, $data): void {
            $movement->update([
                'user_id' => $data['user_id'], 'warehouse_id' => $data['warehouse_id'], 'type' => $data['type'],
            ]);
            $movement->items()->delete();
            foreach ($data['items'] as $item) {
                $movement->items()->create($item);
            }
        });

        return redirect()->route('ui.movements.show', $movement)->with('success', 'Чернетку оновлено.');
    }

    public function destroy(StockMovement $movement): RedirectResponse
    {
        abort_unless($movement->status === 'draft', 403);
        $movement->delete();

        return redirect()->route('ui.movements.index')->with('success', 'Чернетку видалено.');
    }

    public function post(StockMovement $movement, StockMovementService $service): RedirectResponse
    {
        $service->post($movement);

        return redirect()->route('ui.movements.show', $movement)->with('success', 'Документ проведено. Залишки оновлено.');
    }

    public function cancel(StockMovement $movement, StockMovementService $service): RedirectResponse
    {
        $service->cancel($movement);

        return redirect()->route('ui.movements.show', $movement)->with('success', 'Документ скасовано.');
    }

    private function form(StockMovement $movement): View
    {
        return view('ui.movements.form', [
            'movement' => $movement,
            'users' => User::orderBy('name')->get(),
            'warehouses' => Warehouse::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
        ]);
    }

    private function validateInput(Request $request): array
    {
        // Порожні рядки форми ігноруються; кожна заповнена позиція перевіряється окремо.
        $items = array_values(array_filter($request->input('items', []),
            fn ($item) => is_array($item) && (($item['product_id'] ?? '') !== '' || ($item['quantity'] ?? '') !== '')));
        $request->merge(['items' => $items]);
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'type' => ['required', 'in:receipt,shipment'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
        ]);
        $ids = array_column($data['items'], 'product_id');
        if (count($ids) !== count(array_unique($ids))) {
            throw ValidationException::withMessages(['items' => 'Один товар можна додати в документ лише один раз.']);
        }
        foreach ($data['items'] as &$item) {
            $item['unit_price'] = $item['unit_price'] ?? 0;
        }
        unset($item);

        return $data;
    }
}
