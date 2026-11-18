@php
    $user = auth()->user();
    $isAdmin = $user && $user->hasRole('admin');
    $isNurse = $user && $user->hasRole('perawat');
    $homeRoute = $isAdmin ? route('admin.dashboard') : ($isNurse ? route('perawat.dashboard') : route('dashboard'));
    $initials = strtoupper(substr($user?->name ?? 'CA', 0, 2));

    $nurseStatus = null;
    $nurseSuspendedTemp = false;
    $userNotifications = collect();
    $unreadNotifCount = 0;

    if ($user) {
        try {
            \App\Services\NurseNoShowPenaltyService::processOverdueOrders();
            \App\Services\OrderReminderService::dispatchDueReminders();
        } catch (\Throwable $e) {
            // Abaikan error agar navbar tetap aman dirender
        }
        $userNotifications = $user->notifications()->take(15)->get();
        $unreadNotifCount = $user->unreadNotifications()->count();
        if ($isNurse) {
            $np = \App\Models\NurseProfile::where('user_id', $user->id)->first();
            $nurseStatus = $np?->verification_status;
            $nurseSuspendedTemp = $np ? $np->isTemporarilySuspended() : false;
        }
    }
@endphp

<header x-data="{ mobileNavOpen: false, profileDropdownOpen: false }"
    class="sticky top-0 z-50 w-full bg-[#F9F4EA]/95 backdrop-blur-md border-b border-[#78956A] shadow-xs">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20 gap-3">

            <!-- Left: Brand Logo & Desktop Navigation -->
            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ $homeRoute }}" class="flex items-center gap-2.5 shrink-0 group">
                    <x-application-logo :dark="true" />
                </a>

                <div class="hidden lg:block h-7 w-px bg-[#78956A]/40 mx-1 shrink-0"></div>

                <!-- Desktop Navigation Links (No horizontal scroll, all items fit cleanly) -->
                <nav class="hidden lg:flex items-center gap-1">
                    @if ($isAdmin)
                        <a href="{{ route('admin.dashboard') }}"
                            class="cah-nav-link px-2.5 py-2 rounded-xl text-[13px] font-semibold whitespace-nowrap {{ request()->routeIs('admin.dashboard') ? 'cah-nav-link-active' : '' }}">
                            Dashboard Finansial
                        </a>
                        <a href="{{ route('admin.escrow.index') }}"
                            class="cah-nav-link px-2.5 py-2 rounded-xl text-[13px] font-semibold whitespace-nowrap {{ request()->routeIs('admin.escrow*') ? 'cah-nav-link-active' : '' }}">
                            Monitoring Mutasi
                        </a>
                        <a href="{{ route('admin.nurses.index') }}"
                            class="cah-nav-link px-2.5 py-2 rounded-xl text-[13px] font-semibold whitespace-nowrap {{ request()->routeIs('admin.nurses*') ? 'cah-nav-link-active' : '' }}">
                            Verifikasi Nakes
                        </a>
                        <a href="{{ route('admin.services.index') }}"
                            class="cah-nav-link px-2.5 py-2 rounded-xl text-[13px] font-semibold whitespace-nowrap {{ request()->routeIs('admin.services*') ? 'cah-nav-link-active' : '' }}">
                            Katalog &amp; Tarif
                        </a>
                        <a href="{{ route('admin.users.index') }}"
                            class="cah-nav-link px-2.5 py-2 rounded-xl text-[13px] font-semibold whitespace-nowrap {{ request()->routeIs('admin.users*') ? 'cah-nav-link-active' : '' }}">
                            Pengelolaan Akun
                        </a>
                    @elseif ($isNurse)
                        <a href="{{ route('dashboard') }}"
                            class="cah-nav-link px-2.5 py-2 rounded-xl text-[13px] font-semibold whitespace-nowrap {{ request()->routeIs('dashboard') ? 'cah-nav-link-active' : '' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('perawat.feed') }}"
                            class="cah-nav-link px-2.5 py-2 rounded-xl text-[13px] font-semibold whitespace-nowrap {{ request()->routeIs('perawat.feed') ? 'cah-nav-link-active' : '' }}">
                            Feed Order Masuk
                        </a>
                        <a href="{{ route('perawat.dashboard') }}"
                            class="cah-nav-link px-2.5 py-2 rounded-xl text-[13px] font-semibold whitespace-nowrap {{ request()->routeIs('perawat.dashboard') ? 'cah-nav-link-active' : '' }}">
                            Panel Kerja &amp; Lokasi
                        </a>
                        <a href="{{ route('perawat.finansial') }}"
                            class="cah-nav-link px-2.5 py-2 rounded-xl text-[13px] font-semibold whitespace-nowrap {{ request()->routeIs('perawat.finansial') ? 'cah-nav-link-active' : '' }}">
                            Riwayat Finansial
                        </a>
                        <a href="{{ route('perawat.verifikasi') }}"
                            class="cah-nav-link px-2.5 py-2 rounded-xl text-[13px] font-semibold whitespace-nowrap {{ request()->routeIs('perawat.verifikasi*') ? 'cah-nav-link-active' : '' }}">
                            Verifikasi Dokumen
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}"
                            class="cah-nav-link px-2.5 py-2 rounded-xl text-[13px] font-semibold whitespace-nowrap {{ request()->routeIs('dashboard') ? 'cah-nav-link-active' : '' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('pasien.katalog') }}"
                            class="cah-nav-link px-2.5 py-2 rounded-xl text-[13px] font-semibold whitespace-nowrap {{ request()->routeIs('pasien.katalog*') ? 'cah-nav-link-active' : '' }}">
                            Katalog Layanan
                        </a>
                        <a href="{{ route('pasien.pesanan') }}"
                            class="cah-nav-link px-2.5 py-2 rounded-xl text-[13px] font-semibold whitespace-nowrap {{ request()->routeIs('pasien.pesanan') ? 'cah-nav-link-active' : '' }}">
                            Jadwal &amp; Detail Booking
                        </a>
                        <a href="{{ route('pasien.pembayaran') }}"
                            class="cah-nav-link px-2.5 py-2 rounded-xl text-[13px] font-semibold whitespace-nowrap flex items-center gap-1.5 {{ request()->routeIs('pasien.pembayaran') ? 'cah-nav-link-active' : '' }}">
                            <span>Pembayaran Escrow</span>
                        </a>
                        <a href="{{ route('pasien.pembatalan') }}"
                            class="cah-nav-link px-2.5 py-2 rounded-xl text-[13px] font-semibold whitespace-nowrap {{ request()->routeIs('pasien.pembatalan') ? 'cah-nav-link-active' : '' }}">
                            Pembatalan &amp; Refund
                        </a>
                    @endif
                </nav>
            </div>

            <!-- Right: Compact Status Badge, Notification Bell (Nurse), & User Profile -->
            <div class="flex items-center gap-2.5 shrink-0">
                @if ($user)
                    <!-- Compact Status Badge -->
                    @if ($isNurse)
                        @if ($nurseSuspendedTemp)
                            <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl text-xs font-bold bg-[#FF9ABC] text-[#46571A] border border-[#78956A] whitespace-nowrap">
                                <svg class="w-3.5 h-3.5 text-[#46571A]" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                </svg>
                                <span>Suspend Sementara</span>
                            </span>
                        @elseif ($nurseStatus === 'verified')
                            <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl text-xs font-bold bg-[#D1EB8F] text-[#46571A] border border-[#78956A] whitespace-nowrap">
                                <svg class="w-3.5 h-3.5 text-[#46571A]" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Terverifikasi</span>
                            </span>
                        @elseif ($nurseStatus === 'rejected')
                            <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl text-xs font-bold bg-[#FF9ABC] text-[#46571A] border border-[#78956A] whitespace-nowrap">
                                <svg class="w-3.5 h-3.5 text-[#46571A]" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                </svg>
                                <span>Perlu Revisi</span>
                            </span>
                        @elseif ($nurseStatus === 'pending')
                            <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl text-xs font-bold bg-[#FFFDF9] text-[#46571A] border border-[#78956A] whitespace-nowrap">
                                <svg class="w-3.5 h-3.5 text-[#78956A]" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Menunggu Audit</span>
                            </span>
                        @else
                            <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl text-xs font-semibold bg-[#FFFDF9] text-[#78956A] border border-[#78956A]/60 whitespace-nowrap">
                                <span>Draf</span>
                            </span>
                        @endif

                        <!-- In-App Notification Bell Dropdown (Nurse) -->
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
                                        itemEl.classList.remove('notif-item-unread', 'bg-[#D1EB8F]/35', 'bg-[#FF9ABC]/25');
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
                                    const token = document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}';
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
                                        document.querySelectorAll('.notif-item-unread').forEach(el => {
                                            el.classList.remove('notif-item-unread', 'bg-[#D1EB8F]/35', 'bg-[#FF9ABC]/25');
                                        });
                                        document.querySelectorAll('.notif-unread-dot, .notif-mark-single-btn, .audit-notif-banner, .verif-notif-dot').forEach(el => el.remove());
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
                                        this.unreadCount = typeof data.unread_count !== 'undefined' ? data.unread_count : Math.max(0, this.unreadCount - 1);
                                        const itemEl = document.getElementById('notif-item-' + notifId);
                                        if (itemEl) {
                                            itemEl.classList.remove('notif-item-unread', 'bg-[#D1EB8F]/35', 'bg-[#FF9ABC]/25');
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
                                class="relative p-2 rounded-xl bg-[#FFFDF9] border border-[#78956A] text-[#46571A] hover:bg-[#D1EB8F] transition flex items-center justify-center cursor-pointer"
                                title="Notifikasi Verifikasi & Sistem">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                                </svg>
                                <template x-if="unreadCount > 0">
                                    <span class="absolute -top-1.5 -right-1.5 w-5 h-5 rounded-full bg-[#FF9ABC] text-[#46571A] border border-[#78956A] font-extrabold text-[10px] flex items-center justify-center"
                                        x-text="unreadCount"></span>
                                </template>
                            </button>

                            <div x-show="toastMessage" x-cloak
                                class="absolute right-0 top-12 z-50 px-3.5 py-2 rounded-xl bg-[#78956A] text-[#F9F4EA] text-xs font-semibold shadow-lg whitespace-nowrap">
                                <span x-text="toastMessage"></span>
                            </div>

                            <div x-show="notifOpen" @click.away="notifOpen = false" x-cloak
                                class="absolute right-0 mt-2 w-80 sm:w-96 bg-[#FFFDF9] rounded-2xl shadow-xl border border-[#78956A] py-2 text-xs text-[#46571A] z-50">
                                <div class="px-4 py-2.5 border-b border-[#78956A]/30 flex items-center justify-between">
                                    <span class="font-bold text-[#46571A] flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-[#78956A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                                        </svg>
                                        <span>Notifikasi Sistem</span>
                                    </span>
                                    <template x-if="unreadCount > 0">
                                        <button type="button" @click="markAllAsRead($event)" :disabled="isMarking"
                                            class="text-[11px] font-bold text-[#46571A] hover:underline cursor-pointer disabled:opacity-50">
                                            <span x-show="!isMarking">Tandai Semua Dibaca</span>
                                            <span x-show="isMarking" x-cloak>Menandai...</span>
                                        </button>
                                    </template>
                                </div>

                                <div class="max-h-72 overflow-y-auto divide-y divide-[#78956A]/20">
                                    @forelse ($userNotifications as $n)
                                        @php
                                            $nData = $n->data;
                                            $isUnread = is_null($n->read_at);
                                            $isApproved = ($nData['type'] ?? '') === 'document_approved';
                                        @endphp
                                        <div id="notif-item-{{ $n->id }}"
                                             class="p-3.5 space-y-1.5 transition {{ $isUnread ? 'notif-item-unread ' . ($isApproved ? 'bg-[#D1EB8F]/35' : 'bg-[#FF9ABC]/25') : 'hover:bg-[#F9F4EA]' }}">
                                            <div class="flex items-start justify-between gap-2">
                                                <span class="font-bold text-[11px] text-[#46571A]">
                                                    {{ $nData['title'] ?? 'Notifikasi Sistem' }}
                                                </span>
                                                @if ($isUnread)
                                                    <span class="notif-unread-dot w-2 h-2 rounded-full bg-[#FF9ABC] border border-[#78956A] shrink-0 mt-1"></span>
                                                @endif
                                            </div>
                                            <p class="text-[11px] text-[#46571A]/85 leading-relaxed">{{ $nData['message'] ?? '-' }}</p>
                                            @if (!empty($nData['rejection_reason']))
                                                <div class="p-2 rounded-lg bg-[#FF9ABC]/25 border border-[#78956A]/40 text-[10px] text-[#46571A]">
                                                    <strong>Alasan Admin:</strong> {{ $nData['rejection_reason'] }}
                                                </div>
                                            @endif
                                            <div class="flex items-center justify-between pt-1 text-[10px] text-[#78956A]">
                                                <span>{{ $nData['formatted_time'] ?? $n->created_at->diffForHumans() }}</span>
                                                @if ($isUnread)
                                                    <button type="button"
                                                        @click="markSingleAsRead($event, '{{ route('perawat.notifications.read', $n->id) }}', '{{ $n->id }}')"
                                                        class="notif-mark-single-btn text-[#46571A] font-bold hover:underline cursor-pointer">
                                                        Tandai Dibaca
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <div class="p-6 text-center text-[#78956A] text-xs">Belum ada notifikasi baru.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @elseif ($isAdmin)
                        <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl text-xs font-bold bg-[#D1EB8F] text-[#46571A] border border-[#78956A] whitespace-nowrap">
                            <svg class="w-3.5 h-3.5 text-[#46571A]" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                            </svg>
                            <span>Admin</span>
                        </span>

                        <!-- In-App Notification Bell Dropdown (Admin) -->
                        <div class="relative" x-data="{
                            notifOpen: false,
                            unreadCount: {{ $unreadNotifCount }},
                            isMarking: false,
                            async markAllAdminRead() {
                                if (this.isMarking || this.unreadCount <= 0) return;
                                this.isMarking = true;
                                try {
                                    const token = document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}';
                                    const res = await fetch('{{ route('notifications.read-all') }}', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': token,
                                            'X-Requested-With': 'XMLHttpRequest',
                                            'Accept': 'application/json'
                                        }
                                    });
                                    if (res.ok) {
                                        this.unreadCount = 0;
                                        document.querySelectorAll('.admin-notif-unread').forEach(el => el.classList.remove('admin-notif-unread', 'bg-[#D1EB8F]/35'));
                                        document.querySelectorAll('.admin-notif-dot').forEach(el => el.remove());
                                    }
                                } catch (e) { console.error(e); }
                                finally { this.isMarking = false; }
                            }
                        }">
                            <button type="button" @click="notifOpen = !notifOpen"
                                class="relative p-2 rounded-xl bg-[#FFFDF9] border border-[#78956A] text-[#46571A] hover:bg-[#D1EB8F] transition flex items-center justify-center cursor-pointer"
                                title="Notifikasi Admin">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                                </svg>
                                <template x-if="unreadCount > 0">
                                    <span class="absolute -top-1.5 -right-1.5 w-5 h-5 rounded-full bg-[#FF9ABC] text-[#46571A] border border-[#78956A] font-extrabold text-[10px] flex items-center justify-center"
                                        x-text="unreadCount"></span>
                                </template>
                            </button>

                            <div x-show="notifOpen" @click.away="notifOpen = false" x-cloak
                                class="absolute right-0 mt-2 w-80 sm:w-96 bg-[#FFFDF9] rounded-2xl shadow-xl border border-[#78956A] py-2 text-xs text-[#46571A] z-50">
                                <div class="px-4 py-2.5 border-b border-[#78956A]/30 flex items-center justify-between">
                                    <span class="font-bold text-[#46571A]">Notifikasi Admin</span>
                                    <template x-if="unreadCount > 0">
                                        <button type="button" @click="markAllAdminRead()" :disabled="isMarking"
                                            class="text-[11px] font-bold text-[#46571A] hover:underline cursor-pointer disabled:opacity-50">
                                            Tandai Semua Dibaca
                                        </button>
                                    </template>
                                </div>
                                <div class="max-h-72 overflow-y-auto divide-y divide-[#78956A]/20">
                                    @forelse ($userNotifications as $aNotif)
                                        @php
                                            $aData = $aNotif->data;
                                            $isUnread = is_null($aNotif->read_at);
                                        @endphp
                                        <div class="p-3.5 space-y-1 transition {{ $isUnread ? 'admin-notif-unread bg-[#D1EB8F]/35' : 'hover:bg-[#F9F4EA]' }}">
                                            <div class="flex items-start justify-between gap-2">
                                                <span class="font-bold text-[11px] text-[#46571A]">{{ $aData['title'] ?? 'Aktivitas Sistem' }}</span>
                                                @if ($isUnread)
                                                    <span class="admin-notif-dot w-2 h-2 rounded-full bg-[#FF9ABC] border border-[#78956A] shrink-0 mt-1"></span>
                                                @endif
                                            </div>
                                            <p class="text-[11px] text-[#46571A]/85 leading-relaxed">{{ $aData['message'] ?? '-' }}</p>
                                            <span class="block text-[10px] text-[#78956A]">{{ $aData['formatted_time'] ?? $aNotif->created_at->diffForHumans() }}</span>
                                        </div>
                                    @empty
                                        <div class="p-6 text-center text-[#78956A] text-xs">Belum ada notifikasi baru.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @else
                        <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl text-xs font-bold bg-[#D1EB8F] text-[#46571A] border border-[#78956A] whitespace-nowrap">
                            <svg class="w-3.5 h-3.5 text-[#46571A]" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Aktif</span>
                        </span>

                        <!-- In-App Notification Bell Dropdown (Patient) -->
                        <div class="relative" x-data="{
                            notifOpen: false,
                            unreadCount: {{ $unreadNotifCount }},
                            isMarking: false,
                            async markAllPatientRead() {
                                if (this.isMarking || this.unreadCount <= 0) return;
                                this.isMarking = true;
                                try {
                                    const token = document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}';
                                    const res = await fetch('{{ route('notifications.read-all') }}', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': token,
                                            'X-Requested-With': 'XMLHttpRequest',
                                            'Accept': 'application/json'
                                        }
                                    });
                                    if (res.ok) {
                                        this.unreadCount = 0;
                                        document.querySelectorAll('.patient-notif-unread').forEach(el => el.classList.remove('patient-notif-unread', 'bg-[#D1EB8F]/35', 'bg-[#FF9ABC]/25'));
                                        document.querySelectorAll('.patient-notif-dot').forEach(el => el.remove());
                                    }
                                } catch (e) { console.error(e); }
                                finally { this.isMarking = false; }
                            }
                        }">
                            <button type="button" @click="notifOpen = !notifOpen"
                                class="relative p-2 rounded-xl bg-[#FFFDF9] border border-[#78956A] text-[#46571A] hover:bg-[#D1EB8F] transition flex items-center justify-center cursor-pointer"
                                title="Notifikasi Pesanan &amp; E-Report">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                                </svg>
                                <template x-if="unreadCount > 0">
                                    <span class="absolute -top-1.5 -right-1.5 w-5 h-5 rounded-full bg-[#FF9ABC] text-[#46571A] border border-[#78956A] font-extrabold text-[10px] flex items-center justify-center"
                                        x-text="unreadCount"></span>
                                </template>
                            </button>

                            <div x-show="notifOpen" @click.away="notifOpen = false" x-cloak
                                class="absolute right-0 mt-2 w-80 sm:w-96 bg-[#FFFDF9] rounded-2xl shadow-xl border border-[#78956A] py-2 text-xs text-[#46571A] z-50">
                                <div class="px-4 py-2.5 border-b border-[#78956A]/30 flex items-center justify-between">
                                    <span class="font-bold text-[#46571A]">Notifikasi Pasien</span>
                                    <template x-if="unreadCount > 0">
                                        <button type="button" @click="markAllPatientRead()" :disabled="isMarking"
                                            class="text-[11px] font-bold text-[#46571A] hover:underline cursor-pointer disabled:opacity-50">
                                            Tandai Semua Dibaca
                                        </button>
                                    </template>
                                </div>

                                <div class="max-h-72 overflow-y-auto divide-y divide-[#78956A]/20">
                                    @forelse ($userNotifications as $pNotif)
                                        @php
                                            $pData = $pNotif->data;
                                            $pStatus = $pData['status'] ?? '';
                                            $isUnread = is_null($pNotif->read_at);
                                            $isDeclined = in_array($pStatus, ['ditolak', 'declined']);
                                        @endphp
                                        <div class="p-3.5 space-y-1.5 transition {{ $isUnread ? 'patient-notif-unread ' . ($isDeclined ? 'bg-[#FF9ABC]/25' : 'bg-[#D1EB8F]/35') : 'hover:bg-[#F9F4EA]' }}">
                                            <div class="flex items-start justify-between gap-2">
                                                <p class="font-bold text-[11px] text-[#46571A] leading-relaxed">
                                                    {{ $pData['message'] ?? 'Pembaruan status pesanan Anda.' }}
                                                </p>
                                                @if ($isUnread)
                                                    <span class="patient-notif-dot w-2 h-2 rounded-full bg-[#FF9ABC] border border-[#78956A] shrink-0 mt-1"></span>
                                                @endif
                                            </div>
                                            <div class="flex items-center justify-between text-[10px] text-[#78956A]">
                                                <span>{{ $pNotif->created_at->diffForHumans() }}</span>
                                                @if ($isDeclined)
                                                    <a href="{{ route('pasien.katalog') }}" class="font-bold text-[#46571A] hover:underline">Pilih Perawat Lain &rarr;</a>
                                                @elseif ($pStatus === 'selesai')
                                                    <a href="{{ route('pasien.pesanan', ['tab' => 'past']) }}" class="font-bold text-[#46571A] hover:underline">Lihat E-Report &rarr;</a>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <div class="p-6 text-center text-[#78956A] text-xs">Belum ada notifikasi pesanan.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Profile Pill & Dropdown -->
                    <div class="relative">
                        <button @click="profileDropdownOpen = !profileDropdownOpen" type="button"
                            class="flex items-center gap-2 px-2 py-1.5 rounded-xl hover:bg-[#D1EB8F] transition border border-[#78956A] bg-[#FFFDF9] cursor-pointer">
                            <div class="w-8 h-8 rounded-full bg-[#FF9ABC] text-[#46571A] font-bold text-xs flex items-center justify-center border border-[#78956A]">
                                {{ $initials }}
                            </div>
                            <span class="hidden md:inline-block text-xs font-bold text-[#46571A] max-w-[110px] truncate">
                                {{ $user->name }}
                            </span>
                            <svg class="w-4 h-4 text-[#46571A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="profileDropdownOpen" @click.away="profileDropdownOpen = false" x-cloak
                            class="absolute right-0 mt-2 w-56 bg-[#FFFDF9] rounded-2xl shadow-xl border border-[#78956A] py-1.5 text-xs text-[#46571A] z-50">
                            <div class="px-3.5 py-2.5 border-b border-[#78956A]/30">
                                <p class="font-bold text-[#46571A] truncate">{{ $user->name }}</p>
                                <p class="text-[11px] text-[#78956A] truncate">{{ $user->email }}</p>
                            </div>
                            <a href="{{ route('profile') }}"
                                class="block px-3.5 py-2 hover:bg-[#D1EB8F]/60 text-[#46571A] font-semibold transition">
                                Pengaturan Profil
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full text-left px-3.5 py-2 hover:bg-[#FF9ABC]/40 text-[#46571A] font-bold transition cursor-pointer">
                                    Keluar Akun
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                <!-- Mobile Hamburger -->
                <button @click="mobileNavOpen = !mobileNavOpen" type="button"
                    class="lg:hidden p-2 rounded-xl border border-[#78956A] bg-[#FFFDF9] text-[#46571A] hover:bg-[#D1EB8F] transition cursor-pointer"
                    aria-label="Toggle Menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path x-show="!mobileNavOpen" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        <path x-show="mobileNavOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Navigation Drawer -->
        <div x-show="mobileNavOpen" x-cloak class="lg:hidden py-3 border-t border-[#78956A]/30 space-y-1">
            @if ($isAdmin)
                <a href="{{ route('admin.dashboard') }}" class="cah-nav-link block px-3 py-2 rounded-xl text-xs font-semibold {{ request()->routeIs('admin.dashboard') ? 'cah-nav-link-active' : '' }}">Dashboard Finansial</a>
                <a href="{{ route('admin.escrow.index') }}" class="cah-nav-link block px-3 py-2 rounded-xl text-xs font-semibold {{ request()->routeIs('admin.escrow*') ? 'cah-nav-link-active' : '' }}">Monitoring Mutasi</a>
                <a href="{{ route('admin.nurses.index') }}" class="cah-nav-link block px-3 py-2 rounded-xl text-xs font-semibold {{ request()->routeIs('admin.nurses*') ? 'cah-nav-link-active' : '' }}">Verifikasi Nakes</a>
                <a href="{{ route('admin.services.index') }}" class="cah-nav-link block px-3 py-2 rounded-xl text-xs font-semibold {{ request()->routeIs('admin.services*') ? 'cah-nav-link-active' : '' }}">Katalog &amp; Tarif</a>
                <a href="{{ route('admin.users.index') }}" class="cah-nav-link block px-3 py-2 rounded-xl text-xs font-semibold {{ request()->routeIs('admin.users*') ? 'cah-nav-link-active' : '' }}">Pengelolaan Akun</a>
            @elseif ($isNurse)
                <a href="{{ route('dashboard') }}" class="cah-nav-link block px-3 py-2 rounded-xl text-xs font-semibold {{ request()->routeIs('dashboard') ? 'cah-nav-link-active' : '' }}">Dashboard</a>
                <a href="{{ route('perawat.feed') }}" class="cah-nav-link block px-3 py-2 rounded-xl text-xs font-semibold {{ request()->routeIs('perawat.feed') ? 'cah-nav-link-active' : '' }}">Feed Order Masuk</a>
                <a href="{{ route('perawat.dashboard') }}" class="cah-nav-link block px-3 py-2 rounded-xl text-xs font-semibold {{ request()->routeIs('perawat.dashboard') ? 'cah-nav-link-active' : '' }}">Panel Kerja &amp; Lokasi</a>
                <a href="{{ route('perawat.finansial') }}" class="cah-nav-link block px-3 py-2 rounded-xl text-xs font-semibold {{ request()->routeIs('perawat.finansial') ? 'cah-nav-link-active' : '' }}">Riwayat Finansial</a>
                <a href="{{ route('perawat.verifikasi') }}" class="cah-nav-link block px-3 py-2 rounded-xl text-xs font-semibold {{ request()->routeIs('perawat.verifikasi*') ? 'cah-nav-link-active' : '' }}">Verifikasi Dokumen</a>
            @else
                <a href="{{ route('dashboard') }}" class="cah-nav-link block px-3 py-2 rounded-xl text-xs font-semibold {{ request()->routeIs('dashboard') ? 'cah-nav-link-active' : '' }}">Dashboard</a>
                <a href="{{ route('pasien.katalog') }}" class="cah-nav-link block px-3 py-2 rounded-xl text-xs font-semibold {{ request()->routeIs('pasien.katalog*') ? 'cah-nav-link-active' : '' }}">Katalog Layanan</a>
                <a href="{{ route('pasien.pesanan') }}" class="cah-nav-link block px-3 py-2 rounded-xl text-xs font-semibold {{ request()->routeIs('pasien.pesanan') ? 'cah-nav-link-active' : '' }}">Jadwal &amp; Detail Booking</a>
                <a href="{{ route('pasien.pembayaran') }}" class="cah-nav-link block px-3 py-2 rounded-xl text-xs font-semibold {{ request()->routeIs('pasien.pembayaran') ? 'cah-nav-link-active' : '' }}">Pembayaran Escrow</a>
                <a href="{{ route('pasien.pembatalan') }}" class="cah-nav-link block px-3 py-2 rounded-xl text-xs font-semibold {{ request()->routeIs('pasien.pembatalan') ? 'cah-nav-link-active' : '' }}">Pembatalan &amp; Refund</a>
            @endif
        </div>
    </div>
</header>
