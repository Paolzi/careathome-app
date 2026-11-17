<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Spatie\Permission\Models\Role;

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
    public string $nik = '';
    public string $str_number = '';
    public string $sip_number = '';
    public string $specialization = 'Perawatan Luka Kronis & Diabetes';

    // Koordinat GPS otomatis saat registrasi
    public ?float $latitude = null;
    public ?float $longitude = null;

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
            $this->validate($rules);
        } else {
            $rules['nik'] = ['required', 'string', 'min:8', 'max:20', 'unique:nurse_profiles,nik'];
            $rules['str_number'] = ['required', 'string', 'min:8', 'max:50', 'unique:nurse_profiles,str_number'];
            $rules['sip_number'] = ['required', 'string', 'min:5', 'max:100', 'unique:nurse_profiles,sip_number'];
            $rules['specialization'] = ['required', 'string'];
            $this->validate($rules, [
                'nik.required' => 'Nomor Induk Kependudukan (NIK) wajib diisi.',
                'nik.unique' => 'NIK sudah terdaftar oleh akun perawat lain. Registrasi tidak dapat dilanjutkan.',
                'str_number.required' => 'Nomor STR wajib diisi.',
                'str_number.unique' => 'Nomor STR sudah terdaftar oleh akun perawat lain. Registrasi tidak dapat dilanjutkan.',
                'sip_number.required' => 'Nomor SIP wajib diisi.',
                'sip_number.unique' => 'Nomor SIP sudah terdaftar oleh akun perawat lain. Registrasi tidak dapat dilanjutkan.',
            ]);
        }

        // Buat User Baru
        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        // Tetapkan Spatie Role secara ketat
        Role::firstOrCreate(['name' => $this->role, 'guard_name' => 'web']);
        $user->assignRole($this->role);

        event(new Registered($user));
        Auth::login($user);

        // Pisahkan hak akses & redirect awal sejak registrasi
        if ($this->role === 'perawat') {
            \App\Models\NurseProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'nik' => $this->nik,
                    'str_number' => $this->str_number,
                    'sip_number' => $this->sip_number,
                    'specialization' => $this->specialization,
                    'verification_status' => 'draft',
                    'is_available' => false,
                    'is_online' => false,
                    'latitude' => $this->latitude,
                    'longitude' => $this->longitude,
                    'location_updated_at' => ($this->latitude !== null && $this->longitude !== null) ? now() : null,
                ]
            );
            session()->flash('status', 'Pendaftaran akun Mitra Perawat berhasil! Silakan lengkapi berkas legalitas STR & SIP Anda.');
            $this->redirect(route('perawat.verifikasi'), navigate: true);
        } else {
            if ($this->latitude !== null && $this->longitude !== null) {
                session([
                    'patient_last_lat' => $this->latitude,
                    'patient_last_lng' => $this->longitude,
                ]);
            }
            session()->flash('status', 'Pendaftaran akun Pasien berhasil. Selamat datang di CareAtHome!');
            $this->redirect(route('pasien.katalog'), navigate: true);
        }
    }
}; ?>

