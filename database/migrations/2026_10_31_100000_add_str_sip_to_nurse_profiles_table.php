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
        Schema::table('nurse_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('nurse_profiles', 'str_number')) {
                $table->string('str_number')->nullable()->after('reviewed_at');
            }
            if (!Schema::hasColumn('nurse_profiles', 'str_expired_at')) {
                $table->date('str_expired_at')->nullable()->after('str_number');
            }
            if (!Schema::hasColumn('nurse_profiles', 'sip_number')) {
                $table->string('sip_number')->nullable()->after('str_expired_at');
            }
            if (!Schema::hasColumn('nurse_profiles', 'sip_expired_at')) {
                $table->date('sip_expired_at')->nullable()->after('sip_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nurse_profiles', function (Blueprint $table) {
            $table->dropColumn(['str_number', 'str_expired_at', 'sip_number', 'sip_expired_at']);
        });
    }
};
