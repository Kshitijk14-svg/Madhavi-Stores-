<?php

return [
    // Long-lived access token from the Instagram (Instagram-Login) API. Seeds the
    // initial value; once the refresh command runs, the live token is kept in the
    // settings table (see App\Services\InstagramService) so it survives without
    // rewriting .env.
    'access_token' => env('INSTAGRAM_ACCESS_TOKEN', ''),

    // The Instagram user id (NOT the @handle). Shown next to the account on the
    // "Generate access tokens" panel during setup.
    'account_id' => env('INSTAGRAM_BUSINESS_ACCOUNT_ID', ''),

    // App ID + Secret from the Meta app (Settings → Basic). Not required at runtime
    // for the Instagram-Login flow (the weekly refresh uses only the token), kept
    // for reference / manual short-lived→long-lived token exchanges.
    'app_id' => env('INSTAGRAM_APP_ID', ''),
    'app_secret' => env('INSTAGRAM_APP_SECRET', ''),

    // Graph API version to call.
    'graph_version' => env('INSTAGRAM_GRAPH_VERSION', 'v21.0'),

    // How long (seconds) to cache the fetched feed so every page load does not
    // hit the Graph API. Default 1 hour.
    'cache_ttl' => (int) env('INSTAGRAM_CACHE_TTL', 3600),

    // Number of posts to show in the homepage grid.
    'limit' => 6,

    // Refresh the token when this many days remain before expiry. Meta long-lived
    // tokens last 60 days, so 25 leaves a generous grace buffer.
    'token_refresh_days' => (int) env('INSTAGRAM_TOKEN_REFRESH_DAYS', 25),

    // How often (hours) a homepage load is allowed to check whether a refresh is
    // due. Keeps the traffic-driven self-heal loop cheap.
    'token_check_interval_hours' => (int) env('INSTAGRAM_TOKEN_CHECK_HOURS', 6),

    // Email the admins if a refresh fails and expiry is this close (days), or
    // the expiry is unknown.
    'token_alert_days' => (int) env('INSTAGRAM_TOKEN_ALERT_DAYS', 14),
];
