<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * MySQL/SQLite allow unlimited NULLs under a unique index, so COD/non-Razorpay
     * orders (which never set razorpay_payment_id) are unaffected. This closes the
     * TOCTOU window where a browser verifyPayment() call and the Razorpay webhook
     * fire concurrently for the same payment and both pass the pre-transaction
     * idempotency check before either inserts.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unique('razorpay_payment_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['razorpay_payment_id']);
        });
    }
};
