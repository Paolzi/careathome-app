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
        Schema::table('nurse_documents', function (Blueprint $table) {
            $table->string('storage_driver', 50)->default('public')->after('file_path');
            $table->string('str_number', 50)->nullable()->after('storage_driver');
            $table->string('sip_number', 100)->nullable()->after('str_number');
            $table->timestamp('ktki_verified_at')->nullable()->after('sip_number');
            $table->text('rejection_reason')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nurse_documents', function (Blueprint $table) {
            $table->dropColumn([
                'storage_driver',
                'str_number',
                'sip_number',
                'ktki_verified_at',
                'rejection_reason',
            ]);
        });
    }
};

