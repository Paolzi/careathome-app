<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nurse_profiles', function (Blueprint $table) {
            $table->unique('nik', 'nurse_profiles_nik_unique');
            $table->unique('str_number', 'nurse_profiles_str_number_unique');
            $table->unique('sip_number', 'nurse_profiles_sip_number_unique');
        });
    }

    public function down(): void
    {
        Schema::table('nurse_profiles', function (Blueprint $table) {
            $table->dropUnique('nurse_profiles_nik_unique');
            $table->dropUnique('nurse_profiles_str_number_unique');
            $table->dropUnique('nurse_profiles_sip_number_unique');
        });
    }
};
