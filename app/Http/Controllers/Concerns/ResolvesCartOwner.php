<?php

namespace App\Http\Controllers\Concerns;

use App\Support\CartOwner;
use App\Support\GuestCartToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait ResolvesCartOwner
{
    /**
     * Resolve the current cart/wishlist owner: the authenticated user, or a
     * guest cookie token. Pass $creating = true only from an action that may
     * insert a NEW row (add to cart, toggle wishlist on) so a cookie isn't
     * minted for guests who are only viewing/updating/removing.
     */
    private function resolveOwner(Request $request, bool $creating = false): CartOwner
    {
        if (Auth::check()) {
            return CartOwner::forUser(Auth::user());
        }

        $token = $creating
            ? GuestCartToken::getOrCreate($request)
            : GuestCartToken::current($request);

        return CartOwner::forGuestToken($token);
    }
}
