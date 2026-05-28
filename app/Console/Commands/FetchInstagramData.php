<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\InstagramService;
use Illuminate\Support\Facades\Cache;

class FetchInstagramData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'instagram:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and cache the latest Instagram feed and followers count';

    /**
     * Execute the console command.
     */
    public function handle(InstagramService $instagramService)
    {
        $this->info('Fetching Instagram data...');

        $data = $instagramService->fetchProfileAndFeed(6); // Fetch 6 latest posts

        if ($data) {
            // Cache the data for 2 hours
            Cache::put('instagram_data', $data, now()->addHours(2));
            $this->info('Successfully fetched and cached Instagram data!');
        } else {
            $this->error('Failed to fetch Instagram data. Check logs for details.');
        }
    }
}
