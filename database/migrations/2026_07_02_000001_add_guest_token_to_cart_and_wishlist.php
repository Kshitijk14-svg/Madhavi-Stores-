<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->string('guest_token', 64)->nullable()->after('user_id');
            $table->index('guest_token');
            $table->unique(['guest_token', 'product_id', 'size'], 'cart_items_guest_product_size_unique');
        });

        Schema::table('wishlist_items', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->string('guest_token', 64)->nullable()->after('user_id');
            $table->index('guest_token');
            $table->unique(['guest_token', 'product_id'], 'wishlist_items_guest_product_unique');
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique('cart_items_guest_product_size_unique');
            $table->dropIndex(['guest_token']);
            $table->dropColumn('guest_token');
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });

        Schema::table('wishlist_items', function (Blueprint $table) {
            $table->dropUnique('wishlist_items_guest_product_unique');
            $table->dropIndex(['guest_token']);
            $table->dropColumn('guest_token');
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }
};
