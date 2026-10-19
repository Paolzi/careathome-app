<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $role = 'pasien'; // 'pasien' | 'perawat'

    // Form inputs umum
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $phone = '';

    // Form inputs Pasien
    public string $birth_date = '';
    public string $address = '';

    // Form inputs Perawat
    public string $str_number = '';
    public string $specialization = 'Perawatan Luka Kronis & Diabetes';

    public function setRole(string $selectedRole): void
    {
        $this->role = $selectedRole;
        $this->resetErrorBag();
    }

    /**
     * Handle registration request.
     */
    public function register(): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'phone' => ['required', 'string', 'min:9', 'max:20'],
        ];

        if ($this->role === 'pasien') {
            $rules['birth_date'] = ['required', 'date'];
            $rules['address'] = ['required', 'string', 'max:500'];
        } else {
            $rules['str_number'] = ['required', 'string', 'min:8', 'max:30'];
            $rules['specialization'] = ['required', 'string'];
        }

        $this->validate($rules);

        // Buat User Baru
        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        // Tetapkan Spatie Role
        $user->assignRole($this->role);

        event(new Registered($user));
        Auth::login($user);

        // Jika perawat, arahkan ke tahap unggah berkas legalitas STR/SIP (SCRUM-37)
        if ($this->role === 'perawat') {
            session()->flash('status', 'Pendaftaran awal berhasil! Silakan lengkapi berkas legalitas STR & SIP Anda.');
            $this->redirect(route('perawat.verifikasi'), navigate: true);
        } else {
            session()->flash('status', 'Pendaftaran akun Pasien berhasil. Selamat datang di CareAtHome!');
            $this->redirect(route('dashboard'), navigate: true);
        }
    }
}; ?>

