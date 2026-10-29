<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyScannerToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('services.scanner.token', '');
        $provided = (string) $request->header('X-Scanner-Token', '');

        if ($expected === '' || ! hash_equals($expected, $provided)) {
            return response()->json([
                'status' => 'access_denied',
                'message' => 'Невірний або відсутній токен сканера.',
            ], 403);
        }

        $response = $next($request);
        $response->headers->set('X-Security-Policy', 'Enforced');

        return $response;
    }
}
