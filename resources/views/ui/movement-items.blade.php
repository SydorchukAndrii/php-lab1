@extends('layouts.app')
@section('title', 'Позиції документів')
@section('content')
<h1 class="h2 mb-3">Позиції складських документів</h1>
<p class="text-muted">Позиції створюються, змінюються та видаляються у формі чернетки документа.</p>
<table class="table table-bordered table-striped bg-white"><thead class="table-dark"><tr><th>ID</th><th>Документ</th><th>Товар</th><th>Кількість</th><th>Ціна</th></tr></thead><tbody>
@forelse ($items as $item)
<tr><td>{{ $item->id }}</td><td><a href="{{ route('ui.movements.show', $item->stock_movement_id) }}">#{{ $item->stock_movement_id }}</a></td><td>{{ $item->product?->name }}</td><td>{{ $item->quantity }}</td><td>{{ $item->unit_price }}</td></tr>
@empty<tr><td colspan="5">Позицій немає.</td></tr>@endforelse
</tbody></table>
{{ $items->links('pagination::bootstrap-5') }}
@endsection
