<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head> <meta charset="utf-8"> <meta name="viewport" content="width=device-width, initial-scale=1"> <title>@yield('title', $title ?? 'Admin Panel') - CareAtHome</title> <meta name="csrf-token" content="{{ csrf_token() }}"> <!-- Scripts & Styles --> @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js & Chart.js --> <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script> <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script> <style> [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-[#F9F4EA] text-[#46571A] antialiased min-h-screen flex flex-col"> <!-- Unified Top Navigation Header --> <x-careathome-navbar /> <!-- Main Content Container --> <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6"> <!-- Flash Alert Success --> @if (session('status') || session('success'))
            <div class="p-4 rounded-2xl bg-[#D1EB8F]/45 border border-[#78956A]/50 text-xs font-bold text-[#46571A] flex items-center justify-between shadow-xs"> <div class="flex items-center gap-2"> <span class="w-5 h-5 rounded-full bg-[#FF9ABC] text-[#46571A] flex items-center justify-center text-[10px]"><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg></span> <span>{{ session('status') ?? session('success') }}</span> </div> </div> @endif

        <!-- Flash Alert Error --> @if (session('error'))
            <div class="p-4 rounded-2xl bg-[#FF9ABC]/25 border border-[#FF9ABC] text-xs font-bold text-[#46571A] flex items-center justify-between shadow-xs"> <div class="flex items-center gap-2"> <span class="w-5 h-5 rounded-full bg-[#FF9ABC] text-[#46571A] flex items-center justify-center text-[10px]">!</span> <span>{{ session('error') }}</span> </div> </div> @endif

        <!-- Slot / Yield Content --> @if (isset($slot))
            {{ $slot }}
        @else
            @yield('content')
        @endif
    </main> <!-- Footer --> <footer class="bg-white border-t border-[#78956A]/50 py-6 mt-auto text-center text-xs text-[#78956A]"> &copy; 2026 CareAtHome Medical. Sistem Informasi Layanan Homecare Terintegrasi.
    </footer> </body>
</html>
