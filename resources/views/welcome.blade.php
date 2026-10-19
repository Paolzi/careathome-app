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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased selection:bg-emerald-500 selection:text-white" x-data="{
    filterCategory: 'Semua',
    searchQuery: '',
    maxPrice: 300000,
    bookingModalOpen: false,
    selectedService: null,
    simulationModalOpen: false,

    services: [
        {
            id: 1,
            title: 'Perawatan Luka Diabetes & Dekubitus',
            category: 'Intensif',
            levelBadge: 'TINGKAT INTENSIF',
            levelColor: 'bg-rose-100 text-rose-700 border-rose-200',
            duration: '60 - 90 Menit',
            price: 250000,
            priceFormatted: 'Rp 250.000',
            imageEmoji: '🩹',
            desc: 'Pembersihan nekrotik, debridement steril, dan dressing modern anti-mikroba untuk regenerasi jaringan luka kronis.',
            inclusions: ['Pembersihan antiseptik steril', 'Modern wound dressing', 'Pengecekan TTV & Gula Darah', 'Dokumentasi E-Report Foto Luka']
        },
        {
            id: 2,
            title: 'Pemasangan & Penggantian Kateter Urine',
            category: 'Menengah',
            levelBadge: 'TINGKAT MENENGAH',
            levelColor: 'bg-amber-100 text-amber-700 border-amber-200',
            duration: '45 - 60 Menit',
            price: 180000,
            priceFormatted: 'Rp 180.000',
            imageEmoji: '🩺',
            desc: 'Prosedur pemasangan Foley Catheter dengan teknik aseptik tinggi oleh nakes bersertifikat untuk mencegah infeksi saluran kemih.',
            inclusions: ['Teknik fiksasi steril', 'Kantung urin & selang steril', 'Pengecekan volume residu', 'Instruksi higienitas keluarga']
        },
        {
            id: 3,
            title: 'Pemberian Terapi Infus & Injeksi Berkala',
            category: 'Menengah',
            levelBadge: 'TINGKAT MENENGAH',
            levelColor: 'bg-amber-100 text-amber-700 border-amber-200',
            duration: '45 Menit',
            price: 150000,
            priceFormatted: 'Rp 150.000',
            imageEmoji: '💉',
            desc: 'Penggantian botol infus rehidrasi intravena atau injeksi multivitamin/obat sesuai instruksi resep tertulis dari dokter spesialis.',
            inclusions: ['Pemasangan abocath steril', 'Pengaturan tetesan infus mikro/makro', 'Pemeriksaan tanda vital pra/pasca', 'E-Report digital terbit']
        },
        {
            id: 4,
            title: 'Terapi Uap Nebulizer & Fisioterapi Dada',
            category: 'Dasar',
            levelBadge: 'TINGKAT DASAR',
            levelColor: 'bg-emerald-100 text-emerald-700 border-emerald-200',
            duration: '30 - 45 Menit',
            price: 120000,
            priceFormatted: 'Rp 120.000',
            imageEmoji: '🫁',
            desc: 'Pengenceran dahak dan pelonggaran jalan napas untuk penderita asma bronkial, PPOK, dan lansia dengan batuk berlendir.',
            inclusions: ['Perangkat nebulizer higienis', 'Clapping & vibrating postural', 'Pengecekan saturasi oksigen (SpO2)', 'Edukasi latihan napas dalam']
        },
        {
            id: 5,
            title: 'Pemasangan Selang Makan NGT (Nasogastric)',
            category: 'Intensif',
            levelBadge: 'TINGKAT INTENSIF',
            levelColor: 'bg-rose-100 text-rose-700 border-rose-200',
            duration: '60 Menit',
            price: 220000,
            priceFormatted: 'Rp 220.000',
            imageEmoji: '🥣',
            desc: 'Insersi selang enteral lambung dengan pengujian posisi aman (auskultasi & pH) untuk pasien dengan disfagia atau pascastroke.',
            inclusions: ['Pemasangan selang NGT steril', 'Auskultasi kepastian lambung', 'Edukasi jadwal pemberian susu/makanan', 'Pencatatan E-Report klinis']
        },
        {
            id: 6,
            title: 'Pemeriksaan Tanda Vital & Profil Darah Cepat',
            category: 'Dasar',
            levelBadge: 'TINGKAT DASAR',
            levelColor: 'bg-emerald-100 text-emerald-700 border-emerald-200',
            duration: '30 Menit',
            price: 85000,
            priceFormatted: 'Rp 85.000',
            imageEmoji: '❤️‍🩹',
            desc: 'Pengecekan komprehensif tekanan darah digital, saturasi oksigen (SpO2), laju nadi, temperatur, dan skrining gula darah kapiler.',
            inclusions: ['Tensimeter digital terkalibrasi', 'Tes gula darah strip steril', 'Grafik riwayat tanda vital', 'Konsultasi rujukan non-kritis']
        }
    ],

    openBooking(service) {
        this.selectedService = service;
        this.bookingModalOpen = true;
    },

    filteredServices() {
        return this.services.filter(service => {
            const matchCategory = (this.filterCategory === 'Semua' || service.category === this.filterCategory);
            const matchSearch = service.title.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                                service.desc.toLowerCase().includes(this.searchQuery.toLowerCase());
            const matchPrice = service.price <= this.maxPrice;
            return matchCategory && matchSearch && matchPrice;
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
                <button 
                    type="button"
                    @click="simulationModalOpen = true"
                    class="px-3 py-1.5 rounded-lg bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 border border-emerald-400/40 text-xs font-bold transition flex items-center gap-1.5">
                    <span>⚡ Simulasi Akun (3 Peran)</span>
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
                            
                            @if(Auth::user()->hasRole('perawat'))
                                <a href="{{ route('perawat.verifikasi') }}" class="px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-900 font-bold text-xs shadow transition">
                                    Verifikasi STR/SIP
                                </a>
                            @else
                                <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-700/30 transition">
                                    Dashboard
                                </a>
                            @endif

                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="px-3 py-2 text-xs font-semibold text-emerald-300 hover:text-white transition">
                                    Keluar
                                </button>
                            </form>
                        </div>
                    @else
                        <!-- Guest Menu -->
                        <a href="{{ route('login') }}" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold text-emerald-100 hover:text-white hover:bg-emerald-800/40 transition" wire:navigate>
                            Masuk
                        </a>

                        <a href="{{ route('register') }}" class="px-4 sm:px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-extrabold text-xs sm:text-sm shadow-md shadow-emerald-500/25 transition transform hover:-translate-y-0.5" wire:navigate>
                            Daftar Sekarang
                        </a>
                    @endauth
                @endif
            </div>
        </div>
    </header>

    <!-- 3. HERO SECTION (DEEP EMERALD FOREST GRADIENT) -->
    <section class="relative bg-gradient-to-b from-[#064e3b] via-[#033a2c] to-[#022c22] text-white pt-16 pb-24 px-4 sm:px-6 lg:px-8 overflow-hidden border-b border-emerald-800/40">
        
        <!-- Ambient Radial Glows -->
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[400px] bg-emerald-400/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -top-10 -right-10 w-96 h-96 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-5xl mx-auto text-center relative z-10 space-y-8">
            

            <!-- Main Headline -->
            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-[1.15] text-white">
                Layanan Perawat Medis Bersertifikasi Datang ke Rumah Anda
            </h1>

            <!-- Description -->
            <p class="max-w-3xl mx-auto text-base sm:text-lg text-emerald-100/90 leading-relaxed font-normal">
                Akses perawatan medis profesional, steril, dan berizin resmi (STR/SIP) untuk pemulihan optimal keluarga tercinta di rumah dengan sistem pembayaran aman bergaransi escrow.
            </p>

            <!-- Feature Badges -->
            <div class="flex flex-wrap items-center justify-center gap-3 sm:gap-4 pt-2">
                <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 backdrop-blur-md border border-white/15 text-xs sm:text-sm font-semibold text-emerald-100 shadow-sm">
                    <span class="text-emerald-400 font-bold">✓</span>
                    <span>100% Nakes STR & SIP Valid</span>
                </div>
                <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 backdrop-blur-md border border-white/15 text-xs sm:text-sm font-semibold text-emerald-100 shadow-sm">
                    <span class="text-emerald-400 font-bold">✓</span>
                    <span>Kunjungan Terjadwal Fleksibel</span>
                </div>
                <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 backdrop-blur-md border border-white/15 text-xs sm:text-sm font-semibold text-emerald-100 shadow-sm">
                    <span class="text-emerald-400 font-bold">✓</span>
                    <span>E-Report Medis Digital Lengkap</span>
                </div>
            </div>

            <!-- Primary Call to Action Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
                <a href="#katalog" class="w-full sm:w-auto px-8 py-4 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-600 hover:to-teal-500 text-slate-950 font-extrabold text-sm sm:text-base tracking-wide shadow-xl shadow-emerald-500/30 transform hover:-translate-y-0.5 transition flex items-center justify-center gap-2.5">
                    <span>Pilih Layanan Keperawatan</span>
                    <svg class="w-5 h-5 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </a>

                <button 
                    type="button"
                    @click="simulationModalOpen = true"
                    class="w-full sm:w-auto px-6 py-4 rounded-xl bg-emerald-950/60 hover:bg-emerald-900/80 text-white font-bold text-sm sm:text-base border border-emerald-400/40 backdrop-blur-md transition flex items-center justify-center gap-2">
                    <span>⚡ Coba Simulasi Akun (Demo)</span>
                </button>
            </div>

            <!-- Stats Bar -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-12 max-w-4xl mx-auto border-t border-emerald-800/40 text-center">
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
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                    Standar Prosedur Operasional
                </span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mt-3">
                    4 Langkah Praktis Memanggil Perawat ke Rumah
                </h2>
                <p class="text-slate-600 mt-2 text-sm sm:text-base">
                    Alur pemesanan dirancang terstruktur, transparan, dan terintegrasi sistem jaminan mutu asuhan keperawatan
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 relative">
                
                <!-- Step 1 -->
                <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200 hover:border-emerald-500 hover:shadow-lg transition group">
                    <div class="w-12 h-12 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-extrabold text-lg shadow-md shadow-emerald-600/30 mb-5 group-hover:scale-110 transition-transform">
                        1
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900 mb-2">Pilih Tindakan & Jadwal</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Tentukan jenis tindakan medis dari katalog, tanggal kunjungan, serta alamat domisili pasien.
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200 hover:border-emerald-500 hover:shadow-lg transition group">
                    <div class="w-12 h-12 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-extrabold text-lg shadow-md shadow-emerald-600/30 mb-5 group-hover:scale-110 transition-transform">
                        2
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900 mb-2">Pembayaran Escrow</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Dana diamankan di rekening penampung (escrow). Perawat belum menerima pembayaran sebelum tindakan selesai.
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200 hover:border-emerald-500 hover:shadow-lg transition group">
                    <div class="w-12 h-12 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-extrabold text-lg shadow-md shadow-emerald-600/30 mb-5 group-hover:scale-110 transition-transform">
                        3
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900 mb-2">Tindakan Medis Steril</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Perawat ber-STR/SIP hadir tepat waktu ke rumah pasien dengan peralatan standar aseptik medis.
                    </p>
                </div>

                <!-- Step 4 -->
                <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200 hover:border-emerald-500 hover:shadow-lg transition group">
                    <div class="w-12 h-12 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-extrabold text-lg shadow-md shadow-emerald-600/30 mb-5 group-hover:scale-110 transition-transform">
                        4
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900 mb-2">E-Report & Konfirmasi</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Perawat mengisi E-Report klinis, pasien mengonfirmasi selesai & memberikan ulasan, lalu dana diteruskan.
                    </p>
                </div>

            </div>

        </div>
    </section>

    <!-- 5. KATALOG LAYANAN KEPERAWATAN RUMAH SECTION (SCRUM-35 & MOCKUP) -->
    <section id="katalog" class="py-20 px-4 sm:px-6 lg:px-8 bg-slate-100">
        <div class="max-w-7xl mx-auto space-y-10">
            
            <!-- Section Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                        Katalog & Plafon Tarif Baku
                    </span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mt-3">
                        Katalog Layanan Keperawatan Rumah
                    </h2>
                    <p class="text-slate-600 mt-1.5 text-sm">
                        Tarif tindakan transparan dan terstandarisasi medis. Tanpa biaya tersembunyi.
                    </p>
                </div>

                <!-- Category Filter Tabs -->
                <div class="inline-flex p-1.5 bg-white rounded-xl shadow-sm border border-slate-200 self-start md:self-auto overflow-x-auto">
                    <button 
                        type="button" 
                        @click="filterCategory = 'Semua'"
                        :class="filterCategory === 'Semua' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                        class="px-4 py-2 rounded-lg text-xs font-bold transition">
                        Semua Tingkat
                    </button>
                    <button 
                        type="button" 
                        @click="filterCategory = 'Dasar'"
                        :class="filterCategory === 'Dasar' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                        class="px-4 py-2 rounded-lg text-xs font-bold transition">
                        Dasar
                    </button>
                    <button 
                        type="button" 
                        @click="filterCategory = 'Menengah'"
                        :class="filterCategory === 'Menengah' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                        class="px-4 py-2 rounded-lg text-xs font-bold transition">
                        Menengah
                    </button>
                    <button 
                        type="button" 
                        @click="filterCategory = 'Intensif'"
                        :class="filterCategory === 'Intensif' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                        class="px-4 py-2 rounded-lg text-xs font-bold transition">
                        Intensif
                    </button>
                </div>
            </div>

            <!-- Search and Filter Bar -->
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-center gap-4">
                
                <!-- Search Input -->
                <div class="relative flex-1 w-full">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </span>
                    <input 
                        type="text" 
                        x-model="searchQuery"
                        placeholder="Cari tindakan keperawatan (contoh: luka, kateter, infus, nebulizer)..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition" />
                </div>

                <!-- Max Price Slider -->
                <div class="flex items-center gap-3 w-full md:w-auto shrink-0 bg-slate-50 px-4 py-2 rounded-xl border border-slate-200">
                    <span class="text-xs font-bold text-slate-600 whitespace-nowrap">Plafon Maks:</span>
                    <span class="text-xs font-extrabold text-emerald-700 min-w-[90px]" x-text="'Rp ' + Number(maxPrice).toLocaleString('id-ID')"></span>
                    <input 
                        type="range" 
                        min="50000" 
                        max="300000" 
                        step="25000" 
                        x-model="maxPrice"
                        class="w-32 accent-emerald-600 cursor-pointer" />
                </div>
            </div>

            <!-- Service Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <template x-for="item in filteredServices()" :key="item.id">
                    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm hover:shadow-xl hover:border-emerald-500/50 transition-all duration-300 flex flex-col justify-between overflow-hidden group">
                        
                        <!-- Top Card Body -->
                        <div class="p-6 space-y-4">
                            
                            <!-- Badges Row -->
                            <div class="flex items-center justify-between gap-2">
                                <span 
                                    :class="item.levelColor"
                                    class="text-[10px] font-extrabold px-2.5 py-1 rounded-full border uppercase tracking-wider" 
                                    x-text="item.levelBadge">
                                </span>

                                <span class="text-xs text-slate-500 font-semibold flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span x-text="item.duration"></span>
                                </span>
                            </div>

                            <!-- Title & Emoji -->
                            <div class="flex items-start gap-3">
                                <span class="text-3xl p-2 rounded-xl bg-slate-50 border border-slate-100 shrink-0 group-hover:scale-110 transition-transform" x-text="item.imageEmoji"></span>
                                <h3 class="font-extrabold text-base text-slate-900 group-hover:text-emerald-700 transition" x-text="item.title"></h3>
                            </div>

                            <!-- Description -->
                            <p class="text-xs text-slate-600 leading-relaxed" x-text="item.desc"></p>

                            <!-- Inclusions List -->
                            <div class="pt-2 border-t border-slate-100">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-2">Cakupan Tindakan:</p>
                                <ul class="space-y-1.5 text-xs text-slate-700">
                                    <template x-for="inc in item.inclusions">
                                        <li class="flex items-center gap-2">
                                            <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            <span x-text="inc"></span>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>

                        <!-- Bottom Card Footer (Tariff & Button) -->
                        <div class="p-5 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between gap-4">
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-400 block">Biaya Jasa</span>
                                <span class="text-lg font-extrabold text-emerald-800" x-text="item.priceFormatted"></span>
                            </div>

                            <button 
                                type="button" 
                                @click="openBooking(item)"
                                class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-md shadow-emerald-600/25 transition transform active:scale-95 flex items-center gap-1.5">
                                <span>Pesan</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </div>

                    </div>
                </template>

            </div>

            <!-- Empty State if no filter matches -->
            <div x-show="filteredServices().length === 0" class="text-center py-12 bg-white rounded-2xl border border-slate-200">
                <p class="text-3xl mb-2">🔍</p>
                <h4 class="font-extrabold text-slate-800 text-sm">Tidak ada tindakan yang cocok</h4>
                <p class="text-xs text-slate-500 mt-1">Coba sesuaikan kata kunci pencarian atau naikkan filter batas tarif maksimum.</p>
                <button 
                    @click="searchQuery = ''; filterCategory = 'Semua'; maxPrice = 300000"
                    class="mt-4 px-4 py-2 rounded-lg bg-emerald-50 text-emerald-700 font-bold text-xs hover:bg-emerald-100">
                    Reset Filter
                </button>
            </div>

        </div>
    </section>

    <!-- 6. ESCROW PROTECTION & LEGALITAS MEDIS -->
    <section id="escrow" class="py-20 px-4 sm:px-6 lg:px-8 bg-gradient-to-b from-[#022c22] to-[#043328] text-white">
        <div class="max-w-7xl mx-auto">
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                
                <div class="space-y-6">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-bold border border-emerald-400/30">
                        🛡️ Sistem Keamanan & Kepatuhan Medis
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight leading-snug">
                        Jaminan Perlindungan Pasien Melalui Rekening Penampung (Escrow)
                    </h2>
                    <p class="text-emerald-100/90 text-sm leading-relaxed">
                        Di CareAtHome, keselamatan pasien dan transparansi biaya adalah prioritas nomor satu. Pembayaran Anda tidak langsung diberikan ke perawat, melainkan dikunci sementara oleh platform.
                    </p>

                    <div class="space-y-4 pt-2">
                        <div class="flex items-start gap-3 p-4 rounded-xl bg-white/5 border border-white/10">
                            <span class="text-xl">🔒</span>
                            <div>
                                <h4 class="font-extrabold text-sm text-emerald-200">Dana Terkunci Aman (Escrow Settlement)</h4>
                                <p class="text-xs text-emerald-100/80 mt-0.5">Dana hanya diteruskan ke perawat setelah pasien mengonfirmasi selesai dan memeriksa E-Report.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-4 rounded-xl bg-white/5 border border-white/10">
                            <span class="text-xl">🩺</span>
                            <div>
                                <h4 class="font-extrabold text-sm text-emerald-200">Verifikasi STR & SIP Multi-Tahap</h4>
                                <p class="text-xs text-emerald-100/80 mt-0.5">Admin mengaudit keabsahan STR nakes langsung ke portal Konsil Tenaga Kesehatan Indonesia (KTKI).</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-4 rounded-xl bg-white/5 border border-white/10">
                            <span class="text-xl">📑</span>
                            <div>
                                <h4 class="font-extrabold text-sm text-emerald-200">Rekam Asuhan Digital (E-Report Medis)</h4>
                                <p class="text-xs text-emerald-100/80 mt-0.5">Setiap kunjungan wajib didokumentasikan dengan tanda-tanda vital, tindakan, dan instruksi keluarga.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Visual Card Mockup -->
                <div class="relative">
                    <div class="p-8 rounded-3xl bg-slate-900/90 border border-emerald-500/30 shadow-2xl backdrop-blur-xl space-y-6">
                        
                        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                            <div>
                                <p class="text-xs text-slate-400">Nomor Invoice Escrow</p>
                                <p class="font-mono font-bold text-emerald-400 text-sm">CAH-ESC-20261019-9481</p>
                            </div>
                            <span class="text-xs bg-emerald-500/20 text-emerald-300 font-bold px-3 py-1 rounded-full border border-emerald-400/30">
                                Dana Terkunci Aman
                            </span>
                        </div>

                        <div class="space-y-3 text-xs">
                            <div class="flex justify-between text-slate-300">
                                <span>Jasa Tindakan Keperawatan</span>
                                <span class="font-mono text-white">Rp 250.000</span>
                            </div>
                            <div class="flex justify-between text-slate-300">
                                <span>Biaya Layanan Platform (Flat)</span>
                                <span class="font-mono text-white">Rp 5.000</span>
                            </div>
                            <div class="flex justify-between text-slate-300">
                                <span>Ongkos Transportasi Perawat</span>
                                <span class="font-mono text-white">Rp 15.000</span>
                            </div>
                            <div class="pt-3 border-t border-slate-800 flex justify-between font-bold text-sm text-white">
                                <span>Total Pembayaran Pasien</span>
                                <span class="text-emerald-400 font-mono">Rp 270.000</span>
                            </div>
                        </div>

                        <div class="p-4 rounded-xl bg-emerald-950/60 border border-emerald-800/40 text-[11px] text-emerald-200 leading-relaxed flex items-center gap-2">
                            <span>✅</span>
                            <span>Dana akan dilepaskan (85% jasa + 100% transport) ke dompet perawat saat status pesanan <strong>Completed</strong>.</span>
                        </div>

                        <div class="pt-2">
                            <a href="#katalog" class="block w-full py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-slate-950 font-extrabold text-xs text-center transition shadow-lg shadow-emerald-600/30">
                                Pesan Layanan Bergaransi Sekarang
                            </a>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- 7. SIMULASI AKUN DEMO MODAL (3 PERAN: ADMIN, PERAWAT, PASIEN) -->
    <div 
        x-show="simulationModalOpen" 
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
        @keydown.escape.window="simulationModalOpen = false">
        
        <div 
            class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-emerald-500/30 text-slate-800 relative space-y-6"
            @click.away="simulationModalOpen = false">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-2">
                    <span class="text-2xl">⚡</span>
                    <div>
                        <h3 class="font-extrabold text-base text-slate-900">Simulasi Akun (3 Peran)</h3>
                        <p class="text-xs text-slate-500">Coba langsung aplikasi menggunakan akun seeder database</p>
                    </div>
                </div>

                <button @click="simulationModalOpen = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold p-1">
                    &times;
                </button>
            </div>

            <!-- Role Cards -->
            <div class="space-y-3">
                
                <!-- 1. Pasien -->
                <div class="p-4 rounded-2xl border-2 border-emerald-200 hover:border-emerald-500 bg-emerald-50/40 transition flex items-center justify-between gap-4">
                    <div class="space-y-0.5">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">👤</span>
                            <h4 class="font-extrabold text-sm text-slate-900">Peran: Pasien (Patient)</h4>
                            <span class="text-[10px] bg-emerald-600 text-white font-bold px-2 py-0.5 rounded-full">Seeder</span>
                        </div>
                        <p class="text-xs text-slate-600">Ahmad Fauzi &bull; <code>pasien@careathome.com</code></p>
                        <p class="text-[11px] text-emerald-800">Coba: Pesan layanan, pembayaran escrow, ulasan.</p>
                    </div>
                    <a href="{{ route('demo.login', 'pasien') }}" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shrink-0 transition shadow-sm">
                        Masuk Pasien
                    </a>
                </div>

                <!-- 2. Perawat -->
                <div class="p-4 rounded-2xl border-2 border-teal-200 hover:border-teal-500 bg-teal-50/40 transition flex items-center justify-between gap-4">
                    <div class="space-y-0.5">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🩺</span>
                            <h4 class="font-extrabold text-sm text-slate-900">Peran: Perawat (Nurse)</h4>
                            <span class="text-[10px] bg-teal-600 text-white font-bold px-2 py-0.5 rounded-full">Seeder</span>
                        </div>
                        <p class="text-xs text-slate-600">Ns. Siti Nurhaliza &bull; <code>perawat@careathome.com</code></p>
                        <p class="text-[11px] text-teal-800">Coba: Unggah STR/SIP, toggle online/offline, E-Report.</p>
                    </div>
                    <a href="{{ route('demo.login', 'perawat') }}" class="px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shrink-0 transition shadow-sm">
                        Masuk Perawat
                    </a>
                </div>

                <!-- 3. Admin -->
                <div class="p-4 rounded-2xl border-2 border-slate-200 hover:border-slate-500 bg-slate-50 transition flex items-center justify-between gap-4">
                    <div class="space-y-0.5">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🛡️</span>
                            <h4 class="font-extrabold text-sm text-slate-900">Peran: Admin (Operational)</h4>
                            <span class="text-[10px] bg-slate-800 text-white font-bold px-2 py-0.5 rounded-full">Seeder</span>
                        </div>
                        <p class="text-xs text-slate-600">Administrator &bull; <code>admin@careathome.com</code></p>
                        <p class="text-[11px] text-slate-700">Coba: Verifikasi STR nakes, katalog tindakan, payout.</p>
                    </div>
                    <a href="{{ route('demo.login', 'admin') }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold shrink-0 transition shadow-sm">
                        Masuk Admin
                    </a>
                </div>

            </div>

            <div class="text-center pt-2">
                <button @click="simulationModalOpen = false" class="text-xs text-slate-500 hover:text-slate-700 font-semibold underline">
                    Tutup Simulasi
                </button>
            </div>
        </div>
    </div>

    <!-- 8. BOOKING PREVIEW MODAL -->
    <div 
        x-show="bookingModalOpen" 
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
        @keydown.escape.window="bookingModalOpen = false">
        
        <div 
            class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-emerald-500/30 text-slate-800 relative space-y-5"
            @click.away="bookingModalOpen = false">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-2">
                    <span class="text-2xl" x-text="selectedService?.imageEmoji"></span>
                    <div>
                        <h3 class="font-extrabold text-base text-slate-900" x-text="selectedService?.title"></h3>
                        <p class="text-xs text-slate-500" x-text="'Durasi: ' + selectedService?.duration"></p>
                    </div>
                </div>

                <button @click="bookingModalOpen = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold p-1">
                    &times;
                </button>
            </div>

            <div class="space-y-4 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Rencana Tanggal & Waktu Kunjungan</label>
                    <input type="datetime-local" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-emerald-500 focus:border-emerald-500" value="2026-10-21T09:00" />
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Titik Lokasi & Alamat Kunjungan</label>
                    <input type="text" placeholder="Masukkan alamat lengkap rumah pasien..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-emerald-500 focus:border-emerald-500" value="Jl. Dago Asri No. 12, Bandung (Jarak estimasi: 3.2 km)" />
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Catatan Kondisi Klinis Awal Pasien</label>
                    <textarea rows="2" placeholder="Tuliskan keluhan atau instruksi khusus dari dokter..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                </div>

                <!-- Price breakdown -->
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-1.5 font-medium">
                    <div class="flex justify-between text-slate-600">
                        <span>Biaya Tindakan:</span>
                        <span class="font-bold text-slate-800" x-text="selectedService?.priceFormatted"></span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Biaya Layanan Platform:</span>
                        <span class="font-bold text-slate-800">Rp 5.000</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Estimasi Transport Perawat (3 km):</span>
                        <span class="font-bold text-slate-800">Rp 15.000</span>
                    </div>
                    <div class="pt-2 border-t border-slate-200 flex justify-between font-extrabold text-sm text-emerald-800">
                        <span>Total Escrow:</span>
                        <span x-text="'Rp ' + (selectedService ? (selectedService.price + 20000).toLocaleString('id-ID') : '0')"></span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button 
                    @click="bookingModalOpen = false"
                    class="w-1/2 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-50 transition">
                    Batal
                </button>
                <a 
                    href="{{ route('register') }}"
                    class="w-1/2 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs text-center shadow-md shadow-emerald-600/30 transition">
                    Lanjut Booking
                </a>
            </div>
        </div>
    </div>

    <!-- 9. MULTI-COLUMN DARK FOOTER (#0b1329 / #0f172a) -->
    <footer class="bg-gradient-to-b from-[#0b1329] to-[#060a17] text-slate-300 pt-16 pb-12 border-t border-emerald-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                
                <!-- Brand Column -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <x-application-logo />
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Platform health-tech on-demand berbasis web yang menghubungkan pasien dengan perawat medis bersertifikasi (STR & SIP) secara aman melalui sistem garansi rekening penampung (escrow).
                    </p>
                </div>

                <!-- Layanan Medis -->
                <div class="space-y-3">
                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-white">Layanan Tindakan</h4>
                    <ul class="space-y-2 text-xs text-slate-400">
                        <li><a href="#katalog" class="hover:text-emerald-400 transition">Perawatan Luka Diabetes</a></li>
                        <li><a href="#katalog" class="hover:text-emerald-400 transition">Pemasangan Kateter Urine</a></li>
                        <li><a href="#katalog" class="hover:text-emerald-400 transition">Pemberian Terapi Infus & Injeksi</a></li>
                        <li><a href="#katalog" class="hover:text-emerald-400 transition">Terapi Uap Nebulizer</a></li>
                        <li><a href="#katalog" class="hover:text-emerald-400 transition">Pemasangan Selang NGT</a></li>
                    </ul>
                </div>

                <!-- Legalitas & Keamanan -->
                <div class="space-y-3">
                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-white">Legalitas & Kepatuhan</h4>
                    <ul class="space-y-2 text-xs text-slate-400">
                        <li><span class="hover:text-emerald-400 transition">Audit STR & SIP Konsil Tenaga Kesehatan</span></li>
                        <li><span class="hover:text-emerald-400 transition">Kepatuhan UU PDP No. 27 Tahun 2022</span></li>
                        <li><span class="hover:text-emerald-400 transition">Enkripsi Rekam Medis Digital AES-256</span></li>
                        <li><span class="hover:text-emerald-400 transition">Garansi Rekening Escrow Midtrans</span></li>
                    </ul>
                </div>

                <!-- Kontak & Disclaimer -->
                <div class="space-y-3">
                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-white">Batasan Layanan</h4>
                    <p class="text-[11px] text-slate-400 leading-relaxed">
                        <strong class="text-amber-400">Peringatan:</strong> CareAtHome hanya melayani tindakan keperawatan non-kritis dan terencana. Kami <em>tidak melayani</em> kegawatdaruratan (emergency/ICU). Jika darurat, segera hubungi 119 atau IGD terdekat.
                    </p>
                    <div class="pt-2">
                        <button 
                            @click="simulationModalOpen = true"
                            class="text-xs text-emerald-400 hover:text-emerald-300 font-bold underline">
                            Buka Menu Simulasi Akun Demo &rarr;
                        </button>
                    </div>
                </div>

            </div>

            <!-- Copyright -->
            <div class="pt-8 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
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
