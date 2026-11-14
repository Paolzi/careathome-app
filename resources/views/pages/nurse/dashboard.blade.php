<x-app-layout>
    <x-slot name="header">
        @php
            $userNotifications = auth()->user() ? auth()->user()->notifications()->take(10)->get() : collect();
            $unreadNotifCount = auth()->user() ? auth()->user()->unreadNotifications()->count() : 0;
        @endphp
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="font-bold text-xl text-slate-800 leading-tight">
                    {{ __('Dashboard Mitra Perawat') }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Kelola status kesiapan kerja, pelacakan GPS, dan riwayat pesanan homecare</p>
            </div>

            <div class="flex items-center gap-3">
                <!-- Dropdown Notifikasi In-App -->
                <div class="relative" x-data="{
                    notifOpen: false,
                    unreadCount: {{ $unreadNotifCount }},
                    isMarking: false,
                    toastMessage: '',
                    toastType: 'success',

                    init() {
                        window.addEventListener('notification-marked-read', (e) => {
                            if (typeof e.detail?.unread_count !== 'undefined') {
                                this.unreadCount = e.detail.unread_count;
                            } else {
                                this.unreadCount = Math.max(0, this.unreadCount - 1);
                            }
                            const itemEl = document.getElementById('notif-item-' + e.detail.id);
                            if (itemEl) {
                                itemEl.classList.remove('notif-item-unread', 'bg-emerald-50/60', 'bg-rose-50/60');
                                itemEl.classList.add('hover:bg-slate-50');
                                const dot = itemEl.querySelector('.notif-unread-dot');
                                if (dot) dot.remove();
                                const btn = itemEl.querySelector('.notif-mark-single-btn');
                                if (btn) btn.remove();
                            }
                        });
                    },

                    showToast(msg, type = 'success') {
                        this.toastMessage = msg;
                        this.toastType = type;
                        setTimeout(() => { this.toastMessage = ''; }, 3500);
                    },

                    async markAllAsRead(event) {
                        if (event) event.preventDefault();
                        if (this.isMarking || this.unreadCount <= 0) return;
                        this.isMarking = true;

                        try {
                            const token = document.querySelector('meta[name=csrf-token]')?.content 
                                || '{{ csrf_token() }}';

                            const res = await fetch('{{ route('perawat.notifications.read-all') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': token,
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                }
                            });

                            if (res.ok) {
                                const data = await res.json();
                                this.unreadCount = data.unread_count ?? 0;

                                // Ubah status seluruh item notifikasi di dropdown
                                document.querySelectorAll('.notif-item-unread').forEach(el => {
                                    el.classList.remove('notif-item-unread', 'bg-emerald-50/60', 'bg-rose-50/60');
                                    el.classList.add('hover:bg-slate-50');
                                });
                                document.querySelectorAll('.notif-unread-dot').forEach(el => el.remove());
                                document.querySelectorAll('.notif-mark-single-btn').forEach(el => el.remove());

                                // Hilangkan banner verifikasi audit jika ada di dashboard
                                document.querySelectorAll('.audit-notif-banner').forEach(el => el.remove());

                                this.showToast(data.message || 'Seluruh notifikasi telah ditandai sebagai dibaca.');
                            } else {
                                this.showToast('Gagal menandai notifikasi.', 'error');
                            }
                        } catch (err) {
                            console.error('Error markAllAsRead:', err);
                            this.showToast('Terjadi kesalahan jaringan.', 'error');
                        } finally {
                            this.isMarking = false;
                        }
                    },

                    async markSingleAsRead(event, url, notifId) {
                        if (event) event.preventDefault();
                        try {
                            const token = document.querySelector('meta[name=csrf-token]')?.content 
                                || '{{ csrf_token() }}';

                            const res = await fetch(url, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': token,
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                }
                            });

                            if (res.ok) {
                                const data = await res.json();
                                if (typeof data.unread_count !== 'undefined') {
                                    this.unreadCount = data.unread_count;
                                } else {
                                    this.unreadCount = Math.max(0, this.unreadCount - 1);
                                }

                                const itemEl = document.getElementById('notif-item-' + notifId);
                                if (itemEl) {
                                    itemEl.classList.remove('notif-item-unread', 'bg-emerald-50/60', 'bg-rose-50/60');
                                    itemEl.classList.add('hover:bg-slate-50');
                                    const dot = itemEl.querySelector('.notif-unread-dot');
                                    if (dot) dot.remove();
                                    const btn = itemEl.querySelector('.notif-mark-single-btn');
                                    if (btn) btn.remove();
                                }

                                const bannerEl = document.getElementById('notif-banner-' + notifId);
                                if (bannerEl) bannerEl.remove();

                                this.showToast(data.message || 'Notifikasi ditandai dibaca.');
                            }
                        } catch (err) {
                            console.error('Error markSingleAsRead:', err);
                        }
                    }
                }">
                    <button type="button" @click="notifOpen = !notifOpen"
                        class="relative p-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:text-emerald-700 hover:border-emerald-300 transition shadow-xs flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <template x-if="unreadCount > 0">
                            <span class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-rose-600 text-white font-extrabold text-[10px] flex items-center justify-center shadow-xs animate-pulse"
                                  x-text="unreadCount">
                            </span>
                        </template>
                    </button>

                    <!-- Toast Notifikasi Sukses Kecil -->
                    <div x-show="toastMessage" x-cloak
                        class="absolute right-0 top-12 z-50 px-3.5 py-2 rounded-xl text-white text-xs font-semibold shadow-lg transition-all flex items-center gap-1.5 whitespace-nowrap"
                        :class="toastType === 'success' ? 'bg-emerald-700' : 'bg-rose-700'">
                        <span x-text="toastType === 'success' ? '✓' : '⚠️'"></span>
                        <span x-text="toastMessage"></span>
                    </div>

                    <!-- Panel Dropdown Notifikasi -->
                    <div x-show="notifOpen" @click.away="notifOpen = false" x-cloak
                        class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-xl border border-slate-200 py-2 text-xs text-slate-800 z-50">
                        <div class="px-4 py-2.5 border-b border-slate-100 flex items-center justify-between">
                            <span class="font-bold text-slate-900 flex items-center gap-1.5">
                                <span>🔔</span> Notifikasi Verifikasi &amp; Sistem
                            </span>
                            <template x-if="unreadCount > 0">
                                <button type="button" @click="markAllAsRead($event)" :disabled="isMarking"
                                    class="text-[10px] font-bold text-emerald-700 hover:underline flex items-center gap-1 disabled:opacity-50">
                                    <span x-show="!isMarking">Tandai Semua Dibaca</span>
                                    <span x-show="isMarking" x-cloak>Menandai...</span>
                                </button>
                            </template>
                        </div>

                        <div class="max-h-72 overflow-y-auto divide-y divide-slate-100">
                            @forelse ($userNotifications as $n)
                                @php
                                    $data = $n->data;
                                    $isUnread = is_null($n->read_at);
                                    $isApproved = ($data['type'] ?? '') === 'document_approved';
                                @endphp
                                <div id="notif-item-{{ $n->id }}"
                                     class="p-3.5 space-y-1.5 transition {{ $isUnread ? 'notif-item-unread ' . ($isApproved ? 'bg-emerald-50/60' : 'bg-rose-50/60') : 'hover:bg-slate-50' }}">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-sm">{{ $isApproved ? '🎉' : '⚠️' }}</span>
                                            <span class="font-bold text-[11px] {{ $isApproved ? 'text-emerald-900' : 'text-rose-900' }}">
                                                {{ $data['title'] ?? 'Notifikasi Sistem' }}
                                            </span>
                                        </div>
                                        @if ($isUnread)
                                            <span class="notif-unread-dot w-2 h-2 rounded-full bg-emerald-500 shrink-0 mt-1"></span>
                                        @endif
                                    </div>
                                    <p class="text-[11px] text-slate-600 leading-relaxed">{{ $data['message'] ?? '-' }}</p>
                                    @if (!empty($data['rejection_reason']))
                                        <div class="p-2 rounded-lg bg-rose-100/70 border border-rose-200 text-[10px] text-rose-900">
                                            <strong>Alasan Admin:</strong> {{ $data['rejection_reason'] }}
                                        </div>
                                    @endif
                                    <div class="flex items-center justify-between pt-1 text-[10px] text-slate-400">
                                        <span>{{ $data['formatted_time'] ?? $n->created_at->diffForHumans() }}</span>
                                        @if ($isUnread)
                                            <button type="button" 
                                                @click="markSingleAsRead($event, '{{ route('perawat.notifications.read', $n->id) }}', '{{ $n->id }}')"
                                                class="notif-mark-single-btn text-emerald-700 font-bold hover:underline">
                                                Tandai Dibaca
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="p-6 text-center text-slate-400 text-xs">
                                    Belum ada notifikasi baru.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Status Legalitas Badge -->
                <span class="text-xs font-bold px-3 py-1.5 rounded-full {{ $profile->verification_status === 'verified' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs' : ($profile->verification_status === 'rejected' ? 'bg-rose-50 text-rose-800 border border-rose-200' : 'bg-amber-50 text-amber-800 border border-amber-200') }}">
                    Status Legalitas: {{ ucfirst($profile->verification_status) }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="nurseDashboardApp({{ $profile->is_online ? 'true' : 'false' }}, '{{ $profile->verification_status }}', {{ $profile->latitude ?? 'null' }}, {{ $profile->longitude ?? 'null' }})">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Banner Notifikasi Status Verifikasi (Approved / Rejected) -->
            @php
                $latestAuditNotif = auth()->user() ? auth()->user()->notifications()->whereIn('data->type', ['document_approved', 'document_rejected'])->first() : null;
            @endphp

            @if ($latestAuditNotif && is_null($latestAuditNotif->read_at))
                @php $notifData = $latestAuditNotif->data; @endphp
                @if (($notifData['type'] ?? '') === 'document_approved')
                    <div id="notif-banner-{{ $latestAuditNotif->id }}" class="audit-notif-banner p-5 rounded-2xl bg-gradient-to-r from-emerald-800 to-teal-800 text-white shadow-lg flex flex-col sm:flex-row items-center justify-between gap-4 border-2 border-emerald-500/80">
                        <div class="flex items-center gap-3">
                            <span class="text-3xl">🎉</span>
                            <div class="space-y-0.5">
                                <h4 class="font-extrabold text-sm text-white">Selamat! Berkas Legalitas STR &amp; SIP Anda Telah Diverifikasi Admin</h4>
                                <p class="text-xs text-emerald-100">Dokumen Anda telah divalidasi keabsahannya. Anda sekarang dapat mengaktifkan status "Sedang Bekerja" di bawah.</p>
                            </div>
                        </div>
                        <button type="button" 
                            onclick="dismissAuditBanner(event, '{{ route('perawat.notifications.read', $latestAuditNotif->id) }}', '{{ $latestAuditNotif->id }}')"
                            class="px-4 py-2 rounded-xl bg-white text-emerald-900 font-extrabold text-xs shadow-xs hover:bg-emerald-50 transition whitespace-nowrap">
                            Mengerti &amp; Tutup
                        </button>
                    </div>
                @elseif (($notifData['type'] ?? '') === 'document_rejected')
                    <div id="notif-banner-{{ $latestAuditNotif->id }}" class="audit-notif-banner p-5 rounded-2xl bg-gradient-to-r from-rose-800 to-red-900 text-white shadow-lg flex flex-col sm:flex-row items-center justify-between gap-4 border-2 border-rose-500/80">
                        <div class="flex items-center gap-3">
                            <span class="text-3xl">⚠️</span>
                            <div class="space-y-0.5">
                                <h4 class="font-extrabold text-sm text-white">Pemberitahuan: Berkas Dokumen Anda Perlu Perbaikan</h4>
                                <p class="text-xs text-rose-100">Alasan: <strong>{{ $notifData['rejection_reason'] ?? 'Dokumen belum lengkap' }}</strong></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('perawat.verifikasi') }}" class="px-4 py-2 rounded-xl bg-white text-rose-900 font-extrabold text-xs shadow-xs hover:bg-rose-50 transition whitespace-nowrap">
                                Perbaiki Dokumen &rarr;
                            </a>
                            <button type="button" 
                                onclick="dismissAuditBanner(event, '{{ route('perawat.notifications.read', $latestAuditNotif->id) }}', '{{ $latestAuditNotif->id }}')"
                                class="px-3 py-2 rounded-xl bg-rose-700 hover:bg-rose-600 text-white font-bold text-xs transition">
                                ✕
                            </button>
                        </div>
                    </div>
                @endif
            @endif

            <!-- Flash Status Messages -->
            @if (session('status'))
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-800 flex items-center gap-2">
                    <span>✓</span>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-xs font-semibold text-rose-800 flex items-center gap-2">
                    <span>⚠️</span>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-xs font-semibold text-rose-800 space-y-1">
                    <div class="flex items-center gap-2 font-bold">
                        <span>⚠️</span>
                        <span>Mohon periksa kembali isian formulir Anda:</span>
                    </div>
                    <ul class="list-disc list-inside pl-4 space-y-0.5">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Toast Alert Frontend -->
            <div x-show="toastMessage" x-cloak
                class="p-4 rounded-2xl flex items-center justify-between shadow-lg text-xs font-semibold transition"
                :class="toastType === 'success' ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white'">
                <div class="flex items-center gap-2">
                    <span x-text="toastType === 'success' ? '✓' : '⚠️'"></span>
                    <span x-text="toastMessage"></span>
                </div>
                <button @click="toastMessage = ''" class="text-white/80 hover:text-white px-2 py-0.5">Tutup</button>
            </div>

            <!-- 1. HERO BANNER & JADWAL OPERASIONAL HARIAN (WIB) -->
            @php
                $nowWib = now('Asia/Jakarta');
                $currentTimeHi = $nowWib->format('H:i');
                $isOperatingNow = false;
                if (isset($todaySchedule) && $todaySchedule && $todaySchedule->is_active) {
                    $st = substr($todaySchedule->start_time, 0, 5);
                    $et = substr($todaySchedule->end_time, 0, 5);
                    $isOperatingNow = ($currentTimeHi >= $st && $currentTimeHi <= $et);
                }
            @endphp
            <div class="bg-gradient-to-r from-[#064e3b] via-emerald-900 to-teal-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden border border-emerald-700/50">
                <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-[#10b981]/15 rounded-full blur-3xl pointer-events-none"></div>

                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
                    <div class="space-y-3 max-w-xl">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full {{ $isOperatingNow ? 'bg-[#10b981]/25 border-[#10b981]/40 text-emerald-200' : 'bg-amber-500/20 border-amber-400/30 text-amber-200' }} border text-xs font-bold">
                                <span class="w-2 h-2 rounded-full {{ $isOperatingNow ? 'bg-[#10b981] animate-pulse' : 'bg-amber-400' }}"></span>
                                <span>{{ $isOperatingNow ? 'JAM OPERASIONAL AKTIF (WIB)' : 'DI LUAR JAM OPERASIONAL / LIBUR' }}</span>
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 border border-white/15 text-emerald-100 text-xs font-mono font-bold">
                                <span>🕒</span>
                                <span x-text="currentWibClock">{{ $nowWib->format('H:i') }} WIB</span>
                            </span>
                        </div>

                        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                            Halo, {{ $user->name }}
                        </h1>
                        <p class="text-xs sm:text-sm text-emerald-100/90 leading-relaxed">
                            Ketersediaan Anda diatur otomatis berdasarkan <strong>Jadwal Operasional Harian (WIB)</strong> tanpa batasan radius jarak. Pasien dari lokasi manapun dapat memesan layanan Anda sesuai jadwal aktif Anda.
                        </p>

                        <div class="text-[11px] text-emerald-200/80 flex flex-wrap items-center gap-3 pt-1">
                            <span>🛰️ Titik Koordinat Rute OSRM: <strong x-text="lat && lng ? (lat.toFixed(4) + ', ' + lng.toFixed(4)) : 'Surabaya Default'"></strong></span>
                            <span>•</span>
                            <span>⏱️ Sinkronisasi GPS: <strong x-text="lastUpdatedText || '{{ $profile->location_updated_at ? $profile->location_updated_at->diffForHumans() : 'Siap' }}'"></strong></span>
                        </div>

                        <div class="pt-2 flex flex-wrap items-center gap-2.5">
                            <a href="{{ route('perawat.feed') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#10b981] hover:bg-emerald-400 text-slate-950 font-black text-xs shadow-md shadow-emerald-500/20 transition transform active:scale-95">
                                <span>📡 Buka Feed Order Masuk &rarr;</span>
                            </a>
                            <button type="button" @click="scheduleModalOpen = true" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-white text-[#064e3b] hover:bg-emerald-50 font-extrabold text-xs shadow-sm transition cursor-pointer">
                                <span>🗓️ Atur Jadwal Harian (WIB)</span>
                            </button>
                            <button type="button" @click="saveManualLocation()" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-100 font-bold text-xs border border-emerald-400/30 transition cursor-pointer">
                                <span>📍 Perbarui Titik GPS Saya</span>
                            </button>
                            <a href="{{ route('perawat.finansial') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs border border-white/20 transition">
                                <span>💳 Riwayat Finansial</span>
                            </a>
                        </div>
                    </div>

                    <!-- KARTU RINGKASAN JADWAL HARI INI -->
                    <div class="bg-white/10 backdrop-blur-md p-5 rounded-2xl border border-white/15 text-left min-w-[260px] space-y-3">
                        <div class="flex items-center justify-between border-b border-white/10 pb-2">
                            <span class="text-[11px] uppercase font-extrabold text-emerald-200 tracking-wider">Jadwal Hari Ini</span>
                            <span class="px-2 py-0.5 rounded-lg bg-[#10b981]/20 text-emerald-200 text-[10px] font-bold">
                                {{ $todaySchedule->day_name ?? 'Hari Ini' }}
                            </span>
                        </div>

                        @if (isset($todaySchedule) && $todaySchedule && $todaySchedule->is_active)
                            <div class="space-y-1">
                                <span class="text-xs text-emerald-100 block">Jam Praktik Kunjungan:</span>
                                <span class="text-xl font-black text-white font-mono block">
                                    {{ substr($todaySchedule->start_time, 0, 5) }} - {{ substr($todaySchedule->end_time, 0, 5) }} WIB
                                </span>
                                <span class="inline-flex items-center gap-1 text-[11px] text-emerald-300 font-semibold">
                                    ✓ Menerima Pesan Sekarang &amp; Terjadwal
                                </span>
                            </div>
                        @else
                            <div class="space-y-1">
                                <span class="text-base font-extrabold text-amber-300 block">Libur / Tidak Aktif</span>
                                <p class="text-[11px] text-emerald-100/80">Anda sedang mengatur hari ini sebagai libur. Pasien tetap dapat memesan untuk hari aktif lainnya.</p>
                            </div>
                        @endif

                        <button type="button" @click="scheduleModalOpen = true"
                            class="w-full py-2 px-3 rounded-xl bg-[#10b981] hover:bg-emerald-400 text-slate-950 font-extrabold text-xs transition flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                            <span>⚙️ Ubah Jadwal Senin - Minggu</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- 1.5 TIGA KARTU RINGKASAN EKSEKUTIF FINANSIAL PERAWAT -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                
                <!-- Card 1: Total Saldo Utama -->
                <div class="bg-gradient-to-br from-emerald-600 via-teal-600 to-emerald-700 text-white rounded-3xl p-6 shadow-md shadow-emerald-600/20 relative overflow-hidden flex flex-col justify-between space-y-4">
                    <div class="absolute -right-4 -bottom-4 w-32 h-32 rounded-full bg-white/10 blur-xl pointer-events-none"></div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-emerald-100 uppercase tracking-wider flex items-center gap-1.5">
                            <span class="p-1.5 rounded-xl bg-white/20 text-sm">💳</span>
                            <span>Total Saldo Utama</span>
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-white/20 text-white border border-white/20">
                            Siap Dicairkan
                        </span>
                    </div>

                    <div class="space-y-1">
                        <span class="text-3xl font-black tracking-tight block">
                            Rp {{ number_format($totalBalance, 0, ',', '.') }}
                        </span>
                        <p class="text-xs text-emerald-100">
                            Pendapatan bersih 85% tindakan + 100% ongkos transport pasien.
                        </p>
                    </div>

                    <div class="pt-2">
                        <button type="button" @click="openPayoutModal()"
                            class="w-full py-2.5 px-4 rounded-xl bg-white text-emerald-900 hover:bg-emerald-50 font-extrabold text-xs shadow-sm transition flex items-center justify-center gap-1.5 cursor-pointer">
                            <span>💸 Tarik Saldo (Payout)</span>
                        </button>
                    </div>
                </div>

                <!-- Card 2: Saldo Pending / Hold -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                            <span class="p-1.5 rounded-xl bg-amber-50 text-amber-600 text-sm border border-amber-200">⏳</span>
                            <span>Saldo Pending / Payout Hold</span>
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-800 border border-amber-200">
                            Menunggu Otorisasi
                        </span>
                    </div>

                    <div class="space-y-1">
                        <span class="text-3xl font-black text-slate-900 tracking-tight block">
                            Rp {{ number_format($pendingPayout, 0, ',', '.') }}
                        </span>
                        <p class="text-xs text-slate-500">
                            Permintaan penarikan yang sedang diverifikasi dan diproses oleh admin.
                        </p>
                    </div>

                    <div class="pt-2 text-xs font-semibold text-amber-700 flex items-center gap-1">
                        <span>🛡️</span>
                        <span>Estimasi transfer 1x24 jam kerja</span>
                    </div>
                </div>

                <!-- Card 3: Total Penarikan / Akumulasi Pendapatan -->
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                            <span class="p-1.5 rounded-xl bg-sky-50 text-sky-600 text-sm border border-sky-200">🏦</span>
                            <span>Total Penarikan Dana</span>
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-700">
                            Riwayat Transfer
                        </span>
                    </div>

                    <div class="space-y-1">
                        <span class="text-3xl font-black text-slate-900 tracking-tight block">
                            Rp {{ number_format($totalWithdrawn, 0, ',', '.') }}
                        </span>
                        <p class="text-xs text-slate-500">
                            Dana yang berhasil dicairkan ke rekening bank perawat.
                        </p>
                    </div>

                    <div class="pt-2 border-t border-slate-100 text-xs font-bold text-slate-600 flex items-center justify-between">
                        <span>Total Akumulasi Pendapatan:</span>
                        <span class="text-emerald-700 font-extrabold">Rp {{ number_format($totalEarnings, 0, ',', '.') }}</span>
                    </div>
                </div>

            </div>

            <!-- 1.6 SECTION ATUR JADWAL OPERASIONAL HARIAN (SENIN - MINGGU WIB) -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-sm space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                    <div class="space-y-1">
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 text-[#064e3b] text-[11px] font-bold border border-emerald-200">
                            <span>🗓️</span>
                            <span>Manajemen Jadwal Praktik Harian (Zona Waktu WIB)</span>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 tracking-tight">Atur Jadwal Ketersediaan Perawat (Senin – Minggu)</h3>
                        <p class="text-xs text-slate-500">
                            Tentukan jam buka praktik dan hari libur Anda. Pasien yang memilih <strong>Pesan Sekarang</strong> maupun <strong>Pesan Untuk Nanti</strong> akan divalidasi berdasarkan jadwal di bawah ini.
                        </p>
                    </div>
                    <button type="button" @click="scheduleModalOpen = true"
                        class="px-4 py-2.5 rounded-xl bg-[#064e3b] hover:bg-emerald-900 text-white font-extrabold text-xs shadow-sm transition flex items-center gap-1.5 cursor-pointer shrink-0">
                        <span>⚙️ Edit Jadwal di Modal</span>
                    </button>
                </div>

                @if (isset($schedules) && $schedules->isNotEmpty())
                    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
                        @foreach ($schedules as $sch)
                            @php
                                $isTodayCard = (int) now('Asia/Jakarta')->dayOfWeekIso === (int) $sch->day_of_week;
                            @endphp
                            <div class="p-3.5 rounded-2xl border {{ $isTodayCard ? 'border-[#10b981] bg-emerald-50/50 ring-2 ring-[#10b981]/20' : ($sch->is_active ? 'border-slate-200 bg-white' : 'border-slate-200 bg-slate-50 opacity-75') }} flex flex-col justify-between space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-extrabold text-slate-900">{{ $sch->day_name }}</span>
                                    @if ($isTodayCard)
                                        <span class="px-1.5 py-0.5 rounded bg-[#064e3b] text-white text-[9px] font-bold">Hari Ini</span>
                                    @endif
                                </div>

                                @if ($sch->is_active)
                                    <div>
                                        <span class="inline-block px-2 py-0.5 rounded-full bg-emerald-100 text-[#064e3b] text-[10px] font-bold mb-1">
                                            ✓ Aktif Praktik
                                        </span>
                                        <p class="text-xs font-mono font-bold text-slate-800">
                                            {{ substr($sch->start_time, 0, 5) }} - {{ substr($sch->end_time, 0, 5) }}
                                        </p>
                                        <span class="text-[10px] text-slate-400">WIB</span>
                                    </div>
                                @else
                                    <div>
                                        <span class="inline-block px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 text-[10px] font-bold mb-1">
                                            Libur / Off
                                        </span>
                                        <p class="text-[11px] text-slate-400">Tidak menerima kunjungan</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- 1.8 SECTION PENUGASAN MEDIS & E-REPORT KLINIS -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-sm space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                    <div class="space-y-1">
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 text-[#064e3b] text-[11px] font-bold border border-emerald-200">
                            <span>🩺</span>
                            <span>Asuhan Keperawatan Digital &amp; Validasi Waktu Kerja (WIB)</span>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 tracking-tight">Penugasan Medis &amp; E-Report Klinis Pasien</h3>
                        <p class="text-xs text-slate-500">
                            Tombol aksi perjalanan dan perawatan aktif maksimal <strong>30 menit sebelum jadwal perawatan (WIB)</strong>. Wajib melengkapi E-Report untuk penyelesaian pesanan.
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200 flex items-center gap-1.5">
                            <span>📋</span>
                            <span>{{ $ordersNeedingReport->count() }} Penugasan Aktif</span>
                        </span>
                    </div>
                </div>

                <!-- List Pesanan Menunggu E-Report -->
                @if ($ordersNeedingReport->isNotEmpty())
                    <div class="space-y-3">
                        <h4 class="text-xs uppercase font-extrabold text-slate-400 tracking-wider">Tugas Kunjungan yang Perlu Diselesaikan:</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ($ordersNeedingReport as $ord)
                                @php
                                    $actionCheck = $ord->canNursePerformAction();
                                    $canAct = $actionCheck['allowed'] ?? true;
                                @endphp
                                <div class="p-5 rounded-2xl border-2 {{ $canAct ? 'border-emerald-200 bg-emerald-50/20' : 'border-amber-200/90 bg-amber-50/30' }} space-y-4 hover:border-[#10b981] transition shadow-2xs">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-[10px] font-mono font-bold text-slate-400">{{ $ord->order_number }}</span>
                                                <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold {{ ($ord->booking_type ?? 'now') === 'now' ? 'bg-emerald-100 text-[#064e3b]' : 'bg-indigo-100 text-indigo-800' }}">
                                                    {{ ($ord->booking_type ?? 'now') === 'now' ? '⚡ Pesan Sekarang' : '🗓️ Terjadwal' }}
                                                </span>
                                            </div>
                                            <h5 class="font-extrabold text-sm text-slate-900 mt-1">{{ $ord->serviceCatalog?->name }}</h5>
                                            <p class="text-xs text-slate-600 mt-0.5">Pasien: <strong>{{ $ord->patient_name }}</strong> ({{ $ord->patient_phone }})</p>
                                        </div>
                                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-amber-100 text-amber-900 border border-amber-200">
                                            {{ $ord->status_label }}
                                        </span>
                                    </div>

                                    <div class="text-xs text-slate-600 space-y-1.5 bg-white p-3.5 rounded-xl border border-slate-200/80">
                                        <div class="flex items-center justify-between">
                                            <span class="text-slate-400">Jadwal Perawatan:</span>
                                            <span class="font-bold text-slate-900">
                                                {{ $ord->scheduled_date?->format('d M Y') }} • {{ str_contains($ord->scheduled_time ?? '', 'WIB') ? $ord->scheduled_time : ($ord->scheduled_time . ' WIB') }}
                                            </span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-slate-400">Jarak Rute Jalan (OSRM):</span>
                                            <span class="font-bold text-[#064e3b]">{{ number_format((float) $ord->distance_km, 1, ',', '.') }} km</span>
                                        </div>
                                        <div class="flex items-start justify-between gap-2">
                                            <span class="text-slate-400 shrink-0">Lokasi Pasien:</span>
                                            <span class="text-right text-slate-700 truncate max-w-[220px]" title="{{ $ord->address }}">{{ $ord->address }}</span>
                                        </div>
                                        <div class="flex items-center justify-between pt-1.5 border-t border-slate-100 text-[#064e3b] font-extrabold">
                                            <span>Pendapatan Bersih Perawat:</span>
                                            <span>Rp {{ number_format($ord->nurse_earnings, 0, ',', '.') }}</span>
                                        </div>
                                    </div>

                                    @if (!$canAct)
                                        <div class="p-3 rounded-xl bg-amber-100/90 border border-amber-300 text-[11px] text-amber-950 flex items-start gap-2">
                                            <span class="text-sm shrink-0">🔒</span>
                                            <div>
                                                <strong class="font-extrabold block">Menunggu Jadwal Perawatan (WIB)</strong>
                                                <span>{{ $actionCheck['message'] }}</span>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Aksi Tahapan Kunjungan Nakes (Validasi 30 Menit Sebelum Jam Perawatan) -->
                                    <div class="space-y-2">
                                        @if ($ord->status === 'diterima')
                                            <button type="button"
                                                @if($canAct) @click="updateOrderStatus({{ $ord->id }}, 'dalam_perjalanan')" @else disabled @endif
                                                class="w-full py-2.5 px-3 rounded-xl font-bold text-xs shadow-xs transition flex items-center justify-center gap-1.5 {{ $canAct ? 'bg-indigo-600 hover:bg-indigo-700 text-white cursor-pointer' : 'bg-slate-200 text-slate-500 cursor-not-allowed opacity-75' }}">
                                                <span>🛵</span>
                                                <span>Mulai Perjalanan</span>
                                            </button>
                                        @elseif ($ord->status === 'dalam_perjalanan')
                                            <button type="button"
                                                @if($canAct) @click="updateOrderStatus({{ $ord->id }}, 'tiba')" @else disabled @endif
                                                class="w-full py-2.5 px-3 rounded-xl font-bold text-xs shadow-xs transition flex items-center justify-center gap-1.5 {{ $canAct ? 'bg-purple-600 hover:bg-purple-700 text-white cursor-pointer' : 'bg-slate-200 text-slate-500 cursor-not-allowed opacity-75' }}">
                                                <span>📍</span>
                                                <span>Sampai di Lokasi</span>
                                            </button>
                                        @elseif ($ord->status === 'tiba')
                                            <button type="button"
                                                @if($canAct) @click="updateOrderStatus({{ $ord->id }}, 'tindakan_berlangsung')" @else disabled @endif
                                                class="w-full py-2.5 px-3 rounded-xl font-bold text-xs shadow-xs transition flex items-center justify-center gap-1.5 {{ $canAct ? 'bg-teal-600 hover:bg-teal-700 text-white cursor-pointer' : 'bg-slate-200 text-slate-500 cursor-not-allowed opacity-75' }}">
                                                <span>🩺</span>
                                                <span>Mulai Perawatan</span>
                                            </button>
                                        @endif

                                        <button type="button"
                                            @if($canAct) @click="openEReportModal(@js($ord))" @else disabled @endif
                                            class="w-full py-2.5 px-4 rounded-xl font-extrabold text-xs shadow-md transition flex items-center justify-center gap-2 {{ $canAct ? 'bg-gradient-to-r from-[#064e3b] to-[#10b981] hover:from-emerald-900 hover:to-emerald-600 text-white shadow-emerald-600/20 cursor-pointer' : 'bg-slate-200 text-slate-500 cursor-not-allowed opacity-75' }}">
                                            <span>📝</span>
                                            <span>Selesai Perawatan &amp; Isi E-Report</span>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="p-6 text-center rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2">
                        <span class="text-3xl block">🩺</span>
                        <h4 class="text-xs font-bold text-slate-700">Tidak Ada Tugas Kunjungan Tertunda</h4>
                        <p class="text-[11px] text-slate-400 max-w-md mx-auto">
                            Semua tugas kunjungan medis Anda telah dilengkapi E-Report. Pastikan jadwal harian Anda aktif agar pasien dapat memesan layanan Anda.
                        </p>
                    </div>
                @endif

                <!-- Riwayat Pesanan yang Telah Selesai dengan E-Report & Review Pasien -->
                @if ($completedOrdersWithReport->isNotEmpty())
                    <div class="pt-4 border-t border-slate-100 space-y-3">
                        <h4 class="text-xs uppercase font-extrabold text-slate-400 tracking-wider">Riwayat E-Report yang Telah Diterbitkan:</h4>
                        <div class="space-y-3 max-h-80 overflow-y-auto pr-1">
                            @foreach ($completedOrdersWithReport as $cord)
                                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-slate-900">{{ $cord->serviceCatalog?->name }}</span>
                                            <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">✓ Selesai</span>
                                        </div>
                                        <p class="text-[11px] text-slate-500">Pasien: {{ $cord->patient_name }} • Tanggal: {{ $cord->scheduled_date?->format('d M Y') }}</p>
                                        
                                        @if ($cord->review)
                                            <div class="flex items-center gap-1.5 pt-1 text-amber-500 font-bold text-[11px]">
                                                <span>⭐ {{ $cord->review->rating }}/5:</span>
                                                <span class="text-slate-600 font-normal italic">"{{ $cord->review->comment ?: 'Pelayanan sangat memuaskan!' }}"</span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-2 shrink-0">
                                        <span class="font-extrabold text-emerald-700">
                                            +Rp {{ number_format($cord->nurse_earnings, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- 2. GRID UTAMA: LAYANAN MEDIS & RIWAYAT MUTASI DOMPET -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- KOLOM KIRI: PILIHAN LAYANAN MEDIS -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Pilihan Layanan Medis yang Disediakan -->
                    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Layanan yang Anda Sediakan</h3>
                                <p class="text-[11px] text-slate-500">Pilih tindakan medis keahlian Anda.</p>
                            </div>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-800">
                                {{ count($selectedServiceIds) }} Terpilih
                            </span>
                        </div>

                        <form action="{{ route('perawat.layanan.update') }}" method="POST" class="space-y-4">
                            @csrf
                            <div class="space-y-2.5 max-h-96 overflow-y-auto pr-1">
                                @foreach ($services as $service)
                                    <label x-data="{ checked: {{ in_array($service->id, $selectedServiceIds) ? 'true' : 'false' }} }"
                                        class="p-3 rounded-2xl border transition flex items-start gap-3 cursor-pointer hover:bg-slate-50"
                                        :class="checked ? 'border-emerald-500 bg-emerald-50/40' : 'border-slate-200'">
                                        <input type="checkbox" name="service_ids[]" value="{{ $service->id }}"
                                            {{ in_array($service->id, $selectedServiceIds) ? 'checked' : '' }}
                                            @change="checked = $event.target.checked"
                                            class="rounded text-emerald-600 focus:ring-emerald-500 mt-0.5">
                                        <div class="space-y-0.5 text-xs">
                                            <div class="flex items-center gap-1.5">
                                                <span>{{ $service->image_emoji }}</span>
                                                <span class="font-bold text-slate-900">{{ $service->name }}</span>
                                            </div>
                                            <p class="text-[11px] text-slate-500">{{ $service->category }} • {{ $service->estimated_duration }}</p>
                                            <span class="text-[10px] font-semibold text-emerald-700 block">Tarif: Rp {{ number_format($service->price, 0, ',', '.') }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            <button type="submit" class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow transition">
                                Simpan Pilihan Layanan
                            </button>
                        </form>
                    </div>

                    <!-- Riwayat Permintaan Payout Mini -->
                    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-3">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                            <h4 class="text-xs uppercase font-bold text-slate-400 tracking-wider">Status Pencairan</h4>
                            <button type="button" @click="openPayoutModal()" class="text-[11px] font-bold text-emerald-700 hover:underline">
                                + Tarik Saldo
                            </button>
                        </div>
                        
                        @if ($payoutRequests->isEmpty())
                            <p class="text-xs text-slate-400 italic">Belum ada riwayat penarikan dana.</p>
                        @else
                            <div class="space-y-2.5 max-h-60 overflow-y-auto pr-1">
                                @foreach ($payoutRequests as $pr)
                                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/80 text-xs space-y-1">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-slate-900">Rp {{ number_format($pr->amount, 0, ',', '.') }}</span>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold 
                                                {{ $pr->status === 'transferred' ? 'bg-emerald-100 text-emerald-800' : ($pr->status === 'rejected' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                                {{ ucfirst($pr->status) }}
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-slate-500">{{ $pr->bank_name }} - {{ $pr->account_number }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- KOLOM KANAN: TABEL MUTASI FINANSIAL & DOMPET -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Riwayat Mutasi Finansial Dompet -->
                    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Tabel Mutasi Finansial &amp; Riwayat Bagi Hasil</h3>
                                <p class="text-xs text-slate-500">Transparansi potongan platform fee 15% dan pendapatan bersih nakes 85% + transport.</p>
                            </div>
                            <span class="text-xs text-slate-400">15 Transaksi Terakhir</span>
                        </div>

                        @if ($recentTransactions->isEmpty())
                            <p class="text-xs text-slate-400 italic py-8 text-center">Belum ada transaksi pendapatan atau penarikan saldo tercatat.</p>
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs text-slate-700">
                                    <thead class="text-[10px] uppercase font-bold text-slate-400 bg-slate-50 border-b border-slate-100">
                                        <tr>
                                            <th class="py-2.5 px-3">Waktu</th>
                                            <th class="py-2.5 px-3">Keterangan</th>
                                            <th class="py-2.5 px-3">Platform Fee (15%)</th>
                                            <th class="py-2.5 px-3">Net Nakes</th>
                                            <th class="py-2.5 px-3 text-right">Perubahan Saldo</th>
                                            <th class="py-2.5 px-3 text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 font-medium">
                                        @foreach ($recentTransactions as $tx)
                                            <tr class="hover:bg-slate-50/80 transition">
                                                <td class="py-3 px-3 whitespace-nowrap text-[11px] text-slate-500 font-mono">
                                                    {{ $tx->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                                                </td>
                                                <td class="py-3 px-3">
                                                    <span class="font-bold text-slate-800 block">{{ $tx->description }}</span>
                                                    <span class="text-[10px] text-slate-400 font-mono">Tipe: {{ $tx->type }}</span>
                                                </td>
                                                <td class="py-3 px-3 text-amber-700 font-semibold whitespace-nowrap">
                                                    {{ $tx->platform_fee > 0 ? 'Rp ' . number_format($tx->platform_fee, 0, ',', '.') : '-' }}
                                                </td>
                                                <td class="py-3 px-3 text-emerald-800 font-semibold whitespace-nowrap">
                                                    {{ $tx->net_amount > 0 ? 'Rp ' . number_format($tx->net_amount, 0, ',', '.') : '-' }}
                                                </td>
                                                <td class="py-3 px-3 text-right font-extrabold whitespace-nowrap {{ $tx->amount > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                                    {{ $tx->amount > 0 ? '+' : '' }}Rp {{ number_format($tx->amount, 0, ',', '.') }}
                                                </td>
                                                <td class="py-3 px-3 text-center">
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $tx->status === 'completed' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }}">
                                                        {{ ucfirst($tx->status ?: 'Selesai') }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                </div>

            </div>

        </div>

        <!-- ============================================================= -->
        <!-- MODAL FORMULIR E-REPORT KLINIS PERAWAT                        -->
        <!-- ============================================================= -->
        <div x-show="ereportModalOpen" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-6"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">

            <div @click.away="ereportModalOpen = false"
                class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl border border-slate-200 overflow-hidden text-slate-800 max-h-[92vh] flex flex-col"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">

                <!-- Header Modal -->
                <div class="px-6 py-5 bg-gradient-to-r from-emerald-800 via-teal-800 to-slate-900 text-white flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center text-xl">
                            🩺
                        </div>
                        <div>
                            <span class="text-[10px] uppercase tracking-wider font-extrabold text-emerald-300 block">Formulir Rekam Medis Digital</span>
                            <h3 class="text-base font-extrabold leading-tight">Input E-Report Asuhan Keperawatan</h3>
                        </div>
                    </div>
                    <button type="button" @click="ereportModalOpen = false"
                        class="p-2 rounded-xl bg-white/10 hover:bg-white/20 text-white transition text-xs">
                        ✕
                    </button>
                </div>

                <!-- Form Content (Scrollable) -->
                <form :action="ereportFormAction" method="POST" enctype="multipart/form-data" class="overflow-y-auto grow p-6 space-y-6">
                    @csrf

                    <!-- CARD 1: RINGKASAN PASIEN & PESANAN -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs uppercase font-extrabold text-slate-400 tracking-wider">Target Tindakan Medis</span>
                            <span class="text-xs font-mono font-bold text-emerald-800" x-text="activeOrderForReport?.order_number"></span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs pt-1">
                            <div>
                                <span class="text-slate-400 block text-[11px]">Pasien:</span>
                                <strong class="text-slate-900 text-sm" x-text="activeOrderForReport?.patient_name"></strong>
                                <span class="text-slate-500 block text-[11px]" x-text="'Telp: ' + (activeOrderForReport?.patient_phone || '-')"></span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[11px]">Layanan:</span>
                                <strong class="text-emerald-700" x-text="activeOrderForReport?.service_catalog?.name"></strong>
                                <span class="text-slate-500 block text-[11px]" x-text="activeOrderForReport?.scheduled_date + ' • ' + (activeOrderForReport?.scheduled_time || '')"></span>
                            </div>
                        </div>
                    </div>

                    <!-- CARD 2: TANDA-TANDA VITAL (TTV) -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                            <div class="flex items-center gap-2">
                                <span class="text-emerald-600 font-bold">①</span>
                                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Pengukuran Tanda-Tanda Vital (TTV) Pasien</h4>
                            </div>
                            <span class="text-[10px] text-rose-500 font-semibold">*Wajib Diisi</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Tekanan Darah Sistolik -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Tekanan Darah Sistolik</label>
                                <div class="relative rounded-xl shadow-xs">
                                    <input type="number" name="systolic" min="50" max="300" required value="120" placeholder="Mis. 120"
                                        class="w-full text-xs rounded-xl border-slate-200 pr-16 focus:border-emerald-500 focus:ring-emerald-500">
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400 text-xs font-bold">
                                        mmHg
                                    </span>
                                </div>
                            </div>

                            <!-- Tekanan Darah Diastolik -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Tekanan Darah Diastolik</label>
                                <div class="relative rounded-xl shadow-xs">
                                    <input type="number" name="diastolic" min="30" max="200" required value="80" placeholder="Mis. 80"
                                        class="w-full text-xs rounded-xl border-slate-200 pr-16 focus:border-emerald-500 focus:ring-emerald-500">
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400 text-xs font-bold">
                                        mmHg
                                    </span>
                                </div>
                            </div>

                            <!-- Suhu Tubuh -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Suhu Tubuh</label>
                                <div class="relative rounded-xl shadow-xs">
                                    <input type="number" step="0.1" name="body_temp" min="30" max="45" required value="36.5" placeholder="Mis. 36.5"
                                        class="w-full text-xs rounded-xl border-slate-200 pr-12 focus:border-emerald-500 focus:ring-emerald-500">
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400 text-xs font-bold">
                                        °C
                                    </span>
                                </div>
                            </div>

                            <!-- Denyut Nadi -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Denyut Nadi</label>
                                <div class="relative rounded-xl shadow-xs">
                                    <input type="number" name="pulse_rate" min="30" max="250" required value="82" placeholder="Mis. 80"
                                        class="w-full text-xs rounded-xl border-slate-200 pr-14 focus:border-emerald-500 focus:ring-emerald-500">
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400 text-xs font-bold">
                                        bpm
                                    </span>
                                </div>
                            </div>

                            <!-- Saturasi Oksigen SpO2 -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Saturasi Oksigen (SpO2)</label>
                                <div class="relative rounded-xl shadow-xs">
                                    <input type="number" name="spo2" min="50" max="100" required value="98" placeholder="Mis. 98"
                                        class="w-full text-xs rounded-xl border-slate-200 pr-12 focus:border-emerald-500 focus:ring-emerald-500">
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400 text-xs font-bold">
                                        %
                                    </span>
                                </div>
                            </div>

                            <!-- Gula Darah Sewaktu (GDS) -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">
                                    Gula Darah Sewaktu (GDS) <span class="text-[10px] text-slate-400 font-normal">(Opsional)</span>
                                </label>
                                <div class="relative rounded-xl shadow-xs">
                                    <input type="number" name="gds" min="20" max="800" placeholder="Mis. 110"
                                        class="w-full text-xs rounded-xl border-slate-200 pr-16 focus:border-emerald-500 focus:ring-emerald-500">
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400 text-xs font-bold">
                                        mg/dL
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CARD 3: CATATAN ASUHAN & EDUKASI KELUARGA -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 border-b border-slate-100 pb-2">
                            <span class="text-emerald-600 font-bold">②</span>
                            <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Catatan Asuhan Keperawatan &amp; Edukasi Pasien</h4>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Ringkasan Catatan Klinis / Asuhan Keperawatan (SOAP) <span class="text-rose-500">*</span>
                            </label>
                            <textarea name="clinical_notes" rows="3" required
                                placeholder="Tuliskan keluhan pasien, tindakan medis yang telah diberikan, respons pasien, serta kondisi terkini..."
                                class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Instruksi &amp; Edukasi Keluarga Pasien <span class="text-[10px] text-slate-400 font-normal">(Opsional)</span>
                            </label>
                            <textarea name="family_education" rows="2"
                                placeholder="Edukasi mengenai jadwal konsumsi obat, perawatan luka mandiri, pantangan makanan, atau jadwal kontrol lanjutan..."
                                class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                        </div>
                    </div>

                    <!-- CARD 4: UPLOAD DOKUMENTASI FOTO TINDAKAN -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                            <div class="flex items-center gap-2">
                                <span class="text-emerald-600 font-bold">③</span>
                                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Dokumentasi Foto Tindakan / Kondisi Luka</h4>
                            </div>
                            <span class="text-[10px] text-slate-400">Maks. 5 Foto (JPEG/PNG)</span>
                        </div>

                        <div class="p-4 rounded-2xl border-2 border-dashed border-slate-200 hover:border-emerald-500 bg-slate-50/60 transition text-center space-y-2">
                            <input type="file" id="ereportPhotoInput" name="photos[]" multiple accept="image/*" @change="handlePhotoUpload($event)"
                                class="hidden">
                            <label for="ereportPhotoInput" class="cursor-pointer inline-flex flex-col items-center gap-1.5 text-xs text-slate-600 hover:text-emerald-700 font-semibold">
                                <span class="text-2xl">📷</span>
                                <span>Klik di sini untuk memilih foto dokumentasi tindakan</span>
                                <span class="text-[10px] text-slate-400 font-normal">Ukuran maks. 5MB per file</span>
                            </label>
                        </div>

                        <!-- Preview Foto yang Dipilih -->
                        <div x-show="photoPreviews.length > 0" class="grid grid-cols-2 sm:grid-cols-3 gap-3 pt-2">
                            <template x-for="(preview, idx) in photoPreviews" :key="idx">
                                <div class="relative rounded-xl border border-slate-200 overflow-hidden bg-slate-100 p-1 space-y-1">
                                    <img :src="preview.url" class="w-full h-24 object-cover rounded-lg">
                                    <input type="text" :name="'captions[' + idx + ']'" placeholder="Keterangan foto"
                                        class="w-full text-[10px] py-1 px-1.5 rounded border border-slate-200">
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- CARD 5: INFORMASI SETTLEMENT DANA OTOMATIS -->
                    <div class="p-4 rounded-2xl bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200 text-xs text-emerald-950 space-y-2">
                        <div class="flex items-center gap-1.5 font-bold">
                            <span>🛡️</span>
                            <span>Automated Settlement CareAtHome</span>
                        </div>
                        <p class="text-[11px] text-emerald-800 leading-relaxed">
                            Setelah tombol di bawah ditekan, pesanan akan secara otomatis dinyatakan <strong>Selesai</strong> dan dana bagi hasil (85% biaya tindakan + 100% ongkos transport) akan langsung ditransfer ke Dompet Anda secara otomatis.
                        </p>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" @click="ereportModalOpen = false"
                            class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 text-xs font-bold transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 hover:from-emerald-700 hover:to-teal-700 text-white font-extrabold text-xs shadow-md shadow-emerald-600/30 transition flex items-center gap-2 cursor-pointer">
                            <span>✓</span>
                            <span>Simpan E-Report &amp; Selesaikan Layanan</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- MODAL ATUR JADWAL OPERASIONAL HARIAN (SENIN - MINGGU WIB)     -->
        <!-- ============================================================= -->
        <div x-show="scheduleModalOpen" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-6"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">

            <div @click.away="scheduleModalOpen = false"
                class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl border border-slate-200 overflow-hidden text-slate-800 max-h-[92vh] flex flex-col"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">

                <div class="px-6 py-5 bg-gradient-to-r from-[#064e3b] via-emerald-900 to-teal-950 text-white flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center text-xl">
                            🗓️
                        </div>
                        <div>
                            <span class="text-[10px] uppercase tracking-wider font-extrabold text-emerald-300 block">Manajemen Ketersediaan Harian (WIB)</span>
                            <h3 class="text-base font-extrabold leading-tight">Atur Jadwal Praktik Perawat (Senin – Minggu)</h3>
                        </div>
                    </div>
                    <button type="button" @click="scheduleModalOpen = false" class="p-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs">
                        ✕
                    </button>
                </div>

                <form action="{{ route('perawat.jadwal.update') }}" method="POST" class="overflow-y-auto grow p-6 space-y-4">
                    @csrf

                    <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs text-[#064e3b] leading-relaxed">
                        <strong>💡 Cara Kerja Jadwal (WIB):</strong> Pasien dapat memesan Anda untuk <strong>Pesan Sekarang</strong> maupun <strong>Pesan Untuk Nanti</strong> apabila waktu pemesanan berada di dalam rentang jam buka praktik yang Anda aktifkan di bawah ini.
                    </div>

                    <div class="space-y-2.5">
                        @if (isset($schedules))
                            @foreach ($schedules as $idx => $sch)
                                <div x-data="{ activeDay: {{ $sch->is_active ? 'true' : 'false' }} }"
                                    class="p-3.5 rounded-2xl border transition flex flex-col sm:flex-row sm:items-center justify-between gap-3"
                                    :class="activeDay ? 'border-emerald-300 bg-emerald-50/30' : 'border-slate-200 bg-slate-50'">
                                    
                                    <div class="flex items-center gap-3">
                                        <input type="hidden" name="schedules[{{ $idx }}][day_of_week]" value="{{ $sch->day_of_week }}">
                                        <label class="inline-flex items-center gap-2.5 cursor-pointer">
                                            <input type="checkbox" name="schedules[{{ $idx }}][is_active]" value="1"
                                                {{ $sch->is_active ? 'checked' : '' }}
                                                @change="activeDay = $event.target.checked"
                                                class="w-4 h-4 rounded text-[#064e3b] focus:ring-[#10b981]">
                                            <span class="font-extrabold text-xs text-slate-900 w-20">{{ $sch->day_name }}</span>
                                        </label>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                            :class="activeDay ? 'bg-emerald-100 text-[#064e3b]' : 'bg-rose-100 text-rose-800'"
                                            x-text="activeDay ? 'Buka Praktik' : 'Libur / Tidak Aktif'">
                                        </span>
                                    </div>

                                    <div class="flex items-center gap-2" :class="!activeDay ? 'opacity-40 pointer-events-none' : ''">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[10px] font-bold text-slate-500">Mulai:</span>
                                            <input type="time" name="schedules[{{ $idx }}][start_time]"
                                                value="{{ substr($sch->start_time, 0, 5) }}" required
                                                class="text-xs font-mono rounded-xl border-slate-200 py-1.5 px-2.5 focus:border-[#10b981] focus:ring-[#10b981]">
                                        </div>
                                        <span class="text-slate-400 font-bold">-</span>
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[10px] font-bold text-slate-500">Selesai:</span>
                                            <input type="time" name="schedules[{{ $idx }}][end_time]"
                                                value="{{ substr($sch->end_time, 0, 5) }}" required
                                                class="text-xs font-mono rounded-xl border-slate-200 py-1.5 px-2.5 focus:border-[#10b981] focus:ring-[#10b981]">
                                        </div>
                                        <span class="text-[10px] font-bold text-slate-500">WIB</span>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" @click="scheduleModalOpen = false"
                            class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-100 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-[#064e3b] hover:bg-emerald-900 text-white text-xs font-extrabold shadow transition cursor-pointer">
                            ✓ Simpan Jadwal Operasional (WIB)
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- MODAL FORMULIR TARIK SALDO / PAYOUT INSTAN (TANPA REKENING)   -->
        <!-- ============================================================= -->
        <div x-show="payoutModalOpen" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-6"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">

            <div @click.away="payoutModalOpen = false"
                class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden text-slate-800"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">

                <div class="px-6 py-5 bg-gradient-to-r from-[#064e3b] to-emerald-950 text-white flex items-center justify-between">
                    <div>
                        <span class="text-[10px] uppercase tracking-wider font-extrabold text-emerald-300 block">Pencairan Saldo Instan Perawat</span>
                        <h3 class="text-base font-extrabold leading-tight">Tarik Saldo Dompet</h3>
                    </div>
                    <button type="button" @click="payoutModalOpen = false" class="p-1.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs">
                        ✕
                    </button>
                </div>

                <form action="{{ route('perawat.payout.request') }}" method="POST" class="p-6 space-y-4">
                    @csrf

                    <!-- Info Saldo Tersedia -->
                    <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200 flex items-center justify-between">
                        <span class="text-xs font-semibold text-[#064e3b]">Saldo Aktif Tersedia:</span>
                        <strong class="text-lg font-black text-[#064e3b]">Rp {{ number_format($totalBalance, 0, ',', '.') }}</strong>
                    </div>

                    <!-- Pilihan Nominal Cepat (Quick Chips) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilihan Nominal Cepat</label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            <button type="button" @click="setPayoutAmount(50000)"
                                :class="payoutAmount === 50000 ? 'bg-[#064e3b] text-white border-[#064e3b] font-bold' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-emerald-50'"
                                class="py-2 px-2 rounded-xl text-xs border text-center transition cursor-pointer">
                                Rp 50rb
                            </button>
                            <button type="button" @click="setPayoutAmount(100000)"
                                :class="payoutAmount === 100000 ? 'bg-[#064e3b] text-white border-[#064e3b] font-bold' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-emerald-50'"
                                class="py-2 px-2 rounded-xl text-xs border text-center transition cursor-pointer">
                                Rp 100rb
                            </button>
                            <button type="button" @click="setPayoutAmount(250000)"
                                :class="payoutAmount === 250000 ? 'bg-[#064e3b] text-white border-[#064e3b] font-bold' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-emerald-50'"
                                class="py-2 px-2 rounded-xl text-xs border text-center transition cursor-pointer">
                                Rp 250rb
                            </button>
                            <button type="button" @click="setPayoutAmount({{ (int) $totalBalance }})"
                                :class="payoutAmount === {{ (int) $totalBalance }} ? 'bg-[#064e3b] text-white border-[#064e3b] font-bold' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-emerald-50'"
                                class="py-2 px-2 rounded-xl text-xs border text-center transition cursor-pointer">
                                Semua
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Masukkan Nominal Penarikan (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" name="amount" x-model.number="payoutAmount" min="10000" max="{{ max(10000, (int) $totalBalance) }}" required
                            class="w-full text-sm font-bold rounded-xl border-slate-200 focus:border-[#10b981] focus:ring-[#10b981]">
                        <span class="text-[10px] text-slate-500 mt-1 block">
                            Minimal Rp 10.000 &amp; maksimal sesuai saldo aktif (Rp {{ number_format($totalBalance, 0, ',', '.') }}).
                        </span>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-[11px] text-slate-600 leading-relaxed">
                        ⚡ <strong>Pencairan Otomatis Satu Klik:</strong> Anda cukup menginputkan nominal penarikan tanpa perlu mengisi rekening manual. Saldo aktif akan langsung terpotong otomatis dan tercatat di riwayat transaksi serta panel Admin.
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-2">
                        <button type="button" @click="payoutModalOpen = false"
                            class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-100 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-[#10b981] hover:bg-emerald-600 text-slate-950 hover:text-white text-xs font-extrabold shadow transition cursor-pointer">
                            💸 Tarik Saldo Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Alpine App Logic for Nurse Dashboard -->
    <script>
        function nurseDashboardApp(initialOnline, verificationStatus, initialLat, initialLng) {
            return {
                isOnline: initialOnline,
                verificationStatus: verificationStatus,
                lat: initialLat,
                lng: initialLng,
                togglingStatus: false,
                watchId: null,
                lastUpdatedText: '',
                lastLocationSentAt: 0,
                toastMessage: '',
                toastType: 'success',
                currentWibClock: '',

                // Modals State
                scheduleModalOpen: false,
                ereportModalOpen: false,
                activeOrderForReport: null,
                ereportFormAction: '',
                photoPreviews: [],
                payoutModalOpen: false,
                payoutAmount: {{ $totalBalance >= 100000 ? 100000 : max(10000, (int) $totalBalance) }},

                openEReportModal(order) {
                    this.activeOrderForReport = order;
                    this.ereportFormAction = '/perawat/orders/' + order.id + '/ereport';
                    this.photoPreviews = [];
                    this.ereportModalOpen = true;
                },

                async updateOrderStatus(orderId, newStatus) {
                    if (!confirm(`Konfirmasi perbarui tahapan kunjungan medis ini?`)) {
                        return;
                    }

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const response = await fetch(`/perawat/orders/${orderId}/status`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({ status: newStatus }),
                        });
                        const data = await response.json();
                        if (response.ok && data.status === 'success') {
                            alert(data.message);
                            window.location.reload();
                        } else {
                            alert(data.message || 'Gagal memperbarui status kunjungan.');
                        }
                    } catch (e) {
                        alert('Terjadi kesalahan jaringan.');
                    }
                },

                handlePhotoUpload(event) {
                    const files = event.target.files;
                    this.photoPreviews = [];
                    if (files && files.length > 0) {
                        for (let i = 0; i < Math.min(files.length, 5); i++) {
                            const file = files[i];
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                this.photoPreviews.push({
                                    url: e.target.result,
                                    name: file.name
                                });
                            };
                            reader.readAsDataURL(file);
                        }
                    }
                },

                openPayoutModal() {
                    this.payoutModalOpen = true;
                },

                setPayoutAmount(amount) {
                    this.payoutAmount = amount;
                },

                init() {
                    this.updateClock();
                    setInterval(() => this.updateClock(), 1000);
                    this.startLocationWatch();
                },

                updateClock() {
                    const fmt = new Intl.DateTimeFormat('id-ID', {
                        timeZone: 'Asia/Jakarta',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: false
                    });
                    this.currentWibClock = fmt.format(new Date()).replace(/\./g, ':') + ' WIB';
                },

                showToast(msg, type = 'success') {
                    this.toastMessage = msg;
                    this.toastType = type;
                    setTimeout(() => { this.toastMessage = ''; }, 4000);
                },

                async saveManualLocation() {
                    const fallbackLat = this.lat || -7.3144;
                    const fallbackLng = this.lng || 112.7304;

                    if (!navigator.geolocation) {
                        await this.sendLocationUpdate(fallbackLat, fallbackLng, true);
                        return;
                    }

                    navigator.geolocation.getCurrentPosition(
                        async (pos) => {
                            this.lat = parseFloat(pos.coords.latitude.toFixed(6));
                            this.lng = parseFloat(pos.coords.longitude.toFixed(6));
                            await this.sendLocationUpdate(this.lat, this.lng, true);
                        },
                        async () => {
                            this.lat = fallbackLat;
                            this.lng = fallbackLng;
                            await this.sendLocationUpdate(this.lat, this.lng, true);
                        },
                        { enableHighAccuracy: true, timeout: 6000, maximumAge: 0 }
                    );
                },

                async handleToggleOnline() {
                    if (this.togglingStatus) return;

                    const targetState = !this.isOnline;
                    this.togglingStatus = true;

                    if (targetState) {
                        if (!navigator.geolocation) {
                            const fallbackLat = this.lat || -7.3144;
                            const fallbackLng = this.lng || 112.7304;
                            await this.sendToggleRequest(true, fallbackLat, fallbackLng);
                            this.togglingStatus = false;
                            return;
                        }

                        navigator.geolocation.getCurrentPosition(
                            async (pos) => {
                                this.lat = parseFloat(pos.coords.latitude.toFixed(6));
                                this.lng = parseFloat(pos.coords.longitude.toFixed(6));

                                await this.sendToggleRequest(true, this.lat, this.lng);
                                this.startLocationWatch();
                                this.togglingStatus = false;
                            },
                            async (err) => {
                                console.warn('Izin lokasi gagal/ditolak, menggunakan koordinat tersimpan/default:', err);
                                const fallbackLat = this.lat || -7.3144;
                                const fallbackLng = this.lng || 112.7304;
                                this.lat = fallbackLat;
                                this.lng = fallbackLng;
                                await this.sendToggleRequest(true, fallbackLat, fallbackLng);
                                this.togglingStatus = false;
                            },
                            { enableHighAccuracy: true, timeout: 6000, maximumAge: 0 }
                        );
                    } else {
                        this.stopLocationWatch();
                        await this.sendToggleRequest(false);
                        this.togglingStatus = false;
                    }
                },

                async sendToggleRequest(isOnline, lat = null, lng = null) {
                    try {
                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const res = await fetch('/perawat/toggle-online', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                is_online: isOnline,
                                latitude: lat,
                                longitude: lng
                            })
                        });

                        const data = await res.json();
                        if (res.ok && data.status === 'success') {
                            this.isOnline = isOnline;
                            if (data.data?.latitude && data.data?.longitude) {
                                this.lat = parseFloat(data.data.latitude);
                                this.lng = parseFloat(data.data.longitude);
                            }
                            this.showToast(data.message, 'success');
                            this.lastUpdatedText = 'Baru saja';
                        } else {
                            this.isOnline = !isOnline;
                            this.showToast(data.message || 'Gagal mengubah status kerja.', 'error');
                        }
                    } catch (e) {
                        this.isOnline = !isOnline;
                        this.showToast('Terjadi kesalahan jaringan.', 'error');
                    }
                },

                startLocationWatch() {
                    if (!navigator.geolocation) return;

                    this.stopLocationWatch();

                    this.watchId = navigator.geolocation.watchPosition(
                        (pos) => {
                            this.lat = pos.coords.latitude;
                            this.lng = pos.coords.longitude;

                            const now = Date.now();
                            // Throttle pengiriman: maksimal sekali tiap 30 detik
                            if (now - this.lastLocationSentAt >= 30000) {
                                this.lastLocationSentAt = now;
                                this.sendLocationUpdate(this.lat, this.lng);
                            }
                        },
                        (err) => {
                            console.warn('Geolocation error:', err);
                            if (this.isOnline) {
                                this.showToast('Koneksi GPS terputus. Pastikan izin lokasi aktif.', 'error');
                            }
                        },
                        { enableHighAccuracy: true, maximumAge: 30000 }
                    );
                },

                stopLocationWatch() {
                    if (this.watchId !== null) {
                        navigator.geolocation.clearWatch(this.watchId);
                        this.watchId = null;
                    }
                },

                async sendLocationUpdate(lat, lng, showAlert = false) {
                    try {
                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const res = await fetch('/perawat/lokasi', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ latitude: lat, longitude: lng })
                        });

                        const data = await res.json();
                        if (res.ok && data.status === 'success') {
                            this.lastUpdatedText = 'Baru saja';
                            if (showAlert) {
                                this.showToast(data.message || 'Lokasi kerja berhasil disimpan!', 'success');
                            }
                        } else if (showAlert) {
                            this.showToast(data.message || 'Gagal menyimpan lokasi.', 'error');
                        }
                    } catch (e) {
                        console.warn('Gagal sinkron lokasi:', e);
                        if (showAlert) {
                            this.showToast('Terjadi kesalahan jaringan saat menyimpan lokasi.', 'error');
                        }
                    }
                }
            };
        }

        async function dismissAuditBanner(e, url, id) {
            if (e) e.preventDefault();
            const banner = document.getElementById('notif-banner-' + id);
            if (banner) banner.remove();
            try {
                const token = document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}';
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                if (res.ok) {
                    const data = await res.json();
                    window.dispatchEvent(new CustomEvent('notification-marked-read', { 
                        detail: { 
                            id: id, 
                            unread_count: data.unread_count 
                        } 
                    }));
                }
            } catch(err) {
                console.error('Error dismissing audit banner:', err);
            }
        }
    </script>
</x-app-layout>
