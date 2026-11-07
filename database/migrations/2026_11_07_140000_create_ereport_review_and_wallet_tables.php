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
        // 1. Tabel Dompet Khusus Perawat (Nurse Wallets)
        if (!Schema::hasTable('nurse_wallets')) {
            Schema::create('nurse_wallets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('nurse_id')->unique()->constrained('users')->onDelete('cascade');
                $table->unsignedBigInteger('balance')->default(0);
                $table->unsignedBigInteger('pending_balance')->default(0);
                $table->timestamps();
            });
        }

        // 2. Modifikasi Tabel Transaksi Dompet (Wallet Transactions)
        Schema::table('wallet_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('wallet_transactions', 'nurse_wallet_id')) {
                $table->foreignId('nurse_wallet_id')->nullable()->after('wallet_id')->constrained('nurse_wallets')->onDelete('set null');
            }
            if (!Schema::hasColumn('wallet_transactions', 'order_id')) {
                $table->foreignId('order_id')->nullable()->after('nurse_wallet_id')->constrained('orders')->onDelete('set null');
            }
            if (!Schema::hasColumn('wallet_transactions', 'platform_fee')) {
                $table->unsignedBigInteger('platform_fee')->default(0)->after('amount');
            }
            if (!Schema::hasColumn('wallet_transactions', 'net_amount')) {
                $table->unsignedBigInteger('net_amount')->default(0)->after('platform_fee');
            }
            if (!Schema::hasColumn('wallet_transactions', 'status')) {
                $table->string('status', 30)->default('completed')->after('net_amount'); // pending, completed, failed
            }
        });

        // 3. Tabel Lembar Rekam Medis Digital / E-Report Klinis
        if (!Schema::hasTable('e_reports')) {
            Schema::create('e_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->unique()->constrained('orders')->onDelete('cascade');
                $table->foreignId('nurse_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('patient_id')->constrained('users')->onDelete('cascade');

                // Tanda-Tanda Vital (TTV) Fisik
                $table->unsignedSmallInteger('systolic'); // Tekanan Darah Atas (mmHg)
                $table->unsignedSmallInteger('diastolic'); // Tekanan Darah Bawah (mmHg)
                $table->decimal('body_temp', 4, 1); // Suhu Tubuh (°C)
                $table->unsignedSmallInteger('pulse_rate'); // Denyut Nadi (bpm)
                $table->unsignedTinyInteger('spo2'); // Saturasi Oksigen (%)
                $table->unsignedSmallInteger('gds')->nullable(); // Gula Darah Sewaktu (mg/dL)

                // Catatan Asuhan & Edukasi
                $table->text('clinical_notes'); // Ringkasan Asuhan Keperawatan (SOAP)
                $table->text('family_education')->nullable(); // Edukasi Keluarga Pasien

                $table->timestamps();

                $table->index(['nurse_id', 'created_at']);
                $table->index(['patient_id', 'created_at']);
            });
        }

        // 4. Tabel Dokumentasi Foto Tindakan Medis / Luka
        if (!Schema::hasTable('e_report_photos')) {
            Schema::create('e_report_photos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('e_report_id')->constrained('e_reports')->onDelete('cascade');
                $table->string('photo_path', 500);
                $table->string('caption', 255)->nullable();
                $table->timestamps();
            });
        }

        // 5. Tabel Ulasan & Rating Pasien (Reviews)
        if (!Schema::hasTable('reviews')) {
            Schema::create('reviews', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->unique()->constrained('orders')->onDelete('cascade');
                $table->foreignId('patient_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('nurse_id')->constrained('users')->onDelete('cascade');
                $table->unsignedTinyInteger('rating'); // Bintang 1 - 5
                $table->text('comment')->nullable();
                $table->timestamps();

                $table->index(['nurse_id', 'rating']);
                $table->index('patient_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('e_report_photos');
        Schema::dropIfExists('e_reports');

        Schema::table('wallet_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('wallet_transactions', 'nurse_wallet_id')) {
                $table->dropForeign(['nurse_wallet_id']);
                $table->dropColumn('nurse_wallet_id');
            }
            if (Schema::hasColumn('wallet_transactions', 'order_id')) {
                $table->dropForeign(['order_id']);
                $table->dropColumn('order_id');
            }
            if (Schema::hasColumn('wallet_transactions', 'platform_fee')) {
                $table->dropColumn('platform_fee');
            }
            if (Schema::hasColumn('wallet_transactions', 'net_amount')) {
                $table->dropColumn('net_amount');
            }
            if (Schema::hasColumn('wallet_transactions', 'status')) {
                $table->dropColumn('status');
            }
        });

        Schema::dropIfExists('nurse_wallets');
    }
};

