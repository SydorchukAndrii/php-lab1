<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/** Only accessible in local environment by an authenticated administrator. */
class SentryDiagnosticController extends Controller
{
    public function __invoke(Request $request): never
    {
        abort_unless(app()->isLocal(), 404);
        throw new \RuntimeException('Lab 9 local Sentry diagnostic exception');
    }
}
