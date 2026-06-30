<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupForLaunch extends Command
{
    protected $signature   = 'cleanup:launch';
    protected $description = 'Delete all orders and all users except the two owner accounts';

    public function handle(): int
    {
        $keep = ['kshitijmay14@gmail.com', 'harshturakhiya01@gmail.com'];

        if (!$this->confirm('This will permanently delete ALL orders and all users except ' . implode(' & ', $keep) . '. Continue?')) {
            $this->info('Aborted.');
            return 0;
        }

        // Orders — delete items first, then orders (avoids FK issues on hosts without cascade)
        $items = DB::table('order_items')->delete();
        $orders = DB::table('orders')->delete();
        $this->info("Deleted {$orders} orders and {$items} order items.");

        // Users — cascades handle cart_items, wishlist_items, reviews automatically
        $deleted = DB::table('users')->whereNotIn('email', $keep)->delete();
        $this->info("Deleted {$deleted} users.");

        $remaining = DB::table('users')->count();
        $this->info("Remaining users: {$remaining}");
        $this->info('Done. Products and collections are untouched.');

        return 0;
    }
}
