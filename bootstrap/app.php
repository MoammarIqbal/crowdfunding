<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Illuminate\Support\Facades\Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api_tenant.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'identify.tenant' => \App\Http\Middleware\IdentifyTenant::class,
            'ensure.tenant.active' => \App\Http\Middleware\EnsureTenantIsActive::class,
            'switch.tenant.database' => \App\Http\Middleware\SwitchTenantDatabase::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
