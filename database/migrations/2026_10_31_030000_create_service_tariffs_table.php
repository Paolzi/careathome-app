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
        Schema::create('service_tariffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('service_catalogs')->onDelete('cascade');
            $table->unsignedBigInteger('price');
            $table->dateTime('effective_from');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['service_id', 'effective_from']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_tariffs');
    }
};
