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

    <!-- Chart.js CDN for Financial Dashboard -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased min-h-screen flex flex-col lg:flex-row">

    <!-- 1. SIDEBAR NAVIGASI ADMIN -->
    <aside class="w-full lg:w-64 bg-slate-900 text-white shrink-0 flex flex-col justify-between border-r border-slate-800">
        <div>
            <!-- Admin Brand Logo -->
            <div class="h-16 flex items-center gap-3 px-6 border-b border-slate-800/80 bg-slate-950/40">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-emerald-500 flex items-center justify-center text-white shadow-md font-black text-sm">
                    CA
                </div>
                <div>
                    <span class="text-sm font-extrabold tracking-tight block">
                        Care<span class="text-emerald-400">AtHome</span>
                    </span>
                    <span class="text-[9px] uppercase tracking-widest font-extrabold text-indigo-400 bg-indigo-950/60 px-1.5 py-0.2 rounded border border-indigo-700/50">
                        Admin Master
                    </span>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="p-4 space-y-1 text-xs font-semibold">
                <!-- SCRUM-56 -->
                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <span class="text-base">📊</span>
                    <span>Financial &amp; GMV</span>
                </a>

                <!-- SCRUM-55 -->
                <a href="{{ route('admin.nurses.index') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.nurses*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <span class="text-base">🩺</span>
                    <span>Audit Legalitas Nakes</span>
                </a>

                <!-- SCRUM-57 -->
                <a href="{{ route('admin.services.index') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.services*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <span class="text-base">📋</span>
                    <span>Katalog &amp; Tarif</span>
                </a>

                <!-- SCRUM-58 -->
                <a href="{{ route('admin.escrow.index') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.escrow*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <span class="text-base">🏦</span>
                    <span>Monitoring Escrow</span>
                </a>

                <!-- SCRUM-59 -->
                <a href="{{ route('admin.payouts.index') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.payouts*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <span class="text-base">💸</span>
                    <span>Otorisasi Payout</span>
                </a>

                <!-- SCRUM-60 -->
                <a href="{{ route('admin.users.index') }}"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.users*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <span class="text-base">👥</span>
                    <span>Manajemen Pengguna</span>
                </a>

                <div class="pt-4 border-t border-slate-800/80 my-2"></div>

                <a href="{{ route('dashboard') }}"
                    class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/60 transition">
                    <span class="text-base">🏠</span>
                    <span>Portal Pengguna</span>
                </a>
            </nav>
        </div>

        <!-- Admin Profile Bottom -->
        <div class="p-4 border-t border-slate-800 bg-slate-950/30">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-indigo-600 text-white text-xs font-bold flex items-center justify-center">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="overflow-hidden">
                        <span class="text-xs font-bold text-white block truncate">{{ auth()->user()->name }}</span>
                        <span class="text-[10px] text-slate-400 block truncate">Administrator</span>
                    </div>
                </div>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" title="Keluar" class="text-slate-400 hover:text-rose-400 p-1.5 rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- 2. MAIN ADMIN CONTENT -->
    <div class="flex-1 flex flex-col min-h-screen overflow-x-hidden">
        
        <!-- Header Bar -->
        <header class="h-16 bg-white border-b border-slate-200 px-6 sm:px-8 flex items-center justify-between sticky top-0 z-30">
            <h1 class="text-base sm:text-lg font-bold text-slate-800">
                {{ $header ?? 'Admin Panel' }}
            </h1>
            <div class="flex items-center gap-3">
                <span class="text-xs text-slate-500 font-medium">CareAtHome Health System &copy; 2026</span>
            </div>
        </header>

        <!-- Main Body -->
        <main class="p-6 sm:p-8 flex-1 space-y-6">

            <!-- Flash Alert Success -->
            @if (session('status') || session('success'))
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-800 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2">
                        <span>✓</span>
                        <span>{{ session('status') ?? session('success') }}</span>
                    </div>
                </div>
            @endif

            <!-- Flash Alert Error -->
            @if (session('error'))
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-xs font-semibold text-rose-800 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2">
                        <span>⚠️</span>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <!-- Main View Content -->
            @if (isset($slot))
                {{ $slot }}
            @else
                @yield('content')
            @endif
        </main>
    </div>

</body>
</html>
