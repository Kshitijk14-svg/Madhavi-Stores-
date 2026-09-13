# Instagram Token — Make It Never Expire

> **Status:** Implemented 2026-08-29. This document is kept as the design reference.
> The self-heal loop, `InstagramService::refreshToken()`/`ensureTokenFresh()`, the slimmed
> `instagram:refresh-token` command, `InstagramTokenAlertMail`, the admin Design Manager status
> block + paste-token field + "Refresh now" button (`admin.design.instagram.refresh`), and
> `tests/Feature/InstagramTokenTest.php` are all live. Config thresholds:
> `config/instagram.php` → `token_refresh_days` / `token_check_interval_hours` / `token_alert_days`.
> Alert emails go to `role IN (admin, superadmin)`. The Reels-render bug in §11 was also fixed.
> Written 2026-08-27. Everything below was verified against the actual code in this repo.

---

## 1. The Problem

The homepage Instagram Reels section is fed by `App\Services\InstagramService`, which calls
`graph.instagram.com/{version}/{account_id}/media` with a long-lived Instagram access token.

**Meta long-lived tokens expire 60 days after issue.** When that happens the API call fails,
`getPosts()` returns `[]`, and the view silently falls back to 6 hardcoded Unsplash placeholder
images. Nobody gets an error — the reels just quietly stop being real.

### Why it's broken right now

A refresh mechanism **already exists**:

- `app/Console/Commands/RefreshInstagramToken.php` — signature `instagram:refresh-token`
- `routes/console.php:21` — `Schedule::command('instagram:refresh-token')->weekly();`

But the hosting has **no reliable cron**, so `php artisan schedule:run` never fires and that
command never executes. (Note: `routes/console.php:23` also schedules `queue:work --once`
every minute — so queued emails are in the same boat.)

### Important reality check

There is **no truly permanent token** on the Instagram-Login API. The only way to make it
"never expire" is to keep refreshing it before the 60-day window closes. This guide builds a
**self-healing refresh loop driven by normal site traffic instead of cron**, which makes the
token *effectively* immortal.

*(The one genuinely non-expiring option is a Meta Business Manager **System User** token over
`graph.facebook.com` — requires the IG account to be Business and linked to a Facebook Page.
Deliberately out of scope; see §9.)*

---

## 2. How It Will Work

```
Someone loads the homepage
        │
        ▼
InstagramService::getPosts()
        │
        ├─► ensureTokenFresh()          ← cheap, runs first
        │      │
        │      ├─ token empty?                  → stop
        │      ├─ checked in last 6h?           → stop   (cache lock)
        │      ├─ token younger than 24h?       → stop   (Meta rejects these)
        │      ├─ more than 25 days left?       → stop
        │      └─ else: dispatch(...)->afterResponse()
        │                    │
        │                    ▼
        │            refreshToken()  ← runs AFTER the page is sent to the browser
        │                             so the visitor never waits on it
        │
        └─► Cache::remember('instagram_feed_posts', ...) → normal feed fetch
```

Refresh fires at **~25 days remaining** (i.e. roughly every 35 days), leaving a 25-day grace
buffer even if the site goes quiet for a while.

---

## 3. Current State of the Code (reference)

| Thing | Location |
|---|---|
| Feed fetch + token read/write | `app/Services/InstagramService.php` |
| Refresh command | `app/Console/Commands/RefreshInstagramToken.php` |
| Schedule entries | `routes/console.php:18-23` |
| Config + env names | `config/instagram.php` |
| Token storage | `settings` table, key `instagram_token` (plaintext) |
| Settings helper | `app/Models/Setting.php` (`get`/`set`, JSON-encodes arrays) |
| Homepage wiring | `app/Http/Controllers/HomeController.php:99-108` |
| Desktop reels view | `resources/views/pages/home.blade.php:257-310` |
| Mobile reels view | `resources/views/mobile/pages/home.blade.php:199-250` |
| Admin Instagram section (handle only) | `resources/views/admin/design/index.blade.php:189-213` |
| Admin save handler | `app/Http/Controllers/AdminController.php:1264-1281` |
| Admin design routes | `routes/web.php:134-135` |
| Setup docs | `INSTAGRAM-SETUP.md` |

**Env keys** (all currently **empty** in local `.env` lines 68-74 — production has the real values):

```
INSTAGRAM_ACCESS_TOKEN=
INSTAGRAM_BUSINESS_ACCOUNT_ID=
INSTAGRAM_APP_ID=
INSTAGRAM_APP_SECRET=
INSTAGRAM_GRAPH_VERSION=v21.0
INSTAGRAM_CACHE_TTL=3600
```

### Gaps being fixed

