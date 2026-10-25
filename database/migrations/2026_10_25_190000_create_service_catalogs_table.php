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
        Schema::create('service_catalogs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category'); // e.g. Perawatan Khusus, Prosedur Medis, Terapi & Injeksi, etc.
            $table->string('category_badge')->nullable();
            $table->string('badge_color')->nullable();
            $table->string('image_emoji', 20)->default('🩺');
            $table->text('description');
            $table->text('indications')->nullable(); // Indikasi klinis tindakan
            $table->text('contraindications')->nullable(); // Kontraindikasi medis (misal kasus gawat darurat)
            $table->json('inclusions')->nullable(); // Butir-butir standar tindakan
            $table->string('estimated_duration')->default('45 - 60 Menit');
            $table->unsignedInteger('estimated_duration_minutes')->default(60);
            $table->decimal('price', 12, 2); // Plafon tarif standar sesuai KF-04
            $table->boolean('is_active')->default(true); // Status aktif/nonaktif dari admin
            $table->timestamps();

            $table->index(['category', 'is_active']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_catalogs');
    }
};
