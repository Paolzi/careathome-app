<x-admin-layout>
    <x-slot name="header">
        Financial &amp; GMV Dashboard (SCRUM-56)
    </x-slot>

    <!-- 1. KARTU RINGKASAN FINANSIAL & OPERASIONAL -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
        
        <!-- Total GMV -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total GMV Transaksi</span>
                <span class="text-base">💰</span>
            </div>
            <div class="text-xl font-black text-slate-900">
                Rp {{ number_format($totalGmv, 0, ',', '.') }}
            </div>
            <span class="text-[10px] text-emerald-600 font-semibold block">Total pembayaran lunas</span>
        </div>

        <!-- Platform Revenue (15%) -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-indigo-500 uppercase tracking-wider">Pendapatan Platform (15%)</span>
                <span class="text-base">🏢</span>
            </div>
            <div class="text-xl font-black text-indigo-700">
                Rp {{ number_format($totalPlatformFee, 0, ',', '.') }}
            </div>
            <span class="text-[10px] text-slate-400 block">Biaya operasional platform</span>
        </div>

        <!-- Nurse Earnings (85%) -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Porsi Nakes (85%)</span>
                <span class="text-base">🩺</span>
            </div>
            <div class="text-xl font-black text-emerald-700">
                Rp {{ number_format($totalNurseEarnings, 0, ',', '.') }}
            </div>
            <span class="text-[10px] text-slate-400 block">Terkredit ke dompet perawat</span>
        </div>

        <!-- Pesanan Selesai -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pesanan Selesai</span>
                <span class="text-base">✅</span>
            </div>
            <div class="text-xl font-black text-slate-900">
                {{ $completedOrdersCount }} <span class="text-xs font-normal text-slate-400">/ {{ $totalOrdersCount }} total</span>
            </div>
            <span class="text-[10px] text-slate-400 block">Tindakan homecare berhasil</span>
        </div>

        <!-- Perawat Aktif -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Perawat Aktif (10 KM)</span>
                <span class="text-base">🛰️</span>
            </div>
            <div class="text-xl font-black text-slate-900">
                {{ $activeNursesCount }} <span class="text-xs font-normal text-slate-400">/ {{ $totalNursesCount }} mitra</span>
            </div>
            <span class="text-[10px] text-emerald-600 font-semibold block">Online &amp; update GPS &lt; 15 mnt</span>
        </div>

    </div>

    <!-- 2. GRAFIK TREN GMV & PENDAPATAN (CHART.JS) -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Grafik Tren GMV &amp; Pendapatan Platform</h3>
                <p class="text-xs text-slate-500">Pertumbuhan transaksi dan pendapatan platform dalam rentang waktu yang dipilih.</p>
            </div>

            <!-- Filter Rentang Waktu -->
            <form action="{{ route('admin.dashboard') }}" method="GET" class="flex items-center gap-2">
                <span class="text-xs text-slate-500 font-medium">Rentang:</span>
                <select name="days" onchange="this.form.submit()"
                    class="text-xs rounded-xl border-slate-200 font-semibold text-slate-700 focus:ring-indigo-500 focus:border-indigo-500 py-1.5 px-3">
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

    <!-- 3. DUA TABEL: TRANSAKSI TERBARU & TOP PERAWAT -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Tabel Transaksi Pembayaran Terbaru -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">Transaksi Pembayaran Terbaru</h3>
                <a href="{{ route('admin.escrow.index') }}" class="text-xs font-bold text-indigo-600 hover:underline">Lihat Semua &rarr;</a>
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
                                <th class="p-2.5">Porsi Platform (15%)</th>
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

        <!-- Tabel Top Perawat Berdasarkan Saldo/Pendapatan -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">Top Mitra Perawat Berdasarkan Pendapatan</h3>
                <a href="{{ route('admin.nurses.index') }}" class="text-xs font-bold text-indigo-600 hover:underline">Kelola Nakes &rarr;</a>
            </div>

            @if ($topNurses->isEmpty())
                <p class="text-xs text-slate-400 italic py-4 text-center">Belum ada mitra perawat terdaftar.</p>
            @else
                <div class="space-y-3">
                    @foreach ($topNurses as $nurse)
                        <div class="p-3 rounded-2xl border border-slate-100 flex items-center justify-between text-xs hover:border-indigo-300 transition">
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
                                    <span class="text-[10px] text-slate-400">Status Legalitas: {{ ucfirst($nurse['verification_status']) }} • Rating: ⭐ {{ number_format($nurse['rating_avg'], 1) }}</span>
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
