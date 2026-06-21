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

        // Share wishlist ids + cart count with every view. Computed once per
        // request (memoised) so it's not a query per rendered sub-view. This is
        // why individual controllers no longer need to pass $cartCount — pages
        // that used to forget it (shop, collections) previously showed 0.
        view()->composer('*', function ($view) {
            static $wishlistIds = null;
            static $cartCount = null;

            if ($wishlistIds === null) {
                $wishlistIds = auth()->check()
                    ? auth()->user()->wishlistItems()->pluck('product_id')->toArray()
                    : [];
            }
            if ($cartCount === null) {
                $cartCount = auth()->check()
                    ? (int) auth()->user()->cartItems()->sum('quantity')
                    : 0;
            }

            $view->with('wishlistIds', $wishlistIds);
            $view->with('cartCount', $cartCount);
        });
    }
}
