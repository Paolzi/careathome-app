<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head> <meta charset="utf-8"> <meta name="viewport" content="width=device-width, initial-scale=1"> <title>Jadwal Kunjungan &amp; Pelacakan Pesanan - CareAtHome</title> <meta name="csrf-token" content="{{ csrf_token() }}"> <!-- Scripts & Styles --> @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script> <!-- Leaflet CSS & JS for Interactive Map Tracking --> <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" /> <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script> <!-- Direct Instant Payment Escrow --> <style> [x-cloak] { display: none !important; }
        .leaflet-container { font-family: inherit; }

        @keyframes dashMove {
            to {
                stroke-dashoffset: -32px;
            }
        }
        .animated-route-polyline {
            stroke-dasharray: 8, 8;
            animation: dashMove 1.2s linear infinite;
        }

        @keyframes pulseGlow {
            0% { transform: scale(0.95); opacity: 0.9; }
            50% { transform: scale(1.15); opacity: 0.4; }
            100% { transform: scale(0.95); opacity: 0.9; }
        }
        .ping-glow {
            animation: pulseGlow 1.8s ease-in-out infinite;
        }
    </style>
</head>
<body class="bg-[#F9F4EA] text-[#46571A] font-sans antialiased min-h-screen flex flex-col"
    x-data="patientOrdersApp()"> <!-- 1. TOP NAVBAR --> <x-careathome-navbar /> <!-- 2. MAIN CONTAINER --> <main class="grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6"> <!-- Page Header Banner --> <div class="bg-[#78956A] rounded-3xl p-6 sm:p-8 text-[#F9F4EA] shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-6 border border-[#78956A] relative overflow-hidden"> <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-[#FF9ABC] rounded-full blur-3xl pointer-events-none"></div> <div class="space-y-2 relative z-10"> <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#FF9ABC] border border-[#78956A] text-[#D1EB8F] text-xs font-semibold"> <span class="w-2 h-2 rounded-full bg-[#D1EB8F] animate-pulse"></span> Gateway Pembayaran Escrow &amp; Pelacakan Kunjungan Real-time
                </div> <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight"> Jadwal Kunjungan &amp; Pelacakan Pesanan
                </h1> <p class="text-[#F9F4EA]/90 text-xs sm:text-sm max-w-2xl leading-relaxed"> Pantau tahapan kedatangan nakes melalui <strong>Status Stepper</strong>, amankan transaksi via <strong>Rekening Penampung (Escrow)</strong>, serta kelola pembatalan dengan jaminan <strong>100% Refund Otomatis</strong>.
                </p> </div> <div class="shrink-0 flex sm:flex-col items-center sm:items-end justify-between gap-3 border-t sm:border-t-0 sm:border-l border-[#78956A] pt-4 sm:pt-0 sm:pl-6 relative z-10"> <div> <span class="text-[11px] text-[#78956A] uppercase tracking-wider font-semibold block">Total Pesanan Tercatat</span> <span class="text-2xl font-black text-[#D1EB8F]">{{ $totalOrders }} Pesanan</span> </div> <a href="{{ route('pasien.katalog') }}" class="px-4 py-2 rounded-xl bg-[#FF9ABC] hover:bg-[#D1EB8F] text-[#46571A] text-xs font-bold transition flex items-center gap-1.5 shadow-md"> <span>Cari Tindakan Baru</span> <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"> <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /> </svg> </a> </div> </div>

        <!-- 3. TABS SWITCHER --> <div class="flex items-center gap-2 border-b border-[#78956A]/50 pb-2"> <button type="button"
                @click="activeTab = 'active'"
                class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2"
                :class="activeTab === 'active' ? 'bg-[#FF9ABC] text-[#46571A] shadow-sm' : 'bg-white text-[#46571A]/85 hover:bg-[#F9F4EA] border border-[#78956A]/50'"> <span>Jadwal Kunjungan Aktif</span> <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold"
                    :class="activeTab === 'active' ? 'bg-[#FF9ABC] text-[#46571A]' : 'bg-[#F9F4EA] text-[#46571A]'"> {{ $activeOrders->count() }}
                </span> </button> <button type="button"
                @click="activeTab = 'past'"
                class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2"
                :class="activeTab === 'past' ? 'bg-[#FF9ABC] text-[#46571A] shadow-sm' : 'bg-white text-[#46571A]/85 hover:bg-[#F9F4EA] border border-[#78956A]/50'"> <span>Riwayat Selesai, Batal &amp; Ditolak</span> <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold"
                    :class="activeTab === 'past' ? 'bg-[#FF9ABC] text-[#46571A]' : 'bg-[#F9F4EA] text-[#46571A]'"> {{ $pastOrders->count() }}
                </span> </button> </div> <!-- 4. TAB CONTENT: ACTIVE ORDERS --> <div x-show="activeTab === 'active'" class="space-y-6"> @if ($activeOrders->isEmpty())
                <!-- EMPTY STATE --> <div class="bg-white rounded-3xl border border-[#78956A]/50 p-10 sm:p-14 text-center max-w-2xl mx-auto space-y-4 shadow-sm"> <div class="w-20 h-20 rounded-3xl bg-[#D1EB8F]/45 text-[#78956A] mx-auto flex items-center justify-center text-4xl shadow-inner"> </div> <div class="space-y-1.5"> <h3 class="text-xl font-bold text-[#46571A]">Belum Ada Jadwal Kunjungan Aktif</h3> <p class="text-xs sm:text-sm text-[#78956A] leading-relaxed max-w-md mx-auto"> Saat ini belum ada pesanan kunjungan nakes yang sedang berlangsung. Silakan telusuri katalog layanan medis terstandar untuk memesan tindakan perawat ke rumah Anda.
                        </p> </div> <div class="pt-3"> <a href="{{ route('pasien.katalog') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-[#FF9ABC] hover:bg-[#FF9ABC] text-[#46571A] text-xs font-bold shadow-md shadow-[#78956A]/25 transition"> <span>Jelajahi Katalog Layanan &amp; Buat Jadwal</span> <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"> <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /> </svg> </a> </div> </div> @else
                <!-- DAFTAR KARTU PESANAN AKTIF DENGAN STEPPER --> <div class="grid grid-cols-1 lg:grid-cols-2 gap-6"> @foreach ($activeOrders as $order)
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
                        <div class="bg-white rounded-3xl border border-[#78956A]/50 shadow-sm hover:shadow-md hover:border-[#78956A] transition-all flex flex-col justify-between overflow-hidden"> <!-- Card Header --> <div class="p-5 border-b border-[#78956A]/50 flex items-center justify-between bg-[#F9F4EA]"> <div class="flex items-center gap-3"> <div class="w-11 h-11 rounded-2xl bg-white border border-[#78956A]/50 flex items-center justify-center text-2xl shadow-xs"> <svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg> </div> <div> <div class="flex items-center gap-2"> <span class="text-[10px] font-mono font-bold text-[#78956A] uppercase tracking-wider block"> {{ $order->order_number }}
                                            </span> <span class="text-[9px] font-bold px-2 py-0.5 rounded-full {{ $order->payment_status === 'paid' ? 'bg-[#D1EB8F] text-[#46571A]' : 'bg-[#D1EB8F]/70 text-[#46571A]' }}"> {{ $order->payment_status === 'paid' ? ' Escrow Paid' : 'Menunggu Bayar' }}
                                            </span> </div> <h3 class="text-sm font-bold text-[#46571A] leading-snug"> {{ $order->serviceCatalog->name ?? 'Tindakan Keperawatan' }}
                                        </h3> </div> </div> <!-- Status Badge --> @php
                                    $badgeClasses = match($order->status) {
                                        'menunggu_konfirmasi' => 'bg-[#F9F4EA] text-[#46571A] border-[#78956A]/50',
                                        'diterima' => 'bg-[#D1EB8F]/45 text-[#46571A] border-[#78956A]/50',
                                        'dalam_perjalanan' => 'bg-[#D1EB8F]/45 text-[#46571A] border-[#78956A]/50',
                                        'tiba' => 'bg-[#D1EB8F]/45 text-[#46571A] border-[#78956A]/50',
                                        'tindakan_berlangsung' => 'bg-[#D1EB8F]/45 text-[#46571A] border-[#78956A]/50',
                                        default => 'bg-[#F9F4EA] text-[#46571A] border-[#78956A]/50',
                                    };
                                @endphp
                                <span class="text-[11px] font-bold px-3 py-1 rounded-full border {{ $badgeClasses }}"> {{ $order->status_label }}
                                </span> </div> <!-- VISUAL STATUS STEPPER ORDER --> <div class="p-5 border-b border-[#78956A]/50 bg-white space-y-2"> <div class="flex items-center justify-between text-[11px] font-bold text-[#78956A] mb-1"> <span class="text-[#78956A] uppercase tracking-wider text-[10px]">Alur Tracking Pesanan</span> <span class="text-[#46571A]">Tahap {{ $stepIndex }} dari 5</span> </div> <!-- Stepper Bar --> <div class="grid grid-cols-5 gap-1.5 relative pt-1"> <!-- Step 1: Pesanan Dibuat --> <div class="text-center space-y-1"> <div class="w-full h-1.5 rounded-full transition-colors {{ $stepIndex >= 1 ? 'bg-[#FF9ABC]' : 'bg-[#D1EB8F]/50' }}"></div> <span class="text-[10px] block font-semibold truncate {{ $stepIndex >= 1 ? 'text-[#46571A] font-bold' : 'text-[#78956A]' }}"> Dibuat
                                        </span> </div> <!-- Step 2: Diterima Nakes --> <div class="text-center space-y-1"> <div class="w-full h-1.5 rounded-full transition-colors {{ $stepIndex >= 2 ? 'bg-[#FF9ABC]' : 'bg-[#D1EB8F]/50' }}"></div> <span class="text-[10px] block font-semibold truncate {{ $stepIndex >= 2 ? 'text-[#46571A] font-bold' : 'text-[#78956A]' }}"> Diterima
                                        </span> </div> <!-- Step 3: Bayar Escrow --> <div class="text-center space-y-1"> <div class="w-full h-1.5 rounded-full transition-colors {{ $stepIndex >= 3 ? 'bg-[#FF9ABC]' : 'bg-[#D1EB8F]/50' }}"></div> <span class="text-[10px] block font-semibold truncate {{ $stepIndex >= 3 ? 'text-[#46571A] font-bold' : 'text-[#78956A]' }}"> Escrow
                                        </span> </div> <!-- Step 4: Menuju Lokasi --> <div class="text-center space-y-1"> <div class="w-full h-1.5 rounded-full transition-colors {{ $stepIndex >= 4 ? 'bg-[#FF9ABC]' : 'bg-[#D1EB8F]/50' }}"></div> <span class="text-[10px] block font-semibold truncate {{ $stepIndex >= 4 ? 'text-[#46571A] font-bold' : 'text-[#78956A]' }}"> Perjalanan
                                        </span> </div> <!-- Step 5: Tindakan Medis --> <div class="text-center space-y-1"> <div class="w-full h-1.5 rounded-full transition-colors {{ $stepIndex >= 5 ? 'bg-[#FF9ABC]' : 'bg-[#D1EB8F]/50' }}"></div> <span class="text-[10px] block font-semibold truncate {{ $stepIndex >= 5 ? 'text-[#46571A] font-bold' : 'text-[#78956A]' }}"> Tindakan
                                        </span> </div> </div> </div> <!-- Card Body: Schedule, Location & Assigned Nurse --> <div class="p-5 space-y-3.5 text-xs"> <div class="grid grid-cols-2 gap-2 p-3 rounded-2xl bg-[#F9F4EA] border border-[#78956A]/50"> <div> <span class="text-[10px] uppercase font-bold text-[#78956A] block">Jadwal Kunjungan (WIB)</span> <span class="font-bold text-[#46571A]">{{ $order->scheduled_date->format('d M Y') }}</span> <span class="text-[11px] text-[#46571A] font-semibold block">{{ str_contains(strtoupper($order->scheduled_time), 'WIB') ? $order->scheduled_time : $order->scheduled_time . ' WIB' }}</span> </div> <div> <span class="text-[10px] uppercase font-bold text-[#78956A] block">Penerima Layanan</span> <span class="font-semibold text-[#46571A] block truncate">{{ $order->patient_name }}</span> <span class="text-[11px] text-[#78956A] block">{{ $order->patient_phone }}</span> </div> </div> <!-- Perawat Penanggung Jawab --> <div class="p-3 rounded-2xl border border-[#78956A]/50 flex items-center justify-between bg-[#D1EB8F]/45"> <div class="flex items-center gap-2.5"> <div class="w-8 h-8 rounded-full bg-[#FF9ABC] text-[#46571A] font-bold text-xs flex items-center justify-center"> {{ substr($order->nurse->name ?? 'N', 0, 1) }}
                                        </div> <div> <span class="text-[10px] uppercase font-bold text-[#78956A] block">Mitra Nakes Bertugas</span> <span class="font-bold text-[#46571A] block">{{ $order->nurse->name ?? 'Mencari Perawat Terdekat...' }}</span> </div> </div> <span class="text-[10px] font-bold text-[#46571A] bg-[#D1EB8F] px-2 py-0.5 rounded-full border border-[#78956A]/50"> STR &amp; SIP Kemenkes
                                    </span> </div> <div> <span class="text-[10px] uppercase font-bold text-[#78956A] block">Alamat Kunjungan</span> <p class="text-[#46571A] line-clamp-2 mt-0.5 leading-relaxed">{{ $order->address }}</p> <span class="text-[11px] text-[#78956A] font-medium mt-1 block"> Jarak rute jalan raya: <strong class="text-[#46571A]">{{ $order->distance_km }} km</strong> (OSRM Live Routing)
                                    </span> </div> </div> <!-- Card Footer: Total Amount & Action Buttons --> <div class="p-5 bg-[#F9F4EA] border-t border-[#78956A]/50 flex flex-wrap items-center justify-between gap-3"> <div> <span class="text-[10px] uppercase font-bold text-[#78956A] block">Total Tagihan (Escrow)</span> <span class="text-base font-extrabold text-[#46571A]"> Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                    </span> </div> <div class="flex items-center gap-2"> <!-- Detail & Tracking Button --> <button type="button"
                                        @click="openTrackingModal(@js($order))"
                                        class="px-3.5 py-2 rounded-xl bg-white hover:bg-[#F9F4EA] text-[#46571A] border border-[#78956A]/50 text-xs font-bold transition flex items-center gap-1.5 shadow-2xs cursor-pointer"> <span> Detail &amp; Tracking</span> </button> @if ($order->payment_status !== 'paid')
                                        <button type="button"
                                            @click="directPayOrder(@js($order))"
                                            class="px-4 py-2 rounded-xl bg-[#FF9ABC] hover:bg-[#FF9ABC] text-[#46571A] text-xs font-bold shadow-sm shadow-[#78956A]/25 transition flex items-center gap-1.5 cursor-pointer"> <span> Bayar Sekarang</span> </button> @endif

                                    <!-- Pembatalan Button --> @if ($order->isCancellable())
                                        <button type="button"
                                            @click="openCancelModal(@js($order))"
                                            class="px-3 py-2 rounded-xl bg-[#FF9ABC]/25 hover:bg-[#FF9ABC]/40 text-[#46571A] border border-[#FF9ABC] text-xs font-bold transition flex items-center gap-1 cursor-pointer"> <span>Batalkan</span> </button> @endif
                                </div> </div> </div> @endforeach
                </div> @endif
        </div> <!-- 5. TAB CONTENT: PAST ORDERS (RIWAYAT SELESAI & BATAL) --> <div x-show="activeTab === 'past'" x-cloak class="space-y-4"> @if ($pastOrders->isEmpty())
                <div class="bg-white rounded-3xl border border-[#78956A]/50 p-10 text-center max-w-lg mx-auto space-y-3"> <div class="text-4xl text-[#78956A]"></div> <h3 class="text-base font-bold text-[#46571A]">Belum Ada Riwayat Kunjungan</h3> <p class="text-xs text-[#78956A]"> Pesanan yang telah selesai dikerjakan nakes atau pesanan yang dibatalkan dengan pengembalian dana escrow akan tersimpan rapi di sini.
                    </p> </div> @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5"> @foreach ($pastOrders as $order)
                        <div class="bg-white rounded-3xl border border-[#78956A]/50 p-6 space-y-4 shadow-sm"> <div class="flex justify-between items-start gap-3"> <div> <span class="text-[10px] font-mono font-bold text-[#78956A]">{{ $order->order_number }}</span> <h4 class="text-base font-bold text-[#46571A] mt-0.5">{{ $order->serviceCatalog->name ?? 'Tindakan Keperawatan' }}</h4> <p class="text-xs text-[#78956A] mt-1">Jadwal: {{ $order->scheduled_date->format('d M Y') }} • {{ $order->scheduled_time }}</p> </div> <div class="text-right space-y-1"> <span class="text-[11px] font-bold px-3 py-1 rounded-full inline-block {{ in_array($order->status, ['selesai', 'completed']) ? 'bg-[#D1EB8F]/45 text-[#46571A] border border-[#78956A]/50' : (in_array($order->status, ['ditolak', 'declined']) ? 'bg-[#F9F4EA] text-[#46571A] border border-[#78956A]/50' : 'bg-[#FF9ABC]/25 text-[#46571A] border border-[#FF9ABC]') }}"> {{ $order->status_label }}
                                    </span> </div> </div> <!-- Detail Penolakan Mitra Perawat --> @if (in_array($order->status, ['ditolak', 'declined']))
                                <div class="p-4 rounded-2xl bg-[#F9F4EA] border border-[#78956A]/50 text-xs space-y-2"> <div class="flex items-center justify-between"> <span class="font-bold text-[#46571A]">Alasan Penolakan Perawat:</span> <span class="text-[10px] text-[#46571A] font-semibold">{{ $order->nurse?->name ?? 'Mitra Nakes' }}</span> </div> <p class="text-[#46571A] italic">{{ $order->cancellation_reason ?: 'Jadwal kunjungan berbenturan atau kapasitas tindakan perawat penuh. Anda dapat memesan perawat terdekat lainnya di katalog.' }}</p> </div> @endif

                            <!-- Detail Pembatalan & Refund Escrow --> @if ($order->status === 'dibatalkan')
                                <div class="p-4 rounded-2xl bg-[#FF9ABC]/25 border border-[#FF9ABC] text-xs space-y-2"> <div class="flex items-center justify-between"> <span class="font-bold text-[#46571A]">Alasan Pembatalan:</span> <span class="text-[10px] text-[#78956A]">{{ $order->cancelled_at?->format('d M Y, H:i') }} WIB</span> </div> <p class="text-[#46571A] italic">{{ $order->cancellation_reason ?: 'Dibatalkan oleh pasien atas permintaan sendiri.' }}</p> @if ($order->refund_status === 'refunded')
                                        <div class="pt-2 border-t border-[#FF9ABC] flex items-center justify-between text-[#46571A] font-bold"> <span class="flex items-center gap-1"> <span><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg></span> <span>Refund Escrow 100% Berhasil</span> </span> <span class="text-sm">Rp {{ number_format($order->refund_amount, 0, ',', '.') }}</span> </div> @endif
                                </div> @endif

                            <div class="text-xs font-bold text-[#46571A] pt-3 border-t border-[#78956A]/50 flex justify-between items-center"> <span class="text-[#78956A]">Total Transaksi:</span> <span class="text-base font-extrabold text-[#46571A]">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span> </div> <!-- Bagian E-Report Klinis & Ulasan Pasien (KF-11 & KF-12) --> @if (in_array($order->status, ['selesai', 'completed']))
                                <div class="pt-3 border-t border-[#78956A]/50 space-y-2.5"> <div class="flex flex-wrap items-center gap-2"> <!-- Tombol Buka E-Report --> @if ($order->eReport)
                                            <button type="button" @click="fetchAndOpenEReport({{ $order->id }})"
                                                class="px-3.5 py-1.5 rounded-xl bg-[#D1EB8F]/45 hover:bg-[#D1EB8F] text-[#46571A] border border-[#78956A]/50 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer"> <span><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg></span> <span>Lihat Lembar E-Report Medis</span> </button> @else
                                            <span class="text-[11px] text-[#78956A] italic flex items-center gap-1"> <span><svg class="w-4 h-4 inline-block shrink-0 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></span> <span>E-Report sedang diproses nakes</span> </span> @endif

                                        <!-- Tombol / Tampilan Rating & Ulasan --> @if (!$order->review)
                                            <button type="button" @click="openReviewModal(@js($order))"
                                                class="px-3.5 py-1.5 rounded-xl bg-[#F9F4EA] hover:bg-[#D1EB8F]/70 text-[#46571A] border border-[#78956A]/50 text-xs font-bold transition flex items-center gap-1.5 ml-auto cursor-pointer"> <span>⭐</span> <span>Beri Rating &amp; Ulasan</span> </button> @endif
                                    </div> <!-- Tampilan Ulasan yang Sudah Diberikan --> @if ($order->review)
                                        <div class="p-3 rounded-2xl bg-[#F9F4EA] border border-[#78956A]/50 text-xs space-y-1"> <div class="flex items-center justify-between text-[#78956A] font-extrabold text-xs"> <div class="flex items-center gap-1"> @for ($i = 1; $i <= 5; $i++)
                                                        <span>{{ $i <= $order->review->rating ? '' : '' }}</span> @endfor
                                                    <span class="text-[#46571A] ml-1">({{ $order->review->rating }}/5)</span> </div> <span class="text-[10px] text-[#78956A] font-normal">{{ $order->review->created_at->format('d M Y') }}</span> </div> <p class="text-[11px] text-[#46571A] italic">"{{ $order->review->comment ?: 'Pelayanan sangat memuaskan!' }}"</p> </div> @endif
                                </div> @endif
                        </div> @endforeach
                </div> @endif
        </div> </main> <!-- 6. MODAL: DETAIL BOOKING & LIVE TRACKING GPS --> <div x-show="trackingModalOpen" x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-[#46571A]/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-6"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"> <div @click.away="closeTrackingModal()"
            class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl border border-[#78956A]/50 overflow-hidden text-[#46571A] max-h-[90vh] flex flex-col"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"> <!-- Modal Header --> <div class="px-6 py-5 bg-[#78956A] text-[#F9F4EA] flex items-center justify-between shrink-0"> <div> <span class="text-[10px] uppercase font-bold tracking-wider text-[#D1EB8F]">Detail Booking &amp; Pelacakan Real-time</span> <h3 class="text-lg font-bold mt-0.5" x-text="selectedOrder?.service_catalog?.name || 'Detail Pesanan Kunjungan'"></h3> <p class="text-xs text-[#F9F4EA]/90 font-mono" x-text="'No. Pesanan: ' + (selectedOrder?.order_number || '-')"></p> </div> <button type="button" @click="closeTrackingModal()" class="text-[#F9F4EA]/90 hover:text-white p-1"> <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"> <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /> </svg> </button> </div> <!-- Modal Body (Scrollable) --> <div class="p-6 overflow-y-auto space-y-5 text-xs"> <!-- 1. Interactive Peta Geolokasi (Leaflet) --> <div> <div class="flex flex-wrap items-center justify-between gap-2 mb-2"> <div class="flex items-center gap-2"> <span class="font-bold text-[#46571A] flex items-center gap-1.5"> <span><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg></span> <span>Peta Pelacakan Rute Nakes &amp; Titik Pasien</span> </span> <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-[#D1EB8F]/45 border border-[#78956A]/50 text-[9px] font-extrabold text-[#78956A]"> <span class="w-1.5 h-1.5 rounded-full bg-[#FF9ABC] animate-ping"></span> <span x-text="lastLiveSyncWib ? ('LIVE GPS • ' + lastLiveSyncWib) : 'LIVE GPS AKTIF'"></span> </span> </div> <div class="flex items-center gap-2"> <span class="text-[10px] text-[#46571A] bg-[#D1EB8F]/45 px-2.5 py-0.5 rounded-full border border-[#78956A]/50 font-bold"
                                x-text="'Estimasi Tiba: ~' + (liveEtaMin !== null ? liveEtaMin : Math.max(8, Math.round((selectedOrder?.distance_km || 3) * 3))) + ' Menit'"></span> <button type="button" @click="startNurseMovementSimulation()"
                                class="px-2.5 py-1 rounded-lg bg-[#FF9ABC] hover:bg-[#FF9ABC] text-[#46571A] font-bold text-[10px] shadow-xs transition flex items-center gap-1 cursor-pointer"
                                :disabled="isTrackingAnimating"> <span x-show="!isTrackingAnimating">▶ Simulasikan Perjalanan</span> <span x-show="isTrackingAnimating" class="animate-pulse"> Nakes Melaju...</span> </button> </div> </div> <!-- Map Container --> <div id="tracking-map" class="h-64 sm:h-72 w-full rounded-2xl border border-[#78956A]/50 shadow-inner z-0"></div> <!-- Live Metrics Strip --> <div class="grid grid-cols-3 gap-2 mt-2 p-2.5 rounded-xl bg-[#F9F4EA] border border-[#78956A]/50 text-center"> <div> <span class="text-[9px] text-[#78956A] block font-semibold">JARAK KE LOKASI</span> <span class="text-xs font-bold text-[#46571A]" x-text="(liveDistanceKm !== null ? liveDistanceKm : (selectedOrder?.distance_km || '2.8')) + ' km'"></span> </div> <div> <span class="text-[9px] text-[#78956A] block font-semibold">STATUS PERJALANAN</span> <span class="text-xs font-bold text-[#46571A]" x-text="liveStatusText || (selectedOrder?.status_label || 'Siap Berangkat')"></span> </div> <div> <span class="text-[9px] text-[#78956A] block font-semibold">MITRA PENANGGUNG JAWAB</span> <span class="text-xs font-bold text-[#46571A]" x-text="selectedOrder?.nurse?.name || 'Ns. Dinda'"></span> </div> </div> </div> <!-- 2. Stepper Status Timeline --> <div class="p-4 rounded-2xl bg-[#F9F4EA] border border-[#78956A]/50 space-y-3"> <span class="font-bold text-[#46571A] block text-xs">Timeline Status Pemesanan</span> <div class="space-y-3 relative pl-4 border-l-2 border-[#78956A]"> <div class="relative"> <span class="absolute -left-[21px] top-0.5 w-3 h-3 rounded-full bg-[#FF9ABC] border-2 border-white"></span> <span class="font-bold text-[#46571A] block">1. Pesanan Dikonfirmasi Pasien</span> <span class="text-[#78956A] text-[11px] block">Jadwal: <strong x-text="selectedOrder?.scheduled_date + ' (' + selectedOrder?.scheduled_time + ')'"></strong></span> </div> <div class="relative"> <span class="absolute -left-[21px] top-0.5 w-3 h-3 rounded-full"
                                :class="selectedOrder?.payment_status === 'paid' ? 'bg-[#FF9ABC] border-2 border-white' : 'bg-[#78956A]/30'"></span> <span class="font-bold text-[#46571A] block">2. Pembayaran Rekening Penampung (Escrow)</span> <span class="text-[#78956A] text-[11px] block" x-text="selectedOrder?.payment_status === 'paid' ? 'Dana lunas & terkunci aman di rekening escrow' : 'Menunggu pembayaran non-tunai pasien'"></span> </div> <div class="relative"> <span class="absolute -left-[21px] top-0.5 w-3 h-3 rounded-full"
                                :class="['dalam_perjalanan', 'tiba', 'tindakan_berlangsung', 'selesai'].includes(selectedOrder?.status) ? 'bg-[#FF9ABC] border-2 border-white animate-ping' : 'bg-[#78956A]/30'"></span> <span class="font-bold text-[#46571A] block">3. Perawat Menuju Lokasi &amp; Tindakan Medis</span> <span class="text-[#78956A] text-[11px] block" x-text="'Status saat ini: ' + (selectedOrder?.status_label || '-')"></span> </div> </div> </div> <!-- 3. Profil Mitra Nakes & Info Pasien --> <div class="grid grid-cols-1 sm:grid-cols-2 gap-3"> <div class="p-4 rounded-2xl bg-[#D1EB8F]/45 border border-[#78956A]/50 space-y-2"> <span class="text-[10px] font-bold uppercase text-[#46571A] block">Mitra Nakes Penanggung Jawab</span> <div class="flex items-center gap-3"> <div class="w-10 h-10 rounded-xl bg-[#FF9ABC] text-[#46571A] font-extrabold flex items-center justify-center text-sm shadow-sm"> <span x-text="(selectedOrder?.nurse?.name || 'N').substring(0, 1)"></span> </div> <div> <h4 class="font-bold text-[#46571A]" x-text="selectedOrder?.nurse?.name || 'Ns. Siti Nurhaliza, S.Kep'"></h4> <span class="text-[10px] text-[#46571A] font-semibold block">STR &amp; SIP Aktif Kemenkes RI</span> </div> </div> <div class="flex items-center justify-between pt-2 border-t border-[#78956A]/50 text-[11px]"> <span>⭐ Rating: 4.9 (120+ Kunjungan)</span> <a :href="'https://wa.me/6281234567890?text=Halo%20Nakes%20CareAtHome%2C%20terkait%20pesanan%20' + (selectedOrder?.order_number || '')"
                                target="_blank"
                                class="text-[#46571A] font-bold hover:underline"> Chat Nakes
                            </a> </div> </div> <div class="p-4 rounded-2xl bg-[#F9F4EA] border border-[#78956A]/50 space-y-1.5"> <span class="text-[10px] font-bold uppercase text-[#78956A] block">Penerima &amp; Lokasi Tindakan</span> <p class="font-bold text-[#46571A]" x-text="selectedOrder?.patient_name"></p> <p class="text-[#46571A]/85" x-text="selectedOrder?.patient_phone"></p> <p class="text-[#78956A] text-[11px] line-clamp-2" x-text="selectedOrder?.address"></p> </div> </div> <!-- 4. Rincian Biaya --> <div class="p-4 rounded-2xl bg-[#F9F4EA] border border-[#78956A]/50 space-y-2"> <span class="font-bold text-[#46571A] block text-xs">Rincian Plafon Tarif &amp; Escrow</span> <div class="space-y-1 text-[#46571A]/85"> <div class="flex justify-between"> <span>Tarif Layanan Medis Terstandar:</span> <span class="font-semibold text-[#46571A]" x-text="'Rp ' + Number(selectedOrder?.base_price || 0).toLocaleString('id-ID')"></span> </div> <div class="flex justify-between"> <span>Biaya Transportasi GPS (<span x-text="(selectedOrder?.distance_km || 0) + ' km'"></span>):</span> <span class="font-semibold text-[#46571A]" x-text="'Rp ' + Number(selectedOrder?.transport_fee || 0).toLocaleString('id-ID')"></span> </div> <div class="flex justify-between"> <span>Biaya Layanan Aplikasi (Flat):</span> <span class="font-semibold text-[#46571A]">Rp 5.000</span> </div> <div class="flex justify-between pt-2 border-t border-[#78956A]/50 text-sm font-black text-[#46571A]"> <span>Total Pembayaran Escrow:</span> <span x-text="'Rp ' + Number(selectedOrder?.total_amount || 0).toLocaleString('id-ID')"></span> </div> </div> </div> </div> <!-- Modal Footer --> <div class="p-4 bg-[#F9F4EA] border-t border-[#78956A]/50 flex items-center justify-between gap-3 shrink-0"> <template x-if="['menunggu_konfirmasi', 'diterima'].includes(selectedOrder?.status)"> <button type="button" @click="trackingModalOpen = false; openCancelModal(selectedOrder)"
                        class="px-4 py-2 rounded-xl bg-[#FF9ABC]/25 hover:bg-[#FF9ABC]/40 text-[#46571A] text-xs font-bold border border-[#FF9ABC] transition"> Batalkan Pesanan
                    </button> </template> <div class="flex items-center gap-2 ml-auto"> <button type="button" @click="trackingModalOpen = false"
                        class="px-4 py-2 rounded-xl border border-[#78956A]/50 bg-white hover:bg-[#F9F4EA] text-xs font-bold text-[#46571A] transition"> Tutup
                    </button> <template x-if="selectedOrder?.payment_status !== 'paid'"> <button type="button" @click="directPayOrder(selectedOrder)"
                            class="px-5 py-2 rounded-xl bg-[#FF9ABC] hover:bg-[#FF9ABC] text-[#46571A] text-xs font-bold shadow transition cursor-pointer"> Bayar Sekarang (Langsung Lunas)
                        </button> </template> </div> </div> </div> </div> <!-- 7. MODAL: PEMBATALAN PESANAN & REFUND ESCROW --> <div x-show="cancelModalOpen" x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-[#46571A]/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-6"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"> <div @click.away="if (!processingCancel) cancelModalOpen = false"
            class="bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-[#78956A]/50 overflow-hidden text-[#46571A] max-h-[90vh] flex flex-col"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"> <!-- Modal Header --> <div class="px-6 py-4 bg-[#78956A] text-[#F9F4EA] flex items-center justify-between shrink-0"> <div> <span class="text-[10px] uppercase font-bold tracking-wider text-[#F9F4EA]">Pembatalan &amp; Pengembalian Dana</span> <h3 class="text-base font-bold mt-0.5">Konfirmasi Pembatalan Pesanan</h3> </div> <button type="button" @click="cancelModalOpen = false" :disabled="processingCancel" class="text-[#F9F4EA] hover:text-white p-1"> <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"> <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /> </svg> </button> </div> <!-- Modal Body (Scrollable) --> <div class="p-6 space-y-4 text-xs overflow-y-auto grow"> <!-- Escrow Refund Assurance Notice --> <div class="p-4 rounded-2xl border text-xs leading-relaxed"
                    :class="selectedOrder?.payment_status === 'paid' ? 'bg-[#D1EB8F]/45 border-[#78956A]/50 text-[#46571A]' : 'bg-[#F9F4EA] border-[#78956A]/50 text-[#46571A]'"> <template x-if="selectedOrder?.payment_status === 'paid'"> <div class="flex items-start gap-3"> <span class="text-2xl"><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg></span> <div class="space-y-1"> <span class="font-bold block text-[#46571A]">Jaminan 100% Pengembalian Dana Escrow</span> <p class="text-[#46571A]"> Pesanan ini telah lunas dan dana tersimpan di rekening penampung. Karena dibatalkan sebelum nakes berangkat, dana senilai <strong x-text="'Rp ' + Number(selectedOrder?.total_amount || 0).toLocaleString('id-ID')"></strong> akan otomatis dikembalikan penuh (100% Refund tanpa potongan).
                                </p> </div> </div> </template> <template x-if="selectedOrder?.payment_status !== 'paid'"> <div class="flex items-start gap-3"> <span class="text-2xl">ℹ</span> <div class="space-y-1"> <span class="font-bold block text-[#46571A]">Pembatalan Pesanan Belum Dibayar</span> <p class="text-[#46571A]/85"> Pesanan ini belum melalui proses pembayaran escrow, sehingga pembatalan dapat dilakukan langsung tanpa ada proses pengembalian dana.
                                </p> </div> </div> </template> </div> <!-- Form Pilihan Alasan Pembatalan --> <div class="space-y-2"> <label class="block font-bold text-[#46571A]">Pilih Alasan Pembatalan Pesanan:</label> <div class="space-y-1.5"> <label class="p-2.5 rounded-xl border cursor-pointer transition flex items-center gap-2"
                            :class="cancelReason === 'Perubahan jadwal mendadak / ada keperluan lain' ? 'border-[#FF9ABC] bg-[#FF9ABC]/25 text-[#46571A] font-bold' : 'border-[#78956A]/50 hover:bg-[#F9F4EA]'"> <input type="radio" value="Perubahan jadwal mendadak / ada keperluan lain" x-model="cancelReason" class="text-[#46571A]" /> <span>Perubahan jadwal mendadak / ada keperluan lain</span> </label> <label class="p-2.5 rounded-xl border cursor-pointer transition flex items-center gap-2"
                            :class="cancelReason === 'Perawat belum kunjung merespons konfirmasi' ? 'border-[#FF9ABC] bg-[#FF9ABC]/25 text-[#46571A] font-bold' : 'border-[#78956A]/50 hover:bg-[#F9F4EA]'"> <input type="radio" value="Perawat belum kunjung merespons konfirmasi" x-model="cancelReason" class="text-[#46571A]" /> <span>Perawat belum kunjung merespons konfirmasi</span> </label> <label class="p-2.5 rounded-xl border cursor-pointer transition flex items-center gap-2"
                            :class="cancelReason === 'Kondisi darurat / pasien dialihkan ke IGD Rumah Sakit' ? 'border-[#FF9ABC] bg-[#FF9ABC]/25 text-[#46571A] font-bold' : 'border-[#78956A]/50 hover:bg-[#F9F4EA]'"> <input type="radio" value="Kondisi darurat / pasien dialihkan ke IGD Rumah Sakit" x-model="cancelReason" class="text-[#46571A]" /> <span>Kondisi darurat / pasien dialihkan ke IGD Rumah Sakit</span> </label> <label class="p-2.5 rounded-xl border cursor-pointer transition flex items-center gap-2"
                            :class="cancelReason === 'Salah memilih tindakan keperawatan di katalog' ? 'border-[#FF9ABC] bg-[#FF9ABC]/25 text-[#46571A] font-bold' : 'border-[#78956A]/50 hover:bg-[#F9F4EA]'"> <input type="radio" value="Salah memilih tindakan keperawatan di katalog" x-model="cancelReason" class="text-[#46571A]" /> <span>Salah memilih tindakan keperawatan di katalog</span> </label> <label class="p-2.5 rounded-xl border cursor-pointer transition flex items-center gap-2"
                            :class="cancelReason === 'Lainnya' ? 'border-[#FF9ABC] bg-[#FF9ABC]/25 text-[#46571A] font-bold' : 'border-[#78956A]/50 hover:bg-[#F9F4EA]'"> <input type="radio" value="Lainnya" x-model="cancelReason" class="text-[#46571A]" /> <span>Alasan lainnya</span> </label> </div> </div> <!-- Catatan Tambahan (Opsional) --> <div class="space-y-1"> <label class="block font-bold text-[#46571A]">Catatan Tambahan (Opsional):</label> <textarea x-model="cancelNotes" rows="2" placeholder="Tuliskan keterangan tambahan bila ada..."
                        class="w-full rounded-xl border border-[#78956A]/50 text-xs p-2.5 focus:border-[#FF9ABC] focus:ring-[#78956A]"></textarea> </div> <!-- Alert Messages --> <template x-if="cancelSuccessMsg"> <div class="p-3.5 rounded-xl bg-[#D1EB8F] text-[#46571A] text-xs font-semibold leading-relaxed" x-text="cancelSuccessMsg"></div> </template> <template x-if="cancelErrorMsg"> <div class="p-3.5 rounded-xl bg-[#FF9ABC]/40 text-[#46571A] text-xs font-semibold leading-relaxed" x-text="cancelErrorMsg"></div> </template> </div> <!-- Modal Footer --> <div class="p-4 bg-[#F9F4EA] border-t border-[#78956A]/50 flex items-center justify-end gap-2.5"> <button type="button" @click="cancelModalOpen = false" :disabled="processingCancel"
                    class="px-4 py-2 rounded-xl border border-[#78956A]/50 bg-white hover:bg-[#F9F4EA] text-xs font-bold text-[#46571A] transition"> Kembali
                </button> <button type="button" @click="submitCancel()" :disabled="processingCancel"
                    class="px-5 py-2 rounded-xl bg-[#FF9ABC] hover:bg-[#FF9ABC] text-[#46571A] text-xs font-bold shadow transition flex items-center gap-2"> <span x-show="processingCancel" class="animate-spin text-sm"><svg class="w-4 h-4 inline-block shrink-0 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></span> <span x-text="processingCancel ? 'Memproses Pembatalan & Refund...' : (selectedOrder?.payment_status === 'paid' ? 'Batalkan & Refund Escrow' : 'Konfirmasi Batalkan')"></span> </button> </div> </div> </div> <!-- 8. MODAL GATEWAY PEMBAYARAN ESCROW & KONFIRMASI --> <div x-show="paymentModalOpen" x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-[#46571A]/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-6"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"> <div @click.away="if (!processingPayment) paymentModalOpen = false"
            class="bg-white rounded-3xl max-w-xl w-full shadow-2xl border border-[#78956A]/50 overflow-hidden text-[#46571A]"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"> <!-- Modal Header --> <div class="px-6 py-5 bg-[#78956A] text-[#F9F4EA] flex items-center justify-between"> <div> <div class="flex items-center gap-2"> <span class="text-[10px] uppercase font-bold tracking-wider text-[#F9F4EA] bg-[#FF9ABC] px-2 py-0.5 rounded-full border border-[#78956A]"> Rekening Penampung (Escrow)
                        </span> <span class="text-[10px] text-[#F9F4EA]">Midtrans Gateway</span> </div> <h3 class="text-lg font-bold mt-1">Konfirmasi Pembayaran Aman</h3> </div> <button type="button" @click="paymentModalOpen = false" class="text-[#F9F4EA] hover:text-white p-1"> <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"> <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /> </svg> </button> </div> <!-- Modal Body --> <div class="p-6 space-y-4 text-xs"> <!-- Ringkasan Order & Price Breakdown --> <div class="p-4 rounded-2xl bg-[#F9F4EA] border border-[#78956A]/50 space-y-2.5"> <div class="flex justify-between items-center"> <span class="text-[#78956A]">Nomor Pesanan:</span> <span class="font-mono font-bold text-[#46571A] bg-white px-2 py-0.5 rounded border border-[#78956A]/50" x-text="selectedOrder?.order_number"></span> </div> <div class="flex justify-between items-center"> <span class="text-[#78956A]">Tindakan Medis:</span> <span class="font-semibold text-[#46571A]" x-text="selectedOrder?.service_catalog?.name || 'Tindakan Keperawatan'"></span> </div> <div class="space-y-1 pt-2 border-t border-[#78956A]/50 text-[11px] text-[#78956A]"> <div class="flex justify-between"> <span>Tarif Tindakan Medis:</span> <span class="font-medium text-[#46571A]" x-text="'Rp ' + Number(selectedOrder?.base_price || 0).toLocaleString('id-ID')"></span> </div> <div class="flex justify-between"> <span>Biaya Transportasi GPS (<span x-text="(selectedOrder?.distance_km || 0) + ' km'"></span>):</span> <span class="font-medium text-[#46571A]" x-text="'Rp ' + Number(selectedOrder?.transport_fee || 0).toLocaleString('id-ID')"></span> </div> <div class="flex justify-between"> <span>Biaya Layanan Platform:</span> <span class="font-medium text-[#46571A]">Rp 5.000</span> </div> </div> <div class="flex justify-between items-baseline pt-2 border-t border-[#78956A]/50"> <div> <span class="text-[#46571A] font-bold block">Total Tagihan:</span> <span class="text-[10px] text-[#46571A] font-semibold">Tersimpan aman di Escrow CareAtHome</span> </div> <span class="text-xl font-black text-[#46571A]" x-text="'Rp ' + Number(selectedOrder?.total_amount || 0).toLocaleString('id-ID')"></span> </div> </div> <!-- Pilihan Metode Pembayaran Escrow --> <div class="space-y-2"> <div class="flex items-center justify-between"> <label class="block font-bold text-[#46571A]">Pilih Metode Pembayaran Non-Tunai:</label> <span class="text-[10px] text-[#78956A]">Verifikasi Otomatis</span> </div> <div class="grid grid-cols-2 gap-2"> <!-- BCA VA --> <label class="p-3 rounded-2xl border cursor-pointer transition flex items-center justify-between"
                            @click="changePaymentMethod('bca_va')"
                            :class="paymentMethod === 'bca_va' ? 'border-[#78956A] bg-[#D1EB8F]/45 ring-2 ring-[#78956A] font-bold' : 'border-[#78956A]/50 hover:bg-[#F9F4EA]'"> <div class="flex items-center gap-2"> <input type="radio" value="bca_va" x-model="paymentMethod" class="text-[#78956A]" /> <span>BCA Virtual Account</span> </div> <span class="text-[10px] bg-[#D1EB8F] text-[#46571A] font-extrabold px-1.5 py-0.5 rounded">BCA</span> </label> <!-- Mandiri VA --> <label class="p-3 rounded-2xl border cursor-pointer transition flex items-center justify-between"
                            @click="changePaymentMethod('mandiri_va')"
                            :class="paymentMethod === 'mandiri_va' ? 'border-[#78956A] bg-[#D1EB8F]/45 ring-2 ring-[#78956A] font-bold' : 'border-[#78956A]/50 hover:bg-[#F9F4EA]'"> <div class="flex items-center gap-2"> <input type="radio" value="mandiri_va" x-model="paymentMethod" class="text-[#78956A]" /> <span>Mandiri VA / Bill</span> </div> <span class="text-[10px] bg-[#D1EB8F]/70 text-[#46571A] font-extrabold px-1.5 py-0.5 rounded">MDR</span> </label> <!-- BNI VA --> <label class="p-3 rounded-2xl border cursor-pointer transition flex items-center justify-between"
                            @click="changePaymentMethod('bni_va')"
                            :class="paymentMethod === 'bni_va' ? 'border-[#78956A] bg-[#D1EB8F]/45 ring-2 ring-[#78956A] font-bold' : 'border-[#78956A]/50 hover:bg-[#F9F4EA]'"> <div class="flex items-center gap-2"> <input type="radio" value="bni_va" x-model="paymentMethod" class="text-[#78956A]" /> <span>BNI Virtual Account</span> </div> <span class="text-[10px] bg-orange-100 text-orange-800 font-extrabold px-1.5 py-0.5 rounded">BNI</span> </label> <!-- QRIS / E-Wallet --> <label class="p-3 rounded-2xl border cursor-pointer transition flex items-center justify-between"
                            @click="changePaymentMethod('qris')"
                            :class="paymentMethod === 'qris' ? 'border-[#78956A] bg-[#D1EB8F]/45 ring-2 ring-[#78956A] font-bold' : 'border-[#78956A]/50 hover:bg-[#F9F4EA]'"> <div class="flex items-center gap-2"> <input type="radio" value="qris" x-model="paymentMethod" class="text-[#78956A]" /> <span>QRIS &amp; E-Wallet</span> </div> <span class="text-[10px] bg-[#FF9ABC]/40 text-[#46571A] font-extrabold px-1.5 py-0.5 rounded">QRIS</span> </label> </div> </div> <!-- KOTAK INSTRUKSI BAYAR & NOMOR VIRTUAL ACCOUNT --> <div class="p-4 rounded-2xl bg-[#78956A] text-[#F9F4EA] space-y-3 relative overflow-hidden"> <div class="flex items-center justify-between text-[#F9F4EA]/90"> <span class="text-[11px] flex items-center gap-1.5"> <span class="w-2 h-2 rounded-full bg-[#D1EB8F] animate-pulse"></span> <span x-text="paymentMethod === 'qris' ? 'Kode Bayar QRIS Instan:' : 'Nomor Virtual Account ' + (paymentData?.bank || 'BCA') + ':'"></span> </span> <span class="text-[10px] text-[#D1EB8F] font-mono" x-text="'Batas Bayar: ' + (paymentData?.expiry_time || '2 Jam')"></span> </div> <template x-if="paymentMethod !== 'qris'"> <div class="flex items-center justify-between gap-3 bg-[#78956A] p-3 rounded-xl border border-[#78956A]"> <div> <span class="font-mono text-base sm:text-lg font-black tracking-wider text-[#D1EB8F] select-all"
                                    x-text="paymentData?.va_number || '880128123456789'"></span> <span class="text-[10px] text-[#78956A] block mt-0.5">Atas Nama: <strong>CareAtHome Escrow</strong></span> </div> <button type="button" @click="copyVaNumber()"
                                class="px-3 py-1.5 rounded-lg bg-[#FF9ABC] hover:bg-[#FF9ABC] text-[#46571A] text-xs font-bold transition flex items-center gap-1"> <span x-text="copiedVa ? ' Tersalin' : 'Salin'"></span> </button> </div> </template> <template x-if="paymentMethod === 'qris'"> <div class="bg-white p-3 rounded-xl text-[#46571A] flex flex-col items-center justify-center space-y-2 text-center"> <div class="w-36 h-36 bg-[#F9F4EA] rounded-lg border-2 border-dashed border-[#78956A] flex flex-col items-center justify-center p-2"> <span class="text-4xl"></span> <span class="text-[10px] font-mono font-bold mt-1 text-[#46571A]/85 block">QRIS Dinamis</span> <span class="text-[9px] text-[#46571A] font-bold">GoPay • OVO • Dana</span> </div> <p class="text-[11px] text-[#46571A]/85">Pindai kode QR melalui aplikasi perbankan atau dompet digital Anda.</p> </div> </template> </div> <!-- Jaminan Rekening Penampung (Escrow) --> <div class="p-3.5 rounded-2xl bg-[#D1EB8F]/45 border border-[#78956A]/50 text-[#46571A] flex items-start gap-2.5"> <span class="text-xl"><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg></span> <div class="leading-relaxed"> <span class="font-bold block">Jaminan Perlindungan Escrow CareAtHome:</span> <span class="text-[11px] text-[#46571A] block mt-0.5"> Dana Anda 100% aman tersimpan di rekening penampung resmi. Kompensasi baru akan diteruskan ke rekening perawat setelah tindakan selesai dan E-Report telah Anda verifikasi. Jika pesanan dibatalkan sebelum tindakan, dana dikembalikan 100% penuh.
                        </span> </div> </div> <!-- Pesan Notifikasi Sukses / Error --> <template x-if="paymentSuccessMsg"> <div class="p-3 rounded-xl bg-[#D1EB8F] text-[#46571A] text-xs font-semibold flex items-center gap-2" x-text="paymentSuccessMsg"></div> </template> <template x-if="paymentErrorMsg"> <div class="p-3 rounded-xl bg-[#FF9ABC]/40 text-[#46571A] text-xs font-semibold" x-text="paymentErrorMsg"></div> </template> </div> <!-- Modal Footer --> <div class="p-4 bg-[#F9F4EA] border-t border-[#78956A]/50 flex flex-wrap items-center justify-between gap-2.5"> <button type="button" @click="paymentModalOpen = false" :disabled="processingPayment"
                    class="px-4 py-2 rounded-xl border border-[#78956A]/50 bg-white hover:bg-[#F9F4EA] text-xs font-bold text-[#46571A] transition"> Tutup
                </button> <div class="flex items-center gap-2 ml-auto"> <!-- Tombol Konfirmasi Bayar Langsung --> <button type="button" @click="submitPayment()" :disabled="processingPayment"
                        class="px-5 py-2 rounded-xl bg-[#FF9ABC] hover:bg-[#FF9ABC] text-[#46571A] text-xs font-bold shadow-md shadow-[#78956A]/25 transition flex items-center gap-2"> <span x-show="processingPayment" class="animate-spin text-sm"><svg class="w-4 h-4 inline-block shrink-0 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></span> <span x-text="processingPayment ? 'Memproses Pembayaran...' : 'Bayar Sekarang'"></span> </button> </div> </div> </div> </div> <!-- ============================================================= --> <!-- 8.5 MODAL LEMBAR E-REPORT DIGITAL REKAM MEDIS PASIEN (KF-11)   --> <!-- ============================================================= --> <div x-show="ereportModalOpen" x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-[#46571A]/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-6"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"> <div @click.away="ereportModalOpen = false"
            class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl border border-[#78956A]/50 overflow-hidden text-[#46571A] max-h-[92vh] flex flex-col"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"> <!-- Header E-Report Bergaya Health Card Medis --> <div class="px-6 py-5 bg-[#78956A] text-[#F9F4EA] flex items-center justify-between shrink-0"> <div class="flex items-center gap-3"> <div class="w-11 h-11 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center text-2xl border border-white/20"> </div> <div> <div class="flex items-center gap-2"> <span class="text-[10px] uppercase tracking-wider font-extrabold text-[#D1EB8F]">Rekam Medis Asuhan Keperawatan</span> <span class="px-2 py-0.2 rounded-full text-[9px] font-bold bg-white/20 text-white border border-white/20">Terverifikasi</span> </div> <h3 class="text-base font-extrabold tracking-tight">Lembar E-Report Klinis Pasien</h3> </div> </div> <div class="flex items-center gap-2"> <button type="button" @click="window.print()" title="Cetak Lembar Medis"
                        class="p-2 rounded-xl bg-white/10 hover:bg-white/20 text-white transition text-xs flex items-center gap-1 cursor-pointer"> <span></span> <span class="hidden sm:inline text-[11px] font-bold">Cetak</span> </button> <button type="button" @click="ereportModalOpen = false"
                        class="p-2 rounded-xl bg-white/10 hover:bg-white/20 text-white transition text-xs"> </button> </div> </div> <!-- Loading State --> <div x-show="loadingEReport" class="p-12 text-center text-xs text-[#78956A] space-y-3"> <span class="animate-spin text-3xl inline-block"><svg class="w-4 h-4 inline-block shrink-0 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></span> <p class="font-bold">Memuat data rekam medis digital...</p> </div> <!-- Content Rekam Medis (Scrollable) --> <div x-show="!loadingEReport && reportData" class="overflow-y-auto grow p-6 space-y-6 text-xs"> <!-- KOP IDENTITAS REKAM MEDIS --> <div class="p-4 rounded-2xl bg-[#F9F4EA] border border-[#78956A]/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3"> <div class="space-y-1"> <span class="text-[10px] uppercase font-bold text-[#78956A] tracking-wider">Layanan Medis:</span> <h4 class="text-sm font-black text-[#46571A]" x-text="reportData?.service_name"></h4> <p class="text-[11px] text-[#46571A]/85">Pasien: <strong x-text="reportData?.patient_name"></strong></p> </div> <div class="space-y-1 sm:text-right border-t sm:border-t-0 pt-2 sm:pt-0 border-[#78956A]/50"> <span class="text-[10px] uppercase font-bold text-[#78956A] tracking-wider">Perawat Pengampu:</span> <h5 class="text-xs font-extrabold text-[#46571A]" x-text="reportData?.nurse_name"></h5> <p class="text-[10px] text-[#78956A]" x-text="'Diterbitkan: ' + reportData?.created_at"></p> </div> </div> <!-- 5 METRIC STAT CARDS TANDA-TANDA VITAL (TTV) --> <div class="space-y-2"> <div class="flex items-center justify-between"> <span class="text-xs font-bold text-[#46571A] uppercase tracking-wider flex items-center gap-1.5"> <span><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg></span> <span>Pemeriksaan Tanda-Tanda Vital (TTV)</span> </span> <span class="text-[10px] text-[#78956A]">Pemeriksaan Fisik Terstandar</span> </div> <div class="grid grid-cols-2 sm:grid-cols-3 gap-3"> <!-- Tekanan Darah --> <div class="p-3.5 rounded-2xl border bg-white shadow-2xs space-y-1.5"> <span class="text-[10px] font-bold text-[#78956A] uppercase tracking-wider block">Tekanan Darah</span> <div class="flex items-baseline gap-1"> <span class="text-xl font-black text-[#46571A]" x-text="reportData?.vital_signs?.blood_pressure?.value"></span> <span class="text-[10px] font-bold text-[#78956A]" x-text="reportData?.vital_signs?.blood_pressure?.unit"></span> </div> <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold border"
                                :class="reportData?.vital_signs?.blood_pressure?.category?.bg"
                                x-text="reportData?.vital_signs?.blood_pressure?.category?.label"></span> </div> <!-- Suhu Tubuh --> <div class="p-3.5 rounded-2xl border bg-white shadow-2xs space-y-1.5"> <span class="text-[10px] font-bold text-[#78956A] uppercase tracking-wider block">Suhu Tubuh</span> <div class="flex items-baseline gap-1"> <span class="text-xl font-black text-[#46571A]" x-text="reportData?.vital_signs?.body_temp?.value"></span> <span class="text-[10px] font-bold text-[#78956A]" x-text="reportData?.vital_signs?.body_temp?.unit"></span> </div> <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold border"
                                :class="reportData?.vital_signs?.body_temp?.category?.bg"
                                x-text="reportData?.vital_signs?.body_temp?.category?.label"></span> </div> <!-- Denyut Nadi --> <div class="p-3.5 rounded-2xl border bg-white shadow-2xs space-y-1.5"> <span class="text-[10px] font-bold text-[#78956A] uppercase tracking-wider block">Denyut Nadi</span> <div class="flex items-baseline gap-1"> <span class="text-xl font-black text-[#46571A]" x-text="reportData?.vital_signs?.pulse_rate?.value"></span> <span class="text-[10px] font-bold text-[#78956A]" x-text="reportData?.vital_signs?.pulse_rate?.unit"></span> </div> <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold border"
                                :class="reportData?.vital_signs?.pulse_rate?.category?.bg"
                                x-text="reportData?.vital_signs?.pulse_rate?.category?.label"></span> </div> <!-- Saturasi Oksigen SpO2 --> <div class="p-3.5 rounded-2xl border bg-white shadow-2xs space-y-1.5"> <span class="text-[10px] font-bold text-[#78956A] uppercase tracking-wider block">Saturasi Oksigen (SpO2)</span> <div class="flex items-baseline gap-1"> <span class="text-xl font-black text-[#46571A]" x-text="reportData?.vital_signs?.spo2?.value"></span> <span class="text-[10px] font-bold text-[#78956A]" x-text="reportData?.vital_signs?.spo2?.unit"></span> </div> <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold border"
                                :class="reportData?.vital_signs?.spo2?.category?.bg"
                                x-text="reportData?.vital_signs?.spo2?.category?.label"></span> </div> <!-- Gula Darah Sewaktu (GDS) --> <div class="p-3.5 rounded-2xl border bg-white shadow-2xs space-y-1.5 col-span-2 sm:col-span-1"> <span class="text-[10px] font-bold text-[#78956A] uppercase tracking-wider block">Gula Darah (GDS)</span> <div class="flex items-baseline gap-1"> <span class="text-xl font-black text-[#46571A]" x-text="reportData?.vital_signs?.gds?.value"></span> <span class="text-[10px] font-bold text-[#78956A]" x-text="reportData?.vital_signs?.gds?.unit"></span> </div> <template x-if="reportData?.vital_signs?.gds?.category"> <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold border"
                                    :class="reportData?.vital_signs?.gds?.category?.bg"
                                    x-text="reportData?.vital_signs?.gds?.category?.label"></span> </template> <template x-if="!reportData?.vital_signs?.gds?.category"> <span class="text-[10px] text-[#78956A] italic">Tidak dilakukan tes</span> </template> </div> </div> </div> <!-- CATATAN ASUHAN KEPERAWATAN SOAP --> <div class="p-4 rounded-2xl bg-white border border-[#78956A]/50 space-y-1.5"> <span class="text-[11px] font-extrabold text-[#46571A] uppercase tracking-wider flex items-center gap-1.5"> <span><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg></span> <span>Ringkasan Asuhan Keperawatan (SOAP)</span> </span> <p class="text-xs text-[#46571A] leading-relaxed whitespace-pre-line" x-text="reportData?.clinical_notes"></p> </div> <!-- EDUKASI & SARAN KELUARGA --> <div class="p-4 rounded-2xl bg-[#D1EB8F]/45 border border-[#78956A]/50 space-y-1.5"> <span class="text-[11px] font-extrabold text-[#46571A] uppercase tracking-wider flex items-center gap-1.5"> <span><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></span> <span>Instruksi &amp; Edukasi Keluarga Pasien</span> </span> <p class="text-xs text-[#46571A] leading-relaxed whitespace-pre-line" x-text="reportData?.family_education"></p> </div> <!-- FOTO DOKUMENTASI TINDAKAN MEDIS --> <template x-if="reportData?.photos && reportData.photos.length > 0"> <div class="space-y-2"> <span class="text-xs font-bold text-[#46571A] uppercase tracking-wider flex items-center gap-1.5"> <span></span> <span>Dokumentasi Foto Tindakan Medis</span> </span> <div class="grid grid-cols-2 sm:grid-cols-3 gap-3"> <template x-for="p in reportData.photos" :key="p.id"> <div class="rounded-xl border border-[#78956A]/50 overflow-hidden bg-[#F9F4EA] space-y-1 p-1"> <a :href="p.url" target="_blank" title="Klik untuk memperbesar"> <img :src="p.url" class="w-full h-28 object-cover rounded-lg hover:opacity-90 transition"> </a> <p class="text-[10px] text-[#78956A] truncate px-1" x-text="p.caption || 'Foto Tindakan'"></p> </div> </template> </div> </div> </template> </div> <!-- Footer E-Report --> <div class="p-4 bg-[#F9F4EA] border-t border-[#78956A]/50 flex items-center justify-between"> <span class="text-[10px] text-[#78956A]">Tercatat resmi dalam sistem rekam medis elektronik CareAtHome</span> <button type="button" @click="ereportModalOpen = false"
                    class="px-5 py-2 rounded-xl bg-[#78956A] hover:bg-[#78956A] text-[#F9F4EA] font-bold text-xs transition"> Tutup Lembar Medis
                </button> </div> </div> </div> <!-- ============================================================= --> <!-- 8.8 MODAL ULASAN & RATING INTERAKTIF PASIEN (KF-12)          --> <!-- ============================================================= --> <div x-show="reviewModalOpen" x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-[#46571A]/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-6"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"> <div @click.away="reviewModalOpen = false"
            class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-[#78956A]/50 overflow-hidden text-[#46571A]"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"> <div class="px-6 py-5 bg-[#78956A] text-[#F9F4EA] flex items-center justify-between"> <div class="flex items-center gap-2.5"> <span class="text-2xl">⭐</span> <div> <span class="text-[10px] uppercase font-extrabold tracking-wider text-[#F9F4EA] block">Evaluasi Pelayanan</span> <h3 class="text-base font-extrabold leading-tight">Beri Penilaian &amp; Ulasan</h3> </div> </div> <button type="button" @click="reviewModalOpen = false" class="p-1.5 rounded-xl bg-white/20 hover:bg-white/30 text-white text-xs"> </button> </div> <form :action="'/pasien/orders/' + selectedOrderForReview?.id + '/review'" method="POST" class="p-6 space-y-4 text-xs"> @csrf

                <!-- Info Perawat --> <div class="p-3.5 rounded-2xl bg-[#F9F4EA] border border-[#78956A]/50 text-center space-y-1"> <span class="text-[#78956A] text-[11px] block">Mitra Perawat:</span> <strong class="text-sm text-[#46571A] font-extrabold" x-text="selectedOrderForReview?.nurse?.name || 'Ns. CareAtHome'"></strong> <span class="text-[11px] text-[#78956A] block" x-text="selectedOrderForReview?.service_catalog?.name"></span> </div> <!-- Star Rating Interaktif (Hover & Click) --> <div class="text-center space-y-2 pt-2"> <label class="block text-xs font-extrabold text-[#46571A]">Bagaimana kualitas pelayanan perawat?</label> <input type="hidden" name="rating" :value="userRating"> <div class="flex items-center justify-center gap-2"> <template x-for="star in [1, 2, 3, 4, 5]" :key="star"> <button type="button"
                                @click="userRating = star"
                                @mouseenter="hoverRating = star"
                                @mouseleave="hoverRating = 0"
                                class="text-3xl transition-transform hover:scale-125 cursor-pointer focus:outline-none"
                                :class="(hoverRating || userRating) >= star ? 'text-[#78956A]' : 'text-[#F9F4EA]'"> 
                            </button> </template> </div> <!-- Label Kepuasan Dinamis --> <span class="text-xs font-bold block"
                        :class="userRating >= 4 ? 'text-[#46571A]' : (userRating === 3 ? 'text-[#46571A]' : 'text-[#46571A]')"
                        x-text="getRatingLabel(hoverRating || userRating)"> </span> </div> <!-- Kolom Komentar Ulasan --> <div class="pt-2"> <label class="block text-xs font-bold text-[#46571A] mb-1"> Komentar / Pengalaman Pelayanan <span class="text-[#78956A] font-normal">(Opsional)</span> </label> <textarea name="comment" rows="3"
                        placeholder="Ceritakan pengalaman Anda mengenai keramahan, ketelitian, dan kebersihan perawat saat bertugas..."
                        class="w-full text-xs rounded-xl border-[#78956A]/50 focus:border-[#78956A] focus:ring-[#78956A]"></textarea> </div> <!-- Tombol Aksi --> <div class="pt-2 flex items-center justify-end gap-2"> <button type="button" @click="reviewModalOpen = false"
                        class="px-4 py-2.5 rounded-xl border border-[#78956A]/50 text-xs font-bold text-[#46571A]/85 hover:bg-[#F9F4EA] transition"> Batal
                    </button> <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-[#78956A] hover:bg-[#D1EB8F] text-[#46571A] font-extrabold text-xs shadow-md shadow-orange-500/20 transition cursor-pointer"> Kirim Ulasan &amp; Rating
                    </button> </div> </form> </div> </div> <!-- 9. FOOTER --> <footer class="mt-auto border-t border-[#78956A]/50 bg-white py-6"> <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-xs text-[#78956A]"> CareAtHome Health-Tech Platform &copy; 2026. Platform Layanan Keperawatan On-Demand Terstandar Kemenkes RI.
        </div> </footer> <!-- Alpine App Logic for Orders, Escrow Payments, Tracking & Cancellation --> <script> function patientOrdersApp() {
            return {
                activeTab: 'active',
                paymentModalOpen: false,
                trackingModalOpen: false,
                cancelModalOpen: false,
                selectedOrder: null,

                // Payment & Escrow state
                paymentMethod: 'bca_va',
                paymentData: null,
                processingPayment: false,
                paymentSuccessMsg: '',
                paymentErrorMsg: '',
                copiedVa: false,

                // Cancel state
                cancelReason: 'Perubahan jadwal mendadak / ada keperluan lain',
                cancelNotes: '',
                processingCancel: false,
                cancelSuccessMsg: '',
                cancelErrorMsg: '',

                // Leaflet Map state
                trackingMap: null,
                patientMarker: null,
                nurseMarker: null,
                routeLine: null,
                routeCoordinates: [],
                isTrackingAnimating: false,
                trackingInterval: null,
                livePollingInterval: null,
                lastLiveSyncWib: '',
                liveDistanceKm: null,
                liveEtaMin: null,
                liveStatusText: '',
                originNurseCoords: null,
                patientCoords: null,

                // E-Report & Review State
                ereportModalOpen: false,
                loadingEReport: false,
                reportData: null,
                reviewModalOpen: false,
                selectedOrderForReview: null,
                userRating: 5,
                hoverRating: 0,

                async fetchAndOpenEReport(orderId) {
                    this.loadingEReport = true;
                    this.reportData = null;
                    this.ereportModalOpen = true;

                    try {
                        const res = await fetch('/pasien/orders/' + orderId + '/ereport', {
                            headers: { 'Accept': 'application/json' }
                        });
                        const data = await res.json();
                        if (res.ok && data.status === 'success') {
                            this.reportData = data.data;
                        } else {
                            alert(data.message || 'Gagal memuat lembar E-Report klinis.');
                            this.ereportModalOpen = false;
                        }
                    } catch (e) {
                        alert('Terjadi kesalahan jaringan saat memuat E-Report.');
                        this.ereportModalOpen = false;
                    } finally {
                        this.loadingEReport = false;
                    }
                },

                openReviewModal(order) {
                    this.selectedOrderForReview = order;
                    this.userRating = 5;
                    this.hoverRating = 0;
                    this.reviewModalOpen = true;
                },

                getRatingLabel(r) {
                    switch (parseInt(r)) {
                        case 1: return '⭐ Sangat Kecewa / Buruk';
                        case 2: return '⭐⭐ Kurang Puas';
                        case 3: return '⭐⭐⭐ Cukup Baik';
                        case 4: return '⭐⭐⭐⭐ Puas & Ramah';
                        case 5: return '⭐⭐⭐⭐⭐ Sangat Memuaskan & Profesional!';
                        default: return 'Pilih Bintang Penilaian';
                    }
                },

                init() {
                    const params = new URLSearchParams(window.location.search);
                    if (params.get('tab') === 'past') {
                        this.activeTab = 'past';
                    }
                    if (params.get('action') === 'pay') {
                        const unpaid = @json($activeOrders->firstWhere('payment_status', 'unpaid') ?? $activeOrders->first());
                        if (unpaid && unpaid.payment_status !== 'paid') {
                            this.$nextTick(() => {
                                this.openPaymentModal(unpaid);
                            });
                        }
                    }
                    if (params.get('action') === 'track') {
                        const activeOrder = @json($activeOrders->first());
                        if (activeOrder) {
                            this.$nextTick(() => {
                                this.openTrackingModal(activeOrder);
                            });
                        }
                    }
                },

                async directPayOrder(order) {
                    if (!order) return;
                    if (!confirm(`Konfirmasi pembayaran langsung untuk pesanan #${order.order_number} sebesar Rp ${Number(order.total_amount || 0).toLocaleString('id-ID')}?\n\nSaldo akan langsung masuk ke Perawat (85% + transport) dan Admin (15%).`)) {
                        return;
                    }

                    try {
                        const response = await fetch(`/pasien/pesanan/${order.id}/bayar`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            }
                        });
                        const res = await response.json();
                        if (response.ok && res.status === 'success') {
                            alert(` Pembayaran pesanan #${order.order_number} Berhasil! Dana telah langsung dialokasikan ke dompet Perawat dan Admin.`);
                            window.location.reload();
                        } else {
                            alert(res.message || 'Gagal memproses pembayaran.');
                        }
                    } catch (e) {
                        alert('Terjadi gangguan jaringan saat memproses pembayaran.');
                    }
                },

                async openPaymentModal(order) {
                    this.selectedOrder = order;
                    this.paymentMethod = 'bca_va';
                    this.paymentData = null;
                    this.paymentSuccessMsg = '';
                    this.paymentErrorMsg = '';
                    this.copiedVa = false;
                    this.paymentModalOpen = true;

                    // Muat parameter token dan VA dari backend
                    await this.loadPaymentToken();
                },

                async changePaymentMethod(method) {
                    this.paymentMethod = method;
                    await this.loadPaymentToken();
                },

                async loadPaymentToken() {
                    if (!this.selectedOrder) return;
                    try {
                        const response = await fetch(`/pasien/pesanan/${this.selectedOrder.id}/bayar/token`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({
                                payment_method: this.paymentMethod
                            })
                        });

                        const res = await response.json();
                        if (response.ok && res.data) {
                            this.paymentData = res.data;
                        }
                    } catch (e) {
                        console.warn('Gagal memuat token pembayaran:', e);
                    }
                },

                copyVaNumber() {
                    const va = this.paymentData?.va_number || '';
                    if (va) {
                        navigator.clipboard.writeText(va).then(() => {
                            this.copiedVa = true;
                            setTimeout(() => { this.copiedVa = false; }, 2500);
                        });
                    }
                },

                openSnapPopup() {
                    if (!this.paymentData?.snap_token || !window.snap) {
                        alert('Snap popup belum tersedia atau token sedang disiapkan.');
                        return;
                    }
                    const self = this;
                    window.snap.pay(this.paymentData.snap_token, {
                        onSuccess: function(result) {
                            self.submitPayment();
                        },
                        onPending: function(result) {
                            alert('Menunggu pembayaran via ' + (result.payment_type || 'Virtual Account'));
                        },
                        onError: function(result) {
                            alert('Pembayaran gagal atau dibatalkan.');
                        },
                        onClose: function() {
                            console.log('Snap popup ditutup pengguna');
                        }
                    });
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

                    if (this.trackingInterval) {
                        clearInterval(this.trackingInterval);
                        this.trackingInterval = null;
                    }
                    this.stopLiveTrackingPoll();
                    this.isTrackingAnimating = false;

                    // Beri waktu modal untuk dirender di DOM sebelum init Leaflet map & mulai live polling
                    this.$nextTick(() => {
                        this.initTrackingMap(order);
                        this.startLiveTrackingPoll(order.id);
                    });
                },

                closeTrackingModal() {
                    if (this.trackingInterval) {
                        clearInterval(this.trackingInterval);
                        this.trackingInterval = null;
                    }
                    this.stopLiveTrackingPoll();
                    this.isTrackingAnimating = false;
                    this.trackingModalOpen = false;
                },

                startLiveTrackingPoll(orderId) {
                    this.stopLiveTrackingPoll();
                    if (!orderId) return;
                    this.pollLiveTracking(orderId);
                    this.livePollingInterval = setInterval(() => {
                        if (this.trackingModalOpen && this.selectedOrder && this.selectedOrder.id === orderId) {
                            this.pollLiveTracking(orderId);
                        } else {
                            this.stopLiveTrackingPoll();
                        }
                    }, 5000);
                },

                stopLiveTrackingPoll() {
                    if (this.livePollingInterval) {
                        clearInterval(this.livePollingInterval);
                        this.livePollingInterval = null;
                    }
                },

                async pollLiveTracking(orderId) {
                    try {
                        const res = await fetch(`/pasien/pesanan/${orderId}/tracking`, {
                            headers: { 'Accept': 'application/json' }
                        });
                        if (!res.ok) return;
                        const json = await res.json();
                        if (json.status !== 'success' || !json.data) return;

                        const d = json.data;
                        this.lastLiveSyncWib = d.server_time_wib || '';

                        const prevStatus = this.selectedOrder?.status;
                        if (this.selectedOrder) {
                            this.selectedOrder.status = d.status;
                            this.selectedOrder.status_label = d.status_label;
                            this.selectedOrder.payment_status = d.payment_status;
                        }

                        if (!this.isTrackingAnimating) {
                            if (d.status === 'dalam_perjalanan') {
                                this.liveStatusText = ' Nakes Menuju Rumah Anda';
                            } else if (d.status === 'tiba') {
                                this.liveStatusText = ' Nakes Telah Tiba di Lokasi';
                            } else if (d.status === 'tindakan_berlangsung') {
                                this.liveStatusText = ' Sedang Melakukan Perawatan';
                            } else if (d.status === 'selesai') {
                                this.liveStatusText = ' Perawatan Selesai';
                            } else {
                                this.liveStatusText = d.status_label || 'Siap Berangkat';
                            }

                            if (d.nurse && typeof d.nurse.latitude === 'number' && typeof d.nurse.longitude === 'number') {
                                const newLat = d.nurse.latitude;
                                const newLng = d.nurse.longitude;
                                this.originNurseCoords = [newLat, newLng];
                                if (this.nurseMarker) {
                                    this.nurseMarker.setLatLng([newLat, newLng]);
                                }
                            }

                            if (d.routing) {
                                if (d.routing.distance_km) {
                                    this.liveDistanceKm = Number(d.routing.distance_km).toFixed(1);
                                }
                                if (d.routing.duration_minutes) {
                                    this.liveEtaMin = d.routing.duration_minutes;
                                }
                                if (Array.isArray(d.routing.geometry) && d.routing.geometry.length >= 2) {
                                    this.routeCoordinates = d.routing.geometry;
                                    if (this.routeLine) {
                                        this.routeLine.setLatLngs(this.routeCoordinates);
                                    }
                                }
                            }

                            // Jika status berubah menjadi 'dalam_perjalanan', otomatis jalankan animasi rute jalan raya
                            if (prevStatus !== 'dalam_perjalanan' && d.status === 'dalam_perjalanan') {
                                this.startNurseMovementSimulation();
                            }
                        }
                    } catch (e) {
                        // Silent fallback on temporary network hiccup
                    }
                },

                async initTrackingMap(order) {
                    if (this.trackingInterval) {
                        clearInterval(this.trackingInterval);
                        this.trackingInterval = null;
                    }
                    this.isTrackingAnimating = false;

                    // Titik Alamat Pasien
                    const patLat = parseFloat(order.latitude || order.patient_latitude) || -7.3144;
                    const patLng = parseFloat(order.longitude || order.patient_longitude) || 112.7304;
                    this.patientCoords = [patLat, patLng];

                    // Posisi Mitra Perawat (sama dengan logika Katalog: fallback -7.3144, 112.7304)
                    let nLat = parseFloat(order.nurse?.nurse_profile?.latitude);
                    let nLng = parseFloat(order.nurse?.nurse_profile?.longitude);

                    if (isNaN(nLat) || isNaN(nLng)) {
                        nLat = -7.3144;
                        nLng = 112.7304;
                    }

                    const catalogDist = parseFloat(order.distance_km) || 0.1;

                    // Jika jarak di katalog 0.1 km (atau titik sama), geser visual marker ~65 meter (0.1 km)
                    // agar pin perawat & pasien berdampingan rapi dan jarak tetap akurat 0.1 km sesuai katalog!
                    if ((Math.abs(nLat - patLat) < 0.0008 && Math.abs(nLng - patLng) < 0.0008) || catalogDist <= 0.2) {
                        nLat = patLat - 0.0006;
                        nLng = patLng + 0.0005;
                    }
                    this.originNurseCoords = [nLat, nLng];
                    this.routeCoordinates = [this.originNurseCoords, this.patientCoords];

                    this.liveDistanceKm = catalogDist.toFixed(1);
                    this.liveEtaMin = Math.max(1, Math.ceil(catalogDist * 3));
                    this.liveStatusText = order.status === 'dalam_perjalanan' ? ' Nakes Menuju Rumah Anda' : (order.status_label || 'Siap Berangkat');

                    const mapContainer = document.getElementById('tracking-map');
                    if (!mapContainer) return;

                    if (this.trackingMap) {
                        this.trackingMap.remove();
                        this.trackingMap = null;
                    }

                    this.trackingMap = L.map('tracking-map').setView([patLat, patLng], 14);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(this.trackingMap);

                    const patientIcon = L.divIcon({
                        className: 'patient-div-icon',
                        html: `
                            <div style="position:relative; width:34px; height:34px; display:flex; align-items:center; justify-content:center;"> <div style="position:absolute; width:34px; height:34px; border-radius:50%; background:rgba(225,29,72,0.35); animation:pulseGlow 1.6s infinite;"></div> <div style="width:26px; height:26px; border-radius:50%; background:#e11d48; color:white; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:bold; box-shadow:0 4px 6px -1px rgba(0,0,0,0.3); border:2px solid #ffffff;"> </div> </div> `,
                        iconSize: [34, 34],
                        iconAnchor: [17, 17],
                    });

                    this.patientMarker = L.marker([patLat, patLng], { icon: patientIcon })
                        .addTo(this.trackingMap)
                        .bindPopup(`<strong>Titik Alamat Pasien</strong><br>${order.patient_name || 'Rumah Pasien'}`)
                        .openPopup();

                    const nurseName = order.nurse?.name || 'Mitra Perawat';
                    const nurseIcon = L.divIcon({
                        className: 'nurse-div-icon',
                        html: `
                            <div style="position:relative; display:inline-flex; align-items:center; justify-content:center;"> <div style="position:absolute; width:100%; height:100%; border-radius:9999px; background:rgba(16,185,129,0.35); animation:pulseGlow 1.4s infinite;"></div> <div style="padding:4px 10px; border-radius:9999px; background:#78956A; color:white; display:flex; align-items:center; gap:5px; font-size:11px; font-weight:800; box-shadow:0 10px 15px -3px rgba(0,0,0,0.3); border:2px solid #FF9ABC; white-space:nowrap;"> <span><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" /></svg></span> <span>${nurseName}</span> </div> </div> `,
                        iconSize: [110, 32],
                        iconAnchor: [55, 16],
                    });

                    this.nurseMarker = L.marker([nLat, nLng], { icon: nurseIcon })
                        .addTo(this.trackingMap)
                        .bindPopup(`<strong>Posisi Mitra Nakes</strong><br>${nurseName} (Rute Jalan OSRM Aktif)`);

                    // Ambil geometri rute jalan raya sebenarnya dari OSRM Public API
                    try {
                        const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${nLng},${nLat};${patLng},${patLat}?overview=full&geometries=geojson`;
                        const res = await fetch(osrmUrl);
                        const json = await res.json();
                        if (json && json.routes && json.routes[0] && json.routes[0].geometry && Array.isArray(json.routes[0].geometry.coordinates)) {
                            const route = json.routes[0];
                            const roadCoords = route.geometry.coordinates.map(c => [c[1], c[0]]);
                            if (roadCoords.length >= 2) {
                                this.routeCoordinates = roadCoords;
                            }
                            // Pertahankan jarak yang tersimpan sesuai katalog (order.distance_km)
                            if (!order.distance_km && route.distance) {
                                const km = Math.max(0.1, Math.round((route.distance / 1000) * 100) / 100);
                                this.liveDistanceKm = km.toFixed(1);
                                this.liveEtaMin = Math.max(1, Math.ceil(km * 3));
                            }
                        }
                    } catch (e) {
                        // Fallback ke polyline 2 titik jika OSRM tidak terjangkau
                    }

                    // Garis Rute Jalan Raya (mengikuti belokan jalan OSRM)
                    this.routeLine = L.polyline(this.routeCoordinates, {
                        color: '#FF9ABC',
                        weight: 5,
                        opacity: 0.9,
                        className: 'animated-route-polyline',
                    }).addTo(this.trackingMap);

                    this.trackingMap.fitBounds(this.routeLine.getBounds(), { padding: [45, 45] });

                    setTimeout(() => {
                        if (this.trackingMap) {
                            this.trackingMap.invalidateSize();
                        }
                    }, 300);
                },

                startNurseMovementSimulation() {
                    if (this.isTrackingAnimating || !this.nurseMarker || !this.routeCoordinates || this.routeCoordinates.length < 2) {
                        return;
                    }

                    this.isTrackingAnimating = true;
                    this.liveStatusText = ' Nakes Meluncur Mengikuti Rute Jalan...';

                    const totalDist = parseFloat(this.liveDistanceKm) || parseFloat(this.selectedOrder?.distance_km) || 2.8;
                    const totalEta = Math.max(4, Math.round(totalDist * 3));

                    // Bangun titik-titik halus di sepanjang polyline rute jalan OSRM
                    const coords = this.routeCoordinates;
                    const smoothPath = [];
                    const targetSteps = 75;

                    for (let i = 0; i < targetSteps; i++) {
                        const t = i / (targetSteps - 1);
                        const exactIdx = t * (coords.length - 1);
                        const idx = Math.floor(exactIdx);
                        const frac = exactIdx - idx;
                        if (idx >= coords.length - 1) {
                            smoothPath.push(coords[coords.length - 1]);
                        } else {
                            const p1 = coords[idx];
                            const p2 = coords[idx + 1];
                            smoothPath.push([
                                p1[0] + (p2[0] - p1[0]) * frac,
                                p1[1] + (p2[1] - p1[1]) * frac,
                            ]);
                        }
                    }

                    let currentStep = 0;
                    const stepInterval = 110;

                    if (this.trackingInterval) {
                        clearInterval(this.trackingInterval);
                    }

                    this.trackingInterval = setInterval(() => {
                        if (currentStep >= smoothPath.length) {
                            clearInterval(this.trackingInterval);
                            this.trackingInterval = null;
                            this.isTrackingAnimating = false;
                            return;
                        }

                        const [curLat, curLng] = smoothPath[currentStep];
                        currentStep++;
                        const progress = Math.min(1.0, currentStep / smoothPath.length);

                        if (this.nurseMarker) {
                            this.nurseMarker.setLatLng([curLat, curLng]);
                        }

                        const remainingDist = Math.max(0, totalDist * (1.0 - progress));
                        const remainingEta = Math.max(1, Math.round(totalEta * (1.0 - progress)));

                        this.liveDistanceKm = remainingDist.toFixed(1);
                        this.liveEtaMin = remainingEta;

                        if (progress >= 0.85 && progress < 1.0) {
                            this.liveStatusText = ' Nakes Hampir Tiba di Rumah Anda';
                        } else if (progress >= 1.0) {
                            clearInterval(this.trackingInterval);
                            this.trackingInterval = null;
                            this.isTrackingAnimating = false;
                            this.liveDistanceKm = '0.0';
                            this.liveEtaMin = 0;
                            this.liveStatusText = ' Nakes Telah Tiba di Lokasi Pasien!';
                            if (this.nurseMarker) {
                                this.nurseMarker.bindPopup(`<strong> Nakes Telah Tiba!</strong><br>Persiapan tindakan medis dimulai.`).openPopup();
                            }
                        }
                    }, stepInterval);
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
                        this.paymentErrorMsg = err.message || 'Terjadi kesalahan sistem saat memproses pembayaran.';
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
