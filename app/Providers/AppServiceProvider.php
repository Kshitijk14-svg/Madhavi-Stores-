<?php

namespace App\Providers;

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
        view()->composer('*', function ($view) {
            $wishlistIds = auth()->check() ? auth()->user()->wishlistItems()->pluck('product_id')->toArray() : [];
            $view->with('wishlistIds', $wishlistIds);
        });
    }
}
