# Madhavi Stores — Mobile, Security, DB & Scalability Overhaul

## Context
The storefront has a hybrid mobile strategy: a `MobileViewMiddleware` transparently serves files from `resources/views/mobile/` to mobile User-Agents. Only the home page has a dedicated mobile view (`mobile/pages/home.blade.php`); all other pages fall back to the desktop Blade files which use Tailwind responsive utilities but have not been audited or optimized for small screens. Additionally, a duplicate index migration will crash any fresh install, a stock-decrement race condition exists at checkout under concurrent load, and the admin panel has zero mobile responsiveness.

---

## Phase 1 — Database Integrity & ACID Fixes

### 1.1 Fix Duplicate Index Migration (Critical Bug)
`2026_06_16_061933` and `2026_06_20_000002` both add the same four indexes on `orders` (`user_id`, `order_status`, `payment_status`, `created_at`) and `expires_at` on `otp_codes`. A fresh `php artisan migrate` will fail.

**Fix:** Edit `database/migrations/2026_06_20_000002_add_missing_indexes_for_scalability.php` — remove the `orders` and `otp_codes` index definitions that already exist in the earlier migration, keeping only any genuinely new indexes it defines (if any, else delete the file and remove it from git).

### 1.2 Fix Checkout Race Condition (ACID — Isolation)
In `app/Http/Controllers/CheckoutController.php`, the checkout transaction reads `ProductSize::where(...)` or `Product::find()` stock without acquiring a row lock. Two concurrent requests can both read stock=1 and both decrement it to 0 or below.

**Fix:** Add `->lockForUpdate()` to all stock-read queries inside the `DB::transaction()` block in `store()` and `verifyPayment()`.

### 1.3 Fix N+1 Query on Product Detail Page
`ProductController::show()` calls `Product::with(['sizes', 'reviews.user'])` but NOT `with('category')`. The view (`pages/product-show.blade.php`) accesses `$product->category->size_chart_image` multiple times — triggering an extra lazy query on every product page load.

**Fix:** Add `'category'` to the `with()` array in `ProductController::show()`.

### 1.4 Add Missing `product_id` Index on `order_items`
No explicit index on `order_items.product_id` (the FK column). At 1k users this will cause slow joins when loading order history.

**Fix:** Create a new migration `add_product_id_index_to_order_items.php` adding `$table->index('product_id')` on `order_items`. Also add `FULLTEXT` index on `products.name` for search performance.

### 1.5 Fix Categories Listing in Admin
`AdminController::categories()` uses `$query->get()` (loads all into memory). Acceptable now, but add `->paginate(20)` for future scalability.

---

## Phase 2 — Security Hardening

### 2.1 Fix CheckAdmin Middleware
`app/Http/Middleware/CheckAdmin.php` redirects all unauthorized users (including unauthenticated) to `/home`. Unauthenticated users should be redirected to `/login` instead.

**Fix:** Add an `if (!auth()->check()) return redirect()->route('login');` guard at the top of the `handle()` method before the admin check.

### 2.2 Add @csrf to Newsletter Forms
Two forms use `action="#"` (placeholder) with no `@csrf`:
- `resources/views/components/footer.blade.php` line ~47
- `resources/views/pages/home.blade.php` line ~267

**Fix:** Add `@csrf` to both so they're wired correctly when a real endpoint is added.

### 2.3 Session Cookie Security
`SESSION_ENCRYPT=false` and no `SESSION_SECURE_COOKIE` in production config.

**Fix:** In `config/session.php`, set `'secure' => env('SESSION_SECURE_COOKIE', false)` and document in `.env.production.example` that `SESSION_SECURE_COOKIE=true` is required in production.

### 2.4 Hardcoded Razorpay Test Key
`CheckoutController.php` compares against the literal string `'rzp_test_dummykey123'`.

**Fix:** Replace with `app()->environment('local')` or `config('app.env') === 'local'` check.

---

## Phase 3 — Mobile Views (8 Pages)

`MobileViewMiddleware` already prepends `resources/views/mobile` to the view finder. Creating a file at `mobile/pages/{name}.blade.php` automatically serves it to mobile users — no routing change needed.

All 8 views should extend `mobile.layouts.app` and follow the pattern of `mobile/pages/home.blade.php`:
- No sidebars, no multi-column desktop grids
- Bottom-sheet or accordion filters (shop)
- Full-width stacked layout
- Horizontal scroll carousels where appropriate
- `pb-24` bottom padding on all content to clear the mobile bottom nav bar
- Touch-friendly tap targets (min 44px height)

### Files to create:

| File | Key Mobile Patterns |
|---|---|
| `mobile/pages/shop.blade.php` | Sticky top filter bar (horizontal scroll chips), 2-col product grid, no sidebar, pull-up filter drawer via JS |
| `mobile/pages/product-show.blade.php` | Full-width image gallery (Swiper scroll), sticky bottom CTA bar (size select + add to cart), size chart modal, reviews accordion |
| `mobile/pages/cart.blade.php` | Stacked item list, inline qty controls, coupon field, sticky checkout button at bottom |
| `mobile/pages/checkout.blade.php` | Single-column form, summary collapsed accordion at top, sticky pay button |
| `mobile/pages/wishlist.blade.php` | 2-col product grid, move-to-cart button per card |
| `mobile/pages/account.blade.php` | Tab bar for Profile / Orders / Wishlist (horizontal scroll tabs), stacked order cards |
| `mobile/pages/collections.blade.php` | 2-col category cards, large touch targets |
| `mobile/pages/about.blade.php` | Stacked full-width sections, no two-column prose layout |