- No record of when the token was issued or when it expires
- No admin visibility — you find out it broke by noticing the reels are gone
- No way to paste a new token without editing `.env` or the DB by hand
- Refresh failures only `$this->error(...)` to a console nobody reads

---

## 4. Step 1 — Config

**`config/instagram.php`** — add three keys so the thresholds aren't magic numbers:

```php
// Refresh the token when this many days remain before expiry.
// Meta tokens last 60 days, so 25 leaves a generous grace buffer.
'token_refresh_days' => (int) env('INSTAGRAM_TOKEN_REFRESH_DAYS', 25),

// How often (hours) a page load is allowed to check whether a refresh is due.
'token_check_interval_hours' => (int) env('INSTAGRAM_TOKEN_CHECK_HOURS', 6),

// Email the admins if a refresh fails and expiry is this close (days).
'token_alert_days' => (int) env('INSTAGRAM_TOKEN_ALERT_DAYS', 14),
```

Optionally mirror these in `.env.example` and `.env.production.example` (both already carry the
other Instagram keys).

---

## 5. Step 2 — `InstagramService` (the core change)

**`app/Services/InstagramService.php`**

Add a second settings key next to the existing `instagram_token`:

```php
private const TOKEN_META_KEY = 'instagram_token_meta';
```

Meta shape: `['refreshed_at', 'expires_at', 'last_attempt_at', 'last_error', 'failure_count']`.
`Setting::set()` JSON-encodes arrays automatically (see `app/Models/Setting.php:25`), so no
migration is needed — the `settings` table already stores it fine.

### 5a. `getTokenMeta()`

```php
public function getTokenMeta(): array
{
    $meta = Setting::get(self::TOKEN_META_KEY);
    return is_array($meta) ? $meta : [];
}
```

### 5b. Extend `storeToken()` (currently line 53)

Keep the existing `Setting::set` + `forgetCache()` behaviour, and additionally record meta:

```php
public function storeToken(string $token, ?int $expiresIn = null): void
{
    Setting::set(self::TOKEN_SETTING_KEY, $token);

    Setting::set(self::TOKEN_META_KEY, [
        'refreshed_at'    => now()->toIso8601String(),
        'expires_at'      => now()->addSeconds($expiresIn ?: 60 * 86400)->toIso8601String(),
        'last_attempt_at' => now()->toIso8601String(),
        'last_error'      => null,
        'failure_count'   => 0,
    ]);

    $this->forgetCache();
}
```

The `?: 60 * 86400` default covers a token pasted by hand from the admin panel, where we don't
know the real `expires_in`.

### 5c. `refreshToken()` — single source of truth

Move the HTTP call out of the console command so the command, the admin button, and the
traffic-driven loop all share one implementation.

```php
public function refreshToken(): array
{
    $token = $this->getToken();

    if ($token === '') {
        return ['ok' => false, 'message' => 'No Instagram access token configured.'];
    }

    try {
        $response = Http::timeout(10)->get('https://graph.instagram.com/refresh_access_token', [
            'grant_type'   => 'ig_refresh_token',
            'access_token' => $token,
        ]);

        if ($response->failed()) {
            return $this->recordFailure(
                $response->json('error.message') ?? $response->body()
            );
        }

        $newToken = $response->json('access_token');
        if (! $newToken) {
            return $this->recordFailure('Refresh returned no access_token.');
        }

        $expiresIn = (int) $response->json('expires_in', 0);
        $this->storeToken($newToken, $expiresIn);

        return [
            'ok'      => true,
            'message' => 'Instagram token refreshed. Expires in ~'
                         . round(($expiresIn ?: 60 * 86400) / 86400) . ' days.',
        ];
    } catch (\Throwable $e) {
        return $this->recordFailure($e->getMessage());
    }
}
```

> **Critical:** on any failure the existing token is **never overwritten**. The current command
> already gets this right — preserve that behaviour.

### 5d. `recordFailure()` — private helper

```php
private function recordFailure(string $error): array
{
    $meta = $this->getTokenMeta();
    $meta['last_attempt_at'] = now()->toIso8601String();
    $meta['last_error']      = $error;
    $meta['failure_count']   = (int) ($meta['failure_count'] ?? 0) + 1;
    Setting::set(self::TOKEN_META_KEY, $meta);

    Log::warning('Instagram token refresh failed', ['error' => $error]);

    $this->maybeAlertAdmins($error, $meta);

    return ['ok' => false, 'message' => $error];
}
```

### 5e. `ensureTokenFresh()` — the lazy trigger

