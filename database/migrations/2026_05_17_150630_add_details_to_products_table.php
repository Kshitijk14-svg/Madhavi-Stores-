<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('discount_type')->nullable(); // 'percent', 'fixed'
            $table->decimal('discount_value', 10, 2)->nullable();
            $table->boolean('is_new_arrival')->default(false);
            $table->timestamp('new_arrival_expires_at')->nullable();
            $table->json('gallery_images')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('seo_keywords')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'discount_type',
                'discount_value',
                'is_new_arrival',
                'new_arrival_expires_at',
                'gallery_images',
                'seo_title',
                'seo_description',
                'seo_keywords',
            ]);
        });
    }
};
