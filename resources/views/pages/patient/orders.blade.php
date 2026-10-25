<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Jadwal Kunjungan &amp; Pelacakan Pesanan - CareAtHome</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Leaflet CSS & JS for Interactive Map Tracking (SCRUM-46) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <style>
        [x-cloak] { display: none !important; }
        .leaflet-container { font-family: inherit; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col"
    x-data="patientOrdersApp()">

    <!-- 1. TOP NAVBAR (KONSISTEN 100% DENGAN KATALOG & DASHBOARD) -->
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

                    <!-- Desktop Nav Links (Pill Style) -->
                    <nav class="hidden sm:flex items-center space-x-1 ml-2">
                        <a href="{{ route('dashboard') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition">
                            Dashboard
                        </a>
                        <a href="{{ route('pasien.katalog') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition">
                            Katalog Layanan
                        </a>
                        <a href="{{ route('pasien.pesanan') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 transition">
                            Jadwal Kunjungan
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

        <!-- Page Header Banner -->
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-6 border border-slate-700/50 relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="space-y-2 relative z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    SCRUM-46 &amp; SCRUM-47: Live Tracking &amp; Escrow Protection
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Jadwal Kunjungan &amp; Pelacakan Pesanan
                </h1>
                <p class="text-slate-300 text-xs sm:text-sm max-w-2xl leading-relaxed">
                    Pantau tahapan kedatangan nakes melalui <strong>Status Stepper</strong>, lacak posisi GPS secara real-time, serta kelola pembatalan dengan jaminan <strong>100% Refund Escrow</strong>.
                </p>
            </div>

            <div class="shrink-0 flex sm:flex-col items-center sm:items-end justify-between gap-3 border-t sm:border-t-0 sm:border-l border-slate-700/60 pt-4 sm:pt-0 sm:pl-6 relative z-10">
                <div>
                    <span class="text-[11px] text-slate-400 uppercase tracking-wider font-semibold block">Total Pesanan Tercatat</span>
                    <span class="text-2xl font-black text-emerald-400">{{ $totalOrders }} Pesanan</span>
                </div>
                <a href="{{ route('pasien.katalog') }}" class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-bold transition flex items-center gap-1.5 shadow-md">
                    <span>Cari Tindakan Baru</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>
        </div>

        <!-- 3. TABS SWITCHER -->
        <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
            <button type="button"
                @click="activeTab = 'active'"
                class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2"
                :class="activeTab === 'active' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'">
                <span>Jadwal Kunjungan Aktif</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold"
                    :class="activeTab === 'active' ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-700'">
                    {{ $activeOrders->count() }}
                </span>
            </button>

            <button type="button"
                @click="activeTab = 'past'"
                class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2"
                :class="activeTab === 'past' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'">
                <span>Riwayat Selesai &amp; Batal</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold"
                    :class="activeTab === 'past' ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-700'">
                    {{ $pastOrders->count() }}
                </span>
            </button>
        </div>

        <!-- 4. TAB CONTENT: ACTIVE ORDERS (SCRUM-46 & SCRUM-47) -->
        <div x-show="activeTab === 'active'" class="space-y-6">
            @if ($activeOrders->isEmpty())
                <!-- EMPTY STATE -->
                <div class="bg-white rounded-3xl border border-slate-200 p-10 sm:p-14 text-center max-w-2xl mx-auto space-y-4 shadow-sm">
                    <div class="w-20 h-20 rounded-3xl bg-emerald-50 text-emerald-600 mx-auto flex items-center justify-center text-4xl shadow-inner">
                        🗓️
                    </div>
                    <div class="space-y-1.5">
                        <h3 class="text-xl font-bold text-slate-900">Belum Ada Jadwal Kunjungan Aktif</h3>
                        <p class="text-xs sm:text-sm text-slate-500 leading-relaxed max-w-md mx-auto">
                            Saat ini belum ada pesanan kunjungan nakes yang sedang berlangsung. Karena tindakan medis dan tarif diatur oleh admin melalui katalog resmi, silakan jelajahi katalog layanan untuk membuat jadwal baru.
                        </p>
                    </div>
                    <div class="pt-3">
                        <a href="{{ route('pasien.katalog') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/25 transition">
                            <span>Jelajahi Katalog Layanan &amp; Buat Jadwal</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </div>
                </div>
            @else
                <!-- DAFTAR KARTU PESANAN AKTIF DENGAN STEPPER (SCRUM-46) -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    @foreach ($activeOrders as $order)
                        @php
                            // Index Stepper Aktif
                            $stepIndex = match($order->status) {
                                'menunggu_konfirmasi' => 1,
                                'diterima' => 2,
                                'dalam_perjalanan' => 3,
                                'tiba' => 4,
                                'tindakan_berlangsung' => 4,
                                'selesai' => 5,
                                default => 1,
                            };
                            if ($order->payment_status === 'paid' && $stepIndex < 3) {
                                $stepIndex = 3;
                            }
                        @endphp
                        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm hover:shadow-md hover:border-emerald-300 transition-all flex flex-col justify-between overflow-hidden">
                            
                            <!-- Card Header -->
                            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
                                <div class="flex items-center gap-3">
                                    <div class="w-11 h-11 rounded-2xl bg-white border border-slate-200 flex items-center justify-center text-2xl shadow-xs">
                                        {{ $order->serviceCatalog->image_emoji ?? '🩺' }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] font-mono font-bold text-slate-500 uppercase tracking-wider block">
                                                {{ $order->order_number }}
                                            </span>
                                            <span class="text-[9px] font-bold px-2 py-0.5 rounded-full {{ $order->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                                {{ $order->payment_status === 'paid' ? 'Escrow Paid' : 'Belum Bayar' }}
                                            </span>
                                        </div>
                                        <h3 class="text-sm font-bold text-slate-900 leading-snug">
                                            {{ $order->serviceCatalog->name ?? 'Tindakan Keperawatan' }}
                                        </h3>
                                    </div>
                                </div>

                                <!-- Status Badge -->
                                @php
                                    $badgeClasses = match($order->status) {
                                        'menunggu_konfirmasi' => 'bg-amber-50 text-amber-800 border-amber-200',
                                        'diterima' => 'bg-sky-50 text-sky-800 border-sky-200',
                                        'dalam_perjalanan' => 'bg-indigo-50 text-indigo-800 border-indigo-200',
                                        'tiba' => 'bg-purple-50 text-purple-800 border-purple-200',
                                        'tindakan_berlangsung' => 'bg-teal-50 text-teal-800 border-teal-200',
                                        default => 'bg-slate-100 text-slate-800 border-slate-200',
                                    };
                                @endphp
                                <span class="text-[11px] font-bold px-3 py-1 rounded-full border {{ $badgeClasses }}">
                                    {{ $order->status_label }}
                                </span>
                            </div>

                            <!-- SCRUM-46: VISUAL STATUS STEPPER ORDER -->
                            <div class="p-5 border-b border-slate-100 bg-white space-y-2">
                                <div class="flex items-center justify-between text-[11px] font-bold text-slate-500 mb-1">
                                    <span class="text-slate-400 uppercase tracking-wider text-[10px]">Alur Tracking Pesanan</span>
                                    <span class="text-emerald-700">Tahap {{ $stepIndex }} dari 5</span>
                                </div>

                                <!-- Stepper Bar -->
                                <div class="grid grid-cols-5 gap-1.5 relative pt-1">
                                    <!-- Step 1: Konfirmasi -->
                                    <div class="text-center space-y-1">
                                        <div class="w-full h-1.5 rounded-full transition-colors {{ $stepIndex >= 1 ? 'bg-emerald-600' : 'bg-slate-200' }}"></div>
                                        <span class="text-[10px] block font-semibold truncate {{ $stepIndex >= 1 ? 'text-emerald-700 font-bold' : 'text-slate-400' }}">
                                            Konfirmasi
                                        </span>
                                    </div>

                                    <!-- Step 2: Diterima Nakes -->
                                    <div class="text-center space-y-1">
                                        <div class="w-full h-1.5 rounded-full transition-colors {{ $stepIndex >= 2 ? 'bg-emerald-600' : 'bg-slate-200' }}"></div>
                                        <span class="text-[10px] block font-semibold truncate {{ $stepIndex >= 2 ? 'text-emerald-700 font-bold' : 'text-slate-400' }}">
                                            Diterima
                                        </span>
                                    </div>

                                    <!-- Step 3: Bayar Escrow -->
                                    <div class="text-center space-y-1">
                                        <div class="w-full h-1.5 rounded-full transition-colors {{ $stepIndex >= 3 ? 'bg-emerald-600' : 'bg-slate-200' }}"></div>
                                        <span class="text-[10px] block font-semibold truncate {{ $stepIndex >= 3 ? 'text-emerald-700 font-bold' : 'text-slate-400' }}">
                                            Escrow
                                        </span>
                                    </div>

                                    <!-- Step 4: Menuju Lokasi -->
                                    <div class="text-center space-y-1">
                                        <div class="w-full h-1.5 rounded-full transition-colors {{ $stepIndex >= 4 ? 'bg-emerald-600' : 'bg-slate-200' }}"></div>
                                        <span class="text-[10px] block font-semibold truncate {{ $stepIndex >= 4 ? 'text-emerald-700 font-bold' : 'text-slate-400' }}">
                                            Perjalanan
                                        </span>
                                    </div>

                                    <!-- Step 5: Tindakan Medis -->
                                    <div class="text-center space-y-1">
                                        <div class="w-full h-1.5 rounded-full transition-colors {{ $stepIndex >= 5 ? 'bg-emerald-600' : 'bg-slate-200' }}"></div>
                                        <span class="text-[10px] block font-semibold truncate {{ $stepIndex >= 5 ? 'text-emerald-700 font-bold' : 'text-slate-400' }}">
                                            Tindakan
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Body: Schedule, Location & Assigned Nurse -->
                            <div class="p-5 space-y-3.5 text-xs">
                                <div class="grid grid-cols-2 gap-2 p-3 rounded-2xl bg-slate-50 border border-slate-100">
                                    <div>
                                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Jadwal Kunjungan</span>
                                        <span class="font-bold text-slate-900">{{ $order->scheduled_date->format('d M Y') }}</span>
                                        <span class="text-[11px] text-emerald-700 font-semibold block">{{ $order->scheduled_time }}</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Penerima Layanan</span>
                                        <span class="font-semibold text-slate-900 block truncate">{{ $order->patient_name }}</span>
                                        <span class="text-[11px] text-slate-500 block">{{ $order->patient_phone }}</span>
                                    </div>
                                </div>

                                <!-- Perawat Penanggung Jawab -->
                                <div class="p-3 rounded-2xl border border-slate-100 flex items-center justify-between bg-emerald-50/30">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-emerald-600 text-white font-bold text-xs flex items-center justify-center">
                                            {{ substr($order->nurse->name ?? 'N', 0, 1) }}
                                        </div>
                                        <div>
                                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Mitra Nakes Bertugas</span>
                                            <span class="font-bold text-slate-900 block">{{ $order->nurse->name ?? 'Mencari Perawat Terdekat...' }}</span>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100/70 px-2 py-0.5 rounded-full border border-emerald-200">
                                        STR &amp; SIP Kemenkes
                                    </span>
                                </div>

                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Alamat Kunjungan</span>
                                    <p class="text-slate-700 line-clamp-2 mt-0.5 leading-relaxed">{{ $order->address }}</p>
                                    <span class="text-[11px] text-slate-400 font-medium mt-1 block">
                                        📍 Jarak estimasi: <strong class="text-slate-700">{{ $order->distance_km }} km</strong> (Radius Operasional GPS 10 km)
                                    </span>
                                </div>
                            </div>

                            <!-- Card Footer: Total Amount & Action Buttons -->
                            <div class="p-5 bg-slate-50 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Total Tagihan (Escrow)</span>
                                    <span class="text-base font-extrabold text-emerald-700">
                                        Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <!-- SCRUM-46: Detail & Tracking Button -->
                                    <button type="button"
                                        @click="openTrackingModal({{ json_encode($order) }})"
                                        class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-200 text-xs font-bold transition flex items-center gap-1.5 shadow-2xs">
                                        <span>🗺️ Detail &amp; Tracking</span>
                                    </button>

                                    @if ($order->payment_status !== 'paid')
                                        <button type="button"
                                            @click="openPaymentModal({{ json_encode($order) }})"
                                            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm shadow-emerald-600/30 transition flex items-center gap-1.5">
                                            <span>💳 Bayar Escrow</span>
                                        </button>
                                    @endif

                                    <!-- SCRUM-47: Pembatalan Button -->
                                    @if ($order->isCancellable())
                                        <button type="button"
                                            @click="openCancelModal({{ json_encode($order) }})"
                                            class="px-3 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold transition flex items-center gap-1">
                                            <span>Batalkan</span>
                                        </button>
                                    @endif
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- 5. TAB CONTENT: PAST ORDERS (RIWAYAT SELESAI & BATAL - SCRUM-47) -->
        <div x-show="activeTab === 'past'" x-cloak class="space-y-4">
            @if ($pastOrders->isEmpty())
                <div class="bg-white rounded-3xl border border-slate-200 p-10 text-center max-w-lg mx-auto space-y-3">
                    <div class="text-4xl text-slate-400">📋</div>
                    <h3 class="text-base font-bold text-slate-800">Belum Ada Riwayat Kunjungan</h3>
                    <p class="text-xs text-slate-500">
                        Pesanan yang telah selesai dikerjakan nakes atau pesanan yang dibatalkan dengan pengembalian dana escrow akan tersimpan rapi di sini.
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    @foreach ($pastOrders as $order)
                        <div class="bg-white rounded-3xl border border-slate-200 p-6 space-y-4 shadow-sm">
                            <div class="flex justify-between items-start gap-3">
                                <div>
                                    <span class="text-[10px] font-mono font-bold text-slate-400">{{ $order->order_number }}</span>
                                    <h4 class="text-base font-bold text-slate-900 mt-0.5">{{ $order->serviceCatalog->name ?? 'Tindakan Keperawatan' }}</h4>
                                    <p class="text-xs text-slate-500 mt-1">Jadwal: {{ $order->scheduled_date->format('d M Y') }} • {{ $order->scheduled_time }}</p>
                                </div>

                                <div class="text-right space-y-1">
                                    <span class="text-[11px] font-bold px-3 py-1 rounded-full inline-block {{ $order->status === 'selesai' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200' }}">
                                        {{ $order->status_label }}
                                    </span>
                                </div>
                            </div>

                            <!-- SCRUM-47: Detail Pembatalan & Refund Escrow -->
                            @if ($order->status === 'dibatalkan')
                                <div class="p-4 rounded-2xl bg-rose-50/60 border border-rose-200/80 text-xs space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-rose-900">Alasan Pembatalan:</span>
                                        <span class="text-[10px] text-slate-500">{{ $order->cancelled_at?->format('d M Y, H:i') }} WIB</span>
                                    </div>
                                    <p class="text-rose-800 italic">{{ $order->cancellation_reason ?: 'Dibatalkan oleh pasien atas permintaan sendiri.' }}</p>

                                    @if ($order->refund_status === 'refunded')
                                        <div class="pt-2 border-t border-rose-200 flex items-center justify-between text-emerald-800 font-bold">
                                            <span class="flex items-center gap-1">
                                                <span>🛡️</span>
                                                <span>Refund Escrow 100% Berhasil</span>
                                            </span>
                                            <span class="text-sm">Rp {{ number_format($order->refund_amount, 0, ',', '.') }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <div class="text-xs font-bold text-slate-800 pt-3 border-t border-slate-100 flex justify-between items-center">
                                <span class="text-slate-500">Total Transaksi:</span>
                                <span class="text-base font-extrabold text-slate-900">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </main>

    <!-- 6. MODAL SCRUM-46: DETAIL BOOKING & LIVE TRACKING GPS -->
    <div x-show="trackingModalOpen" x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-6"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div @click.away="trackingModalOpen = false"
            class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl border border-slate-200 overflow-hidden text-slate-800 max-h-[90vh] flex flex-col"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100">

            <!-- Modal Header -->
            <div class="px-6 py-5 bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-950 text-white flex items-center justify-between shrink-0">
                <div>
                    <span class="text-[10px] uppercase font-bold tracking-wider text-emerald-300">SCRUM-46 • Detail Booking &amp; Pelacakan Real-time</span>
                    <h3 class="text-lg font-bold mt-0.5" x-text="selectedOrder?.service_catalog?.name || 'Detail Pesanan Kunjungan'"></h3>
                    <p class="text-xs text-slate-300 font-mono" x-text="'No. Pesanan: ' + (selectedOrder?.order_number || '-')"></p>
                </div>
                <button type="button" @click="trackingModalOpen = false" class="text-slate-300 hover:text-white p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Body (Scrollable) -->
            <div class="p-6 overflow-y-auto space-y-5 text-xs">

                <!-- 1. Interactive Peta Geolokasi (Leaflet) -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-bold text-slate-800 flex items-center gap-1.5">
                            <span>🗺️</span>
                            <span>Peta Pelacakan Rute Nakes &amp; Titik Pasien</span>
                        </span>
                        <span class="text-[10px] text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200 font-bold"
                            x-text="'Estimasi Tiba: ~' + Math.max(10, Math.round((selectedOrder?.distance_km || 3) * 4)) + ' Menit'"></span>
                    </div>

                    <!-- Map Container -->
                    <div id="tracking-map" class="h-60 sm:h-64 w-full rounded-2xl border border-slate-200 shadow-inner z-0"></div>
                    <div class="flex items-center justify-between text-[11px] text-slate-500 mt-2 px-1">
                        <span>Titik Merah: Alamat Pasien</span>
                        <span>Titik Hijau: Posisi Mitra Nakes</span>
                        <span x-text="'Jarak: ' + (selectedOrder?.distance_km || '3.5') + ' km'"></span>
                    </div>
                </div>

                <!-- 2. Stepper Status Timeline -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                    <span class="font-bold text-slate-900 block text-xs">Timeline Status Pemesanan</span>
                    <div class="space-y-3 relative pl-4 border-l-2 border-emerald-500">
                        <div class="relative">
                            <span class="absolute -left-[21px] top-0.5 w-3 h-3 rounded-full bg-emerald-600 border-2 border-white"></span>
                            <span class="font-bold text-slate-900 block">1. Pesanan Dikonfirmasi Pasien</span>
                            <span class="text-slate-500 text-[11px] block">Jadwal: <strong x-text="selectedOrder?.scheduled_date + ' (' + selectedOrder?.scheduled_time + ')'"></strong></span>
                        </div>
                        <div class="relative">
                            <span class="absolute -left-[21px] top-0.5 w-3 h-3 rounded-full"
                                :class="selectedOrder?.payment_status === 'paid' ? 'bg-emerald-600 border-2 border-white' : 'bg-slate-300'"></span>
                            <span class="font-bold text-slate-900 block">2. Pembayaran Rekening Penampung (Escrow)</span>
                            <span class="text-slate-500 text-[11px] block" x-text="selectedOrder?.payment_status === 'paid' ? 'Dana lunas & terkunci aman di rekening escrow' : 'Menunggu pembayaran non-tunai pasien'"></span>
                        </div>
                        <div class="relative">
                            <span class="absolute -left-[21px] top-0.5 w-3 h-3 rounded-full"
                                :class="['dalam_perjalanan', 'tiba', 'tindakan_berlangsung', 'selesai'].includes(selectedOrder?.status) ? 'bg-emerald-600 border-2 border-white animate-ping' : 'bg-slate-300'"></span>
                            <span class="font-bold text-slate-900 block">3. Perawat Menuju Lokasi &amp; Tindakan Medis</span>
                            <span class="text-slate-500 text-[11px] block" x-text="'Status saat ini: ' + (selectedOrder?.status_label || '-')"></span>
                        </div>
                    </div>
                </div>

                <!-- 3. Profil Mitra Nakes & Info Pasien -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="p-4 rounded-2xl bg-emerald-50/50 border border-emerald-200 space-y-2">
                        <span class="text-[10px] font-bold uppercase text-emerald-800 block">Mitra Nakes Penanggung Jawab</span>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white font-extrabold flex items-center justify-center text-sm shadow-sm">
                                <span x-text="(selectedOrder?.nurse?.name || 'N').substring(0, 1)"></span>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900" x-text="selectedOrder?.nurse?.name || 'Ns. Siti Nurhaliza, S.Kep'"></h4>
                                <span class="text-[10px] text-emerald-700 font-semibold block">STR &amp; SIP Aktif Kemenkes RI</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between pt-2 border-t border-emerald-200/60 text-[11px]">
                            <span>⭐ Rating: 4.9 (120+ Kunjungan)</span>
                            <a :href="'https://wa.me/6281234567890?text=Halo%20Nakes%20CareAtHome%2C%20terkait%20pesanan%20' + (selectedOrder?.order_number || '')"
                                target="_blank"
                                class="text-emerald-700 font-bold hover:underline">
                                💬 Chat Nakes
                            </a>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-1.5">
                        <span class="text-[10px] font-bold uppercase text-slate-400 block">Penerima &amp; Lokasi Tindakan</span>
                        <p class="font-bold text-slate-900" x-text="selectedOrder?.patient_name"></p>
                        <p class="text-slate-600" x-text="selectedOrder?.patient_phone"></p>
                        <p class="text-slate-500 text-[11px] line-clamp-2" x-text="selectedOrder?.address"></p>
                    </div>
                </div>

                <!-- 4. Rincian Biaya (Price Breakdown Engine) -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                    <span class="font-bold text-slate-900 block text-xs">Rincian Plafon Tarif &amp; Escrow</span>
                    <div class="space-y-1 text-slate-600">
                        <div class="flex justify-between">
                            <span>Tarif Layanan Medis Terstandar:</span>
                            <span class="font-semibold text-slate-900" x-text="'Rp ' + Number(selectedOrder?.base_price || 0).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Biaya Transportasi GPS (<span x-text="(selectedOrder?.distance_km || 0) + ' km'"></span>):</span>
                            <span class="font-semibold text-slate-900" x-text="'Rp ' + Number(selectedOrder?.transport_fee || 0).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Biaya Layanan Aplikasi (Flat):</span>
                            <span class="font-semibold text-slate-900">Rp 5.000</span>
                        </div>
                        <div class="flex justify-between pt-2 border-t border-slate-200 text-sm font-black text-emerald-800">
                            <span>Total Pembayaran Escrow:</span>
                            <span x-text="'Rp ' + Number(selectedOrder?.total_amount || 0).toLocaleString('id-ID')"></span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between gap-3 shrink-0">
                <template x-if="['menunggu_konfirmasi', 'diterima'].includes(selectedOrder?.status)">
                    <button type="button" @click="trackingModalOpen = false; openCancelModal(selectedOrder)"
                        class="px-4 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold border border-rose-200 transition">
                        Batalkan Pesanan
                    </button>
                </template>

                <div class="flex items-center gap-2 ml-auto">
                    <button type="button" @click="trackingModalOpen = false"
                        class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-xs font-bold text-slate-700 transition">
                        Tutup
                    </button>
                    <template x-if="selectedOrder?.payment_status !== 'paid'">
                        <button type="button" @click="trackingModalOpen = false; openPaymentModal(selectedOrder)"
                            class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow transition">
                            Bayar Sekarang (Escrow)
                        </button>
                    </template>
                </div>
            </div>

        </div>
    </div>

    <!-- 7. MODAL SCRUM-47: PEMBATALAN PESANAN & REFUND ESCROW -->
    <div x-show="cancelModalOpen" x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-6"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div @click.away="if (!processingCancel) cancelModalOpen = false"
            class="bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-slate-200 overflow-hidden text-slate-800"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100">

            <!-- Modal Header -->
            <div class="px-6 py-5 bg-gradient-to-r from-rose-700 to-rose-900 text-white flex items-center justify-between">
                <div>
                    <span class="text-[10px] uppercase font-bold tracking-wider text-rose-200">SCRUM-47 • Pembatalan &amp; Refund</span>
                    <h3 class="text-lg font-bold mt-0.5">Konfirmasi Pembatalan Pesanan</h3>
                </div>
                <button type="button" @click="cancelModalOpen = false" :disabled="processingCancel" class="text-rose-200 hover:text-white p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-4 text-xs">

                <!-- Escrow Refund Assurance Notice -->
                <div class="p-4 rounded-2xl border text-xs leading-relaxed"
                    :class="selectedOrder?.payment_status === 'paid' ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-slate-50 border-slate-200 text-slate-700'">
                    <template x-if="selectedOrder?.payment_status === 'paid'">
                        <div class="flex items-start gap-3">
                            <span class="text-2xl">🛡️</span>
                            <div class="space-y-1">
                                <span class="font-bold block text-emerald-950">Jaminan 100% Pengembalian Dana Escrow</span>
                                <p class="text-emerald-800">
                                    Pesanan ini telah lunas dan dana tersimpan di rekening penampung. Karena dibatalkan sebelum nakes berangkat, dana senilai <strong x-text="'Rp ' + Number(selectedOrder?.total_amount || 0).toLocaleString('id-ID')"></strong> akan otomatis dikembalikan penuh (100% Refund).
                                </p>
                            </div>
                        </div>
                    </template>
                    <template x-if="selectedOrder?.payment_status !== 'paid'">
                        <div class="flex items-start gap-3">
                            <span class="text-2xl">ℹ️</span>
                            <div class="space-y-1">
                                <span class="font-bold block text-slate-900">Pembatalan Pesanan Belum Dibayar</span>
                                <p class="text-slate-600">
                                    Pesanan ini belum melalui proses pembayaran escrow, sehingga pembatalan dapat dilakukan langsung tanpa pengembalian dana.
                                </p>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Form Pilihan Alasan Pembatalan -->
                <div class="space-y-2">
                    <label class="block font-bold text-slate-800">Pilih Alasan Pembatalan Pesanan:</label>
                    <div class="space-y-1.5">
                        <label class="p-2.5 rounded-xl border cursor-pointer transition flex items-center gap-2"
                            :class="cancelReason === 'Perubahan jadwal mendadak / ada keperluan lain' ? 'border-rose-400 bg-rose-50 text-rose-900 font-bold' : 'border-slate-200 hover:bg-slate-50'">
                            <input type="radio" value="Perubahan jadwal mendadak / ada keperluan lain" x-model="cancelReason" class="text-rose-600" />
                            <span>Perubahan jadwal mendadak / ada keperluan lain</span>
                        </label>

                        <label class="p-2.5 rounded-xl border cursor-pointer transition flex items-center gap-2"
                            :class="cancelReason === 'Perawat belum kunjung merespons konfirmasi' ? 'border-rose-400 bg-rose-50 text-rose-900 font-bold' : 'border-slate-200 hover:bg-slate-50'">
                            <input type="radio" value="Perawat belum kunjung merespons konfirmasi" x-model="cancelReason" class="text-rose-600" />
                            <span>Perawat belum kunjung merespons konfirmasi</span>
                        </label>

                        <label class="p-2.5 rounded-xl border cursor-pointer transition flex items-center gap-2"
                            :class="cancelReason === 'Kondisi darurat / pasien dialihkan ke IGD Rumah Sakit' ? 'border-rose-400 bg-rose-50 text-rose-900 font-bold' : 'border-slate-200 hover:bg-slate-50'">
                            <input type="radio" value="Kondisi darurat / pasien dialihkan ke IGD Rumah Sakit" x-model="cancelReason" class="text-rose-600" />
                            <span>Kondisi darurat / pasien dialihkan ke IGD Rumah Sakit</span>
                        </label>

                        <label class="p-2.5 rounded-xl border cursor-pointer transition flex items-center gap-2"
                            :class="cancelReason === 'Salah memilih tindakan keperawatan di katalog' ? 'border-rose-400 bg-rose-50 text-rose-900 font-bold' : 'border-slate-200 hover:bg-slate-50'">
                            <input type="radio" value="Salah memilih tindakan keperawatan di katalog" x-model="cancelReason" class="text-rose-600" />
                            <span>Salah memilih tindakan keperawatan di katalog</span>
                        </label>

                        <label class="p-2.5 rounded-xl border cursor-pointer transition flex items-center gap-2"
                            :class="cancelReason === 'Lainnya' ? 'border-rose-400 bg-rose-50 text-rose-900 font-bold' : 'border-slate-200 hover:bg-slate-50'">
                            <input type="radio" value="Lainnya" x-model="cancelReason" class="text-rose-600" />
                            <span>Alasan lainnya</span>
                        </label>
                    </div>
                </div>

                <!-- Catatan Tambahan (Opsional) -->
                <div class="space-y-1">
                    <label class="block font-bold text-slate-700">Catatan Tambahan (Opsional):</label>
                    <textarea x-model="cancelNotes" rows="2" placeholder="Tuliskan keterangan tambahan bila ada..."
                        class="w-full rounded-xl border border-slate-200 text-xs p-2.5 focus:border-rose-500 focus:ring-rose-500"></textarea>
                </div>

                <!-- Alert Messages -->
                <template x-if="cancelSuccessMsg">
                    <div class="p-3.5 rounded-xl bg-emerald-100 text-emerald-900 text-xs font-semibold leading-relaxed" x-text="cancelSuccessMsg"></div>
                </template>
                <template x-if="cancelErrorMsg">
                    <div class="p-3.5 rounded-xl bg-rose-100 text-rose-900 text-xs font-semibold leading-relaxed" x-text="cancelErrorMsg"></div>
                </template>

            </div>

            <!-- Modal Footer -->
            <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-2.5">
                <button type="button" @click="cancelModalOpen = false" :disabled="processingCancel"
                    class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-xs font-bold text-slate-700 transition">
                    Kembali
                </button>
                <button type="button" @click="submitCancel()" :disabled="processingCancel"
                    class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow transition flex items-center gap-2">
                    <span x-show="processingCancel" class="animate-spin text-sm">⏳</span>
                    <span x-text="processingCancel ? 'Memproses Pembatalan & Refund...' : (selectedOrder?.payment_status === 'paid' ? 'Batalkan & Refund Escrow' : 'Konfirmasi Batalkan')"></span>
                </button>
            </div>

        </div>
    </div>

    <!-- 8. MODAL PEMBAYARAN ESCROW INTERAKTIF -->
    <div x-show="paymentModalOpen" x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-6"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div @click.away="if (!processingPayment) paymentModalOpen = false"
            class="bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-slate-200 overflow-hidden text-slate-800"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100">

            <!-- Modal Header -->
            <div class="px-6 py-5 bg-gradient-to-r from-emerald-600 to-teal-700 text-white flex items-center justify-between">
                <div>
                    <span class="text-[10px] uppercase font-bold tracking-wider text-emerald-200">Gateway Pembayaran Escrow</span>
                    <h3 class="text-lg font-bold mt-0.5">Konfirmasi Pembayaran Aman</h3>
                </div>
                <button type="button" @click="paymentModalOpen = false" class="text-emerald-200 hover:text-white p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-4 text-xs">
                
                <!-- Ringkasan Order -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Nomor Pesanan:</span>
                        <span class="font-mono font-bold text-slate-900" x-text="selectedOrder?.order_number"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Tindakan Medis:</span>
                        <span class="font-semibold text-slate-900" x-text="selectedOrder?.service_catalog?.name || 'Tindakan Keperawatan'"></span>
                    </div>
                    <div class="flex justify-between items-baseline pt-2 border-t border-slate-200">
                        <span class="text-slate-800 font-bold">Total Pembayaran:</span>
                        <span class="text-lg font-black text-emerald-700" x-text="'Rp ' + Number(selectedOrder?.total_amount || 0).toLocaleString('id-ID')"></span>
                    </div>
                </div>

                <!-- Pilihan Metode Pembayaran Escrow -->
                <div class="space-y-2">
                    <label class="block font-bold text-slate-700">Pilih Metode Pembayaran Non-Tunai:</label>
                    
                    <div class="grid grid-cols-2 gap-2">
                        <label class="p-3 rounded-xl border cursor-pointer transition flex items-center gap-2"
                            :class="paymentMethod === 'bca_va' ? 'border-emerald-500 bg-emerald-50/50 text-emerald-900 font-bold' : 'border-slate-200 hover:bg-slate-50'">
                            <input type="radio" value="bca_va" x-model="paymentMethod" class="text-emerald-600" />
                            <span>BCA Virtual Account</span>
                        </label>

                        <label class="p-3 rounded-xl border cursor-pointer transition flex items-center gap-2"
                            :class="paymentMethod === 'mandiri_va' ? 'border-emerald-500 bg-emerald-50/50 text-emerald-900 font-bold' : 'border-slate-200 hover:bg-slate-50'">
                            <input type="radio" value="mandiri_va" x-model="paymentMethod" class="text-emerald-600" />
                            <span>Mandiri VA</span>
                        </label>

                        <label class="p-3 rounded-xl border cursor-pointer transition flex items-center gap-2"
                            :class="paymentMethod === 'bni_va' ? 'border-emerald-500 bg-emerald-50/50 text-emerald-900 font-bold' : 'border-slate-200 hover:bg-slate-50'">
                            <input type="radio" value="bni_va" x-model="paymentMethod" class="text-emerald-600" />
                            <span>BNI Virtual Account</span>
                        </label>

                        <label class="p-3 rounded-xl border cursor-pointer transition flex items-center gap-2"
                            :class="paymentMethod === 'qris' ? 'border-emerald-500 bg-emerald-50/50 text-emerald-900 font-bold' : 'border-slate-200 hover:bg-slate-50'">
                            <input type="radio" value="qris" x-model="paymentMethod" class="text-emerald-600" />
                            <span>QRIS / E-Wallet</span>
                        </label>
                    </div>
                </div>

                <!-- Jaminan Rekening Penampung (Escrow) -->
                <div class="p-3.5 rounded-2xl bg-teal-50 border border-teal-200 text-teal-900 flex items-start gap-2.5">
                    <span class="text-lg">🛡️</span>
                    <div class="leading-relaxed">
                        <span class="font-bold block">Jaminan Perlindungan Escrow CareAtHome</span>
                        <span class="text-[11px] text-teal-800 block mt-0.5">
                            Dana ditampung sementara di rekening platform. Dana baru akan diteruskan ke perawat setelah kunjungan selesai dan E-Report telah Anda verifikasi. Jika pesanan dibatalkan sebelum tindakan, dana dikembalikan 100%.
                        </span>
                    </div>
                </div>

                <!-- Pesan Notifikasi Sukses / Error -->
                <template x-if="paymentSuccessMsg">
                    <div class="p-3 rounded-xl bg-emerald-100 text-emerald-800 text-xs font-semibold" x-text="paymentSuccessMsg"></div>
                </template>
                <template x-if="paymentErrorMsg">
                    <div class="p-3 rounded-xl bg-rose-100 text-rose-800 text-xs font-semibold" x-text="paymentErrorMsg"></div>
                </template>

            </div>

            <!-- Modal Footer -->
            <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-2.5">
                <button type="button" @click="paymentModalOpen = false" :disabled="processingPayment"
                    class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-xs font-bold text-slate-700 transition">
                    Batal
                </button>
                <button type="button" @click="submitPayment()" :disabled="processingPayment"
                    class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/30 transition flex items-center gap-2">
                    <span x-show="processingPayment" class="animate-spin text-sm">⏳</span>
                    <span x-text="processingPayment ? 'Memproses Escrow...' : 'Konfirmasi Pembayaran Escrow'"></span>
                </button>
            </div>

        </div>
    </div>

    <!-- 9. FOOTER -->
    <footer class="mt-auto border-t border-slate-200 bg-white py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-xs text-slate-500">
            CareAtHome Health-Tech Platform &copy; 2026. Platform Layanan Keperawatan On-Demand Terstandar Kemenkes RI.
        </div>
    </footer>

    <!-- Alpine App Logic for Orders, Tracking & Cancellation -->
    <script>
        function patientOrdersApp() {
            return {
                activeTab: 'active',
                paymentModalOpen: false,
                trackingModalOpen: false,
                cancelModalOpen: false,
                selectedOrder: null,

                // Payment state
                paymentMethod: 'bca_va',
                processingPayment: false,
                paymentSuccessMsg: '',
                paymentErrorMsg: '',

                // Cancel state (SCRUM-47)
                cancelReason: 'Perubahan jadwal mendadak / ada keperluan lain',
                cancelNotes: '',
                processingCancel: false,
                cancelSuccessMsg: '',
                cancelErrorMsg: '',

                // Leaflet Map state (SCRUM-46)
                trackingMap: null,
                patientMarker: null,
                nurseMarker: null,
                routeLine: null,
                radiusCircle: null,

                openPaymentModal(order) {
                    this.selectedOrder = order;
                    this.paymentMethod = 'bca_va';
                    this.paymentSuccessMsg = '';
                    this.paymentErrorMsg = '';
                    this.paymentModalOpen = true;
                },

                openCancelModal(order) {
                    this.selectedOrder = order;
                    this.cancelReason = 'Perubahan jadwal mendadak / ada keperluan lain';
                    this.cancelNotes = '';
                    this.cancelSuccessMsg = '';
                    this.cancelErrorMsg = '';
                    this.cancelModalOpen = true;
                },

                openTrackingModal(order) {
                    this.selectedOrder = order;
                    this.trackingModalOpen = true;

                    // Beri waktu modal untuk dirender di DOM sebelum init Leaflet map
                    this.$nextTick(() => {
                        this.initTrackingMap(order);
                    });
                },

                initTrackingMap(order) {
                    const lat = parseFloat(order.latitude) || -6.2088;
                    const lng = parseFloat(order.longitude) || 106.8456;
                    
                    // Posisi simulasi perawat (dalam radius operasional ~3 km)
                    const nurseLat = lat + 0.015;
                    const nurseLng = lng - 0.012;

                    const mapContainer = document.getElementById('tracking-map');
                    if (!mapContainer) return;

                    if (this.trackingMap) {
                        this.trackingMap.remove();
                        this.trackingMap = null;
                    }

                    this.trackingMap = L.map('tracking-map').setView([lat, lng], 13);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(this.trackingMap);

                    // Marker Rumah Pasien (Merah)
                    this.patientMarker = L.circleMarker([lat, lng], {
                        radius: 9,
                        fillColor: '#e11d48',
                        color: '#ffffff',
                        weight: 2,
                        fillOpacity: 1
                    }).addTo(this.trackingMap)
                      .bindPopup('<strong>Titik Alamat Pasien</strong><br>' + (order.patient_name || 'Rumah'))
                      .openPopup();

                    // Marker Perawat (Hijau)
                    this.nurseMarker = L.circleMarker([nurseLat, nurseLng], {
                        radius: 9,
                        fillColor: '#059669',
                        color: '#ffffff',
                        weight: 2,
                        fillOpacity: 1
                    }).addTo(this.trackingMap)
                      .bindPopup('<strong>Mitra Nakes Bergerak</strong><br>' + (order.nurse?.name || 'Perawat Homecare'));

                    // Garis Rute Perjalanan
                    this.routeLine = L.polyline([[nurseLat, nurseLng], [lat, lng]], {
                        color: '#059669',
                        weight: 4,
                        opacity: 0.8,
                        dashArray: '6, 8'
                    }).addTo(this.trackingMap);

                    // Lingkaran Radius Operasional 10 KM
                    this.radiusCircle = L.circle([lat, lng], {
                        radius: 10000,
                        color: '#10b981',
                        fillColor: '#10b981',
                        fillOpacity: 0.05,
                        weight: 1
                    }).addTo(this.trackingMap);

                    // Auto-fit kedua titik
                    this.trackingMap.fitBounds([
                        [lat, lng],
                        [nurseLat, nurseLng]
                    ], { padding: [40, 40] });

                    setTimeout(() => {
                        if (this.trackingMap) {
                            this.trackingMap.invalidateSize();
                        }
                    }, 300);
                },

                submitPayment() {
                    if (!this.selectedOrder) return;
                    this.processingPayment = true;
                    this.paymentSuccessMsg = '';
                    this.paymentErrorMsg = '';

                    const url = '/pasien/pesanan/' + this.selectedOrder.id + '/bayar';

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({
                            payment_method: this.paymentMethod,
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
                        this.processingPayment = false;
                        this.paymentSuccessMsg = res.message;
                        setTimeout(() => {
                            window.location.reload();
                        }, 1200);
                    })
                    .catch((err) => {
                        this.processingPayment = false;
                        this.paymentErrorMsg = err.message || 'Terjadi kesalahan sistem.';
                    });
                },

                submitCancel() {
                    if (!this.selectedOrder) return;
                    this.processingCancel = true;
                    this.cancelSuccessMsg = '';
                    this.cancelErrorMsg = '';

                    const url = '/pasien/pesanan/' + this.selectedOrder.id + '/batal';

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({
                            reason: this.cancelReason,
                            notes: this.cancelNotes,
                        })
                    })
                    .then(async (response) => {
                        const data = await response.json();
                        if (!response.ok) {
                            throw new Error(data.message || 'Pembatalan pesanan gagal.');
                        }
                        return data;
                    })
                    .then((res) => {
                        this.processingCancel = false;
                        this.cancelSuccessMsg = res.message;
                        setTimeout(() => {
                            window.location.reload();
                        }, 1400);
                    })
                    .catch((err) => {
                        this.processingCancel = false;
                        this.cancelErrorMsg = err.message || 'Terjadi kesalahan sistem saat membatalkan pesanan.';
                    });
                }
            };
        }
    </script>
</body>
</html>
