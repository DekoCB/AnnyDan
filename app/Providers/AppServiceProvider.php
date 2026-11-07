<?php

namespace App\Providers;

use App\Enums\Rol;
use App\View\Composers\TopbarComposer;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(fn ($user, string $ability) => $user->hasRole(Rol::ADMINISTRADOR->value) ? true : null);

        View::composer('layouts.topbar', TopbarComposer::class);
    }
}
