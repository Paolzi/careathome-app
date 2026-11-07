<x-admin-layout>
    <x-slot name="header">
        Dashboard Finansial &amp; Metrik GMV
    </x-slot>

    <!-- 1. 4 STAT CARDS UTAMA KOKPIT FINANSIAL & GMV -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- Stat 1: Total GMV Transaksi Bruto -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-3 relative overflow-hidden group hover:border-emerald-500 transition">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Total Perputaran Bruto (GMV)</span>
                <span class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-800 flex items-center justify-center text-lg border border-emerald-100">
                    💰
                </span>
            </div>
            <div>
                <span class="text-2xl font-black text-slate-900 tracking-tight block">
                    Rp {{ number_format($totalGmv, 0, ',', '.') }}
                </span>
                <span class="text-[11px] text-emerald-600 font-bold flex items-center gap-1 mt-0.5">
                    <span>↑ 100% Real-Time</span>
                    <span class="text-slate-400 font-normal">&bull; Seluruh transaksi pesanan</span>
                </span>
            </div>
        </div>

        <!-- Stat 2: Pendapatan Bersih Platform (15%) -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-3 relative overflow-hidden group hover:border-indigo-500 transition">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold text-indigo-500 uppercase tracking-wider">Pendapatan Platform (15% Komisi)</span>
                <span class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-700 flex items-center justify-center text-lg border border-indigo-100">
                    🏢
                </span>
            </div>
            <div>
                <span class="text-2xl font-black text-indigo-700 tracking-tight block">
                    Rp {{ number_format($totalPlatformFee, 0, ',', '.') }}
                </span>
                <span class="text-[11px] text-slate-500 flex items-center gap-1 mt-0.5">
                    <span>Kas pendapatan bersih sistem</span>
                </span>
            </div>
        </div>

        <!-- Stat 3: Volume Transaksi Berhasil -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-3 relative overflow-hidden group hover:border-sky-500 transition">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold text-sky-600 uppercase tracking-wider">Volume Transaksi Berhasil</span>
                <span class="w-10 h-10 rounded-2xl bg-sky-50 text-sky-700 flex items-center justify-center text-lg border border-sky-100">
                    ⚡
                </span>
            </div>
            <div>
                <span class="text-2xl font-black text-slate-900 tracking-tight block">
                    {{ $successfulTransactionsCount }} <span class="text-xs font-normal text-slate-400">Transaksi Lunas</span>
                </span>
                <span class="text-[11px] text-slate-500 flex items-center gap-1 mt-0.5">
                    <span>{{ $completedOrdersCount }} tindakan selesai dari {{ $totalOrdersCount }} total</span>
                </span>
            </div>
        </div>

        <!-- Stat 4: Total Dana Disalurkan ke Perawat (85% + Transport) -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-3 relative overflow-hidden group hover:border-teal-500 transition">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold text-emerald-600 uppercase tracking-wider">Disalurkan ke Nakes (85% + Trp)</span>
                <span class="w-10 h-10 rounded-2xl bg-teal-50 text-teal-700 flex items-center justify-center text-lg border border-teal-100">
                    🩺
                </span>
            </div>
            <div>
                <span class="text-2xl font-black text-emerald-700 tracking-tight block">
                    Rp {{ number_format($totalNurseEarnings, 0, ',', '.') }}
                </span>
                <span class="text-[11px] text-emerald-600 font-bold flex items-center gap-1 mt-0.5">
                    <span>Terkredit otomatis ke dompet nakes</span>
                </span>
            </div>
        </div>

    </div>

    <!-- 2. TABEL MONITORING MUTASI TRANSAKSI REAL-TIME (AUTOMATED LEDGER) -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden space-y-4">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <span>Monitoring Mutasi Transaksi Real-Time</span>
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 font-bold">
                        Bypass Otorisasi Manual
                    </span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Log seluruh arus masuk dana dari pembayaran pasien hingga distribusi instan ke kas platform &amp; dompet nakes.
                </p>
            </div>

            <a href="{{ route('admin.escrow.index') }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-900 flex items-center gap-1">
                <span>Buka Rekonsiliasi Escrow Lengkap &rarr;</span>
            </a>
        </div>

        @if ($recentMutations->isEmpty())
            <div class="p-12 text-center text-xs text-slate-400 space-y-2">
                <span class="text-3xl block">📋</span>
                <p class="font-bold">Belum ada mutasi transaksi otomatis yang tercatat.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white text-[10px] uppercase font-bold tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Waktu</th>
                            <th class="py-3 px-4">No. Pesanan / ID</th>
                            <th class="py-3 px-4">Alur Mutasi</th>
                            <th class="py-3 px-4">Penerima Alokasi</th>
                            <th class="py-3 px-4 text-right">Nominal Transaksi</th>
                            <th class="py-3 px-4 text-right">Platform Fee (15%)</th>
                            <th class="py-3 px-4 text-right">Saldo Sesudah</th>
                            <th class="py-3 px-4 text-center">Status Distribusi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                        @foreach ($recentMutations as $mut)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3.5 px-4 text-[11px] text-slate-500 whitespace-nowrap">
                                    {{ $mut->created_at->format('d M Y, H:i:s') }}
                                </td>
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-900 whitespace-nowrap">
                                    {{ $mut->payment?->order_number ?? ($mut->order_id ? 'ORD-' . $mut->order_id : 'TX-' . $mut->id) }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-bold text-slate-900 block truncate max-w-[220px]" title="{{ $mut->description }}">
                                        {{ $mut->description ?: 'Mutasi transaksi otomatis' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @if ($mut->wallet?->user)
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs">👤</span>
                                            <span class="font-bold text-slate-900 text-xs">{{ $mut->wallet->user->name }}</span>
                                            <span class="text-[9px] px-1.5 py-0.2 rounded font-bold uppercase {{ $mut->wallet->user->hasRole('admin') ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                                                {{ $mut->wallet->user->hasRole('admin') ? 'Kas Admin' : 'Nakes' }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-slate-400 italic">Sistem Platform</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right font-black whitespace-nowrap {{ in_array($mut->type, ['income', 'order_income']) ? 'text-emerald-700' : 'text-slate-800' }}">
                                    {{ in_array($mut->type, ['income', 'order_income']) ? '+ ' : '- ' }} Rp {{ number_format(abs($mut->net_amount ?: $mut->amount), 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 text-right text-slate-500 whitespace-nowrap">
                                    {{ $mut->platform_fee > 0 ? 'Rp ' . number_format($mut->platform_fee, 0, ',', '.') : '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-bold text-slate-900 whitespace-nowrap">
                                    Rp {{ number_format($mut->balance_after, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center gap-1 mx-auto">
                                        <span>✓</span>
                                        <span>Terkreditasi Instan</span>
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- 3. GRAFIK TREN GMV & PENDAPATAN (CHART.JS) -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-sm space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-sm font-extrabold text-slate-900">Grafik Tren GMV &amp; Pendapatan Platform</h3>
                <p class="text-xs text-slate-500">Pertumbuhan transaksi kotor dan komisi platform dalam rentang waktu yang dipilih.</p>
            </div>

            <!-- Filter Rentang Waktu -->
            <form action="{{ route('admin.dashboard') }}" method="GET" class="flex items-center gap-2">
                <span class="text-xs text-slate-500 font-medium">Rentang:</span>
                <select name="days" onchange="this.form.submit()"
                    class="text-xs rounded-xl border-slate-200 font-semibold text-slate-700 focus:ring-emerald-500 focus:border-emerald-500 py-1.5 px-3">
                    <option value="7" {{ $days == 7 ? 'selected' : '' }}>7 Hari Terakhir</option>
                    <option value="30" {{ $days == 30 ? 'selected' : '' }}>30 Hari Terakhir</option>
                    <option value="90" {{ $days == 90 ? 'selected' : '' }}>90 Hari Terakhir</option>
                </select>
            </form>
        </div>

        <div class="h-72 w-full">
            <canvas id="gmvChart"></canvas>
        </div>
    </div>

    <!-- 4. DUA KARTU: TOP NAKES & TRANSAKSI PEMBAYARAN TERBARU -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Tabel Transaksi Pembayaran Terbaru -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">Pembayaran Pesanan Terbaru</h3>
                <span class="text-xs text-slate-400">Total: {{ $recentPayments->count() }}</span>
            </div>

            @if ($recentPayments->isEmpty())
                <p class="text-xs text-slate-400 italic py-4 text-center">Belum ada transaksi pembayaran yang tercatat.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400">
                            <tr>
                                <th class="p-2.5 rounded-l-lg">No. Pesanan</th>
                                <th class="p-2.5">Total GMV</th>
                                <th class="p-2.5">Platform (15%)</th>
                                <th class="p-2.5">Status</th>
                                <th class="p-2.5 rounded-r-lg">Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($recentPayments as $payment)
                                <tr class="hover:bg-slate-50/60">
                                    <td class="p-2.5 font-mono font-bold text-slate-900">{{ $payment->order_number }}</td>
                                    <td class="p-2.5 font-bold text-slate-900">Rp {{ number_format($payment->total_amount ?: $payment->gross_amount, 0, ',', '.') }}</td>
                                    <td class="p-2.5 font-semibold text-indigo-700">Rp {{ number_format($payment->admin_fee ?: round(($payment->total_amount ?: $payment->gross_amount) * 0.15), 0, ',', '.') }}</td>
                                    <td class="p-2.5">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $payment->payment_status === 'paid' ? 'bg-emerald-50 text-emerald-700' : ($payment->payment_status === 'refunded' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700') }}">
                                            {{ ucfirst($payment->payment_status) }}
                                        </span>
                                    </td>
                                    <td class="p-2.5 text-[11px] text-slate-400">{{ $payment->created_at->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Tabel Top Perawat Berdasarkan Pendapatan -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">Top Mitra Perawat Berdasarkan Saldo</h3>
                <a href="{{ route('admin.nurses.index') }}" class="text-xs font-bold text-emerald-700 hover:underline">Kelola Nakes &rarr;</a>
            </div>

            @if ($topNurses->isEmpty())
                <p class="text-xs text-slate-400 italic py-4 text-center">Belum ada mitra perawat terdaftar.</p>
            @else
                <div class="space-y-3">
                    @foreach ($topNurses as $nurse)
                        <div class="p-3 rounded-2xl border border-slate-100 flex items-center justify-between text-xs hover:border-emerald-300 transition">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white font-bold flex items-center justify-center">
                                    {{ strtoupper(substr($nurse['name'], 0, 2)) }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <h4 class="font-bold text-slate-900">{{ $nurse['name'] }}</h4>
                                        <span class="text-[10px] {{ $nurse['is_online'] ? 'text-emerald-500 font-bold' : 'text-slate-400' }}">
                                            {{ $nurse['is_online'] ? '● Online' : '○ Offline' }}
                                        </span>
                                    </div>
                                    <span class="text-[10px] text-slate-400">Status Legalitas: {{ ucfirst($nurse['verification_status']) }} &bull; Rating: ⭐ {{ number_format($nurse['rating_avg'], 1) }}</span>
                                </div>
                            </div>

                            <div class="text-right">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block">Saldo Terkumpul</span>
                                <span class="font-extrabold text-sm text-emerald-700">Rp {{ number_format($nurse['balance'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>

    <!-- Script Chart.js -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('gmvChart').getContext('2d');
            const labels = @json($chartLabels);
            const dataGmv = @json($chartGmv);
            const dataFee = @json($chartPlatformFee);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Total GMV Transaksi (Rp)',
                            data: dataGmv,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2.5,
                            pointRadius: 3,
                        },
                        {
                            label: 'Pendapatan Platform 15% (Rp)',
                            data: dataFee,
                            borderColor: '#6366f1',
                            backgroundColor: 'rgba(99, 102, 241, 0.1)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2,
                            pointRadius: 2.5,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: { family: 'Plus Jakarta Sans', size: 11, weight: 'bold' }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': Rp ' + Number(context.raw).toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + Number(value).toLocaleString('id-ID');
                                },
                                font: { family: 'Plus Jakarta Sans', size: 10 }
                            },
                            grid: { color: '#f1f5f9' }
                        },
                        x: {
                            ticks: { font: { family: 'Plus Jakarta Sans', size: 10 } },
                            grid: { display: false }
                        }
                    }
                }
            });
        });
    </script>
</x-admin-layout>
