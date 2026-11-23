@extends('layouts.app')
@section('title', $spec['title'])
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h2">{{ $spec['title'] }}</h1>
    @if (auth()->check() && (auth()->user()->can('manage-directories') || auth()->user()->can('manage-stocks') || auth()->user()->can('manage-users')))
    <a class="btn btn-primary" href="{{ route('ui.'.$kind.'.create') }}">Додати {{ $spec['one'] }}</a>
    @endif
</div>
<div class="table-responsive"><table class="table table-bordered table-striped align-middle bg-white">
<thead class="table-dark"><tr><th>ID</th>@foreach ($spec['columns'] as $caption)<th>{{ $caption }}</th>@endforeach<th>Дії</th></tr></thead>
<tbody>
@forelse ($rows as $row)
<tr><td>{{ $row->id }}</td>
@foreach ($spec['columns'] as $key => $caption)
    @php($value = data_get($row, $key))
    <td>{{ is_bool($value) ? ($value ? 'Так' : 'Ні') : ($value ?? '—') }}</td>
@endforeach
<td class="text-nowrap">
    @if (auth()->check() && (auth()->user()->can('manage-directories') || auth()->user()->can('manage-stocks') || auth()->user()->can('manage-users')))
    <a class="btn btn-sm btn-outline-primary" href="{{ route('ui.'.$kind.'.edit', $row->id) }}">Редагувати</a>
    <form method="POST" action="{{ route('ui.'.$kind.'.destroy', $row->id) }}" class="d-inline" onsubmit="return confirm('Видалити запис?')">
        @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Видалити</button>
    </form>
    @endif
</td></tr>
@empty<tr><td colspan="{{ count($spec['columns']) + 2 }}" class="text-center text-muted">Записів немає.</td></tr>@endforelse
</tbody></table></div>
{{ $rows->links('pagination::bootstrap-5') }}
@if ($kind === 'stocks')<p class="small text-muted">У навчальному інтерфейсі залишки можна коригувати вручну. Для реального обліку використовуйте проведення складських документів.</p>@endif
@endsection
