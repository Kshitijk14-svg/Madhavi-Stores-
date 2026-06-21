<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->index('product_id');
        });

        DB::statement('ALTER TABLE products ADD FULLTEXT INDEX products_name_fulltext (name)');
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
        });

        DB::statement('ALTER TABLE products DROP INDEX products_name_fulltext');
    }
};
