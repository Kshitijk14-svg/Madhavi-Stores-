<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // products: category_id, is_bestseller, is_new_arrival
        // (slug already has a UNIQUE index from create_products — skipped to avoid duplicate)
        Schema::table('products', function (Blueprint $table) {
            $table->index('category_id');
            $table->index('is_bestseller');
            $table->index('is_new_arrival');
        });

        // orders: missing indexes critical for scalability
        Schema::table('orders', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('order_status');
            $table->index('payment_status');
            $table->index('created_at');
        });

        // settings.key already has a UNIQUE index — no additional index needed

        // cart_items.user_id and wishlist_items.user_id
        Schema::table('cart_items', function (Blueprint $table) {
            $table->index('user_id');
        });

        Schema::table('wishlist_items', function (Blueprint $table) {
            $table->index('user_id');
        });

        // otp_codes.expires_at for efficient cleanup queries
        Schema::table('otp_codes', function (Blueprint $table) {
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['category_id']);
            $table->dropIndex(['is_bestseller']);
            $table->dropIndex(['is_new_arrival']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['order_status']);
            $table->dropIndex(['payment_status']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });

        Schema::table('wishlist_items', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });

        Schema::table('otp_codes', function (Blueprint $table) {
            $table->dropIndex(['expires_at']);
        });
    }
};
