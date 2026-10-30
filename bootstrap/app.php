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
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        $middleware->alias([
            'admin.access' => \App\Http\Middleware\EnsureUserCanAccessAdmin::class,
            'menu.manage' => \App\Http\Middleware\EnsureUserCanManageMenu::class,
            'orders.manage' => \App\Http\Middleware\EnsureUserCanManageOrders::class,
            'settings.manage' => \App\Http\Middleware\EnsureUserCanManageSettings::class,
            'reports.view' => \App\Http\Middleware\EnsureUserCanViewReports::class,
            'cash.collect' => \App\Http\Middleware\EnsureUserCanCollectCash::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
