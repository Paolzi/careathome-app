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
        // 1. Tabel Profil Perawat (Nurse Profiles)
        Schema::create('nurse_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('nik', 16)->nullable();
            $table->string('str_number', 50)->nullable();
            $table->date('str_expiry')->nullable();
            $table->string('sip_number', 100)->nullable();
            $table->date('sip_expiry')->nullable();
            $table->string('sip_institution')->nullable();
            $table->string('specialization')->nullable();
            $table->string('verification_status', 30)->default('draft'); // draft, pending, verified, rejected
            $table->boolean('is_available')->default(false);
            $table->decimal('balance', 14, 2)->default(0);
            $table->decimal('rating_avg', 3, 2)->default(0.00);
            $table->unsignedInteger('total_reviews')->default(0);
            $table->timestamps();
        });

        // 2. Tabel Berkas Legalitas Perawat (Nurse Documents)
        Schema::create('nurse_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nurse_profile_id')->constrained('nurse_profiles')->onDelete('cascade');
            $table->string('document_type', 50); // ktp, str, sip, ijazah
            $table->string('file_path', 500);
            $table->string('original_name')->nullable();
            $table->string('file_size', 50)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->string('status', 30)->default('pending'); // draft, pending, verified, rejected
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. Tabel Riwayat Audit Verifikasi Admin (Nurse Verifications)
        Schema::create('nurse_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nurse_profile_id')->constrained('nurse_profiles')->onDelete('cascade');
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('status', 30)->default('pending'); // pending, verified, rejected
            $table->text('rejection_reason')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nurse_verifications');
        Schema::dropIfExists('nurse_documents');
        Schema::dropIfExists('nurse_profiles');
    }
};
