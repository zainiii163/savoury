<?php

namespace App\Providers;

use App\Services\Cart\SessionCartService;
use Illuminate\Pagination\Paginator;
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
        Paginator::useBootstrapFive();

        View::composer('components.site-header', function ($view) {
            $view->with('cartCount', app(SessionCartService::class)->count());
        });
    }
}
