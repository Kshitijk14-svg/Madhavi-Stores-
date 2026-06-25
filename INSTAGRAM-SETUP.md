# Instagram Feed Setup (Instagram API with Instagram Login)

The homepage "Follow Our Journey" section pulls the store's latest Instagram posts
**live** from the Instagram API (`graph.instagram.com`). Until the two credentials below
are filled in, the section automatically falls back to placeholder images — so the site
never breaks.

> **Note:** This uses Meta's **newer "Instagram API with Instagram Login"** (host
> `graph.instagram.com`, permission `instagram_business_basic`), *not* the older
> Facebook-Login Graph API (`graph.facebook.com`). The Instagram-Login API does **not**
> require linking a Facebook Page.

This is a **one-time setup** that produces two values for `.env`:

- `INSTAGRAM_ACCESS_TOKEN` — a long-lived access token (~60 days; auto-refreshed weekly)
- `INSTAGRAM_BUSINESS_ACCOUNT_ID` — the Instagram **user ID** (a number, not the @handle)

---

## Prerequisites

1. **Instagram → Business/Creator account.** In the Instagram app: *Settings → Account type
   and tools → Switch to professional account* → choose **Business** (or Creator).

---

## Step-by-step

### 1. Create a Meta Developer App
- Go to https://developers.facebook.com → **My Apps → Create App**.
- At creation, pick the use case **"Manage messaging and content on Instagram"** (this
  pre-adds the Instagram product and Instagram Login).
- Note your **App ID** and **App Secret** (Settings → Basic).

### 2. Add your Instagram account as a Tester
- App dashboard → **Roles** tab → **Instagram Testers** → add your Instagram account.
- Accept the invite from the Instagram app: *Settings → Apps and websites → Tester invites*.

### 3. Generate the access token
- App dashboard → **Use cases → Customize "Manage messaging and content on Instagram"**.
- Under **"Generate access tokens"**, connect your Instagram account → **Generate token**.
- The token returned is already **long-lived** (~60 days) → goes into `INSTAGRAM_ACCESS_TOKEN`.
- The **Instagram user ID** shown next to the account → goes into `INSTAGRAM_BUSINESS_ACCOUNT_ID`.

### 4. Put the values in `.env`

```
INSTAGRAM_ACCESS_TOKEN=<long-lived token from step 3>
INSTAGRAM_BUSINESS_ACCOUNT_ID=<id from step 4>
INSTAGRAM_APP_ID=<App ID from Settings → Basic>
INSTAGRAM_APP_SECRET=<App Secret from Settings → Basic>
INSTAGRAM_GRAPH_VERSION=v21.0
INSTAGRAM_CACHE_TTL=3600
```

> `INSTAGRAM_APP_ID` / `INSTAGRAM_APP_SECRET` are **not required at runtime** for the
> Instagram-Login flow (the weekly refresh uses only the token). Keep them for reference /
> manual token exchanges, and keep the secret out of version control.

Then clear caches so the new config is picked up:

```
php artisan config:clear
php artisan cache:clear
```

Reload the homepage — the grid should now show your 6 latest posts.

---

## How it stays working

- **Caching:** posts are cached for `INSTAGRAM_CACHE_TTL` seconds (default 1 hour), so the
  homepage does not call the API on every request. Cache key: `instagram_feed_posts`.
- **Token auto-refresh:** Instagram long-lived tokens expire in ~60 days. The scheduled command
  `instagram:refresh-token` (runs **weekly** via `routes/console.php`) re-presents the current
  token via `grant_type=ig_refresh_token` to `graph.instagram.com/refresh_access_token` (token
  only — no app id/secret needed), which resets the 60-day window, and stores the new token in
  the `settings` table — so it never needs manual rotation as long as the Laravel scheduler cron
  is running. Note: a token must be at least 24 hours old before it can be refreshed. To test it
  manually:

  ```
  php artisan instagram:refresh-token
  ```

- **Admin:** the @handle shown in the section (eyebrow + "Follow" button) is editable at
  **/admin/design** (Homepage Layout tab → Instagram Feed). The section can be hidden/reordered
  from the same page's layout list. The **token is never exposed in the admin** — it stays in `.env`.

## Troubleshooting

- **Section shows placeholder images** → token/account ID missing or invalid; check `storage/logs/laravel.log`
  for an `Instagram feed fetch failed` warning, and confirm you ran `config:clear`.
- **Token expired** → run `php artisan instagram:refresh-token`. If it fails because the token is
  fully expired (>60 days unused), repeat steps 2–3 to mint a fresh one.
- **Empty grid even with valid token** → the account may have no posts, or `instagram_business_basic`
  was not granted when the token was generated. Re-generate the token from the use-case setup page.
