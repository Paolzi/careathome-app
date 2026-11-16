<x-app-layout>
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
                    <div class="p-12 text-center text-xs text-[#78956A] space-y-2">
                        <div class="w-12 h-12 rounded-2xl bg-[#F9F4EA] border border-[#78956A]/40 flex items-center justify-center mx-auto text-[#78956A]">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                            </svg>
                        </div>
                        <p class="font-bold text-[#46571A]">Belum ada riwayat transaksi finansial yang tercatat.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead class="bg-[#78956A] text-[#F9F4EA] text-[10px] uppercase font-bold tracking-wider">
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

