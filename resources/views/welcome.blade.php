@php
$services = [
    [
        'id' => 1,
        'title' => 'Perawatan Luka Diabetes & Dekubitus',
        'category' => 'Perawatan Khusus',
        'categoryBadge' => 'Perawatan Khusus',
        'badgeColor' => 'bg-rose-50 text-rose-700 border-rose-200',
        'duration' => '60 - 90 Menit',
        'imageEmoji' => '🩹',
        'desc' => 'Pembersihan antiseptik steril, debridement nekrotik, dan pembalutan modern wound dressing anti-mikroba untuk regenerasi optimal jaringan luka diabetes, dekubitus, atau luka pasca operasi.',
        'inclusions' => [
            'Debridement & pembersihan luka aseptik steril',
            'Pembalutan modern wound dressing anti-bakteri',
            'Pemeriksaan infeksi & monitoring tanda vital',
            'Dokumentasi E-Report & foto progres luka berkala'
        ]
    ],
    [
        'id' => 2,
        'title' => 'Pemasangan & Penggantian Kateter Urine',
        'category' => 'Prosedur Medis',
        'categoryBadge' => 'Prosedur Medis',
        'badgeColor' => 'bg-amber-50 text-amber-700 border-amber-200',
        'duration' => '45 - 60 Menit',
        'imageEmoji' => '🩺',
        'desc' => 'Prosedur insersi dan penggantian Foley Catheter dengan teknik aseptik tinggi oleh nakes bersertifikat untuk memastikan drainase lancar serta mencegah risiko infeksi saluran kemih (ISK).',
        'inclusions' => [
            'Penggantian kateter & urine bag steril baru',
            'Teknik fiksasi steril dan aman untuk mobilitas',
            'Pemeriksaan volume residu & karakteristik urin',
            'Edukasi higienitas perawatan harian bagi keluarga'
        ]
    ],
    [
        'id' => 3,
        'title' => 'Pemberian Terapi Infus & Injeksi Terjadwal',
        'category' => 'Terapi & Injeksi',
        'categoryBadge' => 'Terapi Cairan',
        'badgeColor' => 'bg-teal-50 text-teal-700 border-teal-200',
        'duration' => '45 - 60 Menit',
        'imageEmoji' => '💉',
        'desc' => 'Pemasangan abocath intravena, rehidrasi cairan fisiologis, atau injeksi vitamin dan obat berkala sesuai instruksi dan resep dokter spesialis penanggung jawab.',
        'inclusions' => [
            'Pemasangan infus steril vena perifer mikro/makro',
            'Pengaturan tetesan & evaluasi respon tubuh',
            'Injeksi intramuskular (IM) atau intravena (IV) aman',
            'Monitoring tanda vital pra & pasca tindakan'
        ]
    ],
    [
        'id' => 4,
        'title' => 'Terapi Inhalasi Nebulizer & Saluran Napas',
        'category' => 'Terapi & Injeksi',
        'categoryBadge' => 'Pernapasan',
        'badgeColor' => 'bg-sky-50 text-sky-700 border-sky-200',
        'duration' => '30 - 45 Menit',
        'imageEmoji' => '🫁',
        'desc' => 'Pemberian terapi uap bronkodilator untuk mengencerkan lendir dahak dan melonggarkan jalan napas bagi penderita asma, PPOK, atau lansia dengan batuk berlendir.',
        'inclusions' => [
            'Nebulisasi obat dengan perangkat higienis steril',
            'Fisioterapi dada ringan & clapping postural',
            'Pengecekan saturasi oksigen (SpO2) akurat',
            'Edukasi teknik latihan napas dalam efektif'
        ]
    ],
    [
        'id' => 5,
        'title' => 'Pemasangan & Penggantian Selang NGT (Makan)',
        'category' => 'Prosedur Medis',
        'categoryBadge' => 'Prosedur Medis',
        'badgeColor' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
        'duration' => '45 - 60 Menit',
        'imageEmoji' => '🥣',
        'desc' => 'Insersi selang enteral lambung dengan pengujian keabsahan posisi lambung (auskultasi & aspirasi cairan) untuk pasien disfagia, lansia, atau pascastroke.',
        'inclusions' => [
            'Pemasangan selang NGT steril & fiksasi aman',
            'Uji auskultasi kepastian posisi lambung',
            'Pembilasan dan sanitasi selang enteral',
            'Bimbingan takaran & jadwal nutrisi keluarga'
        ]
    ],
    [
        'id' => 6,
        'title' => 'Pemeriksaan Tanda Vital & Skrining Rutin',
        'category' => 'Pemeriksaan Rutin',
        'categoryBadge' => 'Skrining Rutin',
        'badgeColor' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'duration' => '30 - 45 Menit',
        'imageEmoji' => '❤️‍🩹',
        'desc' => 'Pengecekan komprehensif kondisi fisiologis meliputi tekanan darah digital, denyut nadi, laju respirasi, saturasi oksigen, serta cek cepat gula darah kapiler dan asam urat.',
        'inclusions' => [
            'Pemeriksaan tensimeter digital terkalibrasi',
            'Skrining cepat gula darah kapiler & kolesterol',
            'Pencatatan grafik tren vital di E-Report',
            'Konsultasi rujukan & rekomendasi gaya hidup'
        ]
    ],
    [
        'id' => 7,
        'title' => 'Fisioterapi & Mobilisasi Pasca Stroke',
        'category' => 'Rehabilitasi & Lansia',
        'categoryBadge' => 'Rehabilitasi',
        'badgeColor' => 'bg-purple-50 text-purple-700 border-purple-200',
        'duration' => '60 Menit',
        'imageEmoji' => '🚶‍♂️',
        'desc' => 'Program latihan gerak fungsional Range of Motion (ROM) pasif/aktif di tempat tidur untuk melatih kelenturan sendi, mencegah kekakuan, dan menstimulasi pemulihan motorik.',
        'inclusions' => [
            'Latihan mobilisasi sendi bertahap (ROM)',
            'Teknik alih baring miring berkala (anti-dekubitus)',
            'Stimulasi gerak fungsional & latihan duduk',
            'Panduan teknik transfer pasien yang aman bagi keluarga'
        ]
    ],
    [
        'id' => 8,
        'title' => 'Perawatan Bayi Baru Lahir & Ibu Nifas',
        'category' => 'Perawatan Khusus',
        'categoryBadge' => 'Ibu & Anak',
        'badgeColor' => 'bg-pink-50 text-pink-700 border-pink-200',
        'duration' => '45 - 60 Menit',
        'imageEmoji' => '👶',
        'desc' => 'Asuhan keperawatan neonatal maternitas ramah dan steril meliputi memandikan bayi baru lahir, perawatan tali pusar, pemeriksaan luka nifas, dan panduan laktasi ibu.',
        'inclusions' => [
            'Memandikan bayi & perawatan tali pusar aseptik',
            'Pijat relaksasi lembut untuk stimulasi bayi',
            'Pemeriksaan luka jahitan melahirkan / caesar',
            'Konseling laktasi & posisi menyusui nyaman'
        ]
    ],
    [
        'id' => 9,
        'title' => 'Pendampingan Home Care Lansia (Geriatri)',
        'category' => 'Rehabilitasi & Lansia',
        'categoryBadge' => 'Home Care Lansia',
        'badgeColor' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
        'duration' => '2 - 4 Jam / Sesi',
        'imageEmoji' => '👵',
        'desc' => 'Pendampingan asuhan keperawatan penuh kasih untuk lansia dengan keterbatasan fisik, pemantauan minum obat terjadwal, bantuan kebersihan diri, dan pendampingan harian.',
        'inclusions' => [
            'Bantuan personal hygiene & mandi aman di rumah',
            'Pengawasan kepatuhan minum obat terjadwal',
            'Pemantauan asupan cairan & nutrisi harian',
            'Pendampingan komunikasi terapeutik & stimulasi ringan'
        ]
    ]
];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>CareAtHome Medical - Layanan Perawat Medis On-Demand Datang ke Rumah Anda</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet" />

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
    </style>

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 text-slate-800 font-sans antialiased selection:bg-emerald-500 selection:text-white"
    x-data="{
        filterCategory: 'Semua',
        searchQuery: '',
        bookingModalOpen: false,
        selectedService: null,
        simulationModalOpen: false,
        services: {{ Js::from($services) }},

        openBooking(service) {
            this.selectedService = service;
            this.bookingModalOpen = true;
        },

        filteredServices() {
            const query = this.searchQuery.toLowerCase().trim();
            return this.services.filter(service => {
                const matchCategory = (this.filterCategory === 'Semua' || service.category === this.filterCategory);
                const matchSearch = !query || 
                    service.title.toLowerCase().includes(query) || 
                    service.desc.toLowerCase().includes(query) ||
                    service.category.toLowerCase().includes(query) ||
                    service.inclusions.some(inc => inc.toLowerCase().includes(query));
                return matchCategory && matchSearch;
            });
        }
    }">

    <!-- NAVBAR (HOMECARE MEDICAL HEADER) -->
    <header class="bg-[#04281e]/90 backdrop-blur-md border-b border-emerald-900/50 sticky top-0 z-40 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">

            <!-- Logo -->
            <a href="/" class="flex items-center gap-3 group">
                <x-application-logo />
            </a>

            <!-- Desktop Nav Links -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-emerald-100/90">
                <a href="#katalog" class="hover:text-emerald-400 transition-colors flex items-center gap-1.5">
                    <span>Katalog Tindakan</span>
                </a>
                <a href="#alur" class="hover:text-emerald-400 transition-colors flex items-center gap-1.5">
                    <span>Alur Layanan</span>
                </a>
                <a href="#escrow" class="hover:text-emerald-400 transition-colors flex items-center gap-1.5">
                    <span>Keamanan & Escrow</span>
                </a>
                <button type="button" @click="simulationModalOpen = true"
                    class="px-3 py-1.5 rounded-lg bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 border border-emerald-400/40 text-xs font-bold transition flex items-center gap-1.5">
                    <span>⚡ Simulasi Akun (Demo)</span>
                </button>
            </nav>

            <!-- Auth Buttons -->
            <div class="flex items-center gap-3">
                @if (Route::has('login'))
                    @auth
                        <!-- Authenticated User Menu -->
                        <div class="flex items-center gap-3">
                            <span class="hidden sm:inline-block text-xs font-medium text-emerald-200">
                                Halo, <strong class="text-white">{{ Auth::user()->name }}</strong>
                            </span>

                            @if (Auth::user()->hasRole('perawat'))
                                <a href="{{ route('perawat.verifikasi') }}"
                                    class="px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-900 font-bold text-xs shadow transition">
                                    Verifikasi STR/SIP
                                </a>
                            @else
                                <a href="{{ route('dashboard') }}"
                                    class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-700/30 transition">
                                    Dashboard
                                </a>
                            @endif

                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit"
                                    class="px-3 py-2 text-xs font-semibold text-emerald-300 hover:text-white transition">
                                    Keluar
                                </button>
                            </form>
                        </div>
                    @else
                        <!-- Guest Menu -->
                        <a href="{{ route('login') }}"
                            class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold text-emerald-100 hover:text-white hover:bg-emerald-800/40 transition"
                            wire:navigate>
                            Masuk
                        </a>

                        <a href="{{ route('register') }}"
                            class="px-4 sm:px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-extrabold text-xs sm:text-sm shadow-md shadow-emerald-500/25 transition transform hover:-translate-y-0.5"
                            wire:navigate>
                            Daftar Sekarang
                        </a>
                    @endauth
                @endif
            </div>
        </div>
    </header>

    <!-- 3. HERO SECTION (DEEP EMERALD FOREST GRADIENT) -->
    <section
        class="relative bg-gradient-to-b from-[#064e3b] via-[#033a2c] to-[#022c22] text-white pt-16 pb-24 px-4 sm:px-6 lg:px-8 overflow-hidden border-b border-emerald-800/40">

        <!-- Ambient Radial Glows -->
        <div
            class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[400px] bg-emerald-400/10 rounded-full blur-3xl pointer-events-none">
        </div>
        <div class="absolute -top-10 -right-10 w-96 h-96 bg-teal-500/10 rounded-full blur-3xl pointer-events-none">
        </div>

        <div class="max-w-5xl mx-auto text-center relative z-10 space-y-8">


            <!-- Main Headline -->
            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-[1.15] text-white">
                Layanan Perawat Medis Bersertifikasi Datang ke Rumah Anda
            </h1>

            <!-- Description -->
            <p class="max-w-3xl mx-auto text-base sm:text-lg text-emerald-100/90 leading-relaxed font-normal">
                Akses perawatan medis profesional, steril, dan berizin resmi (STR/SIP) untuk pemulihan optimal keluarga
                tercinta di rumah dengan sistem pembayaran aman bergaransi escrow.
            </p>

            <!-- Feature Badges -->
            <div class="flex flex-wrap items-center justify-center gap-3 sm:gap-4 pt-2">
                <div
                    class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 backdrop-blur-md border border-white/15 text-xs sm:text-sm font-semibold text-emerald-100 shadow-sm">
                    <span class="text-emerald-400 font-bold">✓</span>
                    <span>100% Nakes STR & SIP Valid</span>
                </div>
                <div
                    class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 backdrop-blur-md border border-white/15 text-xs sm:text-sm font-semibold text-emerald-100 shadow-sm">
                    <span class="text-emerald-400 font-bold">✓</span>
                    <span>Kunjungan Terjadwal Fleksibel</span>
                </div>
                <div
                    class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 backdrop-blur-md border border-white/15 text-xs sm:text-sm font-semibold text-emerald-100 shadow-sm">
                    <span class="text-emerald-400 font-bold">✓</span>
                    <span>E-Report Medis Digital Lengkap</span>
                </div>
            </div>

            <!-- Primary Call to Action Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
                <a href="#katalog"
                    class="w-full sm:w-auto px-8 py-4 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-600 hover:to-teal-500 text-slate-950 font-extrabold text-sm sm:text-base tracking-wide shadow-xl shadow-emerald-500/30 transform hover:-translate-y-0.5 transition flex items-center justify-center gap-2.5">
                    <span>Pilih Layanan Keperawatan</span>
                    <svg class="w-5 h-5 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7">
                        </path>
                    </svg>
                </a>

                <button type="button" @click="simulationModalOpen = true"
                    class="w-full sm:w-auto px-6 py-4 rounded-xl bg-emerald-950/60 hover:bg-emerald-900/80 text-white font-bold text-sm sm:text-base border border-emerald-400/40 backdrop-blur-md transition flex items-center justify-center gap-2">
                    <span>⚡ Coba Simulasi Akun (Demo)</span>
                </button>
            </div>

            <!-- Stats Bar -->
            <div
                class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-12 max-w-4xl mx-auto border-t border-emerald-800/40 text-center">
                <div class="p-3">
                    <div class="text-2xl sm:text-3xl font-extrabold text-emerald-400">1.200+</div>
                    <div class="text-xs text-emerald-200/80 mt-1">Kunjungan Berhasil</div>
                </div>
                <div class="p-3">
                    <div class="text-2xl sm:text-3xl font-extrabold text-emerald-400">100%</div>
                    <div class="text-xs text-emerald-200/80 mt-1">Nakes Berizin STR & SIP</div>
                </div>
                <div class="p-3">
                    <div class="text-2xl sm:text-3xl font-extrabold text-emerald-400">4.9 / 5.0</div>
                    <div class="text-xs text-emerald-200/80 mt-1">Rating Kepuasan Pasien</div>
                </div>
                <div class="p-3">
                    <div class="text-2xl sm:text-3xl font-extrabold text-emerald-400">Escrow</div>
                    <div class="text-xs text-emerald-200/80 mt-1">Garansi Transaksi Aman</div>
                </div>
            </div>

        </div>
    </section>

    <!-- 4. EDUKASI 4 LANGKAH PEMANGGILAN PERAWAT (PROPOSAL BAB IV & FIGMA MOCKUP) -->
    <section id="alur" class="py-20 px-4 sm:px-6 lg:px-8 bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto">

            <div class="text-center max-w-3xl mx-auto mb-16">
                <span
                    class="text-xs font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                    Standar Prosedur Operasional
                </span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mt-3">
                    4 Langkah Praktis Memanggil Perawat ke Rumah
                </h2>
                <p class="text-slate-600 mt-2 text-sm sm:text-base">
                    Alur pemesanan dirancang terstruktur, transparan, dan terintegrasi sistem jaminan mutu asuhan
                    keperawatan
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 relative">

                <!-- Step 1 -->
                <div
                    class="bg-slate-50 rounded-2xl p-6 border border-slate-200 hover:border-emerald-500 hover:shadow-lg transition group">
                    <div
                        class="w-12 h-12 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-extrabold text-lg shadow-md shadow-emerald-600/30 mb-5 group-hover:scale-110 transition-transform">
                        1
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900 mb-2">Pilih Tindakan & Jadwal</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Tentukan jenis tindakan medis dari katalog, tanggal kunjungan, serta alamat domisili pasien.
                    </p>
                </div>

                <!-- Step 2 -->
                <div
                    class="bg-slate-50 rounded-2xl p-6 border border-slate-200 hover:border-emerald-500 hover:shadow-lg transition group">
                    <div
                        class="w-12 h-12 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-extrabold text-lg shadow-md shadow-emerald-600/30 mb-5 group-hover:scale-110 transition-transform">
                        2
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900 mb-2">Pembayaran Escrow</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Dana diamankan di rekening penampung (escrow). Perawat belum menerima pembayaran sebelum
                        tindakan selesai.
                    </p>
                </div>

                <!-- Step 3 -->
                <div
                    class="bg-slate-50 rounded-2xl p-6 border border-slate-200 hover:border-emerald-500 hover:shadow-lg transition group">
                    <div
                        class="w-12 h-12 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-extrabold text-lg shadow-md shadow-emerald-600/30 mb-5 group-hover:scale-110 transition-transform">
                        3
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900 mb-2">Tindakan Medis Steril</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Perawat ber-STR/SIP hadir tepat waktu ke rumah pasien dengan peralatan standar aseptik medis.
                    </p>
                </div>

                <!-- Step 4 -->
                <div
                    class="bg-slate-50 rounded-2xl p-6 border border-slate-200 hover:border-emerald-500 hover:shadow-lg transition group">
                    <div
                        class="w-12 h-12 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-extrabold text-lg shadow-md shadow-emerald-600/30 mb-5 group-hover:scale-110 transition-transform">
                        4
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900 mb-2">E-Report & Konfirmasi</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Perawat mengisi E-Report klinis, pasien mengonfirmasi selesai & memberikan ulasan, lalu dana
                        diteruskan.
                    </p>
                </div>

            </div>

        </div>
    </section>

    <!-- 5. KATALOG LAYANAN KEPERAWATAN RUMAH SECTION -->
    <section id="katalog" class="py-20 px-4 sm:px-6 lg:px-8 bg-slate-100 border-b border-slate-200">
        <div class="max-w-7xl mx-auto space-y-10">

            <!-- Section Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div>
                    <span
                        class="text-xs font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-3.5 py-1.5 rounded-full border border-emerald-200 inline-flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                        Layanan Medis Resmi Berlisensi
                    </span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mt-3">
                        Katalog Layanan Keperawatan Rumah
                    </h2>
                    <p class="text-slate-600 mt-1.5 text-sm sm:text-base">
                        Pilihan asuhan keperawatan medis profesional langsung ke rumah oleh nakes berizin resmi (STR &amp; SIP).
                    </p>
                </div>

                <!-- Category Filter Tabs -->
                <div
                    class="inline-flex p-1.5 bg-white rounded-2xl shadow-sm border border-slate-200 self-start md:self-auto overflow-x-auto max-w-full">
                    <button type="button" @click="filterCategory = 'Semua'"
                        :class="filterCategory === 'Semua' ? 'bg-emerald-600 text-white shadow-sm' :
                            'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap">
                        Semua Layanan
                    </button>
                    <button type="button" @click="filterCategory = 'Perawatan Khusus'"
                        :class="filterCategory === 'Perawatan Khusus' ? 'bg-emerald-600 text-white shadow-sm' :
                            'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap">
                        Perawatan Khusus
                    </button>
                    <button type="button" @click="filterCategory = 'Prosedur Medis'"
                        :class="filterCategory === 'Prosedur Medis' ? 'bg-emerald-600 text-white shadow-sm' :
                            'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap">
                        Prosedur Medis
                    </button>
                    <button type="button" @click="filterCategory = 'Terapi & Injeksi'"
                        :class="filterCategory === 'Terapi & Injeksi' ? 'bg-emerald-600 text-white shadow-sm' :
                            'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap">
                        Terapi &amp; Injeksi
                    </button>
                    <button type="button" @click="filterCategory = 'Rehabilitasi & Lansia'"
                        :class="filterCategory === 'Rehabilitasi & Lansia' ? 'bg-emerald-600 text-white shadow-sm' :
                            'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap">
                        Rehabilitasi &amp; Lansia
                    </button>
                </div>
            </div>

            <!-- Search and Results Count Bar (No Price Slider) -->
            <div
                class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/90 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">

                <!-- Search Input -->
                <div class="relative flex-1 w-full">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </span>
                    <input type="text" x-model="searchQuery"
                        placeholder="Cari layanan keperawatan (contoh: luka, kateter, infus, nebulizer, stroke, bayi)..."
                        class="w-full pl-10 pr-10 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition placeholder:text-slate-400" />

                    <button type="button" x-show="searchQuery.length > 0" @click="searchQuery = ''"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600">
                        <span class="text-sm font-bold">&times;</span>
                    </button>
                </div>

                <!-- Count Badge -->
                <div class="flex items-center gap-2 shrink-0">
                    <span
                        class="px-3.5 py-2 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-bold text-emerald-800 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Menampilkan <strong class="text-emerald-950 font-extrabold"
                                x-text="filteredServices().length"></strong> Layanan</span>
                    </span>
                </div>
            </div>

            <!-- Service Cards Grid (Pre-rendered via Blade + Alpine Interactive Filtering) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                @foreach ($services as $item)
                    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm hover:shadow-xl hover:border-emerald-500/50 hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between overflow-hidden group"
                        x-show="(filterCategory === 'Semua' || filterCategory === '{{ $item['category'] }}') && (!searchQuery || '{{ strtolower($item['title'] . ' ' . $item['desc'] . ' ' . implode(' ', $item['inclusions'])) }}'.includes(searchQuery.toLowerCase().trim()))">

                        <!-- Top Card Body -->
                        <div class="p-6 space-y-4">

                            <!-- Badges Row -->
                            <div class="flex items-center justify-between gap-2">
                                <span class="{{ $item['badgeColor'] }} text-[10px] font-extrabold px-3 py-1 rounded-full border uppercase tracking-wider">
                                    {{ $item['categoryBadge'] }}
                                </span>

                                <span class="text-xs text-slate-500 font-semibold flex items-center gap-1 bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-100">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>{{ $item['duration'] }}</span>
                                </span>
                            </div>

                            <!-- Title & Emoji -->
                            <div class="flex items-start gap-3.5">
                                <span
                                    class="text-3xl p-2.5 rounded-2xl bg-emerald-50/70 border border-emerald-100/80 shrink-0 group-hover:scale-110 group-hover:bg-emerald-100/70 transition-transform">
                                    {{ $item['imageEmoji'] }}
                                </span>
                                <div>
                                    <h3 class="font-extrabold text-base sm:text-lg text-slate-900 group-hover:text-emerald-700 transition leading-snug">
                                        {{ $item['title'] }}
                                    </h3>
                                </div>
                            </div>

                            <!-- Description -->
                            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                                {{ $item['desc'] }}
                            </p>

                            <!-- Inclusions List -->
                            <div class="pt-3 border-t border-slate-100 space-y-2">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Cakupan Asuhan Medis:</p>
                                <ul class="space-y-1.5 text-xs text-slate-700">
                                    @foreach ($item['inclusions'] as $inc)
                                        <li class="flex items-start gap-2">
                                            <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clip-rule="evenodd"></path>
                                            </svg>
                                            <span class="leading-tight">{{ $inc }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <!-- Bottom Card Footer (No Price - Clean Trust Badge & Action Button) -->
                        <div class="p-5 bg-slate-50/90 border-t border-slate-100 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-1.5 text-[11px] font-bold text-emerald-800 bg-emerald-50 px-2.5 py-1.5 rounded-xl border border-emerald-200/60">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span>Nakes STR &amp; SIP</span>
                            </div>

                            <button type="button" @click="openBooking({{ Js::from($item) }})"
                                class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-extrabold text-xs shadow-md shadow-emerald-600/25 transition transform active:scale-95 flex items-center gap-1.5 group-hover:shadow-lg">
                                <span>Pesan Layanan</span>
                                <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </div>

                    </div>
                @endforeach

            </div>

            <!-- Empty State if no filter matches -->
            <div x-show="filteredServices().length === 0"
                class="text-center py-16 bg-white rounded-3xl border border-slate-200">
                <p class="text-4xl mb-3">🔍</p>
                <h4 class="font-extrabold text-slate-800 text-base">Tidak ada tindakan yang cocok</h4>
                <p class="text-xs text-slate-500 mt-1">Coba sesuaikan kata kunci pencarian atau ubah kategori filter.</p>
                <button @click="searchQuery = ''; filterCategory = 'Semua'"
                    class="mt-4 px-5 py-2.5 rounded-xl bg-emerald-50 text-emerald-700 font-bold text-xs hover:bg-emerald-100 transition border border-emerald-200">
                    Reset Filter
                </button>
            </div>

        </div>
    </section>

    <!-- 6. ESCROW PROTECTION & LEGALITAS MEDIS (Sistem Keamanan & Kepatuhan Medis Saja - Tanpa Kolom Invoice) -->
    <section id="escrow"
        class="py-24 px-4 sm:px-6 lg:px-8 bg-gradient-to-b from-[#022c22] via-[#043328] to-[#02241c] text-white relative overflow-hidden">

        <!-- Ambient Radial Glows -->
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-teal-400/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto relative z-10 space-y-12">

            <!-- Section Title & Subtitle Centered -->
            <div class="max-w-3xl mx-auto text-center space-y-4">
                <div
                    class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-bold border border-emerald-400/30">
                    <span>🛡️</span>
                    <span>Sistem Keamanan &amp; Kepatuhan Medis</span>
                </div>
                
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight">
                    Jaminan Perlindungan Pasien Melalui Rekening Penampung (Escrow)
                </h2>

                <p class="text-emerald-100/90 text-sm sm:text-base leading-relaxed">
                    Di CareAtHome, keselamatan pasien dan transparansi adalah prioritas nomor satu. Pembayaran
                    Anda tidak langsung diberikan ke perawat, melainkan dikunci sementara oleh platform hingga pelayanan medis selesai dan terverifikasi secara klinis.
                </p>
            </div>

            <!-- 4 Keunggulan Sistem Keamanan & Kepatuhan Medis (Full-Width Responsive Grid) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 pt-4">

                <!-- Card 1 -->
                <div class="p-6 rounded-3xl bg-white/5 border border-white/10 hover:border-emerald-400/40 hover:bg-white/10 transition-all group flex flex-col justify-between space-y-4">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 text-emerald-300 flex items-center justify-center text-2xl mb-4 group-hover:scale-110 transition-transform border border-emerald-400/30">
                            🔒
                        </div>
                        <h4 class="font-extrabold text-base text-white group-hover:text-emerald-300 transition">
                            Dana Terkunci Aman (Escrow)
                        </h4>
                        <p class="text-xs text-emerald-100/80 mt-2 leading-relaxed">
                            Dana pembayaran Anda diamankan di rekening penampung resmi. Nakes baru menerima pembayaran setelah pasien mengonfirmasi selesai dan memeriksa E-Report.
                        </p>
                    </div>
                    <div class="pt-3 border-t border-white/10 text-[11px] font-bold text-emerald-400 flex items-center gap-1.5">
                        <span>✓</span>
                        <span>Bebas Risiko Penipuan</span>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="p-6 rounded-3xl bg-white/5 border border-white/10 hover:border-emerald-400/40 hover:bg-white/10 transition-all group flex flex-col justify-between space-y-4">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-teal-500/20 text-teal-300 flex items-center justify-center text-2xl mb-4 group-hover:scale-110 transition-transform border border-teal-400/30">
                            🩺
                        </div>
                        <h4 class="font-extrabold text-base text-white group-hover:text-teal-300 transition">
                            Verifikasi STR &amp; SIP Multi-Tahap
                        </h4>
                        <p class="text-xs text-emerald-100/80 mt-2 leading-relaxed">
                            Admin mengaudit keabsahan STR nakes langsung ke portal Konsil Tenaga Kesehatan Indonesia (KTKI) sebelum perawat dapat menerima pesanan.
                        </p>
                    </div>
                    <div class="pt-3 border-t border-white/10 text-[11px] font-bold text-teal-400 flex items-center gap-1.5">
                        <span>✓</span>
                        <span>100% Nakes Berizin Aktif</span>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="p-6 rounded-3xl bg-white/5 border border-white/10 hover:border-emerald-400/40 hover:bg-white/10 transition-all group flex flex-col justify-between space-y-4">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-sky-500/20 text-sky-300 flex items-center justify-center text-2xl mb-4 group-hover:scale-110 transition-transform border border-sky-400/30">
                            📑
                        </div>
                        <h4 class="font-extrabold text-base text-white group-hover:text-sky-300 transition">
                            Rekam Asuhan Digital (E-Report)
                        </h4>
                        <p class="text-xs text-emerald-100/80 mt-2 leading-relaxed">
                            Setiap kunjungan wajib didokumentasikan dengan tanda-tanda vital, tindakan aseptik, dan foto luka yang transparan serta dapat dipantau keluarga.
                        </p>
                    </div>
                    <div class="pt-3 border-t border-white/10 text-[11px] font-bold text-sky-400 flex items-center gap-1.5">
                        <span>✓</span>
                        <span>Transparan &amp; Terdokumentasi</span>
                    </div>
                </div>

                <!-- Card 4 -->
                <div class="p-6 rounded-3xl bg-white/5 border border-white/10 hover:border-emerald-400/40 hover:bg-white/10 transition-all group flex flex-col justify-between space-y-4">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 text-emerald-300 flex items-center justify-center text-2xl mb-4 group-hover:scale-110 transition-transform border border-emerald-400/30">
                            🤝
                        </div>
                        <h4 class="font-extrabold text-base text-white group-hover:text-emerald-300 transition">
                            Garansi Penggantian Nakes
                        </h4>
                        <p class="text-xs text-emerald-100/80 mt-2 leading-relaxed">
                            Jika perawat berhalangan hadir atau ada kendala klinis, platform menjamin penugasan nakes pengganti atau pengembalian dana penuh tanpa potongan.
                        </p>
                    </div>
                    <div class="pt-3 border-t border-white/10 text-[11px] font-bold text-emerald-400 flex items-center gap-1.5">
                        <span>✓</span>
                        <span>Garansi 100% Kepuasan Pasien</span>
                    </div>
                </div>

            </div>

            <!-- Trust Highlights Footer Bar -->
            <div class="pt-6 border-t border-emerald-900/60 flex flex-col sm:flex-row items-center justify-between gap-6">
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-4 sm:gap-6 text-xs text-emerald-200/90 font-semibold">
                    <span class="flex items-center gap-1.5">
                        <span class="text-emerald-400">🔒</span> Enkripsi Data Medis AES-256
                    </span>
                    <span class="hidden sm:inline text-emerald-700">&bull;</span>
                    <span class="flex items-center gap-1.5">
                        <span class="text-emerald-400">🏛️</span> Kepatuhan UU PDP No. 27/2022
                    </span>
                    <span class="hidden sm:inline text-emerald-700">&bull;</span>
                    <span class="flex items-center gap-1.5">
                        <span class="text-emerald-400">📋</span> Standar SOP Medis Kemenkes RI
                    </span>
                </div>

                <a href="#katalog"
                    class="px-6 py-3 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-600 hover:to-teal-500 text-slate-950 font-extrabold text-xs sm:text-sm tracking-wide shadow-lg shadow-emerald-500/25 transition transform hover:-translate-y-0.5 inline-flex items-center gap-2">
                    <span>Pilih Layanan Keperawatan</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </a>
            </div>

        </div>
    </section>

    <!-- 7. SIMULASI AKUN DEMO MODAL (PASIEN & PERAWAT SAJA - ADMIN DINONAKTIFKAN DEMI KEAMANAN DATA) -->
    <div x-show="simulationModalOpen" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
        @keydown.escape.window="simulationModalOpen = false">

        <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-emerald-500/30 text-slate-800 relative space-y-6"
            @click.away="simulationModalOpen = false">

            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-2.5">
                    <span class="text-2xl">⚡</span>
                    <div>
                        <h3 class="font-extrabold text-base text-slate-900">Simulasi Akun Demo</h3>
                        <p class="text-xs text-slate-500">Coba alur pemesanan dan layanan langsung (Pasien &amp; Perawat)</p>
                    </div>
                </div>

                <button @click="simulationModalOpen = false"
                    class="text-slate-400 hover:text-slate-700 text-xl font-bold p-1">
                    &times;
                </button>
            </div>

            <!-- Role Cards (Pasien & Perawat Saja - Admin Tidak Disediakan Demo) -->
            <div class="space-y-3">

                <!-- 1. Pasien -->
                <div
                    class="p-4 rounded-2xl border-2 border-emerald-200 hover:border-emerald-500 bg-emerald-50/40 transition flex items-center justify-between gap-4">
                    <div class="space-y-0.5">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">👤</span>
                            <h4 class="font-extrabold text-sm text-slate-900">Peran: Pasien (Patient)</h4>
                            <span
                                class="text-[10px] bg-emerald-600 text-white font-bold px-2 py-0.5 rounded-full">Demo</span>
                        </div>
                        <p class="text-xs text-slate-600">Ahmad Fauzi &bull; <code>pasien@careathome.com</code></p>
                        <p class="text-[11px] text-emerald-800">Coba: Pesan layanan, simulasi escrow, cek E-Report.</p>
                    </div>
                    <a href="{{ route('demo.login', 'pasien') }}"
                        class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shrink-0 transition shadow-sm">
                        Masuk Pasien
                    </a>
                </div>

                <!-- 2. Perawat -->
                <div
                    class="p-4 rounded-2xl border-2 border-teal-200 hover:border-teal-500 bg-teal-50/40 transition flex items-center justify-between gap-4">
                    <div class="space-y-0.5">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🩺</span>
                            <h4 class="font-extrabold text-sm text-slate-900">Peran: Perawat (Nurse)</h4>
                            <span
                                class="text-[10px] bg-teal-600 text-white font-bold px-2 py-0.5 rounded-full">Demo</span>
                        </div>
                        <p class="text-xs text-slate-600">Ns. Siti Nurhaliza &bull; <code>perawat@careathome.com</code>
                        </p>
                        <p class="text-[11px] text-teal-800">Coba: Verifikasi STR/SIP, toggle status hadir, E-Report klinis.</p>
                    </div>
                    <a href="{{ route('demo.login', 'perawat') }}"
                        class="px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shrink-0 transition shadow-sm">
                        Masuk Perawat
                    </a>
                </div>

            </div>

            <!-- Security Notice regarding Admin -->
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-[11px] text-slate-500 leading-relaxed flex items-start gap-2">
                <span class="text-slate-400 mt-0.5">🔒</span>
                <span><strong>Catatan Keamanan:</strong> Akun Administrator dilindungi privilese penuh dan tidak disediakan dalam simulasi instan demi menjaga integritas data medis dan keuangan.</span>
            </div>

            <div class="text-center pt-1">
                <button @click="simulationModalOpen = false"
                    class="text-xs text-slate-500 hover:text-slate-700 font-semibold underline">
                    Tutup Simulasi
                </button>
            </div>
        </div>
    </div>

    <!-- 8. BOOKING PREVIEW MODAL -->
    <div x-show="bookingModalOpen" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
        @keydown.escape.window="bookingModalOpen = false">

        <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-emerald-500/30 text-slate-800 relative space-y-5"
            @click.away="bookingModalOpen = false">

            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <span class="text-3xl p-2 rounded-2xl bg-emerald-50 border border-emerald-100" x-text="selectedService?.imageEmoji"></span>
                    <div>
                        <h3 class="font-extrabold text-base text-slate-900" x-text="selectedService?.title"></h3>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200" x-text="selectedService?.categoryBadge"></span>
                            <span class="text-xs text-slate-500" x-text="'Durasi: ' + selectedService?.duration"></span>
                        </div>
                    </div>
                </div>

                <button @click="bookingModalOpen = false"
                    class="text-slate-400 hover:text-slate-700 text-2xl font-bold p-1">
                    &times;
                </button>
            </div>

            <div class="space-y-4 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Rencana Tanggal &amp; Waktu
                        Kunjungan</label>
                    <input type="datetime-local"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-emerald-500 focus:border-emerald-500"
                        value="2026-10-21T09:00" />
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Titik Lokasi &amp; Alamat
                        Kunjungan</label>
                    <input type="text" placeholder="Masukkan alamat lengkap rumah pasien..."
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-emerald-500 focus:border-emerald-500"
                        value="Jl. Dago Asri No. 12, Bandung (Jarak estimasi: 3.2 km)" />
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Catatan Kondisi Klinis
                        Awal Pasien</label>
                    <textarea rows="2" placeholder="Tuliskan keluhan atau instruksi khusus bagi perawat..."
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                </div>

                <!-- Clean Escrow Guarantee Note (No Price Breakdown) -->
                <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200/80 space-y-1.5">
                    <div class="flex items-center gap-2 text-xs font-bold text-emerald-900">
                        <span>🛡️</span>
                        <span>Jaminan Perlindungan Pasien Bergaransi Escrow</span>
                    </div>
                    <p class="text-[11px] text-emerald-800 leading-relaxed">
                        Perawat ber-STR &amp; SIP resmi terdekat akan segera ditugaskan ke lokasi Anda setelah pemesanan. Pembayaran Anda terlindungi aman di rekening penampung (escrow) hingga tindakan selesai dan terverifikasi di E-Report.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button @click="bookingModalOpen = false"
                    class="w-1/2 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-50 transition">
                    Batal
                </button>
                <a href="{{ route('register') }}"
                    class="w-1/2 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-extrabold text-xs text-center shadow-md shadow-emerald-600/30 transition">
                    Lanjut Pesan Sekarang
                </a>
            </div>
        </div>
    </div>

    <!-- 9. MULTI-COLUMN DARK FOOTER (#0b1329 / #0f172a) -->
    <footer
        class="bg-gradient-to-b from-[#0b1329] to-[#060a17] text-slate-300 pt-16 pb-12 border-t border-emerald-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">

            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">

                <!-- Brand Column -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <x-application-logo />
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Platform health-tech on-demand berbasis web yang menghubungkan pasien dengan perawat medis
                        bersertifikasi (STR & SIP) secara aman melalui sistem garansi rekening penampung (escrow).
                    </p>
                </div>

                <!-- Layanan Medis -->
                <div class="space-y-3">
                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-white">Layanan Tindakan</h4>
                    <ul class="space-y-2 text-xs text-slate-400">
                        <li><a href="#katalog" class="hover:text-emerald-400 transition">Perawatan Luka Diabetes</a>
                        </li>
                        <li><a href="#katalog" class="hover:text-emerald-400 transition">Pemasangan Kateter Urine</a>
                        </li>
                        <li><a href="#katalog" class="hover:text-emerald-400 transition">Pemberian Terapi Infus &
                                Injeksi</a></li>
                        <li><a href="#katalog" class="hover:text-emerald-400 transition">Terapi Uap Nebulizer</a></li>
                        <li><a href="#katalog" class="hover:text-emerald-400 transition">Pemasangan Selang NGT</a>
                        </li>
                    </ul>
                </div>

                <!-- Legalitas & Keamanan -->
                <div class="space-y-3">
                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-white">Legalitas & Kepatuhan</h4>
                    <ul class="space-y-2 text-xs text-slate-400">
                        <li><span class="hover:text-emerald-400 transition">Audit STR & SIP Konsil Tenaga
                                Kesehatan</span></li>
                        <li><span class="hover:text-emerald-400 transition">Kepatuhan UU PDP No. 27 Tahun 2022</span>
                        </li>
                        <li><span class="hover:text-emerald-400 transition">Enkripsi Rekam Medis Digital AES-256</span>
                        </li>
                        <li><span class="hover:text-emerald-400 transition">Garansi Rekening Escrow Midtrans</span>
                        </li>
                    </ul>
                </div>

                <!-- Kontak & Disclaimer -->
                <div class="space-y-3">
                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-white">Batasan Layanan</h4>
                    <p class="text-[11px] text-slate-400 leading-relaxed">
                        <strong class="text-amber-400">Peringatan:</strong> CareAtHome hanya melayani tindakan
                        keperawatan non-kritis dan terencana. Kami <em>tidak melayani</em> kegawatdaruratan
                        (emergency/ICU). Jika darurat, segera hubungi 119 atau IGD terdekat.
                    </p>
                    <div class="pt-2">
                        <button @click="simulationModalOpen = true"
                            class="text-xs text-emerald-400 hover:text-emerald-300 font-bold underline">
                            Buka Menu Simulasi Akun Demo &rarr;
                        </button>
                    </div>
                </div>

            </div>

            <!-- Copyright -->
            <div
                class="pt-8 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
                <p>&copy; 2026 CareAtHome Medical (Kelompok 8 PPL). Seluruh Hak Cipta Dilindungi.</p>
                <div class="flex items-center gap-6">
                    <a href="#" class="hover:text-slate-300">Ketentuan Layanan</a>
                    <a href="#" class="hover:text-slate-300">Kebijakan Privasi</a>
                    <a href="#" class="hover:text-slate-300">Standar Etika Medis</a>
                </div>
            </div>

        </div>
    </footer>

</body>

</html>
