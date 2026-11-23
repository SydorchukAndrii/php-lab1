@extends('layouts.app')
@section('title', $product->exists ? 'Редагування товару' : 'Новий товар')
@section('content')
<h1 class="h2 mb-3">{{ $product->exists ? 'Редагування товару' : 'Створення товару' }}</h1>
<form method="POST" action="{{ $product->exists ? route('ui.products.update', $product) : route('ui.products.store') }}" class="card p-4 shadow-sm">
    @csrf
    @if ($product->exists) @method('PUT') @endif
    <div class="row">
        <div class="col-md-6"><x-form.input name="sku" label="SKU (артикул)" :value="$product->sku" required /></div>
        <div class="col-md-6"><x-form.input name="name" label="Назва товару" :value="$product->name" required /></div>
        <div class="col-md-6"><x-form.input name="unit" label="Одиниця виміру" :value="$product->unit" required /></div>
        <div class="col-md-6"><x-form.input name="minimum_stock" label="Мінімальний залишок" type="number" min="0" :value="$product->minimum_stock" required /></div>
    </div>
    <div class="mb-3">
        <label class="form-label" for="category_id">Категорія (1:M)</label>
        <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
            <option value="">— Оберіть категорію —</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string)old('category_id', $product->category_id) === (string)$category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <fieldset class="mb-3 border rounded p-3"><legend class="float-none w-auto px-2 fs-6">Постачальники (M:M)</legend>
        @php($selectedSuppliers = array_map('intval', old('supplier_ids', $product->exists ? $product->suppliers->modelKeys() : [])))
        @forelse ($suppliers as $supplier)
            <label class="form-check"><input type="checkbox" class="form-check-input" name="supplier_ids[]" value="{{ $supplier->id }}" @checked(in_array($supplier->id, $selectedSuppliers, true))> {{ $supplier->name }}</label>
        @empty<p class="text-muted">Спочатку створіть постачальника в довіднику.</p>@endforelse
    </fieldset>
    <input type="hidden" name="is_active" value="0">
    <label class="form-check mb-3"><input type="checkbox" name="is_active" value="1" class="form-check-input" @checked((bool)old('is_active', $product->is_active))> Активний товар</label>
    <div class="d-flex gap-2"><button class="btn btn-success">{{ $product->exists ? 'Зберегти зміни' : 'Створити товар' }}</button><a href="{{ route('ui.products.index') }}" class="btn btn-secondary">Назад</a></div>
</form>
@endsection
