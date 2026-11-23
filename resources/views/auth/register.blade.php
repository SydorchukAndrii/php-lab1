@extends('layouts.app')
@section('title', 'Реєстрація')
@section('content')
<div class="card shadow-sm mx-auto" style="max-width:470px"><div class="card-body p-4">
    <h1 class="h3 mb-4">Реєстрація</h1>
    <form method="POST" action="{{ route('register') }}">@csrf
        <x-form.input name="name" label="Ім’я" required />
        <x-form.input name="email" type="email" label="Email" required />
        <x-form.input name="password" type="password" label="Пароль" required />
        <x-form.input name="password_confirmation" type="password" label="Повторіть пароль" required />
        <button class="btn btn-success">Створити обліковий запис</button>
    </form>
</div></div>
@endsection
