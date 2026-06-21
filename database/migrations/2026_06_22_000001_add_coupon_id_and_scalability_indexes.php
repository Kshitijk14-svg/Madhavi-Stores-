<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Additive, backward-compatible. Safe to run on the live database.
 *  - orders.coupon_id : proper FK to coupons (coupon_code kept for back-compat).
 *  - reviews.product_id index : the composite unique leads with user_id and
 *    cannot serve `WHERE product_id = ?` (every PDP review lookup).
 *  - products.price index : price range filters + orderBy('price').
 *  - orders.coupon_code index : per-user coupon usage count runs on every checkout.
 */
return new class extends Migration
{
    public function up(): void
    {
        // cart_items.size was NOT NULL (default 'M'), but the cart stores NULL for
        // products without sizes — which would fail the insert on strict MySQL.
        // Make it nullable so size-less products can be added to the cart.
        Schema::table('cart_items', function (Blueprint $table) {
            $table->string('size')->nullable()->default(null)->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'coupon_id')) {
                $table->foreignId('coupon_id')->nullable()->after('coupon_code')
                    ->constrained('coupons')->nullOnDelete();
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (!$this->indexExists('orders', 'orders_coupon_code_index')) {
                $table->index('coupon_code');
            }
        });

        Schema::table('reviews', function (Blueprint $table) {
            if (!$this->indexExists('reviews', 'reviews_product_id_index')) {
                $table->index('product_id');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (!$this->indexExists('products', 'products_price_index')) {
                $table->index('price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'coupon_id')) {
                $table->dropConstrainedForeignId('coupon_id');
            }
            if ($this->indexExists('orders', 'orders_coupon_code_index')) {
                $table->dropIndex('orders_coupon_code_index');
            }
        });

        Schema::table('reviews', function (Blueprint $table) {
            if ($this->indexExists('reviews', 'reviews_product_id_index')) {
                $table->dropIndex('reviews_product_id_index');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if ($this->indexExists('products', 'products_price_index')) {
                $table->dropIndex('products_price_index');
            }
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        return collect(Schema::getIndexes($table))
            ->contains(fn ($i) => $i['name'] === $index);
    }
};
