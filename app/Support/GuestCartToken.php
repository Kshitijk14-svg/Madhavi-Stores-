<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

/**
 * Identifies an anonymous visitor's cart/wishlist across requests via a
 * dedicated first-party cookie — not the Laravel session ID, since the
 * session is regenerated on login/logout (AuthController) and expires
 * after 120 minutes (config/session.php), which would silently drop a
 * guest's cart before they ever got a chance to check out.
 */
class GuestCartToken
{
    private const COOKIE = 'guest_cart_token';
    private const MINUTES = 60 * 24 * 365; // ~1 year

    /** Read the existing guest token, if any. Never creates one. */
    public static function current(Request $request): ?string
    {
        return $request->cookie(self::COOKIE);
    }

    /** Read the existing guest token, or mint + queue a new one. */
    public static function getOrCreate(Request $request): string
    {
        $token = self::current($request);
        if ($token) {
            return $token;
        }

        $token = Str::random(48);
        Cookie::queue(self::COOKIE, $token, self::MINUTES, null, null, null, true, false, 'lax');

        return $token;
    }

    /** Forget the guest cookie — called once its cart has been merged into a user. */
    public static function forget(): void
    {
        Cookie::queue(Cookie::forget(self::COOKIE));
    }
}
