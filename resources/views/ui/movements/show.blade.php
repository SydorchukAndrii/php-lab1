@extends('layouts.app')
@section('title', 'Документ №'.$movement->id)
@section('content')
<h1 class="h2">Документ №{{ $movement->id }}</h1>
<dl class="row"><dt class="col-sm-2">Операція</dt><dd class="col-sm-10">{{ $movement->type === 'receipt' ? 'Надходження' : 'Відвантаження' }}</dd>
<dt class="col-sm-2">Склад</dt><dd class="col-sm-10">{{ $movement->warehouse?->name }}</dd><dt class="col-sm-2">Виконавець</dt><dd class="col-sm-10">{{ $movement->user?->name }}</dd>
<dt class="col-sm-2">Статус</dt><dd class="col-sm-10">{{ $movement->status }}</dd></dl>
<table class="table table-bordered bg-white"><thead class="table-dark"><tr><th>SKU</th><th>Товар</th><th>Кількість</th><th>Ціна за одиницю</th></tr></thead><tbody>
@foreach ($movement->items as $item)<tr><td>{{ $item->product?->sku }}</td><td>{{ $item->product?->name }}</td><td>{{ $item->quantity }}</td><td>{{ number_format((float)$item->unit_price, 2, '.', ' ') }}</td></tr>@endforeach
</tbody></table>
<div class="d-flex gap-2 flex-wrap">
@if ($movement->status === 'draft')
    <a class="btn btn-outline-primary" href="{{ route('ui.movements.edit', $movement) }}">Редагувати</a>
    <form method="POST" action="{{ route('ui.movements.post', $movement) }}">@csrf<button class="btn btn-success">Провести документ</button></form>
    <form method="POST" action="{{ route('ui.movements.destroy', $movement) }}" onsubmit="return confirm('Видалити чернетку?')">@csrf @method('DELETE')<button class="btn btn-outline-danger">Видалити чернетку</button></form>
@endif
@if ($movement->status !== 'cancelled')
    <form method="POST" action="{{ route('ui.movements.cancel', $movement) }}">@csrf<button class="btn btn-warning">Скасувати документ</button></form>
@endif
<a class="btn btn-secondary" href="{{ route('ui.movements.index') }}">До списку</a>
</div>
@endsection
