@extends('layouts.app')
@section('title', 'Вхід')
@section('content')
<div class="card shadow-sm mx-auto" style="max-width:470px"><div class="card-body p-4">
    <h1 class="h3 mb-4">Вхід до системи</h1>
    <form method="POST" action="{{ route('login') }}">@csrf
        <x-form.input name="email" type="email" label="Email" required />
        <x-form.input name="password" type="password" label="Пароль" required />
        <label class="form-check mb-3"><input type="checkbox" name="remember" value="1" class="form-check-input"> Запам'ятати мене</label>
        <button class="btn btn-primary">Увійти</button>
        <a class="btn btn-link" href="{{ route('register') }}">Реєстрація</a>
    </form>
</div></div>
@endsection
