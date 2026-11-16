<x-app-layout> <div class="py-8" x-data="nurseFeedApp()"> <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6"> <!-- Flash Status Messages --> @if (session('status'))
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-800 flex items-center justify-between shadow-xs"> <div class="flex items-center gap-2"> <span><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg></span> <span>{{ session('status') }}</span> </div> </div> @endif

            @if (session('error'))
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-xs font-semibold text-rose-800 flex items-center justify-between shadow-xs"> <div class="flex items-center gap-2"> <span><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg></span> <span>{{ session('error') }}</span> </div> </div> @endif

            <!-- Alert Toast Frontend --> <div x-show="toastMessage" x-cloak
                class="p-4 rounded-2xl flex items-center justify-between shadow-lg text-xs font-semibold transition"
                :class="toastType === 'success' ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white'"> <div class="flex items-center gap-2"> <span x-show="toastType === 'success'"><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></span><span x-show="toastType !== 'success'"><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg></span> <span x-text="toastMessage"></span> </div> <button @click="toastMessage = ''" class="text-white/80 hover:text-white px-2 py-0.5">Tutup</button> </div> <!-- Banner Header Feed --> <div class="bg-gradient-to-r from-slate-900 via-teal-950 to-emerald-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden border border-emerald-800/40"> <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div> <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10"> <div class="space-y-2 max-w-xl"> <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-xs font-semibold"> <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Real-Time Medical Job Board
                        </div> <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight"> Peluang Kunjungan Medis Tersedia
                        </h1> <p class="text-xs sm:text-sm text-slate-300 leading-relaxed"> Pesanan pasien yang membutuhkan perawatan di rumah terdekat. Anda mendapatkan <strong>85% dari tarif tindakan medis + 100% ongkos transportasi pasien</strong> yang langsung masuk ke saldo dompet saat pesanan selesai.
                        </p> </div> <div class="flex items-center gap-3"> <div class="bg-white/10 backdrop-blur-md px-4 py-3 rounded-2xl border border-white/10 text-center min-w-[120px]"> <span class="text-[10px] text-slate-300 block uppercase font-bold tracking-wider">Order Masuk</span> <span class="text-2xl font-black text-amber-300">{{ $feedOrders->count() }}</span> </div> <div class="bg-white/10 backdrop-blur-md px-4 py-3 rounded-2xl border border-white/10 text-center min-w-[120px]"> <span class="text-[10px] text-slate-300 block uppercase font-bold tracking-wider">Sedang Dikerjakan</span> <span class="text-2xl font-black text-emerald-300">{{ $activeOrders->count() }}</span> </div> </div> </div> </div> <!-- JOB BOARD GRID: ORDER MASUK TERSAJI --> <div class="space-y-4"> <div class="flex items-center justify-between"> <div> <h3 class="text-base font-extrabold text-slate-900 tracking-tight flex items-center gap-2"> <span>Daftar Order Kunjungan Siap Diambil</span> <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 font-bold"> {{ $feedOrders->count() }} Tersedia
                            </span> </h3> <p class="text-xs text-slate-500 mt-0.5">Tekan "Terima Order" untuk mengambil penugasan. Sistem mencegah persaingan dengan penguncian real-time.</p> </div> <a href="{{ route('perawat.dashboard') }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-900 flex items-center gap-1"> <span>Panel Kerja &rarr;</span> </a> </div> @if ($feedOrders->isEmpty())
                    <!-- Empty State dengan Ilustrasi Radar --> <div class="bg-white rounded-3xl border border-slate-200 p-12 text-center space-y-4 shadow-sm"> <div class="w-16 h-16 rounded-3xl bg-slate-100 text-slate-400 flex items-center justify-center text-3xl mx-auto shadow-inner relative"> <span><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" /></svg></span> <span class="absolute -top-1 -right-1 flex h-3 w-3"> <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span> <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span> </span> </div> <div class="space-y-1"> <h4 class="text-base font-bold text-slate-900">Belum Ada Order Kunjungan Baru Saat Ini</h4> <p class="text-xs text-slate-500 max-w-md mx-auto"> Sistem memantau pesanan masuk secara real-time tanpa batasan radius jarak. Pastikan Jadwal Operasional Harian (WIB) Anda aktif di dashboard.
                            </p> </div> <div class="pt-2"> <a href="{{ route('perawat.dashboard') }}" class="px-5 py-2.5 rounded-xl bg-[#064e3b] hover:bg-emerald-900 text-white text-xs font-bold shadow-xs transition inline-flex items-center gap-2"> <span>Atur Jadwal &amp; Layanan di Dashboard &rarr;</span> </a> </div> </div> @else
                    <!-- Responsive Grid of Job Cards --> <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"> @foreach ($feedOrders as $ord)
                            <div class="bg-white rounded-3xl border-2 border-slate-200 hover:border-emerald-500 transition-all shadow-sm hover:shadow-md p-6 flex flex-col justify-between space-y-5 group"
                                id="feed-card-{{ $ord->id }}"
                                x-data="{ isProcessing: false, isDismissed: false }"
                                x-show="!isDismissed"> <!-- Card Header --> <div class="space-y-3"> <div class="flex items-start justify-between gap-2 border-b border-slate-100 pb-3"> <div class="flex items-center gap-2.5"> <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-800 flex items-center justify-center text-xl border border-emerald-100"> <svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg> </div> <div> <span class="font-mono text-[10px] font-bold text-slate-400 uppercase tracking-wider block"> {{ $ord->order_number }}
                                                </span> <h4 class="font-extrabold text-sm text-slate-900 leading-snug"> {{ $ord->serviceCatalog->name ?? 'Layanan Keperawatan' }}
                                                </h4> </div> </div> <!-- Distance Badge --> <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center gap-1 whitespace-nowrap shadow-2xs"> <span><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg></span> <span>{{ $ord->calculated_distance ?? $ord->distance_km ?? 0 }} km</span> </span> </div> <!-- Schedule & Patient Location --> <div class="space-y-2 text-xs text-slate-600 bg-slate-50/80 p-3.5 rounded-2xl border border-slate-200/60"> <div class="flex items-center justify-between"> <span class="text-slate-400 text-[10px] uppercase font-bold tracking-wider">Jadwal:</span> <span class="font-bold text-slate-800"> {{ $ord->scheduled_date ? $ord->scheduled_date->format('d M Y') : '-' }} &bull; {{ str_contains($ord->scheduled_time ?? '', 'WIB') ? $ord->scheduled_time : (($ord->scheduled_time ?? '09:00') . ' WIB') }}
                                            </span> </div> <div class="flex items-start justify-between gap-2"> <span class="text-slate-400 text-[10px] uppercase font-bold tracking-wider shrink-0">Pasien:</span> <span class="font-bold text-slate-900 text-right truncate max-w-[200px]"> {{ $ord->patient_name }}
                                            </span> </div> <div class="flex items-start justify-between gap-2 border-t border-slate-200/60 pt-1.5"> <span class="text-slate-400 text-[10px] uppercase font-bold tracking-wider shrink-0">Lokasi:</span> <span class="font-medium text-slate-700 text-right text-[11px] leading-tight line-clamp-2" title="{{ $ord->address }}"> {{ $ord->address }}
                                            </span> </div> </div> <!-- Keluhan Klinis Pasien --> <div class="p-3 rounded-2xl bg-teal-50/50 border border-teal-100 text-xs space-y-1"> <div class="flex items-center gap-1.5 text-teal-900 font-extrabold text-[11px]"> <span><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg></span> <span>Keluhan &amp; Catatan Pasien:</span> </div> <p class="text-slate-700 text-[11px] leading-relaxed line-clamp-3 italic"> "{{ $ord->clinical_notes ?: 'Pasien meminta asuhan keperawatan dan pemantauan kondisi fisik umum di rumah.' }}"
                                        </p> </div> <!-- ESTIMASI KOMISI BERSIH PERAWAT --> <div class="p-3.5 rounded-2xl bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/80 flex items-center justify-between"> <div class="space-y-0.5"> <span class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider block">Estimasi Komisi Bersih:</span> <span class="text-base font-black text-emerald-700"> Rp {{ number_format($ord->estimated_nurse_earnings, 0, ',', '.') }}
                                            </span> </div> <span class="text-[9px] font-bold text-emerald-800 bg-emerald-100/80 px-2 py-0.5 rounded-full border border-emerald-300"> 85% + Transport
                                        </span> </div> </div> <!-- Action Buttons: Terima / Tolak Order --> <div class="pt-2 grid grid-cols-2 gap-2.5"> <button type="button"
                                        :disabled="isProcessing"
                                        @click="handleDeclineOrder({{ $ord->id }}, '{{ $ord->order_number }}', $data)"
                                        class="w-full py-2.5 px-3 rounded-xl bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-700 border border-slate-200 hover:border-rose-200 font-extrabold text-xs transition flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50"> <span> Tolak</span> </button> <button type="button"
                                        :disabled="isProcessing"
                                        @click="handleAcceptOrder({{ $ord->id }}, '{{ $ord->order_number }}', $data)"
                                        class="w-full py-2.5 px-3 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-black text-xs shadow-md shadow-emerald-500/20 transition transform active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50"> <template x-if="!isProcessing"> <span class="flex items-center gap-1"> <span> Terima Order</span> </span> </template> <template x-if="isProcessing"> <span class="flex items-center gap-1"> <span class="animate-spin"><svg class="w-4 h-4 inline-block shrink-0 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></span> <span>Proses...</span> </span> </template> </button> </div> </div> @endforeach
                    </div> @endif
            </div> <!-- SECTION PESANAN YANG SEDANG DIKERJAKAN --> @if ($activeOrders->isNotEmpty())
                <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-sm space-y-4"> <div class="flex items-center justify-between border-b border-slate-100 pb-3"> <div class="space-y-0.5"> <h3 class="text-sm font-extrabold text-slate-900 tracking-tight flex items-center gap-2"> <span>Tugas Kunjungan yang Sedang Anda Kerjakan</span> <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 font-bold"> {{ $activeOrders->count() }} Aktif
                                </span> </h3> <p class="text-xs text-slate-500">Pesanan yang telah Anda terima. Selesaikan asuhan keperawatan dan isi E-Report klinis.</p> </div> <a href="{{ route('perawat.dashboard') }}" class="text-xs font-bold text-emerald-700 hover:underline"> Ke Dashboard Utama &rarr;
                        </a> </div> <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"> @foreach ($activeOrders as $act)
                            @php
                                $actCheck = $act->canNursePerformAction();
                                $canActFeed = $actCheck['allowed'] ?? true;
                            @endphp
                            <div class="p-4 rounded-2xl border {{ $canActFeed ? 'border-emerald-200 bg-emerald-50/20' : 'border-amber-200 bg-amber-50/30' }} space-y-3"> <div class="flex items-center justify-between"> <span class="font-mono text-[10px] font-bold text-slate-400">{{ $act->order_number }}</span> <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-[#064e3b]"> {{ $act->status_label }}
                                    </span> </div> <div> <h5 class="font-bold text-xs text-slate-900">{{ $act->serviceCatalog->name ?? 'Layanan Medis' }}</h5> <p class="text-[11px] text-slate-500">Pasien: {{ $act->patient_name }} ({{ $act->patient_phone }})</p> <p class="text-[11px] font-semibold text-[#064e3b] mt-0.5"> Jadwal: {{ $act->scheduled_date?->format('d M Y') }} • {{ str_contains($act->scheduled_time ?? '', 'WIB') ? $act->scheduled_time : ($act->scheduled_time . ' WIB') }}
                                    </p> </div> @if (!$canActFeed)
                                    <div class="p-2.5 rounded-xl bg-amber-100 border border-amber-300 text-[10px] text-amber-950 font-semibold"> {{ $actCheck['message'] }}
                                    </div> @endif

                                <div class="pt-2 border-t border-slate-200/60 space-y-2"> @if ($act->status === 'diterima')
                                        <button type="button"
                                            @if($canActFeed) @click="updateOrderStatus({{ $act->id }}, 'dalam_perjalanan')" @else disabled @endif
                                            class="w-full py-2 px-3 rounded-xl font-bold text-xs transition flex items-center justify-center gap-1.5 {{ $canActFeed ? 'bg-indigo-600 hover:bg-indigo-700 text-white cursor-pointer' : 'bg-slate-200 text-slate-500 cursor-not-allowed opacity-75' }}"> <span> Mulai Perjalanan</span> </button> @elseif ($act->status === 'dalam_perjalanan')
                                        <button type="button"
                                            @if($canActFeed) @click="updateOrderStatus({{ $act->id }}, 'tiba')" @else disabled @endif
                                            class="w-full py-2 px-3 rounded-xl font-bold text-xs transition flex items-center justify-center gap-1.5 {{ $canActFeed ? 'bg-purple-600 hover:bg-purple-700 text-white cursor-pointer' : 'bg-slate-200 text-slate-500 cursor-not-allowed opacity-75' }}"> <span> Sampai di Lokasi</span> </button> @elseif ($act->status === 'tiba')
                                        <button type="button"
                                            @if($canActFeed) @click="updateOrderStatus({{ $act->id }}, 'tindakan_berlangsung')" @else disabled @endif
                                            class="w-full py-2 px-3 rounded-xl font-bold text-xs transition flex items-center justify-center gap-1.5 {{ $canActFeed ? 'bg-teal-600 hover:bg-teal-700 text-white cursor-pointer' : 'bg-slate-200 text-slate-500 cursor-not-allowed opacity-75' }}"> <span> Mulai Perawatan</span> </button> @endif
                                    <a href="{{ route('perawat.dashboard') }}" class="w-full py-2 px-3 rounded-xl bg-[#064e3b] hover:bg-emerald-900 text-white font-bold text-xs transition flex items-center justify-center gap-1.5"> <span> Selesai Perawatan &amp; Isi E-Report &rarr;</span> </a> </div> </div> @endforeach
                    </div> </div> @endif

        </div> </div> <!-- Alpine.js Feed Logic --> <script> function nurseFeedApp() {
            return {
                toastMessage: '',
                toastType: 'success',

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