Each view passes the same `$variables` already provided by existing controllers — no controller changes needed.

---

## Phase 4 — Different Mobile Banner

### 4.1 Mobile Hero Banner Image
Currently `mobile/pages/home.blade.php` uses the same `image_url` from `hero_slides` settings but at 70vh height. The user wants a distinct mobile banner image.

**Plan:**
1. Add a `mobile_image_url` field to each slide's JSON structure in the `settings` table (hero_slides key).
2. Update `resources/views/admin/design/index.blade.php` — add a "Mobile Image (optional)" upload field per slide in the hero section.
3. Update `AdminController::updateDesignSettings()` to handle `mobile_image_url` upload per slide (reuse the existing `convertToWebp()` helper).
4. In `mobile/pages/home.blade.php`, use `$slide['mobile_image_url'] ?? $slide['image_url']` — falls back to desktop image if no mobile image is set (backward compatible).

### 4.2 Mobile Dual Banners
The `mobile/pages/home.blade.php` already stacks dual banners vertically. No separate image is needed unless different crops are required — defer to Phase 4.1 pattern if needed.

---

## Phase 5 — Pagination & Indexing for 1k Concurrent Users

### 5.1 Indexes Already in Place (from existing migrations)
- `products`: `slug` (unique), `category_id`, `is_bestseller`, `is_new_arrival`
- `orders`: `user_id`, `order_status`, `payment_status`, `created_at`
- `cart_items`: `user_id`, composite `(user_id, product_id, size)` unique
- `wishlist_items`: `user_id`, composite `(user_id, product_id)` unique
- `otp_codes`: `email`, `expires_at`

### 5.2 Gaps to Address
- `order_items.product_id` — add index (Phase 1.4)
- `products.name` — add FULLTEXT index for search (Phase 1.4)
- After Phase 1.1 fix, all critical indexes will be in place

### 5.3 Pagination
All shop/admin listings already use `->paginate()`. Fix the one omission:
- `AuthController::account()` orders pagination: add `->withQueryString()` to preserve query params on page links.

### 5.4 OTP Mail Queuing
Verify `OtpMail` is dispatched via `Mail::to()->queue()` not `send()` — synchronous mail on every login will bottleneck at 1k users. If using `send()`, change to `queue()` in `AuthController::sendOtp()`.

---

## Phase 6 — Admin Panel Mobile Responsiveness

`resources/views/admin/layout.blade.php` wraps all admin pages. The horizontal tab nav overflows on small screens.

**Fix:** Add `overflow-x-auto` and `flex-nowrap` to the admin tab nav, and ensure the admin content area (`admin-content`) has appropriate `px-4` padding on mobile. The admin panel does not need a full redesign — basic scroll + readable layout is sufficient.

---

## Files Summary

### Create (new mobile views)
- `resources/views/mobile/pages/shop.blade.php`
- `resources/views/mobile/pages/product-show.blade.php`
- `resources/views/mobile/pages/cart.blade.php`
- `resources/views/mobile/pages/checkout.blade.php`
- `resources/views/mobile/pages/wishlist.blade.php`
- `resources/views/mobile/pages/account.blade.php`
- `resources/views/mobile/pages/collections.blade.php`
- `resources/views/mobile/pages/about.blade.php`
- `database/migrations/[timestamp]_add_order_items_and_fulltext_indexes.php`

### Modify (bug fixes + security)
- `database/migrations/2026_06_20_000002_add_missing_indexes_for_scalability.php` — remove duplicate indexes
- `app/Http/Controllers/ProductController.php` — add `'category'` to with() in show()
- `app/Http/Controllers/CheckoutController.php` — add lockForUpdate(), fix hardcoded test key
- `app/Http/Controllers/AuthController.php` — add withQueryString(), verify OTP mail is queued
- `app/Http/Controllers/AdminController.php` — paginate categories, handle mobile_image_url
- `app/Http/Middleware/CheckAdmin.php` — redirect unauthenticated to /login
- `resources/views/components/footer.blade.php` — add @csrf
- `resources/views/pages/home.blade.php` — add @csrf
- `resources/views/mobile/pages/home.blade.php` — use mobile_image_url fallback
- `resources/views/admin/design/index.blade.php` — add mobile image upload per slide
- `resources/views/admin/layout.blade.php` — make nav scrollable on mobile
- `config/session.php` — document secure cookie env var

---

## Verification Plan

1. **DB integrity**: Run `php artisan migrate:fresh --seed` — should complete with no errors (validates duplicate index fix).
2. **ACID race condition**: Open two browser tabs as different users, both attempt to checkout the last unit of a product simultaneously — only one should succeed.
3. **Mobile views**: Open DevTools → Toggle device toolbar → navigate all 8 pages — no horizontal scroll, readable fonts, working tap targets.
4. **Mobile banner**: Upload a different mobile image in Design Manager → verify mobile view shows the different image.
5. **Security**: Attempt to access `/admin/products` while logged out — should redirect to `/login` not home.
6. **Performance**: Use Laravel Debugbar or `DB::listen()` log to confirm product detail page fires no extra queries for category.
