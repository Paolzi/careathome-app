<x-admin-layout>
    <x-slot name="header">
        Dashboard Analitik Eksekutif &amp; Kokpit Finansial (WIB)
    </x-slot>

    <!-- HERO BANNER EKSEKUTIF ADMIN -->
    <div class="bg-gradient-to-r from-[#064e3b] via-emerald-900 to-teal-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden border border-emerald-700/50">
        <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-[#10b981]/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
            <div class="space-y-2 max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#10b981]/20 border border-[#10b981]/40 text-emerald-200 text-xs font-bold">
                    <span class="w-2 h-2 rounded-full bg-[#10b981] animate-pulse"></span>
                    <span>PUSAT KENDALI CAREATHOME • ZONA WAKTU WIB ({{ now('Asia/Jakarta')->format('d M Y, H:i') }} WIB)</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Dashboard Analitik &amp; Manajemen Operasional
                </h1>
                <p class="text-xs sm:text-sm text-emerald-100/90 leading-relaxed">
                    Pantau grafik tren pemesanan (<strong>Pesan Sekarang vs Pesan Untuk Nanti</strong>), perbandingan arus kas masuk &amp; penarikan saldo (payout) perawat, distribusi status pesanan, serta verifikasi registrasi manual perawat.
                </p>
            </div>

            <!-- Filter Rentang Waktu Global -->
            <form action="{{ route('admin.dashboard') }}" method="GET" class="bg-white/10 backdrop-blur-md p-4 rounded-2xl border border-white/15 flex flex-col gap-2 min-w-[210px]">
                <label class="text-[10px] uppercase font-extrabold text-emerald-200 tracking-wider">Rentang Analitik Grafik</label>
                <select name="days" onchange="this.form.submit()"
                    class="text-xs rounded-xl border-white/20 bg-white text-slate-900 font-extrabold focus:ring-[#10b981] focus:border-[#10b981] py-2 px-3 cursor-pointer">
                    <option value="7" {{ $days == 7 ? 'selected' : '' }}>7 Hari Terakhir</option>
                    <option value="14" {{ $days == 14 ? 'selected' : '' }}>14 Hari Terakhir</option>
                    <option value="30" {{ $days == 30 ? 'selected' : '' }}>30 Hari Terakhir</option>
                    <option value="90" {{ $days == 90 ? 'selected' : '' }}>90 Hari Terakhir</option>
                </select>
            </form>
        </div>
    </div>

    <!-- 1. 4 STAT CARDS UTAMA KOKPIT FINANSIAL & OPERASIONAL -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- Stat 1: Total GMV Transaksi Bruto -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-3 relative overflow-hidden group hover:border-[#10b981] transition">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Total Pendapatan Masuk (GMV)</span>
                <span class="w-10 h-10 rounded-2xl bg-emerald-50 text-[#064e3b] flex items-center justify-center text-lg border border-emerald-100">
                    💰
                </span>
            </div>
            <div>
                <span class="text-2xl font-black text-slate-900 tracking-tight block">
                    Rp {{ number_format($totalGmv, 0, ',', '.') }}
                </span>
                <span class="text-[11px] text-[#064e3b] font-bold flex items-center gap-1 mt-0.5">
                    <span>Komisi Platform (15%): Rp {{ number_format($totalPlatformFee, 0, ',', '.') }}</span>
                </span>
            </div>
        </div>

        <!-- Stat 2: Total Saldo & Payout Perawat -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-3 relative overflow-hidden group hover:border-[#10b981] transition">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold text-[#064e3b] uppercase tracking-wider">Hak Bersih &amp; Payout Perawat</span>
                <span class="w-10 h-10 rounded-2xl bg-teal-50 text-teal-800 flex items-center justify-center text-lg border border-teal-100">
                    💸
                </span>
            </div>
            <div>
                <span class="text-2xl font-black text-[#064e3b] tracking-tight block">
                    Rp {{ number_format($totalNurseEarnings, 0, ',', '.') }}
                </span>
                <span class="text-[11px] text-slate-500 flex items-center gap-1 mt-0.5">
                    <span>Total ditarik (Payout): <strong class="text-rose-600">Rp {{ number_format($totalPayoutWithdrawn ?? 0, 0, ',', '.') }}</strong></span>
                </span>
            </div>
        </div>

        <!-- Stat 3: Volume Pesanan & Transaksi -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-3 relative overflow-hidden group hover:border-[#10b981] transition">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold text-sky-600 uppercase tracking-wider">Volume Layanan Homecare</span>
                <span class="w-10 h-10 rounded-2xl bg-sky-50 text-sky-700 flex items-center justify-center text-lg border border-sky-100">
                    🩺
                </span>
            </div>
            <div>
                <span class="text-2xl font-black text-slate-900 tracking-tight block">
                    {{ $totalOrdersCount }} <span class="text-xs font-normal text-slate-400">Total Pesanan</span>
                </span>
                <span class="text-[11px] text-slate-500 flex items-center gap-1 mt-0.5">
                    <span><strong>{{ $completedOrdersCount }}</strong> selesai &bull; <strong>{{ $successfulTransactionsCount }}</strong> lunas</span>
                </span>
            </div>
        </div>

        <!-- Stat 4: Verifikasi Registrasi Perawat -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-3 relative overflow-hidden group hover:border-[#10b981] transition">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold text-amber-600 uppercase tracking-wider">Mitra Perawat Terdaftar</span>
                <span class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center text-lg border border-amber-100">
                    🛡️
                </span>
            </div>
            <div>
                <span class="text-2xl font-black text-slate-900 tracking-tight block">
                    {{ $verifiedNursesCount }} <span class="text-xs font-normal text-slate-400">/ {{ $totalNursesCount }} Terverifikasi</span>
                </span>
                <div class="flex items-center justify-between mt-0.5">
                    <span class="text-[11px] text-amber-700 font-bold">
                        {{ $pendingNursesCount }} menunggu verifikasi
                    </span>
                    <a href="{{ route('admin.nurses.index') }}" class="text-[11px] font-extrabold text-[#064e3b] hover:underline">
                        Verifikasi &rarr;
                    </a>
                </div>
            </div>
        </div>

    </div>

    <!-- 2. TIGA GRAFIK VISUALISASI INTERAKTIF (CHART.JS) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- CHART 1: GRAFIK TREN PEMESANAN ("PESAN SEKARANG" VS "PESAN UNTUK NANTI") -->
        <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
                <div>
                    <span class="text-[10px] uppercase font-extrabold text-[#064e3b] tracking-wider block">Visualisasi 1 • Line / Area Chart</span>
                    <h3 class="text-base font-extrabold text-slate-900">Grafik Tren Pemesanan Harian (WIB)</h3>
                    <p class="text-xs text-slate-500">Perbandingan jumlah pesanan tipe <strong>Pesan Sekarang</strong> vs <strong>Pesan Untuk Nanti (Terjadwal)</strong>.</p>
                </div>
                <div class="flex items-center gap-3 text-xs font-bold">
                    <span class="inline-flex items-center gap-1.5 text-[#064e3b]">
                        <span class="w-3 h-3 rounded-full bg-[#10b981]"></span> Pesan Sekarang
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-indigo-700">
                        <span class="w-3 h-3 rounded-full bg-indigo-500"></span> Pesan Untuk Nanti
                    </span>
                </div>
            </div>
            <div class="h-72 w-full">
                <canvas id="bookingTrendChart"></canvas>
            </div>
        </div>

        <!-- CHART 3: DISTRIBUSI STATUS PESANAN (DONUT CHART) -->
        <div class="lg:col-span-1 bg-white rounded-3xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <span class="text-[10px] uppercase font-extrabold text-[#064e3b] tracking-wider block">Visualisasi 3 • Donut Chart</span>
                <h3 class="text-base font-extrabold text-slate-900">Distribusi Status Pesanan</h3>
                <p class="text-xs text-slate-500">Proporsi status seluruh pesanan perawatan di sistem.</p>
            </div>

            <div class="h-56 w-full flex items-center justify-center relative">
                <canvas id="orderStatusDonutChart"></canvas>
            </div>

            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100 text-xs">
                <div class="p-2.5 rounded-xl bg-emerald-50/70 border border-emerald-200 flex items-center justify-between">
                    <span class="font-bold text-[#064e3b]">✓ Selesai</span>
                    <strong class="text-[#064e3b]">{{ $orderStatusDistribution['counts'][0] ?? 0 }}</strong>
                </div>
                <div class="p-2.5 rounded-xl bg-sky-50 border border-sky-200 flex items-center justify-between">
                    <span class="font-bold text-sky-800">🛵 Berlangsung</span>
                    <strong class="text-sky-900">{{ $orderStatusDistribution['counts'][1] ?? 0 }}</strong>
                </div>
                <div class="p-2.5 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-between">
                    <span class="font-bold text-amber-800">🗓️ Terjadwal</span>
                    <strong class="text-amber-900">{{ $orderStatusDistribution['counts'][2] ?? 0 }}</strong>
                </div>
                <div class="p-2.5 rounded-xl bg-rose-50 border border-rose-200 flex items-center justify-between">
                    <span class="font-bold text-rose-800">✕ Batal/Tolak</span>
                    <strong class="text-rose-900">{{ $orderStatusDistribution['counts'][3] ?? 0 }}</strong>
                </div>
            </div>
        </div>

    </div>

    <!-- CHART 2: GRAFIK PENDAPATAN TRANSAKSI MASUK VS PENARIKAN PAYOUT PERAWAT (BAR CHART) -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div>
                <span class="text-[10px] uppercase font-extrabold text-[#064e3b] tracking-wider block">Visualisasi 2 • Interactive Bar Chart</span>
                <h3 class="text-base font-extrabold text-slate-900">Grafik Pendapatan Transaksi Masuk vs Penarikan Saldo (Payout) Perawat</h3>
                <p class="text-xs text-slate-500">Perbandingan harian total pembayaran pasien yang masuk dengan total penarikan dana (payout) oleh perawat.</p>
            </div>
            <div class="flex items-center gap-4 text-xs font-bold">
                <span class="inline-flex items-center gap-1.5 text-[#064e3b]">
                    <span class="w-3 h-3 rounded bg-[#064e3b]"></span> Total Transaksi Masuk (Rp)
                </span>
                <span class="inline-flex items-center gap-1.5 text-amber-600">
                    <span class="w-3 h-3 rounded bg-amber-500"></span> Penarikan Payout Perawat (Rp)
                </span>
            </div>
        </div>
        <div class="h-72 w-full">
            <canvas id="revenuePayoutBarChart"></canvas>
        </div>
    </div>

    <!-- 3. DUA PANEL MANAJEMEN: VERIFIKASI & JADWAL PERAWAT + LOG PEMOTONGAN SALDO PAYOUT -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Panel Kiri: Manajemen Verifikasi & Jadwal Perawat (Registrasi Manual) -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Status Verifikasi, Jadwal &amp; Saldo Perawat</h3>
                    <p class="text-[11px] text-slate-500">Daftar perawat hasil registrasi beserta jadwal aktif mingguan dan saldo dompet.</p>
                </div>
                <a href="{{ route('admin.nurses.index') }}" class="px-3 py-1.5 rounded-xl bg-[#064e3b] hover:bg-emerald-900 text-white text-xs font-bold transition shrink-0">
                    Kelola Verifikasi &rarr;
                </a>
            </div>

            @if ($topNurses->isEmpty())
                <div class="p-8 text-center rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2">
                    <span class="text-2xl block">👩‍⚕️</span>
                    <p class="text-xs font-bold text-slate-700">Belum Ada Akun Perawat Terdaftar</p>
                    <p class="text-[11px] text-slate-500">Perawat baru yang melakukan registrasi manual akan muncul di sini untuk diverifikasi Admin.</p>
                </div>
            @else
                <div class="space-y-3 max-h-96 overflow-y-auto pr-1">
                    @foreach ($topNurses as $nurse)
                        <div class="p-3.5 rounded-2xl border border-slate-200/90 flex items-center justify-between text-xs hover:border-[#10b981] transition bg-slate-50/40">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#064e3b] to-[#10b981] text-white font-black flex items-center justify-center shadow-xs">
                                    {{ strtoupper(substr($nurse['name'], 0, 2)) }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-extrabold text-slate-900">{{ $nurse['name'] }}</h4>
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold {{ $nurse['verification_status'] === 'verified' ? 'bg-emerald-100 text-[#064e3b]' : ($nurse['verification_status'] === 'rejected' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                            {{ ucfirst($nurse['verification_status']) }}
                                        </span>
                                    </div>
                                    <span class="text-[11px] text-slate-500 block mt-0.5">
                                        🗓️ {{ $nurse['active_days_count'] }}/7 Hari Praktik Aktif &bull; ⭐ {{ number_format($nurse['rating_avg'], 1) }}
                                    </span>
                                </div>
                            </div>

                            <div class="text-right">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block">Saldo Aktif</span>
                                <span class="font-extrabold text-sm text-[#064e3b]">Rp {{ number_format($nurse['balance'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Panel Kanan: Log Pemotongan Saldo Payout Perawat -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Log Penarikan Saldo (Payout) Perawat</h3>
                    <p class="text-[11px] text-slate-500">Pemantauan pemotongan saldo otomatis saat perawat menarik dana.</p>
                </div>
                <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-[#064e3b] border border-emerald-200 text-[10px] font-extrabold">
                    Potong Saldo Otomatis
                </span>
            </div>

            @if ($recentPayouts->isEmpty())
                <div class="p-8 text-center rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2">
                    <span class="text-2xl block">💸</span>
                    <p class="text-xs font-bold text-slate-700">Belum Ada Riwayat Penarikan Saldo</p>
                    <p class="text-[11px] text-slate-500">Setiap penarikan nominal saldo oleh perawat akan tercatat otomatis di sini.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-100">
                            <tr>
                                <th class="p-2.5">Waktu (WIB)</th>
                                <th class="p-2.5">Nama Perawat</th>
                                <th class="p-2.5 text-right">Nominal Ditarik</th>
                                <th class="p-2.5 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($recentPayouts as $po)
                                <tr class="hover:bg-slate-50/70">
                                    <td class="p-2.5 text-[11px] font-mono text-slate-500 whitespace-nowrap">
                                        {{ $po->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                                    </td>
                                    <td class="p-2.5 font-bold text-slate-900">
                                        {{ $po->nurse?->name ?? $po->account_holder ?? 'Mitra Perawat' }}
                                    </td>
                                    <td class="p-2.5 text-right font-extrabold text-rose-600 whitespace-nowrap">
                                        - Rp {{ number_format($po->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="p-2.5 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-[#064e3b] border border-emerald-200">
                                            ✓ Saldo Terpotong
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>

    <!-- 4. TABEL MONITORING MUTASI TRANSAKSI REAL-TIME (AUTOMATED LEDGER) -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden space-y-4">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <span>Buku Besar Mutasi Transaksi &amp; Pembayaran (WIB)</span>
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-50 text-[#064e3b] border border-emerald-200 font-bold">
                        Real-Time Ledger
                    </span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Log seluruh arus masuk pembayaran pasien, distribusi komisi 15% platform, 85% pendapatan bersih perawat, dan penarikan saldo.
                </p>
            </div>

            <a href="{{ route('admin.escrow.index') }}" class="text-xs font-bold text-[#064e3b] hover:underline flex items-center gap-1">
                <span>Buka Monitoring Transaksi Lengkap &rarr;</span>
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
                    <thead class="bg-gradient-to-r from-[#064e3b] via-emerald-900 to-teal-950 text-white text-[10px] uppercase font-bold tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Waktu (WIB)</th>
                            <th class="py-3 px-4">No. Pesanan / ID</th>
                            <th class="py-3 px-4">Keterangan Mutasi</th>
                            <th class="py-3 px-4">Pemilik Dompet</th>
                            <th class="py-3 px-4 text-right">Perubahan Nominal</th>
                            <th class="py-3 px-4 text-right">Komisi Platform (15%)</th>
                            <th class="py-3 px-4 text-right">Saldo Akhir</th>
                            <th class="py-3 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                        @foreach ($recentMutations as $mut)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3.5 px-4 text-[11px] font-mono text-slate-500 whitespace-nowrap">
                                    {{ $mut->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                                </td>
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-900 whitespace-nowrap">
                                    {{ $mut->payment?->order_number ?? ($mut->order_id ? 'ORD-' . $mut->order_id : 'TX-' . $mut->id) }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-bold text-slate-900 block truncate max-w-[240px]" title="{{ $mut->description }}">
                                        {{ $mut->description ?: 'Mutasi transaksi otomatis' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @if ($mut->wallet?->user)
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs">👤</span>
                                            <span class="font-bold text-slate-900 text-xs">{{ $mut->wallet->user->name }}</span>
                                            <span class="text-[9px] px-1.5 py-0.2 rounded font-bold uppercase {{ $mut->wallet->user->hasRole('admin') ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-emerald-50 text-[#064e3b] border border-emerald-200' }}">
                                                {{ $mut->wallet->user->hasRole('admin') ? 'Admin' : 'Perawat' }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-slate-400 italic">Sistem Platform</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right font-black whitespace-nowrap {{ $mut->amount >= 0 ? 'text-[#064e3b]' : 'text-rose-600' }}">
                                    {{ $mut->amount >= 0 ? '+ ' : '- ' }} Rp {{ number_format(abs($mut->net_amount ?: $mut->amount), 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 text-right text-slate-500 whitespace-nowrap">
                                    {{ $mut->platform_fee > 0 ? 'Rp ' . number_format($mut->platform_fee, 0, ',', '.') : '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-bold text-slate-900 whitespace-nowrap">
                                    Rp {{ number_format($mut->balance_after, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-[#064e3b] border border-emerald-200">
                                        ✓ Selesai
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Script Inisialisasi 3 Grafik Interaktif Chart.js -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const labels = @json($chartLabels);
            const dataBookingNow = @json($chartBookingNow);
            const dataBookingScheduled = @json($chartBookingScheduled);
            const dataRevenueIn = @json($chartRevenueIn);
            const dataPayoutOut = @json($chartPayoutOut);
            const statusDist = @json($orderStatusDistribution);

            // 1. Line/Area Chart: Tren Pemesanan (Pesan Sekarang vs Pesan Untuk Nanti)
            const ctxTrend = document.getElementById('bookingTrendChart');
            if (ctxTrend) {
                new Chart(ctxTrend.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Pesan Sekarang (On-Demand)',
                                data: dataBookingNow,
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16, 185, 129, 0.16)',
                                fill: true,
                                tension: 0.35,
                                borderWidth: 2.5,
                                pointRadius: 3.5,
                            },
                            {
                                label: 'Pesan Untuk Nanti (Terjadwal)',
                                data: dataBookingScheduled,
                                borderColor: '#6366f1',
                                backgroundColor: 'rgba(99, 102, 241, 0.12)',
                                fill: true,
                                tension: 0.35,
                                borderWidth: 2.5,
                                pointRadius: 3.5,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: { font: { family: 'Plus Jakarta Sans', size: 11, weight: 'bold' } }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { stepSize: 1, font: { family: 'Plus Jakarta Sans', size: 10 } },
                                grid: { color: '#f1f5f9' }
                            },
                            x: {
                                ticks: { font: { family: 'Plus Jakarta Sans', size: 10 } },
                                grid: { display: false }
                            }
                        }
                    }
                });
            }

            // 2. Bar Chart: Pendapatan Masuk vs Penarikan Payout Perawat
            const ctxBar = document.getElementById('revenuePayoutBarChart');
            if (ctxBar) {
                new Chart(ctxBar.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Total Transaksi Masuk (Rp)',
                                data: dataRevenueIn,
                                backgroundColor: '#064e3b',
                                borderRadius: 6,
                            },
                            {
                                label: 'Penarikan Saldo / Payout Perawat (Rp)',
                                data: dataPayoutOut,
                                backgroundColor: '#f59e0b',
                                borderRadius: 6,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: { font: { family: 'Plus Jakarta Sans', size: 11, weight: 'bold' } }
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
            }

            // 3. Donut Chart: Distribusi Status Pesanan
            const ctxDonut = document.getElementById('orderStatusDonutChart');
            if (ctxDonut) {
                const counts = statusDist.counts || [0, 0, 0, 0];
                const totalAll = counts.reduce((a, b) => a + b, 0);
                new Chart(ctxDonut.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: statusDist.labels || ['Selesai', 'Berlangsung', 'Terjadwal', 'Dibatalkan/Ditolak'],
                        datasets: [{
                            data: totalAll > 0 ? counts : [1, 0, 0, 0],
                            backgroundColor: totalAll > 0
                                ? ['#10b981', '#0ea5e9', '#f59e0b', '#f43f5e']
                                : ['#e2e8f0', '#e2e8f0', '#e2e8f0', '#e2e8f0'],
                            borderWidth: 2,
                            borderColor: '#ffffff',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '66%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { font: { family: 'Plus Jakarta Sans', size: 10, weight: 'bold' }, boxWidth: 10 }
                            }
                        }
                    }
                });
            }
        });
    </script>
</x-admin-layout>
