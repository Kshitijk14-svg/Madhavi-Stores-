<?php

namespace App\Services;

use App\Mail\InstagramTokenAlertMail;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Fetches the store's latest Instagram posts via the Meta Graph API and caches
 * them so the homepage does not call the API on every request.
 *
 * Resilient by design: if no token/account is configured, or the API call
 * fails/times out, getPosts() returns an empty array and the view falls back to
 * its placeholder grid. This service never throws.
 *
 * Token lifecycle: Meta long-lived tokens expire 60 days after issue. The host
 * has no reliable cron, so instead of relying only on the weekly
 * `instagram:refresh-token` command, ensureTokenFresh() runs on every homepage
 * view and — when the token is inside its renewal window — refreshes it *after
 * the response is flushed* (dispatch()->afterResponse()). No queue worker or
 * cron required.
 */
class InstagramService
{
    private const CACHE_KEY = 'instagram_feed_posts';
    private const TOKEN_SETTING_KEY = 'instagram_token';
    private const TOKEN_META_KEY = 'instagram_token_meta';

    // Only one page load every N hours gets to run the freshness check.
    private const CHECK_LOCK_KEY = 'instagram_token_check_lock';
    // At most one failure alert email per day.
    private const ALERT_LOCK_KEY = 'instagram_token_alert_sent';

    // Assumed lifetime (seconds) when Meta does not tell us expires_in — e.g. a
    // token pasted by hand in the admin panel.
    private const DEFAULT_LIFETIME = 60 * 86400;

    /**
     * Return up to N normalized posts:
     * [ ['image' => url, 'video_url' => url|null, 'media_type' => string, 'permalink' => url, 'caption' => string], ... ]
     */
    public function getPosts(): array
    {
        $this->ensureTokenFresh();

        $ttl = max(60, (int) config('instagram.cache_ttl', 3600));

        return Cache::remember(self::CACHE_KEY, $ttl, function () {
            return $this->fetchPosts();
        });
    }

    /**
     * The live access token: prefer the one persisted by the refresh command
     * (settings table), falling back to the .env-seeded config value.
     */
    public function getToken(): string
    {
        $dbToken = Setting::get(self::TOKEN_SETTING_KEY);
        if (is_string($dbToken) && $dbToken !== '') {
            return $dbToken;
        }

        return (string) config('instagram.access_token', '');
    }

    /**
     * Token lifecycle metadata:
     * ['refreshed_at', 'expires_at', 'last_attempt_at', 'last_error', 'failure_count']
     */
    public function getTokenMeta(): array
    {
        $meta = Setting::get(self::TOKEN_META_KEY);

        return is_array($meta) ? $meta : [];
    }

