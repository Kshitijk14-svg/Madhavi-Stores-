# Fix: "Proceed to Checkout" still asks guests to sign in

## Context

Guest checkout (cookie-token + email identity) was built last session and is committed at HEAD (`6094e85` "guest checkout"). The user reports that clicking **Proceed to Checkout** from the cart still lands on the sign-in page. Per user decision: keep the existing **cookie + email** guest identity (no IP-based identity).

**Key finding from code exploration: the committed source has no sign-in gate anywhere on the cart → checkout path.**
- `routes/web.php:71-73` — `/checkout` GET/POST and `/checkout/verify` are in the guests-allowed section, outside the `auth` middleware group (commit `6094e85` moved them out).
- `app/Http/Controllers/CheckoutController.php` — `index()`/`store()` use `resolveOwner()` (`app/Http/Controllers/Concerns/ResolvesCartOwner.php`), which falls back to `CartOwner::forGuestToken()` for guests. The only redirect is empty-cart → `/cart`, never `/login`.
- `resources/views/pages/cart.blade.php:100-102` and `resources/views/mobile/pages/cart.blade.php:94-95` — Proceed button is a plain `<a href="{{ route('checkout') }}">`, no JS auth check.
- `resources/views/pages/checkout.blade.php:21-26` — explicitly renders a guest form ("continue as a guest below").
- No route cache (`bootstrap/cache/` has only packages.php/services.php); working tree clean.

So the symptom is environmental (stale compiled views/config/session/browser cache, or the server not restarted since the commit) **or** an edge not visible from static reading (e.g. the PJAX link interceptor at `resources/views/layouts/app.blade.php:167-183` swapping in unexpected redirect content, or the `guest_cart_token` cookie not being persisted so the guest cart looks empty). The fix must be driven by live reproduction, not more code guessing.

## Plan

### 1. Clean environment and start the app
- `php artisan view:clear && php artisan route:clear && php artisan config:clear && php artisan cache:clear`
  (63 compiled views in `storage/framework/views` predate today's commit; clear them so nothing stale can render.)
- Start the app: `php artisan serve` (or confirm XAMPP Apache is serving it), verify `http://localhost:8000` (or the Apache URL) responds.

### 2. Reproduce the guest flow with Playwright (fresh browser = true guest)
1. Open the site with a clean profile (no cookies → real guest).
2. Add a product to the bag; confirm the `guest_cart_token` cookie is set and the cart count updates.
3. Navigate to `/cart`; confirm the item is listed.
4. Click **Proceed to Checkout**.
   - **Expected:** checkout form renders with the "continue as a guest below" note.
   - **If login page appears:** capture the network trace (`browser_network_requests`) to see exactly which response redirected to `/login`, then fix that actual gate.
5. Repeat at a mobile viewport (~390px) — mobile uses separate views (`resources/views/mobile/...`).
6. Complete a guest order through the checkout POST (Razorpay test path or at least confirm the POST validates and reaches order creation with `user_id = NULL` + email).

### 3. Fix whatever the reproduction reveals
Likely candidates, in order of probability:
- Stale compiled Blade views / config cache → step 1 already fixes; confirm via re-test.
- `guest_cart_token` cookie dropped (e.g. `Secure`/`SameSite` flags on plain-HTTP localhost) → adjust cookie options in `app/Support/GuestCartToken.php`.
- PJAX interceptor mishandling the redirect chain → add `/checkout` handling or `no-pjax` class if it's the culprit.
- If nothing reproduces: the user's browser had a cached page/session — have them re-test in an incognito window; no code change needed.

### 4. Verification
- Playwright end-to-end as a guest: add to bag → cart → Proceed to Checkout → guest form visible → submit reaches payment step. Desktop and mobile viewports.
- Sanity check logged-in checkout still works (saved addresses render, coupons available).
- Run the existing PHPUnit suite (`php artisan test`) — 30 tests exist from the SAAS audit; they must stay green.

## Out of scope (per user decision)
- No IP-based guest identity. Existing cookie-token + email design stays as-is.
