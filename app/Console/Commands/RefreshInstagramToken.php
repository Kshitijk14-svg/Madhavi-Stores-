<?php

namespace App\Console\Commands;

use App\Services\InstagramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Refreshes the long-lived Instagram (Instagram-Login API) access token. Meta
 * long-lived (60-day) Instagram User tokens are extended by re-presenting the
 * current token via grant_type=ig_refresh_token to graph.instagram.com, which
 * resets the 60-day window. Unlike the old Facebook-Login flow this needs only
 * the token itself — no app id/secret. Scheduled to run weekly (see
 * routes/console.php) so the token never lapses. The refreshed token is
 * persisted via InstagramService (settings table).
 */
class RefreshInstagramToken extends Command
{
    protected $signature = 'instagram:refresh-token';

    protected $description = 'Refresh (extend) the long-lived Instagram access token.';

    public function handle(InstagramService $instagram): int
    {
        $token = $instagram->getToken();

        if ($token === '') {
            $this->warn('No Instagram access token configured — nothing to refresh.');
            return self::SUCCESS;
        }

        try {
            $response = Http::timeout(10)->get('https://graph.instagram.com/refresh_access_token', [
                'grant_type' => 'ig_refresh_token',
                'access_token' => $token,
            ]);

            if ($response->failed()) {
                $this->error('Token refresh failed: ' . ($response->json('error.message') ?? $response->body()));
                return self::FAILURE;
            }

            $newToken = $response->json('access_token');
            if (! $newToken) {
                $this->error('Token refresh returned no access_token.');
                return self::FAILURE;
            }

            $instagram->storeToken($newToken);

            $expiresIn = (int) $response->json('expires_in', 0);
            $this->info('Instagram token refreshed. Expires in ~' . round($expiresIn / 86400) . ' days.');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Token refresh threw an exception: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
