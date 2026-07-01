<?php

namespace App\Providers;

use App\Models\CartItem;
use App\Models\WishlistItem;
use App\Support\CartOwner;
use App\Support\GuestCartToken;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // On cPanel shared hosting the web root is public_html/ but Laravel's
        // public_path() resolves to madhavi-app/public/ which doesn't exist.
        // DomPDF calls realpath(public_path()) and throws "Cannot resolve public path"
        // when that directory is absent. Point to public_html when needed.
        if (!realpath(public_path())) {
            $publicHtml = realpath(base_path('../public_html'));
            if ($publicHtml) {
                $this->app->usePublicPath($publicHtml);
            }
        }
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
        //
        // Guests (not logged in) get their own cart/wishlist via a cookie token
        // (see App\Support\GuestCartToken) — read-only here, so simply viewing a
        // page never mints a cookie for a visitor who hasn't added anything yet.
        view()->composer('*', function ($view) {
            static $owner = null;
            static $wishlistIds = null;
            static $cartCount = null;

            if ($owner === null) {
                $owner = auth()->check()
                    ? CartOwner::forUser(auth()->user())
                    : CartOwner::forGuestToken(GuestCartToken::current(request()));
            }
            if ($wishlistIds === null) {
                $wishlistIds = $owner->isEmpty()
                    ? []
                    : $owner->scope(WishlistItem::query())->pluck('product_id')->toArray();
            }
            if ($cartCount === null) {
                $cartCount = $owner->isEmpty()
                    ? 0
                    : (int) $owner->scope(CartItem::query())->sum('quantity');
            }

            $view->with('wishlistIds', $wishlistIds);
            $view->with('cartCount', $cartCount);
        });
    }
}
