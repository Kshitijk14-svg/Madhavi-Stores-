<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Remove duplicate cart rows before adding unique constraint
        DB::statement('
            DELETE c1 FROM cart_items c1
            INNER JOIN cart_items c2
            WHERE c1.id > c2.id
              AND c1.user_id = c2.user_id
              AND c1.product_id = c2.product_id
              AND c1.size = c2.size
        ');

        Schema::table('cart_items', function (Blueprint $table) {
            $table->unique(['user_id', 'product_id', 'size']);
        });

        // Remove duplicate wishlist rows before adding unique constraint
        DB::statement('
            DELETE w1 FROM wishlist_items w1
            INNER JOIN wishlist_items w2
            WHERE w1.id > w2.id
              AND w1.user_id = w2.user_id
              AND w1.product_id = w2.product_id
        ');

        Schema::table('wishlist_items', function (Blueprint $table) {
            $table->unique(['user_id', 'product_id']);
        });

        // Remove duplicate reviews before adding unique constraint
        DB::statement('
            DELETE r1 FROM reviews r1
            INNER JOIN reviews r2
            WHERE r1.id > r2.id
              AND r1.user_id = r2.user_id
              AND r1.product_id = r2.product_id
        ');

        Schema::table('reviews', function (Blueprint $table) {
            $table->unique(['user_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'product_id', 'size']);
        });

        Schema::table('wishlist_items', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'product_id']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'product_id']);
        });
    }
};