```php
public function ensureTokenFresh(): void
{
    if ($this->getToken() === '') {
        return;
    }

    // Throttle: only one request every N hours gets to do the check at all.
    $lockTtl = now()->addHours((int) config('instagram.token_check_interval_hours', 6));
    if (! Cache::add('instagram_token_check_lock', 1, $lockTtl)) {
        return;
    }

    $meta = $this->getTokenMeta();

    // Meta rejects refreshing a token that is less than 24 hours old.
    if (! empty($meta['refreshed_at'])
        && Carbon::parse($meta['refreshed_at'])->gt(now()->subDay())) {
        return;
    }

    // Plenty of life left — nothing to do.
    $refreshDays = (int) config('instagram.token_refresh_days', 25);
    if (! empty($meta['expires_at'])
        && now()->lt(Carbon::parse($meta['expires_at'])->subDays($refreshDays))) {
        return;
    }

    // Unknown expiry (legacy token) or inside the renewal window → refresh,
    // but only after the response has been flushed to the browser.
    dispatch(function () {
        app(self::class)->refreshToken();
    })->afterResponse();
}
```

`dispatch(...)->afterResponse()` runs the closure once the response is sent — **no queue worker
and no cron required**, which is the whole point.

### 5f. Hook it into `getPosts()` (line 27)

```php
public function getPosts(): array
{
    $this->ensureTokenFresh();          // ← add this line

    $ttl = max(60, (int) config('instagram.cache_ttl', 3600));

    return Cache::remember(self::CACHE_KEY, $ttl, function () {
        return $this->fetchPosts();
    });
}
```

Put it **before** `Cache::remember`, so it runs on every homepage view (the cache lock keeps it
cheap), not only on a cache miss.

Add `use Carbon\Carbon;` to the imports.

---

## 6. Step 3 — Slim the console command

**`app/Console/Commands/RefreshInstagramToken.php`** — reduce `handle()` to a wrapper:

```php
protected $signature = 'instagram:refresh-token {--force : Refresh even if not due yet}';

public function handle(InstagramService $instagram): int
{
    $result = $instagram->refreshToken();

    $result['ok'] ? $this->info($result['message']) : $this->error($result['message']);

    return $result['ok'] ? self::SUCCESS : self::FAILURE;
}
```

**Keep** `Schedule::command('instagram:refresh-token')->weekly();` in `routes/console.php:21`.
It's harmless without cron, and it automatically becomes the primary refresh path if cron is
ever configured — the traffic-driven loop then just acts as a safety net.

---

## 7. Step 4 — Failure alerting

New mailable `app/Mail/InstagramTokenAlertMail.php` + a simple blade view.

Triggered from `maybeAlertAdmins()` when expiry is unknown or within `token_alert_days`:

```php
private function maybeAlertAdmins(string $error, array $meta): void
{
    $alertDays = (int) config('instagram.token_alert_days', 14);
    $expiresAt = ! empty($meta['expires_at']) ? Carbon::parse($meta['expires_at']) : null;

    // Only shout when it actually matters.
    if ($expiresAt && now()->lt($expiresAt->copy()->subDays($alertDays))) {
        return;
    }

    // At most one alert per day.
    if (! Cache::add('instagram_token_alert_sent', 1, now()->addDay())) {
        return;
    }

    try {
        $admins = User::where('role', 'admin')->pluck('email')->filter()->all();
        if (! empty($admins)) {
            Mail::to($admins)->sendNow(new InstagramTokenAlertMail($error, $expiresAt));
        }
    } catch (\Throwable $e) {
        logger()->error('Failed to send Instagram token alert: ' . $e->getMessage());
    }
}
```

Two things that matter here:

- **`sendNow()`, not `queue()`** — `QUEUE_CONNECTION=database` with no cron means a queued mail
  would sit in the table forever. `AuthController.php:505` already uses `sendNow()` for OTP for
  the same reason.
- **Wrapped in try/catch** — mirrors `CheckoutController.php:433-437`, so a mail failure can
  never break a page render.

The admin-recipient lookup is the same pattern as `CheckoutController.php:425`.

---

## 8. Step 5 — Admin UI

### 8a. View — `resources/views/admin/design/index.blade.php:189-213`

Line 193 currently says *"token configured in `.env`"* — that copy is now wrong; replace it.

Extend the existing `type=instagram` form with:

1. **Status block**
   - Token present / absent
   - Days until expiry, colour-coded: green > 30, amber 8-30, red ≤ 7 or unknown
   - Last refreshed date
   - `last_error` text if present
2. **Paste-new-token field** — `<input type="password" name="instagram_access_token">`,
   blank means "leave unchanged"
3. **"Refresh now" button** — posts to the new route in §8c

### 8b. Controller — `app/Http/Controllers/AdminController.php`

**`designManager()`** (around line 1166, where `$instagram` is already built) — also pass:

