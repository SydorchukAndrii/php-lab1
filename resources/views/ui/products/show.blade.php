@extends('layouts.app')
@section('title', $product->name)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h2">{{ $product->name }}</h1>@can('update', $product)<a class="btn btn-primary" href="{{ route('ui.products.edit', $product) }}">Редагувати</a>@endcan</div>
<div class="card shadow-sm"><div class="card-body">
    <dl class="row mb-0"><dt class="col-sm-3">Артикул</dt><dd class="col-sm-9">{{ $product->sku }}</dd>
    <dt class="col-sm-3">Категорія</dt><dd class="col-sm-9">{{ $product->category?->name ?? '—' }}</dd>
    <dt class="col-sm-3">Мінімальний запас</dt><dd class="col-sm-9">{{ $product->minimum_stock }} {{ $product->unit }}</dd>
    <dt class="col-sm-3">Постачальники</dt><dd class="col-sm-9">{{ $product->suppliers->pluck('name')->join(', ') ?: '—' }}</dd></dl>
    <h2 class="h5">Залишки за складами</h2>
    <ul class="list-group">@forelse ($product->stocks as $stock)<li class="list-group-item">{{ $stock->warehouse?->name }}: {{ $stock->quantity }} {{ $product->unit }}</li>@empty<li class="list-group-item">Немає залишків.</li>@endforelse</ul>
</div></div>
<a href="{{ route('ui.products.index') }}" class="btn btn-link mt-3">До каталогу</a>
@endsection
