<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl text-slate-800 leading-tight">
                {{ __('Dashboard Pasien') }}
            </h2>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200">
                Portal Pasien CareAtHome
            </span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Alert Messages if any -->
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

            <!-- Welcome Banner Pasien -->
            <div class="bg-gradient-to-r from-emerald-700 via-teal-700 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-lg relative overflow-hidden">
                <!-- Background decorative shapes -->
                <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-emerald-400/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="absolute top-0 right-1/3 w-40 h-40 bg-teal-300/10 rounded-full blur-xl pointer-events-none"></div>

                <div class="max-w-2xl space-y-3 relative z-10">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-200 text-xs font-semibold border border-emerald-400/30">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        CareAtHome On-Demand Medis
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                        Selamat Datang, {{ auth()->user()->name }}!
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-200 leading-relaxed">
                        Layanan perawatan medis terstandar datang langsung ke rumah Anda. Didukung nakes bersertifikasi resmi STR/SIP Kementerian Kesehatan serta jaminan keamanan pembayaran <em>Escrow System</em>.
                    </p>
                    <div class="pt-2">
                        <a href="{{ route('pasien.katalog') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white text-emerald-800 hover:bg-slate-100 font-bold text-xs shadow-md transition transform hover:-translate-y-0.5">
                            <span>Jelajahi Katalog Layanan Medis</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            @if (auth()->user()->hasRole('perawat'))
                <!-- Khusus Tampilan Jika Login Sebagai Perawat -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div class="space-y-2">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-teal-700 bg-teal-50 px-2.5 py-1 rounded-full border border-teal-200">
                                Portal Mitra Perawat
                            </span>
                            <h3 class="text-lg font-bold text-slate-900">Verifikasi Berkas Legalitas STR &amp; SIP</h3>
                            <p class="text-xs text-slate-600 max-w-xl">
                                Kelola kelengkapan dokumen resmi profesi (KTP, STR aktif, SIP, dan ijazah keperawatan) untuk dapat menerima pesanan kunjungan homecare.
                            </p>
                        </div>
                        <a href="{{ route('perawat.verifikasi') }}" class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow transition whitespace-nowrap">
                            Buka Verifikasi Nakes &rarr;
                        </a>
                    </div>
                </div>
            @else
                <!-- FITUR UTAMA PASIEN (MURNI UNTUK PASIEN) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    
                    <!-- Card 1: Katalog Layanan Medis -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm hover:border-emerald-500 hover:shadow-md transition flex flex-col justify-between group">
                        <div class="space-y-3">
                            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center text-2xl font-bold group-hover:scale-105 transition-transform">
                                🩺
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                    9 Tindakan Tersedia
                                </span>
                                <h3 class="text-base font-bold text-slate-900 mt-1">Katalog &amp; Pencarian Medis</h3>
                            </div>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Temukan tindakan keperawatan resmi: rawat luka diabetes, pasang kateter, selang NGT, nebulizer, infus/injeksi, hingga pendampingan lansia.
                            </p>
                        </div>
                        <div class="pt-5 mt-4 border-t border-slate-100">
                            <a href="{{ route('pasien.katalog') }}" class="inline-flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition">
                                <span>Buka Katalog Layanan</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Card 2: Pesanan & Pelacakan Kunjungan -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm hover:border-teal-500 hover:shadow-md transition flex flex-col justify-between group">
                        <div class="space-y-3">
                            <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-700 border border-teal-200 flex items-center justify-center text-2xl font-bold group-hover:scale-105 transition-transform">
                                🚗
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-teal-700 bg-teal-50 px-2 py-0.5 rounded-full border border-teal-200">
                                    Radius GPS 10 KM
                                </span>
                                <h3 class="text-base font-bold text-slate-900 mt-1">Jadwal, Pelacakan &amp; Escrow</h3>
                            </div>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Pantau pergerakan nakes secara real-time, lakukan pembayaran aman via rekening penampung (Escrow), serta batalkan pesanan dengan jaminan 100% refund otomatis.
                            </p>
                        </div>
                        <div class="pt-4 mt-4 border-t border-slate-100 space-y-2">
                            <a href="{{ route('pasien.pesanan') }}" class="inline-flex items-center justify-center gap-2 w-full px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold transition shadow-sm">
                                <span>🗺️ Detail Booking &amp; Pelacakan</span>
                            </a>
                            <div class="grid grid-cols-2 gap-2">
                                <a href="{{ route('pasien.pembayaran') }}" class="inline-flex items-center justify-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-[11px] font-bold transition">
                                    <span>💳 Bayar Escrow</span>
                                </a>
                                <a href="{{ route('pasien.pembatalan') }}" class="inline-flex items-center justify-center gap-1 px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-800 border border-rose-200 text-[11px] font-bold transition">
                                    <span>Batalkan / Refund</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: Rekam Asuhan Medis / E-Report -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm hover:border-indigo-500 hover:shadow-md transition flex flex-col justify-between group">
                        <div class="space-y-3">
                            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-700 border border-indigo-200 flex items-center justify-center text-2xl font-bold group-hover:scale-105 transition-transform">
                                📋
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-full border border-indigo-200">
                                    Rekam Medis Digital
                                </span>
                                <h3 class="text-base font-bold text-slate-900 mt-1">E-Report &amp; Tanda Vital</h3>
                            </div>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Akses arsip digital hasil asuhan keperawatan: tanda-tanda vital (tensi, nadi, suhu, gula darah), foto luka, dan petunjuk perawatan mandiri.
                            </p>
                        </div>
                        <div class="pt-5 mt-4 border-t border-slate-100">
                            <span class="inline-flex items-center justify-center gap-1.5 w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-500 text-xs font-semibold">
                                <span>Tersedia Pasca Tindakan</span>
                            </span>
                        </div>
                    </div>

                </div>

                <!-- Petunjuk Alur 4 Langkah untuk Pasien -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">
                                Cara Kerja Homecare
                            </span>
                            <h3 class="text-base font-extrabold text-slate-900 mt-1">4 Langkah Mudah Pemanggilan Perawat ke Rumah</h3>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-2">
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-1.5">
                            <div class="w-7 h-7 rounded-lg bg-emerald-600 text-white font-extrabold text-xs flex items-center justify-center">1</div>
                            <h4 class="text-xs font-bold text-slate-900">Pilih Layanan</h4>
                            <p class="text-[11px] text-slate-500 leading-relaxed">Pilih tindakan keperawatan dari katalog terstandar sesuai kondisi pasien.</p>
                        </div>

                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-1.5">
                            <div class="w-7 h-7 rounded-lg bg-emerald-600 text-white font-extrabold text-xs flex items-center justify-center">2</div>
                            <h4 class="text-xs font-bold text-slate-900">Atur Jadwal &amp; Lokasi</h4>
                            <p class="text-[11px] text-slate-500 leading-relaxed">Tentukan jam kunjungan dan titik alamat rumah melalui integrasi peta GPS.</p>
                        </div>

                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-1.5">
                            <div class="w-7 h-7 rounded-lg bg-emerald-600 text-white font-extrabold text-xs flex items-center justify-center">3</div>
                            <h4 class="text-xs font-bold text-slate-900">Pembayaran Escrow</h4>
                            <p class="text-[11px] text-slate-500 leading-relaxed">Dana ditampung aman di rekening sistem sebelum perawat berangkat.</p>
                        </div>

                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-1.5">
                            <div class="w-7 h-7 rounded-lg bg-emerald-600 text-white font-extrabold text-xs flex items-center justify-center">4</div>
                            <h4 class="text-xs font-bold text-slate-900">E-Report &amp; Ulasan</h4>
                            <p class="text-[11px] text-slate-500 leading-relaxed">Tindakan steril selesai, perawat mengunggah E-Report dan pasien memberi ulasan.</p>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
