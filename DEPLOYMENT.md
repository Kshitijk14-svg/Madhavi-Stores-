# Deployment Guide — Madhavi Stores

How to ship changes from local → GitHub → live (Hostinger). Written for the
2026-06-21 SAAS-readiness release, but the workflow applies to any future deploy.

## Repo facts (why the steps below are what they are)
- **Remote:** GitHub `origin`, branch `main`.
- **`vendor/` is gitignored** → the live server must run `composer install` after pulling.
- **`composer.lock` is tracked** → deterministic installs. (This release added **no** new
  packages, so dependencies are unchanged.)
- **`public/build` is gitignored** → Vite assets are not in git. This release changed **no**
  CSS/JS (error pages use self-contained inline styles), so **no `npm run build` is needed**.
- **`.env` is gitignored** → the live `.env` is separate and is NOT overwritten by a pull.
  It is where you add new keys.

---

## 1. Before pushing (local machine)
```bash
php artisan test          # confirm green (currently 30 passing)
git add -A
git commit -m "SAAS readiness: checkout integrity, ACID, security, error handling, tests"
git push origin main
```
A feature branch + PR is best practice, but committing to `main` is fine if that is your flow.
`.env` is gitignored, so it will not be pushed — correct.

---

## 2. Prerequisites on live — BEFORE you pull (do not skip)
1. **Back up the database** (hPanel → Databases → Export, or `mysqldump`). The migration alters
   `cart_items` and `orders`. This backup is your safety net.
2. **Back up / note the current commit** (`git rev-parse HEAD`) so you can roll back.
3. **Maintenance mode:** `php artisan down`
4. **Update the live `.env`** (separate from git) with the new keys:
   - `RAZORPAY_WEBHOOK_SECRET=...`
   - `SESSION_DRIVER=database`
   - `SESSION_SECURE_COOKIE=true`
   - confirm `APP_ENV=production` and `APP_DEBUG=false`

---

## 3. On live — the deploy (requires SSH/terminal access)
```bash
git pull origin main
composer install --no-dev --optimize-autoloader   # registers new classes (CartService, etc.)
php artisan migrate --force                        # applies the new migration (REQUIRED)
php artisan config:clear && php artisan cache:clear
php artisan route:clear  && php artisan view:clear
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan up
```
- `--force` is required because `migrate` refuses to run interactively in production.
- No `npm run build` needed for this release (no asset changes). If a future release changes
  files under `resources/css` or `resources/js`, run `npm install && npm run build` and deploy
  the resulting `public/build` (it is gitignored, so build on the host or upload it).

> **No SSH on your hosting plan?** hPanel's Git tool can do the `git pull`, but you still need a
> terminal to run `migrate` + the cache commands (artisan can't be run from the Git UI). If you
> have no terminal at all, ask for a safe one-off approach before deploying — do not skip the
> migration.

---

## 4. After deploy
1. **Razorpay webhook** — Dashboard → Settings → Webhooks → add:
   - URL: `https://yourdomain.com/webhooks/razorpay`
   - Event: `payment.captured`
   - Secret: same value as `RAZORPAY_WEBHOOK_SECRET` in `.env`
2. **Scheduler cron** in hPanel (drives queued OTP/invoice emails — without it, mail never sends):
   ```
   * * * * * php /home/USERNAME/public_html/artisan schedule:run >> /dev/null 2>&1
   ```
3. **Smoke test** (top of `MANUAL-TEST-CHECKLIST.md`):
   - Product search returns results with no error.
   - A discounted product: PDP price == cart price == charged total.
   - Oversell: stock=1, two simultaneous checkouts → only one order, no negative stock.
   - Logged-out `/admin` → redirects to login.
   - A bad URL → branded 404 (with `APP_DEBUG=false`).
4. **Rotate the leaked secrets** when you can (Gmail App Password, Instagram token). ⚠️ Rotating
   `APP_KEY` logs everyone out and breaks existing encrypted data — only do it if the old key leaked.

---

## 5. Rollback (if something breaks)
```bash
php artisan down
git reset --hard <previous-commit>      # the HEAD you noted in step 2.2
composer install --no-dev --optimize-autoloader
# restore the database from the backup taken in step 2.1 (hPanel import / mysql < dump.sql)
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan up
```

---

## Honest caveats for this specific release
- The migration touches existing tables (`cart_items.size` → nullable; adds `orders.coupon_id`
  + indexes on `reviews.product_id`, `products.price`, `orders.coupon_code`). It is additive and
  safe, but **the DB backup (step 2.1) matters** — ideally run `migrate` against a copy of the
  live DB first.
- The migration was validated end-to-end on the SQLite test database (30 tests green) but could
  **not** be applied to MySQL during development (local MySQL was offline). Live will be its first
  real MySQL run — which is exactly why the backup is non-negotiable.

See also: `AUDIT-REPORT-2026-06-21.md`, `MANUAL-TEST-CHECKLIST.md`, `docs/DB-NOTES.md`.
