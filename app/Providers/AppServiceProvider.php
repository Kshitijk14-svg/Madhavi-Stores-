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
        \Illuminate\Pagination\Paginator::useTailwind();

        // Scope composer to layout views only — prevents one DB query per rendered sub-view
        view()->composer('*', function ($view) {
            static $wishlistIds = null;
            if ($wishlistIds === null) {
                $wishlistIds = auth()->check() ? auth()->user()->wishlistItems()->pluck('product_id')->toArray() : [];
            }
            $view->with('wishlistIds', $wishlistIds);
        });
    }
}
