<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Konfirmasi Pembayaran Langsung - CareAtHome</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col"
    x-data="patientPaymentApp()">

    <!-- 1. TOP NAVBAR -->
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
                            Jadwal &amp; Detail Booking
                        </a>
                        <a href="{{ route('pasien.pembayaran') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 transition flex items-center gap-1.5">
                            <span>Pembayaran Pesanan</span>
                            <span class="text-[9px] font-extrabold px-1.5 py-0.2 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300">Pencatatan Otomatis</span>
                        </a>
                        <a href="{{ route('pasien.pembatalan') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition">
                            Pembatalan &amp; Refund
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
                            <span class="text-[10px] text-slate-500 block leading-tight">Pengguna Pasien</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </header>

    <!-- 2. MAIN CONTAINER -->
    <main class="grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        <!-- Page Header Banner -->
        <div class="bg-gradient-to-r from-emerald-900 via-teal-900 to-slate-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-6 border border-emerald-700/40 relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="space-y-2 relative z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Sistem Pembayaran Langsung &amp; Alokasi Saldo
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Konfirmasi Pembayaran Pesanan
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 max-w-2xl leading-relaxed">
                    Pembayaran disederhanakan dengan tombol langsung <strong>"Bayar Sekarang"</strong>. Sistem otomatis mencatat transaksi dan membagi 85% untuk saldo perawat dan 15% untuk biaya operasional platform.
                </p>
            </div>

            <div class="flex items-center gap-4 relative z-10">
                <div class="bg-white/10 backdrop-blur-md px-4 py-3 rounded-2xl border border-white/10 text-center">
                    <span class="text-[10px] text-slate-300 block uppercase font-bold tracking-wider">Menunggu Bayar</span>
                    <span class="text-xl font-extrabold text-amber-300">{{ $totalPending }}</span>
                </div>
                <div class="bg-white/10 backdrop-blur-md px-4 py-3 rounded-2xl border border-white/10 text-center">
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

        <!-- SECTION: TAGIHAN MENUNGGU PEMBAYARAN -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                        <span>Tagihan Aktif Menunggu Konfirmasi</span>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 font-bold">{{ $unpaidOrders->count() }}</span>
                    </h2>
                    <p class="text-xs text-slate-500">Klik "Bayar Sekarang" untuk memproses pembayaran dan meneruskan pesanan ke nakes.</p>
                </div>
            </div>

            @if ($unpaidOrders->isEmpty())
                <div class="bg-white rounded-3xl border border-slate-200/80 p-8 sm:p-12 text-center space-y-4 shadow-sm">
                    <div class="w-16 h-16 rounded-3xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-3xl mx-auto shadow-inner">
                        ✓
                    </div>
                    <div class="space-y-1.5">
                        <h3 class="text-lg font-bold text-slate-900">Tidak Ada Tagihan yang Menunggu Pembayaran</h3>
                        <p class="text-xs text-slate-500 leading-relaxed max-w-md mx-auto">
                            Semua pesanan aktif Anda telah berstatus lunas. Jika Anda ingin memesan layanan keperawatan baru, silakan buka katalog.
                        </p>
                    </div>
                    <div class="pt-2 flex flex-wrap items-center justify-center gap-3">
                        <a href="{{ route('pasien.katalog') }}" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow transition">
                            Cari Tindakan di Katalog Layanan &rarr;
                        </a>
                        <a href="{{ route('pasien.pesanan') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                            Lihat Jadwal Kunjungan
                        </a>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    @foreach ($unpaidOrders as $order)
                        @php
                            $breakdown = $paymentService->calculateBreakdown($order->total_amount);
                        @endphp
                        <div class="bg-white rounded-3xl border-2 border-emerald-500/30 shadow-md p-6 space-y-5 flex flex-col justify-between"
                            x-data="{ isPaying: false }">

                            <!-- Card Header -->
                            <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-800 flex items-center justify-center text-2xl border border-emerald-100">
                                        {{ $order->serviceCatalog->image_emoji ?? '🩺' }}
                                    </div>
                                    <div>
                                        <span class="text-[10px] font-mono font-bold text-slate-400 uppercase tracking-wider block">
                                            {{ $order->order_number }}
                                        </span>
                                        <h3 class="text-sm font-bold text-slate-900 leading-snug">
                                            {{ $order->serviceCatalog->name ?? 'Tindakan Keperawatan' }}
                                        </h3>
                                        <span class="text-[11px] text-slate-500">
                                            Jadwal: {{ $order->scheduled_date ? $order->scheduled_date->format('d M Y') : '-' }} • {{ $order->scheduled_time }}
                                        </span>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-800 border border-amber-200">
                                    Menunggu Bayar
                                </span>
                            </div>

                            <!-- Detail Perawat & Penerima -->
                            <div class="text-xs text-slate-600 bg-slate-50 p-3 rounded-2xl border border-slate-200/60 space-y-1">
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Pasien:</span>
                                    <span class="font-bold text-slate-800">{{ $order->patient_name }} ({{ $order->patient_phone }})</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Perawat Ditugaskan:</span>
                                    <span class="font-bold text-emerald-700">{{ $order->nurse->name ?? 'Ns. Mitra CareAtHome' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Alamat:</span>
                                    <span class="font-medium text-slate-700 truncate max-w-[200px]" title="{{ $order->address }}">{{ $order->address }}</span>
                                </div>
                            </div>

                            <!-- Price Breakdown & Alokasi Finansial -->
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3 text-xs">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Rincian Plafon Biaya Tindakan</span>
                                <div class="space-y-1.5 text-slate-600">
                                    <div class="flex justify-between">
                                        <span>Tarif Tindakan Medis:</span>
                                        <span class="font-semibold text-slate-900">Rp {{ number_format($order->base_price, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Biaya Transportasi GPS ({{ $order->distance_km ?? 0 }} km):</span>
                                        <span class="font-semibold text-slate-900">Rp {{ number_format($order->transport_fee, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Biaya Layanan Platform:</span>
                                        <span class="font-semibold text-slate-900">Rp {{ number_format($order->platform_fee, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between items-baseline pt-2 border-t border-slate-200 text-sm font-black text-slate-900">
                                        <span>Total Tagihan:</span>
                                        <span class="text-base text-emerald-700">Rp {{ number_format($breakdown['total_amount'], 0, ',', '.') }}</span>
                                    </div>
                                </div>

                                <!-- Box Porsi Pembagian Finansial (85% Perawat, 15% Platform) -->
                                <div class="pt-3 border-t border-dashed border-slate-300 space-y-1.5 bg-emerald-50/70 p-3 rounded-xl border border-emerald-200/60">
                                    <div class="flex items-center justify-between text-[11px] font-bold text-emerald-950">
                                        <span class="flex items-center gap-1">
                                            <span>🩺</span>
                                            <span>Bagian Mitra Perawat ({{ 100 - $breakdown['fee_percent'] }}%):</span>
                                        </span>
                                        <span class="font-black text-emerald-700">Rp {{ number_format($breakdown['nurse_earning'], 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-[11px] font-bold text-slate-700">
                                        <span class="flex items-center gap-1">
                                            <span>🏢</span>
                                            <span>Bagian Operasional Platform ({{ $breakdown['fee_percent'] }}%):</span>
                                        </span>
                                        <span class="font-black text-slate-700">Rp {{ number_format($breakdown['admin_fee'], 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Tombol Bayar Sekarang Tunggal -->
                            <div class="pt-2">
                                <button type="button"
                                    :disabled="isPaying"
                                    @click="payOrder({{ $order->id }}, '{{ $order->order_number }}')"
                                    class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-extrabold text-sm shadow-lg shadow-emerald-500/20 flex items-center justify-center gap-2 transition transform active:scale-95 disabled:opacity-50">
                                    <template x-if="!isPaying">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                            <span>Bayar Sekarang (Rp {{ number_format($breakdown['total_amount'], 0, ',', '.') }})</span>
                                        </div>
                                    </template>
                                    <template x-if="isPaying">
                                        <div class="flex items-center gap-2">
                                            <span class="animate-spin text-base">⏳</span>
                                            <span>Memproses Pembayaran...</span>
                                        </div>
                                    </template>
                                </button>
                                <p class="text-[10px] text-center text-slate-400 mt-2">
                                    Dana langsung tercatat &amp; dikreditkan ke saldo dompet masing-masing pihak dalam 1 transaksi aman.
                                </p>
                            </div>

                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- SECTION: RIWAYAT PEMBAYARAN TELAH LUNAS -->
        @if ($escrowHeldOrders->isNotEmpty())
            <div class="space-y-4 pt-6 border-t border-slate-200">
                <h2 class="text-base font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                    <span>Riwayat Pembayaran Berhasil</span>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold">{{ $escrowHeldOrders->count() }}</span>
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach ($escrowHeldOrders as $paid)
                        @php
                            $paidBreakdown = $paymentService->calculateBreakdown($paid->total_amount);
                        @endphp
                        <div class="bg-white rounded-2xl border border-slate-200 p-5 space-y-3 shadow-sm hover:shadow-md transition">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <div>
                                    <span class="text-[10px] font-mono text-slate-400 block">{{ $paid->order_number }}</span>
                                    <h4 class="text-xs font-bold text-slate-900">{{ $paid->serviceCatalog->name ?? 'Layanan Medis' }}</h4>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Lunas
                                </span>
                            </div>

                            <div class="text-xs space-y-1.5 text-slate-600">
                                <div class="flex justify-between font-bold text-slate-900">
                                    <span>Total Dibayar:</span>
                                    <span class="text-emerald-700">Rp {{ number_format($paid->total_amount, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between text-[11px] text-slate-500">
                                    <span>Porsi Perawat (85%):</span>
                                    <span class="font-semibold text-emerald-600">Rp {{ number_format($paidBreakdown['nurse_earning'], 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between text-[11px] text-slate-500">
                                    <span>Porsi Platform (15%):</span>
                                    <span class="font-semibold text-slate-600">Rp {{ number_format($paidBreakdown['admin_fee'], 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between text-[11px] text-slate-400 pt-1 border-t border-slate-100">
                                    <span>Status Pesanan:</span>
                                    <span class="font-bold text-slate-700">{{ $paid->status_label }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </main>

    <!-- Alpine App Logic -->
    <script>
        function patientPaymentApp() {
            return {
                alertMessage: '',
                alertType: 'success',

                async payOrder(orderId, orderNumber) {
                    if (!confirm('Konfirmasi pembayaran langsung untuk pesanan ' + orderNumber + '?')) {
                        return;
                    }

                    try {
                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const response = await fetch(`/pasien/pesanan/${orderId}/bayar`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({})
                        });

                        const result = await response.json();

                        if (response.ok && result.status === 'success') {
                            this.alertType = 'success';
                            this.alertMessage = result.message || 'Pembayaran berhasil dikonfirmasi!';
                            setTimeout(() => {
                                window.location.reload();
                            }, 1200);
                        } else {
                            this.alertType = 'error';
                            this.alertMessage = result.message || 'Gagal memproses pembayaran.';
                        }
                    } catch (e) {
                        this.alertType = 'error';
                        this.alertMessage = 'Terjadi gangguan jaringan saat memproses pembayaran.';
                    }
                }
            };
        }
    </script>
</body>
</html>
