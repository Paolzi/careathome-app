<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gateway Pembayaran Escrow &amp; Konfirmasi - CareAtHome</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Midtrans Snap JS (Sandbox) -->
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>

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
                            <span>Pembayaran Escrow</span>
                            <span class="text-[9px] font-extrabold px-1.5 py-0.2 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300">Escrow</span>
                        </a>
                        <a href="{{ route('pasien.pembatalan') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition">
                            Pembatalan &amp; Refund
                        </a>
                    </nav>
                </div>

                <!-- Right Actions: User Info & CTA -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('pasien.katalog') }}"
                        class="hidden md:inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm shadow-emerald-600/20 transition">
                        <span>+ Pesan Layanan Baru</span>
                    </a>

                    <div class="flex items-center gap-2 pl-2 border-l border-slate-200">
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

        <!-- Page Header Banner Khusus Pembayaran Escrow -->
        <div class="bg-gradient-to-r from-emerald-900 via-teal-900 to-slate-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-6 border border-emerald-700/40 relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="space-y-2 relative z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Gateway Pembayaran Escrow &amp; Rekening Penampung Aman
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Gateway Pembayaran Escrow &amp; Konfirmasi
                </h1>
                <p class="text-slate-300 text-xs sm:text-sm max-w-2xl leading-relaxed">
                    Sistem transaksi non-tunai terstandar. Dana pembayaran tagihan pasien <strong>dikunci aman di rekening penampung (Escrow)</strong> sistem dan baru dicairkan ke mitra perawat setelah tindakan medis selesai serta E-Report tervalidasi.
                </p>
            </div>

            <div class="shrink-0 grid grid-cols-2 sm:grid-cols-2 gap-4 border-t sm:border-t-0 sm:border-l border-emerald-700/60 pt-4 sm:pt-0 sm:pl-6 relative z-10 text-right">
                <div class="p-3 bg-white/5 rounded-2xl border border-white/10 text-left">
                    <span class="text-[10px] text-amber-300 uppercase tracking-wider font-bold block">Tagihan Tertunda</span>
                    <span class="text-2xl font-black text-amber-400">{{ $totalPending }} Pesanan</span>
                </div>
                <div class="p-3 bg-white/5 rounded-2xl border border-white/10 text-left">
                    <span class="text-[10px] text-emerald-300 uppercase tracking-wider font-bold block">Terkunci di Escrow</span>
                    <span class="text-2xl font-black text-emerald-400">{{ $totalHeld }} Lunas</span>
                </div>
            </div>
        </div>

        <!-- 3. KARTU JAMINAN PERLINDUNGAN ESCROW -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-4 rounded-2xl bg-white border border-slate-200/90 shadow-2xs flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-xl shrink-0">
                    🛡️
                </div>
                <div class="space-y-0.5">
                    <h4 class="text-xs font-bold text-slate-900">Rekening Penampung Aman</h4>
                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        Dana tidak langsung masuk ke rekening pribadi perawat, melainkan dijaga oleh platform hingga verifikasi asuhan selesai.
                    </p>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-white border border-slate-200/90 shadow-2xs flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center text-xl shrink-0">
                    ⚡
                </div>
                <div class="space-y-0.5">
                    <h4 class="text-xs font-bold text-slate-900">100% Pengembalian Dana Otomatis</h4>
                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        Jika pesanan dibatalkan sebelum nakes diberangkatkan ke rumah Anda, dana escrow dikembalikan 100% tanpa potongan.
                    </p>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-white border border-slate-200/90 shadow-2xs flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-700 flex items-center justify-center text-xl shrink-0">
                    🏦
                </div>
                <div class="space-y-0.5">
                    <h4 class="text-xs font-bold text-slate-900">Metode Non-Tunai Resmi</h4>
                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        Didukung Virtual Account Bank BCA, Mandiri, BNI, BRI serta QRIS dinamis berizin Bank Indonesia.
                    </p>
                </div>
            </div>
        </div>

        <!-- 4. DAFTAR TAGIHAN MENUNGGU PEMBAYARAN ESCROW -->
        <div class="space-y-4">
            <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Tagihan Menunggu Pembayaran</h2>
                    <p class="text-xs text-slate-500">Pilih metode pembayaran non-tunai untuk mengunci dana ke rekening penampung sebelum nakes menuju lokasi.</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                    {{ $totalPending }} Menunggu Pembayaran
                </span>
            </div>

            @if ($unpaidOrders->isEmpty())
                <!-- EMPTY STATE KHUSUS PEMBAYARAN -->
                <div class="bg-white rounded-3xl border border-slate-200 p-10 sm:p-14 text-center max-w-xl mx-auto space-y-4 shadow-sm">
                    <div class="w-20 h-20 rounded-3xl bg-emerald-50 text-emerald-600 mx-auto flex items-center justify-center text-4xl shadow-inner">
                        💳
                    </div>
                    <div class="space-y-1.5">
                        <h3 class="text-lg font-bold text-slate-900">Tidak Ada Tagihan yang Menunggu Pembayaran</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Semua pesanan aktif Anda telah lunas dan dana aman tersimpan di sistem escrow platform. Jika Anda ingin memesan perawat baru, silakan kunjungi katalog layanan.
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
                <!-- DAFTAR INVOICE TAGIHAN INTERAKTIF -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    @foreach ($unpaidOrders as $order)
                        <div class="bg-white rounded-3xl border-2 border-emerald-500/30 shadow-md p-6 space-y-5 flex flex-col justify-between"
                            x-data="{
                                method: 'bca_va',
                                vaNum: '88012' + '{{ substr(preg_replace('/\D/', '', $order->patient_phone . $order->id . '12345678'), 0, 11) }}',
                                copied: false,
                                isPaying: false,
                                copyVA() {
                                    navigator.clipboard.writeText(this.vaNum).then(() => {
                                        this.copied = true;
                                        setTimeout(() => { this.copied = false; }, 2500);
                                    });
                                },
                                updateMethod(m) {
                                    this.method = m;
                                    if (m === 'mandiri_va') {
                                        this.vaNum = '70012' + '{{ substr(preg_replace('/\D/', '', $order->patient_phone . $order->id . '12345678'), 0, 11) }}';
                                    } else if (m === 'bni_va') {
                                        this.vaNum = '98812' + '{{ substr(preg_replace('/\D/', '', $order->patient_phone . $order->id . '12345678'), 0, 11) }}';
                                    } else if (m === 'bri_va') {
                                        this.vaNum = '12812' + '{{ substr(preg_replace('/\D/', '', $order->patient_phone . $order->id . '12345678'), 0, 11) }}';
                                    } else {
                                        this.vaNum = '88012' + '{{ substr(preg_replace('/\D/', '', $order->patient_phone . $order->id . '12345678'), 0, 11) }}';
                                    }
                                }
                            }">

                            <!-- Invoice Header -->
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
                                            Jadwal: {{ $order->scheduled_date->format('d M Y') }} • {{ $order->scheduled_time }}
                                        </span>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-800 border border-amber-200">
                                    Menunggu Bayar
                                </span>
                            </div>

                            <!-- Price Breakdown (KF-10 Transparansi Tarif) -->
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2 text-xs">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Rincian Plafon Tarif &amp; Tagihan</span>
                                <div class="space-y-1 text-slate-600">
                                    <div class="flex justify-between">
                                        <span>Tarif Tindakan Medis:</span>
                                        <span class="font-semibold text-slate-900">Rp {{ number_format($order->base_price, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Biaya Transportasi GPS ({{ $order->distance_km }} km):</span>
                                        <span class="font-semibold text-slate-900">Rp {{ number_format($order->transport_fee, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Biaya Layanan Platform (Flat):</span>
                                        <span class="font-semibold text-slate-900">Rp 5.000</span>
                                    </div>
                                    <div class="flex justify-between items-baseline pt-2 border-t border-slate-200 text-sm font-black text-emerald-800">
                                        <span>Total Tagihan Escrow:</span>
                                        <span class="text-base text-emerald-700">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Pilihan Metode Pembayaran Non-Tunai -->
                            <div class="space-y-2 text-xs">
                                <label class="block font-bold text-slate-700">Pilih Metode Transfer Escrow:</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <button type="button" @click="updateMethod('bca_va')"
                                        class="p-2.5 rounded-xl border text-left transition flex items-center justify-between"
                                        :class="method === 'bca_va' ? 'border-emerald-500 bg-emerald-50 font-bold text-emerald-900 ring-2 ring-emerald-400/20' : 'border-slate-200 hover:bg-slate-50 text-slate-700'">
                                        <span>BCA Virtual Account</span>
                                        <span class="text-[9px] bg-blue-100 text-blue-800 font-bold px-1.5 py-0.5 rounded">BCA</span>
                                    </button>

                                    <button type="button" @click="updateMethod('mandiri_va')"
                                        class="p-2.5 rounded-xl border text-left transition flex items-center justify-between"
                                        :class="method === 'mandiri_va' ? 'border-emerald-500 bg-emerald-50 font-bold text-emerald-900 ring-2 ring-emerald-400/20' : 'border-slate-200 hover:bg-slate-50 text-slate-700'">
                                        <span>Mandiri VA / Bill</span>
                                        <span class="text-[9px] bg-amber-100 text-amber-800 font-bold px-1.5 py-0.5 rounded">MDR</span>
                                    </button>

                                    <button type="button" @click="updateMethod('bni_va')"
                                        class="p-2.5 rounded-xl border text-left transition flex items-center justify-between"
                                        :class="method === 'bni_va' ? 'border-emerald-500 bg-emerald-50 font-bold text-emerald-900 ring-2 ring-emerald-400/20' : 'border-slate-200 hover:bg-slate-50 text-slate-700'">
                                        <span>BNI Virtual Account</span>
                                        <span class="text-[9px] bg-orange-100 text-orange-800 font-bold px-1.5 py-0.5 rounded">BNI</span>
                                    </button>

                                    <button type="button" @click="updateMethod('qris')"
                                        class="p-2.5 rounded-xl border text-left transition flex items-center justify-between"
                                        :class="method === 'qris' ? 'border-emerald-500 bg-emerald-50 font-bold text-emerald-900 ring-2 ring-emerald-400/20' : 'border-slate-200 hover:bg-slate-50 text-slate-700'">
                                        <span>QRIS &amp; E-Wallet</span>
                                        <span class="text-[9px] bg-rose-100 text-rose-800 font-bold px-1.5 py-0.5 rounded">QRIS</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Kotak Nomor Virtual Account / QRIS -->
                            <div class="p-4 rounded-2xl bg-slate-900 text-white space-y-2.5 text-xs">
                                <div class="flex items-center justify-between text-slate-300">
                                    <span class="flex items-center gap-1.5 text-[11px]">
                                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                        <span x-text="method === 'qris' ? 'QRIS Dinamis Otomatis:' : 'Nomor Virtual Account ' + method.toUpperCase().replace('_VA', '') + ':'"></span>
                                    </span>
                                    <span class="text-[10px] text-amber-300 font-mono">Batas: 2 Jam</span>
                                </div>

                                <template x-if="method !== 'qris'">
                                    <div class="flex items-center justify-between bg-slate-800 p-3 rounded-xl border border-slate-700">
                                        <div>
                                            <span class="font-mono text-base font-black text-emerald-400 select-all" x-text="vaNum"></span>
                                            <span class="text-[10px] text-slate-400 block mt-0.5">Atas Nama: <strong>CareAtHome Escrow System</strong></span>
                                        </div>
                                        <button type="button" @click="copyVA()"
                                            class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition">
                                            <span x-text="copied ? '✓ Tersalin' : 'Salin'"></span>
                                        </button>
                                    </div>
                                </template>

                                <template x-if="method === 'qris'">
                                    <div class="bg-white p-3 rounded-xl text-slate-900 text-center space-y-1">
                                        <div class="w-24 h-24 bg-slate-100 rounded-lg mx-auto flex items-center justify-center text-3xl border border-dashed border-slate-300">
                                            📱
                                        </div>
                                        <span class="text-[10px] text-slate-600 font-semibold block">Scan via GoPay, OVO, Dana, atau Mobile Banking</span>
                                    </div>
                                </template>
                            </div>

                            <!-- Action Buttons -->
                            <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-3">
                                <a href="{{ route('pasien.pesanan') }}" class="text-xs text-slate-500 hover:text-slate-800 font-semibold underline">
                                    Lihat Detail Booking
                                </a>

                                <button type="button"
                                    @click="isPaying = true; processPay('{{ $order->id }}', method)"
                                    :disabled="isPaying"
                                    class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/30 transition flex items-center gap-2">
                                    <span x-show="isPaying" class="animate-spin text-sm">⏳</span>
                                    <span x-text="isPaying ? 'Mengunci Dana di Escrow...' : 'Konfirmasi Bayar (Simulasi Escrow)'"></span>
                                </button>
                            </div>

                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- 5. DAFTAR PESANAN LUNAS TERKUNCI DI REKENING PENAMPUNG (ESCROW HELD) -->
        @if ($escrowHeldOrders->isNotEmpty())
            <div class="space-y-4 pt-6">
                <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">Dana Terkunci Aman di Rekening Penampung (Escrow)</h2>
                        <p class="text-xs text-slate-500">Pesanan berikut telah lunas dan dana ditahan oleh platform CareAtHome hingga asuhan keperawatan tuntas.</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                        {{ $totalHeld }} Pesanan Terproteksi
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($escrowHeldOrders as $order)
                        <div class="bg-white rounded-3xl border border-emerald-300/60 p-5 shadow-2xs space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] font-mono font-bold text-slate-400">{{ $order->order_number }}</span>
                                        <span class="text-[9px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">
                                            🛡️ Escrow Locked
                                        </span>
                                    </div>
                                    <h4 class="text-sm font-bold text-slate-900 mt-1">{{ $order->serviceCatalog->name ?? 'Tindakan Keperawatan' }}</h4>
                                    <p class="text-[11px] text-slate-500">Jadwal: {{ $order->scheduled_date->format('d M Y') }} • {{ $order->scheduled_time }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] text-slate-400 uppercase font-bold block">Total Dana Escrow</span>
                                    <span class="text-base font-extrabold text-emerald-700">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            <div class="p-3 rounded-2xl bg-emerald-50/50 border border-emerald-200 text-xs flex items-center justify-between text-emerald-950">
                                <span>Status Nakes: <strong>{{ $order->status_label }}</strong></span>
                                <a href="{{ route('pasien.pesanan') }}" class="font-bold text-emerald-700 hover:underline">
                                    Lacak Rute &rarr;
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </main>

    <!-- 6. FOOTER -->
    <footer class="mt-auto border-t border-slate-200 bg-white py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-xs text-slate-500">
            CareAtHome Health-Tech Platform &copy; 2026. Transaksi Non-Tunai Berbasis Escrow Dilindungi Standar Perbankan Terdaftar.
        </div>
    </footer>

    <!-- Alpine App Logic for Payment Execution -->
    <script>
        function patientPaymentApp() {
            return {
                processPay(orderId, paymentMethod) {
                    const url = '/pasien/pesanan/' + orderId + '/bayar';

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({
                            payment_method: paymentMethod
                        })
                    })
                    .then(async (response) => {
                        const data = await response.json();
                        if (!response.ok) {
                            throw new Error(data.message || 'Pembayaran gagal diproses.');
                        }
                        return data;
                    })
                    .then((res) => {
                        alert(res.message);
                        window.location.reload();
                    })
                    .catch((err) => {
                        alert(err.message || 'Terjadi kesalahan sistem.');
                    });
                }
            };
        }
    </script>
</body>
</html>
