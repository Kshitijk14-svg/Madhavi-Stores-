<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // orders table: most critical missing indexes for scalability
        Schema::table('orders', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('order_status');
            $table->index('payment_status');
            $table->index('created_at');
        });

        // otp_codes: needed for efficient expired-record cleanup queries
        Schema::table('otp_codes', function (Blueprint $table) {
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['order_status']);
            $table->dropIndex(['payment_status']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('otp_codes', function (Blueprint $table) {
            $table->dropIndex(['expires_at']);
        });
    }
};
