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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 50)->unique();
            $table->foreignId('patient_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('service_catalog_id')->constrained('service_catalogs')->onDelete('cascade');
            $table->foreignId('nurse_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Jadwal Kunjungan
            $table->date('scheduled_date');
            $table->string('scheduled_time', 50); // e.g. "09:00 - 10:00"
            
            // Data Penerima Layanan & Alamat Lokasi
            $table->string('patient_name');
            $table->string('patient_phone', 25);
            $table->text('address');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('distance_km', 6, 2)->default(0.00);
            
            // Catatan Kondisi Klinis & Foto (Opsional)
            $table->text('clinical_notes')->nullable();
            $table->string('medical_photo_path', 500)->nullable();
            
            // Price Breakdown Engine (KF-05, KF-07, KF-10)
            $table->decimal('base_price', 12, 2); // Tarif tindakan katalog
            $table->decimal('transport_fee', 12, 2)->default(0.00); // Ongkos transport berbasis jarak GPS
            $table->decimal('platform_fee', 12, 2)->default(5000.00); // Biaya layanan aplikasi (flat Rp 5.000)
            $table->decimal('total_amount', 12, 2); // base_price + transport_fee + platform_fee
            $table->decimal('commission_amount', 12, 2)->default(0.00); // 15% dari base_price
            $table->decimal('nurse_earnings', 12, 2)->default(0.00); // 85% base_price + transport_fee
            
            // Status Pesanan & Pembayaran Escrow
            $table->string('status', 40)->default('menunggu_konfirmasi'); 
            // 'menunggu_konfirmasi', 'diterima', 'dalam_perjalanan', 'tiba', 'tindakan_berlangsung', 'selesai', 'dibatalkan'
            
            $table->string('payment_status', 30)->default('unpaid'); 
            // 'unpaid', 'paid', 'refunded'
            
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            // Indexing untuk performa kueri pesanan aktif & riwayat
            $table->index(['patient_id', 'status']);
            $table->index(['nurse_id', 'status']);
            $table->index('scheduled_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