```php
$igService   = app(InstagramService::class);
$igTokenMeta = $igService->getTokenMeta();
$igHasToken  = $igService->getToken() !== '';
```

**The `$type === 'instagram'` branch** (lines 1264-1281) — keep the handle logic exactly as is,
and add token handling:

```php
$request->validate([
    'instagram_handle'       => ['nullable', 'string', 'max:60', 'regex:/^[A-Za-z0-9_.]*$/'],
    'instagram_access_token' => ['nullable', 'string', 'max:500'],
]);

// ... existing handle save ...

$newToken = trim((string) $request->input('instagram_access_token'));
if ($newToken !== '') {
    app(InstagramService::class)->storeToken($newToken);
}
```

**New action:**

```php
public function refreshInstagramToken(InstagramService $instagram)
{
    $result = $instagram->refreshToken();

    return redirect()->route('admin.design.index')
        ->with($result['ok'] ? 'success' : 'error', $result['message']);
}
```

### 8c. Route — `routes/web.php`, beside lines 134-135

```php
Route::post('/design/instagram/refresh', [AdminController::class, 'refreshInstagramToken'])
    ->name('design.instagram.refresh')->middleware('desktop.only');
```

Same admin group, same `desktop.only` middleware as the neighbouring design routes.

---

## 9. Tests — `tests/Feature/InstagramTokenTest.php` (new)

There are currently **zero** tests touching Instagram. Using `Http::fake()`, matching the
existing style in `tests/Feature/`:

- [ ] Successful refresh persists the new token **and** meta, and busts `instagram_feed_posts`
- [ ] Failed refresh keeps the old token, records `last_error`, doesn't clear expiry
- [ ] `ensureTokenFresh()` does nothing when the token is < 24h old
- [ ] `ensureTokenFresh()` does nothing when expiry is > 25 days away
- [ ] `ensureTokenFresh()` schedules a refresh when expiry is unknown or near
- [ ] The cache lock prevents a second attempt inside the interval
- [ ] Admin refresh route requires admin auth and returns the result message

---

## 10. Verification

1. `php artisan test --filter=InstagramTokenTest` — all green
2. `php artisan test` — confirm the existing suite still passes (no homepage/design regressions)
3. `php artisan instagram:refresh-token` locally — with the empty `INSTAGRAM_ACCESS_TOKEN` it
   should report *"No Instagram access token configured"* cleanly, not error out
4. With real credentials set (`INSTAGRAM_ACCESS_TOKEN` + `INSTAGRAM_BUSINESS_ACCOUNT_ID`):
   - Load `/` → real reels render, not the Unsplash placeholders
   - Open `/admin/design` → status block shows a sane expiry (~60 days)
   - Click **Refresh now** → success message, "last refreshed" updates
5. **Simulate near-expiry:** set `instagram_token_meta`'s `expires_at` to 10 days out, delete
   the `instagram_token_check_lock` cache key, load `/`, then confirm from
   `storage/logs/laravel.log` and the admin status block that a refresh ran after the response
6. **Simulate failure:** fake a 400 from the refresh endpoint → confirm the old token survives,
   `last_error` shows in admin, and the alert email is attempted

---

## 11. Notes, Caveats, Assumptions

- **Effectively immortal, not literally permanent.** Refreshes roughly every 35 days with a
  25-day buffer. If the site got *zero* traffic for 60 straight days the token would still
  lapse — that's what the admin status block, the email alert, and the paste-token field are
  for. In practice a live store always has traffic.
- **Cron may actually exist.** `routes/console.php:23` schedules `queue:work --once` every
  minute, which suggests cron was at least intended on this host. If it *is* running, the weekly
  command becomes the primary path and this loop is pure belt-and-braces. Both are kept on
  purpose. **Worth confirming** with the hosting provider — if cron works, queued emails work
  too, which matters elsewhere in the app.
- **A truly non-expiring token** would mean a Meta Business Manager **System User** token over
  `graph.facebook.com` (needs IG Business + a linked Facebook Page). Out of scope here.
  `getToken()` and `refreshToken()` stay the only token touchpoints, so swapping later is a
  contained change.
- **The token stays plaintext** in `settings.value`. Encrypting it is a separate concern and
  would need a migration of the existing value — not addressed here.
- **Unrelated bug spotted while reading `fetchPosts()`:** only `media_type === 'VIDEO'` is
  treated as video. Instagram also returns `REELS` for some media, and `media_product_type`
  isn't requested at all — so actual Reels may render as static images. Worth a separate fix.
- `host.md:19-23` describes the older `instagram_basic` Graph Explorer flow, which is
  inconsistent with the Instagram-Login flow the code actually uses. `INSTAGRAM-SETUP.md` is the
  accurate one.
