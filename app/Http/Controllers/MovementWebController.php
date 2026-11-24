<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Movement\StoreMovementRequest;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\MovementDraftService;
use App\Services\StockMovementService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class MovementWebController extends Controller
{
    public function __construct(private readonly MovementDraftService $drafts) {}

    public function index(): View
    {
        $query = StockMovement::with(['user', 'warehouse']);
        if (auth()->user()->role === 'worker') {
            $query->where('user_id', auth()->id());
        }

        return view('ui.movements.index', ['movements' => $query->latest('id')->paginate(10)]);
    }

    public function create(): View
    {
        return $this->form(new StockMovement(['type' => 'receipt', 'status' => 'draft']));
    }

    public function show(StockMovement $movement): View
    {
        $this->authorizeMovement($movement);

        return view('ui.movements.show', ['movement' => $movement->load(['user', 'warehouse', 'items.product'])]);
    }

    public function edit(StockMovement $movement): View
    {
        $this->authorizeMovement($movement);
        abort_unless($movement->status === 'draft', 403, 'Редагувати можна лише чернетку.');

        return $this->form($movement->load('items'));
    }

    public function store(StoreMovementRequest $request): RedirectResponse
    {
        $data = $request->validated();
        abort_if($request->user()->role === 'worker' && (int) $data['user_id'] !== $request->user()->id, 403);
        $movement = $this->drafts->createDraft($data);

        return redirect()->route('ui.movements.show', $movement)->with('success', 'Створено чернетку документа.');
    }

    public function update(StoreMovementRequest $request, StockMovement $movement): RedirectResponse
    {
        $this->authorizeMovement($movement);
        abort_unless($movement->status === 'draft', 403);
        $data = $request->validated();
        abort_if(auth()->user()->role === 'worker' && (int) $data['user_id'] !== auth()->id(), 403);
        $this->drafts->updateDraft($movement, $data);

        return redirect()->route('ui.movements.show', $movement)->with('success', 'Чернетку оновлено.');
    }

    public function destroy(StockMovement $movement): RedirectResponse
    {
        $this->authorizeMovement($movement);
        abort_unless($movement->status === 'draft', 403);
        $movement->delete();

        return redirect()->route('ui.movements.index')->with('success', 'Чернетку видалено.');
    }

    public function post(StockMovement $movement, StockMovementService $service): RedirectResponse
    {
        $this->authorizeMovement($movement);
        $service->post($movement);

        return redirect()->route('ui.movements.show', $movement)->with('success', 'Документ проведено. Залишки оновлено.');
    }

    public function cancel(StockMovement $movement, StockMovementService $service): RedirectResponse
    {
        $this->authorizeMovement($movement);
        $service->cancel($movement);

        return redirect()->route('ui.movements.show', $movement)->with('success', 'Документ скасовано.');
    }

    private function authorizeMovement(StockMovement $movement): void
    {
        abort_if(auth()->user()->role === 'worker' && $movement->user_id !== auth()->id(), 403);
    }

    private function form(StockMovement $movement): View
    {
        return view('ui.movements.form', [
            'movement' => $movement,
            'users' => auth()->user()->role === 'worker' ? User::whereKey(auth()->id())->get() : User::orderBy('name')->get(),
            'warehouses' => Warehouse::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
        ]);
    }
}
