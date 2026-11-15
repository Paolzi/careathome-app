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
        if (!Schema::hasColumn('nurse_schedules', 'time_slots')) {
            Schema::table('nurse_schedules', function (Blueprint $table) {
                $table->json('time_slots')->nullable()->after('end_time');
            });

            // Isi default slot jam kerja per hari untuk data jadwal yang sudah ada
            $defaultSlots = json_encode(['08:00', '10:00', '13:00', '15:00', '19:00']);
            DB::table('nurse_schedules')
                ->whereNull('time_slots')
                ->update(['time_slots' => $defaultSlots]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('nurse_schedules', 'time_slots')) {
            Schema::table('nurse_schedules', function (Blueprint $table) {
                $table->dropColumn('time_slots');
            });
        }
    }
};
