<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class WebAuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function showRegisterForm(): View
    {
        return view('auth.register');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => 'Невірна електронна пошта або пароль.']);
        }
        if (! $request->user()?->is_active) {
            Auth::logout();
            throw ValidationException::withMessages(['email' => 'Обліковий запис деактивовано.']);
        }
        $request->session()->regenerate();

        return redirect()->intended(route('ui.home'))->with('success', 'Вхід виконано успішно.');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        // Роль не приймаємо з клієнта: самостійно можна створити тільки worker.
        $user = User::create($data + ['role' => 'worker', 'is_active' => true]);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('ui.home')->with('success', 'Реєстрацію завершено.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('ui.home')->with('success', 'Вихід виконано.');
    }
}
