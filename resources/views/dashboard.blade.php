<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Alert Messages -->
            @if (session('status'))
                <div class="p-4 rounded-2xl bg-[#D1EB8F] border border-[#78956A] text-xs font-semibold text-[#46571A] flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-[#46571A] shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 rounded-2xl bg-[#FF9ABC]/35 border border-[#78956A] text-xs font-semibold text-[#46571A] flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-[#46571A] shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Welcome Banner Pasien -->
            <div class="bg-[#78956A] rounded-3xl p-6 sm:p-8 text-[#F9F4EA] border border-[#46571A]/30 shadow-md relative overflow-hidden">
                <div class="max-w-2xl space-y-3 relative z-10">
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#D1EB8F] text-[#46571A] text-xs font-bold border border-[#46571A]/30">
                        <span class="w-2 h-2 rounded-full bg-[#46571A]"></span>
                        CareAtHome On-Demand Medis
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-[#F9F4EA]">
                        Selamat Datang, {{ auth()->user()->name }}!
                    </h1>
                    <p class="text-xs sm:text-sm text-[#F9F4EA]/95 leading-relaxed">
                        Layanan perawatan medis terstandar datang langsung ke rumah Anda. Didukung nakes bersertifikasi resmi STR/SIP Kementerian Kesehatan serta jaminan keamanan pembayaran <em>Escrow System</em>.
                    </p>
                    <div class="pt-2">
                        <a href="{{ route('pasien.katalog') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#FF9ABC] hover:bg-[#D1EB8F] text-[#46571A] border border-[#46571A]/30 font-bold text-xs shadow-sm transition transform hover:-translate-y-0.5">
                            <span>Jelajahi Katalog Layanan Medis</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            @if (auth()->user()->hasRole('perawat'))
                <!-- Khusus Tampilan Jika Login Sebagai Perawat -->
                @php
                    $nurseProfile = \App\Models\NurseProfile::where('user_id', auth()->id())->first();
                    $isVerified = $nurseProfile && $nurseProfile->verification_status === 'verified';
                @endphp
                <div class="bg-[#FFFDF9] rounded-2xl border border-[#78956A] p-6 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="space-y-2">
                            <span class="inline-block text-[10px] font-bold uppercase tracking-wider text-[#46571A] bg-[#D1EB8F] px-2.5 py-1 rounded-full border border-[#78956A]">
                                Portal Mitra Perawat
                            </span>
                            @if ($isVerified)
                                <h3 class="text-lg font-bold text-[#46571A]">Dashboard Kerja &amp; Ketersediaan</h3>
                                <p class="text-xs text-[#46571A]/85 max-w-xl">
                                    Akun Anda telah terverifikasi. Buka dashboard untuk mengaktifkan status kerja dan mulai menerima order kunjungan homecare.
                                </p>
                            @else
                                <h3 class="text-lg font-bold text-[#46571A]">Verifikasi Berkas Legalitas STR &amp; SIP</h3>
                                <p class="text-xs text-[#46571A]/85 max-w-xl">
                                    Kelola kelengkapan dokumen resmi profesi (KTP, STR aktif, SIP, dan ijazah keperawatan) untuk dapat menerima pesanan kunjungan homecare.
                                </p>
                            @endif
                        </div>
                        @if ($isVerified)
                            <a href="{{ route('perawat.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#FF9ABC] hover:bg-[#D1EB8F] text-[#46571A] border border-[#78956A] font-bold text-xs shadow-sm transition whitespace-nowrap">
                                <span>Buka Dashboard Kerja</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                </svg>
                            </a>
                        @else
                            <a href="{{ route('perawat.verifikasi') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#FF9ABC] hover:bg-[#D1EB8F] text-[#46571A] border border-[#78956A] font-bold text-xs shadow-sm transition whitespace-nowrap">
                                <span>Buka Verifikasi Nakes</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>
            @else
                <!-- FITUR UTAMA PASIEN -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <!-- Card 1: Katalog Layanan Medis -->
                    <div class="bg-[#FFFDF9] rounded-2xl border border-[#78956A] p-6 shadow-sm hover:shadow-md transition flex flex-col justify-between group">
                        <div class="space-y-3">
                            <div class="w-12 h-12 rounded-xl bg-[#D1EB8F] text-[#46571A] border border-[#78956A] flex items-center justify-center group-hover:scale-105 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                </svg>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-[#46571A] bg-[#D1EB8F] px-2.5 py-0.5 rounded-full border border-[#78956A]">
                                    9 Tindakan Tersedia
                                </span>
                                <h3 class="text-base font-bold text-[#46571A] mt-2">Katalog &amp; Pencarian Medis</h3>
                            </div>
                            <p class="text-xs text-[#46571A]/85 leading-relaxed">
                                Temukan tindakan keperawatan resmi: rawat luka diabetes, pasang kateter, selang NGT, nebulizer, infus/injeksi, hingga pendampingan lansia.
                            </p>
                        </div>
                        <div class="pt-5 mt-4 border-t border-[#78956A]/25">
                            <a href="{{ route('pasien.katalog') }}" class="inline-flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-xl bg-[#FF9ABC] hover:bg-[#D1EB8F] text-[#46571A] border border-[#78956A] text-xs font-bold shadow-sm transition">
                                <span>Buka Katalog Layanan</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Card 2: Pesanan & Pelacakan Kunjungan -->
                    <div class="bg-[#FFFDF9] rounded-2xl border border-[#78956A] p-6 shadow-sm hover:shadow-md transition flex flex-col justify-between group">
                        <div class="space-y-3">
                            <div class="w-12 h-12 rounded-xl bg-[#D1EB8F] text-[#46571A] border border-[#78956A] flex items-center justify-center group-hover:scale-105 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                </svg>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-[#46571A] bg-[#D1EB8F] px-2.5 py-0.5 rounded-full border border-[#78956A]">
                                    Radius GPS 10 KM
                                </span>
                                <h3 class="text-base font-bold text-[#46571A] mt-2">Jadwal, Pelacakan &amp; Escrow</h3>
                            </div>
                            <p class="text-xs text-[#46571A]/85 leading-relaxed">
                                Pantau pergerakan nakes secara real-time, lakukan pembayaran aman via rekening penampung (Escrow), serta batalkan pesanan dengan jaminan 100% refund otomatis.
                            </p>
                        </div>
                        <div class="pt-4 mt-4 border-t border-[#78956A]/25 space-y-2">
                            <a href="{{ route('pasien.pesanan') }}" class="inline-flex items-center justify-center gap-2 w-full px-4 py-2 rounded-xl bg-[#FF9ABC] hover:bg-[#D1EB8F] text-[#46571A] border border-[#78956A] text-xs font-bold transition shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z" />
                                </svg>
                                <span>Detail Booking &amp; Pelacakan</span>
                            </a>
                            <div class="grid grid-cols-2 gap-2">
                                <a href="{{ route('pasien.pembayaran') }}" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#78956A] hover:bg-[#D1EB8F] text-[#F9F4EA] hover:text-[#46571A] border border-[#78956A] text-[11px] font-bold transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                                    </svg>
                                    <span>Bayar Escrow</span>
                                </a>
                                <a href="{{ route('pasien.pembatalan') }}" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#F9F4EA] hover:bg-[#FF9ABC] text-[#46571A] border border-[#78956A] text-[11px] font-bold transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                                    </svg>
                                    <span>Batalkan / Refund</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: Rekam Asuhan Medis / E-Report -->
                    <div class="bg-[#FFFDF9] rounded-2xl border border-[#78956A] p-6 shadow-sm hover:shadow-md transition flex flex-col justify-between group">
                        <div class="space-y-3">
                            <div class="w-12 h-12 rounded-xl bg-[#D1EB8F] text-[#46571A] border border-[#78956A] flex items-center justify-center group-hover:scale-105 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-[#46571A] bg-[#D1EB8F] px-2.5 py-0.5 rounded-full border border-[#78956A]">
                                    Rekam Medis Digital
                                </span>
                                <h3 class="text-base font-bold text-[#46571A] mt-2">E-Report &amp; Tanda Vital</h3>
                            </div>
                            <p class="text-xs text-[#46571A]/85 leading-relaxed">
                                Akses arsip digital hasil asuhan keperawatan: tanda-tanda vital (tensi, nadi, suhu, gula darah), foto luka, dan petunjuk perawatan mandiri.
                            </p>
                        </div>
                        <div class="pt-5 mt-4 border-t border-[#78956A]/25">
                            <a href="{{ route('pasien.pesanan', ['tab' => 'past']) }}" class="inline-flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-xl bg-[#78956A] hover:bg-[#D1EB8F] text-[#F9F4EA] hover:text-[#46571A] border border-[#78956A] text-xs font-bold shadow-sm transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                <span>Buka Riwayat &amp; E-Report</span>
                            </a>
                        </div>
                    </div>

                </div>

                <!-- Petunjuk Alur 4 Langkah untuk Pasien -->
                <div class="bg-[#FFFDF9] rounded-2xl border border-[#78956A] p-6 sm:p-8 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-[#46571A] bg-[#D1EB8F] px-2.5 py-1 rounded-full border border-[#78956A]">
                                Cara Kerja Homecare
                            </span>
                            <h3 class="text-base font-extrabold text-[#46571A] mt-2">4 Langkah Mudah Pemanggilan Perawat ke Rumah</h3>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-2">
                        <div class="p-4 rounded-xl bg-[#F9F4EA] border border-[#78956A]/50 space-y-1.5">
                            <div class="w-7 h-7 rounded-lg bg-[#78956A] text-[#F9F4EA] font-extrabold text-xs flex items-center justify-center">1</div>
                            <h4 class="text-xs font-bold text-[#46571A]">Pilih Layanan</h4>
                            <p class="text-[11px] text-[#46571A]/80 leading-relaxed">Pilih tindakan keperawatan dari katalog terstandar sesuai kondisi pasien.</p>
                        </div>

                        <div class="p-4 rounded-xl bg-[#F9F4EA] border border-[#78956A]/50 space-y-1.5">
                            <div class="w-7 h-7 rounded-lg bg-[#78956A] text-[#F9F4EA] font-extrabold text-xs flex items-center justify-center">2</div>
                            <h4 class="text-xs font-bold text-[#46571A]">Atur Jadwal &amp; Lokasi</h4>
                            <p class="text-[11px] text-[#46571A]/80 leading-relaxed">Tentukan jam kunjungan dan titik alamat rumah melalui integrasi peta GPS.</p>
                        </div>

                        <div class="p-4 rounded-xl bg-[#F9F4EA] border border-[#78956A]/50 space-y-1.5">
                            <div class="w-7 h-7 rounded-lg bg-[#78956A] text-[#F9F4EA] font-extrabold text-xs flex items-center justify-center">3</div>
                            <h4 class="text-xs font-bold text-[#46571A]">Pembayaran Escrow</h4>
                            <p class="text-[11px] text-[#46571A]/80 leading-relaxed">Dana ditampung aman di rekening sistem sebelum perawat berangkat.</p>
                        </div>

                        <div class="p-4 rounded-xl bg-[#F9F4EA] border border-[#78956A]/50 space-y-1.5">
                            <div class="w-7 h-7 rounded-lg bg-[#78956A] text-[#F9F4EA] font-extrabold text-xs flex items-center justify-center">4</div>
                            <h4 class="text-xs font-bold text-[#46571A]">E-Report &amp; Ulasan</h4>
                            <p class="text-[11px] text-[#46571A]/80 leading-relaxed">Tindakan steril selesai, perawat mengunggah E-Report dan pasien memberi ulasan.</p>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
