<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    public function show(int $id): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => User::findOrFail($id)->profile]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $data = $request->validate(['phone' => ['nullable', 'string', 'max:50'], 'position' => ['nullable', 'string', 'max:255']]);
        $profile = $user->profile()->updateOrCreate(['user_id' => $user->id], $data);

        return response()->json(['status' => 'success', 'data' => $profile]);
    }
}
