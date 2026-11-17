@extends('admin.layout')

@section('title', 'Monitoring Rekening Escrow & Saldo')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-[#46571A] tracking-tight">Monitoring Escrow & Audit Saldo</h1>
            <p class="text-[#78956A] text-sm mt-1">Audit mutasi wallet perawat & platform secara real-time dengan rekonsiliasi otomatis.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.escrow.export', request()->all()) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-[#78956A] text-[#F9F4EA] rounded-xl text-sm font-semibold hover:bg-[#78956A] transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export CSV
            </a>
        </div>
    </div>

    <!-- Reconciliation Alert Banner -->
    @if($isReconciled)
        <div class="p-4 bg-[#D1EB8F]/45 border border-[#78956A]/50 rounded-2xl flex items-center gap-3 text-[#46571A] text-sm">
            <svg class="w-5 h-5 text-[#78956A] flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <div>
                <span class="font-bold">Audit Rekonsiliasi Sukses:</span> Total saldo seluruh wallet (Rp {{ number_format($totalWalletBalance, 0, ',', '.') }}) presisi 100% sama dengan total aliran transaksi masuk dikurangi keluar.
            </div>
        </div>
    @else
        <div class="p-4 bg-[#FF9ABC]/25 border border-[#FF9ABC] rounded-2xl flex items-center gap-3 text-[#46571A] text-sm animate-pulse">
            <svg class="w-5 h-5 text-[#46571A] flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            <div>
                <span class="font-bold">PERINGATAN ANOMALI:</span> Terdapat selisih antara saldo wallet (Rp {{ number_format($totalWalletBalance, 0, ',', '.') }}) dengan hasil kalkulasi riwayat transaksi (Rp {{ number_format($expectedNetBalance, 0, ',', '.') }}). Segera periksa integritas ledger!
            </div>
        </div>
    @endif

    <!-- Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-3xl border border-[#78956A]/50 shadow-sm">
            <div class="text-xs font-bold text-[#78956A] uppercase tracking-wider">Total Saldo Tersedia</div>
            <div class="text-2xl font-black text-[#46571A] mt-2">Rp {{ number_format($totalWalletBalance, 0, ',', '.') }}</div>
            <div class="text-xs text-[#78956A] mt-1">Gabungan semua wallet aktif di sistem</div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-[#78956A]/50 shadow-sm">
            <div class="text-xs font-bold text-[#78956A] uppercase tracking-wider">Saldo Tertahan (Pending Payout)</div>
            <div class="text-2xl font-black text-[#78956A] mt-2">Rp {{ number_format($totalPendingPayout, 0, ',', '.') }}</div>
            <div class="text-xs text-[#78956A] mt-1">Menunggu persetujuan transfer admin</div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-[#78956A]/50 shadow-sm">
            <div class="text-xs font-bold text-[#78956A] uppercase tracking-wider">Total Pendapatan Seumur Hidup</div>
            <div class="text-2xl font-black text-[#78956A] mt-2">Rp {{ number_format($totalLifetimeIncome, 0, ',', '.') }}</div>
            <div class="text-xs text-[#78956A] mt-1">Akumulasi seluruh transaksi pesanan</div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white p-6 rounded-3xl border border-[#78956A]/50 shadow-sm">
        <form method="GET" action="{{ route('admin.escrow.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-xs font-bold text-[#78956A] uppercase mb-1">Tipe Transaksi</label>
                <select name="type" class="w-full text-sm border-[#78956A] rounded-xl focus:border-[#78956A] focus:ring-[#78956A]">
                    <option value="">Semua Tipe</option>
                    <option value="order_income" {{ request('type') == 'order_income' ? 'selected' : '' }}>Order Income</option>
                    <option value="payout_hold" {{ request('type') == 'payout_hold' ? 'selected' : '' }}>Payout Hold</option>
                    <option value="payout_release" {{ request('type') == 'payout_release' ? 'selected' : '' }}>Payout Release</option>
                    <option value="payout_paid" {{ request('type') == 'payout_paid' ? 'selected' : '' }}>Payout Paid</option>
                    <option value="refund_reversal" {{ request('type') == 'refund_reversal' ? 'selected' : '' }}>Refund Reversal</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-[#78956A] uppercase mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full text-sm border-[#78956A] rounded-xl focus:border-[#78956A] focus:ring-[#78956A]">
            </div>
            <div>
                <label class="block text-xs font-bold text-[#78956A] uppercase mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full text-sm border-[#78956A] rounded-xl focus:border-[#78956A] focus:ring-[#78956A]">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-[#FF9ABC] text-[#46571A] rounded-xl text-sm font-bold hover:bg-[#FF9ABC] transition">Filter</button>
                <a href="{{ route('admin.escrow.index') }}" class="px-3 py-2 bg-[#F9F4EA] text-[#46571A]/85 rounded-xl text-sm font-semibold hover:bg-[#D1EB8F]/50 transition">Reset</a>
            </div>
        </form>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-3xl border border-[#78956A]/50 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-[#78956A]/50 flex items-center justify-between">
            <h2 class="text-base font-bold text-[#46571A]">Buku Besar Transaksi Wallet (Append-Only Ledger)</h2>
            <span class="text-xs text-[#78956A]">Total data: {{ $transactions->total() }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-[#F9F4EA] border-b border-[#78956A]/50 text-[#78956A] text-xs font-bold uppercase tracking-wider">
                        <th class="p-4 pl-6">ID & Waktu</th>
                        <th class="p-4">Pemilik Wallet</th>
                        <th class="p-4">Tipe</th>
                        <th class="p-4 text-right">Nominal</th>
                        <th class="p-4 text-right">Saldo Akhir</th>
                        <th class="p-4 pr-6">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#78956A]/30">
                    @forelse($transactions as $trx)
                        <tr class="hover:bg-[#F9F4EA] transition">
                            <td class="p-4 pl-6 font-mono text-xs text-[#46571A]/85">
                                <div class="font-bold">#{{ $trx->id }}</div>
                                <div class="text-[#78956A] text-[11px]">{{ $trx->created_at->format('d/m/Y H:i') }}</div>
                            </td>
                            <td class="p-4">
                                <div class="font-semibold text-[#46571A]">{{ $trx->wallet->user->name ?? 'User #' . $trx->wallet->user_id }}</div>
                                <div class="text-xs text-[#78956A]">{{ $trx->wallet->user->email ?? '-' }}</div>
                            </td>
                            <td class="p-4">
                                @if(in_array($trx->type, ['order_income', 'payout_release']))
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-[#D1EB8F] text-[#46571A]">
                                        + {{ $trx->type }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-[#FF9ABC]/40 text-[#46571A]">
                                        - {{ $trx->type }}
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-right font-bold {{ in_array($trx->type, ['order_income', 'payout_release']) ? 'text-[#78956A]' : 'text-[#46571A]' }}">
                                Rp {{ number_format($trx->amount, 0, ',', '.') }}
                            </td>
                            <td class="p-4 text-right font-mono font-semibold text-[#46571A]">
                                Rp {{ number_format($trx->balance_after, 0, ',', '.') }}
                            </td>
                            <td class="p-4 pr-6 text-xs text-[#78956A] max-w-xs truncate" title="{{ $trx->description }}">
                                {{ $trx->description }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-[#78956A]">Tidak ada transaksi ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-[#78956A]/50">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection
