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
        if (! Schema::hasTable('nurse_schedules')) {
            Schema::create('nurse_schedules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('nurse_id')->constrained('users')->cascadeOnDelete();
                $table->unsignedTinyInteger('day_of_week')->comment('1=Senin, 2=Selasa, 3=Rabu, 4=Kamis, 5=Jumat, 6=Sabtu, 7=Minggu');
                $table->string('day_name', 20);
                $table->string('start_time', 10)->default('08:00');
                $table->string('end_time', 10)->default('17:00');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['nurse_id', 'day_of_week']);
                $table->index(['day_of_week', 'is_active']);
            });
        }

        if (Schema::hasTable('orders') && ! Schema::hasColumn('orders', 'booking_type')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('booking_type', 20)->default('scheduled')->after('order_number')->comment('now atau scheduled');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'booking_type')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('booking_type');
            });
        }

        Schema::dropIfExists('nurse_schedules');
    }
};

