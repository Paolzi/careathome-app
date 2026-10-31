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
        Schema::create('nurse_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nurse_profile_id')->constrained('nurse_profiles')->onDelete('cascade');
            $table->foreignId('service_id')->constrained('service_catalogs')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['nurse_profile_id', 'service_id'], 'nurse_service_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nurse_services');
    }
};
