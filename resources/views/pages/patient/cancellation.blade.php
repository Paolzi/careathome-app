<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pusat Pembatalan Pesanan &amp; Refund Escrow - CareAtHome</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-[#F9F4EA] text-[#46571A] font-sans antialiased min-h-screen flex flex-col"
    x-data="patientCancellationApp()">

    <!-- 1. TOP NAVBAR -->
    <x-careathome-navbar />

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
            <h3 class="font-bold text-[#46571A] flex items-center gap-2">
                <svg class="w-4 h-4 text-[#78956A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                </svg>
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
                <div class="p-8 rounded-3xl bg-[#FFFDF9] border border-[#78956A] text-center space-y-2">
                    <div class="w-12 h-12 rounded-2xl bg-[#F9F4EA] border border-[#78956A]/50 flex items-center justify-center mx-auto text-[#78956A]">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                        </svg>
                    </div>
                    <h4 class="text-sm font-bold text-[#46571A]">Tidak Ada Pesanan yang Memenuhi Syarat Pembatalan</h4>
                    <p class="text-xs text-[#78956A] max-w-md mx-auto">
                        Saat ini Anda tidak memiliki pesanan kunjungan aktif yang berstatus menunggu atau diterima.
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    @foreach ($cancellableOrders as $order)
                        <div class="bg-[#FFFDF9] rounded-3xl border border-[#78956A] p-6 space-y-4 shadow-sm flex flex-col justify-between">
                            <div class="space-y-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-[#D1EB8F] border border-[#78956A] text-[#46571A] flex items-center justify-center">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <span class="text-[10px] font-mono font-bold text-[#78956A]">{{ $order->order_number }}</span>
                                            <h4 class="text-sm font-bold text-[#46571A] leading-snug">{{ $order->serviceCatalog->name ?? 'Tindakan Keperawatan' }}</h4>
                                        </div>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border border-[#78956A]/50 {{ $order->payment_status === 'paid' ? 'bg-[#D1EB8F] text-[#46571A]' : 'bg-[#F9F4EA] text-[#78956A]' }}">
                                        {{ $order->payment_status === 'paid' ? 'Dana di Escrow' : 'Belum Bayar' }}
                                    </span>
                                </div>

                                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 text-xs space-y-1">
                                    <div class="flex justify-between text-slate-600">
                                        <span>Jadwal:</span>
                                        <span class="font-bold text-slate-900">{{ $order->scheduled_date->format('d M Y') }} ({{ str_contains($order->scheduled_time ?? '', 'WIB') ? $order->scheduled_time : ($order->scheduled_time . ' WIB') }})</span>
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
                                <button type="button" @click="openCancelModal(@js($order))"
                                    class="px-4 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold transition cursor-pointer">
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
                <div class="p-10 rounded-3xl bg-[#FFFDF9] border border-[#78956A] text-center space-y-2 max-w-lg mx-auto">
                    <div class="w-12 h-12 rounded-2xl bg-[#D1EB8F] border border-[#78956A] flex items-center justify-center mx-auto text-[#46571A]">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h4 class="text-sm font-bold text-[#46571A]">Belum Ada Riwayat Pembatalan</h4>
                    <p class="text-xs text-[#78956A]">
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
                                    {{ in_array($order->status, ['ditolak', 'declined']) ? 'Ditolak Perawat' : 'Dibatalkan' }}
                                </span>
                            </div>

                            <!-- Alasan Pembatalan & Status Refund Escrow -->
                            <div class="p-4 rounded-2xl bg-rose-50/60 border border-rose-200/70 text-xs space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-rose-900">Alasan Pembatalan:</span>
                                    <span class="text-[10px] text-slate-500">{{ $order->cancelled_at?->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</span>
                                </div>
                                <p class="text-rose-800 italic">{{ $order->cancellation_reason ?: 'Dibatalkan oleh pasien.' }}</p>

                                @if ($order->refund_status === 'refunded' || $order->payment_status === 'refunded')
                                    <div class="pt-2 border-t border-[#78956A]/30 flex items-center justify-between text-[#46571A] font-bold">
                                        <span class="flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-[#78956A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                            </svg>
                                            <span>Refund 100% Berhasil</span>
                                        </span>
                                        <span class="text-sm">Rp {{ number_format($order->refund_amount ?: $order->total_amount, 0, ',', '.') }}</span>
                                    </div>
                                    <span class="text-[10px] text-emerald-700 block mt-0.5">
                                        {{ $order->refund_notes ?: 'Pengembalian dana 100% telah selesai diproses.' }}
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
            class="bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-slate-200 overflow-hidden text-slate-800 max-h-[90vh] flex flex-col"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100">

            <!-- Modal Header -->
            <div class="px-6 py-4 bg-gradient-to-r from-rose-700 to-rose-900 text-white flex items-center justify-between shrink-0">
                <div>
                    <span class="text-[10px] uppercase font-bold tracking-wider text-rose-200">Konfirmasi Pembatalan &amp; Refund</span>
                    <h3 class="text-base font-bold mt-0.5" x-text="'Batalkan Pesanan #' + (selectedOrder?.order_number || '')"></h3>
                </div>
                <button type="button" @click="cancelModalOpen = false" :disabled="processingCancel" class="text-rose-200 hover:text-white p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Body (Scrollable) -->
            <div class="p-6 space-y-4 text-xs overflow-y-auto grow">

                <!-- Escrow Refund Assurance Notice -->
                <div class="p-4 rounded-2xl border text-xs leading-relaxed"
                    :class="selectedOrder?.payment_status === 'paid' ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-slate-50 border-slate-200 text-slate-700'">
                    <template x-if="selectedOrder?.payment_status === 'paid'">
                        <div class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-[#78956A] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                            </svg>
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
            <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-2.5 shrink-0">
                <button type="button" @click="cancelModalOpen = false" :disabled="processingCancel"
                    class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-xs font-bold text-slate-700 transition">
                    Kembali
                </button>
                <button type="button" @click="submitCancel()" :disabled="processingCancel"
                    class="px-5 py-2 rounded-xl bg-[#FF9ABC] hover:bg-[#D1EB8F] text-[#46571A] border border-[#78956A] text-xs font-bold shadow transition flex items-center gap-2 cursor-pointer">
                    <svg x-show="processingCancel" class="animate-spin w-4 h-4 text-[#46571A]" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
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
