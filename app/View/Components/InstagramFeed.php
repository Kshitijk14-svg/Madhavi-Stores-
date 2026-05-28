<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Illuminate\Support\Facades\Cache;

class InstagramFeed extends Component
{
    public array $feed;
    public int $followersCount;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $data = Cache::get('instagram_data', [
            'followers_count' => 0,
            'feed' => []
        ]);

        $this->followersCount = $data['followers_count'] ?? 0;
        $this->feed = $data['feed'] ?? [];
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.instagram-feed');
    }
}
