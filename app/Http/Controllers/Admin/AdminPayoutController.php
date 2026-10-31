<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PayoutRequest;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminPayoutController extends Controller
{
    /**
     * [SCRUM-59] Manajemen Otorisasi Payout & Daftar Permohonan.
     */
    public function index(Request $request): View
    {
        $query = PayoutRequest::with(['nurseUser', 'processor'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payouts = $query->paginate(15)->withQueryString();

        $stats = [
            'pending' => PayoutRequest::where('status', 'pending')->count(),
            'transferred' => PayoutRequest::where('status', 'transferred')->count(),
            'rejected' => PayoutRequest::where('status', 'rejected')->count(),
            'total_pending_amount' => (int) PayoutRequest::where('status', 'pending')->sum('amount'),
        ];

        return view('admin.payouts.index', compact('payouts', 'stats'));
    }

    /**
     * Setujui payout & input berkas bukti transfer.
     */
    public function transfer(Request $request, PayoutRequest $payout): RedirectResponse
    {
        $request->validate([
            'proof_file' => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'transferred_at' => 'required|date',
        ]);

        if ($payout->status !== 'pending') {
            return back()->with('error', 'Permintaan penarikan ini sudah diproses sebelumnya.');
        }

        DB::transaction(function () use ($request, $payout) {
            $path = $request->file('proof_file')->store('payout_proofs', 'public');

            $payout->update([
                'status' => 'transferred',
                'proof_path' => $path,
                'transferred_at' => $request->transferred_at,
                'processed_by' => auth()->id(),
            ]);

            // Ambil / pastikan instance wallet perawat tersedia
            $nurseUser = $payout->nurseUser ?? $payout->nurse;
            $wallet = $nurseUser?->wallet ?? Wallet::getOrCreateForUser($payout->nurse_user_id);

            // Catat transaksi mutasi payout_paid
            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'payout_paid',
                'amount' => $payout->amount,
                'balance_after' => $wallet->balance,
                'payout_request_id' => $payout->id,
                'description' => "Pencairan dana payout #{$payout->id} berhasil ditransfer ke {$payout->bank_name} {$payout->account_number}",
            ]);

            AuditLog::log(
                'payout_transferred',
                $payout,
                ['status' => 'pending'],
                ['status' => 'transferred', 'proof_path' => $path]
            );
        });

        return back()->with('success', 'Bukti transfer berhasil disimpan dan status penarikan diubah menjadi Ditransfer.');
    }

    /**
     * Tolak permohonan payout & kembalikan saldo tertahan ke perawat.
     */
    public function reject(Request $request, PayoutRequest $payout): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($payout->status !== 'pending') {
            return back()->with('error', 'Permintaan penarikan ini sudah diproses sebelumnya.');
        }

        DB::transaction(function () use ($request, $payout) {
            $wallet = Wallet::where('user_id', $payout->nurse_user_id)->lockForUpdate()->first();
            if (!$wallet) {
                $wallet = Wallet::getOrCreateForUser($payout->nurse_user_id);
            }

            // Kembalikan saldo yang ditahan
            $wallet->balance += $payout->amount;
            $wallet->save();

            $payout->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'processed_by' => auth()->id(),
            ]);

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'payout_release',
                'amount' => $payout->amount,
                'balance_after' => $wallet->balance,
                'payout_request_id' => $payout->id,
                'description' => "Pengembalian dana penarikan #{$payout->id} yang ditolak: {$request->rejection_reason}",
            ]);

            AuditLog::log(
                'payout_rejected',
                $payout,
                ['status' => 'pending'],
                ['status' => 'rejected', 'reason' => $request->rejection_reason]
            );
        });

        return back()->with('success', 'Permintaan penarikan ditolak dan saldo perawat telah dikembalikan.');
    }
}
