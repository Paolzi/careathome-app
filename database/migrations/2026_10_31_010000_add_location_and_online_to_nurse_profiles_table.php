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
            $table->boolean('is_online')->default(false)->index()->after('is_available');
            $table->decimal('latitude', 10, 7)->nullable()->after('is_online');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->timestamp('location_updated_at')->nullable()->after('longitude');
            $table->text('rejection_reason')->nullable()->after('verification_status');
            $table->foreignId('reviewed_by')->nullable()->after('rejection_reason')->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');

            // Index gabungan untuk query radius geolocation
            $table->index(['latitude', 'longitude'], 'nurse_profiles_lat_lng_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nurse_profiles', function (Blueprint $table) {
            $table->dropIndex('nurse_profiles_lat_lng_idx');
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn([
                'is_online',
                'latitude',
                'longitude',
                'location_updated_at',
                'rejection_reason',
                'reviewed_by',
                'reviewed_at',
            ]);
        });
    }
};
