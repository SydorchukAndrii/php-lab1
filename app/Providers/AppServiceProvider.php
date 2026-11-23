<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::define('manage-directories', fn (User $user): bool => in_array($user->role, ['manager', 'admin'], true) && $user->is_active);
        Gate::define('manage-stocks', fn (User $user): bool => in_array($user->role, ['manager', 'admin'], true) && $user->is_active);
        Gate::define('manage-movements', fn (User $user): bool => in_array($user->role, ['worker', 'manager', 'admin'], true) && $user->is_active);
        Gate::define('manage-users', fn (User $user): bool => $user->role === 'admin' && $user->is_active);
    }
}
