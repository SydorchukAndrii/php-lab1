@extends('layouts.app')
@section('title', $movement->exists ? 'Редагувати документ' : 'Новий документ')
@section('content')
<h1 class="h2 mb-3">{{ $movement->exists ? 'Редагування документа' : 'Новий документ руху' }}</h1>
<form method="POST" class="card p-4 shadow-sm" action="{{ $movement->exists ? route('ui.movements.update', $movement) : route('ui.movements.store') }}">
    @csrf
    @if ($movement->exists) @method('PUT') @endif
    <div class="row">
        <div class="col-md-4 mb-3"><label class="form-label" for="user_id">Працівник</label>
            <select name="user_id" id="user_id" required class="form-select"><option value="">— Оберіть —</option>
                @foreach ($users as $user)<option value="{{ $user->id }}" @selected((string)old('user_id', $movement->user_id) === (string)$user->id)>{{ $user->name }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-4 mb-3"><label class="form-label" for="warehouse_id">Склад</label>
            <select name="warehouse_id" id="warehouse_id" required class="form-select"><option value="">— Оберіть —</option>
                @foreach ($warehouses as $warehouse)<option value="{{ $warehouse->id }}" @selected((string)old('warehouse_id', $movement->warehouse_id) === (string)$warehouse->id)>{{ $warehouse->name }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-4 mb-3"><label class="form-label" for="type">Тип</label>
            <select name="type" id="type" class="form-select"><option value="receipt" @selected(old('type', $movement->type) === 'receipt')>Надходження</option><option value="shipment" @selected(old('type', $movement->type) === 'shipment')>Відвантаження</option></select>
        </div>
    </div>
    <h2 class="h5 mt-3">Позиції документа (MovementItem)</h2>
    @php
        $current = $movement->exists ? $movement->items->map(fn ($item) => $item->only(['product_id', 'quantity', 'unit_price']))->all() : [];
        $lines = old('items', $current);
        $lineCount = max(5, count($lines));
    @endphp
    <p class="small text-muted">Заповни потрібні рядки. Порожні рядки ігноруються. Один товар можна додати лише один раз.</p>
    @for ($i = 0; $i < $lineCount; $i++)
        @php($line = $lines[$i] ?? [])
        <div class="row g-2 mb-2 border-bottom pb-2">
            <div class="col-md-6"><select name="items[{{ $i }}][product_id]" class="form-select"><option value="">— Товар (рядок {{ $i + 1 }}) —</option>
                @foreach ($products as $product)<option value="{{ $product->id }}" @selected((string)($line['product_id'] ?? '') === (string)$product->id)>{{ $product->sku }} · {{ $product->name }}</option>@endforeach
            </select></div>
            <div class="col-md-3"><input name="items[{{ $i }}][quantity]" type="number" min="1" class="form-control" placeholder="Кількість" value="{{ $line['quantity'] ?? '' }}"></div>
            <div class="col-md-3"><input name="items[{{ $i }}][unit_price]" type="number" min="0" step="0.01" class="form-control" placeholder="Ціна" value="{{ $line['unit_price'] ?? '' }}"></div>
        </div>
    @endfor
    <div class="d-flex gap-2 mt-3"><button class="btn btn-success">Зберегти чернетку</button><a class="btn btn-secondary" href="{{ route('ui.movements.index') }}">Скасувати</a></div>
</form>
@endsection
