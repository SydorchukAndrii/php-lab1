<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use OpenApi\Attributes as OA;

#[OA\Info(version: '1.0.0', title: 'Складський облік — REST API', description: 'Лабораторна робота №8: авторизація Sanctum та OpenAPI')]
#[OA\Server(url: 'http://127.0.0.1:8000/api/v1', description: 'Локальний сервер')]
#[OA\SecurityScheme(securityScheme: 'bearerAuth', type: 'http', scheme: 'bearer', bearerFormat: 'Sanctum token')]
abstract class Controller
{
    use AuthorizesRequests;
}
