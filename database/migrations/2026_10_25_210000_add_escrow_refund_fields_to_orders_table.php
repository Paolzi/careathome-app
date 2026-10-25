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
            $table->string('refund_status', 30)->default('none')->after('payment_status'); // 'none', 'pending', 'refunded'
            $table->decimal('refund_amount', 12, 2)->default(0.00)->after('refund_status');
            $table->timestamp('refunded_at')->nullable()->after('refund_amount');
            $table->text('refund_notes')->nullable()->after('refunded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['refund_status', 'refund_amount', 'refunded_at', 'refund_notes']);
        });
    }
};
