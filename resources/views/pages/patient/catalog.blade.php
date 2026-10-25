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

    <style>
        [x-cloak] { display: none !important; }
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
                <div class="bg-white rounded-2xl border border-slate-200 hover:border-emerald-400 hover:shadow-lg transition-all duration-300 flex flex-col justify-between overflow-hidden group">
                    
                    <!-- Card Top Content -->
                    <div class="p-6 space-y-4">
                        
                        <!-- Header: Emoji Icon, Category Badge & Duration -->
                        <div class="flex items-start justify-between gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                                <span x-text="item.image_emoji"></span>
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

    <!-- 8. MODAL: BOOKING PREVIEW MODAL -->
    <div x-show="bookingModalOpen" x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div @click.away="bookingModalOpen = false"
            class="bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-slate-200 overflow-hidden text-slate-800"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100">

            <div class="p-6 bg-emerald-600 text-white flex items-center justify-between">
                <div>
                    <span class="text-emerald-100 text-xs font-semibold uppercase tracking-wider">Konfirmasi Pemilihan Layanan</span>
                    <h3 class="text-xl font-bold mt-0.5">Siap Melakukan Pemesanan</h3>
                </div>
                <button type="button" @click="bookingModalOpen = false" class="text-emerald-200 hover:text-white p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-6 space-y-4">
                <div class="flex items-center gap-3.5 p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                    <div class="w-12 h-12 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-2xl" x-text="activeService?.image_emoji"></div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-900" x-text="activeService?.name"></h4>
                        <p class="text-xs text-slate-500" x-text="activeService?.category"></p>
                    </div>
                </div>

                <!-- Price Breakdown Estimate -->
                <div class="space-y-2 text-xs">
                    <p class="font-bold text-slate-400 uppercase tracking-wider text-[10px]">Estimasi Rincian Biaya (Price Breakdown):</p>
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                        <div class="flex justify-between text-slate-600">
                            <span>Tarif Tindakan Medis:</span>
                            <span class="font-semibold text-slate-900" x-text="activeService?.formatted_price"></span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Biaya Layanan Platform:</span>
                            <span class="font-semibold text-slate-900">Rp 5.000</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Ongkos Transport GPS:</span>
                            <span class="text-emerald-600 font-semibold italic">Dihitung pada tahap penentuan alamat</span>
                        </div>
                    </div>
                </div>

                <div class="p-3.5 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-800 flex items-start gap-2">
                    <span class="text-base">ℹ️</span>
                    <p>
                        Tahap selanjutnya adalah <strong>Penentuan Tanggal &amp; Waktu Kunjungan serta Titik Alamat GPS</strong>.
                    </p>
                </div>
            </div>

            <div class="p-4 sm:p-6 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-3">
                <button type="button" @click="bookingModalOpen = false" class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-xs font-bold text-slate-700 transition">
                    Kembali ke Katalog
                </button>
                <button type="button" @click="proceedToBooking()" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md transition flex items-center gap-1.5">
                    <span>Lanjut ke Penjadwalan &amp; Alamat</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </div>

        </div>
    </div>

    <!-- 9. FOOTER -->
    <footer class="mt-auto border-t border-slate-200 bg-white py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-xs text-slate-500">
            CareAtHome Health-Tech Platform &copy; 2026. Platform Layanan Keperawatan On-Demand Terstandar Kemenkes RI.
        </div>
    </footer>

    <!-- Alpine App Logic for Instant Filtering and Search -->
    <script>
        function patientCatalogApp() {
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
                activeService: null,

                initData() {
                    this.filterServices();
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

                    // 1. Filter Kategori
                    if (this.selectedCategory && this.selectedCategory !== 'Semua') {
                        items = items.filter(s => s.category === this.selectedCategory);
                    }

                    // 2. Filter Search Keyword
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

                    // 3. Filter Rentang Tarif
                    if (this.priceBracket === 'under_150k') {
                        items = items.filter(s => parseFloat(s.price) < 150000);
                    } else if (this.priceBracket === '150k_200k') {
                        items = items.filter(s => parseFloat(s.price) >= 150000 && parseFloat(s.price) <= 200000);
                    } else if (this.priceBracket === 'above_200k') {
                        items = items.filter(s => parseFloat(s.price) > 200000);
                    }

                    // 4. Pengurutan (Sorting)
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
                    this.bookingModalOpen = true;
                },

                proceedToBooking() {
                    alert('Layanan "' + this.activeService.name + '" dipilih! Menuju modul form pemesanan dan jadwal.');
                    this.bookingModalOpen = false;
                }
            };
        }
    </script>
</body>
</html>
