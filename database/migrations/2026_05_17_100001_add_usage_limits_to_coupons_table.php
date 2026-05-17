<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->unsignedInteger('max_uses')->nullable()->after('expires_at'); // null = unlimited
            $table->unsignedInteger('used_count')->default(0)->after('max_uses');
            $table->unsignedInteger('max_uses_per_user')->default(1)->after('used_count');
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn(['max_uses', 'used_count', 'max_uses_per_user']);
        });
    }
};
