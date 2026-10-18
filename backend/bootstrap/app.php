<?php

use App\Http\Middleware\SetAdminLocale;
use App\Http\Middleware\SetApiLocale;
use App\Support\Http\ApiError;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            // Mobile App API (token auth): /api/v1/...
            Route::middleware('api')
                ->prefix('api/v1')
                ->name('api.v1.')
                ->group(base_path('routes/api_v1.php'));

            // Web Admin (server-rendered, session auth): /admin/...
            Route::middleware(['web', SetAdminLocale::class])
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [SetApiLocale::class]);

        // Sanctum token abilities: access tokens carry "api", refresh tokens carry "token:refresh".
        $middleware->alias([
            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,
        ]);

        // Guests hitting a protected admin page go to the admin login screen (AD-01).
        $middleware->redirectGuestsTo(
            fn (Request $request) => $request->is('admin', 'admin/*') ? route('admin.login') : null,
        );
        // Signed-in administrators opening the login screen go to the dashboard (AD-02).
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $isApiRequest = fn (Request $request): bool => $request->is('api/*');

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request): bool => $isApiRequest($request) || $request->expectsJson(),
        );

        // Every API error uses the same JSON shape: { code, message, details }.
        $exceptions->render(
            fn (Throwable $exception, Request $request) => $isApiRequest($request)
                ? ApiError::fromThrowable($exception)
                : null,
        );
    })->create();
