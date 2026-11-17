<x-app-layout>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <div class="py-8" x-data="nurseFeedApp()"> <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6"> <!-- Flash Status Messages --> @if (session('status'))
                <div class="p-4 rounded-2xl bg-[#D1EB8F]/45 border border-[#78956A]/50 text-xs font-semibold text-[#46571A] flex items-center justify-between shadow-xs"> <div class="flex items-center gap-2"> <span><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg></span> <span>{{ session('status') }}</span> </div> </div> @endif

            @if (session('error'))
                <div class="p-4 rounded-2xl bg-[#FF9ABC]/25 border border-[#FF9ABC] text-xs font-semibold text-[#46571A] flex items-center justify-between shadow-xs"> <div class="flex items-center gap-2"> <span><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg></span> <span>{{ session('error') }}</span> </div> </div> @endif

            <!-- Alert Toast Frontend --> <div x-show="toastMessage" x-cloak
                class="p-4 rounded-2xl flex items-center justify-between shadow-lg text-xs font-semibold transition"
                :class="toastType === 'success' ? 'bg-[#FF9ABC] text-[#46571A]' : 'bg-[#FF9ABC] text-[#46571A]'"> <div class="flex items-center gap-2"> <span x-show="toastType === 'success'"><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></span><span x-show="toastType !== 'success'"><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg></span> <span x-text="toastMessage"></span> </div> <button @click="toastMessage = ''" class="text-white/80 hover:text-white px-2 py-0.5">Tutup</button> </div> <!-- Banner Header Feed --> <div class="bg-[#78956A] rounded-3xl p-6 sm:p-8 text-[#F9F4EA] shadow-xl relative overflow-hidden border border-[#78956A]"> <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-[#FF9ABC] rounded-full blur-3xl pointer-events-none"></div> <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10"> <div class="space-y-2 max-w-xl"> <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#FF9ABC] border border-[#78956A] text-[#D1EB8F] text-xs font-semibold"> <span class="w-2 h-2 rounded-full bg-[#D1EB8F] animate-pulse"></span> Real-Time Medical Job Board
                        </div> <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight"> Peluang Kunjungan Medis Tersedia
                        </h1> <p class="text-xs sm:text-sm text-[#F9F4EA]/90 leading-relaxed"> Pesanan pasien yang membutuhkan perawatan di rumah terdekat. Anda mendapatkan <strong>85% dari tarif tindakan medis + 100% ongkos transportasi pasien</strong> yang langsung masuk ke saldo dompet saat pesanan selesai.
                        </p> </div> <div class="flex items-center gap-3"> <div class="bg-white/10 backdrop-blur-md px-4 py-3 rounded-2xl border border-white/10 text-center min-w-[120px]"> <span class="text-[10px] text-[#F9F4EA]/90 block uppercase font-bold tracking-wider">Order Masuk</span> <span class="text-2xl font-black text-[#D1EB8F]">{{ $feedOrders->count() }}</span> </div> <div class="bg-white/10 backdrop-blur-md px-4 py-3 rounded-2xl border border-white/10 text-center min-w-[120px]"> <span class="text-[10px] text-[#F9F4EA]/90 block uppercase font-bold tracking-wider">Sedang Dikerjakan</span> <span class="text-2xl font-black text-[#D1EB8F]">{{ $activeOrders->count() }}</span> </div> </div> </div> </div> <!-- JOB BOARD GRID: ORDER MASUK TERSAJI --> <div class="space-y-4"> <div class="flex items-center justify-between"> <div> <h3 class="text-base font-extrabold text-[#46571A] tracking-tight flex items-center gap-2"> <span>Daftar Order Kunjungan Siap Diambil</span> <span class="text-xs px-2.5 py-0.5 rounded-full bg-[#D1EB8F]/45 text-[#46571A] border border-[#78956A]/50 font-bold"> {{ $feedOrders->count() }} Tersedia
                            </span> </h3> <p class="text-xs text-[#78956A] mt-0.5">Tekan "Terima Order" untuk mengambil penugasan. Sistem mencegah persaingan dengan penguncian real-time.</p> </div> <a href="{{ route('perawat.dashboard') }}" class="text-xs font-bold text-[#46571A] hover:text-[#46571A] flex items-center gap-1"> <span>Panel Kerja &rarr;</span> </a> </div> @if ($feedOrders->isEmpty())
                    <!-- Empty State dengan Ilustrasi Radar --> <div class="bg-white rounded-3xl border border-[#78956A]/50 p-12 text-center space-y-4 shadow-sm"> <div class="w-16 h-16 rounded-3xl bg-[#F9F4EA] text-[#78956A] flex items-center justify-center text-3xl mx-auto shadow-inner relative"> <span><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" /></svg></span> <span class="absolute -top-1 -right-1 flex h-3 w-3"> <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#D1EB8F] opacity-75"></span> <span class="relative inline-flex rounded-full h-3 w-3 bg-[#FF9ABC]"></span> </span> </div> <div class="space-y-1"> <h4 class="text-base font-bold text-[#46571A]">Belum Ada Order Kunjungan Baru Saat Ini</h4> <p class="text-xs text-[#78956A] max-w-md mx-auto"> Sistem memantau pesanan masuk secara real-time tanpa batasan radius jarak. Pastikan Jadwal Operasional Harian (WIB) Anda aktif di dashboard.
                            </p> </div> <div class="pt-2"> <a href="{{ route('perawat.dashboard') }}" class="px-5 py-2.5 rounded-xl bg-[#78956A] hover:bg-[#78956A] text-[#F9F4EA] text-xs font-bold shadow-xs transition inline-flex items-center gap-2"> <span>Atur Jadwal &amp; Layanan di Dashboard &rarr;</span> </a> </div> </div> @else
                    <!-- Responsive Grid of Job Cards --> <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"> @foreach ($feedOrders as $ord)
                            <div class="bg-white rounded-3xl border-2 border-[#78956A]/50 hover:border-[#78956A] transition-all shadow-sm hover:shadow-md p-6 flex flex-col justify-between space-y-5 group"
                                id="feed-card-{{ $ord->id }}"
                                x-data="{ isProcessing: false, isDismissed: false }"
                                x-show="!isDismissed"> <!-- Card Header --> <div class="space-y-3"> <div class="flex items-start justify-between gap-2 border-b border-[#78956A]/50 pb-3"> <div class="flex items-center gap-2.5"> <div class="w-10 h-10 rounded-2xl bg-[#D1EB8F]/45 text-[#46571A] flex items-center justify-center text-xl border border-[#78956A]/50"> <svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg> </div> <div> <span class="font-mono text-[10px] font-bold text-[#78956A] uppercase tracking-wider block"> {{ $ord->order_number }}
                                                </span> <h4 class="font-extrabold text-sm text-[#46571A] leading-snug"> {{ $ord->serviceCatalog->name ?? 'Layanan Keperawatan' }}
                                                </h4> </div> </div> <!-- Distance Badge --> <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-[#D1EB8F]/45 text-[#46571A] border border-[#78956A]/50 flex items-center gap-1 whitespace-nowrap shadow-2xs"> <span><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg></span> <span>{{ $ord->calculated_distance ?? $ord->distance_km ?? 0 }} km</span> </span> </div> <!-- Schedule & Patient Location --> <div class="space-y-2 text-xs text-[#46571A]/85 bg-[#F9F4EA] p-3.5 rounded-2xl border border-[#78956A]/50"> <div class="flex items-center justify-between"> <span class="text-[#78956A] text-[10px] uppercase font-bold tracking-wider">Jadwal:</span> <span class="font-bold text-[#46571A]"> {{ $ord->scheduled_date ? $ord->scheduled_date->format('d M Y') : '-' }} &bull; {{ str_contains($ord->scheduled_time ?? '', 'WIB') ? $ord->scheduled_time : (($ord->scheduled_time ?? '09:00') . ' WIB') }}
                                            </span> </div> <div class="flex items-start justify-between gap-2"> <span class="text-[#78956A] text-[10px] uppercase font-bold tracking-wider shrink-0">Pasien:</span> <span class="font-bold text-[#46571A] text-right truncate max-w-[200px]"> {{ $ord->patient_name }}
                                            </span> </div> <div class="flex items-start justify-between gap-2 border-t border-[#78956A]/50 pt-1.5"> <span class="text-[#78956A] text-[10px] uppercase font-bold tracking-wider shrink-0">Lokasi:</span> <span class="font-medium text-[#46571A] text-right text-[11px] leading-tight line-clamp-2" title="{{ $ord->address }}"> {{ $ord->address }}
                                            </span> </div> </div> <!-- Keluhan Klinis Pasien --> <div class="p-3 rounded-2xl bg-[#D1EB8F]/45 border border-[#78956A]/50 text-xs space-y-1"> <div class="flex items-center gap-1.5 text-[#46571A] font-extrabold text-[11px]"> <span><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg></span> <span>Keluhan &amp; Catatan Pasien:</span> </div> <p class="text-[#46571A] text-[11px] leading-relaxed line-clamp-3 italic"> "{{ $ord->clinical_notes ?: 'Pasien meminta asuhan keperawatan dan pemantauan kondisi fisik umum di rumah.' }}"
                                        </p> </div> <!-- ESTIMASI KOMISI BERSIH PERAWAT --> <div class="p-3.5 rounded-2xl bg-[#D1EB8F]/40 border border-[#78956A]/50 flex items-center justify-between"> <div class="space-y-0.5"> <span class="text-[10px] font-bold text-[#46571A] uppercase tracking-wider block">Estimasi Komisi Bersih:</span> <span class="text-base font-black text-[#46571A]"> Rp {{ number_format($ord->estimated_nurse_earnings, 0, ',', '.') }}
                                            </span> </div> <span class="text-[9px] font-bold text-[#46571A] bg-[#D1EB8F] px-2 py-0.5 rounded-full border border-[#78956A]"> 85% + Transport
                                        </span> </div> </div> <!-- Action Buttons: Terima / Tolak Order --> <div class="pt-2 grid grid-cols-2 gap-2.5"> <button type="button"
                                        :disabled="isProcessing"
                                        @click="handleDeclineOrder({{ $ord->id }}, '{{ $ord->order_number }}', $data)"
                                        class="w-full py-2.5 px-3 rounded-xl bg-[#F9F4EA] hover:bg-[#FF9ABC]/25 text-[#46571A]/85 hover:text-[#46571A] border border-[#78956A]/50 hover:border-[#FF9ABC] font-extrabold text-xs transition flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50"> <span> Tolak</span> </button> <button type="button"
                                        :disabled="isProcessing"
                                        @click="handleAcceptOrder({{ $ord->id }}, '{{ $ord->order_number }}', $data)"
                                        class="w-full py-2.5 px-3 rounded-xl bg-[#78956A] hover:bg-[#D1EB8F] text-[#46571A] font-black text-xs shadow-md shadow-[#78956A]/25 transition transform active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50"> <template x-if="!isProcessing"> <span class="flex items-center gap-1"> <span> Terima Order</span> </span> </template> <template x-if="isProcessing"> <span class="flex items-center gap-1"> <span class="animate-spin"><svg class="w-4 h-4 inline-block shrink-0 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></span> <span>Proses...</span> </span> </template> </button> </div> </div> @endforeach
                    </div> @endif
            </div> <!-- SECTION PESANAN YANG SEDANG DIKERJAKAN --> @if ($activeOrders->isNotEmpty())
                <div class="bg-white rounded-3xl border border-[#78956A]/50 p-6 sm:p-7 shadow-sm space-y-4"> <div class="flex items-center justify-between border-b border-[#78956A]/50 pb-3"> <div class="space-y-0.5"> <h3 class="text-sm font-extrabold text-[#46571A] tracking-tight flex items-center gap-2"> <span>Tugas Kunjungan yang Sedang Anda Kerjakan</span> <span class="text-xs px-2 py-0.5 rounded-full bg-[#D1EB8F]/45 text-[#46571A] border border-[#78956A]/50 font-bold"> {{ $activeOrders->count() }} Aktif
                                </span> </h3> <p class="text-xs text-[#78956A]">Pesanan yang telah Anda terima. Selesaikan asuhan keperawatan dan isi E-Report klinis.</p> </div> <a href="{{ route('perawat.dashboard') }}" class="text-xs font-bold text-[#46571A] hover:underline"> Ke Dashboard Utama &rarr;
                        </a> </div> <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"> @foreach ($activeOrders as $act)
                            @php
                                $actCheck = $act->canNursePerformAction();
                                $canActFeed = $actCheck['allowed'] ?? true;
                            @endphp
                            <div class="p-4 rounded-2xl border {{ $canActFeed ? 'border-[#78956A]/50 bg-[#D1EB8F]/45' : 'border-[#78956A]/50 bg-[#F9F4EA]' }} space-y-3"> <div class="flex items-center justify-between"> <span class="font-mono text-[10px] font-bold text-[#78956A]">{{ $act->order_number }}</span> <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#D1EB8F] text-[#78956A]"> {{ $act->status_label }}
                                    </span> </div> <div> <h5 class="font-bold text-xs text-[#46571A]">{{ $act->serviceCatalog->name ?? 'Layanan Medis' }}</h5> <p class="text-[11px] text-[#78956A]">Pasien: {{ $act->patient_name }} ({{ $act->patient_phone }})</p> <p class="text-[11px] font-semibold text-[#78956A] mt-0.5"> Jadwal: {{ $act->scheduled_date?->format('d M Y') }} • {{ str_contains($act->scheduled_time ?? '', 'WIB') ? $act->scheduled_time : ($act->scheduled_time . ' WIB') }}
                                    </p> </div> @if (!$canActFeed)
                                    <div class="p-2.5 rounded-xl bg-[#D1EB8F]/70 border border-[#78956A] text-[10px] text-[#46571A] font-semibold"> {{ $actCheck['message'] }}
                                    </div> @endif

                                <div class="pt-2 border-t border-[#78956A]/50 space-y-2">
                                    @php
                                        $actLat = $act->latitude ?? $act->patient_latitude;
                                        $actLng = $act->longitude ?? $act->patient_longitude;
                                        $actGmaps = ($actLat && $actLng)
                                            ? ('https://www.google.com/maps/dir/?api=1&' . ($profile->latitude && $profile->longitude ? "origin={$profile->latitude},{$profile->longitude}&" : '') . "destination={$actLat},{$actLng}&travelmode=driving")
                                            : ('https://www.google.com/maps/search/?api=1&query=' . urlencode($act->address));
                                    @endphp
                                    <div class="grid grid-cols-2 gap-1.5">
                                        <button type="button" @click="openRouteModal(@js($act))"
                                            class="py-1.5 px-2.5 rounded-xl bg-[#D1EB8F] hover:bg-[#78956A] text-[#46571A] hover:text-[#F9F4EA] border border-[#78956A] text-[11px] font-bold transition text-center cursor-pointer">
                                            Lihat Rute (App)
                                        </button>
                                        <a href="{{ $actGmaps }}" target="_blank" rel="noopener noreferrer"
                                            class="py-1.5 px-2.5 rounded-xl bg-[#FFFDF9] hover:bg-[#D1EB8F] text-[#46571A] border border-[#78956A] text-[11px] font-bold transition text-center">
                                            Google Maps
                                        </a>
                                    </div>
                                    @if ($act->status === 'diterima')
                                        <button type="button"
                                            @if($canActFeed) @click="updateOrderStatus({{ $act->id }}, 'dalam_perjalanan')" @else disabled @endif
                                            class="w-full py-2 px-3 rounded-xl font-bold text-xs transition flex items-center justify-center gap-1.5 {{ $canActFeed ? 'bg-[#FF9ABC] hover:bg-[#78956A] text-[#46571A] cursor-pointer' : 'bg-[#D1EB8F]/50 text-[#78956A] cursor-not-allowed opacity-75' }}"> <span> Mulai Perjalanan</span> </button> @elseif ($act->status === 'dalam_perjalanan')
                                        <button type="button"
                                            @if($canActFeed) @click="updateOrderStatus({{ $act->id }}, 'tiba')" @else disabled @endif
                                            class="w-full py-2 px-3 rounded-xl font-bold text-xs transition flex items-center justify-center gap-1.5 {{ $canActFeed ? 'bg-[#FF9ABC] hover:bg-[#D1EB8F] text-[#46571A] cursor-pointer' : 'bg-[#D1EB8F]/50 text-[#78956A] cursor-not-allowed opacity-75' }}"> <span> Sampai di Lokasi</span> </button> @elseif ($act->status === 'tiba')
                                        <button type="button"
                                            @if($canActFeed) @click="updateOrderStatus({{ $act->id }}, 'tindakan_berlangsung')" @else disabled @endif
                                            class="w-full py-2 px-3 rounded-xl font-bold text-xs transition flex items-center justify-center gap-1.5 {{ $canActFeed ? 'bg-[#FF9ABC] hover:bg-[#FF9ABC] text-[#46571A] cursor-pointer' : 'bg-[#D1EB8F]/50 text-[#78956A] cursor-not-allowed opacity-75' }}"> <span> Mulai Perawatan</span> </button> @endif
                                    <a href="{{ route('perawat.dashboard') }}" class="w-full py-2 px-3 rounded-xl bg-[#78956A] hover:bg-[#78956A] text-[#F9F4EA] font-bold text-xs transition flex items-center justify-center gap-1.5"> <span> Selesai Perawatan &amp; Isi E-Report &rarr;</span> </a> </div> </div> @endforeach
                    </div> </div> @endif

        </div>

        <!-- MODAL PETA RUTE MENUJU RUMAH PASIEN -->
        <div x-show="routeModalOpen" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto bg-[#46571A]/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-6">
            <div @click.away="routeModalOpen = false"
                class="bg-[#FFFDF9] rounded-3xl max-w-2xl w-full shadow-2xl border border-[#78956A] overflow-hidden text-[#46571A] flex flex-col">
                <div class="px-6 py-4 bg-[#78956A] text-[#F9F4EA] flex items-center justify-between">
                    <div>
                        <span class="text-[10px] uppercase font-bold tracking-wider text-[#D1EB8F]" x-text="'Navigasi Kunjungan #' + (routeOrder?.order_number || '')"></span>
                        <h3 class="text-base font-bold mt-0.5" x-text="'Rute ke Rumah ' + (routeOrder?.patient_name || 'Pasien')"></h3>
                    </div>
                    <button type="button" @click="routeModalOpen = false" class="p-1.5 rounded-xl hover:bg-white/10 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="p-5 space-y-3 text-xs">
                    <div class="grid grid-cols-2 gap-3 p-3 rounded-2xl bg-[#F9F4EA] border border-[#78956A]/40">
                        <div>
                            <span class="text-[10px] font-bold uppercase text-[#78956A] block">Jarak Rute Jalan</span>
                            <span class="text-sm font-extrabold text-[#46571A]" x-text="routeDistanceKm"></span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold uppercase text-[#78956A] block">Estimasi Waktu Tempuh</span>
                            <span class="text-sm font-extrabold text-[#46571A]" x-text="routeDurationMin"></span>
                        </div>
                    </div>
                    <p class="text-[11px] text-[#46571A]/85"><strong>Alamat Pasien:</strong> <span x-text="routeOrder?.address || '-'"></span></p>
                    <div id="feedRouteLeafletMap" class="w-full h-72 rounded-2xl border border-[#78956A] z-0"></div>
                </div>
                <div class="px-6 py-4 bg-[#F9F4EA] border-t border-[#78956A]/30 flex flex-wrap items-center justify-between gap-2">
                    <a :href="routeOrder ? ('https://www.google.com/maps/dir/?api=1&' + (nurseLat && nurseLng ? `origin=${nurseLat},${nurseLng}&` : '') + `destination=${routeOrder.latitude || routeOrder.patient_latitude},${routeOrder.longitude || routeOrder.patient_longitude}&travelmode=driving`) : '#'"
                        target="_blank" rel="noopener noreferrer"
                        class="px-4 py-2 rounded-xl bg-[#FF9ABC] hover:bg-[#D1EB8F] text-[#46571A] border border-[#78956A] text-xs font-bold transition flex items-center gap-1.5">
                        <span>Buka Navigasi di Google Maps</span>
                    </a>
                    <button type="button" @click="routeModalOpen = false"
                        class="px-4 py-2 rounded-xl border border-[#78956A] bg-[#FFFDF9] text-xs font-bold text-[#46571A] cursor-pointer">Tutup</button>
                </div>
            </div>
        </div>
        </div> <!-- Alpine.js Feed Logic --> <script> function nurseFeedApp() {
            return {
                toastMessage: '',
                toastType: 'success',
                nurseLat: {{ $profile->latitude ?? 'null' }},
                nurseLng: {{ $profile->longitude ?? 'null' }},
                routeModalOpen: false,
                routeOrder: null,
                routeMap: null,
                routeDistanceKm: '-',
                routeDurationMin: '-',

                openRouteModal(order) {
                    this.routeOrder = order;
                    this.routeDistanceKm = order.distance_km ? `${Number(order.distance_km).toFixed(1)} km` : '-';
                    this.routeDurationMin = order.distance_km ? `~${Math.max(3, Math.round(Number(order.distance_km) * 3))} menit` : '-';
                    this.routeModalOpen = true;

                    this.$nextTick(async () => {
                        if (typeof L === 'undefined') return;
                        const nLat = parseFloat(this.nurseLat || -7.307743);
                        const nLng = parseFloat(this.nurseLng || 112.729963);
                        const pLat = parseFloat(order.latitude || order.patient_latitude || nLat);
                        const pLng = parseFloat(order.longitude || order.patient_longitude || nLng);

                        if (this.routeMap) {
                            this.routeMap.remove();
                            this.routeMap = null;
                        }

                        this.routeMap = L.map('feedRouteLeafletMap').setView([(nLat + pLat) / 2, (nLng + pLng) / 2], 14);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; OpenStreetMap'
                        }).addTo(this.routeMap);

                        const nurseMarker = L.marker([nLat, nLng]).addTo(this.routeMap).bindPopup('<strong>Posisi Anda (Perawat)</strong>').openPopup();
                        const patientMarker = L.marker([pLat, pLng]).addTo(this.routeMap).bindPopup(`<strong>Rumah Pasien: ${order.patient_name || 'Pasien'}</strong><br>${order.address || ''}`);

                        try {
                            const url = `https://router.project-osrm.org/route/v1/driving/${nLng},${nLat};${pLng},${pLat}?overview=full&geometries=geojson`;
                            const res = await fetch(url);
                            const json = await res.json();
                            if (json && json.routes && json.routes[0]) {
                                const r = json.routes[0];
                                const coords = r.geometry.coordinates.map(c => [c[1], c[0]]);
                                const poly = L.polyline(coords, { color: '#46571A', weight: 5, opacity: 0.85 }).addTo(this.routeMap);
                                this.routeMap.fitBounds(poly.getBounds().pad(0.25));
                                this.routeDistanceKm = (Math.max(0.1, r.distance / 1000)).toFixed(1) + ' km';
                                this.routeDurationMin = '~' + Math.max(2, Math.round(r.duration / 60)) + ' menit';
                            } else {
                                this.routeMap.fitBounds(L.featureGroup([nurseMarker, patientMarker]).getBounds().pad(0.3));
                            }
                        } catch (e) {
                            this.routeMap.fitBounds(L.featureGroup([nurseMarker, patientMarker]).getBounds().pad(0.3));
                        }
                        setTimeout(() => this.routeMap && this.routeMap.invalidateSize(), 150);
                    });
                },

                async handleAcceptOrder(orderId, orderNumber, cardScope) {
                    if (!confirm(`Apakah Anda yakin ingin menerima penugasan medis pesanan #${orderNumber}?`)) {
                        return;
                    }

                    cardScope.isProcessing = true;
                    this.toastMessage = '';

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const response = await fetch(`/perawat/orders/${orderId}/accept`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            }
                        });

                        const res = await response.json();

                        if (response.ok && res.status === 'success') {
                            cardScope.isProcessing = false;
                            cardScope.isDismissed = true;
                            this.toastType = 'success';
                            this.toastMessage = res.message || `Order #${orderNumber} berhasil Anda terima!`;
                            setTimeout(() => {
                                window.location.href = "{{ route('perawat.dashboard') }}";
                            }, 1200);
                        } else {
                            cardScope.isProcessing = false;
                            this.toastType = 'error';
                            this.toastMessage = res.message || 'Gagal menerima pesanan. Kemungkinan pesanan sudah diambil oleh perawat lain.';
                        }
                    } catch (error) {
                        cardScope.isProcessing = false;
                        this.toastType = 'error';
                        this.toastMessage = 'Terjadi kesalahan komunikasi dengan server.';
                    }
                },

                async handleDeclineOrder(orderId, orderNumber, cardScope) {
                    if (!confirm(`Apakah Anda ingin menolak pesanan #${orderNumber}? Pesanan ini akan dialihkan ke feed perawat lain.`)) {
                        return;
                    }

                    cardScope.isProcessing = true;

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const response = await fetch(`/perawat/orders/${orderId}/decline`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            }
                        });

                        const res = await response.json();

                        if (response.ok && res.status === 'success') {
                            cardScope.isProcessing = false;
                            cardScope.isDismissed = true;
                            this.toastType = 'success';
                            this.toastMessage = `Pesanan #${orderNumber} telah ditolak dan dialihkan.`;
                        } else {
                            cardScope.isProcessing = false;
                            this.toastType = 'error';
                            this.toastMessage = res.message || 'Gagal menolak pesanan.';
                        }
                    } catch (error) {
                        cardScope.isProcessing = false;
                        this.toastType = 'error';
                        this.toastMessage = 'Terjadi gangguan jaringan.';
                    }
                },

                async updateOrderStatus(orderId, newStatus) {
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const response = await fetch(`/perawat/orders/${orderId}/status`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({ status: newStatus })
                        });
                        const data = await response.json();
                        if (response.ok && data.status === 'success') {
                            this.toastType = 'success';
                            this.toastMessage = data.message;
                            setTimeout(() => window.location.reload(), 800);
                        } else {
                            this.toastType = 'error';
                            this.toastMessage = data.message || 'Gagal memperbarui status kunjungan.';
                        }
                    } catch (e) {
                        this.toastType = 'error';
                        this.toastMessage = 'Terjadi gangguan jaringan.';
                    }
                }
            };
        }
    </script>
</x-app-layout> 