<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'CareAtHome') }} - Platform Layanan On-Demand Perawat Medis</title>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-[#F9F4EA] min-h-screen text-[#46571A] flex flex-col justify-between selection:bg-[#FF9ABC] selection:text-[#46571A] relative overflow-x-hidden">
        <!-- Top Mini Navigation -->
        <header class="sticky top-0 z-40 w-full h-16 px-6 sm:px-10 flex items-center justify-between border-b border-[#78956A]/25 backdrop-blur-md bg-[#FFFDF9]/95 shadow-sm">
            <a href="/" class="group flex items-center gap-3 transition-transform hover:scale-[1.01]" wire:navigate>
                <x-application-logo :dark="true" />
            </a>

            <div class="flex items-center gap-4 text-sm font-medium">
                <a href="/" class="text-[#46571A] hover:bg-[#D1EB8F]/60 px-3 py-1.5 rounded-xl flex items-center gap-1.5 transition" wire:navigate>
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
        <footer class="w-full py-4 text-center text-xs text-[#78956A] border-t border-[#78956A]/20 bg-[#FFFDF9]/70 z-10">
            CareAtHome &bull; Platform Medis Berlisensi Terdaftar Kemenkes
        </footer>
    </body>
</html>
