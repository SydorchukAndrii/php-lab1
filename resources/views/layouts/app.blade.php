<!doctype html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Складський облік') · Laravel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="{{ route('ui.home') }}">Складський облік</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="nav">
            <div class="navbar-nav flex-wrap">
                <a class="nav-link" href="{{ route('ui.products.index') }}">Товари</a>
                <a class="nav-link" href="{{ route('ui.categories.index') }}">Категорії</a>
                <a class="nav-link" href="{{ route('ui.suppliers.index') }}">Постачальники</a>
                <a class="nav-link" href="{{ route('ui.warehouses.index') }}">Склади</a>
                <a class="nav-link" href="{{ route('ui.stocks.index') }}">Залишки</a>
                <a class="nav-link" href="{{ route('ui.movements.index') }}">Рух товарів</a>
                <a class="nav-link" href="{{ route('ui.movement-items.index') }}">Позиції документів</a>
                <a class="nav-link" href="{{ route('ui.users.index') }}">Користувачі</a>
            </div>
        </div>
    </div>
</nav>
<main class="container pb-5">
    @if (session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <strong>Перевірте введені дані.</strong>
            <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    @yield('content')
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
