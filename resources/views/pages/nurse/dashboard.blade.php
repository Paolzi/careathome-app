<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                {{ __('Dashboard Mitra Perawat') }}
            </h2>
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $profile->verification_status === 'verified' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }}">
                    Status Legalitas: {{ ucfirst($profile->verification_status) }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="nurseDashboardApp({{ $profile->is_online ? 'true' : 'false' }}, '{{ $profile->verification_status }}', {{ $profile->latitude ?? 'null' }}, {{ $profile->longitude ?? 'null' }})">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

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

            <!-- 1. HERO BANNER & TOGGLE ON/OFF "SEDANG BEKERJA" -->
            <div class="bg-gradient-to-r from-slate-900 via-teal-950 to-emerald-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden border border-emerald-800/40">
                <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
                    <div class="space-y-3 max-w-xl">
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-xs font-semibold">
                            <span class="w-2 h-2 rounded-full" :class="isOnline ? 'bg-emerald-400 animate-pulse' : 'bg-slate-400'"></span>
                            <span x-text="isOnline ? 'SEDANG BEKERJA (ON)' : 'OFFLINE (TIDAK BEKERJA)'"></span>
                        </span>

                        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                            Halo, {{ $user->name }}
                        </h1>
                        <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                            Aktifkan toggle <strong>"Sedang Bekerja"</strong> agar Anda dapat ditemukan oleh pasien yang berada dalam <strong>radius 10 km</strong> dari lokasi GPS Anda.
                        </p>

                        <div class="text-[11px] text-slate-400 flex items-center gap-3 pt-1">
                            <span>🛰️ Koordinat: <strong x-text="lat && lng ? (lat.toFixed(4) + ', ' + lng.toFixed(4)) : 'Belum terlacak'"></strong></span>
                            <span>•</span>
                            <span>⏱️ Terakhir Update: <strong x-text="lastUpdatedText || '{{ $profile->location_updated_at ? $profile->location_updated_at->diffForHumans() : 'Belum ada' }}'"></strong></span>
                        </div>
                    </div>

                    <!-- TOGGLE SWITCH INTERAKTIF -->
                    <div class="bg-white/10 backdrop-blur-md p-5 rounded-2xl border border-white/10 text-center min-w-[240px] space-y-3">
                        <span class="text-xs uppercase font-extrabold text-slate-200 tracking-wider block">Status Ketersediaan</span>
                        
                        <div class="flex items-center justify-center gap-3">
                            <span class="text-xs font-bold" :class="!isOnline ? 'text-white' : 'text-slate-400'">Offline</span>
                            
                            <!-- Toggle Button -->
                            <button type="button" 
                                @click="handleToggleOnline()"
                                :disabled="togglingStatus"
                                class="w-16 h-8 rounded-full p-1 transition-colors duration-200 ease-in-out focus:outline-none relative"
                                :class="isOnline ? 'bg-emerald-500' : 'bg-slate-700'">
                                <div class="w-6 h-6 rounded-full bg-white shadow-md transform transition-transform duration-200 ease-in-out"
                                    :class="isOnline ? 'translate-x-8' : 'translate-x-0'"></div>
                            </button>
                            
                            <span class="text-xs font-bold" :class="isOnline ? 'text-emerald-400' : 'text-slate-400'">Bekerja</span>
                        </div>

                        <p class="text-[10px] text-slate-300">
                            <template x-if="verificationStatus !== 'verified'">
                                <span class="text-amber-300 font-semibold block">⚠️ Syarat: Dokumen STR/SIP harus berstatus Terverifikasi sebelum bisa ON.</span>
                            </template>
                            <template x-if="verificationStatus === 'verified'">
                                <span x-text="isOnline ? 'Lokasi dikirim berkala (maks. tiap 30 detik).' : 'Klik toggle untuk mulai menerima pesanan.'"></span>
                            </template>
                        </p>
                    </div>
                </div>
            </div>

            <!-- 2. GRID UTAMA: DOMPET SALDO & LAYANAN MEDIS -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- KOLOM KIRI: DOMPET, SALDO & PENARIKAN DANA (TARIK SALDO) -->
                <div id="rekening-saldo" class="lg:col-span-1 space-y-6">

                    <!-- Kartu Saldo Dompet -->
                    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-5">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <span class="text-xs uppercase font-bold text-slate-400 tracking-wider">Dompet Pendapatan</span>
                            <span class="text-[10px] bg-emerald-50 text-emerald-800 font-bold px-2 py-0.5 rounded-full border border-emerald-200">Mitra Nakes</span>
                        </div>

                        <div class="space-y-1">
                            <span class="text-xs text-slate-500">Saldo Tersedia:</span>
                            <h3 class="text-2xl font-black text-slate-900">
                                Rp {{ number_format($wallet->balance, 0, ',', '.') }}
                            </h3>
                            <p class="text-[10px] text-slate-400">
                                Bagian 85% dari setiap pesanan yang Anda selesaikan langsung masuk ke saldo ini.
                            </p>
                        </div>

                        <!-- Form Tarik Saldo -->
                        <div class="pt-4 border-t border-slate-100 space-y-3" x-data="{ openForm: window.location.hash === '#rekening-saldo' }">
                            <button type="button" @click="openForm = !openForm"
                                class="w-full py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow transition flex items-center justify-center gap-1.5">
                                <span>💸 Tarik Saldo (Payout)</span>
                            </button>

                            <div x-show="openForm" x-cloak class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                                <h4 class="text-xs font-bold text-slate-800">Form Pengajuan Penarikan</h4>
                                <form action="{{ route('perawat.payout.request') }}" method="POST" class="space-y-3">
                                    @csrf
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Nominal (Rp)</label>
                                        <input type="number" name="amount" min="10000" max="{{ $wallet->balance }}" required
                                            placeholder="Min. 10000"
                                            class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Bank Tujuan</label>
                                        <input type="text" name="bank_name" placeholder="Mis. BCA, Mandiri, BRI" required
                                            class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Nomor Rekening</label>
                                        <input type="text" name="account_number" placeholder="Nomor rekening" required
                                            class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Atas Nama Rekening</label>
                                        <input type="text" name="account_holder" placeholder="Nama pemilik rekening" required
                                            class="w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">
                                    </div>

                                    <button type="submit"
                                        class="w-full py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow transition">
                                        Kirim Pengajuan Payout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Riwayat Permintaan Payout -->
                    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-3">
                        <h4 class="text-xs uppercase font-bold text-slate-400 tracking-wider">Riwayat Payout</h4>
                        
                        @if ($payoutRequests->isEmpty())
                            <p class="text-xs text-slate-400 italic">Belum ada riwayat penarikan dana.</p>
                        @else
                            <div class="space-y-2.5 max-h-64 overflow-y-auto pr-1">
                                @foreach ($payoutRequests as $pr)
                                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/80 text-xs space-y-1">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-slate-900">Rp {{ number_format($pr->amount, 0, ',', '.') }}</span>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold 
                                                {{ $pr->status === 'transferred' ? 'bg-emerald-100 text-emerald-800' : ($pr->status === 'rejected' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                                {{ ucfirst($pr->status) }}
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-slate-500">{{ $pr->bank_name }} - {{ $pr->account_number }} ({{ $pr->account_holder }})</p>
                                        @if ($pr->proof_path)
                                            <a href="{{ asset('storage/' . $pr->proof_path) }}" target="_blank" class="text-[10px] text-emerald-600 font-bold hover:underline block">
                                                Lihat Bukti Transfer Admin &rarr;
                                            </a>
                                        @endif
                                        @if ($pr->rejection_reason)
                                            <p class="text-[10px] text-rose-600 italic">Alasan penolakan: {{ $pr->rejection_reason }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                </div>

                <!-- KOLOM KANAN: PILIHAN LAYANAN YANG DISEDIAKAN & RIWAYAT MUTASI -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Pilihan Layanan Medis yang Disediakan -->
                    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Layanan Keperawatan yang Anda Sediakan</h3>
                                <p class="text-xs text-slate-500">Centang tindakan yang Anda kuasai. Anda hanya akan menerima pesanan untuk tindakan yang dipilih.</p>
                            </div>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-800">
                                {{ count($selectedServiceIds) }} Terpilih
                            </span>
                        </div>

                        <form action="{{ route('perawat.layanan.update') }}" method="POST" class="space-y-4">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-80 overflow-y-auto pr-1">
                                @foreach ($services as $service)
                                    <label class="p-3 rounded-2xl border transition flex items-start gap-3 cursor-pointer hover:bg-slate-50"
                                        :class="checked ? 'border-emerald-500 bg-emerald-50/40' : 'border-slate-200'">
                                        <input type="checkbox" name="service_ids[]" value="{{ $service->id }}"
                                            {{ in_array($service->id, $selectedServiceIds) ? 'checked' : '' }}
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

                            <div class="flex justify-end pt-2">
                                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow transition">
                                    Simpan Pilihan Layanan
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Riwayat Transaksi Dompet (10 Terakhir) -->
                    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <h3 class="text-sm font-bold text-slate-900">Riwayat Mutasi Saldo Dompet</h3>
                            <span class="text-xs text-slate-400">10 Transaksi Terakhir</span>
                        </div>

                        @if ($recentTransactions->isEmpty())
                            <p class="text-xs text-slate-400 italic py-4 text-center">Belum ada transaksi pendapatan atau penarikan saldo.</p>
                        @else
                            <div class="space-y-2">
                                @foreach ($recentTransactions as $tx)
                                    <div class="p-3 rounded-2xl border border-slate-100 flex items-center justify-between text-xs">
                                        <div class="space-y-0.5">
                                            <span class="font-bold text-slate-800 block">{{ $tx->description }}</span>
                                            <span class="text-[10px] text-slate-400">{{ $tx->created_at->format('d M Y H:i') }} • Tipe: {{ $tx->type }}</span>
                                        </div>
                                        <div class="text-right">
                                            <span class="font-extrabold block {{ $tx->amount > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                                {{ $tx->amount > 0 ? '+' : '' }}Rp {{ number_format($tx->amount, 0, ',', '.') }}
                                            </span>
                                            <span class="text-[10px] text-slate-400">Saldo: Rp {{ number_format($tx->balance_after, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                </div>

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

                init() {
                    if (this.isOnline) {
                        this.startLocationWatch();
                    }
                },

                showToast(msg, type = 'success') {
                    this.toastMessage = msg;
                    this.toastType = type;
                    setTimeout(() => { this.toastMessage = ''; }, 4000);
                },

                async handleToggleOnline() {
                    if (this.togglingStatus) return;

                    // Validasi status verifikasi
                    if (!this.isOnline && this.verificationStatus !== 'verified') {
                        this.showToast('Gagal: Berkas STR & SIP Anda belum terverifikasi oleh Admin.', 'error');
                        return;
                    }

                    const targetState = !this.isOnline;
                    this.togglingStatus = true;

                    if (targetState) {
                        // Meminta izin lokasi browser sebelum mengaktifkan ON
                        if (!navigator.geolocation) {
                            this.showToast('Browser Anda tidak mendukung Geolocation.', 'error');
                            this.togglingStatus = false;
                            return;
                        }

                        navigator.geolocation.getCurrentPosition(
                            async (pos) => {
                                this.lat = pos.coords.latitude;
                                this.lng = pos.coords.longitude;

                                await this.sendToggleRequest(true, this.lat, this.lng);
                                this.startLocationWatch();
                                this.togglingStatus = false;
                            },
                            (err) => {
                                console.warn('Izin lokasi ditolak:', err);
                                this.showToast('Izin lokasi ditolak oleh browser. Status kerja gagal diaktifkan.', 'error');
                                this.isOnline = false;
                                this.togglingStatus = false;
                            },
                            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                        );
                    } else {
                        // Mematikan status ON -> OFF
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

                async sendLocationUpdate(lat, lng) {
                    if (!this.isOnline) return;

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
                        }
                    } catch (e) {
                        console.warn('Gagal sinkron lokasi:', e);
                    }
                }
            };
        }
    </script>
</x-app-layout>
