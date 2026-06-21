<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // All indexes here duplicated 2026_06_16_061933 — intentional no-op to avoid
        // fresh-install failure. See that migration for orders + otp_codes indexes.
    }

    public function down(): void
    {
        // no-op
    }
};
