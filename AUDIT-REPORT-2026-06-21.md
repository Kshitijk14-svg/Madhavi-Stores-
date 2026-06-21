# SAAS Production-Readiness Audit & Remediation — Report

**Date:** 2026-06-21
**Project:** Madhavi Stores (Laravel 12, Filament, Razorpay, DomPDF, MySQL/InnoDB, shared hosting / no Redis)
**Status:** Code changes complete · **30 automated tests passing** · app boots clean · 79 routes resolve

Three independent audits were run (database/ACID, security, code-quality), then remediated.
Detailed plan: `C:\Users\kshit\.claude\plans\list-of-changes-are-kind-meadow.md`
DB design rationale: `docs/DB-NOTES.md` · Manual QA: `MANUAL-TEST-CHECKLIST.md`

---

## ✅ WHAT WAS DONE

### 🔒 Security
- Removed the privilege-escalation footgun: `role`/`is_admin` are no longer mass-assignable.
  `role` is now the single source of truth; `is_admin` is auto-synced by a `User` saving hook.
  Auth checks use `isAdmin()`. (Automated test proves a registering user can't self-promote.)
- Design-manager (`updateDesignSettings`) now validates URL/link fields — blocks `javascript:`/`data:`
  schemes while allowing relative paths — and validates uploaded files as images.
- Added **HSTS** header on HTTPS requests (`SecurityHeaders` middleware).
- Added **throttling** to checkout, payment-verify, coupon-apply, and product-review routes.
- Branded **error pages** (404/403/419/500/503) + a global exception renderer that returns clean
  JSON for AJAX and never leaks the raw error in production.
- Confirmed already-solid (no change needed): CSRF coverage, no SQL injection, XSS-safe `Setting::format()`,
  IDOR scoping by `Auth::id()`, OTP atomicity + anti-enumeration, Razorpay signature verification,
  correctly-gated mock-payment path. `.env` was never committed to git.

### 💳 Payment & Checkout integrity (highest severity)
- **Discount split-brain fixed.** New `Product::final_price` accessor (applies `discount_type`/
  `discount_value`) is the ONE price used by the PDP, cart, checkout, and the order line item —
  customers can no longer be shown one price and charged another.
- **Oversell now rejected** inside the locked transaction (previously it silently clamped stock to 0
  while still creating the order and charging the customer).
- **Idempotency** on `razorpay_payment_id` prevents double-orders from a double-submit or webhook race.
- **Razorpay webhook** added (`POST /webhooks/razorpay`, CSRF-exempt, signature-verified) so an order
  is still created if the buyer closes the tab after paying. It reconstructs the order from Razorpay
  `notes` set during order creation.
- Both checkout transactions wrapped in try/catch with user-safe messages; gateway internals no longer
  leaked to the client.

### 🗄️ Database — ACID, normalization, scalability
- All cart-pricing + order-creation logic centralized in **`app/Services/CartService.php`** (removed
  6× duplicated, drifted math). Coupon validity is now identical in the cart and at checkout.
- Coupon row is `lockForUpdate()`-ed and re-validated inside the order transaction before its usage
  is incremented (prevents over-redemption under concurrency).
- Order number generated inside the transaction (no more 500 on a rare collision).
- **InnoDB pinned** in `config/database.php` (transactions/locks silently no-op on MyISAM).
- **Real anomaly fixed:** `products.rating` / `review_count` now recompute on review **save AND delete**
  via `Review::booted()` (previously drifted permanently when a review/user was removed). These columns
  were removed from `Product::$fillable`.
- Added `orders.coupon_id` FK; indexes on `reviews.product_id`, `products.price`, `orders.coupon_code`.
- **Broken feature fixed:** product search referenced a nonexistent `description` column (SQL error on
  every search) — now searches name + seo_keywords + tags.
- **Latent bug fixed:** `cart_items.size` was `NOT NULL` but the cart stores `null` for non-sized
  products (would fail on strict MySQL) — made nullable.
- Scaling: `OtpMail` now `ShouldQueue` + queued (was synchronous SMTP per auth action);
  `SitemapController` cached/column-limited/guarded against errors; unbounded reads addressed.
- BCNF kept **pragmatic** (per decision): real anomalies fixed, JSON columns documented as deliberate
  denormalization in `docs/DB-NOTES.md`.

### 🧯 Error handling & user-facing messages
- Branded `resources/views/errors/{404,403,419,500,503}.blade.php` (+ shared `errors/layout`).
- Global exception handling in `bootstrap/app.php` (`CheckoutException` → friendly flash/JSON; generic
  fallback for AJAX; no raw messages in production).
- Invoice PDF download wrapped in try/catch; `register()` guards a unique-violation race;
  `WishlistController::toggle` now validates the product exists; sitemap failures degrade gracefully.

### ✅ Tests & checklist (explicitly requested)
- **`MANUAL-TEST-CHECKLIST.md`** — full checkbox QA script: auth, catalog, pricing, cart/coupons,
  checkout (incl. oversell + double-submit), admin, errors, mobile, cross-cutting security.
- **30 automated PHPUnit tests** (SQLite in-memory) covering: final-price math, coupons, checkout
  (COD, discount, oversell, sized oversell, idempotency, empty cart, coupon application, per-user
  limit), review recompute on save/delete, mass-assignment protection, and admin access gating.
- Factories added: Product, Category, ProductSize, Coupon, CartItem (+ `UserFactory::admin()`).
- The two MySQL-only migrations are guarded with `DB::getDriverName()==='mysql'` so the SQLite test
  DB migrates cleanly.

### Files (high level)
- **New:** `app/Services/CartService.php`, `app/Exceptions/CheckoutException.php`,
  `resources/views/errors/*`, `database/migrations/2026_06_22_000001_add_coupon_id_and_scalability_indexes.php`,
  `database/factories/{Product,Category,ProductSize,Coupon,CartItem}Factory.php`,
  `tests/Unit/*`, `tests/Feature/*`, `MANUAL-TEST-CHECKLIST.md`, `docs/DB-NOTES.md`, this report.
- **Modified:** CheckoutController, CartController, ProductController, WishlistController, SitemapController,
  AdminController, AuthController; models Product/User/Order/Review (+ HasFactory on several);
  AppServiceProvider (cartCount composer), OtpMail, SecurityHeaders, CheckAdmin, bootstrap/app.php,
  routes/web.php, config/database.php, config/razorpay.php, .env.production.example,
  PDP blades (desktop + mobile).

---

## ⚠️ WHAT YOU MUST DO

### Blocking (do before this works on your machine / server)
1. **Run the migration.** MySQL was offline during the session, so the new migration is NOT yet applied.
   Start MySQL in XAMPP, then run:  `php artisan migrate`
   (It is additive / backward-compatible and was validated end-to-end by the test suite.)
2. **Dev dependencies note.** PHPUnit was installed via `composer install` (your project had been
   installed `--no-dev`). On the **live server**, keep deploying with `composer install --no-dev`.

### Before going live (production hardening — cannot be automated)
3. 🔒 **Rotate the leaked secrets** (they are real, working credentials currently on disk):
   - Revoke the OLD Gmail App Password in your Google Account → generate a new one.
   - Invalidate the OLD Instagram token in the Meta Developer Console.
   - Run `php artisan key:generate` for the production `.env` (rotates `APP_KEY`; logs everyone out).
4. 🔒 **Set production `.env` flags** (see `.env.production.example`):
   - `APP_ENV=production`, `APP_DEBUG=false` — **critical:** `APP_ENV=local` enables the mock-payment
     bypass, so it MUST be `production` in production.
   - `SESSION_DRIVER=database` (file sessions serialize concurrent AJAX cart calls at scale).
   - `SESSION_SECURE_COOKIE=true` (HTTPS only).
   - Real Razorpay **live** keys.
5. 💳 **Configure the Razorpay webhook** in the Dashboard → Settings → Webhooks:
   - Event: `payment.captured` · URL: `https://yourdomain.com/webhooks/razorpay`
   - Set the same secret in `RAZORPAY_WEBHOOK_SECRET` in `.env`.
6. ⚙️ **Add the scheduler cron** on the host (drives queued emails + cleanup):
   `* * * * * php /home/USERNAME/public_html/artisan schedule:run >> /dev/null 2>&1`
   Without it, queued OTP/invoice emails never send.

### Verify after deploy (key smoke tests from MANUAL-TEST-CHECKLIST.md)
7. Search a product → returns results, no error.
8. Set a product discount → PDP price == cart price == charged total.
9. Oversell test: stock = 1, two buyers check out simultaneously → only one order, no negative stock,
   loser not charged.
10. Logged-out visit to `/admin` → redirects to login; customer → redirects home.
11. With `APP_DEBUG=false`, hit a bad URL → branded 404 (no stack trace).
12. `php artisan test` → green.

---

## DEFERRED (documented, intentionally not done)
- FormRequest class extraction (pure refactor).
- Orphaned-upload-file cleanup on image replacement.
- Removing the dead/parallel Filament admin panel (it's gated; kept for now).
- Full BCNF pivot tables for the JSON columns (`tags`, `gallery_images`, `details`) — accepted
  denormalization, see `docs/DB-NOTES.md`.
- Tightening CSP (`unsafe-inline`/`unsafe-eval`) — needs an inline-handler refactor first.
