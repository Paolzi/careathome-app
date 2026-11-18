<?php

namespace App\Console\Commands;

use App\Services\NurseNoShowPenaltyService;
use Illuminate\Console\Command;

class CheckNurseNoShowPenalties extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'careathome:check-noshow-penalties';

    /**
     * The console command description.
     */
    protected $description = 'Periksa pesanan yang terlambat > 1 jam dari jadwal dan terapkan auto-suspend 1 hari serta auto-refund secara real-time di background.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $processed = NurseNoShowPenaltyService::processOverdueOrders(true);
        $this->info("Selesai memeriksa pesanan terlambat. Diproses: {$processed} pesanan.");

        return self::SUCCESS;
    }
}
