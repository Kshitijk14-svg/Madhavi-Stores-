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
];
