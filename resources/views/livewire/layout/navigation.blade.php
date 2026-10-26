<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<nav x-data="{ open: false }" class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center gap-3">
                <!-- Brand Logo & Portal Tag -->
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 flex items-center justify-center text-white shadow-md shadow-emerald-500/20 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-lg font-extrabold text-slate-900 tracking-tight leading-none block">
                            Care<span class="text-emerald-600">AtHome</span>
                        </span>
                        <span class="text-[10px] uppercase tracking-wider font-semibold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200/60 mt-0.5 inline-block">
                            {{ auth()->user()?->hasRole('perawat') ? 'Portal Perawat' : 'Portal Pasien' }}
                        </span>
                    </div>
                </a>

                <!-- Nav separator -->
                <div class="hidden sm:block h-6 w-px bg-slate-200 ml-2"></div>

                <!-- Navigation Links (Pill Style) -->
                <div class="hidden sm:flex items-center space-x-1 ml-2">
                    <a href="{{ route('dashboard') }}" 
                        class="px-3 py-1.5 rounded-lg text-xs transition {{ request()->routeIs('dashboard') ? 'font-bold text-emerald-700 bg-emerald-50 border border-emerald-200' : 'font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        {{ __('Dashboard') }}
                    </a>

                    @if (auth()->user()?->hasRole('perawat'))
                        <a href="{{ route('perawat.verifikasi') }}" 
                            class="px-3 py-1.5 rounded-lg text-xs transition {{ request()->routeIs('perawat.verifikasi*') ? 'font-bold text-emerald-700 bg-emerald-50 border border-emerald-200' : 'font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            {{ __('Verifikasi Legalitas Nakes') }}
                        </a>
                    @else
                        <a href="{{ route('pasien.katalog') }}" 
                            class="px-3 py-1.5 rounded-lg text-xs transition {{ request()->routeIs('pasien.katalog*') ? 'font-bold text-emerald-700 bg-emerald-50 border border-emerald-200' : 'font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            {{ __('Katalog Layanan') }}
                        </a>

                        <a href="{{ route('pasien.pesanan') }}" 
                            class="px-3 py-1.5 rounded-lg text-xs transition {{ request()->routeIs('pasien.pesanan') ? 'font-bold text-emerald-700 bg-emerald-50 border border-emerald-200' : 'font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            {{ __('Jadwal & Detail Booking') }}
                        </a>

                        <a href="{{ route('pasien.pembayaran') }}" 
                            class="px-3 py-1.5 rounded-lg text-xs transition flex items-center gap-1.5 {{ request()->routeIs('pasien.pembayaran') ? 'font-bold text-emerald-700 bg-emerald-50 border border-emerald-200' : 'font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            <span>{{ __('Pembayaran Escrow') }}</span>
                            <span class="text-[9px] font-extrabold px-1.5 py-0.2 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300">Escrow</span>
                        </a>

                        <a href="{{ route('pasien.pembatalan') }}" 
                            class="px-3 py-1.5 rounded-lg text-xs transition {{ request()->routeIs('pasien.pembatalan') ? 'font-bold text-rose-700 bg-rose-50 border border-rose-200' : 'font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            {{ __('Pembatalan & Refund') }}
                        </a>
                    @endif
                </div>
            </div>

            <!-- Settings & Profile Right Section -->
            <div class="flex items-center gap-3">
                <div class="hidden md:flex items-center gap-2 text-xs text-slate-500 bg-slate-100 px-3 py-1.5 rounded-xl border border-slate-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>{{ auth()->user()?->hasRole('perawat') ? 'Mitra Perawat' : 'Pasien Terdaftar' }}: <strong>{{ auth()->user()->name }}</strong></span>
                </div>

                <!-- Dropdown -->
                <div class="relative" x-data="{ dropdownOpen: false }">
                    <button @click="dropdownOpen = !dropdownOpen" type="button" class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-slate-100 transition border border-transparent hover:border-slate-200">
                        <div class="w-8 h-8 rounded-full bg-emerald-600 text-white font-bold text-xs flex items-center justify-center">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="dropdownOpen" @click.away="dropdownOpen = false" x-cloak
                        class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-200 py-1.5 text-xs text-slate-700 z-50">
                        <div class="px-3 py-2 border-b border-slate-100">
                            <p class="font-bold text-slate-900 truncate">{{ auth()->user()->name }}</p>
                            <p class="text-[11px] text-slate-500 truncate">{{ auth()->user()->email }}</p>
                        </div>

                        <a href="{{ route('profile') }}" class="block px-3 py-2 hover:bg-slate-50 text-slate-700 font-medium">
                            {{ __('Pengaturan Profil') }}
                        </a>

                        <button wire:click="logout" class="w-full text-start block px-3 py-2 hover:bg-rose-50 text-rose-600 font-medium">
                            {{ __('Keluar Akun') }}
                        </button>
                    </div>
                </div>

                <!-- Hamburger -->
                <div class="-me-2 flex items-center sm:hidden">
                    <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-xl text-slate-500 hover:text-slate-800 hover:bg-slate-100 focus:outline-none transition">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-slate-200 bg-white">
        <div class="pt-2 pb-3 space-y-1 px-4">
            <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-lg text-xs transition {{ request()->routeIs('dashboard') ? 'font-bold text-emerald-700 bg-emerald-50 border border-emerald-200' : 'font-semibold text-slate-600 hover:bg-slate-100' }}">
                {{ __('Dashboard') }}
            </a>

            @if (auth()->user()?->hasRole('perawat'))
                <a href="{{ route('perawat.verifikasi') }}" class="block px-3 py-2 rounded-lg text-xs transition {{ request()->routeIs('perawat.verifikasi*') ? 'font-bold text-emerald-700 bg-emerald-50 border border-emerald-200' : 'font-semibold text-slate-600 hover:bg-slate-100' }}">
                    {{ __('Verifikasi Legalitas Nakes') }}
                </a>
            @else
                <a href="{{ route('pasien.katalog') }}" class="block px-3 py-2 rounded-lg text-xs transition {{ request()->routeIs('pasien.katalog*') ? 'font-bold text-emerald-700 bg-emerald-50 border border-emerald-200' : 'font-semibold text-slate-600 hover:bg-slate-100' }}">
                    {{ __('Katalog Layanan') }}
                </a>

                <a href="{{ route('pasien.pesanan') }}" class="block px-3 py-2 rounded-lg text-xs transition {{ request()->routeIs('pasien.pesanan') ? 'font-bold text-emerald-700 bg-emerald-50 border border-emerald-200' : 'font-semibold text-slate-600 hover:bg-slate-100' }}">
                    {{ __('Jadwal & Detail Booking') }}
                </a>

                <a href="{{ route('pasien.pembayaran') }}" class="block px-3 py-2 rounded-lg text-xs transition {{ request()->routeIs('pasien.pembayaran') ? 'font-bold text-emerald-700 bg-emerald-50 border border-emerald-200' : 'font-semibold text-slate-600 hover:bg-slate-100' }}">
                    {{ __('Pembayaran Escrow') }}
                </a>

                <a href="{{ route('pasien.pembatalan') }}" class="block px-3 py-2 rounded-lg text-xs transition {{ request()->routeIs('pasien.pembatalan') ? 'font-bold text-rose-700 bg-rose-50 border border-rose-200' : 'font-semibold text-slate-600 hover:bg-slate-100' }}">
                    {{ __('Pembatalan & Refund') }}
                </a>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-3 border-t border-slate-200 px-4">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-600 text-white font-bold text-xs flex items-center justify-center">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div>
                    <div class="font-bold text-xs text-slate-800">{{ auth()->user()->name }}</div>
                    <div class="font-medium text-[11px] text-slate-500">{{ auth()->user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <a href="{{ route('profile') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-100">
                    {{ __('Pengaturan Profil') }}
                </a>

                <button wire:click="logout" class="w-full text-start block px-3 py-2 rounded-lg text-xs font-semibold text-rose-600 hover:bg-rose-50">
                    {{ __('Keluar Akun') }}
                </button>
            </div>
        </div>
    </div>
</nav>
