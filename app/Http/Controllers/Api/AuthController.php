<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(path: '/register', summary: 'Реєстрація та отримання токена', tags: ['Auth'], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['name', 'email', 'password', 'password_confirmation'], properties: [new OA\Property(property: 'name', type: 'string', example: 'Марія'), new OA\Property(property: 'email', type: 'string', format: 'email', example: 'maria@example.com'), new OA\Property(property: 'password', type: 'string', example: 'pass123456'), new OA\Property(property: 'password_confirmation', type: 'string', example: 'pass123456')])), responses: [new OA\Response(response: 201, description: 'Створено токен'), new OA\Response(response: 422, description: 'Помилка валідації')])]
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $user = User::create($data + ['role' => 'worker', 'is_active' => true]);

        return response()->json(['user' => $user, 'token' => $user->createToken('lab8-api')->plainTextToken], 201);
    }

    #[OA\Post(path: '/login', summary: 'Увійти та отримати Bearer-токен', tags: ['Auth'], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['email', 'password'], properties: [new OA\Property(property: 'email', type: 'string', example: 'maria@example.com'), new OA\Property(property: 'password', type: 'string', example: 'pass123456')])), responses: [new OA\Response(response: 200, description: 'Токен видано'), new OA\Response(response: 422, description: 'Неправильні облікові дані')])]
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        $user = User::where('email', $data['email'])->first();
        if (! $user || ! $user->is_active || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => 'Невірна електронна пошта або пароль.']);
        }

        return response()->json(['user' => $user, 'token' => $user->createToken('lab8-api')->plainTextToken]);
    }

    #[OA\Post(path: '/logout', summary: 'Відкликати поточний токен', security: [['bearerAuth' => []]], tags: ['Auth'], responses: [new OA\Response(response: 200, description: 'Токен відкликано'), new OA\Response(response: 401, description: 'Токен відсутній')])]
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Вихід виконано.']);
    }
}
