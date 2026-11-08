<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('nurse_profiles', function (Blueprint $table) {
            // Ubah latitude dan longitude menjadi DECIMAL(10, 8) dan (11, 8) untuk presisi kalkulasi GPS
            $table->decimal('latitude', 10, 8)->nullable()->change();
            $table->decimal('longitude', 11, 8)->nullable()->change();

            if (!Schema::hasColumn('nurse_profiles', 'status_verifikasi')) {
                $table->string('status_verifikasi', 30)->default('verified')->after('verification_status')->index();
            }

            if (!Schema::hasColumn('nurse_profiles', 'status_legalitas')) {
                $table->string('status_legalitas', 30)->default('verified')->after('status_verifikasi')->index();
            }
        });

        // Sinkronkan nilai kolom status_verifikasi dan status_legalitas dengan verification_status
        DB::statement("UPDATE nurse_profiles SET status_verifikasi = verification_status, status_legalitas = verification_status");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nurse_profiles', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->change();
            $table->decimal('longitude', 10, 7)->nullable()->change();

            if (Schema::hasColumn('nurse_profiles', 'status_verifikasi')) {
                $table->dropColumn('status_verifikasi');
            }
            if (Schema::hasColumn('nurse_profiles', 'status_legalitas')) {
                $table->dropColumn('status_legalitas');
            }
        });
    }
};

