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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('order_number', 50)->index();
            $table->string('transaction_id', 100)->nullable()->index();
            $table->string('payment_method', 50)->default('bca_va'); // bca_va, mandiri_va, bni_va, bri_va, qris, dll.
            $table->string('payment_type', 50)->default('bank_transfer'); // bank_transfer, qris, cstore, credit_card
            $table->string('bank', 30)->nullable();
            $table->string('va_number', 60)->nullable();
            $table->decimal('gross_amount', 12, 2);
            $table->string('currency', 10)->default('IDR');
            
            // Status Pembayaran & Escrow
            $table->string('payment_status', 30)->default('pending')->index(); 
            // pending, settlement, paid, expire, cancel, refunded, failed
            
            $table->string('escrow_status', 30)->default('unpaid')->index(); 
            // unpaid, held (terkunci aman di rekening penampung), released (diteruskan ke nakes), refunded (dikembalikan ke pasien)
            
            // Midtrans Snap Token & URL
            $table->string('snap_token', 255)->nullable();
            $table->string('snap_redirect_url', 500)->nullable();
            $table->json('payment_payload')->nullable(); // Metadata response Midtrans
            
            // Timestamp Pembayaran & Refund Escrow
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('settlement_time')->nullable();
            $table->decimal('refund_amount', 12, 2)->default(0.00);
            $table->timestamp('refunded_at')->nullable();
            $table->text('refund_notes')->nullable();
            
            $table->timestamps();

            // Indexing kueri finansial & status
            $table->index(['order_id', 'payment_status']);
            $table->index(['escrow_status', 'payment_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
