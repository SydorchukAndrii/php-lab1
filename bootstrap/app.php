<?php

use App\Http\Middleware\VerifyScannerToken;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Sentry\Laravel\Integration;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['scanner.access' => VerifyScannerToken::class]);
        // Для навчального JSON API без сесійних cookie.
        // У продуктивній системі: routes/api.php + належна автентифікація.
        $middleware->validateCsrfTokens(except: [
            'products',
            'products/*',
            'categories',
            'suppliers',
            'warehouses',
            'users',
            'users/*/profile',
            'movements',
            'movements/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Sentry is configured locally via SENTRY_LARAVEL_DSN (never in Git).
        Integration::handles($exceptions);
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
