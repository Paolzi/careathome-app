<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi Legalitas STR/SIP Nakes - CareAtHome</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col" x-data="{
    draftSaved: false,
    isSavingDraft: false,
    isSubmitting: false,
    lastSavedTime: '',

    // Form Inputs — pre-populated dari database
    nik: '{{ old('nik', $nurseProfile->nik ?? '') }}',
    str_number: '{{ old('str_number', $nurseProfile->str_number ?? '') }}',
    str_expiry: '{{ old('str_expiry', $nurseProfile->str_expiry ? \Carbon\Carbon::parse($nurseProfile->str_expiry)->format('Y-m-d') : '') }}',
    sip_number: '{{ old('sip_number', $nurseProfile->sip_number ?? '') }}',

    // Dokumen — pre-populated dari database (null = belum ada)
    documents: {
        ktp: {{ isset($documents['ktp']) ? json_encode(['name' => $documents['ktp']->original_name, 'size' => $documents['ktp']->file_size, 'isPdf' => str_contains($documents['ktp']->mime_type ?? '', 'pdf'), 'isImage' => str_contains($documents['ktp']->mime_type ?? '', 'image'), 'fromDb' => true, 'isSample' => false]) : 'null' }},
        str: {{ isset($documents['str']) ? json_encode(['name' => $documents['str']->original_name, 'size' => $documents['str']->file_size, 'isPdf' => str_contains($documents['str']->mime_type ?? '', 'pdf'), 'isImage' => str_contains($documents['str']->mime_type ?? '', 'image'), 'fromDb' => true, 'isSample' => false]) : 'null' }},
        sip: {{ isset($documents['sip']) ? json_encode(['name' => $documents['sip']->original_name, 'size' => $documents['sip']->file_size, 'isPdf' => str_contains($documents['sip']->mime_type ?? '', 'pdf'), 'isImage' => str_contains($documents['sip']->mime_type ?? '', 'image'), 'fromDb' => true, 'isSample' => false]) : 'null' }},
        ijazah: {{ isset($documents['ijazah']) ? json_encode(['name' => $documents['ijazah']->original_name, 'size' => $documents['ijazah']->file_size, 'isPdf' => str_contains($documents['ijazah']->mime_type ?? '', 'pdf'), 'isImage' => str_contains($documents['ijazah']->mime_type ?? '', 'image'), 'fromDb' => true, 'isSample' => false]) : 'null' }}
    },

    init() {
        // Tandai waktu tersimpan jika ada data dari DB
        const dbSavedAt = '{{ $nurseProfile->updated_at ? $nurseProfile->updated_at->translatedFormat("d M Y H:i") : "" }}';
        if (dbSavedAt) this.lastSavedTime = dbSavedAt;
    },

    // Handler upload file nyata
    handleFileUpload(event, docType) {
        const file = event.target.files[0];
        if (!file) return;
        this.documents[docType] = {
            name: file.name,
            size: (file.size / (1024 * 1024)).toFixed(1) + ' MB',
            isPdf: file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf'),
            isImage: file.type.startsWith('image/'),
            fromDb: false,
            isSample: false
        };
        // Reset hidden sample input
        const sampleInput = document.getElementById('sample_' + docType);
        if (sampleInput) sampleInput.value = '';
    },

    // Load contoh dokumen (untuk demo/testing)
    loadSampleDoc(docType, sampleType) {
        const isPdf = sampleType === 'pdf';
        this.documents[docType] = {
            name: isPdf
                ? docType.toUpperCase() + '_Dokumen_Resmi_Contoh.pdf'
                : 'Swafoto_KTP_Contoh.jpg',
            size: isPdf ? '1.2 MB' : '850 KB',
            isPdf: isPdf,
            isImage: !isPdf,
            fromDb: false,
            isSample: true
        };
        // Set hidden sample input agar ikut terkirim ke server
        const sampleInput = document.getElementById('sample_' + docType);
        if (sampleInput) sampleInput.value = '1';
        // Reset file input
        const fileInput = document.getElementById('file_' + docType);
        if (fileInput) fileInput.value = '';
    },

    removeDoc(docType) {
        this.documents[docType] = null;
        const sampleInput = document.getElementById('sample_' + docType);
        if (sampleInput) sampleInput.value = '';
        const fileInput = document.getElementById('file_' + docType);
        if (fileInput) fileInput.value = '';
    },

    // SIMPAN DRAF ke DATABASE via AJAX (tanpa navigasi halaman)
    async handleSaveDraft() {
        this.isSavingDraft = true;
        try {
            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name=csrf-token]').content);
            formData.append('nik', this.nik || '');
            formData.append('str_number', this.str_number || '');
            formData.append('str_expiry', this.str_expiry || '');
            formData.append('sip_number', this.sip_number || '');

            // Lampirkan file nyata jika ada
            ['ktp', 'str', 'sip', 'ijazah'].forEach(type => {
                const fileInput = document.getElementById('file_' + type);
                if (fileInput && fileInput.files && fileInput.files[0]) {
                    formData.append(type, fileInput.files[0]);
                }
                // Lampirkan flag sample jika dipilih
                const sampleInput = document.getElementById('sample_' + type);
                if (sampleInput && sampleInput.value === '1') {
                    formData.append('sample_' + type, '1');
                }
            });

            const response = await fetch('{{ route('perawat.verifikasi.draft') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            if (data.success) {
                this.draftSaved = true;
                this.lastSavedTime = data.saved_at || new Date().toLocaleString('id-ID');
                window.scrollTo({ top: 0, behavior: 'smooth' });
                // Auto-hide notif setelah 4 detik
                setTimeout(() => { this.draftSaved = false; }, 4000);
            } else {
                alert('Gagal menyimpan draf. Silakan coba lagi.');
            }
        } catch (err) {
            console.error('Error simpan draf:', err);
            alert('Koneksi bermasalah. Pastikan server berjalan dan coba lagi.');
        } finally {
            this.isSavingDraft = false;
        }
    },

    // KIRIM DOKUMEN ke Admin — form POST native ke server
    handleSubmit() {
        if (this.isSubmitting) return;
        this.isSubmitting = true;

        // Update hidden inputs dengan nilai Alpine terkini sebelum submit
        document.getElementById('hidden_nik').value = this.nik || '';
        document.getElementById('hidden_str_number').value = this.str_number || '';
        document.getElementById('hidden_str_expiry').value = this.str_expiry || '';
        document.getElementById('hidden_sip_number').value = this.sip_number || '';

        // Submit form ke server secara native
        document.getElementById('nurse-verification-form').submit();
    }
}">

    <!-- Top Announcement Bar -->
    <div class="bg-gradient-to-r from-emerald-900 via-emerald-800 to-teal-900 text-white text-xs py-2 px-4 text-center font-medium border-b border-emerald-700/50">
        <span class="inline-flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            PORTAL MITRA PERAWAT RESMI &bull; Standarisasi Legalitas STR & SIP Sesuai Regulasi Kemenkes RI
        </span>
    </div>

    <!-- Header Navbar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="/" class="flex items-center gap-3">
                    <x-application-logo />
                </a>
                <span class="hidden sm:inline-block text-slate-300">|</span>
                <span class="hidden sm:inline-block text-xs font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200">
                    Portal Nakes
                </span>
            </div>

            <div class="flex items-center gap-3 sm:gap-4">
                <!-- User Profile Info -->
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-emerald-600 to-teal-500 text-white font-bold flex items-center justify-center text-sm shadow">
                        {{ auth()->check() ? substr(auth()->user()->name, 0, 2) : 'NS' }}
                    </div>
                    <div class="hidden md:block text-left text-xs leading-tight">
                        <p class="font-bold text-slate-900">{{ auth()->check() ? auth()->user()->name : 'Ns. Siti Nurhaliza, S.Kep' }}</p>
                        <p class="text-emerald-600 font-medium">Mitra Perawat (Menunggu Verifikasi)</p>
                    </div>
                </div>

                <!-- Ke Landing Page Button -->
                <a href="/" class="text-xs font-bold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 px-3.5 py-2 rounded-xl border border-emerald-200 transition flex items-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span>Ke Landing Page</span>
                </a>

                <!-- Keluar (Logout) -->
                <a href="{{ route('logout') }}" class="text-xs font-semibold text-slate-500 hover:text-rose-600 px-2.5 py-2 transition flex items-center gap-1" title="Keluar dari sesi akun">
                    <span>Keluar</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Container with Sidebar -->
    <div class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col lg:flex-row gap-8">
        
        <!-- Sidebar Navigation (SCRUM-37) -->
        <aside class="w-full lg:w-64 shrink-0">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sticky top-24 space-y-6">
                
                <!-- Nurse Profile Card -->
                <div class="p-3.5 rounded-xl bg-gradient-to-br from-emerald-900 to-[#022c22] text-white">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-full bg-emerald-500/20 border border-emerald-400/40 flex items-center justify-center text-emerald-300 font-extrabold text-base">
                            🩺
                        </div>
                        <div class="overflow-hidden">
                            <h3 class="font-bold text-xs truncate">{{ auth()->check() ? auth()->user()->name : 'Ns. Siti Nurhaliza' }}</h3>
                            <span class="inline-block mt-0.5 text-[10px] bg-amber-400/20 text-amber-300 font-semibold px-2 py-0.5 rounded-full border border-amber-400/30">
                                Dokumen Pending
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Navigation Menu -->
                <nav class="space-y-1 text-xs font-medium">
                    <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span>1. Profil Biodata Nakes</span>
                    </a>

                    <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-emerald-50 text-emerald-800 font-bold border border-emerald-200 transition">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        <span>2. Verifikasi STR / SIP</span>
                    </a>

                    <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-400 hover:bg-slate-50 cursor-not-allowed">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                        <span>3. Rekening Pencairan Saldo</span>
                    </a>

                    <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-400 hover:bg-slate-50 cursor-not-allowed">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span>4. Pengaturan Akun</span>
                    </a>
                </nav>

                <!-- Help Notice -->
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-[11px] text-slate-600 space-y-1">
                    <p class="font-bold text-slate-800 flex items-center gap-1">
                        <span>ℹ️</span> Butuh Bantuan?
                    </p>
                    <p class="leading-relaxed">Pastikan scan dokumen jelas, terbaca, dan STR masih berlaku minimal 3 bulan ke depan.</p>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 space-y-6">

            <!-- Banner: Draf Tersimpan Dimuat dari Storage -->
            <div x-show="hasLoadedDraft && !submitted && !draftSaved" x-cloak class="p-4 rounded-2xl bg-slate-100 border border-slate-300 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-slate-700">
                <div class="flex items-center gap-2.5">
                    <span class="text-lg">💾</span>
                    <div>
                        <p class="font-bold text-slate-900">Draf Isian & Berkas Tersimpan Otomatis Dimuat</p>
                        <p class="text-[11px] text-slate-500">Data tetap utuh dari sesi sebelumnya (<span class="font-semibold text-emerald-800" x-text="lastSavedTime"></span>) meskipun Anda logout atau menutup peramban.</p>
                    </div>
                </div>
                <button type="button" @click="clearDraft" class="text-xs font-bold text-rose-600 hover:text-rose-800 underline self-start sm:self-auto">
                    Kosongkan / Reset Form
                </button>
            </div>

            <!-- Alert: Berhasil Kirim Dokumen ke Admin -->
            <div x-show="submitted" x-cloak class="p-6 rounded-2xl bg-emerald-50 border-2 border-emerald-500 shadow-lg text-emerald-950 flex flex-col sm:flex-row items-center gap-4 transition-all">
                <div class="w-12 h-12 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0 text-xl font-bold shadow">
                    ✓
                </div>
                <div class="text-center sm:text-left flex-1">
                    <h3 class="text-base font-extrabold text-emerald-900">Dokumen Berhasil Dikirimkan ke Admin!</h3>
                    <p class="text-xs text-emerald-800 mt-1">Status verifikasi Anda sekarang: <strong>"Sedang Diaudit Admin (In Review)"</strong>. Admin akan memvalidasi keaslian STR & SIP Anda ke Konsil Tenaga Kesehatan dalam 1x24 jam kerja.</p>
                </div>
                <a href="/" class="px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold shrink-0 transition shadow-md">
                    Kembali ke Beranda
                </a>
            </div>

            <!-- Alert: Draf Tersimpan -->
            <div x-show="draftSaved" x-cloak class="p-6 rounded-2xl bg-blue-50 border-2 border-blue-400 shadow-md text-blue-950 flex flex-col sm:flex-row items-center gap-4 transition-all">
                <div class="w-12 h-12 rounded-full bg-blue-600 text-white flex items-center justify-center shrink-0 text-xl font-bold shadow">
                    💾
                </div>
                <div class="text-center sm:text-left flex-1">
                    <h3 class="text-base font-extrabold text-blue-900">Draf Pengisian Dokumen Berhasil Disimpan!</h3>
                    <p class="text-xs text-blue-800 mt-1">Seluruh data nomor izin dan lampiran berkas telah tersimpan utuh di peramban Anda. Dokumen tidak akan hilang meskipun Anda logout.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="draftSaved = false" class="px-4 py-2 rounded-xl bg-white border border-blue-300 text-blue-800 text-xs font-bold transition">
                        Lanjut Mengisi
                    </button>
                    <a href="/" class="px-4 py-2 rounded-xl bg-blue-700 hover:bg-blue-800 text-white text-xs font-bold shrink-0 transition shadow-md">
                        Ke Beranda
                    </a>
                </div>
            </div>

            <!-- Page Header Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                            SCRUM-37 &bull; Verifikasi Legalitas
                        </span>
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-2">
                            Unggah Dokumen Legalitas Nakes
                        </h1>
                        <p class="text-sm text-slate-600 mt-1">
                            Lengkapi data identitas (KTP) dan berkas STR & SIP aktif agar akun Anda dapat menerima order kunjungan
                        </p>
                    </div>

                    <div class="shrink-0 flex items-center gap-2 bg-amber-50 text-amber-800 border border-amber-200 px-4 py-2 rounded-xl text-xs font-bold">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-ping"></span>
                        Status: Menunggu Audit Admin
                    </div>
                </div>

                <!-- Step-by-Step Progress Tracker (Steps 1 to 4) -->
                <div class="mt-8 pt-6 border-t border-slate-100">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        
                        <!-- Step 1 (Completed) -->
                        <div class="p-3 rounded-xl bg-emerald-50/70 border border-emerald-200 flex items-center gap-3">
                            <div class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold shrink-0">
                                ✓
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase text-emerald-700">Langkah 1</p>
                                <p class="text-xs font-bold text-slate-800">Identitas Diri</p>
                            </div>
                        </div>

                        <!-- Step 2 (Active) -->
                        <div class="p-3 rounded-xl bg-emerald-600 text-white border-2 border-emerald-500 shadow-md shadow-emerald-600/20 flex items-center gap-3 ring-2 ring-emerald-400/30">
                            <div class="w-7 h-7 rounded-full bg-white text-emerald-800 flex items-center justify-center text-xs font-bold shrink-0">
                                2
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase text-emerald-100">Langkah 2 (Aktif)</p>
                                <p class="text-xs font-extrabold">STR & SIP Nakes</p>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center gap-3 opacity-60">
                            <div class="w-7 h-7 rounded-full bg-slate-300 text-slate-700 flex items-center justify-center text-xs font-bold shrink-0">
                                3
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase text-slate-500">Langkah 3</p>
                                <p class="text-xs font-semibold text-slate-700">Plafon Tarif</p>
                            </div>
                        </div>

                        <!-- Step 4 -->
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center gap-3 opacity-60">
                            <div class="w-7 h-7 rounded-full bg-slate-300 text-slate-700 flex items-center justify-center text-xs font-bold shrink-0">
                                4
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase text-slate-500">Langkah 4</p>
                                <p class="text-xs font-semibold text-slate-700">Audit Admin</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Data Legalitas -->
            <form
                id="nurse-verification-form"
                action="{{ route('perawat.verifikasi.submit') }}"
                method="POST"
                enctype="multipart/form-data"
                class="space-y-6">
                @csrf

                <!-- Hidden inputs bound to Alpine.js to ensure latest values submitted -->
                <input type="hidden" name="nik" :value="nik">
                <input type="hidden" name="str_number" :value="str_number">
                <input type="hidden" name="str_expiry" :value="str_expiry">
                <input type="hidden" name="sip_number" :value="sip_number">

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
                    <h2 class="text-lg font-extrabold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-3">
                        <span class="w-2.5 h-6 rounded-full bg-emerald-600"></span>
                        Data Nomor Lisensi & Izin Praktik
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        
                        <!-- NIK KTP (Default: KOSONG, bound with x-model) -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                Nomor Induk Kependudukan (NIK KTP) <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                maxlength="16"
                                placeholder="16 Digit Angka NIK KTP" 
                                x-model="nik"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm font-mono transition"
                                required />
                            <span class="text-[11px] text-slate-500 mt-1 block">Wajib sesuai dengan dokumen KTP asli</span>
                        </div>

                        <!-- Nomor STR (Default: KOSONG, bound with x-model) -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                Nomor Surat Tanda Registrasi (STR) <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                placeholder="Contoh: 140152119-3891024" 
                                x-model="str_number"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm font-mono transition"
                                required />
                            <span class="text-[11px] text-slate-500 mt-1 block">Diterbitkan oleh KTKI / Konsil Tenaga Kesehatan</span>
                        </div>

                        <!-- Masa Berlaku STR (Default: KOSONG, bound with x-model) -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                Tanggal Masa Berlaku STR <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="date" 
                                x-model="str_expiry"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm transition"
                                required />
                        </div>

                        <!-- Nomor SIP (Default: KOSONG, bound with x-model) -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                Nomor Surat Izin Praktik (SIP) <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                placeholder="Contoh: 446.1/1042/Dinkes-PP/2026" 
                                x-model="sip_number"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm font-mono transition"
                                required />
                            <span class="text-[11px] text-slate-500 mt-1 block">Izin praktik dari Dinas Kesehatan setempat</span>
                        </div>
                    </div>
                </div>

                <!-- Unggah Berkas Bukti Fisik (Live PDF & Image Preview) -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
                        <div>
                            <h2 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
                                <span class="w-2.5 h-6 rounded-full bg-emerald-600"></span>
                                Unggah Berkas Bukti Fisik
                            </h2>
                            <p class="text-xs text-slate-500 mt-0.5">Berkas yang diunggah akan diaudit dan divalidasi keabsahannya oleh tim Admin CareAtHome</p>
                        </div>
                        <span class="text-xs text-slate-500 bg-slate-100 px-3 py-1 rounded-full border border-slate-200">
                            Format: PDF / JPEG / PNG (Maks 5 MB)
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <!-- CARD 1: SWAFOTO KTP -->
                        <div class="p-5 rounded-2xl border-2 transition-all relative"
                             :class="documents.ktp ? 'border-emerald-500 bg-emerald-50/20 shadow-md' : 'border-dashed border-slate-300 hover:border-emerald-500 bg-slate-50/50'">
                            
                            <!-- State: Kosong / Belum Ada File -->
                            <div x-show="!documents.ktp" class="text-center flex flex-col items-center justify-center py-4">
                                <div class="w-14 h-14 rounded-2xl bg-white shadow-sm border border-slate-200 flex items-center justify-center text-3xl mb-2 text-emerald-600">
                                    🪪
                                </div>
                                <h3 class="font-extrabold text-sm text-slate-800">1. Swafoto Bersama KTP Asli</h3>
                                <p class="text-[11px] text-slate-500 mt-1 max-w-xs leading-relaxed">Pegang KTP sejajar dada. Wajah dan nomor NIK harus terbaca jelas.</p>
                                
                                <div class="mt-4 flex items-center gap-2">
                                    <label class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm cursor-pointer transition">
                                        Pilih Berkas KTP
                                        <input type="file" class="hidden" accept="image/*,.pdf" @change="handleFileUpload($event, 'ktp')" />
                                    </label>
                                    <button type="button" @click="loadSampleDoc('ktp', 'image')" class="px-3 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-100 text-slate-600 text-xs font-semibold shadow-sm transition">
                                        + Contoh File
                                    </button>
                                </div>
                            </div>

                            <!-- State: Preview Dokumen / Gambar Terunggah -->
                            <div x-show="documents.ktp" x-cloak class="space-y-3">
                                <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                                    <span class="text-xs font-extrabold text-slate-900 flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        1. Pratinjau Swafoto KTP
                                    </span>
                                    <button type="button" @click="removeDoc('ktp')" class="text-xs font-bold text-rose-600 hover:text-rose-800 underline">
                                        Hapus
                                    </button>
                                </div>

                                <!-- Image Preview Card -->
                                <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
                                    <div class="w-16 h-16 rounded-lg bg-emerald-50 flex items-center justify-center text-3xl border border-emerald-200 shrink-0">
                                        🪪
                                    </div>
                                    <div class="flex-1 overflow-hidden">
                                        <p class="text-xs font-bold text-slate-900 truncate" x-text="documents.ktp?.name"></p>
                                        <p class="text-[11px] text-slate-500 mt-0.5" x-text="'Ukuran: ' + documents.ktp?.size"></p>
                                        
                                        <!-- BADGE STATUS: Neutral "Terlampir", bukan centang verified -->
                                        <span class="inline-flex items-center gap-1.5 mt-1.5 text-[10px] text-amber-800 font-bold bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Berkas KTP Terlampir &bull; Menunggu Audit Admin
                                        </span>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- CARD 2: STR ASLI (PDF PREVIEW CARD) -->
                        <div class="p-5 rounded-2xl border-2 transition-all relative"
                             :class="documents.str ? 'border-emerald-500 bg-emerald-50/20 shadow-md' : 'border-dashed border-slate-300 hover:border-emerald-500 bg-slate-50/50'">
                            
                            <!-- State: Kosong / Belum Ada File -->
                            <div x-show="!documents.str" class="text-center flex flex-col items-center justify-center py-4">
                                <div class="w-14 h-14 rounded-2xl bg-white shadow-sm border border-slate-200 flex items-center justify-center text-3xl mb-2 text-emerald-600">
                                    📜
                                </div>
                                <h3 class="font-extrabold text-sm text-slate-800">2. Surat Tanda Registrasi (STR)</h3>
                                <p class="text-[11px] text-slate-500 mt-1 max-w-xs leading-relaxed">Scan dokumen STR aktif lengkap dengan QR code validasi KTKI.</p>
                                
                                <div class="mt-4 flex items-center gap-2">
                                    <label class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm cursor-pointer transition">
                                        Pilih Berkas STR (PDF)
                                        <input type="file" class="hidden" accept=".pdf,image/*" @change="handleFileUpload($event, 'str')" />
                                    </label>
                                    <button type="button" @click="loadSampleDoc('str', 'pdf')" class="px-3 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-100 text-slate-600 text-xs font-semibold shadow-sm transition">
                                        + Contoh PDF
                                    </button>
                                </div>
                            </div>

                            <!-- State: Pratinjau Halaman Pertama Dokumen PDF -->
                            <div x-show="documents.str" x-cloak class="space-y-3">
                                <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                                    <span class="text-xs font-extrabold text-slate-900 flex items-center gap-1.5">
                                        <span class="px-1.5 py-0.5 rounded bg-rose-600 text-white text-[10px] font-bold">PDF</span>
                                        2. Pratinjau Halaman 1 Dokumen STR
                                    </span>
                                    <button type="button" @click="removeDoc('str')" class="text-xs font-bold text-rose-600 hover:text-rose-800 underline">
                                        Ganti Berkas
                                    </button>
                                </div>

                                <!-- Simulated Official Document 1st-Page Preview -->
                                <div class="p-4 bg-white rounded-xl border border-slate-200 shadow-sm space-y-2.5">
                                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                                        <div class="flex items-center gap-2">
                                            <span class="text-lg">🏛️</span>
                                            <div>
                                                <p class="text-[10px] font-bold uppercase text-slate-700">KONSIL TENAGA KESEHATAN INDONESIA</p>
                                                <p class="text-[9px] text-slate-400">SURAT TANDA REGISTRASI PERAWAT</p>
                                            </div>
                                        </div>
                                        
                                        <!-- BADGE STATUS: Neutral "Terlampir", bukan QR Code Valid ✓ -->
                                        <span class="text-[10px] font-medium text-amber-800 bg-amber-50 px-2 py-0.5 rounded border border-amber-200 flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Berkas STR Terlampir
                                        </span>
                                    </div>

                                    <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-100 space-y-1 text-[11px] text-slate-700">
                                        <p class="font-bold text-slate-900 flex items-center justify-between">
                                            <span x-text="documents.str?.name"></span>
                                            <span class="text-[10px] text-slate-500 font-normal" x-text="documents.str?.size"></span>
                                        </p>
                                        <p class="text-[10px] text-slate-500">Dokumen STR diunggah &bull; Menunggu Pemeriksaan & Validasi Admin</p>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- CARD 3: SIP AKTIF (PDF PREVIEW CARD) -->
                        <div class="p-5 rounded-2xl border-2 transition-all relative"
                             :class="documents.sip ? 'border-emerald-500 bg-emerald-50/20 shadow-md' : 'border-dashed border-slate-300 hover:border-emerald-500 bg-slate-50/50'">
                            
                            <!-- State: Kosong / Belum Ada File -->
                            <div x-show="!documents.sip" class="text-center flex flex-col items-center justify-center py-4">
                                <div class="w-14 h-14 rounded-2xl bg-white shadow-sm border border-slate-200 flex items-center justify-center text-3xl mb-2 text-emerald-600">
                                    🏥
                                </div>
                                <h3 class="font-extrabold text-sm text-slate-800">3. Surat Izin Praktik (SIP) Aktif</h3>
                                <p class="text-[11px] text-slate-500 mt-1 max-w-xs leading-relaxed">Izin praktik yang diterbitkan oleh Dinas Kesehatan setempat.</p>
                                
                                <div class="mt-4 flex items-center gap-2">
                                    <label class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm cursor-pointer transition">
                                        Pilih Berkas SIP (PDF)
                                        <input type="file" class="hidden" accept=".pdf,image/*" @change="handleFileUpload($event, 'sip')" />
                                    </label>
                                    <button type="button" @click="loadSampleDoc('sip', 'pdf')" class="px-3 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-100 text-slate-600 text-xs font-semibold shadow-sm transition">
                                        + Contoh PDF
                                    </button>
                                </div>
                            </div>

                            <!-- State: Pratinjau Halaman Pertama Dokumen SIP -->
                            <div x-show="documents.sip" x-cloak class="space-y-3">
                                <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                                    <span class="text-xs font-extrabold text-slate-900 flex items-center gap-1.5">
                                        <span class="px-1.5 py-0.5 rounded bg-rose-600 text-white text-[10px] font-bold">PDF</span>
                                        3. Pratinjau Halaman 1 Dokumen SIP
                                    </span>
                                    <button type="button" @click="removeDoc('sip')" class="text-xs font-bold text-rose-600 hover:text-rose-800 underline">
                                        Ganti Berkas
                                    </button>
                                </div>

                                <div class="p-4 bg-white rounded-xl border border-slate-200 shadow-sm space-y-2.5">
                                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                                        <div class="flex items-center gap-2">
                                            <span class="text-lg">🏢</span>
                                            <div>
                                                <p class="text-[10px] font-bold uppercase text-slate-700">PEMERINTAH DAERAH DINAS KESEHATAN</p>
                                                <p class="text-[9px] text-slate-400">SURAT IZIN PRAKTIK (SIP) PERAWAT MANDIRI</p>
                                            </div>
                                        </div>
                                        
                                        <!-- BADGE STATUS: Neutral "Terlampir", bukan Legal Dinas ✓ -->
                                        <span class="text-[10px] font-medium text-amber-800 bg-amber-50 px-2 py-0.5 rounded border border-amber-200 flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Berkas SIP Terlampir
                                        </span>
                                    </div>

                                    <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-100 space-y-1 text-[11px] text-slate-700">
                                        <p class="font-bold text-slate-900 flex items-center justify-between">
                                            <span x-text="documents.sip?.name"></span>
                                            <span class="text-[10px] text-slate-500 font-normal" x-text="documents.sip?.size"></span>
                                        </p>
                                        <p class="text-[10px] text-slate-500">Dokumen SIP diunggah &bull; Menunggu Validasi Izin Admin</p>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- CARD 4: IJAZAH KEPERAWATAN -->
                        <div class="p-5 rounded-2xl border-2 transition-all relative"
                             :class="documents.ijazah ? 'border-emerald-500 bg-emerald-50/20 shadow-md' : 'border-dashed border-slate-300 hover:border-emerald-500 bg-slate-50/50'">
                            
                            <!-- State: Kosong / Belum Ada File -->
                            <div x-show="!documents.ijazah" class="text-center flex flex-col items-center justify-center py-4">
                                <div class="w-14 h-14 rounded-2xl bg-white shadow-sm border border-slate-200 flex items-center justify-center text-3xl mb-2 text-emerald-600">
                                    🎓
                                </div>
                                <h3 class="font-extrabold text-sm text-slate-800">4. Ijazah Keperawatan (D3 / S1 / Ners)</h3>
                                <p class="text-[11px] text-slate-500 mt-1 max-w-xs leading-relaxed">Bukti kelulusan institusi pendidikan keperawatan terakreditasi.</p>
                                
                                <div class="mt-4 flex items-center gap-2">
                                    <label class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm cursor-pointer transition">
                                        Pilih Berkas Ijazah
                                        <input type="file" class="hidden" accept=".pdf,image/*" @change="handleFileUpload($event, 'ijazah')" />
                                    </label>
                                    <button type="button" @click="loadSampleDoc('ijazah', 'pdf')" class="px-3 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-100 text-slate-600 text-xs font-semibold shadow-sm transition">
                                        + Contoh PDF
                                    </button>
                                </div>
                            </div>

                            <!-- State: Pratinjau Dokumen Ijazah -->
                            <div x-show="documents.ijazah" x-cloak class="space-y-3">
                                <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                                    <span class="text-xs font-extrabold text-slate-900 flex items-center gap-1.5">
                                        <span class="px-1.5 py-0.5 rounded bg-rose-600 text-white text-[10px] font-bold">PDF</span>
                                        4. Pratinjau Ijazah Pendidikan
                                    </span>
                                    <button type="button" @click="removeDoc('ijazah')" class="text-xs font-bold text-rose-600 hover:text-rose-800 underline">
                                        Ganti Berkas
                                    </button>
                                </div>

                                <div class="p-4 bg-white rounded-xl border border-slate-200 shadow-sm space-y-2.5">
                                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                                        <div class="flex items-center gap-2">
                                            <span class="text-lg">🎓</span>
                                            <div>
                                                <p class="text-[10px] font-bold uppercase text-slate-700">IJAZAH PENDIDIKAN KEPERAWATAN</p>
                                                <p class="text-[9px] text-slate-400">FAKULTAS KEPERAWATAN</p>
                                            </div>
                                        </div>
                                        
                                        <!-- BADGE STATUS: Neutral "Terlampir", bukan Terakreditasi ✓ -->
                                        <span class="text-[10px] font-medium text-amber-800 bg-amber-50 px-2 py-0.5 rounded border border-amber-200 flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Berkas Ijazah Terlampir
                                        </span>
                                    </div>

                                    <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-100 space-y-1 text-[11px] text-slate-700">
                                        <p class="font-bold text-slate-900 flex items-center justify-between">
                                            <span x-text="documents.ijazah?.name"></span>
                                            <span class="text-[10px] text-slate-500 font-normal" x-text="documents.ijazah?.size"></span>
                                        </p>
                                        <p class="text-[10px] text-slate-500">Dokumen Ijazah diunggah &bull; Menunggu Pemeriksaan Admin</p>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>

                <!-- Amber Warning Box -->
                <div class="p-5 rounded-2xl bg-amber-50 border-2 border-amber-300/80 text-amber-950 flex items-start gap-4 shadow-sm">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-800 flex items-center justify-center shrink-0 text-xl font-bold">
                        ⚠️
                    </div>
                    <div class="text-xs sm:text-sm space-y-1">
                        <h4 class="font-extrabold text-amber-900">Perhatian Prosedur Audit Admin:</h4>
                        <p class="leading-relaxed text-amber-800">
                            Dokumen akan divalidasi ke portal resmi Konsil Tenaga Kesehatan Indonesia (KTKI) oleh Admin CareAtHome. Akun perawat akan diaktifkan setelah dokumen terverifikasi sah. <em>Mohon tunggu konfirmasi melalui email dan notifikasi sistem dalam 1x24 jam kerja.</em>
                        </p>
                    </div>
                </div>

                <!-- Action Buttons: Simpan Draf vs Kirim Dokumen -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4">
                    
                    <!-- Batal / Kembali ke Landing Page -->
                    <a href="/" class="w-full sm:w-auto px-6 py-3 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 font-bold text-xs text-center transition flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        <span>Kembali ke Beranda</span>
                    </a>

                    <div class="w-full sm:w-auto flex flex-col sm:flex-row items-center gap-3">
                        
                        <!-- Simpan Draf (Lanjut Nanti) -->
                        <button 
                            type="button" 
                            @click="handleSaveDraft"
                            :disabled="isSavingDraft"
                            class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-white border border-slate-300 hover:border-slate-400 hover:bg-slate-50 text-slate-700 font-bold text-xs transition flex items-center justify-center gap-1.5 shadow-sm disabled:opacity-60 disabled:cursor-not-allowed">
                            <span x-show="!isSavingDraft">💾 Simpan Sebagai Draf</span>
                            <span x-show="isSavingDraft" x-cloak class="flex items-center gap-1.5">
                                <svg class="animate-spin w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Menyimpan...
                            </span>
                        </button>
                        
                        <!-- Submit to Admin — @click calls handleSubmit yang submit form ke server -->
                        <button 
                            type="button"
                            @click="handleSubmit"
                            class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-700 hover:to-emerald-600 text-white font-extrabold text-sm shadow-lg shadow-emerald-600/30 hover:shadow-emerald-600/40 transform hover:-translate-y-0.5 active:translate-y-0 transition flex items-center justify-center gap-2">
                            <span>Simpan dan Kirim Dokumen</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </form>


        </main>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 mt-12 text-center text-xs text-slate-500">
        &copy; 2026 CareAtHome Medical. Sistem Informasi Layanan Homecare Terintegrasi.
    </footer>

</body>
</html>
