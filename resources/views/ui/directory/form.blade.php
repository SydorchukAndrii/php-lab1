@extends('layouts.app')
@section('title', $spec['title'])
@section('content')
<h1 class="h2 mb-3">{{ $model->exists ? 'Редагування' : 'Створення' }}: {{ $spec['title'] }}</h1>
<form method="POST" action="{{ $model->exists ? route('ui.'.$kind.'.update', $model->id) : route('ui.'.$kind.'.store') }}" class="card p-4 shadow-sm">
@csrf
@if ($model->exists) @method('PUT') @endif
@foreach ($spec['fields'] as $field => [$label, $type])
    @php
      $isProfile = str_starts_with($field, 'profile_');
      $fieldValue = $isProfile ? data_get($model->profile ?? null, substr($field, 8)) : data_get($model, $field);
    @endphp
    @if ($type === 'checkbox')
        <div class="form-check mb-3"><input type="checkbox" class="form-check-input" name="{{ $field }}" id="{{ $field }}" value="1" @checked((bool)old($field, $model->exists ? $fieldValue : true))>
            <label for="{{ $field }}" class="form-check-label">{{ $label }}</label></div>
    @elseif (str_starts_with($type, 'select:'))
        @php($optionSet = $choices[substr($type, 7)] ?? [])
        <div class="mb-3"><label for="{{ $field }}" class="form-label">{{ $label }}</label>
            <select class="form-select @error($field) is-invalid @enderror" id="{{ $field }}" name="{{ $field }}" required>
                <option value="">— Оберіть —</option>
                @foreach ($optionSet as $value => $option)
                    <option value="{{ $value }}" @selected((string)old($field, $fieldValue) === (string)$value)>{{ $option }}</option>
                @endforeach
            </select>@error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    @elseif ($type === 'textarea')
        <div class="mb-3"><label class="form-label" for="{{ $field }}">{{ $label }}</label>
            <textarea class="form-control @error($field) is-invalid @enderror" name="{{ $field }}" id="{{ $field }}">{{ old($field, $fieldValue) }}</textarea>
            @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    @else
        <x-form.input :name="$field" :label="$label" :type="$type" :value="$type === 'password' ? '' : $fieldValue" :min="$type === 'number' ? 0 : null" />
    @endif
@endforeach
<div class="d-flex gap-2"><button type="submit" class="btn btn-success">Зберегти</button><a class="btn btn-secondary" href="{{ route('ui.'.$kind.'.index') }}">Скасувати</a></div>
</form>
@if ($kind === 'users')<p class="text-muted small mt-3">Навчальна форма користувачів. Для реального використання потрібні авторизація та перевірка ролей.</p>@endif
@endsection
