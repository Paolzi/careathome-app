<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Jadwal &amp; Pesanan Kunjungan Saya - CareAtHome</title>
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
    x-data="patientOrdersApp()">

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
                                Care<span class="text-emerald-600">At</span>Home
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
                        <a href="{{ route('pasien.pesanan') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 transition">
                            Jadwal Kunjungan Saya
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
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-6 border border-slate-700/50">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Manajemen Kunjungan Homecare
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Jadwal Kunjungan &amp; Pesanan Saya
                </h1>
                <p class="text-slate-300 text-xs sm:text-sm max-w-2xl leading-relaxed">
                    Pantau status kedatangan mitra perawat medis, konfirmasi jadwal kunjungan ke rumah, serta lakukan pembayaran aman dengan jaminan rekening penampung (Escrow).
                </p>
            </div>

            <div class="shrink-0 flex sm:flex-col items-center sm:items-end justify-between gap-3 border-t sm:border-t-0 sm:border-l border-slate-700/60 pt-4 sm:pt-0 sm:pl-6">
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

        <!-- 4. TAB CONTENT: ACTIVE ORDERS -->
        <div x-show="activeTab === 'active'" class="space-y-4">
            @if ($activeOrders->isEmpty())
                <!-- EMPTY STATE (Sesuai Konfirmasi Pengguna) -->
                <div class="bg-white rounded-3xl border border-slate-200 p-10 sm:p-14 text-center max-w-2xl mx-auto space-y-4 shadow-sm">
                    <div class="w-20 h-20 rounded-3xl bg-emerald-50 text-emerald-600 mx-auto flex items-center justify-center text-4xl shadow-inner">
                        🗓️
                    </div>
                    <div class="space-y-1.5">
                        <h3 class="text-xl font-bold text-slate-900">Belum Ada Jadwal Kunjungan Aktif</h3>
                        <p class="text-xs sm:text-sm text-slate-500 leading-relaxed max-w-md mx-auto">
                            Anda belum memiliki pesanan kunjungan perawat homecare. Karena pemesanan dan pembayaran memerlukan pemilihan tindakan medis terlebih dahulu, silakan pilih layanan di katalog untuk menentukan jadwal.
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
                <!-- DAFTAR KARTU PESANAN AKTIF -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    @foreach ($activeOrders as $order)
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:border-emerald-300 transition-all flex flex-col justify-between overflow-hidden">
                            <!-- Card Header -->
                            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-xl shadow-xs">
                                        {{ $order->serviceCatalog->image_emoji ?? '🩺' }}
                                    </div>
                                    <div>
                                        <span class="text-[10px] font-mono font-bold text-slate-500 uppercase tracking-wider block">
                                            {{ $order->order_number }}
                                        </span>
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
                                <span class="text-[11px] font-bold px-2.5 py-1 rounded-full border {{ $badgeClasses }}">
                                    {{ $order->status_label }}
                                </span>
                            </div>

                            <!-- Card Body: Schedule & Location -->
                            <div class="p-5 space-y-3.5 text-xs">
                                <div class="grid grid-cols-2 gap-2 p-3 rounded-xl bg-slate-50 border border-slate-100">
                                    <div>
                                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Jadwal Kunjungan</span>
                                        <span class="font-bold text-slate-900">{{ $order->scheduled_date->format('d M Y') }}</span>
                                        <span class="text-[11px] text-emerald-700 font-semibold block">{{ $order->scheduled_time }}</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Pasien &amp; Kontak</span>
                                        <span class="font-semibold text-slate-900 block truncate">{{ $order->patient_name }}</span>
                                        <span class="text-[11px] text-slate-500 block">{{ $order->patient_phone }}</span>
                                    </div>
                                </div>

                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Alamat Kunjungan</span>
                                    <p class="text-slate-700 line-clamp-2 mt-0.5 leading-relaxed">{{ $order->address }}</p>
                                    <span class="text-[11px] text-slate-400 font-medium mt-1 block">
                                        📍 Jarak estimasi: <strong class="text-slate-700">{{ $order->distance_km }} km</strong>
                                    </span>
                                </div>

                                @if ($order->clinical_notes)
                                    <div class="pt-2 border-t border-slate-100">
                                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Catatan Medis Awal:</span>
                                        <p class="text-slate-600 italic line-clamp-2 mt-0.5">{{ $order->clinical_notes }}</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Card Footer: Total Amount & Escrow Payment Action -->
                            <div class="p-5 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-3">
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Total Tagihan (Escrow)</span>
                                    <span class="text-base font-extrabold text-emerald-700">
                                        Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                    </span>
                                </div>

                                <div>
                                    @if ($order->payment_status === 'paid')
                                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-100 text-emerald-800 text-xs font-bold border border-emerald-200">
                                            <span>✓ Dana Aman di Escrow</span>
                                        </div>
                                    @else
                                        <button type="button"
                                            @click="openPaymentModal({{ json_encode($order) }})"
                                            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm shadow-emerald-600/30 transition flex items-center gap-1.5">
                                            <span>💳 Bayar Sekarang</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- 5. TAB CONTENT: PAST ORDERS -->
        <div x-show="activeTab === 'past'" x-cloak class="space-y-4">
            @if ($pastOrders->isEmpty())
                <div class="bg-white rounded-3xl border border-slate-200 p-10 text-center max-w-lg mx-auto space-y-3">
                    <div class="text-3xl text-slate-400">📋</div>
                    <h3 class="text-base font-bold text-slate-800">Belum Ada Riwayat Kunjungan Selesai</h3>
                    <p class="text-xs text-slate-500">
                        Pesanan yang telah selesai dikerjakan perawat atau pesanan yang dibatalkan akan tersimpan rapi di tab riwayat ini.
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($pastOrders as $order)
                        <div class="bg-white rounded-2xl border border-slate-200 p-5 space-y-3 opacity-90">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="text-[10px] font-mono text-slate-400">{{ $order->order_number }}</span>
                                    <h4 class="text-sm font-bold text-slate-900">{{ $order->serviceCatalog->name ?? 'Tindakan' }}</h4>
                                </div>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $order->status === 'selesai' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200' }}">
                                    {{ $order->status_label }}
                                </span>
                            </div>
                            <div class="text-xs text-slate-600">
                                Jadwal: {{ $order->scheduled_date->format('d M Y') }} ({{ $order->scheduled_time }})
                            </div>
                            <div class="text-xs font-bold text-slate-800 pt-2 border-t border-slate-100 flex justify-between">
                                <span>Total Pembayaran:</span>
                                <span>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </main>

    <!-- 6. MODAL PEMBAYARAN ESCROW INTERAKTIF -->
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
            <div class="p-6 space-y-4">
                
                <!-- Ringkasan Order -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs space-y-2">
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
                    <label class="block text-xs font-bold text-slate-700">Pilih Metode Pembayaran Non-Tunai:</label>
                    
                    <div class="grid grid-cols-2 gap-2 text-xs">
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
                <div class="p-3.5 rounded-2xl bg-teal-50 border border-teal-200 text-teal-900 text-xs flex items-start gap-2.5">
                    <span class="text-lg">🛡️</span>
                    <div class="leading-relaxed">
                        <span class="font-bold block">Jaminan Perlindungan Escrow CareAtHome</span>
                        <span class="text-[11px] text-teal-800 block mt-0.5">
                            Dana ditampung sementara di rekening platform. Dana baru akan diteruskan ke perawat setelah kunjungan selesai dan E-Report telah Anda verifikasi.
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

    <!-- 7. FOOTER -->
    <footer class="mt-auto border-t border-slate-200 bg-white py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-xs text-slate-500">
            CareAtHome Health-Tech Platform &copy; 2026. Platform Layanan Keperawatan On-Demand Terstandar Kemenkes RI.
        </div>
    </footer>

    <!-- Alpine App Logic for Orders & Escrow Payment -->
    <script>
        function patientOrdersApp() {
            return {
                activeTab: 'active',
                paymentModalOpen: false,
                selectedOrder: null,
                paymentMethod: 'bca_va',
                processingPayment: false,
                paymentSuccessMsg: '',
                paymentErrorMsg: '',

                openPaymentModal(order) {
                    this.selectedOrder = order;
                    this.paymentMethod = 'bca_va';
                    this.paymentSuccessMsg = '';
                    this.paymentErrorMsg = '';
                    this.paymentModalOpen = true;
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
                }
            };
        }
    </script>
</body>
</html>
