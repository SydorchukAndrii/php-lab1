@extends('layouts.app')
@section('title', 'Каталог товарів')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h2">Каталог товарів</h1>@auth<a href="{{ route('ui.products.create') }}" class="btn btn-primary">Додати товар</a>@endauth
</div>
<form method="GET" action="{{ route('ui.products.index') }}" class="d-flex gap-2 mb-3">
    <input name="q" value="{{ request('q') }}" class="form-control" placeholder="Назва або SKU">
    <button class="btn btn-outline-secondary">Пошук</button>
</form>
<div class="table-responsive"><table class="table table-striped table-bordered align-middle bg-white">
<thead class="table-dark"><tr><th>ID</th><th>SKU</th><th>Назва</th><th>Категорія</th><th>Постачальники</th><th>Залишок</th><th>Дії</th></tr></thead>
<tbody>
@forelse ($products as $product)
<tr>
    <td>{{ $product->id }}</td><td>{{ $product->sku }}</td><td>{{ $product->name }}</td>
    <td>{{ $product->category?->name ?? '—' }}</td>
    <td>@forelse ($product->suppliers as $supplier)<span class="badge bg-secondary me-1">{{ $supplier->name }}</span>@empty — @endforelse</td>
    <td>{{ $product->stocks->sum('quantity') }} {{ $product->unit }}</td>
    <td class="text-nowrap">
        <a href="{{ route('ui.products.show', $product) }}" class="btn btn-sm btn-outline-info">Перегляд</a>
        @can('update', $product)
        <a href="{{ route('ui.products.edit', $product) }}" class="btn btn-sm btn-outline-primary">Змінити</a>
        @endcan
        @can('delete', $product)
        <form method="POST" action="{{ route('ui.products.destroy', $product) }}" class="d-inline" onsubmit="return confirm('Видалити товар?')">
            @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Видалити</button>
        </form>
        @endcan
    </td>
</tr>
@empty<tr><td colspan="7" class="text-center text-muted">Товарів не знайдено.</td></tr>@endforelse
</tbody></table></div>
{{ $products->links('pagination::bootstrap-5') }}
@endsection
