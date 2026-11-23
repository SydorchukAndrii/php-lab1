@extends('layouts.app')
@section('title', 'Складські документи')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h2">Документи руху</h1><a href="{{ route('ui.movements.create') }}" class="btn btn-primary">Створити документ</a></div>
<table class="table table-striped table-bordered bg-white"><thead class="table-dark"><tr><th>ID</th><th>Тип</th><th>Склад</th><th>Виконавець</th><th>Статус</th><th>Дії</th></tr></thead><tbody>
@forelse ($movements as $movement)
<tr><td>{{ $movement->id }}</td><td>{{ $movement->type === 'receipt' ? 'Надходження' : 'Відвантаження' }}</td><td>{{ $movement->warehouse?->name }}</td><td>{{ $movement->user?->name }}</td><td>{{ $movement->status }}</td>
<td><a class="btn btn-sm btn-outline-primary" href="{{ route('ui.movements.show', $movement) }}">Переглянути</a></td></tr>
@empty<tr><td colspan="6" class="text-center">Документів немає.</td></tr>@endforelse
</tbody></table>
{{ $movements->links('pagination::bootstrap-5') }}
@endsection
