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
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'estimated_travel_minutes')) {
                $table->unsignedSmallInteger('estimated_travel_minutes')->nullable()->after('distance_km');
            }
            if (!Schema::hasColumn('orders', 'estimated_arrival_at')) {
                $table->timestamp('estimated_arrival_at')->nullable()->after('estimated_travel_minutes');
            }
            if (!Schema::hasColumn('orders', 'departed_at')) {
                $table->timestamp('departed_at')->nullable()->after('estimated_arrival_at');
            }
            if (!Schema::hasColumn('orders', 'arrived_at')) {
                $table->timestamp('arrived_at')->nullable()->after('departed_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            foreach (['arrived_at', 'departed_at', 'estimated_arrival_at', 'estimated_travel_minutes'] as $col) {
                if (Schema::hasColumn('orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
