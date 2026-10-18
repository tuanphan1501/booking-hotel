<?php

namespace App\Providers;

use App\Models\AdminUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Application-wide service registration and bootstrapping.
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register application services in the container.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap application services.
     *
     * - Enables strict Eloquent checks outside production (lazy loading, unknown attributes).
     * - Maps every permission code to a Gate ability, so `@can('booking.view')` and
     *   `->middleware('can:booking.view')` work without defining each ability by hand.
     *
     * @return void
     */
    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());

        Gate::before(
            fn (mixed $user, string $ability): ?bool => $user instanceof AdminUser && $user->hasPermission($ability)
                ? true
                : null,
        );
    }
}
