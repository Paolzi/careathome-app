<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-800 text-xl border border-emerald-200">
                    💳
                </span>
                <div>
                    <h2 class="font-extrabold text-base text-slate-900 leading-tight">
                        Riwayat Finansial &amp; Mutasi Saldo
                    </h2>
                    <p class="text-xs text-slate-500">
                        Buku kas digital, pembagian bagi hasil otomatis (85% + transport), dan status penarikan dana.
                    </p>
                </div>
            </div>

            <a href="{{ route('perawat.dashboard') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                &larr; Kembali ke Panel Kerja
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- 3 Financial Stat Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div class="bg-gradient-to-br from-emerald-600 via-teal-600 to-emerald-700 text-white rounded-3xl p-6 shadow-md shadow-emerald-600/20 space-y-2">
                    <span class="text-[10px] font-bold text-emerald-100 uppercase tracking-wider block">Saldo Dompet Tersedia</span>
                    <span class="text-3xl font-black block">Rp {{ number_format($nurseWallet->balance, 0, ',', '.') }}</span>
                    <span class="text-xs text-emerald-100 block">Siap diajukan penarikan kapan saja</span>
                </div>

                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-2">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Saldo Pending (Proses Payout)</span>
                    <span class="text-3xl font-black text-amber-600 block">Rp {{ number_format($nurseWallet->pending_balance, 0, ',', '.') }}</span>
                    <span class="text-xs text-slate-500 block">Sedang dalam verifikasi transfer admin</span>
                </div>

                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-2">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Mutasi Transaksi</span>
                    <span class="text-3xl font-black text-slate-900 block">{{ $transactions->total() }}</span>
                    <span class="text-xs text-emerald-600 font-semibold block">Tercatat secara otomatis &amp; akuntabel</span>
                </div>
            </div>

            <!-- Tabel Riwayat Mutasi Dompet -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden space-y-4">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 tracking-tight">Log Mutasi Finansial Perawat</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Seluruh arus pendapatan tindakan medis dan penarikan saldo.</p>
                    </div>
                </div>

                @if ($transactions->isEmpty())
                    <div class="p-12 text-center text-xs text-slate-500 space-y-2">
                        <span class="text-3xl block">📋</span>
                        <p class="font-bold">Belum ada riwayat transaksi finansial yang tercatat.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead class="bg-slate-900 text-white text-[10px] uppercase font-bold tracking-wider">
                                <tr>
                                    <th class="py-3.5 px-4">Tanggal &amp; Waktu</th>
                                    <th class="py-3.5 px-4">Tipe Transaksi</th>
                                    <th class="py-3.5 px-4">Keterangan / No. Order</th>
                                    <th class="py-3.5 px-4 text-right">Potongan Platform (15%)</th>
                                    <th class="py-3.5 px-4 text-right">Nominal Bersih Nakes</th>
                                    <th class="py-3.5 px-4 text-right">Saldo Akhir</th>
                                    <th class="py-3.5 px-4 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                                @foreach ($transactions as $tx)
                                    <tr class="hover:bg-slate-50/70 transition">
                                        <td class="py-3.5 px-4 whitespace-nowrap text-slate-500 text-[11px]">
                                            {{ $tx->created_at->format('d M Y, H:i') }}
                                        </td>
                                        <td class="py-3.5 px-4 whitespace-nowrap">
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold {{ in_array($tx->type, ['income', 'order_income']) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                                {{ in_array($tx->type, ['income', 'order_income']) ? '+ Pendapatan Medis' : '- Penarikan Saldo' }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="font-bold text-slate-900 block">{{ $tx->description ?: 'Mutasi dompet nakes' }}</span>
                                        </td>
                                        <td class="py-3.5 px-4 text-right text-slate-400 whitespace-nowrap">
                                            {{ $tx->platform_fee > 0 ? 'Rp ' . number_format($tx->platform_fee, 0, ',', '.') : '-' }}
                                        </td>
                                        <td class="py-3.5 px-4 text-right font-black whitespace-nowrap {{ in_array($tx->type, ['income', 'order_income']) ? 'text-emerald-700' : 'text-slate-800' }}">
                                            {{ in_array($tx->type, ['income', 'order_income']) ? '+ ' : '- ' }} Rp {{ number_format(abs($tx->net_amount ?: $tx->amount), 0, ',', '.') }}
                                        </td>
                                        <td class="py-3.5 px-4 text-right font-bold text-slate-900 whitespace-nowrap">
                                            Rp {{ number_format($tx->balance_after, 0, ',', '.') }}
                                        </td>
                                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">
                                                {{ ucfirst($tx->status ?: 'completed') }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4 border-t border-slate-100">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>

