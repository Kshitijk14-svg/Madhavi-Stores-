<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Fetches the store's latest Instagram posts via the Meta Graph API and caches
 * them so the homepage does not call the API on every request.
 *
 * Resilient by design: if no token/account is configured, or the API call
 * fails/times out, getPosts() returns an empty array and the view falls back to
 * its placeholder grid. This service never throws.
 */
class InstagramService
{
    private const CACHE_KEY = 'instagram_feed_posts';
    private const TOKEN_SETTING_KEY = 'instagram_token';

    /**
     * Return up to N normalized posts:
     * [ ['image' => url, 'permalink' => url, 'caption' => string], ... ]
     */
    public function getPosts(): array
    {
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
     * Persist a refreshed token and bust the feed cache.
     */
    public function storeToken(string $token): void
    {
        Setting::set(self::TOKEN_SETTING_KEY, $token);
        $this->forgetCache();
    }

    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
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
                    'fields' => 'id,caption,media_type,media_url,thumbnail_url,permalink',
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
                    // Videos/reels expose a still via thumbnail_url; images use media_url.
                    $image = ($item['media_type'] ?? '') === 'VIDEO'
                        ? ($item['thumbnail_url'] ?? $item['media_url'] ?? null)
                        : ($item['media_url'] ?? null);

                    if (! $image) {
                        return null;
                    }

                    return [
                        'image' => $image,
                        'permalink' => $item['permalink'] ?? null,
                        'caption' => isset($item['caption'])
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
