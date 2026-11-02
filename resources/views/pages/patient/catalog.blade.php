<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Katalog Layanan Medis Homecare - CareAtHome</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Leaflet CSS & JS for Interactive Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <style>
        [x-cloak] { display: none !important; }
        .leaflet-container { font-family: inherit; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col"
    x-data="patientCatalogApp()"
    x-init="initData()">

    <!-- 1. TOP NAVBAR -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <!-- Left: Brand Logo & Portal Tag -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 group">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 flex items-center justify-center text-white shadow-md shadow-emerald-500/20 group-hover:scale-105 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-lg font-extrabold text-slate-900 tracking-tight leading-none block">
                                Care<span class="text-emerald-600">At</span>Home
                            </span>
                            <span class="text-[10px] uppercase tracking-wider font-semibold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200/60 mt-0.5 inline-block">
                                Portal Pasien
                            </span>
                        </div>
                    </a>

                    <!-- Nav separator -->
                    <div class="hidden sm:block h-6 w-px bg-slate-200 ml-2"></div>

                    <!-- Desktop Nav Links -->
                    <nav class="hidden sm:flex items-center space-x-1 ml-2">
                        <a href="{{ route('dashboard') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition">
                            Dashboard
                        </a>
                        <a href="{{ route('pasien.katalog') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 transition">
                            Katalog Layanan
                        </a>
                        <a href="{{ route('pasien.pesanan') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition">
                            Jadwal &amp; Detail Booking
                        </a>
                        <a href="{{ route('pasien.pembayaran') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition flex items-center gap-1.5">
                            <span>Pembayaran Escrow</span>
                            <span class="text-[9px] font-extrabold px-1.5 py-0.2 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300">Escrow</span>
                        </a>
                        <a href="{{ route('pasien.pembatalan') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition">
                            Pembatalan &amp; Refund
                        </a>
                    </nav>
                </div>

                <!-- Right: User Profile & Quick Switch -->
                <div class="flex items-center gap-3">
                    <div class="hidden md:flex items-center gap-2 text-xs text-slate-500 bg-slate-100 px-3 py-1.5 rounded-xl border border-slate-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Pasien Terdaftar: <strong>{{ auth()->user()->name }}</strong></span>
                    </div>

                    <!-- Dropdown / Logout -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" type="button" class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-slate-100 transition border border-transparent hover:border-slate-200">
                            <div class="w-8 h-8 rounded-full bg-emerald-600 text-white font-bold text-xs flex items-center justify-center">
                                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                            </div>
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="open" @click.away="open = false" x-cloak
                            class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-200 py-1.5 text-xs text-slate-700 z-50">
                            <div class="px-3 py-2 border-b border-slate-100">
                                <p class="font-bold text-slate-900 truncate">{{ auth()->user()->name }}</p>
                                <p class="text-[11px] text-slate-500 truncate">{{ auth()->user()->email }}</p>
                            </div>
                            <a href="{{ route('profile') }}" class="block px-3 py-2 hover:bg-slate-50 text-slate-700 font-medium">Pengaturan Profil</a>
                            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="block px-3 py-2 hover:bg-rose-50 text-rose-600 font-medium">
                                Keluar Akun
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                                @csrf
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- 2. MAIN CONTENT WRAPPER -->
    <main class="flex-1 py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full space-y-8">
        
        <!-- Breadcrumb & Header Hero -->
        <div class="space-y-4">
            <nav class="flex items-center gap-2 text-xs text-slate-500">
                <a href="{{ route('dashboard') }}" class="hover:text-emerald-600">Dashboard</a>
                <span>/</span>
                <span class="text-slate-900 font-semibold">Katalog Layanan Medis</span>
            </nav>

            <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
                <!-- Background Glow decorative elements -->
                <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute top-0 right-1/4 w-40 h-40 bg-teal-400/10 rounded-full blur-2xl pointer-events-none"></div>

                <div class="relative z-10 max-w-3xl space-y-4">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-semibold border border-emerald-400/30">
                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        Nakes Berlisensi STR & SIP Aktif Kemenkes RI
                    </div>

                    <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold tracking-tight text-white">
                        Katalog Asuhan Keperawatan Rumah
                    </h1>

                    <p class="text-sm sm:text-base text-slate-300 leading-relaxed">
                        Pilih tindakan medis terstandar untuk pemulihan mandiri di rumah. Seluruh tarif berpedoman pada plafon resmi dengan jaminan keamanan pembayaran <em>Escrow System</em>.
                    </p>

                    <!-- Feature Badges -->
                    <div class="pt-2 flex flex-wrap gap-2.5 text-xs text-slate-200">
                        <div class="bg-white/10 backdrop-blur-sm px-3 py-1.5 rounded-xl border border-white/10 flex items-center gap-1.5">
                            <span class="text-emerald-400">✓</span> Transparansi Tarif Tanpa Mark-up
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm px-3 py-1.5 rounded-xl border border-white/10 flex items-center gap-1.5">
                            <span class="text-emerald-400">✓</span> E-Report Rekam Medis Digital
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm px-3 py-1.5 rounded-xl border border-white/10 flex items-center gap-1.5">
                            <span class="text-emerald-400">✓</span> Perawat Terdekat Radius GPS 10 KM
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2.5 SECTION PERAWAT TERDEKAT RADIUS 10 KM (BAGIAN 3) -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-sm space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 text-[11px] font-bold border border-emerald-200">
                        <span>📍</span>
                        <span>Radius Layanan Maksimal 10 KM</span>
                    </div>
                    <h2 class="text-lg font-bold text-slate-900 tracking-tight">Mitra Perawat Aktif Terdekat</h2>
                    <p class="text-xs text-slate-500">
                        Menampilkan perawat yang berstatus <strong>Online (Sedang Bekerja)</strong>, terverifikasi STR/SIP, dan berjarak maksimal 10 km dari lokasi Anda.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="requestBrowserLocation()"
                        :disabled="loadingNurses"
                        class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow transition flex items-center gap-2">
                        <span x-show="loadingNurses" class="animate-spin text-sm">⏳</span>
                        <span x-show="!loadingNurses">🎯 Deteksi Lokasi GPS Saya</span>
                    </button>
                </div>
            </div>

            <!-- Loading State -->
            <div x-show="loadingNurses" class="py-8 text-center text-xs text-slate-500 space-y-2">
                <span class="animate-spin text-2xl inline-block">⏳</span>
                <p>Mencari perawat aktif di sekitar lokasi Anda...</p>
            </div>

            <!-- Empty State: No Nurse in 10 km Radius -->
            <div x-show="!loadingNurses && nearbyNurses.length === 0" x-cloak
                class="py-8 px-4 text-center rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl mx-auto border border-amber-200">
                    🛵
                </div>
                <div class="space-y-1 max-w-md mx-auto">
                    <h4 class="text-sm font-bold text-slate-800">Tidak Ada Perawat Aktif Dalam Radius 10 KM</h4>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Saat ini belum ada perawat aktif yang berstatus online dalam jarak 10 km dari titik koordinat Anda. Anda tetap dapat memilih tindakan medis dari katalog di bawah.
                    </p>
                </div>
            </div>

            <!-- Nurse Cards Grid -->
            <div x-show="!loadingNurses && nearbyNurses.length > 0" x-cloak
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <template x-for="nurse in nearbyNurses" :key="nurse.id">
                    <div class="bg-white rounded-2xl border border-slate-200 hover:border-emerald-500 hover:shadow-md transition p-5 flex flex-col justify-between space-y-4">
                        
                        <!-- Header Nurse Card -->
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-2xl bg-emerald-600 text-white font-extrabold text-sm flex items-center justify-center shadow-sm">
                                    <span x-text="nurse.name ? nurse.name.substring(0, 2).toUpperCase() : 'NS'"></span>
                                </div>
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <h4 class="text-xs font-bold text-slate-900" x-text="nurse.name"></h4>
                                        <span class="text-emerald-600 text-xs" title="Terverifikasi Kemenkes">✓</span>
                                    </div>
                                    <span class="text-[10px] text-slate-500 block" x-text="nurse.specialization || 'Perawat Homecare'"></span>
                                    <div class="flex items-center gap-1 mt-0.5 text-[11px] text-amber-500 font-bold">
                                        <span>⭐</span>
                                        <span x-text="nurse.rating_avg.toFixed(1)"></span>
                                        <span class="text-slate-400 font-normal" x-text="'(' + nurse.total_reviews + ' ulasan)'"></span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Jarak Pill -->
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center gap-1">
                                <span>📍</span>
                                <span x-text="nurse.formatted_distance"></span>
                            </span>
                        </div>

                        <!-- Layanan yang Disediakan -->
                        <!-- Layanan yang Disediakan (Klik langsung untuk memilih) -->
                        <div class="space-y-1.5 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Layanan Disediakan:</span>
                                <span class="text-[10px] text-emerald-600 font-semibold" x-text="nurse.services ? nurse.services.length + ' pilihan' : ''"></span>
                            </div>
                            <div class="flex flex-wrap gap-1.5">
                                <template x-for="s in nurse.services" :key="s.id">
                                    <button type="button" 
                                        @click.stop="selectNurseWithSpecificService(nurse, s)"
                                        class="text-[10px] px-2 py-0.5 rounded-lg bg-slate-50 hover:bg-emerald-50 text-slate-700 hover:text-emerald-800 font-medium border border-slate-200/80 hover:border-emerald-300 transition-all flex items-center gap-1 group/btn cursor-pointer hover:scale-102"
                                        :title="'Klik untuk memesan layanan ' + s.name + ' bersama ' + nurse.name">
                                        <span x-text="s.image_emoji"></span>
                                        <span class="group-hover/btn:underline" x-text="s.name"></span>
                                    </button>
                                </template>
                                <template x-if="!nurse.services || nurse.services.length === 0">
                                    <span class="text-[10px] text-slate-400 italic">Semua layanan standar</span>
                                </template>
                            </div>
                        </div>

                        <!-- Tombol Pilih Perawat -->
                        <div class="pt-2 border-t border-slate-100">
                            <button type="button" @click="openNurseServicePicker(nurse)"
                                class="w-full py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center justify-center gap-1.5 hover:-translate-y-0.5">
                                <span>Pilih Layanan &amp; Pesan Perawat</span>
                                <span>&rarr;</span>
                            </button>
                        </div>

                    </div>
                </template>
            </div>
        </div>

        <!-- 3. FILTER, SEARCH & CONTROLS TOOLBAR -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-sm space-y-4">
            
            <!-- Top Row: Search Input + Sort Dropdown -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3.5">
                
                <!-- Search Input with icon and clear button -->
                <div class="md:col-span-8 relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text"
                        x-model="search"
                        @input="filterServices()"
                        placeholder="Cari tindakan medis (contoh: luka, kateter, infus, nebulizer, stroke, bayi)..."
                        class="w-full pl-11 pr-10 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition placeholder:text-slate-400" />
                    
                    <button type="button"
                        x-show="search.length > 0"
                        @click="search = ''; filterServices()"
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Sort By Dropdown -->
                <div class="md:col-span-4 flex items-center gap-2">
                    <label for="sort_by" class="text-xs font-semibold text-slate-500 whitespace-nowrap">Urutkan:</label>
                    <select id="sort_by"
                        x-model="sortBy"
                        @change="filterServices()"
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                        <option value="nama_asc">Nama Layanan (A - Z)</option>
                        <option value="termurah">Plafon Tarif Termurah</option>
                        <option value="termahal">Plafon Tarif Tertinggi</option>
                        <option value="durasi_singkat">Durasi Tindakan Tersingkat</option>
                    </select>
                </div>
            </div>

            <!-- Category Filter Tabs / Chips -->
            <div class="pt-2 border-t border-slate-100 flex flex-wrap items-center gap-2">
                <span class="text-xs font-bold text-slate-500 mr-1 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Kategori:
                </span>

                <button type="button"
                    @click="setCategory('Semua')"
                    :class="selectedCategory === 'Semua' ? 'bg-emerald-600 text-white shadow-sm ring-2 ring-emerald-600/20' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                    <span>Semua Tindakan</span>
                    <span :class="selectedCategory === 'Semua' ? 'bg-emerald-700 text-white' : 'bg-slate-200 text-slate-700'" class="px-1.5 py-0.2 rounded-full text-[10px]" x-text="rawServices.length"></span>
                </button>

                <template x-for="cat in availableCategories" :key="cat.name">
                    <button type="button"
                        @click="setCategory(cat.name)"
                        :class="selectedCategory === cat.name ? 'bg-emerald-600 text-white shadow-sm ring-2 ring-emerald-600/20' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                        <span x-text="cat.name"></span>
                        <span :class="selectedCategory === cat.name ? 'bg-emerald-700 text-white' : 'bg-slate-200 text-slate-700'" class="px-1.5 py-0.2 rounded-full text-[10px]" x-text="cat.count"></span>
                    </button>
                </template>
            </div>

            <!-- Price Range Quick Filters & Active Counters -->
            <div class="pt-2 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-slate-500">Rentang Tarif:</span>
                    <div class="inline-flex p-0.5 bg-slate-100 rounded-lg">
                        <button type="button" @click="setPriceFilter('all')" :class="priceBracket === 'all' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600'" class="px-2.5 py-1 rounded-md text-[11px] transition">Semua</button>
                        <button type="button" @click="setPriceFilter('under_150k')" :class="priceBracket === 'under_150k' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600'" class="px-2.5 py-1 rounded-md text-[11px] transition">&lt; Rp 150rb</button>
                        <button type="button" @click="setPriceFilter('150k_200k')" :class="priceBracket === '150k_200k' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600'" class="px-2.5 py-1 rounded-md text-[11px] transition">Rp 150rb - 200rb</button>
                        <button type="button" @click="setPriceFilter('above_200k')" :class="priceBracket === 'above_200k' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600'" class="px-2.5 py-1 rounded-md text-[11px] transition">&gt; Rp 200rb</button>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <span class="text-slate-500">
                        Menampilkan <strong class="text-slate-800" x-text="filteredServices.length"></strong> dari <span x-text="rawServices.length"></span> layanan
                    </span>

                    <button type="button"
                        x-show="search.length > 0 || selectedCategory !== 'Semua' || priceBracket !== 'all'"
                        @click="resetFilters()"
                        class="text-rose-600 hover:text-rose-700 font-semibold underline text-xs">
                        Reset Filter
                    </button>
                </div>
            </div>

        </div>

        <!-- 4. SERVICES CATALOG GRID -->
        <div x-show="filteredServices.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <template x-for="item in filteredServices" :key="item.id">
                <div class="bg-white rounded-2xl border hover:shadow-lg transition-all duration-300 flex flex-col justify-between overflow-hidden group"
                    :class="isInCompare(item.id) ? 'border-indigo-400 ring-2 ring-indigo-400/20 shadow-md' : 'border-slate-200 hover:border-emerald-400'">
                    
                    <!-- Card Top Content -->
                    <div class="p-6 space-y-4">
                        
                        <!-- Header: Emoji Icon, Category Badge & Duration -->
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <!-- Compare Checkbox -->
                                <button type="button" @click.stop="toggleCompare(item)"
                                    :title="isInCompare(item.id) ? 'Hapus dari perbandingan' : 'Tambah ke perbandingan'"
                                    class="w-7 h-7 rounded-lg border-2 flex items-center justify-center text-xs font-bold transition-all shrink-0"
                                    :class="isInCompare(item.id) ? 'bg-indigo-600 border-indigo-600 text-white shadow-sm scale-110' : 'border-slate-300 text-transparent hover:border-indigo-400 hover:bg-indigo-50 hover:text-indigo-400'">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>
                                <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                                    <span x-text="item.image_emoji"></span>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-1.5">
                                <span class="text-[11px] font-bold px-2.5 py-1 rounded-full border"
                                    :class="item.badge_color ? item.badge_color : 'bg-emerald-50 text-emerald-800 border-emerald-200'"
                                    x-text="item.category_badge || item.category"></span>
                                <span class="text-[11px] font-semibold text-slate-500 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span x-text="item.estimated_duration"></span>
                                </span>
                            </div>
                        </div>

                        <!-- Service Title -->
                        <div>
                            <h3 class="text-base font-bold text-slate-900 group-hover:text-emerald-700 transition leading-snug" x-text="item.name"></h3>
                            <p class="text-xs text-slate-600 line-clamp-3 mt-1.5 leading-relaxed" x-text="item.description"></p>
                        </div>

                        <!-- Key Inclusions Checklist -->
                        <div class="pt-2 border-t border-slate-100 space-y-1.5">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Termasuk Dalam Layanan:</p>
                            <template x-for="(inc, idx) in item.inclusions.slice(0, 3)" :key="idx">
                                <div class="flex items-start gap-1.5 text-xs text-slate-700">
                                    <span class="text-emerald-600 font-bold shrink-0">✓</span>
                                    <span class="line-clamp-1" x-text="inc"></span>
                                </div>
                            </template>
                            <template x-if="item.inclusions.length > 3">
                                <p class="text-[11px] text-emerald-600 font-semibold cursor-pointer hover:underline" @click="openDetailModal(item)">
                                    +<span x-text="item.inclusions.length - 3"></span> butir tindakan lainnya...
                                </p>
                            </template>
                        </div>

                    </div>

                    <!-- Card Footer: Price & Action Buttons -->
                    <div class="p-5 bg-slate-50/80 border-t border-slate-100 flex flex-col gap-3">
                        <div class="flex items-baseline justify-between">
                            <div>
                                <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 block">Plafon Tarif Medis</span>
                                <span class="text-lg font-black text-slate-900" x-text="item.formatted_price"></span>
                            </div>
                            <span class="text-[10px] text-slate-500 font-medium">/ 1 Sesi Kunjungan</span>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <!-- Detail Button -->
                            <button type="button"
                                @click="openDetailModal(item)"
                                class="px-3 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition flex items-center justify-center gap-1">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Detail Medis
                            </button>

                            <!-- Booking Trigger Button -->
                            <button type="button"
                                @click="openBookingModal(item)"
                                class="px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm shadow-emerald-600/30 transition flex items-center justify-center gap-1">
                                <span>Pesan</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </button>
                        </div>
                    </div>

                </div>
            </template>
        </div>

        <!-- 5. EMPTY STATE: NO MATCH FOR SEARCH / FILTER -->
        <div x-show="filteredServices.length === 0 && rawServices.length > 0" x-cloak
            class="bg-white rounded-3xl border border-slate-200 p-12 text-center max-w-xl mx-auto space-y-4 shadow-sm">
            <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-600 mx-auto flex items-center justify-center text-3xl">
                🔍
            </div>
            <h3 class="text-lg font-bold text-slate-900">Layanan Medis Tidak Ditemukan</h3>
            <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">
                Tidak ada tindakan keperawatan yang cocok dengan kata kunci atau filter yang Anda pilih. Coba gunakan kata kunci umum seperti <em>luka</em>, <em>infus</em>, atau <em>kateter</em>.
            </p>
            <div class="pt-2">
                <button type="button" @click="resetFilters()" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition">
                    Hapus Filter & Tampilkan Semua Layanan
                </button>
            </div>
        </div>

        <!-- 6. EMPTY STATE: DATABASE TABLE IS EMPTY (ADMIN HAS NOT ADDED SERVICES YET) -->
        <div x-show="rawServices.length === 0" x-cloak
            class="bg-white rounded-3xl border border-slate-200 p-12 text-center max-w-2xl mx-auto space-y-5 shadow-sm">
            <div class="w-20 h-20 rounded-3xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center text-4xl">
                📋
            </div>
            <div class="space-y-2">
                <span class="text-[11px] font-bold text-amber-700 bg-amber-50 px-3 py-1 rounded-full border border-amber-200 uppercase tracking-wider">
                    Sistem Master Katalog Kosong
                </span>
                <h3 class="text-xl font-black text-slate-900">Katalog Belum Diterbitkan Oleh Administrator</h3>
                <p class="text-xs sm:text-sm text-slate-600 max-w-lg mx-auto leading-relaxed">
                    Katalog tindakan medis beserta plafon tarif resmi dikelola secara terpusat oleh Administrator CareAtHome.
                </p>
            </div>

            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 text-left text-xs text-slate-700 space-y-2">
                <p class="font-bold text-slate-900 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Informasi Pengujian Sistem (Development Mode):
                </p>
                <p class="text-slate-600">
                    Untuk mengisi katalog layanan standar medis ke basis data, Anda dapat menjalankan perintah seeder melalui terminal:
                </p>
                <code class="block bg-slate-900 text-emerald-400 p-2.5 rounded-xl font-mono text-[11px]">
                    php artisan db:seed --class=ServiceCatalogSeeder
                </code>
            </div>

            <button type="button" onclick="location.reload()" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition">
                Muat Ulang Halaman
            </button>
        </div>

    </main>

    <!-- 7. MODAL: DETAIL KLINIS & SPO TINDAKAN MEDIS -->
    <div x-show="detailModalOpen" x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div @click.away="detailModalOpen = false"
            class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl border border-slate-200 overflow-hidden text-slate-800"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100">

            <!-- Modal Header -->
            <div class="p-6 bg-slate-50 border-b border-slate-200 flex items-start justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center justify-center text-2xl" x-text="activeService?.image_emoji"></div>
                    <div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border bg-emerald-50 text-emerald-800 border-emerald-200 uppercase tracking-wider" x-text="activeService?.category"></span>
                        <h2 class="text-lg font-bold text-slate-900 mt-1" x-text="activeService?.name"></h2>
                    </div>
                </div>
                <button type="button" @click="detailModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Body (Clinical Tabs / Overview) -->
            <div class="p-6 space-y-5 max-h-[70vh] overflow-y-auto">
                
                <!-- Description -->
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Deskripsi Prosedur Medis</h4>
                    <p class="text-xs sm:text-sm text-slate-700 leading-relaxed" x-text="activeService?.description"></p>
                </div>

                <!-- Indikasi Klinis -->
                <div class="bg-emerald-50/70 p-4 rounded-2xl border border-emerald-100 space-y-1">
                    <h4 class="text-xs font-bold text-emerald-900 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Indikasi Medis Tindakan
                    </h4>
                    <p class="text-xs text-emerald-800 leading-relaxed" x-text="activeService?.indications || 'Diberikan pada pasien yang membutuhkan perawatan berkelanjutan di rumah sesuai asesmen nakes.'"></p>
                </div>

                <!-- Kontraindikasi (Warning Kritis) -->
                <div class="bg-rose-50/70 p-4 rounded-2xl border border-rose-100 space-y-1">
                    <h4 class="text-xs font-bold text-rose-900 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Batasan Layanan &amp; Kontraindikasi (Bukan Kasus Emergency)
                    </h4>
                    <p class="text-xs text-rose-800 leading-relaxed" x-text="activeService?.contraindications || 'Tidak melayani penanganan gawat darurat ICU/UGD.'"></p>
                </div>

                <!-- Yang Termasuk Dalam Layanan -->
                <div class="space-y-2">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Rincian Paket Tindakan Termasuk:</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <template x-for="(inc, idx) in activeService?.inclusions" :key="idx">
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 text-xs text-slate-700 flex items-start gap-2">
                                <span class="text-emerald-600 font-bold">✓</span>
                                <span x-text="inc"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Info Plafon & Durasi -->
                <div class="p-4 rounded-2xl bg-slate-100/80 border border-slate-200 flex flex-wrap items-center justify-between gap-3 text-xs">
                    <div>
                        <span class="text-slate-500 block">Estimasi Durasi Kunjungan:</span>
                        <strong class="text-slate-800" x-text="activeService?.estimated_duration"></strong>
                    </div>
                    <div class="text-right">
                        <span class="text-slate-500 block">Plafon Tarif Standar:</span>
                        <strong class="text-base text-emerald-700 font-black" x-text="activeService?.formatted_price"></strong>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="p-4 sm:p-6 bg-slate-50 border-t border-slate-200 flex items-center justify-between gap-3">
                <button type="button" @click="detailModalOpen = false" class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-xs font-bold text-slate-700 transition">
                    Tutup
                </button>

                <button type="button"
                    @click="detailModalOpen = false; openBookingModal(activeService)"
                    class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5">
                    <span>Lanjutkan Pemesanan</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </div>

        </div>
    </div>

    <!-- 7.5. MODAL: PILIH LAYANAN DARI MITRA PERAWAT (MULTI-SERVICE SELECTION) -->
    <div x-show="nurseServicePickerOpen" x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-6"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div @click.away="nurseServicePickerOpen = false"
            class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl border border-slate-200 overflow-hidden text-slate-800 flex flex-col max-h-[90vh]"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100">

            <!-- Modal Header -->
            <div class="px-6 py-5 bg-gradient-to-r from-emerald-600 via-teal-700 to-slate-900 text-white flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-white font-extrabold text-base shadow-inner">
                        <span x-text="pickerNurse?.name ? pickerNurse.name.substring(0, 2).toUpperCase() : 'NS'"></span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-full bg-emerald-500/30 border border-emerald-400/30 text-emerald-100">
                                Pilih Layanan Medis
                            </span>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-white/20 text-white font-bold flex items-center gap-1">
                                <span>📍</span>
                                <span x-text="pickerNurse?.formatted_distance"></span>
                            </span>
                        </div>
                        <h3 class="text-lg sm:text-xl font-bold tracking-tight mt-0.5" x-text="pickerNurse?.name"></h3>
                        <p class="text-xs text-emerald-100" x-text="pickerNurse?.specialization || 'Perawat Homecare'"></p>
                    </div>
                </div>
                <button type="button" @click="nurseServicePickerOpen = false" class="text-emerald-200 hover:text-white p-1.5 rounded-xl hover:bg-white/10 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Subheader & Helper -->
            <div class="px-6 py-3 bg-emerald-50/80 border-b border-emerald-100 text-xs text-emerald-900 flex items-center justify-between">
                <span>Perawat ini menyediakan <strong x-text="pickerNurse?.services ? pickerNurse.services.length : 0"></strong> jenis tindakan asuhan keperawatan rumah. Silakan pilih tindakan yang Anda butuhkan:</span>
            </div>

            <!-- Modal Body (Grid of Services) -->
            <div class="p-6 overflow-y-auto space-y-3 max-h-[60vh]">
                <template x-for="s in pickerNurse?.services" :key="s.id">
                    <div class="p-4 rounded-2xl border border-slate-200 hover:border-emerald-500 hover:shadow-md transition-all bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-3 group cursor-pointer"
                        @click="selectNurseWithSpecificService(pickerNurse, s)">
                        <div class="flex items-start gap-3.5">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-2xl flex items-center justify-center shrink-0 border border-emerald-200/60 group-hover:scale-105 transition-transform"
                                x-text="s.image_emoji || '🩺'"></div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-900 group-hover:text-emerald-700 transition-colors" x-text="s.name"></h4>
                                <div class="flex items-center gap-2 mt-1 text-xs text-slate-500">
                                    <span class="inline-flex items-center gap-1">
                                        <span>⏱️</span>
                                        <span x-text="getServiceDuration(s.id)"></span>
                                    </span>
                                    <span>•</span>
                                    <span class="text-emerald-600 font-semibold">Tersedia untuk Homecare</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center sm:flex-col sm:items-end justify-between sm:justify-center border-t sm:border-t-0 pt-2 sm:pt-0 border-slate-100">
                            <div>
                                <span class="text-[10px] text-slate-400 block font-bold uppercase sm:text-right">Tarif Tindakan</span>
                                <span class="text-sm font-black text-emerald-700" x-text="s.formatted_price"></span>
                            </div>
                            <button type="button" 
                                class="mt-1 px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition group-hover:bg-emerald-700">
                                Pilih Tindakan &rarr;
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Modal Footer -->
            <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                <button type="button" @click="nurseServicePickerOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-xs font-bold text-slate-700 transition">
                    Kembali
                </button>
                <span class="text-[11px] text-slate-400">Ongkos transport terhitung transparan sesuai titik GPS nakes.</span>
            </div>
        </div>
    </div>

    <!-- 8. MODAL: MULTI-STEP PEMESANAN & PENJADWALAN KUNJUNGAN PASIEN -->
    <div x-show="bookingModalOpen" x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-6"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div @click.away="if (!submittingOrder && !orderSuccess) bookingModalOpen = false"
            class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl border border-slate-200 overflow-hidden text-slate-800 flex flex-col max-h-[92vh]"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100">

            <!-- Modal Header -->
            <div class="px-6 py-5 bg-gradient-to-r from-emerald-600 via-emerald-700 to-teal-700 text-white flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-2xl" x-text="activeService?.image_emoji"></div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-full bg-emerald-500/40 border border-white/20 text-emerald-100">
                                Formulir Pemesanan &amp; Penjadwalan
                            </span>
                            <span class="text-xs text-emerald-200 font-medium" x-show="!orderSuccess">
                                Langkah <span x-text="bookingStep"></span> dari 3
                            </span>
                        </div>
                        <h3 class="text-lg sm:text-xl font-bold tracking-tight mt-0.5" x-text="activeService?.name"></h3>
                    </div>
                </div>
                <button type="button" @click="closeBookingModal()" class="text-emerald-200 hover:text-white p-1 rounded-xl hover:bg-white/10 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Stepper Progress Bar (Only visible before success) -->
            <div x-show="!orderSuccess" class="bg-slate-50 border-b border-slate-200 px-6 py-3 shrink-0">
                <div class="flex items-center justify-between max-w-md mx-auto">
                    <!-- Step 1 Indicator -->
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-all"
                            :class="bookingStep >= 1 ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-200 text-slate-500'">
                            1
                        </div>
                        <span class="text-xs font-semibold" :class="bookingStep === 1 ? 'text-emerald-700' : 'text-slate-500'">Jadwal &amp; Pasien</span>
                    </div>

                    <div class="h-0.5 w-8 sm:w-12 bg-slate-200" :class="bookingStep >= 2 ? 'bg-emerald-500' : 'bg-slate-200'"></div>

                    <!-- Step 2 Indicator -->
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-all"
                            :class="bookingStep >= 2 ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-200 text-slate-500'">
                            2
                        </div>
                        <span class="text-xs font-semibold" :class="bookingStep === 2 ? 'text-emerald-700' : 'text-slate-500'">Alamat &amp; GPS</span>
                    </div>

                    <div class="h-0.5 w-8 sm:w-12 bg-slate-200" :class="bookingStep >= 3 ? 'bg-emerald-500' : 'bg-slate-200'"></div>

                    <!-- Step 3 Indicator -->
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-all"
                            :class="bookingStep >= 3 ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-200 text-slate-500'">
                            3
                        </div>
                        <span class="text-xs font-semibold" :class="bookingStep === 3 ? 'text-emerald-700' : 'text-slate-500'">Rincian Biaya</span>
                    </div>
                </div>
            </div>

            <!-- Modal Body (Scrollable) -->
            <div class="p-6 overflow-y-auto space-y-5 grow">

                <!-- Alert Pesan Error Validasi -->
                <div x-show="errorMessage" x-cloak class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-start gap-3">
                    <span class="text-base font-bold text-rose-500">⚠️</span>
                    <div class="grow" x-text="errorMessage"></div>
                    <button type="button" @click="errorMessage = ''" class="text-rose-400 hover:text-rose-700 font-bold">✕</button>
                </div>

                <!-- ==================== STEP 1: JADWAL & DATA PASIEN ==================== -->
                <div x-show="bookingStep === 1 && !orderSuccess" class="space-y-4">
                    <div class="border-b border-slate-100 pb-3">
                        <h4 class="text-sm font-bold text-slate-900">Penentuan Jadwal &amp; Data Penerima Layanan</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Pilih tanggal dan sesi jam kunjungan perawat ke lokasi Anda.</p>
                    </div>

                    <!-- Banner Mitra Perawat & Switcher Layanan -->
                    <template x-if="selectedNurse">
                        <div class="p-3.5 rounded-2xl bg-gradient-to-r from-emerald-50 via-teal-50 to-emerald-50 border border-emerald-200/90 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs shadow-2xs">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white font-black text-xs flex items-center justify-center shrink-0 shadow-sm"
                                    x-text="selectedNurse?.name ? selectedNurse.name.substring(0, 2).toUpperCase() : 'NS'">
                                </div>
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[10px] uppercase font-bold text-emerald-800 tracking-wider">Mitra Perawat:</span>
                                        <span class="font-extrabold text-slate-900" x-text="selectedNurse?.name"></span>
                                        <span class="text-emerald-600 text-xs font-bold" title="Terverifikasi STR/SIP">✓</span>
                                    </div>
                                    <span class="text-slate-500 text-[11px] block"
                                        x-text="(selectedNurse?.specialization || 'Perawat Homecare') + ' • Jarak: ' + (bookingForm.distance_km === 0 ? '0 km (Sangat Dekat)' : bookingForm.distance_km + ' km')"></span>
                                </div>
                            </div>

                            <!-- Pilihan Layanan Dropdown jika Perawat Memiliki Banyak Layanan -->
                            <template x-if="selectedNurse?.services && selectedNurse.services.length > 1">
                                <div class="shrink-0 flex items-center gap-2 bg-white px-3 py-1.5 rounded-xl border border-emerald-200 shadow-2xs">
                                    <label class="text-[10px] font-bold uppercase text-slate-500 whitespace-nowrap">Ubah Tindakan:</label>
                                    <select @change="changeSelectedServiceFromNurse($event.target.value)"
                                        class="text-xs font-bold text-emerald-800 bg-transparent border-0 focus:ring-0 p-0 cursor-pointer">
                                        <template x-for="s in selectedNurse.services" :key="s.id">
                                            <option :value="s.id" :selected="s.id === activeService?.id" x-text="s.name + ' (' + s.formatted_price + ')'"></option>
                                        </template>
                                    </select>
                                </div>
                            </template>
                        </div>
                    </template>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Tanggal Kunjungan -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Tanggal Kunjungan <span class="text-rose-500">*</span>
                            </label>
                            <input type="date"
                                x-model="bookingForm.scheduled_date"
                                :min="todayDate"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-xs" />
                            <p class="text-[10px] text-slate-400 mt-1">Dapat dijadwalkan mulai hari ini atau hari berikutnya.</p>
                        </div>

                        <!-- Sesi Waktu Kunjungan -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Sesi Waktu Kunjungan <span class="text-rose-500">*</span>
                            </label>
                            <select x-model="bookingForm.scheduled_time"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-xs">
                                <option value="08:00 - 10:00">Pagi (08:00 - 10:00 WIB)</option>
                                <option value="10:30 - 12:30">Siang (10:30 - 12:30 WIB)</option>
                                <option value="13:30 - 15:30">Siang-Sore (13:30 - 15:30 WIB)</option>
                                <option value="16:00 - 18:00">Sore (16:00 - 18:00 WIB)</option>
                                <option value="19:00 - 21:00">Malam (19:00 - 21:00 WIB)</option>
                            </select>
                            <p class="text-[10px] text-slate-400 mt-1">Estimasi kedatangan perawat di awal rentang sesi.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Nama Lengkap Pasien -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Nama Pasien <span class="text-rose-500">*</span>
                            </label>
                            <input type="text"
                                x-model="bookingForm.patient_name"
                                placeholder="Contoh: Bpk. Gunawan / Ibu Ratna"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-xs" />
                        </div>

                        <!-- Nomor Kontak WhatsApp -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Nomor WhatsApp / HP Aktif <span class="text-rose-500">*</span>
                            </label>
                            <input type="tel"
                                x-model="bookingForm.patient_phone"
                                placeholder="08xxxxxxxxxx"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-xs" />
                        </div>
                    </div>

                    <!-- Catatan Kondisi Klinis / Keluhan Awal -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Catatan Kondisi Medis / Keluhan Awal Pasien
                        </label>
                        <textarea x-model="bookingForm.clinical_notes"
                            rows="2"
                            placeholder="Deskripsikan kondisi terkini, misal: luka pasca operasi hari ke-5, sudah ada resep salep dokter, pasien tirah baring..."
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-xs"></textarea>
                    </div>

                    <!-- Unggah Foto Kondisi Medis (Opsional) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Foto Area Tindakan / Luka (Opsional)
                        </label>
                        <input type="file"
                            accept="image/*"
                            @change="handlePhotoUpload($event)"
                            class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer" />
                        <p class="text-[10px] text-slate-400 mt-1">Membantu perawat mempersiapkan alat steril &amp; balutan yang sesuai (Maksimal 5 MB, JPG/PNG).</p>
                    </div>
                </div>

                <!-- ==================== STEP 2: ALAMAT & LOKASI GPS (LEAFLET) ==================== -->
                <div x-show="bookingStep === 2 && !orderSuccess" class="space-y-4">
                    <div class="border-b border-slate-100 pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">Alamat Kunjungan &amp; Titik Lokasi GPS</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Tentukan titik akurat untuk navigasi perawat dan perhitungan tarif transportasi.</p>
                        </div>
                        <button type="button"
                            @click="detectCurrentLocation()"
                            :disabled="detectingLocation"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-xs font-bold transition shrink-0">
                            <span x-show="!detectingLocation">📍</span>
                            <span x-show="detectingLocation" class="animate-spin text-xs">⏳</span>
                            <span x-text="detectingLocation ? 'Mencari GPS...' : 'Deteksi Lokasi Saya'"></span>
                        </button>
                    </div>

                    <!-- Alamat Lengkap & Patokan -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Alamat Lengkap Rumah / Patokan Lokasi <span class="text-rose-500">*</span>
                        </label>
                        <textarea x-model="bookingForm.address"
                            rows="2"
                            placeholder="Jl. Mawar No. 12, RT 03/RW 05 (Pagar hitam samping pos satpam), Kelurahan..."
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-xs"></textarea>
                    </div>

                    <!-- Peta Leaflet Interaktif -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs font-bold text-slate-700">
                                Titik Pinpoint Peta Digital (Geser marker untuk menyesuaikan titik)
                            </label>
                            <span class="text-[11px] text-slate-500 font-medium">
                                Koordinat: <span class="font-mono text-emerald-700 font-semibold" x-text="bookingForm.latitude + ', ' + bookingForm.longitude"></span>
                            </span>
                        </div>
                        
                        <div id="bookingLeafletMap" class="w-full h-56 rounded-2xl border border-slate-200 shadow-inner z-0"></div>
                        <p class="text-[10px] text-slate-400 mt-1.5 flex items-center gap-1">
                            <span>💡</span> Klik di peta atau geser pin merah untuk menggeser titik koordinat penjemputan perawat.
                        </p>
                    </div>

                    <!-- Jarak & Radius Operasional Box -->
                    <div class="p-3.5 rounded-2xl border flex items-center justify-between"
                        :class="bookingForm.distance_km <= 10.0 ? 'bg-emerald-50/80 border-emerald-200 text-emerald-900' : 'bg-rose-50 border-rose-200 text-rose-900'">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-lg"
                                :class="bookingForm.distance_km <= 10.0 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'">
                                <span x-show="bookingForm.distance_km <= 10.0">🛵</span>
                                <span x-show="bookingForm.distance_km > 10.0">⚠️</span>
                            </div>
                            <div>
                                <div class="text-xs font-bold">
                                    <span x-text="selectedNurse ? ('Estimasi Jarak dari Ns. ' + selectedNurse.name + ':') : 'Estimasi Jarak dari Pusat Nakes:'"></span>
                                    <span class="text-sm font-black text-emerald-800" x-text="(bookingForm.distance_km === 0 ? '0 km (Sangat Dekat)' : bookingForm.distance_km + ' km')"></span>
                                </div>
                                <div class="text-[11px]"
                                    x-text="bookingForm.distance_km <= 10.0 ? '✓ Lokasi berada di dalam radius operasional layanan homecare (Maks. 10 km)' : 'Lokasi melebihi radius operasional maksimal (10 km).'"></div>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Ongkos Transport</span>
                            <span class="text-xs font-bold text-emerald-700" x-text="breakdown.formatted.transport_fee"></span>
                        </div>
                    </div>
                </div>

                <!-- ==================== STEP 3: RINCIAN BIAYA & KONFIRMASI ==================== -->
                <div x-show="bookingStep === 3 && !orderSuccess" class="space-y-4">
                    <div class="border-b border-slate-100 pb-3">
                        <h4 class="text-sm font-bold text-slate-900">Rincian Biaya (Price Breakdown) &amp; Konfirmasi</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Periksa kembali ringkasan pesanan dan transparansi biaya sebelum mengonfirmasi.</p>
                    </div>

                    <!-- Ringkasan Informasi Pesanan -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3 text-xs">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-400 block">Layanan Tindakan</span>
                                <span class="font-bold text-slate-900" x-text="activeService?.name"></span>
                                <span class="text-[11px] text-slate-500 block" x-text="'Durasi: ' + activeService?.estimated_duration"></span>
                            </div>
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-400 block">Jadwal Kunjungan</span>
                                <span class="font-bold text-slate-900" x-text="formatDisplayDate(bookingForm.scheduled_date)"></span>
                                <span class="text-[11px] text-emerald-700 font-semibold block" x-text="bookingForm.scheduled_time"></span>
                            </div>
                        </div>

                        <div class="border-t border-slate-200/80 pt-2 grid grid-cols-2 gap-3">
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-400 block">Nama Pasien &amp; Kontak</span>
                                <span class="font-semibold text-slate-900" x-text="bookingForm.patient_name"></span>
                                <span class="text-[11px] text-slate-500 block" x-text="bookingForm.patient_phone"></span>
                            </div>
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-400 block">Alamat Kunjungan</span>
                                <span class="text-slate-800 line-clamp-2" x-text="bookingForm.address"></span>
                            </div>
                        </div>

                        <template x-if="bookingForm.clinical_notes">
                            <div class="border-t border-slate-200/80 pt-2">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block">Catatan Medis Awal:</span>
                                <p class="text-slate-700 italic" x-text="bookingForm.clinical_notes"></p>
                            </div>
                        </template>
                    </div>

                    <!-- Breakdown Biaya Transparan -->
                    <div class="rounded-2xl border border-slate-200 overflow-hidden text-xs">
                        <div class="px-4 py-2.5 bg-slate-100/80 font-bold text-slate-700 flex justify-between items-center">
                            <span>Komponen Rincian Biaya</span>
                            <span>Tarif Transparan</span>
                        </div>
                        <div class="p-4 space-y-2.5 bg-white">
                            <div class="flex justify-between items-center text-slate-600">
                                <span>Tarif Tindakan Medis Standar:</span>
                                <span class="font-semibold text-slate-900" x-text="breakdown.formatted.base_price"></span>
                            </div>
                            <div class="flex justify-between items-center text-slate-600">
                                <span class="flex items-center gap-1">
                                    <span>Ongkos Transportasi GPS:</span>
                                    <span class="text-[10px] text-slate-400" x-text="'(' + bookingForm.distance_km + ' km)'"></span>
                                </span>
                                <span class="font-semibold text-slate-900" x-text="breakdown.formatted.transport_fee"></span>
                            </div>
                            <div class="flex justify-between items-center text-slate-600">
                                <span>Biaya Layanan Aplikasi (Platform Fee):</span>
                                <span class="font-semibold text-slate-900" x-text="breakdown.formatted.platform_fee"></span>
                            </div>

                            <div class="border-t border-slate-200 pt-3 flex justify-between items-baseline">
                                <div>
                                    <span class="text-xs font-bold uppercase tracking-wider text-slate-900 block">Total Tagihan</span>
                                    <span class="text-[10px] text-slate-500">Sudah termasuk PPN &amp; seluruh komponen</span>
                                </div>
                                <span class="text-xl font-extrabold text-emerald-700" x-text="breakdown.formatted.total_amount"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Info Perlindungan Escrow -->
                    <div class="p-3.5 rounded-2xl bg-teal-50 border border-teal-200 text-teal-900 text-xs flex items-start gap-2.5">
                        <span class="text-lg">🛡️</span>
                        <div>
                            <span class="font-bold block">Garansi Perlindungan Rekening Penampung (Escrow)</span>
                            <span class="text-[11px] text-teal-800 leading-relaxed block mt-0.5">
                                Pembayaran ditampung di rekening bersama platform dan baru diteruskan kepada perawat setelah kunjungan medis terlaksana dan E-Report telah Anda setujui.
                            </span>
                        </div>
                    </div>
                </div>

                <!-- ==================== STEP 4: ORDER CREATED SUCCESS STATE ==================== -->
                <div x-show="orderSuccess" class="py-6 text-center space-y-4">
                    <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 mx-auto flex items-center justify-center text-3xl shadow-sm">
                        ✓
                    </div>
                    <div>
                        <span class="text-xs uppercase font-bold tracking-wider text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                            Pesanan Kunjungan Berhasil Dibuat
                        </span>
                        <h3 class="text-xl font-bold text-slate-900 mt-2">Permintaan Layanan Siap Diteruskan</h3>
                        <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                            Pesanan Anda telah tercatat ke dalam sistem CareAtHome dan akan segera dikonfirmasi oleh mitra perawat terdekat.
                        </p>
                    </div>

                    <div class="max-w-md mx-auto p-4 rounded-2xl bg-slate-50 border border-slate-200 text-left text-xs space-y-2">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Nomor Pesanan:</span>
                            <span class="font-mono font-bold text-slate-900" x-text="createdOrderData?.order_number"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Layanan:</span>
                            <span class="font-semibold text-slate-900" x-text="activeService?.name"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Jadwal:</span>
                            <span class="font-semibold text-slate-900" x-text="createdOrderData?.scheduled_date + ' (' + createdOrderData?.scheduled_time + ')'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Total Biaya:</span>
                            <span class="font-bold text-emerald-700" x-text="createdOrderData?.price_breakdown?.formatted?.total_amount"></span>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t border-slate-200">
                            <span class="text-slate-500">Status Awal:</span>
                            <span class="px-2.5 py-0.5 rounded-full bg-amber-50 border border-amber-200 text-amber-800 font-bold text-[11px]">
                                Menunggu Konfirmasi Perawat
                            </span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Modal Footer Controls -->
            <div class="p-4 sm:p-5 bg-slate-50 border-t border-slate-200 flex items-center justify-between shrink-0">
                <!-- Left Action -->
                <div>
                    <button type="button"
                        x-show="bookingStep > 1 && !orderSuccess"
                        @click="prevStep()"
                        class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-xs font-bold text-slate-700 transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        <span>Kembali</span>
                    </button>
                    <button type="button"
                        x-show="bookingStep === 1 && !orderSuccess"
                        @click="closeBookingModal()"
                        class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-xs font-bold text-slate-700 transition">
                        Batal
                    </button>
                </div>

                <!-- Right Action -->
                <div>
                    <!-- Step 1 & 2: Tombol Lanjut -->
                    <button type="button"
                        x-show="bookingStep < 3 && !orderSuccess"
                        @click="nextStep()"
                        class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5">
                        <span>Lanjut ke Tahap Berikutnya</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>

                    <!-- Step 3: Tombol Konfirmasi Pesanan -->
                    <button type="button"
                        x-show="bookingStep === 3 && !orderSuccess"
                        @click="submitOrder()"
                        :disabled="submittingOrder || bookingForm.distance_km > 10.0"
                        class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white text-xs font-bold shadow-md shadow-emerald-600/30 transition flex items-center gap-2">
                        <span x-show="submittingOrder" class="animate-spin text-sm">⏳</span>
                        <span x-text="submittingOrder ? 'Memproses Pesanan...' : 'Konfirmasi &amp; Buat Pesanan'"></span>
                        <svg x-show="!submittingOrder" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </button>

                    <!-- Success State: Tombol Selesai & Ke Jadwal Saya -->
                    <div x-show="orderSuccess" class="flex items-center gap-2">
                        <a href="{{ route('pasien.pesanan') }}"
                            class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-md">
                            <span>Lihat di Jadwal Saya &amp; Bayar</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                        <button type="button"
                            @click="closeBookingModal()"
                            class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-xs font-bold text-slate-700 transition">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- 9A. FLOATING COMPARISON BAR -->
    <div x-show="compareList.length > 0" x-cloak
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-8"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-8"
        class="fixed bottom-0 inset-x-0 z-40 pb-4 px-4">
        <div class="max-w-4xl mx-auto bg-gradient-to-r from-indigo-600 via-indigo-700 to-violet-700 rounded-2xl shadow-2xl shadow-indigo-600/30 border border-indigo-500/30 p-4 flex items-center justify-between gap-4">
            
            <!-- Left: Selected Items Preview -->
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-white/15 backdrop-blur-sm border border-white/20 flex items-center justify-center text-white shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="text-white font-bold text-sm">Bandingkan Layanan</span>
                        <span class="px-2 py-0.5 rounded-full bg-white/20 text-white text-[10px] font-black border border-white/20" x-text="compareList.length + '/3'"></span>
                    </div>
                    <div class="flex items-center gap-1.5 mt-1">
                        <template x-for="cItem in compareList" :key="cItem.id">
                            <div class="flex items-center gap-1 bg-white/10 backdrop-blur-sm rounded-lg px-2 py-0.5 border border-white/10">
                                <span class="text-xs" x-text="cItem.image_emoji"></span>
                                <span class="text-[10px] text-white/90 font-medium truncate max-w-[80px]" x-text="cItem.name"></span>
                                <button type="button" @click="removeFromCompare(cItem.id)" class="text-white/50 hover:text-white ml-0.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Right: Actions -->
            <div class="flex items-center gap-2 shrink-0">
                <button type="button" @click="clearCompare()"
                    class="px-3 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white/80 hover:text-white text-xs font-bold border border-white/10 transition">
                    Hapus
                </button>
                <button type="button" @click="openCompareModal()"
                    :disabled="compareList.length < 2"
                    class="px-4 py-2 rounded-xl bg-white hover:bg-indigo-50 text-indigo-700 text-xs font-extrabold shadow-md transition disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span>Bandingkan Sekarang</span>
                </button>
            </div>
        </div>
    </div>

    <!-- 9B. MODAL: PERBANDINGAN LAYANAN SIDE-BY-SIDE -->
    <div x-show="compareModalOpen" x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-6"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div @click.away="compareModalOpen = false"
            class="bg-white rounded-3xl w-full shadow-2xl border border-slate-200 overflow-hidden text-slate-800 flex flex-col"
            :class="compareList.length === 3 ? 'max-w-5xl' : 'max-w-3xl'"
            style="max-height: 92vh;"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100">

            <!-- Modal Header -->
            <div class="px-6 py-5 bg-gradient-to-r from-indigo-600 via-indigo-700 to-violet-700 text-white flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-full bg-indigo-500/40 border border-white/20 text-indigo-100">
                            Perbandingan Layanan
                        </span>
                        <h3 class="text-lg font-bold tracking-tight mt-0.5">Bandingkan <span x-text="compareList.length"></span> Layanan Medis</h3>
                    </div>
                </div>
                <button type="button" @click="compareModalOpen = false" class="text-indigo-200 hover:text-white p-1 rounded-xl hover:bg-white/10 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Comparison Table Body -->
            <div class="overflow-y-auto overflow-x-auto grow">
                <table class="w-full text-xs">
                    <!-- Row: Service Name & Emoji -->
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="text-left px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 w-36 sticky left-0 bg-slate-50 z-10">Atribut</th>
                            <template x-for="cItem in compareList" :key="cItem.id">
                                <th class="text-center px-4 py-3 min-w-[180px]">
                                    <div class="flex flex-col items-center gap-1.5">
                                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-2xl">
                                            <span x-text="cItem.image_emoji"></span>
                                        </div>
                                        <span class="font-bold text-sm text-slate-900 leading-tight" x-text="cItem.name"></span>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200" x-text="cItem.category"></span>
                                    </div>
                                </th>
                            </template>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <!-- Row: Plafon Tarif -->
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-3 font-bold text-slate-600 sticky left-0 bg-white z-10">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-emerald-600">💰</span> Plafon Tarif
                                </div>
                            </td>
                            <template x-for="cItem in compareList" :key="'price-'+cItem.id">
                                <td class="px-4 py-3 text-center">
                                    <span class="text-base font-extrabold" x-text="cItem.formatted_price"
                                        :class="cItem.price == getLowestComparePrice() ? 'text-emerald-700' : 'text-slate-800'"></span>
                                    <template x-if="cItem.price == getLowestComparePrice() && compareList.length > 1">
                                        <span class="block text-[10px] font-bold text-emerald-600 mt-0.5">✨ Termurah</span>
                                    </template>
                                </td>
                            </template>
                        </tr>

                        <!-- Row: Estimasi Durasi -->
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-3 font-bold text-slate-600 sticky left-0 bg-white z-10">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-blue-500">⏱️</span> Durasi
                                </div>
                            </td>
                            <template x-for="cItem in compareList" :key="'dur-'+cItem.id">
                                <td class="px-4 py-3 text-center">
                                    <span class="font-semibold text-slate-800" x-text="cItem.estimated_duration"></span>
                                    <template x-if="cItem.estimated_duration_minutes == getShortestCompareDuration() && compareList.length > 1">
                                        <span class="block text-[10px] font-bold text-blue-600 mt-0.5">⚡ Tercepat</span>
                                    </template>
                                </td>
                            </template>
                        </tr>

                        <!-- Row: Deskripsi -->
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-3 font-bold text-slate-600 sticky left-0 bg-white z-10">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-slate-400">📋</span> Deskripsi
                                </div>
                            </td>
                            <template x-for="cItem in compareList" :key="'desc-'+cItem.id">
                                <td class="px-4 py-3 text-left">
                                    <p class="text-slate-600 leading-relaxed line-clamp-4" x-text="cItem.description"></p>
                                </td>
                            </template>
                        </tr>

                        <!-- Row: Indikasi Medis -->
                        <tr class="hover:bg-emerald-50/30">
                            <td class="px-4 py-3 font-bold text-slate-600 sticky left-0 bg-white z-10">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-emerald-600">✅</span> Indikasi
                                </div>
                            </td>
                            <template x-for="cItem in compareList" :key="'ind-'+cItem.id">
                                <td class="px-4 py-3 text-left">
                                    <p class="text-emerald-800 leading-relaxed text-[11px]" x-text="cItem.indications || 'Sesuai asesmen perawat'"></p>
                                </td>
                            </template>
                        </tr>

                        <!-- Row: Kontraindikasi -->
                        <tr class="hover:bg-rose-50/30">
                            <td class="px-4 py-3 font-bold text-slate-600 sticky left-0 bg-white z-10">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-rose-500">⚠️</span> Kontra&shy;indikasi
                                </div>
                            </td>
                            <template x-for="cItem in compareList" :key="'contra-'+cItem.id">
                                <td class="px-4 py-3 text-left">
                                    <p class="text-rose-700 leading-relaxed text-[11px]" x-text="cItem.contraindications || 'Tidak untuk emergency ICU/UGD'"></p>
                                </td>
                            </template>
                        </tr>

                        <!-- Row: Termasuk Dalam Layanan -->
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-3 font-bold text-slate-600 align-top sticky left-0 bg-white z-10">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-teal-600">📦</span> Inklusi
                                </div>
                            </td>
                            <template x-for="cItem in compareList" :key="'inc-'+cItem.id">
                                <td class="px-4 py-3 align-top">
                                    <div class="space-y-1">
                                        <template x-for="(inc, idx) in (cItem.inclusions || []).slice(0, 5)" :key="idx">
                                            <div class="flex items-start gap-1 text-[11px] text-slate-700">
                                                <span class="text-emerald-600 font-bold shrink-0">✓</span>
                                                <span x-text="inc"></span>
                                            </div>
                                        </template>
                                        <template x-if="(cItem.inclusions || []).length > 5">
                                            <span class="text-[10px] text-indigo-600 font-semibold">+<span x-text="cItem.inclusions.length - 5"></span> lainnya</span>
                                        </template>
                                        <template x-if="!cItem.inclusions || cItem.inclusions.length === 0">
                                            <span class="text-[11px] text-slate-400 italic">Tidak tersedia</span>
                                        </template>
                                    </div>
                                </td>
                            </template>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Modal Footer -->
            <div class="p-4 sm:p-5 bg-slate-50 border-t border-slate-200 flex items-center justify-between gap-3 shrink-0">
                <button type="button" @click="compareModalOpen = false" class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-xs font-bold text-slate-700 transition">
                    Tutup Perbandingan
                </button>
                <div class="flex items-center gap-2">
                    <button type="button" @click="clearCompare(); compareModalOpen = false" class="px-3 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-xs font-bold text-slate-500 transition">
                        Hapus Semua
                    </button>
                    <button type="button" @click="bookCheapestFromCompare()" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5">
                        <span>Pesan yang Termurah</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 9. FOOTER -->
    <footer class="mt-auto border-t border-slate-200 bg-white py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-xs text-slate-500">
            CareAtHome Health-Tech Platform &copy; 2026. Platform Layanan Keperawatan On-Demand Terstandar Kemenkes RI.
        </div>
    </footer>

    <!-- Alpine App Logic for Instant Filtering, Search, and Multi-Step Booking -->
    <script>
        function patientCatalogApp() {
            const today = new Date().toISOString().split('T')[0];

            return {
                rawServices: @json($services),
                availableCategories: @json($categories),
                filteredServices: [],
                search: @json($currentSearch ?? ''),
                selectedCategory: @json($currentCategory ?? 'Semua'),
                sortBy: @json($currentSort ?? 'nama_asc'),
                priceBracket: @json($currentPriceBracket ?? 'all'),
                detailModalOpen: false,
                bookingModalOpen: false,
                compareModalOpen: false,
                nurseServicePickerOpen: false,
                pickerNurse: null,
                compareList: [],
                activeService: null,

                // State Booking & Penjadwalan
                bookingStep: 1,
                todayDate: today,
                submittingOrder: false,
                orderSuccess: false,
                createdOrderData: null,
                errorMessage: '',
                detectingLocation: false,
                uploadedPhotoFile: null,

                // Nearby Nurses State (BAGIAN 3)
                patientLat: -6.2088,
                patientLng: 106.8456,
                nearbyNurses: [],
                loadingNurses: false,
                selectedNurse: null,

                bookingForm: {
                    nurse_id: null,
                    scheduled_date: today,
                    scheduled_time: '08:00 - 10:00',
                    patient_name: @json(auth()->user()?->name ?? 'Pasien CareAtHome'),
                    patient_phone: '081234567890',
                    address: '',
                    latitude: -6.2088,
                    longitude: 106.8456,
                    distance_km: 0.5,
                    clinical_notes: '',
                },

                breakdown: {
                    base_price: 0,
                    distance_km: 0.5,
                    transport_fee: 10000,
                    platform_fee: 5000,
                    total_amount: 15000,
                    formatted: {
                        base_price: 'Rp 0',
                        transport_fee: 'Rp 10.000',
                        platform_fee: 'Rp 5.000',
                        total_amount: 'Rp 15.000',
                    }
                },

                map: null,
                marker: null,

                initData() {
                    this.filterServices();
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            (pos) => {
                                this.patientLat = parseFloat(pos.coords.latitude.toFixed(6));
                                this.patientLng = parseFloat(pos.coords.longitude.toFixed(6));
                                this.bookingForm.latitude = this.patientLat;
                                this.bookingForm.longitude = this.patientLng;
                                this.fetchNearbyNurses();
                            },
                            () => {
                                this.fetchNearbyNurses();
                            },
                            { enableHighAccuracy: true, timeout: 6000 }
                        );
                    } else {
                        this.fetchNearbyNurses();
                    }
                },

                async fetchNearbyNurses() {
                    this.loadingNurses = true;
                    try {
                        const url = `/api/v1/perawat/terdekat?latitude=${this.patientLat}&longitude=${this.patientLng}`;
                        const res = await fetch(url);
                        const data = await res.json();
                        if (res.ok && data.status === 'success') {
                            this.nearbyNurses = data.data || [];
                        }
                    } catch (e) {
                        console.warn('Gagal memuat perawat terdekat:', e);
                    } finally {
                        this.loadingNurses = false;
                    }
                },

                requestBrowserLocation() {
                    if (!navigator.geolocation) {
                        alert('Browser Anda tidak mendukung Geolocation.');
                        return;
                    }
                    this.loadingNurses = true;
                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            this.patientLat = parseFloat(pos.coords.latitude.toFixed(6));
                            this.patientLng = parseFloat(pos.coords.longitude.toFixed(6));
                            this.bookingForm.latitude = this.patientLat;
                            this.bookingForm.longitude = this.patientLng;
                            this.fetchNearbyNurses();
                        },
                        (err) => {
                            this.loadingNurses = false;
                            alert('Gagal mendeteksi lokasi GPS: ' + err.message);
                        },
                        { enableHighAccuracy: true, timeout: 8000 }
                    );
                },

                openNurseServicePicker(nurse) {
                    this.selectedNurse = nurse;
                    this.bookingForm.nurse_id = nurse.nurse_id;
                    if (nurse.distance_km !== undefined && nurse.distance_km !== null) {
                        this.bookingForm.distance_km = parseFloat(nurse.distance_km);
                    }
                    if (nurse.latitude && (this.bookingForm.latitude === -6.2088 || this.patientLat === -6.2088)) {
                        this.bookingForm.latitude = parseFloat(nurse.latitude);
                        this.bookingForm.longitude = parseFloat(nurse.longitude);
                    }

                    if (nurse.services && nurse.services.length > 1) {
                        this.pickerNurse = nurse;
                        this.nurseServicePickerOpen = true;
                    } else if (nurse.services && nurse.services.length === 1) {
                        const match = this.rawServices.find(s => s.id === nurse.services[0].id) || this.rawServices[0];
                        this.openBookingModal(match);
                    } else if (this.rawServices.length > 0) {
                        this.openBookingModal(this.rawServices[0]);
                    }
                },

                selectNurseWithSpecificService(nurse, service) {
                    this.selectedNurse = nurse;
                    this.bookingForm.nurse_id = nurse.nurse_id;
                    if (nurse.distance_km !== undefined && nurse.distance_km !== null) {
                        this.bookingForm.distance_km = parseFloat(nurse.distance_km);
                    }
                    if (nurse.latitude && (this.bookingForm.latitude === -6.2088 || this.patientLat === -6.2088)) {
                        this.bookingForm.latitude = parseFloat(nurse.latitude);
                        this.bookingForm.longitude = parseFloat(nurse.longitude);
                    }
                    this.nurseServicePickerOpen = false;

                    const match = this.rawServices.find(s => s.id === service.id) || service;
                    this.openBookingModal(match);
                },

                selectNurseForBooking(nurse) {
                    this.openNurseServicePicker(nurse);
                },

                changeSelectedServiceFromNurse(serviceId) {
                    const id = parseInt(serviceId);
                    const match = this.rawServices.find(s => s.id === id) || 
                                 (this.selectedNurse?.services?.find(s => s.id === id));
                    if (match) {
                        this.activeService = match;
                        this.fetchPriceBreakdown();
                    }
                },

                getServiceDuration(serviceId) {
                    const match = this.rawServices.find(s => s.id === serviceId);
                    return match?.estimated_duration || '60 Menit';
                },

                setCategory(cat) {
                    this.selectedCategory = cat;
                    this.filterServices();
                },

                setPriceFilter(bracket) {
                    this.priceBracket = bracket;
                    this.filterServices();
                },

                resetFilters() {
                    this.search = '';
                    this.selectedCategory = 'Semua';
                    this.sortBy = 'nama_asc';
                    this.priceBracket = 'all';
                    this.filterServices();
                },

                filterServices() {
                    let items = [...this.rawServices];

                    if (this.selectedCategory && this.selectedCategory !== 'Semua') {
                        items = items.filter(s => s.category === this.selectedCategory);
                    }

                    if (this.search && this.search.trim().length > 0) {
                        const q = this.search.toLowerCase().trim();
                        items = items.filter(s => {
                            const matchName = s.name.toLowerCase().includes(q);
                            const matchDesc = s.description.toLowerCase().includes(q);
                            const matchCat = s.category.toLowerCase().includes(q);
                            const matchInc = Array.isArray(s.inclusions) && s.inclusions.some(i => i.toLowerCase().includes(q));
                            return matchName || matchDesc || matchCat || matchInc;
                        });
                    }

                    if (this.priceBracket === 'under_150k') {
                        items = items.filter(s => parseFloat(s.price) < 150000);
                    } else if (this.priceBracket === '150k_200k') {
                        items = items.filter(s => parseFloat(s.price) >= 150000 && parseFloat(s.price) <= 200000);
                    } else if (this.priceBracket === 'above_200k') {
                        items = items.filter(s => parseFloat(s.price) > 200000);
                    }

                    items.sort((a, b) => {
                        if (this.sortBy === 'termurah') {
                            return parseFloat(a.price) - parseFloat(b.price);
                        } else if (this.sortBy === 'termahal') {
                            return parseFloat(b.price) - parseFloat(a.price);
                        } else if (this.sortBy === 'durasi_singkat') {
                            return (a.estimated_duration_minutes || 60) - (b.estimated_duration_minutes || 60);
                        } else {
                            return a.name.localeCompare(b.name);
                        }
                    });

                    this.filteredServices = items;
                },

                openDetailModal(service) {
                    this.activeService = service;
                    this.detailModalOpen = true;
                },

                openBookingModal(service) {
                    this.activeService = service;
                    this.bookingStep = 1;
                    this.orderSuccess = false;
                    this.createdOrderData = null;
                    this.errorMessage = '';
                    this.uploadedPhotoFile = null;
                    this.bookingModalOpen = true;

                    // Sinkronisasi jarak awal dan koordinat
                    if (this.selectedNurse) {
                        if (this.selectedNurse.distance_km !== undefined && this.selectedNurse.distance_km !== null) {
                            this.bookingForm.distance_km = parseFloat(this.selectedNurse.distance_km);
                        }
                        if (this.bookingForm.latitude === -6.2088) {
                            if (this.patientLat !== -6.2088) {
                                this.bookingForm.latitude = this.patientLat;
                                this.bookingForm.longitude = this.patientLng;
                            } else if (this.selectedNurse.latitude) {
                                this.bookingForm.latitude = parseFloat(this.selectedNurse.latitude);
                                this.bookingForm.longitude = parseFloat(this.selectedNurse.longitude);
                            }
                        }
                    } else if (this.bookingForm.latitude === -6.2088 && this.patientLat !== -6.2088) {
                        this.bookingForm.latitude = this.patientLat;
                        this.bookingForm.longitude = this.patientLng;
                    }

                    this.fetchPriceBreakdown();
                },

                closeBookingModal() {
                    this.bookingModalOpen = false;
                    this.bookingStep = 1;
                    this.errorMessage = '';
                    this.orderSuccess = false;
                },

                nextStep() {
                    this.errorMessage = '';

                    // Validasi Step 1
                    if (this.bookingStep === 1) {
                        if (!this.bookingForm.scheduled_date) {
                            this.errorMessage = 'Silakan pilih tanggal kunjungan terlebih dahulu.';
                            return;
                        }
                        if (!this.bookingForm.scheduled_time) {
                            this.errorMessage = 'Silakan pilih sesi waktu kunjungan.';
                            return;
                        }
                        if (!this.bookingForm.patient_name || this.bookingForm.patient_name.trim().length === 0) {
                            this.errorMessage = 'Nama lengkap pasien wajib diisi.';
                            return;
                        }
                        if (!this.bookingForm.patient_phone || this.bookingForm.patient_phone.trim().length < 8) {
                            this.errorMessage = 'Nomor telepon/WhatsApp aktif wajib diisi minimal 8 digit.';
                            return;
                        }

                        this.bookingStep = 2;
                        this.initLeafletMap();
                        return;
                    }

                    // Validasi Step 2
                    if (this.bookingStep === 2) {
                        if (!this.bookingForm.address || this.bookingForm.address.trim().length < 5) {
                            this.errorMessage = 'Silakan masukkan alamat lengkap rumah atau patokan lokasi kunjungan.';
                            return;
                        }
                        if (this.bookingForm.distance_km > 10.0) {
                            this.errorMessage = 'Lokasi di luar radius operasional maksimal (10 km). Silakan pilih lokasi terdekat.';
                            return;
                        }

                        this.bookingStep = 3;
                        this.fetchPriceBreakdown();
                        return;
                    }
                },

                prevStep() {
                    this.errorMessage = '';
                    if (this.bookingStep > 1) {
                        this.bookingStep--;
                        if (this.bookingStep === 2) {
                            this.initLeafletMap();
                        }
                    }
                },

                initLeafletMap() {
                    this.$nextTick(() => {
                        const container = document.getElementById('bookingLeafletMap');
                        if (!container) return;

                        let lat = this.bookingForm.latitude;
                        let lng = this.bookingForm.longitude;

                        // Gunakan koordinat GPS pasien atau perawat jika form masih default Jakarta
                        if (lat === -6.2088) {
                            if (this.patientLat !== -6.2088) {
                                lat = this.patientLat;
                                lng = this.patientLng;
                            } else if (this.selectedNurse && this.selectedNurse.latitude) {
                                lat = parseFloat(this.selectedNurse.latitude);
                                lng = parseFloat(this.selectedNurse.longitude);
                            }
                            this.bookingForm.latitude = lat;
                            this.bookingForm.longitude = lng;
                        }

                        if (!this.map) {
                            this.map = L.map('bookingLeafletMap').setView([lat, lng], 15);

                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                maxZoom: 19,
                                attribution: '&copy; OpenStreetMap'
                            }).addTo(this.map);

                            this.marker = L.marker([lat, lng], { draggable: true }).addTo(this.map);

                            this.marker.on('dragend', (e) => {
                                const pos = e.target.getLatLng();
                                this.updateLocation(pos.lat, pos.lng);
                            });

                            this.map.on('click', (e) => {
                                this.marker.setLatLng(e.latlng);
                                this.updateLocation(e.latlng.lat, e.latlng.lng);
                            });
                        } else {
                            this.map.setView([lat, lng], 15);
                            if (this.marker) {
                                this.marker.setLatLng([lat, lng]);
                            }
                            setTimeout(() => {
                                this.map.invalidateSize();
                            }, 100);
                        }

                        // Sinkronkan jarak aktual saat map diinisialisasi
                        this.updateLocation(lat, lng);
                    });
                },

                detectCurrentLocation() {
                    if (!navigator.geolocation) {
                        this.errorMessage = 'Fitur geolokasi tidak didukung oleh browser Anda.';
                        return;
                    }

                    this.detectingLocation = true;
                    this.errorMessage = '';

                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            this.detectingLocation = false;
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;

                            this.updateLocation(lat, lng);

                            if (this.map && this.marker) {
                                this.map.setView([lat, lng], 15);
                                this.marker.setLatLng([lat, lng]);
                            }
                        },
                        (err) => {
                            this.detectingLocation = false;
                            this.errorMessage = 'Gagal mendeteksi lokasi GPS: ' + (err.message || 'Izin akses lokasi ditolak');
                        },
                        { timeout: 10000, enableHighAccuracy: true }
                    );
                },

                updateLocation(lat, lng) {
                    this.bookingForm.latitude = parseFloat(Number(lat).toFixed(6));
                    this.bookingForm.longitude = parseFloat(Number(lng).toFixed(6));

                    // Titik koordinat acuan pos perawat/nakes yang dipilih
                    let baseLat = null;
                    let baseLng = null;

                    if (this.selectedNurse && this.selectedNurse.latitude && this.selectedNurse.longitude) {
                        baseLat = parseFloat(this.selectedNurse.latitude);
                        baseLng = parseFloat(this.selectedNurse.longitude);
                    } else if (this.nearbyNurses && this.nearbyNurses.length > 0) {
                        const matchedNurse = this.nearbyNurses.find(n => 
                            !this.activeService || (n.services && n.services.some(s => s.id === this.activeService.id))
                        ) || this.nearbyNurses[0];

                        if (matchedNurse && matchedNurse.latitude && matchedNurse.longitude) {
                            baseLat = parseFloat(matchedNurse.latitude);
                            baseLng = parseFloat(matchedNurse.longitude);
                        }
                    }

                    let dist = 0.5;
                    if (baseLat !== null && baseLng !== null) {
                        dist = this.haversineDistance(baseLat, baseLng, this.bookingForm.latitude, this.bookingForm.longitude);
                    } else {
                        // Jika belum ada perawat spesifik, beri jarak default aman 0.5 km
                        dist = 0.5;
                    }

                    // Format jarak 1 desimal. Jika kurang dari 0.1 km (sama persis), tetapkan 0 km
                    const rounded = Math.round(dist * 10) / 10;
                    this.bookingForm.distance_km = rounded < 0.1 ? 0 : rounded;

                    // Hilangkan pesan error radius jika sekarang sudah berada dalam radius aman
                    if (this.bookingForm.distance_km <= 10.0 && this.errorMessage.includes('radius')) {
                        this.errorMessage = '';
                    }

                    this.fetchPriceBreakdown();
                },

                haversineDistance(lat1, lon1, lat2, lon2) {
                    const R = 6371; // km
                    const dLat = (lat2 - lat1) * Math.PI / 180;
                    const dLon = (lon2 - lon1) * Math.PI / 180;
                    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                              Math.sin(dLon / 2) * Math.sin(dLon / 2);
                    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                    return R * c;
                },

                fetchPriceBreakdown() {
                    if (!this.activeService) return;

                    const servicePrice = parseFloat(this.activeService.price);
                    const distance = typeof this.bookingForm.distance_km === 'number' ? this.bookingForm.distance_km : 0;

                    // Hitung lokal instan: 3 km pertama Rp 10.000, berikutnya +Rp 2.500/km
                    let transport = 10000;
                    if (distance > 3.0) {
                        transport = 10000 + (Math.ceil(distance - 3.0) * 2500);
                    }
                    const platform = 5000;
                    const total = servicePrice + transport + platform;

                    this.breakdown = {
                        base_price: servicePrice,
                        distance_km: distance,
                        transport_fee: transport,
                        platform_fee: platform,
                        total_amount: total,
                        formatted: {
                            base_price: 'Rp ' + Number(servicePrice).toLocaleString('id-ID'),
                            transport_fee: 'Rp ' + Number(transport).toLocaleString('id-ID'),
                            platform_fee: 'Rp ' + Number(platform).toLocaleString('id-ID'),
                            total_amount: 'Rp ' + Number(total).toLocaleString('id-ID'),
                        }
                    };

                    // Sinkronisasi via API kalkulasi harga resmi
                    fetch('{{ route("api.orders.calculate-price") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({
                            service_catalog_id: this.activeService.id,
                            distance_km: distance,
                        })
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === 'success' && res.data) {
                            this.breakdown = res.data;
                        }
                    })
                    .catch(() => {
                        // Fallback ke hitungan lokal jika offline
                },

                handlePhotoUpload(event) {
                    const file = event.target.files[0];
                    if (file) {
                        if (file.size > 5 * 1024 * 1024) {
                            alert('Ukuran berkas melebihi 5 MB. Silakan pilih foto lain.');
                            event.target.value = '';
                            return;
                        }
                        this.uploadedPhotoFile = file;
                    }
                },

                submitOrder() {
                    if (this.submittingOrder) return;
                    this.submittingOrder = true;
                    this.errorMessage = '';

                    const formData = new FormData();
                    formData.append('service_catalog_id', this.activeService.id);
                    formData.append('scheduled_date', this.bookingForm.scheduled_date);
                    formData.append('scheduled_time', this.bookingForm.scheduled_time);
                    formData.append('patient_name', this.bookingForm.patient_name);
                    formData.append('patient_phone', this.bookingForm.patient_phone);
                    formData.append('address', this.bookingForm.address);
                    formData.append('latitude', this.bookingForm.latitude);
                    formData.append('longitude', this.bookingForm.longitude);
                    formData.append('distance_km', this.bookingForm.distance_km);

                    if (this.bookingForm.nurse_id) {
                        formData.append('nurse_id', this.bookingForm.nurse_id);
                    }
                    if (this.bookingForm.clinical_notes) {
                        formData.append('clinical_notes', this.bookingForm.clinical_notes);
                    }
                    if (this.uploadedPhotoFile) {
                        formData.append('medical_photo', this.uploadedPhotoFile);
                    }

                    fetch('{{ route("api.orders.store") }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: formData,
                    })
                    .then(async (response) => {
                        const data = await response.json();
                        if (!response.ok) {
                            throw new Error(data.message || 'Terjadi kesalahan saat memproses pesanan.');
                        }
                        return data;
                    })
                    .then((data) => {
                        this.submittingOrder = false;
                        this.orderSuccess = true;
                        this.createdOrderData = data.data;
                    })
                    .catch((err) => {
                        this.submittingOrder = false;
                        this.errorMessage = err.message || 'Gagal mengirim pesanan. Silakan coba kembali.';
                    });
                },

                formatDisplayDate(dateStr) {
                    if (!dateStr) return '-';
                    const parts = dateStr.split('-');
                    if (parts.length === 3) {
                        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                        return parts[2] + ' ' + months[parseInt(parts[1], 10) - 1] + ' ' + parts[0];
                    }
                    return dateStr;
                },

                // ===== COMPARE SERVICES FEATURE =====
                toggleCompare(service) {
                    const idx = this.compareList.findIndex(s => s.id === service.id);
                    if (idx !== -1) {
                        this.compareList.splice(idx, 1);
                    } else {
                        if (this.compareList.length >= 3) {
                            alert('Maksimal 3 layanan dapat dibandingkan secara bersamaan. Hapus salah satu layanan terlebih dahulu.');
                            return;
                        }
                        this.compareList.push(service);
                    }
                },

                isInCompare(serviceId) {
                    return this.compareList.some(s => s.id === serviceId);
                },

                removeFromCompare(serviceId) {
                    this.compareList = this.compareList.filter(s => s.id !== serviceId);
                },

                clearCompare() {
                    this.compareList = [];
                },

                openCompareModal() {
                    if (this.compareList.length < 2) {
                        alert('Pilih minimal 2 layanan untuk membandingkan.');
                        return;
                    }
                    this.compareModalOpen = true;
                },

                getLowestComparePrice() {
                    if (this.compareList.length === 0) return 0;
                    return Math.min(...this.compareList.map(s => parseFloat(s.price)));
                },

                getShortestCompareDuration() {
                    if (this.compareList.length === 0) return 0;
                    return Math.min(...this.compareList.map(s => s.estimated_duration_minutes || 60));
                },

                bookCheapestFromCompare() {
                    if (this.compareList.length === 0) return;
                    const cheapest = this.compareList.reduce((min, s) =>
                        parseFloat(s.price) < parseFloat(min.price) ? s : min
                    , this.compareList[0]);
                    this.compareModalOpen = false;
                    this.openBookingModal(cheapest);
                }
            };
        }
    </script>
</body>
</html>

