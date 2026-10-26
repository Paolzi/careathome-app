<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pusat Pembatalan Pesanan &amp; Refund Escrow - CareAtHome</title>
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
    x-data="patientCancellationApp()">

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
                        <a href="{{ route('pasien.pembayaran') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition flex items-center gap-1.5">
                            <span>Pembayaran Escrow</span>
                            <span class="text-[9px] font-extrabold px-1.5 py-0.2 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300">Escrow</span>
                        </a>
                        <a href="{{ route('pasien.pembatalan') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold text-rose-700 bg-rose-50 border border-rose-200 transition">
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

        <!-- Page Header Banner Khusus Pembatalan & Refund -->
        <div class="bg-gradient-to-r from-rose-950 via-slate-900 to-slate-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-6 border border-rose-900/40 relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-rose-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="space-y-2 relative z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-500/20 border border-rose-400/30 text-rose-300 text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-rose-400 animate-pulse"></span>
                    Kebijakan Pembatalan &amp; Pengembalian Dana Escrow 100%
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Pusat Pembatalan Pesanan &amp; Refund Escrow
                </h1>
                <p class="text-slate-300 text-xs sm:text-sm max-w-2xl leading-relaxed">
                    Ajukan pembatalan pesanan secara mandiri dengan perlindungan penuh hak pasien. Pesanan yang dibatalkan sebelum mitra perawat diberangkatkan dijamin <strong>100% pengembalian dana dari rekening penampung (Escrow)</strong> ke rekening Anda tanpa potongan platform.
                </p>
            </div>

            <div class="shrink-0 grid grid-cols-2 sm:grid-cols-2 gap-4 border-t sm:border-t-0 sm:border-l border-slate-700/60 pt-4 sm:pt-0 sm:pl-6 relative z-10 text-right">
                <div class="p-3 bg-white/5 rounded-2xl border border-white/10 text-left">
                    <span class="text-[10px] text-slate-300 uppercase tracking-wider font-bold block">Total Dibatalkan</span>
                    <span class="text-2xl font-black text-rose-400">{{ $totalCancelled }} Pesanan</span>
                </div>
                <div class="p-3 bg-white/5 rounded-2xl border border-white/10 text-left">
                    <span class="text-[10px] text-emerald-300 uppercase tracking-wider font-bold block">Dana Dikembalikan</span>
                    <span class="text-xl sm:text-2xl font-black text-emerald-400">Rp {{ number_format($totalRefundAmount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- 3. ATURAN PEMBATALAN & GARANSI REFUND ESCROW -->
        <div class="p-5 rounded-3xl bg-white border border-slate-200/90 shadow-2xs space-y-3 text-xs leading-relaxed">
            <h3 class="font-bold text-slate-900 flex items-center gap-2">
                <span>🛡️</span>
                <span>Ketentuan Garansi 100% Pengembalian Dana (Escrow Protection)</span>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-slate-600 pt-1">
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                    <span class="font-bold text-slate-800 block text-[11px]">1. Pembatalan Mandiri</span>
                    <p class="text-[11px]">Dapat dilakukan kapan saja selama status pesanan masih <em>Menunggu Konfirmasi</em> atau <em>Diterima Perawat</em>.</p>
                </div>
                <div class="p-3.5 rounded-2xl bg-emerald-50/60 border border-emerald-100 text-emerald-950 space-y-1">
                    <span class="font-bold text-emerald-900 block text-[11px]">2. 100% Refund Otomatis</span>
                    <p class="text-[11px]">Dana yang telah disetor ke rekening penampung (Escrow) dikembalikan penuh tanpa biaya penalti maupun potongan platform fee.</p>
                </div>
                <div class="p-3.5 rounded-2xl bg-amber-50/60 border border-amber-100 text-amber-950 space-y-1">
                    <span class="font-bold text-amber-900 block text-[11px]">3. Perawat Dalam Perjalanan</span>
                    <p class="text-[11px]">Jika nakes telah bergerak ke lokasi, pembatalan diarahkan melalui konfirmasi pusat bantuan untuk perlindungan kompensasi transport.</p>
                </div>
            </div>
        </div>

        <!-- 4. PESANAN YANG MEMENUHI SYARAT PEMBATALAN -->
        <div class="space-y-4">
            <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Pesanan Aktif yang Dapat Dibatalkan</h2>
                    <p class="text-xs text-slate-500">Pilih pesanan di bawah ini jika Anda ingin mengajukan pembatalan sebelum tindakan dimulai.</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                    {{ $cancellableOrders->count() }} Pesanan
                </span>
            </div>

            @if ($cancellableOrders->isEmpty())
                <div class="p-8 rounded-3xl bg-white border border-slate-200 text-center space-y-2">
                    <span class="text-3xl block">📋</span>
                    <h4 class="text-sm font-bold text-slate-800">Tidak Ada Pesanan yang Memenuhi Syarat Pembatalan</h4>
                    <p class="text-xs text-slate-500 max-w-md mx-auto">
                        Saat ini Anda tidak memiliki pesanan kunjungan aktif yang berstatus menunggu atau diterima.
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    @foreach ($cancellableOrders as $order)
                        <div class="bg-white rounded-3xl border border-slate-200 p-6 space-y-4 shadow-sm flex flex-col justify-between">
                            <div class="space-y-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-xl">
                                            {{ $order->serviceCatalog->image_emoji ?? '🩺' }}
                                        </div>
                                        <div>
                                            <span class="text-[10px] font-mono font-bold text-slate-400">{{ $order->order_number }}</span>
                                            <h4 class="text-sm font-bold text-slate-900 leading-snug">{{ $order->serviceCatalog->name ?? 'Tindakan Keperawatan' }}</h4>
                                        </div>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $order->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $order->payment_status === 'paid' ? '🛡️ Dana di Escrow' : 'Belum Bayar' }}
                                    </span>
                                </div>

                                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 text-xs space-y-1">
                                    <div class="flex justify-between text-slate-600">
                                        <span>Jadwal:</span>
                                        <span class="font-bold text-slate-900">{{ $order->scheduled_date->format('d M Y') }} ({{ $order->scheduled_time }})</span>
                                    </div>
                                    <div class="flex justify-between text-slate-600">
                                        <span>Mitra Nakes:</span>
                                        <span class="font-bold text-slate-900">{{ $order->nurse->name ?? 'Menunggu Konfirmasi Nakes' }}</span>
                                    </div>
                                    <div class="flex justify-between text-slate-600 pt-1 border-t border-slate-200">
                                        <span>Nilai Transaksi:</span>
                                        <span class="font-black text-slate-900">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-3">
                                <span class="text-[11px] text-slate-500">
                                    {{ $order->payment_status === 'paid' ? 'Garansi refund 100% dana escrow.' : 'Dapat dibatalkan tanpa biaya.' }}
                                </span>
                                <button type="button" @click="openCancelModal({{ json_encode($order) }})"
                                    class="px-4 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold transition">
                                    Batalkan Pesanan Ini
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- 5. RIWAYAT PESANAN DIBATALKAN & AUDIT REFUND ESCROW -->
        <div class="space-y-4 pt-6">
            <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Riwayat Pembatalan &amp; Pengembalian Dana</h2>
                    <p class="text-xs text-slate-500">Arsip pencatatan pesanan yang dibatalkan beserta transparansi pengembalian dana escrow ke pasien.</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200">
                    {{ $totalCancelled }} Riwayat Dibatalkan
                </span>
            </div>

            @if ($cancelledOrders->isEmpty())
                <div class="p-10 rounded-3xl bg-white border border-slate-200 text-center space-y-2 max-w-lg mx-auto">
                    <span class="text-4xl block">🎉</span>
                    <h4 class="text-sm font-bold text-slate-800">Belum Ada Riwayat Pembatalan</h4>
                    <p class="text-xs text-slate-500">
                        Seluruh pesanan Anda berjalan sesuai jadwal atau belum pernah ada riwayat pembatalan transaksi.
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    @foreach ($cancelledOrders as $order)
                        <div class="bg-white rounded-3xl border border-rose-200/80 p-6 space-y-4 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <span class="text-[10px] font-mono font-bold text-slate-400">{{ $order->order_number }}</span>
                                    <h4 class="text-sm font-bold text-slate-900 mt-0.5">{{ $order->serviceCatalog->name ?? 'Tindakan Keperawatan' }}</h4>
                                    <p class="text-[11px] text-slate-500 mt-0.5">Jadwal Sebelumnya: {{ $order->scheduled_date->format('d M Y') }} • {{ $order->scheduled_time }}</p>
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-800 border border-rose-200">
                                    Dibatalkan
                                </span>
                            </div>

                            <!-- Alasan Pembatalan & Status Refund Escrow -->
                            <div class="p-4 rounded-2xl bg-rose-50/60 border border-rose-200/70 text-xs space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-rose-900">Alasan Pembatalan:</span>
                                    <span class="text-[10px] text-slate-500">{{ $order->cancelled_at?->format('d M Y, H:i') }} WIB</span>
                                </div>
                                <p class="text-rose-800 italic">{{ $order->cancellation_reason ?: 'Dibatalkan oleh pasien.' }}</p>

                                @if ($order->refund_status === 'refunded')
                                    <div class="pt-2 border-t border-rose-200 flex items-center justify-between text-emerald-800 font-bold">
                                        <span class="flex items-center gap-1.5">
                                            <span>🛡️</span>
                                            <span>Refund Escrow 100% Berhasil</span>
                                        </span>
                                        <span class="text-sm">Rp {{ number_format($order->refund_amount, 0, ',', '.') }}</span>
                                    </div>
                                    <span class="text-[10px] text-emerald-700 block mt-0.5">
                                        {{ $order->refund_notes ?: 'Pengembalian dana dari rekening penampung telah selesai diproses.' }}
                                    </span>
                                @else
                                    <div class="pt-1 text-[11px] text-slate-500">
                                        Status Transaksi: <strong>Tidak ada pemotongan saldo (Belum Bayar)</strong>
                                    </div>
                                @endif
                            </div>

                            <div class="text-xs font-bold text-slate-800 pt-3 border-t border-slate-100 flex justify-between items-center">
                                <span class="text-slate-500">Total Nilai Pesanan:</span>
                                <span class="text-sm font-extrabold text-slate-900">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </main>

    <!-- 6. MODAL KONFIRMASI PEMBATALAN PESANAN -->
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
                    <span class="text-[10px] uppercase font-bold tracking-wider text-rose-200">Konfirmasi Pembatalan &amp; Refund</span>
                    <h3 class="text-lg font-bold mt-0.5">Batalkan Pesanan Kunjungan</h3>
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
                                    Pesanan ini telah lunas dan dana tersimpan di rekening penampung. Karena dibatalkan sebelum perawat berangkat, dana senilai <strong x-text="'Rp ' + Number(selectedOrder?.total_amount || 0).toLocaleString('id-ID')"></strong> akan otomatis dikembalikan penuh (100% Refund tanpa potongan).
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
                                    Pesanan ini belum melalui proses pembayaran escrow, sehingga pembatalan dapat dilakukan langsung tanpa ada proses pengembalian dana.
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

    <!-- 7. FOOTER -->
    <footer class="mt-auto border-t border-slate-200 bg-white py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-xs text-slate-500">
            CareAtHome Health-Tech Platform &copy; 2026. Transparansi Rekening Penampung &amp; Jaminan Pengembalian Dana Terstandar Kemenkes RI.
        </div>
    </footer>

    <!-- Alpine App Logic for Cancellation -->
    <script>
        function patientCancellationApp() {
            return {
                cancelModalOpen: false,
                selectedOrder: null,
                cancelReason: 'Perubahan jadwal mendadak / ada keperluan lain',
                cancelNotes: '',
                processingCancel: false,
                cancelSuccessMsg: '',
                cancelErrorMsg: '',

                openCancelModal(order) {
                    this.selectedOrder = order;
                    this.cancelReason = 'Perubahan jadwal mendadak / ada keperluan lain';
                    this.cancelNotes = '';
                    this.cancelSuccessMsg = '';
                    this.cancelErrorMsg = '';
                    this.cancelModalOpen = true;
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
