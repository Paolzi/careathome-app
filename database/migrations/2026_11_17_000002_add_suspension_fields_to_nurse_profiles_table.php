<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nurse_profiles', function (Blueprint $table) {
            $table->timestamp('suspended_until')->nullable()->after('is_online');
            $table->string('suspension_reason', 500)->nullable()->after('suspended_until');
        });
    }

    public function down(): void
    {
        Schema::table('nurse_profiles', function (Blueprint $table) {
            $table->dropColumn(['suspended_until', 'suspension_reason']);
        });
    }
};
