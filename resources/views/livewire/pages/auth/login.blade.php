<?php

use App\Livewire\Forms\LoginForm;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle standard login
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $user = Auth::user();

        // Redirect sesuai role
        if ($user && $user->hasRole('perawat')) {
            $this->redirect(route('perawat.verifikasi'), navigate: true);
            return;
        }

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    /**
     * Quick 1-Click Simulation Login for Testing
     */
    public function quickLogin(string $role): void
    {
        if ($role === 'admin') {
            $this->addError('form.email', 'Akun Administrator tidak tersedia untuk simulasi 1-klik demi keamanan data.');
            return;
        }

        $email = match ($role) {
            'perawat' => 'perawat@careathome.com',
            'pasien' => 'pasien@careathome.com',
            default => 'pasien@careathome.com',
        };

        $user = User::where('email', $email)->first();

        if ($user) {
            Auth::login($user);
            Session::regenerate();

            if ($role === 'perawat') {
                $this->redirect(route('perawat.verifikasi'), navigate: true);
                return;
            }

            $this->redirect(route('dashboard'), navigate: true);
        } else {
            $this->addError('form.email', 'Akun seeder belum tersedia. Jalankan php artisan db:seed terlebih dahulu.');
        }
    }
}; ?>

<div class="w-full max-w-md bg-white/95 backdrop-blur-xl shadow-2xl rounded-2xl border border-emerald-500/20 p-6 sm:p-8 text-slate-800">
    
    <!-- Title & Subtitle -->
    <div class="text-center mb-6">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold mb-2">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            Autentikasi Aman &bull; Sesi Terenkripsi
        </div>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">
            Masuk ke CareAtHome
        </h1>
        <p class="text-xs text-slate-600 mt-1">
            Gunakan email terdaftar atau coba simulasi akun instan di bawah
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4 text-xs font-semibold text-emerald-700 bg-emerald-50 p-3 rounded-lg border border-emerald-200" :status="session('status')" />

    @if (app()->environment('local'))
    <!-- Quick Simulation Buttons (One-Click Login for Evaluators/Testing - Pasien & Perawat Saja) -->
    <div class="mb-6 p-3.5 rounded-xl bg-slate-50 border border-slate-200">
        <div class="flex items-center justify-between mb-2.5">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"></path>
                </svg>
                Simulasi Akun 1-Klik (Demo Testing)
            </span>
            <span class="text-[10px] bg-emerald-100 text-emerald-800 font-semibold px-2 py-0.5 rounded-full">Dev Local</span>
        </div>
        <div class="grid grid-cols-2 gap-2.5">
            <button 
                type="button" 
                wire:click="quickLogin('pasien')"
                class="py-2.5 px-3 rounded-xl bg-white border-2 border-emerald-200 hover:border-emerald-500 hover:bg-emerald-50/50 text-xs font-bold text-slate-800 hover:text-emerald-900 transition shadow-sm text-center flex flex-col items-center gap-1 group">
                <span class="text-lg group-hover:scale-110 transition-transform">👤</span>
                <span>Masuk Pasien</span>
            </button>
            <button 
                type="button" 
                wire:click="quickLogin('perawat')"
                class="py-2.5 px-3 rounded-xl bg-white border-2 border-teal-200 hover:border-teal-500 hover:bg-teal-50/50 text-xs font-bold text-slate-800 hover:text-teal-900 transition shadow-sm text-center flex flex-col items-center gap-1 group">
                <span class="text-lg group-hover:scale-110 transition-transform">🩺</span>
                <span>Masuk Perawat</span>
            </button>
        </div>
        <div class="mt-2.5 p-2 rounded-lg bg-emerald-50/60 border border-emerald-100 text-[10px] text-slate-600 leading-tight flex items-start gap-1.5">
            <span class="text-emerald-700">🔒</span>
            <span><strong>Catatan:</strong> Akun Admin wajib login kredensial manual demi integritas data rekam medis & keuangan.</span>
        </div>
    </div>

    <!-- Divider -->
    <div class="relative flex items-center justify-center my-4">
        <div class="border-t border-slate-200 w-full"></div>
        <span class="bg-white px-3 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Atau Masuk Manual</span>
    </div>
    @endif

    <!-- Standard Login Form -->
    <form wire:submit="login" class="space-y-4">
        
        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                Alamat Email <span class="text-rose-500">*</span>
            </label>
            <input 
                wire:model="form.email" 
                id="email" 
                type="email" 
                placeholder="nama@email.com" 
                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm transition"
                required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-1 text-xs text-rose-500" />
        </div>

        <!-- Password -->
        <div>
            <div class="flex items-center justify-between mb-1">
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                    Kata Sandi <span class="text-rose-500">*</span>
                </label>
                @if (Route::has('password.request'))
                    <a class="text-xs text-emerald-700 hover:text-emerald-800 font-medium underline" href="{{ route('password.request') }}" wire:navigate>
                        Lupa sandi?
                    </a>
                @endif
            </div>

            <input 
                wire:model="form.password" 
                id="password" 
                type="password" 
                placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" 
                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm transition"
                required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('form.password')" class="mt-1 text-xs text-rose-500" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between text-xs pt-1">
            <label for="remember" class="inline-flex items-center gap-2 cursor-pointer select-none text-slate-600">
                <input wire:model="form.remember" id="remember" type="checkbox" class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                <span>Ingat Sesi Masuk</span>
            </label>
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button 
                type="submit" 
                class="w-full py-3 px-6 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-700 hover:to-emerald-600 text-white font-extrabold text-sm tracking-wide shadow-md shadow-emerald-600/30 hover:shadow-emerald-600/40 transition flex items-center justify-center gap-2">
                <span>Masuk Sekarang</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                </svg>
            </button>
        </div>

        <!-- Link to Register -->
        <div class="text-center pt-4 border-t border-slate-200 text-xs text-slate-600">
            Belum memiliki akun? 
            <a href="{{ route('register') }}" class="font-bold text-emerald-700 hover:text-emerald-800 ml-1 underline decoration-2 underline-offset-2" wire:navigate>
                Daftar Pasien / Perawat
            </a>
        </div>
    </form>
</div>