<div class="w-full max-w-2xl bg-white/95 backdrop-blur-xl shadow-2xl rounded-2xl border border-[#78956A] p-6 sm:p-10 text-[#46571A]"
    x-data
    x-init="
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const lat = parseFloat(pos.coords.latitude.toFixed(6));
                    const lng = parseFloat(pos.coords.longitude.toFixed(6));
                    $wire.set('latitude', lat, false);
                    $wire.set('longitude', lng, false);
                    localStorage.setItem('careathome_patient_lat', lat);
                    localStorage.setItem('careathome_patient_lng', lng);
                },
                () => {},
                { enableHighAccuracy: true, timeout: 6000, maximumAge: 0 }
            );
        }
    ">
    
    <!-- Title & Subtitle -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#D1EB8F]/45 border border-[#78956A]/50 text-[#46571A] text-xs font-semibold mb-3">
            <span class="w-2 h-2 rounded-full bg-[#FF9ABC]"></span>
            Registrasi Pengguna CareAtHome
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-[#46571A] tracking-tight">
            Buat Akun Layanan Kesehatan
        </h1>
        <p class="text-sm text-[#46571A]/85 mt-1.5">
            Pilih jenis akun yang sesuai untuk mendapatkan akses layanan terstandarisasi medis
        </p>
    </div>

    <!-- Role Switcher Tabs -->
    <div class="grid grid-cols-2 gap-3 p-1.5 bg-[#F9F4EA] rounded-xl mb-8 border border-[#78956A]/50">
        <button 
            type="button" 
            wire:click="setRole('pasien')"
            class="flex items-center justify-center gap-2.5 py-3 px-4 rounded-lg font-bold text-sm transition-all duration-200 {{ $role === 'pasien' ? 'bg-[#FF9ABC] text-[#46571A] shadow-md shadow-[#78956A]/25 ring-1 ring-[#78956A]' : 'text-[#46571A]/85 hover:text-[#46571A] hover:bg-[#D1EB8F]/50' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            <span>Pasien / Keluarga</span>
        </button>

        <button 
            type="button" 
            wire:click="setRole('perawat')"
            class="flex items-center justify-center gap-2.5 py-3 px-4 rounded-lg font-bold text-sm transition-all duration-200 {{ $role === 'perawat' ? 'bg-[#FF9ABC] text-[#46571A] shadow-md shadow-[#78956A]/25 ring-1 ring-[#78956A]' : 'text-[#46571A]/85 hover:text-[#46571A] hover:bg-[#D1EB8F]/50' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
            </svg>
            <span>Perawat (Mitra Medis)</span>
        </button>
    </div>

    <!-- Information Banner based on selected role -->
    @if ($role === 'pasien')
        <div class="mb-6 p-4 rounded-xl bg-[#D1EB8F]/45 border border-[#78956A]/50 flex items-start gap-3 text-[#46571A] text-xs sm:text-sm">
            <svg class="w-5 h-5 text-[#78956A] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <p class="font-bold">Akun Pasien CareAtHome</p>
                <p class="text-[#46571A] mt-0.5">Dapat langsung memesan kunjungan perawat berlisensi, melacak status pesanan secara real-time, dan menerima rekam medis digital (E-Report).</p>
            </div>
        </div>
    @else
        <div class="mb-6 p-4 rounded-xl bg-[#F9F4EA] border border-[#78956A]/50 flex items-start gap-3 text-[#46571A] text-xs sm:text-sm">
            <svg class="w-5 h-5 text-[#78956A] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <div>
                <p class="font-bold">Akun Mitra Perawat Profesional</p>
                <p class="text-[#46571A] mt-0.5">Wajib memiliki Surat Tanda Registrasi (STR) aktif. Setelah pengisian data awal ini, Anda akan diarahkan untuk mengunggah dokumen legalitas (STR, SIP, KTP).</p>
            </div>
        </div>
    @endif

    <form wire:submit="register" class="space-y-5">
        
        <!-- Nama Lengkap -->
        <div>
            <label for="name" class="block text-xs font-bold uppercase tracking-wider text-[#46571A] mb-1">
                {{ $role === 'perawat' ? 'Nama Lengkap Beserta Gelar Profesi' : 'Nama Lengkap Pasien' }} <span class="text-[#46571A]">*</span>
            </label>
            <input 
                wire:model="name" 
                id="name" 
                type="text" 
                placeholder="{{ $role === 'perawat' ? 'Contoh: Ns. Siti Rahmawati, S.Kep' : 'Contoh: Ahmad Fauzi' }}" 
                class="w-full px-4 py-2.5 rounded-xl border border-[#78956A] text-[#46571A] focus:outline-none focus:ring-2 focus:ring-[#78956A] focus:border-[#78956A] text-sm transition"
                required autofocus />
            @error('name') <span class="text-[#46571A] text-xs mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Row: Email & No. WhatsApp -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-[#46571A] mb-1">
                    Alamat Email Aktif <span class="text-[#46571A]">*</span>
                </label>
                <input 
                    wire:model="email" 
                    id="email" 
                    type="email" 
                    placeholder="nama@email.com" 
                    class="w-full px-4 py-2.5 rounded-xl border border-[#78956A] text-[#46571A] focus:outline-none focus:ring-2 focus:ring-[#78956A] focus:border-[#78956A] text-sm transition"
                    required />
                @error('email') <span class="text-[#46571A] text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-[#46571A] mb-1">
                    Nomor WhatsApp / HP <span class="text-[#46571A]">*</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-xs font-semibold text-[#78956A] pointer-events-none">+62</span>
                    <input 
                        wire:model="phone" 
                        id="phone" 
                        type="tel" 
                        placeholder="81234567890" 
                        class="w-full pl-12 pr-4 py-2.5 rounded-xl border border-[#78956A] text-[#46571A] focus:outline-none focus:ring-2 focus:ring-[#78956A] focus:border-[#78956A] text-sm transition"
                        required />
                </div>
                @error('phone') <span class="text-[#46571A] text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Conditional Fields based on Role -->
        @if ($role === 'pasien')
            <!-- Tanggal Lahir & Alamat -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="birth_date" class="block text-xs font-bold uppercase tracking-wider text-[#46571A] mb-1">
                        Tanggal Lahir Pasien <span class="text-[#46571A]">*</span>
                    </label>
                    <input 
                        wire:model="birth_date" 
                        id="birth_date" 
                        type="date" 
                        class="w-full px-4 py-2.5 rounded-xl border border-[#78956A] text-[#46571A] focus:outline-none focus:ring-2 focus:ring-[#78956A] focus:border-[#78956A] text-sm transition"
                        required />
                    @error('birth_date') <span class="text-[#46571A] text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="address" class="block text-xs font-bold uppercase tracking-wider text-[#46571A] mb-1">
                        Alamat Domisili Kunjungan <span class="text-[#46571A]">*</span>
                    </label>
                    <input 
                        wire:model="address" 
                        id="address" 
                        type="text" 
                        placeholder="Nama Jalan, No. Rumah, Kota" 
                        class="w-full px-4 py-2.5 rounded-xl border border-[#78956A] text-[#46571A] focus:outline-none focus:ring-2 focus:ring-[#78956A] focus:border-[#78956A] text-sm transition"
                        required />
                    @error('address') <span class="text-[#46571A] text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
        @else
            <!-- NIK, STR, SIP & Peminatan Spesialisasi (Unik per Perawat) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="nik" class="block text-xs font-bold uppercase tracking-wider text-[#46571A] mb-1">
                        Nomor Induk Kependudukan (NIK) <span class="text-[#46571A]">*</span>
                    </label>
                    <input 
                        wire:model="nik" 
                        id="nik" 
                        type="text" 
                        placeholder="Contoh: 3578101503960001" 
                        class="w-full px-4 py-2.5 rounded-xl border border-[#78956A] text-[#46571A] focus:outline-none focus:ring-2 focus:ring-[#78956A] focus:border-[#78956A] text-sm transition"
                        required />
                    @error('nik') <span class="text-[#46571A] text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="str_number" class="block text-xs font-bold uppercase tracking-wider text-[#46571A] mb-1">
                        Nomor Registrasi STR / NIRA <span class="text-[#46571A]">*</span>
                    </label>
                    <input 
                        wire:model="str_number" 
                        id="str_number" 
                        type="text" 
                        placeholder="Contoh: 140152119-3891024" 
                        class="w-full px-4 py-2.5 rounded-xl border border-[#78956A] text-[#46571A] focus:outline-none focus:ring-2 focus:ring-[#78956A] focus:border-[#78956A] text-sm transition"
                        required />
                    @error('str_number') <span class="text-[#46571A] text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="sip_number" class="block text-xs font-bold uppercase tracking-wider text-[#46571A] mb-1">
                        Nomor Surat Izin Praktik (SIP) <span class="text-[#46571A]">*</span>
                    </label>
                    <input 
                        wire:model="sip_number" 
                        id="sip_number" 
                        type="text" 
                        placeholder="Contoh: 446.2/DINKES-SBY/2026" 
                        class="w-full px-4 py-2.5 rounded-xl border border-[#78956A] text-[#46571A] focus:outline-none focus:ring-2 focus:ring-[#78956A] focus:border-[#78956A] text-sm transition"
                        required />
                    @error('sip_number') <span class="text-[#46571A] text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="specialization" class="block text-xs font-bold uppercase tracking-wider text-[#46571A] mb-1">
                        Peminatan Tindakan Medis <span class="text-[#46571A]">*</span>
                    </label>
                    <select 
                        wire:model="specialization" 
                        id="specialization" 
                        class="w-full px-4 py-2.5 rounded-xl border border-[#78956A] text-[#46571A] bg-white focus:outline-none focus:ring-2 focus:ring-[#78956A] focus:border-[#78956A] text-sm transition"
                        required>
                        <option value="Perawatan Luka Kronis & Diabetes">Perawatan Luka Kronis & Diabetes</option>
                        <option value="Pemasangan Kateter Urine & NGT">Pemasangan Kateter Urine & NGT</option>
                        <option value="Pemberian Infus & Injeksi Berkala">Pemberian Infus & Injeksi Berkala</option>
                        <option value="Terapi Respirasi & Nebulizer">Terapi Respirasi & Nebulizer</option>
                        <option value="Pendampingan Lansia & Pascastroke">Pendampingan Lansia & Pascastroke</option>
                        <option value="Perawatan Paliatif & Bedridden">Perawatan Paliatif & Bedridden</option>
                    </select>
                    @error('specialization') <span class="text-[#46571A] text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
        @endif

        <!-- Row: Password & Konfirmasi Password -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-[#46571A] mb-1">
                    Kata Sandi <span class="text-[#46571A]">*</span>
                </label>
                <input 
                    wire:model="password" 
                    id="password" 
                    type="password" 
                    placeholder="Minimal 8 karakter" 
                    class="w-full px-4 py-2.5 rounded-xl border border-[#78956A] text-[#46571A] focus:outline-none focus:ring-2 focus:ring-[#78956A] focus:border-[#78956A] text-sm transition"
                    required autocomplete="new-password" />
                @error('password') <span class="text-[#46571A] text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-[#46571A] mb-1">
                    Konfirmasi Kata Sandi <span class="text-[#46571A]">*</span>
                </label>
                <input 
                    wire:model="password_confirmation" 
                    id="password_confirmation" 
                    type="password" 
                    placeholder="Ulangi kata sandi" 
                    class="w-full px-4 py-2.5 rounded-xl border border-[#78956A] text-[#46571A] focus:outline-none focus:ring-2 focus:ring-[#78956A] focus:border-[#78956A] text-sm transition"
                    required autocomplete="new-password" />
                @error('password_confirmation') <span class="text-[#46571A] text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Ketentuan Privasi -->
        <div class="text-xs text-[#78956A] leading-relaxed pt-2">
            Dengan mendaftar, Anda menyetujui <a href="#" class="text-[#46571A] underline font-medium hover:text-[#46571A]">Syarat & Ketentuan</a> serta <a href="#" class="text-[#46571A] underline font-medium hover:text-[#46571A]">Kebijakan Privasi Rekam Medis (UU PDP No. 27/2022)</a> CareAtHome.
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button 
                type="submit" 
                class="w-full py-3.5 px-6 rounded-xl bg-[#78956A] hover:bg-[#D1EB8F] text-[#46571A] font-extrabold text-sm sm:text-base tracking-wide shadow-lg shadow-[#78956A]/25 hover:shadow-[#78956A]/25 transform hover:-translate-y-0.5 active:translate-y-0 transition-all flex items-center justify-center gap-2">
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
        <div class="text-center pt-4 border-t border-[#78956A]/50 text-xs sm:text-sm text-[#46571A]/85">
            Sudah memiliki akun? 
            <a href="{{ route('login') }}" class="font-bold text-[#46571A] hover:text-[#46571A] ml-1 underline decoration-2 underline-offset-2" wire:navigate>
                Masuk di Sini
            </a>
        </div>
    </form>
</div>
