<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('otp_codes', function (Blueprint $table) {
            // Widen code to hold a bcrypt hash instead of the raw 6-digit code.
            $table->string('code', 255)->change();
            // Cap brute-force guesses against a single live code.
            $table->unsignedTinyInteger('attempts')->default(0)->after('purpose');
            // Lookups are always by (email, purpose).
            $table->index(['email', 'purpose'], 'otp_codes_email_purpose_index');
        });
    }

    public function down(): void
    {
        Schema::table('otp_codes', function (Blueprint $table) {
            $table->dropIndex('otp_codes_email_purpose_index');
            $table->dropColumn('attempts');
            $table->string('code', 6)->change();
        });
    }
};
