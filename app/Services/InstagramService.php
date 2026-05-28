<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstagramService
{
    protected string $accessToken;
    protected string $accountId;
    protected string $baseUrl = 'https://graph.facebook.com/v20.0';

    public function __construct()
    {
        $this->accessToken = config('instagram.access_token');
        $this->accountId = config('instagram.account_id');
    }

    public function fetchProfileAndFeed(int $limit = 6)
    {
        if (empty($this->accessToken) || empty($this->accountId)) {
            Log::warning('Instagram API configuration is missing.');
            return null;
        }

        try {
            // Fetch Profile Data (Followers Count) and Media Feed
            $response = Http::get("{$this->baseUrl}/{$this->accountId}", [
                'fields' => "followers_count,media.limit({$limit}){id,caption,media_type,media_url,thumbnail_url,permalink}",
                'access_token' => $this->accessToken,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                $followersCount = $data['followers_count'] ?? 0;
                $media = $data['media']['data'] ?? [];

                return [
                    'followers_count' => $followersCount,
                    'feed' => $media,
                ];
            }

            Log::error('Instagram API Error: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Instagram API Exception: ' . $e->getMessage());
        }

        return null;
    }
}
