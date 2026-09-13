<?php

namespace App\Console\Commands;

use App\Services\InstagramService;
use Illuminate\Console\Command;

/**
 * Refreshes the long-lived Instagram (Instagram-Login API) access token. Meta
 * long-lived (60-day) Instagram User tokens are extended by re-presenting the
 * current token via grant_type=ig_refresh_token to graph.instagram.com, which
 * resets the 60-day window. Unlike the old Facebook-Login flow this needs only
 * the token itself — no app id/secret.
 *
 * The actual HTTP call lives in InstagramService::refreshToken() so the command,
 * the admin "Refresh now" button, and the traffic-driven self-heal loop all
 * share one implementation. Scheduled weekly (routes/console.php) as a bonus
 * path — harmless without cron, primary if cron is ever configured.
 */
class RefreshInstagramToken extends Command
{
    // --force is accepted for parity with tooling that passes it; refreshToken()
    // always attempts a refresh regardless, so it is currently a no-op flag.
    protected $signature = 'instagram:refresh-token {--force : Refresh even if not due yet}';

    protected $description = 'Refresh (extend) the long-lived Instagram access token.';

    public function handle(InstagramService $instagram): int
    {
        $result = $instagram->refreshToken();

        $result['ok'] ? $this->info($result['message']) : $this->error($result['message']);

        return $result['ok'] ? self::SUCCESS : self::FAILURE;
    }
}