<div class="w-full max-w-2xl bg-white/95 backdrop-blur-xl shadow-2xl rounded-2xl border border-emerald-500/20 p-6 sm:p-10 text-slate-800">
    
    <!-- Title & Subtitle -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold mb-3">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            Registrasi Pengguna CareAtHome
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
            Buat Akun Layanan Kesehatan
        </h1>
        <p class="text-sm text-slate-600 mt-1.5">
            Pilih jenis akun yang sesuai untuk mendapatkan akses layanan terstandarisasi medis
        </p>
    </div>

    <!-- Role Switcher Tabs -->
    <div class="grid grid-cols-2 gap-3 p-1.5 bg-slate-100 rounded-xl mb-8 border border-slate-200/80">
        <button 
            type="button" 
            wire:click="setRole('pasien')"
            class="flex items-center justify-center gap-2.5 py-3 px-4 rounded-lg font-bold text-sm transition-all duration-200 {{ $role === 'pasien' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30 ring-1 ring-emerald-500' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/60' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            <span>Pasien / Keluarga</span>
        </button>

        <button 
            type="button" 
            wire:click="setRole('perawat')"
            class="flex items-center justify-center gap-2.5 py-3 px-4 rounded-lg font-bold text-sm transition-all duration-200 {{ $role === 'perawat' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30 ring-1 ring-emerald-500' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/60' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
            </svg>
            <span>Perawat (Mitra Medis)</span>
        </button>
    </div>

    <!-- Information Banner based on selected role -->
    @if ($role === 'pasien')
        <div class="mb-6 p-4 rounded-xl bg-emerald-50/80 border border-emerald-200 flex items-start gap-3 text-emerald-900 text-xs sm:text-sm">
            <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <p class="font-bold">Akun Pasien CareAtHome</p>
                <p class="text-emerald-700 mt-0.5">Dapat langsung memesan kunjungan perawat berlisensi, melacak status pesanan secara real-time, dan menerima rekam medis digital (E-Report).</p>
            </div>
        </div>
    @else
        <div class="mb-6 p-4 rounded-xl bg-amber-50/90 border border-amber-200 flex items-start gap-3 text-amber-900 text-xs sm:text-sm">
            <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <div>
                <p class="font-bold">Akun Mitra Perawat Profesional</p>
                <p class="text-amber-800 mt-0.5">Wajib memiliki Surat Tanda Registrasi (STR) aktif. Setelah pengisian data awal ini, Anda akan diarahkan untuk mengunggah dokumen legalitas (STR, SIP, KTP).</p>
            </div>
        </div>
    @endif

    <form wire:submit="register" class="space-y-5">
        
        <!-- Nama Lengkap -->
        <div>
            <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                {{ $role === 'perawat' ? 'Nama Lengkap Beserta Gelar Profesi' : 'Nama Lengkap Pasien' }} <span class="text-rose-500">*</span>
            </label>
            <input 
                wire:model="name" 
                id="name" 
                type="text" 
                placeholder="{{ $role === 'perawat' ? 'Contoh: Ns. Siti Rahmawati, S.Kep' : 'Contoh: Ahmad Fauzi' }}" 
                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm transition"
                required autofocus />
            @error('name') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Row: Email & No. WhatsApp -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                    Alamat Email Aktif <span class="text-rose-500">*</span>
                </label>
                <input 
                    wire:model="email" 
                    id="email" 
                    type="email" 
                    placeholder="nama@email.com" 
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm transition"
                    required />
                @error('email') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                    Nomor WhatsApp / HP <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-xs font-semibold text-slate-500 pointer-events-none">+62</span>
                    <input 
                        wire:model="phone" 
                        id="phone" 
                        type="tel" 
                        placeholder="81234567890" 
                        class="w-full pl-12 pr-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm transition"
                        required />
                </div>
                @error('phone') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Conditional Fields based on Role -->
        @if ($role === 'pasien')
            <!-- Tanggal Lahir & Alamat -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="birth_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                        Tanggal Lahir Pasien <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        wire:model="birth_date" 
                        id="birth_date" 
                        type="date" 
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm transition"
                        required />
                    @error('birth_date') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="address" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                        Alamat Domisili Kunjungan <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        wire:model="address" 
                        id="address" 
                        type="text" 
                        placeholder="Nama Jalan, No. Rumah, Kota" 
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm transition"
                        required />
                    @error('address') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
        @else
            <!-- NIRA/STR & Peminatan Spesialisasi -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="str_number" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                        Nomor Registrasi STR / NIRA <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        wire:model="str_number" 
                        id="str_number" 
                        type="text" 
                        placeholder="Contoh: 140152119-3891024" 
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm transition"
                        required />
                    @error('str_number') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="specialization" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                        Peminatan Tindakan Medis <span class="text-rose-500">*</span>
                    </label>
                    <select 
                        wire:model="specialization" 
                        id="specialization" 
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm transition"
                        required>
                        <option value="Perawatan Luka Kronis & Diabetes">Perawatan Luka Kronis & Diabetes</option>
                        <option value="Pemasangan Kateter Urine & NGT">Pemasangan Kateter Urine & NGT</option>
                        <option value="Pemberian Infus & Injeksi Berkala">Pemberian Infus & Injeksi Berkala</option>
                        <option value="Terapi Respirasi & Nebulizer">Terapi Respirasi & Nebulizer</option>
                        <option value="Pendampingan Lansia & Pascastroke">Pendampingan Lansia & Pascastroke</option>
                        <option value="Perawatan Paliatif & Bedridden">Perawatan Paliatif & Bedridden</option>
                    </select>
                    @error('specialization') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
        @endif

        <!-- Row: Password & Konfirmasi Password -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                    Kata Sandi <span class="text-rose-500">*</span>
                </label>
                <input 
                    wire:model="password" 
                    id="password" 
                    type="password" 
                    placeholder="Minimal 8 karakter" 
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm transition"
                    required autocomplete="new-password" />
                @error('password') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                    Konfirmasi Kata Sandi <span class="text-rose-500">*</span>
                </label>
                <input 
                    wire:model="password_confirmation" 
                    id="password_confirmation" 
                    type="password" 
                    placeholder="Ulangi kata sandi" 
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm transition"
                    required autocomplete="new-password" />
                @error('password_confirmation') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Ketentuan Privasi -->
        <div class="text-xs text-slate-500 leading-relaxed pt-2">
            Dengan mendaftar, Anda menyetujui <a href="#" class="text-emerald-700 underline font-medium hover:text-emerald-800">Syarat & Ketentuan</a> serta <a href="#" class="text-emerald-700 underline font-medium hover:text-emerald-800">Kebijakan Privasi Rekam Medis (UU PDP No. 27/2022)</a> CareAtHome.
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button 
                type="submit" 
                class="w-full py-3.5 px-6 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-700 hover:to-emerald-600 text-white font-extrabold text-sm sm:text-base tracking-wide shadow-lg shadow-emerald-600/30 hover:shadow-emerald-600/40 transform hover:-translate-y-0.5 active:translate-y-0 transition-all flex items-center justify-center gap-2">
                @if ($role === 'pasien')
                    <span>Daftar Sebagai Pasien</span>
                @else
                    <span>Daftar & Lanjut Unggah STR / SIP</span>
                @endif
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                </svg>
            </button>
        </div>

        <!-- Link ke Login -->
        <div class="text-center pt-4 border-t border-slate-200 text-xs sm:text-sm text-slate-600">
            Sudah memiliki akun? 
            <a href="{{ route('login') }}" class="font-bold text-emerald-700 hover:text-emerald-800 ml-1 underline decoration-2 underline-offset-2" wire:navigate>
                Masuk di Sini
            </a>
        </div>
    </form>
</div>
