<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tagihan &amp; Pembayaran Langsung - CareAtHome</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col"
    x-data="patientPaymentTableApp()">

    <!-- 1. TOP NAVBAR PASIEN -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <!-- Left: Brand Logo & Portal Tag -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 group">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 flex items-center justify-center text-white shadow-md shadow-emerald-500/20 group-hover:scale-105 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-lg font-extrabold text-slate-900 tracking-tight leading-none block">
                                Care<span class="text-emerald-600">AtHome</span>
                            </span>
                            <span class="text-[10px] uppercase tracking-wider font-semibold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200/60 mt-0.5 inline-block">
                                Portal Pasien
                            </span>
                        </div>
                    </a>

                    <!-- Nav separator -->
                    <div class="hidden sm:block h-6 w-px bg-slate-200 ml-2"></div>

                    <!-- Desktop Nav Links -->
                    <nav class="hidden sm:flex items-center space-x-1 ml-2">
                        <a href="{{ route('dashboard') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition">
                            Dashboard
                        </a>
                        <a href="{{ route('pasien.katalog') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition">
                            Katalog Layanan
                        </a>
                        <a href="{{ route('pasien.pesanan') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition">
                            Riwayat Layanan
                        </a>
                        <a href="{{ route('pasien.pembayaran') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 transition flex items-center gap-1.5">
                            <span>Tagihan &amp; Pembayaran</span>
                            <span class="text-[9px] font-extrabold px-1.5 py-0.2 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300">Direct Pay</span>
                        </a>
                        <a href="{{ route('pasien.pembatalan') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-rose-600 hover:text-rose-900 hover:bg-rose-50 transition">
                            Pembatalan
                        </a>
                    </nav>
                </div>

                <!-- Right Actions: User Info -->
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center text-xs font-bold">
                            {{ substr(auth()->user()?->name ?? 'P', 0, 1) }}
                        </div>
                        <div class="hidden lg:block text-left">
                            <span class="text-xs font-bold text-slate-800 block leading-tight">{{ auth()->user()?->name ?? 'Pasien' }}</span>
                            <span class="text-[10px] text-slate-500 block leading-tight">Pasien Terdaftar</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </header>

    <!-- 2. MAIN CONTAINER -->
    <main class="grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        <!-- Banner Header: Direct Instant Payment System -->
        <div class="bg-gradient-to-r from-slate-900 via-teal-950 to-emerald-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-6 border border-emerald-800/40 relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="space-y-2 relative z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Sistem Pembayaran Instan Berbasis Tabel (Direct Instant Payment Table)
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Tagihan Layanan &amp; Pembayaran Langsung
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 max-w-2xl leading-relaxed">
                    Rincian tagihan medis transparan dan terperinci. Bayar secara instan dengan <strong>satu klik tanpa unggah bukti transfer atau scan QRIS manual</strong>. Saldo langsung dialokasikan ke perawat (85% + transport) dan platform (15%).
                </p>
            </div>

            <div class="flex items-center gap-3 relative z-10">
                <div class="bg-white/10 backdrop-blur-md px-4 py-3 rounded-2xl border border-white/10 text-center min-w-[110px]">
                    <span class="text-[10px] text-slate-300 block uppercase font-bold tracking-wider">Menunggu Bayar</span>
                    <span class="text-xl font-extrabold text-amber-300" x-text="unpaidOrdersCount"></span>
                </div>
                <div class="bg-white/10 backdrop-blur-md px-4 py-3 rounded-2xl border border-white/10 text-center min-w-[110px]">
                    <span class="text-[10px] text-slate-300 block uppercase font-bold tracking-wider">Telah Lunas</span>
                    <span class="text-xl font-extrabold text-emerald-300">{{ $totalHeld }}</span>
                </div>
            </div>
        </div>

        <!-- Feedback Alert Toast -->
        <div x-show="alertMessage" x-cloak
            class="p-4 rounded-2xl flex items-center justify-between shadow-lg transition-all"
            :class="alertType === 'success' ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white'">
            <div class="flex items-center gap-3">
                <span class="text-xl font-black" x-text="alertType === 'success' ? '✓' : '⚠️'"></span>
                <span class="text-xs font-semibold" x-text="alertMessage"></span>
            </div>
            <button @click="alertMessage = ''" class="text-white/80 hover:text-white text-xs font-bold px-2 py-1">Tutup</button>
        </div>

        <!-- 3. TABEL PEMBAYARAN INSTAN (DIRECT INSTANT PAYMENT TABLE) -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden space-y-4">
            
            <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                        <span>Daftar Tagihan Aktif (Checkout Table)</span>
                        <span class="text-xs px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-200 font-bold"
                            x-text="unpaidOrdersCount + ' Tagihan Menunggu'"></span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Rincian komponen tarif medis, transportasi perawat, dan biaya layanan. Tekan tombol pada baris tagihan untuk pembayaran instan.</p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-[11px] text-slate-400 italic flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Otomatisasi Verifikasi 100% Real-Time
                    </span>
                </div>
            </div>

            @if ($unpaidOrders->isEmpty())
                <div class="p-12 text-center space-y-4">
                    <div class="w-16 h-16 rounded-3xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-3xl mx-auto shadow-inner">
                        ✓
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-base font-bold text-slate-900">Semua Tagihan Anda Telah Berstatus Lunas</h3>
                        <p class="text-xs text-slate-500 max-w-md mx-auto">
                            Tidak ada pesanan aktif yang menunggu pembayaran saat ini. Anda dapat melihat riwayat kunjungan medis atau memesan layanan baru.
                        </p>
                    </div>
                    <div class="pt-2 flex items-center justify-center gap-3">
                        <a href="{{ route('pasien.katalog') }}" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition">
                            Pesan Layanan di Katalog &rarr;
                        </a>
                        <a href="{{ route('pasien.pesanan') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                            Lihat Riwayat Layanan
                        </a>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <!-- Dark / Contrast Header -->
                        <thead class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white text-[11px] uppercase tracking-wider font-extrabold">
                            <tr>
                                <th class="py-3.5 px-4 font-bold">No. Pesanan &amp; Layanan</th>
                                <th class="py-3.5 px-4 font-bold">Jadwal Kunjungan</th>
                                <th class="py-3.5 px-4 font-bold">Nakes Ditugaskan</th>
                                <th class="py-3.5 px-4 font-bold text-right">Tarif Tindakan</th>
                                <th class="py-3.5 px-4 font-bold text-right">Transport GPS</th>
                                <th class="py-3.5 px-4 font-bold text-right">Biaya Platform</th>
                                <th class="py-3.5 px-4 font-bold text-right text-emerald-300">Total Akhir</th>
                                <th class="py-3.5 px-4 font-bold text-center">Status</th>
                                <th class="py-3.5 px-4 font-bold text-center">Aksi Langsung</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                            @foreach ($unpaidOrders as $order)
                                @php
                                    $breakdown = $paymentService->calculateBreakdown($order);
                                @endphp
                                <tr class="hover:bg-emerald-50/30 transition group"
                                    id="order-row-{{ $order->id }}"
                                    x-data="{ isRowPaying: false, isRowPaid: false }">
                                    
                                    <!-- No. Pesanan & Layanan -->
                                    <td class="py-4 px-4">
                                        <div class="space-y-0.5">
                                            <span class="font-mono text-[10px] font-bold text-slate-400 block">{{ $order->order_number }}</span>
                                            <div class="flex items-center gap-1.5 font-extrabold text-slate-900 text-xs">
                                                <span>{{ $order->serviceCatalog->image_emoji ?? '🩺' }}</span>
                                                <span>{{ $order->serviceCatalog->name ?? 'Tindakan Keperawatan' }}</span>
                                            </div>
                                            <span class="text-[10px] text-slate-500 block truncate max-w-[180px]" title="{{ $order->address }}">
                                                📍 {{ $order->address }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Jadwal Kunjungan -->
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        <div class="space-y-0.5">
                                            <span class="font-bold text-slate-800 block">
                                                {{ $order->scheduled_date ? $order->scheduled_date->format('d M Y') : '-' }}
                                            </span>
                                            <span class="text-[11px] text-slate-500">Pukul {{ $order->scheduled_time ?? '09:00' }}</span>
                                        </div>
                                    </td>

                                    <!-- Nakes Ditugaskan -->
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        @if ($order->nurse)
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold flex items-center justify-center">
                                                    {{ strtoupper(substr($order->nurse->name, 0, 1)) }}
                                                </div>
                                                <span class="font-bold text-emerald-800 text-xs">{{ $order->nurse->name }}</span>
                                            </div>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                                Broadcast Sekitar (Open)
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Tarif Tindakan Medis -->
                                    <td class="py-4 px-4 text-right font-semibold text-slate-800 whitespace-nowrap">
                                        Rp {{ number_format($breakdown['base_price'], 0, ',', '.') }}
                                    </td>

                                    <!-- Ongkir Transport GPS -->
                                    <td class="py-4 px-4 text-right whitespace-nowrap">
                                        <div class="space-y-0.5">
                                            <span class="font-semibold text-slate-800">Rp {{ number_format($breakdown['transport_fee'], 0, ',', '.') }}</span>
                                            <span class="text-[9px] text-slate-400 block">({{ $order->distance_km ?? 0 }} km)</span>
                                        </div>
                                    </td>

                                    <!-- Biaya Platform -->
                                    <td class="py-4 px-4 text-right font-semibold text-slate-600 whitespace-nowrap">
                                        Rp {{ number_format($breakdown['platform_fee'], 0, ',', '.') }}
                                    </td>

                                    <!-- Total Akhir -->
                                    <td class="py-4 px-4 text-right whitespace-nowrap">
                                        <span class="text-sm font-black text-emerald-700">
                                            Rp {{ number_format($breakdown['total_amount'], 0, ',', '.') }}
                                        </span>
                                    </td>

                                    <!-- Status Pembayaran -->
                                    <td class="py-4 px-4 text-center whitespace-nowrap">
                                        <span x-show="!isRowPaid" class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-800 border border-amber-200 shadow-2xs inline-block">
                                            Belum Dibayar
                                        </span>
                                        <span x-show="isRowPaid" x-cloak class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-300 shadow-2xs inline-flex items-center justify-center gap-1">
                                            <span>✓</span>
                                            <span>Lunas (Paid)</span>
                                        </span>
                                    </td>

                                    <!-- Tombol Aksi Langsung (Instant Pay) -->
                                    <td class="py-4 px-4 text-center whitespace-nowrap">
                                        <button type="button"
                                            x-show="!isRowPaid"
                                            :disabled="isRowPaying"
                                            @click="payOrderRow({{ $order->id }}, '{{ $order->order_number }}', $data)"
                                            class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-black text-xs shadow-md shadow-emerald-500/20 transition-all transform active:scale-95 disabled:opacity-50 inline-flex items-center gap-1.5 mx-auto cursor-pointer">
                                            <span x-show="!isRowPaying" class="inline-flex items-center gap-1.5">
                                                <span>⚡</span>
                                                <span>Bayar Sekarang</span>
                                            </span>
                                            <span x-show="isRowPaying" x-cloak class="inline-flex items-center gap-1.5">
                                                <span class="animate-spin">⏳</span>
                                                <span>Memproses...</span>
                                            </span>
                                        </button>

                                        <span x-show="isRowPaid" x-cloak class="text-[11px] font-bold text-emerald-600 inline-flex items-center justify-center gap-1">
                                            <span>🎉</span>
                                            <span>Pembayaran Selesai</span>
                                        </span>
                                    </td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <!-- Footer Penjelasan Sistem -->
            <div class="p-4 bg-slate-50 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between text-xs text-slate-500 gap-2">
                <div class="flex items-center gap-2">
                    <span class="p-1 rounded-lg bg-emerald-100 text-emerald-800 text-xs">🛡️</span>
                    <span>Direct Simulated Instant Settlement &bull; Alokasi Saldo Otomatis (85% Perawat + 15% Platform).</span>
                </div>
                <div class="text-[11px] text-slate-400">
                    Bypass verifikasi manual admin &bull; Transaksi aman dan real-time.
                </div>
            </div>

        </div>

        <!-- 4. TABEL RIWAYAT TRANSAKSI LUNAS (SELESAI) -->
        @if ($escrowHeldOrders->isNotEmpty())
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden space-y-4 pt-2">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                            <span>Riwayat Pembayaran Lunas</span>
                            <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 font-bold">
                                {{ $escrowHeldOrders->count() }} Transaksi
                            </span>
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">Daftar transaksi pesanan yang telah berhasil dibayar dan dana telah dialokasikan ke mitra perawat.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-100 text-slate-700 text-[10px] uppercase tracking-wider font-bold">
                            <tr>
                                <th class="py-3 px-4">No. Pesanan</th>
                                <th class="py-3 px-4">Layanan &amp; Nakes</th>
                                <th class="py-3 px-4">Tanggal Kunjungan</th>
                                <th class="py-3 px-4 text-right">Porsi Nakes (85%)</th>
                                <th class="py-3 px-4 text-right">Platform Fee (15%)</th>
                                <th class="py-3 px-4 text-right">Total Transaksi</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-center">Dokumen</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-600">
                            @foreach ($escrowHeldOrders as $paid)
                                @php
                                    $paidBreakdown = $paymentService->calculateBreakdown($paid);
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                        {{ $paid->order_number }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="space-y-0.5">
                                            <span class="font-bold text-slate-900 block">{{ $paid->serviceCatalog->name ?? 'Layanan Medis' }}</span>
                                            <span class="text-[10px] text-slate-400">Nakes: {{ $paid->nurse->name ?? 'Mitra Nakes' }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        {{ $paid->scheduled_date ? $paid->scheduled_date->format('d M Y') : '-' }} &bull; {{ $paid->scheduled_time }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-semibold text-emerald-700 whitespace-nowrap">
                                        Rp {{ number_format($paidBreakdown['nurse_earning'], 0, ',', '.') }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-semibold text-slate-600 whitespace-nowrap">
                                        Rp {{ number_format($paidBreakdown['admin_fee'], 0, ',', '.') }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-black text-slate-900 whitespace-nowrap">
                                        Rp {{ number_format($paidBreakdown['total_amount'], 0, ',', '.') }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            ✓ Lunas
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        <a href="{{ route('pasien.pesanan') }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-900 underline">
                                            Lihat Rekam Medis &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </main>

    <!-- Alpine.js Application Logic -->
    <script>
        function patientPaymentTableApp() {
            return {
                unpaidOrdersCount: {{ $unpaidOrders->count() }},
                alertMessage: '',
                alertType: 'success',

                async payOrderRow(orderId, orderNumber, rowScope) {
                    if (!confirm(`Konfirmasi pembayaran instan untuk pesanan #${orderNumber}?\n\nDana akan langsung diteruskan ke dompet nakes (85% + transport) dan kas platform (15%).`)) {
                        return;
                    }

                    rowScope.isRowPaying = true;
                    this.alertMessage = '';

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const response = await fetch(`/pasien/pesanan/${orderId}/bayar`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            }
                        });

                        const res = await response.json();

                        if (response.ok && res.status === 'success') {
                            rowScope.isRowPaying = false;
                            rowScope.isRowPaid = true;
                            this.unpaidOrdersCount = Math.max(0, this.unpaidOrdersCount - 1);
                            this.alertType = 'success';
                            this.alertMessage = `✓ Pembayaran pesanan #${orderNumber} sebesar Rp ${Number(res.data.gross_amount).toLocaleString('id-ID')} Berhasil! Dana telah langsung dialokasikan ke Perawat (85% + transport) dan Admin (15%).`;
                            setTimeout(() => window.location.reload(), 1300);
                        } else {
                            rowScope.isRowPaying = false;
                            this.alertType = 'error';
                            this.alertMessage = res.message || 'Gagal memproses pembayaran langsung.';
                        }
                    } catch (error) {
                        rowScope.isRowPaying = false;
                        this.alertType = 'error';
                        this.alertMessage = 'Terjadi gangguan jaringan saat menghubungi server pembayaran.';
                    }
                }
            };
        }
    </script>
</body>
</html>
