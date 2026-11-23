@extends('layouts.app')
@section('title', 'Головна сторінка')
@section('content')
<div class="p-4 bg-white border rounded-3 shadow-sm mb-4">
    <h1 class="display-6">Система обліку товарів на складі</h1>
    <p class="text-secondary">Лабораторна №7: Blade, компоненти, форми та CRUD-операції.</p>
    <a href="{{ route('ui.products.index') }}" class="btn btn-primary">Перейти до товарів</a>
    <a href="{{ route('ui.movements.create') }}" class="btn btn-outline-primary">Новий рух товарів</a>
</div>
<div class="row g-3">
    @foreach ([['Товарів', $productsCount], ['Документів', $movementsCount], ['Одиниць на складах', $stockTotal], ['Позицій з дефіцитом', $lowStocksCount]] as $metric)
        <div class="col-sm-6 col-lg-3"><div class="card h-100 shadow-sm"><div class="card-body">
            <div class="text-muted">{{ $metric[0] }}</div><div class="display-6">{{ $metric[1] }}</div>
        </div></div></div>
    @endforeach
</div>
@endsection
