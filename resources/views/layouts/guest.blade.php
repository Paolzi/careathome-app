<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'CareAtHome') }} - Platform Layanan On-Demand Perawat Medis</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gradient-to-br from-[#064e3b] via-[#043328] to-[#0b1329] min-h-screen text-slate-100 flex flex-col justify-between selection:bg-emerald-500 selection:text-white relative overflow-x-hidden">
        
        <!-- Ambient Background Glows -->
        <div class="fixed top-0 left-1/4 w-96 h-96 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="fixed bottom-0 right-1/4 w-96 h-96 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Top Mini Navigation -->
        <header class="w-full py-4 px-6 sm:px-10 flex items-center justify-between z-10 border-b border-emerald-800/30 backdrop-blur-md bg-emerald-950/40">
            <a href="/" class="group flex items-center gap-3 transition-transform hover:scale-105" wire:navigate>
                <x-application-logo />
            </a>

            <div class="flex items-center gap-4 text-sm font-medium">
                <a href="/" class="text-emerald-200/80 hover:text-white flex items-center gap-1 transition" wire:navigate>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>Kembali ke Beranda</span>
                </a>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col justify-center items-center py-10 px-4 sm:px-6 lg:px-8 z-10">
            {{ $slot }}
        </main>

        <!-- Footer Note -->
        <footer class="w-full py-4 text-center text-xs text-emerald-300/60 border-t border-emerald-900/30 z-10">
            CareAtHome &bull; Platform Medis Berlisensi Terdaftar Kemenkes
        </footer>
    </body>
</html>
