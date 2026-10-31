<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $title ?? 'Admin Panel') - CareAtHome</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col">

    <!-- Top Announcement Bar -->
    <div class="bg-gradient-to-r from-emerald-900 via-emerald-800 to-teal-900 text-white text-xs py-2 px-4 text-center font-medium border-b border-emerald-700/50">
        <span class="inline-flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            PORTAL ADMINISTRATOR UTAMA &bull; Sistem Informasi &amp; Manajemen Layanan Medis CareAtHome
        </span>
    </div>

    <!-- Top Navigation Header -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-sm" x-data="{ mobileMenu: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="h-16 flex items-center justify-between gap-4">
                
                <!-- Left: Brand Logo & Portal Tag -->
                <div class="flex items-center gap-3 shrink-0">
                    <a href="{{ route('admin.nurses.index') }}" class="flex items-center gap-2.5 group">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center font-black text-sm shadow-md group-hover:scale-105 transition-transform">
                            CA
                        </div>
                        <div class="flex flex-col">
                            <span class="text-base font-extrabold text-slate-900 tracking-tight leading-none">
                                Care<span class="text-emerald-600">AtHome</span>
                            </span>
                            <span class="text-[10px] text-slate-400 font-semibold tracking-wide">HEALTH SYSTEM</span>
                        </div>
                    </a>
                    <span class="hidden sm:inline-block text-slate-200">|</span>
                    <span class="hidden sm:inline-block text-[11px] font-bold uppercase tracking-wider text-emerald-800 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200/80">
                        Admin Portal
                    </span>
                </div>

                <!-- Center: Navigation Menus (Desktop) -->
                <nav class="hidden md:flex items-center gap-1 text-xs font-bold">
                    <a href="{{ route('admin.nurses.index') }}"
                        class="px-3.5 py-2 rounded-xl transition flex items-center gap-2 {{ request()->routeIs('admin.nurses*') ? 'bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-xs' : 'text-slate-600 hover:text-emerald-700 hover:bg-slate-100' }}">
                        <span class="text-sm">🩺</span>
                        <span>Verifikasi Nakes</span>
                    </a>

                    <a href="{{ route('admin.services.index') }}"
                        class="px-3.5 py-2 rounded-xl transition flex items-center gap-2 {{ request()->routeIs('admin.services*') ? 'bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-xs' : 'text-slate-600 hover:text-emerald-700 hover:bg-slate-100' }}">
                        <span class="text-sm">📋</span>
                        <span>Katalog &amp; Tarif</span>
                    </a>

                    <a href="{{ route('admin.users.index') }}"
                        class="px-3.5 py-2 rounded-xl transition flex items-center gap-2 {{ request()->routeIs('admin.users*') ? 'bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-xs' : 'text-slate-600 hover:text-emerald-700 hover:bg-slate-100' }}">
                        <span class="text-sm">👥</span>
                        <span>Pengelolaan Akun</span>
                    </a>
                </nav>

                <!-- Right: Profile & Logout -->
                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex items-center gap-2.5 pl-3 border-l border-slate-200">
                        <div class="w-8 h-8 rounded-full bg-emerald-600 text-white text-xs font-bold flex items-center justify-center shadow-xs">
                            {{ strtoupper(substr(auth()->user()->name ?? 'AD', 0, 2)) }}
                        </div>
                        <div class="text-left text-xs leading-tight">
                            <span class="font-bold text-slate-800 block truncate max-w-[130px]">{{ auth()->user()->name ?? 'Administrator' }}</span>
                            <span class="text-[10px] text-emerald-600 font-semibold">Admin Master</span>
                        </div>
                    </div>

                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" title="Keluar dari sesi Admin"
                            class="text-xs font-bold text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-xl border border-rose-200 transition flex items-center gap-1.5 shadow-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span>Keluar</span>
                        </button>
                    </form>

                    <!-- Mobile Menu Hamburger -->
                    <button type="button" @click="mobileMenu = !mobileMenu" class="md:hidden p-2 text-slate-500 hover:text-slate-800 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>

            </div>

            <!-- Mobile Navigation Drawer -->
            <div x-show="mobileMenu" x-cloak class="md:hidden py-3 border-t border-slate-100 space-y-1">
                <a href="{{ route('admin.nurses.index') }}"
                    class="block px-3 py-2 rounded-xl text-xs font-bold {{ request()->routeIs('admin.nurses*') ? 'bg-emerald-50 text-emerald-800' : 'text-slate-600 hover:bg-slate-100' }}">
                    🩺 Verifikasi Nakes
                </a>
                <a href="{{ route('admin.services.index') }}"
                    class="block px-3 py-2 rounded-xl text-xs font-bold {{ request()->routeIs('admin.services*') ? 'bg-emerald-50 text-emerald-800' : 'text-slate-600 hover:bg-slate-100' }}">
                    📋 Katalog &amp; Tarif
                </a>
                <a href="{{ route('admin.users.index') }}"
                    class="block px-3 py-2 rounded-xl text-xs font-bold {{ request()->routeIs('admin.users*') ? 'bg-emerald-50 text-emerald-800' : 'text-slate-600 hover:bg-slate-100' }}">
                    👥 Pengelolaan Akun
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        <!-- Flash Alert Success -->
        @if (session('status') || session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs font-bold text-emerald-800 flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <span class="w-5 h-5 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[10px]">✓</span>
                    <span>{{ session('status') ?? session('success') }}</span>
                </div>
            </div>
        @endif

        <!-- Flash Alert Error -->
        @if (session('error'))
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-xs font-bold text-rose-800 flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <span class="w-5 h-5 rounded-full bg-rose-600 text-white flex items-center justify-center text-[10px]">!</span>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <!-- Slot / Yield Content -->
        @if (isset($slot))
            {{ $slot }}
        @else
            @yield('content')
        @endif
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 mt-auto text-center text-xs text-slate-500">
        &copy; 2026 CareAtHome Medical. Sistem Informasi Layanan Homecare Terintegrasi.
    </footer>

</body>
</html>
