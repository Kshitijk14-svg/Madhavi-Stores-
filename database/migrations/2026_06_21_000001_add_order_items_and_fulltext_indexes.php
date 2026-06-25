<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SHOW INDEX / FULLTEXT are MySQL-only. On the SQLite test database there
        // is no partial prior run to guard against, so just add the plain index.
        if (DB::getDriverName() !== 'mysql') {
            if (! $this->indexExists('order_items', 'order_items_product_id_index')) {
                Schema::table('order_items', function (Blueprint $table) {
                    $table->index('product_id');
                });
            }
            return;
        }

        // Guard against a partial prior run that already added the index.
        $hasIndex = collect(DB::select("SHOW INDEX FROM order_items WHERE Key_name = 'order_items_product_id_index'"))->isNotEmpty();
        if (! $hasIndex) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->index('product_id');
            });
        }

        $hasFT = collect(DB::select("SHOW INDEX FROM products WHERE Key_name = 'products_name_fulltext'"))->isNotEmpty();
        if (! $hasFT) {
            DB::statement('ALTER TABLE products ADD FULLTEXT INDEX products_name_fulltext (name)');
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        return collect(Schema::getIndexes($table))
            ->contains(fn ($i) => $i['name'] === $index);
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE products DROP INDEX products_name_fulltext');
        }
    }
};
