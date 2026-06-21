# Madhavi Stores — Manual QA Checklist

Run through this before every release. Test on **both desktop and a real mobile
device** (the app serves different views by User-Agent). Mark each box `[x]`.

Legend: 🔒 = security check · 💳 = payment/money · 📱 = mobile-specific.

> Tip: set `APP_DEBUG=false` in `.env` while testing the **Errors** section so you
> see the real branded pages, not the developer stack trace.

---

## 0. Pre-flight (do once per environment)
- [ ] `php artisan migrate` runs with no errors.
- [ ] `php artisan test` is green.
- [ ] A queue worker is running (or the `schedule:run` cron is installed) — otherwise
      OTP/invoice emails never send.
- [ ] 🔒 `APP_ENV=production`, `APP_DEBUG=false` on the live server.
- [ ] 🔒 Old Gmail app password revoked; old Instagram token invalidated; `APP_KEY` rotated.
- [ ] 🔒 `SESSION_SECURE_COOKIE=true` and the site loads over HTTPS only.

---

## 1. Authentication & Account
- [ ] Register a new account → redirected to the OTP verify page.
- [ ] OTP email arrives; entering it logs you in / verifies the email.
- [ ] Enter a **wrong** OTP → clear error, not logged in.
- [ ] Resend OTP works; hammering "Resend" quickly gets throttled (🔒 rate limit).
- [ ] Register with an email that already exists → friendly "already exists" message (not a 500).
- [ ] Log in with correct password (tick "remember me", close browser, reopen → still logged in).
- [ ] Log in with wrong password → "credentials do not match" error.
- [ ] 🔒 Hammer the login form with wrong passwords → throttled after ~10 tries (429).
- [ ] Forgot password → OTP → set new password → log in with the new password.
- [ ] Forgot password for an email that does NOT exist → same neutral message (🔒 no account enumeration).
- [ ] Update profile (name) on the account page → saved.
- [ ] Log out → session ends, protected pages redirect to login.
- [ ] 🔒 Register while sending `role=superadmin` / `is_admin=1` in the form (dev tools) →
      the new account is still a normal customer (covered by an automated test too).

## 2. Catalog & Browsing
- [ ] Home page renders all sections (hero, categories, new arrivals, banners, bestsellers, newsletter).
- [ ] "View All" links go to the right filtered shop pages.
- [ ] Shop: filter by category, by price range, by tags → results correct.
- [ ] **Search** a product name → returns matching results with NO error (this was previously broken).
- [ ] Search a nonsense string → graceful "no products found".
- [ ] Sort by newest / price low→high / price high→low.
- [ ] Pagination works and keeps filters applied.
- [ ] Product detail: image gallery, sizes, description, details, size chart, reviews all show.
- [ ] Collections page lists categories; About page renders.

## 3. Pricing & Discounts 💳
- [ ] Set a product `discount_type=percent, discount_value=10` in admin.
- [ ] PDP shows the discounted price.
- [ ] 💳 Add it to the cart → cart line price **matches the PDP discounted price**.
- [ ] 💳 Checkout total uses the discounted price; the created order's item price matches.
- [ ] Repeat with a **fixed** discount; and with a discount larger than the price (price floors at ₹0).

## 4. Cart & Coupons
- [ ] Add a sized product (pick a size) and a non-sized product → both land in the cart.
- [ ] Increase / decrease quantity; decreasing to 0 removes the line.
- [ ] Try to add/increase beyond available stock → blocked with "only N available".
- [ ] Remove an item → totals update.
- [ ] Apply a valid coupon → discount shows; remove it → discount gone.
- [ ] Apply a coupon below its minimum cart value → clear "minimum spend" message.
- [ ] Apply an expired / inactive coupon → rejected.
- [ ] 🔒 Spam "apply coupon" with random codes quickly → throttled.
- [ ] Apply a coupon you've already used up to its per-user limit → rejected **in the cart**
      (it must not be accepted in the cart but then fail at checkout — they now agree).

## 5. Checkout & Orders 💳
- [ ] COD order: place it → success message with order number; cart clears.
- [ ] Order appears in your account order history.
- [ ] Product stock decreased by the ordered quantity.
- [ ] 💳 Razorpay (Card/UPI) with **live** keys: complete a real payment → order created & marked Paid.
- [ ] 💳 Simulate a failed/cancelled payment → no order created, clear message, no stock change.
- [ ] 💳 **Oversell test:** set a product stock to 1. In two browsers/accounts, add it and
      check out at nearly the same time → only ONE order succeeds; the other sees
      "just went out of stock"; stock never goes negative; the loser is not charged.
- [ ] 💳 Double-click the final "Pay/Place order" button → only ONE order is created (idempotency).
- [ ] Invoice email to the customer sends (check inbox / queue).
- [ ] Coupon's `used_count` increments by exactly 1 after a successful order using it.

## 6. Admin Panel 🔒
- [ ] 🔒 While logged OUT, visit `/admin` → redirected to **login**.
- [ ] 🔒 While logged in as a normal customer, visit `/admin` → redirected to **home** with an error.
- [ ] Log in as admin → dashboard loads with correct stats.
- [ ] Products: create (with image upload), edit, delete. Uploaded image shows as WebP.
- [ ] Categories: create / edit / delete (deleting a category that has products is blocked).
- [ ] Coupons: create / edit / toggle / delete.
- [ ] Orders: change status; download the PDF invoice; email the invoice.
- [ ] 🔒 Force an invoice failure (e.g. a broken image in the order) → friendly error, not a raw 500.
- [ ] Users: change a user's role. 🔒 A non-superadmin cannot create or modify a superadmin.
- [ ] Design manager: edit hero slides, banners, promo, newsletter, about → saved and reflected on site.
- [ ] 🔒 In the design manager, put `javascript:alert(1)` in a link/image URL field → rejected by validation.

## 7. Error Handling
- [ ] Visit a non-existent URL → branded **404** page (with `APP_DEBUG=false`).
- [ ] Trigger a server error path → branded **500** page, no stack trace leaked.
- [ ] Let a form sit until the CSRF token expires, then submit → **419** page.
- [ ] An AJAX action that fails (e.g. add-to-cart while offline) → a toast message, not a silent failure.

## 8. Mobile 📱
- [ ] 📱 Hamburger opens the side menu; it sits ABOVE all content and blurs the background.
- [ ] 📱 The hamburger turns into an ✕ and closes the menu (also closes on backdrop tap & after navigating).
- [ ] 📱 Each page (home, shop, product, cart, checkout, wishlist, account, collections, about) has
      no horizontal scroll and readable text.
- [ ] 📱 Tap targets (buttons, add-to-cart, filters) are easy to hit.
- [ ] 📱 Cart badge count updates after adding an item.
- [ ] 📱 Shop Sort and Filter open as separate bottom sheets.

## 9. Cross-cutting Security 🔒
- [ ] 🔒 Logged in as user A, try to modify user B's cart item id (change the id in the request) → rejected.
- [ ] 🔒 Try to open another user's order/invoice → not possible (no public order route).
- [ ] 🔒 All forms still work (CSRF tokens present); the Razorpay webhook is the only CSRF-exempt POST.
- [ ] 🔒 Site is HTTPS-only; the session cookie is `Secure` + `HttpOnly` (check dev tools → Application → Cookies).

---

### Notes for the tester
- The **oversell** and **double-submit** checks are the most important money-safety tests — do them every release.
- If a queued email doesn't arrive, confirm the queue worker / scheduler cron is actually running.
- Known accepted designs (not bugs): see `docs/DB-NOTES.md`.
