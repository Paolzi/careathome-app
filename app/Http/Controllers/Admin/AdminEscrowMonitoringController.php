<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PayoutRequest;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminEscrowMonitoringController extends Controller
{
    /**
     * [SCRUM-58] Monitoring Rekening Escrow, Saldo Dompet & Audit Mutasi.
     */
    public function index(Request $request): View
    {
        // 1. Hitung total saldo semua wallet
        $wallets = Wallet::with('user.roles')->get();
        $totalWalletBalance = (int) $wallets->sum('balance');

        // 2. Total pending payout (dana yang sedang ditahan untuk ditarik)
        $totalPendingPayout = (int) PayoutRequest::where('status', 'pending')->sum('amount');

        // 3. Total pendapatan seumur hidup yang masuk ke sistem (order_income)
        $totalLifetimeIncome = (int) WalletTransaction::where('type', 'order_income')->sum('amount');

        // 4. Rekonsiliasi ledger:
        // Mutasi yang menambah saldo wallet: order_income, payout_release
        // Mutasi yang mengurangi saldo wallet: refund_reversal, payout_hold
        $totalCredits = (int) WalletTransaction::whereIn('type', ['order_income', 'payout_release'])->sum('amount');
        $totalDebits = (int) WalletTransaction::whereIn('type', ['refund_reversal', 'payout_hold'])->sum('amount');
        $expectedNetBalance = $totalCredits - $totalDebits;

        $isReconciled = ($totalWalletBalance === $expectedNetBalance);

        // 5. Query transaksi dengan filter
        $query = WalletTransaction::with(['wallet.user', 'payment', 'payoutRequest'])->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('user_id')) {
            $userId = (int) $request->user_id;
            $query->whereHas('wallet', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->paginate(20)->withQueryString();

        return view('admin.escrow.index', compact(
            'wallets',
            'totalWalletBalance',
            'totalPendingPayout',
            'totalLifetimeIncome',
            'expectedNetBalance',
            'isReconciled',
            'transactions'
        ));
    }

    /**
     * Export data buku besar transaksi wallet ke format CSV.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $fileName = 'wallet_transactions_' . date('Ymd_His') . '.csv';

        $query = WalletTransaction::with(['wallet.user'])->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        AuditLog::log('export_csv', null, null, ['filter' => $request->all()]);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'ID Transaksi',
                'Tanggal',
                'Nama Pemilik',
                'Email',
                'Tipe Transaksi',
                'Nominal (Rp)',
                'Saldo Setelahnya (Rp)',
                'Keterangan',
                'Payment Ref',
                'Payout Ref'
            ]);

            $query->chunk(500, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->id,
                        $row->created_at->format('Y-m-d H:i:s'),
                        $row->wallet->user->name ?? 'N/A',
                        $row->wallet->user->email ?? 'N/A',
                        $row->type,
                        $row->amount,
                        $row->balance_after,
                        $row->description,
                        $row->payment_id ?? '-',
                        $row->payout_request_id ?? '-',
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }
}
