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
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('total_amount')->nullable()->after('gross_amount');
            $table->unsignedBigInteger('admin_fee')->default(0)->after('total_amount');
            $table->unsignedBigInteger('nurse_earning')->default(0)->after('admin_fee');
            $table->decimal('fee_percent', 5, 2)->default(15.00)->after('nurse_earning');
            $table->string('status', 30)->default('pending')->index()->after('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'total_amount',
                'admin_fee',
                'nurse_earning',
                'fee_percent',
                'status',
            ]);
        });
    }
};