    /**
     * Persist a refreshed token, record its lifecycle metadata, and bust the
     * feed cache. $expiresIn is Meta's `expires_in` (seconds); when unknown we
     * assume the standard 60-day window.
     */
    public function storeToken(string $token, ?int $expiresIn = null): void
    {
        Setting::set(self::TOKEN_SETTING_KEY, $token);

        $lifetime = $expiresIn && $expiresIn > 0 ? $expiresIn : self::DEFAULT_LIFETIME;

        Setting::set(self::TOKEN_META_KEY, [
            'refreshed_at'    => now()->toIso8601String(),
            'expires_at'      => now()->addSeconds($lifetime)->toIso8601String(),
            'last_attempt_at' => now()->toIso8601String(),
            'last_error'      => null,
            'failure_count'   => 0,
        ]);

        $this->forgetCache();
    }

    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Extend the long-lived token via grant_type=ig_refresh_token. Single source
     * of truth shared by the console command, the traffic-driven loop, and the
     * admin "Refresh now" button.
     *
     * On ANY failure the existing token is left untouched.
     *
     * @return array{ok: bool, message: string}
     */
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
                    . round(($expiresIn ?: self::DEFAULT_LIFETIME) / 86400) . ' days.',
            ];
        } catch (\Throwable $e) {
            return $this->recordFailure($e->getMessage());
        }
    }

    /**
     * Cheap check run on every homepage view. Refreshes the token (after the
     * response is sent) when it is inside its renewal window.
     *
     * @return bool whether a refresh was dispatched
     */
    public function ensureTokenFresh(): bool
    {
        if ($this->getToken() === '') {
            return false;
        }

        // Throttle: only one request every N hours gets to do the check at all.
        $lockTtl = now()->addHours((int) config('instagram.token_check_interval_hours', 6));
        if (! Cache::add(self::CHECK_LOCK_KEY, 1, $lockTtl)) {
            return false;
        }

        $meta = $this->getTokenMeta();

        // Meta rejects refreshing a token that is less than 24 hours old.
        if (! empty($meta['refreshed_at'])
            && Carbon::parse($meta['refreshed_at'])->gt(now()->subDay())) {
            return false;
        }

        // Plenty of life left — nothing to do.
        $refreshDays = (int) config('instagram.token_refresh_days', 25);
        if (! empty($meta['expires_at'])
            && now()->lt(Carbon::parse($meta['expires_at'])->subDays($refreshDays))) {
            return false;
        }

        // Unknown expiry (legacy token) or inside the renewal window → refresh,
        // but only after the response has been flushed to the browser.
        dispatch(function () {
            app(self::class)->refreshToken();
        })->afterResponse();

        return true;
    }

    /**
     * Record a failed refresh attempt without touching the token itself, and
     * alert admins if expiry is close (or unknown).
     *
     * @return array{ok: false, message: string}
     */
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

    /**
     * Email admins when a refresh fails and the token is close to (or past)
     * expiry, or the expiry is unknown. Sent synchronously (sendNow) — the host
     * has no queue worker — and wrapped so a mail failure can never break a
     * page render.
     */
    private function maybeAlertAdmins(string $error, array $meta): void
    {
        $alertDays = (int) config('instagram.token_alert_days', 14);
        $expiresAt = ! empty($meta['expires_at']) ? Carbon::parse($meta['expires_at']) : null;

        // Only shout when it actually matters.
        if ($expiresAt && now()->lt($expiresAt->copy()->subDays($alertDays))) {
            return;
        }

        // At most one alert per day.
        if (! Cache::add(self::ALERT_LOCK_KEY, 1, now()->addDay())) {
            return;
        }

        try {
            $admins = User::whereIn('role', ['admin', 'superadmin'])
                ->pluck('email')
                ->filter()
                ->all();

            if (! empty($admins)) {
                Mail::to($admins)->sendNow(new InstagramTokenAlertMail($error, $expiresAt));
            }
        } catch (\Throwable $e) {
            logger()->error('Failed to send Instagram token alert: ' . $e->getMessage());
        }
    }

    private function fetchPosts(): array
    {
        $token = $this->getToken();
        $accountId = (string) config('instagram.account_id', '');

        if ($token === '' || $accountId === '') {
            // Not configured yet — view will show the placeholder grid.
            return [];
        }

        $version = config('instagram.graph_version', 'v21.0');
        $limit = (int) config('instagram.limit', 6);

        try {
            $response = Http::timeout(5)
                ->retry(1, 200)
                ->get("https://graph.instagram.com/{$version}/{$accountId}/media", [
                    'fields' => 'id,caption,media_type,media_product_type,media_url,thumbnail_url,permalink',
                    'limit' => $limit,
                    'access_token' => $token,
                ]);

            if ($response->failed()) {
                Log::warning('Instagram feed fetch failed', [
                    'status' => $response->status(),
                    'body' => $response->json('error.message') ?? $response->body(),
                ]);
                return [];
            }

            $items = $response->json('data', []);

            return collect($items)
                ->map(function ($item) {
                    $mediaType = $item['media_type'] ?? 'IMAGE';

                    // Reels come back as media_type=VIDEO with media_product_type=REELS,
                    // but some responses label the type itself REELS. Treat both as video.
                    $isVideo = in_array($mediaType, ['VIDEO', 'REELS'], true)
                        || ($item['media_product_type'] ?? null) === 'REELS';

                    $image    = $isVideo
                        ? ($item['thumbnail_url'] ?? $item['media_url'] ?? null)
                        : ($item['media_url'] ?? null);
                    $videoUrl = $isVideo ? ($item['media_url'] ?? null) : null;

                    if (! $image && ! $videoUrl) {
                        return null;
                    }

                    return [
                        'image'      => $image,
                        'video_url'  => $videoUrl,
                        // Normalize so the homepage view's `=== 'VIDEO'` gate catches Reels.
                        'media_type' => $isVideo ? 'VIDEO' : $mediaType,
                        'permalink'  => $item['permalink'] ?? null,
                        'caption'    => isset($item['caption'])
                            ? \Illuminate\Support\Str::limit($item['caption'], 120)
                            : '',
                    ];
                })
                ->filter()
                ->take($limit)
                ->values()
                ->all();
        } catch (\Throwable $e) {
            Log::warning('Instagram feed fetch threw an exception', ['error' => $e->getMessage()]);
            return [];
        }
    }
}
