<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head> <meta charset="utf-8"> <meta name="viewport" content="width=device-width, initial-scale=1"> <title>Verifikasi Legalitas STR/SIP Nakes - CareAtHome</title> <meta name="csrf-token" content="{{ csrf_token() }}"> <!-- Scripts & Styles --> @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js --> <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script> <style> [x-cloak] { display: none !important; }
    </style>
</head>
@php
    $isLocked = in_array($nurseProfile->verification_status ?? '', ['pending', 'verified']);
    $userNotifications = auth()->check() ? auth()->user()->notifications()->take(10)->get() : collect();
    $unreadNotifCount = auth()->check() ? auth()->user()->unreadNotifications()->count() : 0;
@endphp
<body class="bg-[#F9F4EA] text-[#46571A] font-sans antialiased min-h-screen flex flex-col" x-data="{
    draftSaved: false,
    isSavingDraft: false,
    isSubmitting: false,
    lastSavedTime: '',
    isLocked: {{ $isLocked ? 'true' : 'false' }},

    // Form Inputs — pre-populated dari database
    nik: '{{ old('nik', $nurseProfile->nik ?? '') }}',
    str_number: '{{ old('str_number', $nurseProfile->str_number ?? '') }}',
    str_expiry: '{{ old('str_expiry', $nurseProfile->str_expiry ? \Carbon\Carbon::parse($nurseProfile->str_expiry)->format('Y-m-d') : ($nurseProfile->str_expired_at ? \Carbon\Carbon::parse($nurseProfile->str_expired_at)->format('Y-m-d') : '')) }}',
    sip_number: '{{ old('sip_number', $nurseProfile->sip_number ?? '') }}',
    sip_expiry: '{{ old('sip_expiry', $nurseProfile->sip_expiry ? \Carbon\Carbon::parse($nurseProfile->sip_expiry)->format('Y-m-d') : ($nurseProfile->sip_expired_at ? \Carbon\Carbon::parse($nurseProfile->sip_expired_at)->format('Y-m-d') : '')) }}',
    sip_institution: '{{ old('sip_institution', $nurseProfile->sip_institution ?? '') }}',
    specialization: '{{ old('specialization', $nurseProfile->specialization ?? '') }}',

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
        if (this.documents[docType] && this.documents[docType].fromDb) {
            // Hapus dari database via AJAX
            fetch('/perawat/verifikasi/document/' + docType, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                }
            }).catch(console.error);
        }
        this.documents[docType] = null;
        const sampleInput = document.getElementById('sample_' + docType);
        if (sampleInput) sampleInput.value = '';
        const fileInput = document.getElementById('file_' + docType);
        if (fileInput) fileInput.value = '';
    },

    changeDoc(docType) {
        const fileInput = document.getElementById('file_' + docType);
        if (fileInput) fileInput.click();
    },

    // SIMPAN DRAF ke DATABASE via AJAX (tanpa navigasi halaman)
    async handleSaveDraft() {
        if (this.isLocked) return;
        this.isSavingDraft = true;
        try {
            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name=csrf-token]').content);
            formData.append('nik', this.nik || '');
            formData.append('str_number', this.str_number || '');
            formData.append('str_expiry', this.str_expiry || '');
            formData.append('sip_number', this.sip_number || '');
            formData.append('sip_expiry', this.sip_expiry || '');
            formData.append('sip_institution', this.sip_institution || '');
            formData.append('specialization', this.specialization || '');

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
        if (this.isLocked || this.isSubmitting) return;

        // Validasi kelengkapan awal sebelum submit
        if (!this.nik || !this.str_number || !this.str_expiry || !this.sip_number) {
            alert('Mohon lengkapi NIK KTP, Nomor STR, Masa Berlaku STR, dan Nomor SIP sebelum mengirim dokumen.');
            return;
        }

        this.isSubmitting = true;

        // Update hidden inputs dengan nilai Alpine terkini sebelum submit
        document.getElementById('hidden_nik').value = this.nik || '';
        document.getElementById('hidden_str_number').value = this.str_number || '';
        document.getElementById('hidden_str_expiry').value = this.str_expiry || '';
        document.getElementById('hidden_sip_number').value = this.sip_number || '';
        document.getElementById('hidden_sip_expiry').value = this.sip_expiry || '';
        document.getElementById('hidden_sip_institution').value = this.sip_institution || '';
        document.getElementById('hidden_specialization').value = this.specialization || '';

        // Submit form ke server secara native
        document.getElementById('nurse-verification-form').submit();
    }
}"> <!-- Unified Navbar --> <x-careathome-navbar /> <!-- Main Container with Sidebar --> <div class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col lg:flex-row gap-8"> <!-- Sidebar Navigation --> <aside class="w-full lg:w-64 shrink-0"> <div class="bg-white rounded-2xl border border-[#78956A]/50 shadow-sm p-4 sticky top-24 space-y-6"> <!-- Nurse Profile Card --> <div class="p-3.5 rounded-xl bg-[#78956A] to-[#022c22] text-[#F9F4EA]"> <div class="flex items-center gap-3"> <div class="w-11 h-11 rounded-full bg-[#FF9ABC] border border-[#78956A] flex items-center justify-center text-[#D1EB8F] font-extrabold text-base"> </div> <div class="overflow-hidden"> <h3 class="font-bold text-xs truncate">{{ auth()->check() ? auth()->user()->name : 'Ns. Siti Nurhaliza' }}</h3> @if(($nurseProfile->verification_status ?? '') === 'verified')
                                <span class="inline-block mt-0.5 text-[10px] bg-[#D1EB8F] text-[#D1EB8F] font-extrabold px-2 py-0.5 rounded-full border border-[#78956A]"> Akun Terverifikasi
                                </span> @elseif(($nurseProfile->verification_status ?? '') === 'pending')
                                <span class="inline-block mt-0.5 text-[10px] bg-[#D1EB8F] text-[#D1EB8F] font-semibold px-2 py-0.5 rounded-full border border-[#78956A]"> Dokumen Pending
                                </span> @elseif(($nurseProfile->verification_status ?? '') === 'rejected')
                                <span class="inline-block mt-0.5 text-[10px] bg-[#D1EB8F]/20 text-[#FF9ABC] font-semibold px-2 py-0.5 rounded-full border border-[#FF9ABC]"> Ditolak Admin
                                </span> @else
                                <span class="inline-block mt-0.5 text-[10px] bg-[#D1EB8F]/20 text-[#46571A] font-semibold px-2 py-0.5 rounded-full border border-[#78956A]"> Draf Isian
                                </span> @endif
                        </div> </div> </div> <!-- Navigation Menu --> <nav class="space-y-1 text-xs font-medium"> <!-- Dashboard Kerja --> <a href="{{ route('perawat.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[#46571A] hover:bg-[#D1EB8F]/45 hover:text-[#46571A] transition"> <svg class="w-4 h-4 text-[#78956A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"> <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path> </svg> <span class="font-bold text-[#46571A]">Dashboard &amp; Ketersediaan</span> </a> <!-- 1. Profil Biodata Nakes --> <a href="{{ route('profile') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[#46571A]/85 hover:bg-[#F9F4EA] hover:text-[#46571A] transition"> <svg class="w-4 h-4 text-[#78956A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"> <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path> </svg> <span>1. Profil Biodata Nakes</span> </a> <!-- 2. Verifikasi STR / SIP (Aktif) --> <a href="{{ route('perawat.verifikasi') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-[#D1EB8F]/45 text-[#46571A] font-bold border border-[#78956A]/50 transition"> <svg class="w-4 h-4 text-[#78956A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"> <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path> </svg> <span>2. Verifikasi STR / SIP</span> </a> <!-- 3. Rekening Pencairan Saldo --> <a href="{{ route('perawat.dashboard') }}#rekening-saldo" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[#46571A]/85 hover:bg-[#D1EB8F]/45 hover:text-[#46571A] transition"> <svg class="w-4 h-4 text-[#78956A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"> <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path> </svg> <span>3. Rekening Pencairan Saldo</span> </a> <!-- 4. Pengaturan Akun --> <a href="{{ route('profile') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[#46571A]/85 hover:bg-[#D1EB8F]/45 hover:text-[#46571A] transition"> <svg class="w-4 h-4 text-[#78956A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"> <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path> <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path> </svg> <span>4. Pengaturan Akun</span> </a> </nav> <!-- Help Notice --> <div class="p-3.5 rounded-xl bg-[#F9F4EA] border border-[#78956A]/50 text-[11px] text-[#46571A]/85 space-y-1"> <p class="font-bold text-[#46571A] flex items-center gap-1"> <span>ℹ</span> Butuh Bantuan?
                    </p> <p class="leading-relaxed">Pastikan scan dokumen jelas, terbaca, dan STR masih berlaku minimal 3 bulan ke depan.</p> </div> </div> </aside> <!-- Main Content Area --> <main class="flex-1 space-y-6"> <!-- Banner: Draf Tersimpan Dimuat dari Storage --> <div x-show="hasLoadedDraft && !submitted && !draftSaved" x-cloak class="p-4 rounded-2xl bg-[#F9F4EA] border border-[#78956A] shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-[#46571A]"> <div class="flex items-center gap-2.5"> <span class="text-lg"></span> <div> <p class="font-bold text-[#46571A]">Draf Isian & Berkas Tersimpan Otomatis Dimuat</p> <p class="text-[11px] text-[#78956A]">Data tetap utuh dari sesi sebelumnya (<span class="font-semibold text-[#46571A]" x-text="lastSavedTime"></span>) meskipun Anda logout atau menutup peramban.</p> </div> </div> <button type="button" @click="clearDraft" class="text-xs font-bold text-[#46571A] hover:text-[#46571A] underline self-start sm:self-auto"> Kosongkan / Reset Form
                </button> </div> <!-- Alert: Berhasil Kirim Dokumen ke Admin --> <div x-show="submitted" x-cloak class="p-6 rounded-2xl bg-[#D1EB8F]/45 border-2 border-[#78956A] shadow-lg text-[#46571A] flex flex-col sm:flex-row items-center gap-4 transition-all"> <div class="w-12 h-12 rounded-full bg-[#FF9ABC] text-[#46571A] flex items-center justify-center shrink-0 text-xl font-bold shadow"> </div> <div class="text-center sm:text-left flex-1"> <h3 class="text-base font-extrabold text-[#46571A]">Dokumen Berhasil Dikirimkan ke Admin!</h3> <p class="text-xs text-[#46571A] mt-1">Status verifikasi Anda sekarang: <strong>"Sedang Diaudit Admin (In Review)"</strong>. Admin akan memvalidasi keaslian STR & SIP Anda ke Konsil Tenaga Kesehatan dalam 1x24 jam kerja.</p> </div> <a href="/" class="px-5 py-2.5 rounded-xl bg-[#FF9ABC] hover:bg-[#78956A] text-[#46571A] text-xs font-bold shrink-0 transition shadow-md"> Kembali ke Beranda
                </a> </div> <!-- Alert: Draf Tersimpan --> <div x-show="draftSaved" x-cloak class="p-6 rounded-2xl bg-[#D1EB8F]/45 border-2 border-[#78956A] shadow-md text-[#46571A] flex flex-col sm:flex-row items-center gap-4 transition-all"> <div class="w-12 h-12 rounded-full bg-[#FF9ABC] text-[#46571A] flex items-center justify-center shrink-0 text-xl font-bold shadow"> </div> <div class="text-center sm:text-left flex-1"> <h3 class="text-base font-extrabold text-[#46571A]">Draf Pengisian Dokumen Berhasil Disimpan!</h3> <p class="text-xs text-[#46571A] mt-1">Seluruh data nomor izin dan lampiran berkas telah tersimpan utuh di peramban Anda. Dokumen tidak akan hilang meskipun Anda logout.</p> </div> <div class="flex items-center gap-2"> <button type="button" @click="draftSaved = false" class="px-4 py-2 rounded-xl bg-white border border-[#78956A] text-[#46571A] text-xs font-bold transition"> Lanjut Mengisi
                    </button> <a href="/" class="px-4 py-2 rounded-xl bg-[#FF9ABC] hover:bg-[#D1EB8F] text-[#46571A] text-xs font-bold shrink-0 transition shadow-md"> Ke Beranda
                    </a> </div> </div> <!-- Banner Khusus Berdasarkan Status Verifikasi Nakes --> @if(($nurseProfile->verification_status ?? '') === 'verified')
                <div class="p-6 rounded-3xl bg-[#78956A] text-[#F9F4EA] shadow-xl flex flex-col gap-5 border-2 border-[#78956A]"> <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4"> <div class="flex items-start sm:items-center gap-4"> <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-3xl shrink-0 border border-white/20"> </div> <div class="space-y-1"> <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#D1EB8F] border border-[#78956A] text-[#F9F4EA] text-[10px] font-black uppercase tracking-wider"> NOTIFIKASI RESMI: LEGALITAS DISETUJUI ADMIN
                                </span> <h3 class="text-lg sm:text-xl font-black tracking-tight text-white">Selamat! STR &amp; SIP Anda Telah Diverifikasi &amp; Disetujui</h3> <p class="text-xs text-[#F9F4EA] leading-relaxed max-w-2xl"> Seluruh fitur Mitra Perawat kini telah aktif sepenuhnya! Anda dapat langsung mengatur Jadwal Harian (WIB), menerima pesanan homecare di Feed Order, mengisi E-Report Klinis, dan mencairkan pendapatan di Dompet Nakes.
                                </p> </div> </div> <a href="{{ route('perawat.dashboard') }}" class="px-6 py-3.5 rounded-2xl bg-[#FF9ABC] hover:bg-[#D1EB8F] text-[#46571A] font-black text-xs shadow-lg transition transform hover:-translate-y-0.5 whitespace-nowrap flex items-center gap-2 shrink-0"> <span> Masuk ke Dashboard &amp; Mulai Bekerja</span> <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg> </a> </div> <div class="pt-3 border-t border-white/15 flex flex-wrap items-center gap-2.5 text-xs"> <span class="text-[#F9F4EA] font-bold text-[11px]">Akses Cepat Fitur Perawat:</span> <a href="{{ route('perawat.dashboard') }}" class="px-3.5 py-1.5 rounded-xl bg-white/15 hover:bg-white/25 text-white font-bold text-[11px] transition"> Atur Jadwal &amp; Layanan</a> <a href="{{ route('perawat.feed') }}" class="px-3.5 py-1.5 rounded-xl bg-white/15 hover:bg-white/25 text-white font-bold text-[11px] transition"> Buka Feed Order Masuk</a> <a href="{{ route('perawat.finansial') }}" class="px-3.5 py-1.5 rounded-xl bg-white/15 hover:bg-white/25 text-white font-bold text-[11px] transition"> Dompet &amp; Pencairan Saldo</a> </div> </div> @elseif(($nurseProfile->verification_status ?? '') === 'rejected')
                <div class="p-6 rounded-3xl bg-[#78956A] text-[#F9F4EA] shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-6 border-2 border-[#FF9ABC]"> <div class="flex items-start gap-4"> <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-3xl shrink-0 border border-white/20"> </div> <div class="space-y-2"> <div class="flex flex-wrap items-center gap-2"> <span class="px-2.5 py-0.5 rounded-full bg-[#FF9ABC] border border-[#FF9ABC] text-[#F9F4EA] text-[10px] font-black uppercase tracking-wider"> STATUS LEGALITAS: DITOLAK ADMIN &bull; PERLU REVISI
                                </span> @if ($nurseProfile->reviewed_at)
                                    <span class="text-[10px] text-[#F9F4EA]">Direview {{ $nurseProfile->reviewed_at->diffForHumans() }}</span> @endif
                            </div> <h3 class="text-lg sm:text-xl font-black tracking-tight text-white">Pengajuan Dokumen Legalitas Anda Ditolak — Silakan Revisi</h3> <div class="p-3.5 rounded-2xl bg-black/25 border border-[#FF9ABC] text-xs text-[#F9F4EA] leading-relaxed max-w-2xl"> <strong class="text-[#F9F4EA] block mb-0.5">Catatan Alasan Penolakan dari Administrator:</strong> &ldquo;{{ $nurseProfile->rejection_reason ?? 'Terdapat berkas yang tidak valid atau buram. Silakan periksa kembali nomor dan berkas STR/SIP Anda.' }}&rdquo;
                            </div> <p class="text-[11px] text-[#F9F4EA]"> Formulir di bawah ini telah <strong>dibuka kembali</strong>. Silakan perbaiki data NIK/STR/SIP atau ganti berkas dokumen yang diminta, lalu klik tombol <strong>&ldquo;Kirim Ulang Hasil Revisi ke Admin&rdquo;</strong> di bagian bawah.
                            </p> </div> </div> <button type="button"
                        @click="document.getElementById('nurse-verification-form').scrollIntoView({ behavior: 'smooth' }); setTimeout(() => document.getElementById('input_nik')?.focus(), 400);"
                        class="px-5 py-3.5 rounded-2xl bg-white text-[#46571A] hover:bg-[#FF9ABC]/25 font-black text-xs shadow-lg transition transform hover:-translate-y-0.5 whitespace-nowrap flex items-center gap-2 shrink-0 cursor-pointer"> <span> Revisi Data &amp; Berkas Sekarang</span> <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg> </button> </div> @elseif(($nurseProfile->verification_status ?? '') === 'pending')
                <div class="p-6 rounded-3xl bg-[#78956A] text-[#F9F4EA] shadow-xl flex flex-col md:flex-row items-center justify-between gap-6 border-2 border-[#78956A]"> <div class="flex items-center gap-4"> <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-3xl shrink-0 border border-white/20"> </div> <div class="space-y-1"> <h3 class="text-lg font-black tracking-tight text-white">Dokumen Legalitas Anda Sedang Ditinjau Admin</h3> <p class="text-xs text-[#F9F4EA] leading-relaxed max-w-2xl"> Berkas Anda telah berhasil dikirim ke Admin CareAtHome dan saat ini sedang dalam proses audit keaslian KTKI & Dinkes. Formulir dikunci sementara untuk mencegah perubahan selama proses review.
                            </p> </div> </div> <a href="{{ route('perawat.dashboard') }}" class="px-5 py-3 rounded-2xl bg-[#FF9ABC] hover:bg-[#D1EB8F] text-[#46571A] font-black text-xs shadow-md transition transform hover:-translate-y-0.5 whitespace-nowrap flex items-center gap-2 shrink-0"> <span>Lihat Dashboard Nakes</span> <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg> </a> </div> @endif

            <!-- Page Header Card --> <div class="bg-white rounded-2xl border border-[#78956A]/50 shadow-sm p-6 sm:p-8"> <div class="flex flex-col md:flex-row md:items-center justify-between gap-4"> <div> <span class="text-xs font-bold uppercase tracking-wider text-[#46571A] bg-[#D1EB8F]/45 px-3 py-1 rounded-full border border-[#78956A]/50"> Verifikasi Mitra &bull; Legalitas Profesi
                        </span> <h1 class="text-2xl sm:text-3xl font-extrabold text-[#46571A] tracking-tight mt-2"> Unggah Dokumen Legalitas Nakes
                        </h1> <p class="text-sm text-[#46571A]/85 mt-1"> Lengkapi data identitas (KTP) dan berkas STR & SIP aktif agar akun Anda dapat menerima order kunjungan
                        </p> </div> @if(($nurseProfile->verification_status ?? '') === 'verified')
                        <div class="shrink-0 flex items-center gap-2 bg-[#D1EB8F]/45 text-[#46571A] border border-[#78956A]/50 px-4 py-2 rounded-xl text-xs font-bold"> <span class="w-2.5 h-2.5 rounded-full bg-[#FF9ABC]"></span> Status: Terverifikasi (Siap Bekerja)
                        </div> @elseif(($nurseProfile->verification_status ?? '') === 'rejected')
                        <div class="shrink-0 flex items-center gap-2 bg-[#FF9ABC]/25 text-[#46571A] border border-[#FF9ABC] px-4 py-2 rounded-xl text-xs font-bold"> <span class="w-2.5 h-2.5 rounded-full bg-[#FF9ABC]"></span> Status: Ditolak (Perlu Perbaikan)
                        </div> @elseif(($nurseProfile->verification_status ?? '') === 'pending')
                        <div class="shrink-0 flex items-center gap-2 bg-[#F9F4EA] text-[#46571A] border border-[#78956A]/50 px-4 py-2 rounded-xl text-xs font-bold"> <span class="w-2.5 h-2.5 rounded-full bg-[#FF9ABC] animate-pulse"></span> Status: Menunggu Audit Admin
                        </div> @else
                        <div class="shrink-0 flex items-center gap-2 bg-[#F9F4EA] text-[#46571A] border border-[#78956A]/50 px-4 py-2 rounded-xl text-xs font-bold"> <span class="w-2.5 h-2.5 rounded-full bg-[#D1EB8F]"></span> Status: Draf (Belum Dikirim)
                        </div> @endif
                </div> <!-- Step-by-Step Progress Tracker (Steps 1 to 4) --> <div class="mt-8 pt-6 border-t border-[#78956A]/50"> <div class="grid grid-cols-2 md:grid-cols-4 gap-3"> <!-- Step 1 (Completed) --> <div class="p-3 rounded-xl bg-[#D1EB8F]/45 border border-[#78956A]/50 flex items-center gap-3"> <div class="w-7 h-7 rounded-full bg-[#FF9ABC] text-[#46571A] flex items-center justify-center text-xs font-bold shrink-0"> </div> <div> <p class="text-[10px] font-bold uppercase text-[#46571A]">Langkah 1</p> <p class="text-xs font-bold text-[#46571A]">Identitas Diri</p> </div> </div> <!-- Step 2 --> @if(($nurseProfile->verification_status ?? '') === 'verified')
                            <div class="p-3 rounded-xl bg-[#D1EB8F]/45 border border-[#78956A]/50 flex items-center gap-3"> <div class="w-7 h-7 rounded-full bg-[#FF9ABC] text-[#46571A] flex items-center justify-center text-xs font-bold shrink-0"> </div> <div> <p class="text-[10px] font-bold uppercase text-[#46571A]">Langkah 2</p> <p class="text-xs font-bold text-[#46571A]">STR &amp; SIP Nakes</p> </div> </div> @else
                            <div class="p-3 rounded-xl bg-[#FF9ABC] text-[#46571A] border-2 border-[#78956A] shadow-md shadow-[#78956A]/25 flex items-center gap-3 ring-2 ring-[#78956A]"> <div class="w-7 h-7 rounded-full bg-white text-[#46571A] flex items-center justify-center text-xs font-bold shrink-0"> 2
                                </div> <div> <p class="text-[10px] font-bold uppercase text-[#F9F4EA]">Langkah 2 (Aktif)</p> <p class="text-xs font-extrabold">STR &amp; SIP Nakes</p> </div> </div> @endif

                        <!-- Step 3 --> <div class="p-3 rounded-xl {{ ($nurseProfile->verification_status ?? '') === 'verified' ? 'bg-[#D1EB8F]/45 border border-[#78956A]/50' : 'bg-[#F9F4EA] border border-[#78956A]/50 opacity-60' }} flex items-center gap-3"> <div class="w-7 h-7 rounded-full {{ ($nurseProfile->verification_status ?? '') === 'verified' ? 'bg-[#FF9ABC] text-[#46571A]' : 'bg-[#78956A]/30 text-[#46571A]' }} flex items-center justify-center text-xs font-bold shrink-0"> {{ ($nurseProfile->verification_status ?? '') === 'verified' ? '' : '3' }}
                            </div> <div> <p class="text-[10px] font-bold uppercase {{ ($nurseProfile->verification_status ?? '') === 'verified' ? 'text-[#46571A]' : 'text-[#78956A]' }}">Langkah 3</p> <p class="text-xs font-semibold text-[#46571A]">Plafon Tarif</p> </div> </div> <!-- Step 4 --> <div class="p-3 rounded-xl {{ ($nurseProfile->verification_status ?? '') === 'verified' ? 'bg-[#FF9ABC] text-[#46571A] shadow-md' : (($nurseProfile->verification_status ?? '') === 'rejected' ? 'bg-[#FF9ABC]/25 border-2 border-[#FF9ABC]' : (($nurseProfile->verification_status ?? '') === 'pending' ? 'bg-[#F9F4EA] border border-[#78956A]' : 'bg-[#F9F4EA] border border-[#78956A]/50 opacity-60')) }} flex items-center gap-3"> <div class="w-7 h-7 rounded-full {{ ($nurseProfile->verification_status ?? '') === 'verified' ? 'bg-white text-[#46571A]' : (($nurseProfile->verification_status ?? '') === 'rejected' ? 'bg-[#FF9ABC] text-[#46571A]' : (($nurseProfile->verification_status ?? '') === 'pending' ? 'bg-[#FF9ABC] text-[#46571A] animate-pulse' : 'bg-[#78956A]/30 text-[#46571A]')) }} flex items-center justify-center text-xs font-bold shrink-0"> {{ ($nurseProfile->verification_status ?? '') === 'verified' ? '' : (($nurseProfile->verification_status ?? '') === 'rejected' ? '' : '4') }}
                            </div> <div> <p class="text-[10px] font-bold uppercase {{ ($nurseProfile->verification_status ?? '') === 'verified' ? 'text-[#F9F4EA]' : (($nurseProfile->verification_status ?? '') === 'rejected' ? 'text-[#46571A]' : (($nurseProfile->verification_status ?? '') === 'pending' ? 'text-[#46571A]' : 'text-[#78956A]')) }}">Langkah 4</p> <p class="text-xs font-semibold {{ ($nurseProfile->verification_status ?? '') === 'verified' ? 'text-white font-extrabold' : (($nurseProfile->verification_status ?? '') === 'rejected' ? 'text-[#46571A] font-extrabold' : (($nurseProfile->verification_status ?? '') === 'pending' ? 'text-[#46571A] font-bold' : 'text-[#46571A]')) }}"> {{ ($nurseProfile->verification_status ?? '') === 'verified' ? 'Terverifikasi' : (($nurseProfile->verification_status ?? '') === 'rejected' ? 'Ditolak (Revisi)' : 'Audit Admin') }}
                                </p> </div> </div> </div> </div> </div> <!-- Pesan Notifikasi Sukses / Error Validasi --> @if ($errors->any())
                <div class="p-5 rounded-2xl bg-[#FF9ABC]/25 border-2 border-[#FF9ABC] text-[#46571A] space-y-2 shadow-sm"> <div class="flex items-center gap-2 font-bold text-sm text-[#46571A]"> <span><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg></span> <span>Terdapat kesalahan validasi pada formulir:</span> </div> <ul class="list-disc list-inside text-xs text-[#46571A] space-y-1"> @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li> @endforeach
                    </ul> </div> @endif

            @if (session('submitted'))
                <div class="p-6 rounded-2xl bg-[#D1EB8F]/45 border-2 border-[#78956A] shadow-md text-[#46571A] flex flex-col sm:flex-row items-center gap-4"> <div class="w-12 h-12 rounded-full bg-[#FF9ABC] text-[#46571A] flex items-center justify-center shrink-0 text-xl font-bold shadow"> </div> <div class="text-center sm:text-left flex-1"> <h3 class="text-base font-extrabold text-[#46571A]">Dokumen Berhasil Dikirimkan ke Admin!</h3> <p class="text-xs text-[#46571A] mt-1">{{ session('submitted') }} Status verifikasi Anda sekarang: <strong>"Menunggu Review (Pending)"</strong>.</p> </div> <a href="{{ route('perawat.dashboard') }}" class="px-5 py-2.5 rounded-xl bg-[#FF9ABC] hover:bg-[#78956A] text-[#46571A] text-xs font-bold shrink-0 transition shadow-sm"> Ke Dashboard Nakes
                    </a> </div> @endif

            @if (session('draft_saved'))
                <div class="p-4 rounded-2xl bg-[#D1EB8F]/45 border border-[#78956A] shadow-xs text-[#46571A] flex items-center gap-3"> <span class="text-xl"></span> <p class="text-xs font-semibold text-[#46571A]">{{ session('draft_saved') }}</p> </div> @endif

            <!-- Form Data Legalitas --> <form
                id="nurse-verification-form"
                action="{{ route('perawat.verifikasi.submit') }}"
                method="POST"
                enctype="multipart/form-data"
                class="space-y-6"> @csrf

                <!-- Hidden inputs bound to Alpine.js to ensure latest values submitted --> <input type="hidden" id="hidden_nik" name="nik" :value="nik"> <input type="hidden" id="hidden_str_number" name="str_number" :value="str_number"> <input type="hidden" id="hidden_str_expiry" name="str_expiry" :value="str_expiry"> <input type="hidden" id="hidden_sip_number" name="sip_number" :value="sip_number"> <input type="hidden" id="hidden_sip_expiry" name="sip_expiry" :value="sip_expiry"> <input type="hidden" id="hidden_sip_institution" name="sip_institution" :value="sip_institution"> <input type="hidden" id="hidden_specialization" name="specialization" :value="specialization"> <!-- Hidden inputs for sample files --> <input type="hidden" id="sample_ktp" name="sample_ktp" value=""> <input type="hidden" id="sample_str" name="sample_str" value=""> <input type="hidden" id="sample_sip" name="sample_sip" value=""> <input type="hidden" id="sample_ijazah" name="sample_ijazah" value=""> <div class="bg-white rounded-2xl border border-[#78956A]/50 shadow-sm p-6 sm:p-8 space-y-6"> <div class="flex items-center justify-between border-b border-[#78956A]/50 pb-3"> <h2 class="text-lg font-extrabold text-[#46571A] flex items-center gap-2"> <span class="w-2.5 h-6 rounded-full bg-[#FF9ABC]"></span> Data Nomor Lisensi &amp; Izin Praktik
                        </h2> @if($isLocked)
                            <span class="text-xs font-bold {{ ($nurseProfile->verification_status ?? '') === 'verified' ? 'text-[#46571A] bg-[#D1EB8F]/45 border-[#78956A]/50' : 'text-[#46571A] bg-[#F9F4EA] border-[#78956A]/50' }} px-3 py-1 rounded-full border flex items-center gap-1.5"> <span class="w-2 h-2 rounded-full {{ ($nurseProfile->verification_status ?? '') === 'verified' ? 'bg-[#FF9ABC]' : 'bg-[#FF9ABC]' }}"></span> {{ ($nurseProfile->verification_status ?? '') === 'verified' ? 'Formulir Terverifikasi Resmi' : 'Formulir Dikunci (Sedang Ditinjau Admin)' }}
                            </span> @elseif(($nurseProfile->verification_status ?? '') === 'rejected')
                            <span class="text-xs font-bold text-[#46571A] bg-[#FF9ABC]/25 px-3 py-1 rounded-full border border-[#FF9ABC] flex items-center gap-1.5"> <span class="w-2 h-2 rounded-full bg-[#FF9ABC] animate-pulse"></span> Mode Revisi Aktif — Silakan Perbaiki Data &amp; Berkas
                            </span> @endif
                    </div> <div class="grid grid-cols-1 md:grid-cols-2 gap-5"> <!-- NIK KTP --> <div> <label class="block text-xs font-bold uppercase tracking-wider text-[#46571A] mb-1"> Nomor Induk Kependudukan (NIK KTP) <span class="text-[#46571A]">*</span> </label> <input 
                                type="text" 
                                id="input_nik"
                                maxlength="20"
                                placeholder="16 Digit Angka NIK KTP" 
                                x-model="nik"
                                {{ $isLocked ? 'disabled readonly' : '' }}
                                class="w-full px-4 py-2.5 rounded-xl border {{ $isLocked ? 'bg-[#F9F4EA] text-[#78956A] cursor-not-allowed' : 'bg-white text-[#46571A]' }} border-[#78956A] focus:outline-none focus:ring-2 focus:ring-[#78956A] focus:border-[#78956A] text-sm font-mono transition"
                                required /> <span class="text-[11px] text-[#78956A] mt-1 block">Wajib sesuai dengan identitas KTP asli</span> @error('nik') <span class="text-[#46571A] text-xs mt-1 block font-semibold">{{ $message }}</span> @enderror
                        </div> <!-- Nomor STR --> <div> <label class="block text-xs font-bold uppercase tracking-wider text-[#46571A] mb-1"> Nomor Surat Tanda Registrasi (STR) <span class="text-[#46571A]">*</span> </label> <input 
                                type="text" 
                                id="input_str_number"
                                placeholder="Contoh: 140152119-3891024" 
                                x-model="str_number"
                                {{ $isLocked ? 'disabled readonly' : '' }}
                                class="w-full px-4 py-2.5 rounded-xl border {{ $isLocked ? 'bg-[#F9F4EA] text-[#78956A] cursor-not-allowed' : 'bg-white text-[#46571A]' }} border-[#78956A] focus:outline-none focus:ring-2 focus:ring-[#78956A] focus:border-[#78956A] text-sm font-mono transition"
                                required /> <span class="text-[11px] text-[#78956A] mt-1 block">Diterbitkan oleh KTKI / Konsil Tenaga Kesehatan</span> @error('str_number') <span class="text-[#46571A] text-xs mt-1 block font-semibold">{{ $message }}</span> @enderror
                        </div> <!-- Masa Berlaku STR --> <div> <label class="block text-xs font-bold uppercase tracking-wider text-[#46571A] mb-1"> Tanggal Masa Berlaku STR <span class="text-[#46571A]">*</span> </label> <input 
                                type="date" 
                                id="input_str_expiry"
                                x-model="str_expiry"
                                {{ $isLocked ? 'disabled readonly' : '' }}
                                class="w-full px-4 py-2.5 rounded-xl border {{ $isLocked ? 'bg-[#F9F4EA] text-[#78956A] cursor-not-allowed' : 'bg-white text-[#46571A]' }} border-[#78956A] focus:outline-none focus:ring-2 focus:ring-[#78956A] focus:border-[#78956A] text-sm transition"
                                required /> @error('str_expiry') <span class="text-[#46571A] text-xs mt-1 block font-semibold">{{ $message }}</span> @enderror
                        </div> <!-- Nomor SIP --> <div> <label class="block text-xs font-bold uppercase tracking-wider text-[#46571A] mb-1"> Nomor Surat Izin Praktik (SIP) <span class="text-[#46571A]">*</span> </label> <input 
                                type="text" 
                                id="input_sip_number"
                                placeholder="Contoh: 446.1/1042/Dinkes-PP/2026" 
                                x-model="sip_number"
                                {{ $isLocked ? 'disabled readonly' : '' }}
                                class="w-full px-4 py-2.5 rounded-xl border {{ $isLocked ? 'bg-[#F9F4EA] text-[#78956A] cursor-not-allowed' : 'bg-white text-[#46571A]' }} border-[#78956A] focus:outline-none focus:ring-2 focus:ring-[#78956A] focus:border-[#78956A] text-sm font-mono transition"
                                required /> <span class="text-[11px] text-[#78956A] mt-1 block">Izin praktik dari Dinas Kesehatan setempat</span> @error('sip_number') <span class="text-[#46571A] text-xs mt-1 block font-semibold">{{ $message }}</span> @enderror
                        </div> <!-- Masa Berlaku SIP --> <div> <label class="block text-xs font-bold uppercase tracking-wider text-[#46571A] mb-1"> Tanggal Masa Berlaku SIP
                            </label> <input 
                                type="date" 
                                id="input_sip_expiry"
                                x-model="sip_expiry"
                                {{ $isLocked ? 'disabled readonly' : '' }}
                                class="w-full px-4 py-2.5 rounded-xl border {{ $isLocked ? 'bg-[#F9F4EA] text-[#78956A] cursor-not-allowed' : 'bg-white text-[#46571A]' }} border-[#78956A] focus:outline-none focus:ring-2 focus:ring-[#78956A] focus:border-[#78956A] text-sm transition" /> @error('sip_expiry') <span class="text-[#46571A] text-xs mt-1 block font-semibold">{{ $message }}</span> @enderror
                        </div> <!-- Institusi SIP --> <div> <label class="block text-xs font-bold uppercase tracking-wider text-[#46571A] mb-1"> Institusi / Fasilitas Tempat Praktik SIP
                            </label> <input 
                                type="text" 
                                id="input_sip_institution"
                                placeholder="Contoh: Praktik Mandiri, RSUD, Puskesmas" 
                                x-model="sip_institution"
                                {{ $isLocked ? 'disabled readonly' : '' }}
                                class="w-full px-4 py-2.5 rounded-xl border {{ $isLocked ? 'bg-[#F9F4EA] text-[#78956A] cursor-not-allowed' : 'bg-white text-[#46571A]' }} border-[#78956A] focus:outline-none focus:ring-2 focus:ring-[#78956A] focus:border-[#78956A] text-sm transition" /> @error('sip_institution') <span class="text-[#46571A] text-xs mt-1 block font-semibold">{{ $message }}</span> @enderror
                        </div> <!-- Spesialisasi Nakes --> <div class="md:col-span-2"> <label class="block text-xs font-bold uppercase tracking-wider text-[#46571A] mb-1"> Spesialisasi / Keahlian Nakes
                            </label> <input 
                                type="text" 
                                id="input_specialization"
                                placeholder="Contoh: Perawat Umum, Wound Care (Perawatan Luka), Geriatri (Lansia), Pasca Operasi" 
                                x-model="specialization"
                                {{ $isLocked ? 'disabled readonly' : '' }}
                                class="w-full px-4 py-2.5 rounded-xl border {{ $isLocked ? 'bg-[#F9F4EA] text-[#78956A] cursor-not-allowed' : 'bg-white text-[#46571A]' }} border-[#78956A] focus:outline-none focus:ring-2 focus:ring-[#78956A] focus:border-[#78956A] text-sm transition" /> @error('specialization') <span class="text-[#46571A] text-xs mt-1 block font-semibold">{{ $message }}</span> @enderror
                        </div> </div> </div> <!-- Unggah Berkas Bukti Fisik (Live PDF & Image Preview) --> <div class="bg-white rounded-2xl border border-[#78956A]/50 shadow-sm p-6 sm:p-8 space-y-6"> <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#78956A]/50 pb-3"> <div> <h2 class="text-lg font-extrabold text-[#46571A] flex items-center gap-2"> <span class="w-2.5 h-6 rounded-full bg-[#FF9ABC]"></span> Unggah Berkas Bukti Fisik
                            </h2> <p class="text-xs text-[#78956A] mt-0.5">Berkas yang diunggah akan diaudit dan divalidasi keabsahannya oleh tim Admin CareAtHome</p> </div> <span class="text-xs text-[#78956A] bg-[#F9F4EA] px-3 py-1 rounded-full border border-[#78956A]/50"> Format: PDF / JPEG / PNG (Maks 5 MB)
                        </span> </div> <div class="grid grid-cols-1 md:grid-cols-2 gap-6"> <!-- CARD 1: SWAFOTO KTP --> <div class="p-5 rounded-2xl border-2 transition-all relative"
                             :class="documents.ktp ? 'border-[#78956A] bg-[#D1EB8F]/45 shadow-md' : 'border-dashed border-[#78956A] hover:border-[#78956A] bg-[#F9F4EA]'"> <!-- State: Kosong / Belum Ada File --> <div x-show="!documents.ktp" class="text-center flex flex-col items-center justify-center py-4"> <div class="w-14 h-14 rounded-2xl bg-white shadow-sm border border-[#78956A]/50 flex items-center justify-center text-3xl mb-2 text-[#78956A]"> </div> <h3 class="font-extrabold text-sm text-[#46571A]">1. Swafoto Bersama KTP Asli</h3> <p class="text-[11px] text-[#78956A] mt-1 max-w-xs leading-relaxed">Pegang KTP sejajar dada. Wajah dan nomor NIK harus terbaca jelas.</p> @if(!$isLocked)
                                <div class="mt-4 flex items-center gap-2"> <label class="px-4 py-2 rounded-xl bg-[#FF9ABC] hover:bg-[#FF9ABC] text-[#46571A] text-xs font-bold shadow-sm cursor-pointer transition"> Pilih Berkas KTP
                                        <input type="file" id="file_ktp" name="ktp" class="hidden" accept="image/*,.pdf" @change="handleFileUpload($event, 'ktp')" /> </label> <button type="button" @click="loadSampleDoc('ktp', 'image')" class="px-3 py-2 rounded-xl bg-white border border-[#78956A]/50 hover:bg-[#F9F4EA] text-[#46571A]/85 text-xs font-semibold shadow-sm transition"> + Contoh File
                                    </button> </div> @else
                                <div class="mt-4 text-xs font-semibold text-[#78956A] bg-[#F9F4EA] px-3 py-1.5 rounded-lg border border-[#78956A]/50"> Berkas terkunci selama peninjauan
                                </div> @endif
                            </div> <!-- State: Preview Dokumen / Gambar Terunggah --> <div x-show="documents.ktp" x-cloak class="space-y-3"> <div class="flex items-center justify-between border-b border-[#78956A]/50 pb-2"> <span class="text-xs font-extrabold text-[#46571A] flex items-center gap-1.5"> <span class="w-2 h-2 rounded-full bg-[#FF9ABC]"></span> 1. Pratinjau Swafoto KTP
                                    </span> @if(!$isLocked)
                                    <div class="flex items-center gap-3"> <button type="button" @click="changeDoc('ktp')" class="text-xs font-bold text-[#46571A] hover:text-[#46571A] underline"> Ganti Berkas
                                        </button> <button type="button" @click="removeDoc('ktp')" class="text-xs font-bold text-[#46571A] hover:text-[#46571A] underline"> Hapus
                                        </button> </div> @endif
                                </div> <!-- Image Preview Card --> <div class="p-3 bg-white rounded-xl border border-[#78956A]/50 shadow-sm flex items-center gap-4"> <div class="w-16 h-16 rounded-lg bg-[#D1EB8F]/45 flex items-center justify-center text-3xl border border-[#78956A]/50 shrink-0"> </div> <div class="flex-1 overflow-hidden"> <p class="text-xs font-bold text-[#46571A] truncate" x-text="documents.ktp?.name"></p> <p class="text-[11px] text-[#78956A] mt-0.5" x-text="'Ukuran: ' + documents.ktp?.size"></p> <!-- BADGE STATUS: Neutral "Terlampir", bukan centang verified --> <span class="inline-flex items-center gap-1.5 mt-1.5 text-[10px] text-[#46571A] font-bold bg-[#F9F4EA] px-2.5 py-0.5 rounded-full border border-[#78956A]/50"> <span class="w-1.5 h-1.5 rounded-full bg-[#FF9ABC]"></span> Berkas KTP Terlampir &bull; Menunggu Audit Admin
                                        </span> </div> </div> </div> </div> <!-- CARD 2: STR ASLI (PDF PREVIEW CARD) --> <div class="p-5 rounded-2xl border-2 transition-all relative"
                             :class="documents.str ? 'border-[#78956A] bg-[#D1EB8F]/45 shadow-md' : 'border-dashed border-[#78956A] hover:border-[#78956A] bg-[#F9F4EA]'"> <!-- State: Kosong / Belum Ada File --> <div x-show="!documents.str" class="text-center flex flex-col items-center justify-center py-4"> <div class="w-14 h-14 rounded-2xl bg-white shadow-sm border border-[#78956A]/50 flex items-center justify-center text-3xl mb-2 text-[#78956A]"> </div> <h3 class="font-extrabold text-sm text-[#46571A]">2. Surat Tanda Registrasi (STR)</h3> <p class="text-[11px] text-[#78956A] mt-1 max-w-xs leading-relaxed">Scan dokumen STR aktif lengkap dengan QR code validasi KTKI.</p> @if(!$isLocked)
                                <div class="mt-4 flex items-center gap-2"> <label class="px-4 py-2 rounded-xl bg-[#FF9ABC] hover:bg-[#FF9ABC] text-[#46571A] text-xs font-bold shadow-sm cursor-pointer transition"> Pilih Berkas STR (PDF)
                                        <input type="file" id="file_str" name="str" class="hidden" accept=".pdf,image/*" @change="handleFileUpload($event, 'str')" /> </label> <button type="button" @click="loadSampleDoc('str', 'pdf')" class="px-3 py-2 rounded-xl bg-white border border-[#78956A]/50 hover:bg-[#F9F4EA] text-[#46571A]/85 text-xs font-semibold shadow-sm transition"> + Contoh PDF
                                    </button> </div> @else
                                <div class="mt-4 text-xs font-semibold text-[#78956A] bg-[#F9F4EA] px-3 py-1.5 rounded-lg border border-[#78956A]/50"> Berkas terkunci selama peninjauan
                                </div> @endif
                            </div> <!-- State: Pratinjau Halaman Pertama Dokumen PDF --> <div x-show="documents.str" x-cloak class="space-y-3"> <div class="flex items-center justify-between border-b border-[#78956A]/50 pb-2"> <span class="text-xs font-extrabold text-[#46571A] flex items-center gap-1.5"> <span class="px-1.5 py-0.5 rounded bg-[#FF9ABC] text-[#46571A] text-[10px] font-bold">PDF</span> 2. Pratinjau Halaman 1 Dokumen STR
                                    </span> @if(!$isLocked)
                                    <div class="flex items-center gap-3"> <button type="button" @click="changeDoc('str')" class="text-xs font-bold text-[#46571A] hover:text-[#46571A] underline"> Ganti Berkas
                                        </button> <button type="button" @click="removeDoc('str')" class="text-xs font-bold text-[#46571A] hover:text-[#46571A] underline"> Hapus
                                        </button> </div> @endif
                                </div> <!-- Simulated Official Document 1st-Page Preview --> <div class="p-4 bg-white rounded-xl border border-[#78956A]/50 shadow-sm space-y-2.5"> <div class="flex items-center justify-between border-b border-[#78956A]/50 pb-2"> <div class="flex items-center gap-2"> <span class="text-lg"></span> <div> <p class="text-[10px] font-bold uppercase text-[#46571A]">KONSIL TENAGA KESEHATAN INDONESIA</p> <p class="text-[9px] text-[#78956A]">SURAT TANDA REGISTRASI PERAWAT</p> </div> </div> <!-- BADGE STATUS: Neutral "Terlampir", bukan QR Code Valid  --> <span class="text-[10px] font-medium text-[#46571A] bg-[#F9F4EA] px-2 py-0.5 rounded border border-[#78956A]/50 flex items-center gap-1"> <span class="w-1.5 h-1.5 rounded-full bg-[#FF9ABC]"></span> Berkas STR Terlampir
                                        </span> </div> <div class="bg-[#F9F4EA] p-2.5 rounded-lg border border-[#78956A]/50 space-y-1 text-[11px] text-[#46571A]"> <p class="font-bold text-[#46571A] flex items-center justify-between"> <span x-text="documents.str?.name"></span> <span class="text-[10px] text-[#78956A] font-normal" x-text="documents.str?.size"></span> </p> <p class="text-[10px] text-[#78956A]">Dokumen STR diunggah &bull; Menunggu Pemeriksaan & Validasi Admin</p> </div> </div> </div> </div> <!-- CARD 3: SIP AKTIF (PDF PREVIEW CARD) --> <div class="p-5 rounded-2xl border-2 transition-all relative"
                             :class="documents.sip ? 'border-[#78956A] bg-[#D1EB8F]/45 shadow-md' : 'border-dashed border-[#78956A] hover:border-[#78956A] bg-[#F9F4EA]'"> <!-- State: Kosong / Belum Ada File --> <div x-show="!documents.sip" class="text-center flex flex-col items-center justify-center py-4"> <div class="w-14 h-14 rounded-2xl bg-white shadow-sm border border-[#78956A]/50 flex items-center justify-center text-3xl mb-2 text-[#78956A]"> </div> <h3 class="font-extrabold text-sm text-[#46571A]">3. Surat Izin Praktik (SIP) Aktif</h3> <p class="text-[11px] text-[#78956A] mt-1 max-w-xs leading-relaxed">Izin praktik yang diterbitkan oleh Dinas Kesehatan setempat.</p> @if(!$isLocked)
                                <div class="mt-4 flex items-center gap-2"> <label class="px-4 py-2 rounded-xl bg-[#FF9ABC] hover:bg-[#FF9ABC] text-[#46571A] text-xs font-bold shadow-sm cursor-pointer transition"> Pilih Berkas SIP (PDF)
                                        <input type="file" id="file_sip" name="sip" class="hidden" accept=".pdf,image/*" @change="handleFileUpload($event, 'sip')" /> </label> <button type="button" @click="loadSampleDoc('sip', 'pdf')" class="px-3 py-2 rounded-xl bg-white border border-[#78956A]/50 hover:bg-[#F9F4EA] text-[#46571A]/85 text-xs font-semibold shadow-sm transition"> + Contoh PDF
                                    </button> </div> @else
                                <div class="mt-4 text-xs font-semibold text-[#78956A] bg-[#F9F4EA] px-3 py-1.5 rounded-lg border border-[#78956A]/50"> Berkas terkunci selama peninjauan
                                </div> @endif
                            </div> <!-- State: Pratinjau Halaman Pertama Dokumen SIP --> <div x-show="documents.sip" x-cloak class="space-y-3"> <div class="flex items-center justify-between border-b border-[#78956A]/50 pb-2"> <span class="text-xs font-extrabold text-[#46571A] flex items-center gap-1.5"> <span class="px-1.5 py-0.5 rounded bg-[#FF9ABC] text-[#46571A] text-[10px] font-bold">PDF</span> 3. Pratinjau Halaman 1 Dokumen SIP
                                    </span> @if(!$isLocked)
                                    <div class="flex items-center gap-3"> <button type="button" @click="changeDoc('sip')" class="text-xs font-bold text-[#46571A] hover:text-[#46571A] underline"> Ganti Berkas
                                        </button> <button type="button" @click="removeDoc('sip')" class="text-xs font-bold text-[#46571A] hover:text-[#46571A] underline"> Hapus
                                        </button> </div> @endif
                                </div> <div class="p-4 bg-white rounded-xl border border-[#78956A]/50 shadow-sm space-y-2.5"> <div class="flex items-center justify-between border-b border-[#78956A]/50 pb-2"> <div class="flex items-center gap-2"> <span class="text-lg"></span> <div> <p class="text-[10px] font-bold uppercase text-[#46571A]">PEMERINTAH DAERAH DINAS KESEHATAN</p> <p class="text-[9px] text-[#78956A]">SURAT IZIN PRAKTIK (SIP) PERAWAT MANDIRI</p> </div> </div> <!-- BADGE STATUS: Neutral "Terlampir", bukan Legal Dinas  --> <span class="text-[10px] font-medium text-[#46571A] bg-[#F9F4EA] px-2 py-0.5 rounded border border-[#78956A]/50 flex items-center gap-1"> <span class="w-1.5 h-1.5 rounded-full bg-[#FF9ABC]"></span> Berkas SIP Terlampir
                                        </span> </div> <div class="bg-[#F9F4EA] p-2.5 rounded-lg border border-[#78956A]/50 space-y-1 text-[11px] text-[#46571A]"> <p class="font-bold text-[#46571A] flex items-center justify-between"> <span x-text="documents.sip?.name"></span> <span class="text-[10px] text-[#78956A] font-normal" x-text="documents.sip?.size"></span> </p> <p class="text-[10px] text-[#78956A]">Dokumen SIP diunggah &bull; Menunggu Validasi Izin Admin</p> </div> </div> </div> </div> <!-- CARD 4: IJAZAH KEPERAWATAN --> <div class="p-5 rounded-2xl border-2 transition-all relative"
                             :class="documents.ijazah ? 'border-[#78956A] bg-[#D1EB8F]/45 shadow-md' : 'border-dashed border-[#78956A] hover:border-[#78956A] bg-[#F9F4EA]'"> <!-- State: Kosong / Belum Ada File --> <div x-show="!documents.ijazah" class="text-center flex flex-col items-center justify-center py-4"> <div class="w-14 h-14 rounded-2xl bg-white shadow-sm border border-[#78956A]/50 flex items-center justify-center text-3xl mb-2 text-[#78956A]"> </div> <h3 class="font-extrabold text-sm text-[#46571A]">4. Ijazah Keperawatan (D3 / S1 / Ners)</h3> <p class="text-[11px] text-[#78956A] mt-1 max-w-xs leading-relaxed">Bukti kelulusan institusi pendidikan keperawatan terakreditasi.</p> @if(!$isLocked)
                                <div class="mt-4 flex items-center gap-2"> <label class="px-4 py-2 rounded-xl bg-[#FF9ABC] hover:bg-[#FF9ABC] text-[#46571A] text-xs font-bold shadow-sm cursor-pointer transition"> Pilih Berkas Ijazah
                                        <input type="file" id="file_ijazah" name="ijazah" class="hidden" accept=".pdf,image/*" @change="handleFileUpload($event, 'ijazah')" /> </label> <button type="button" @click="loadSampleDoc('ijazah', 'pdf')" class="px-3 py-2 rounded-xl bg-white border border-[#78956A]/50 hover:bg-[#F9F4EA] text-[#46571A]/85 text-xs font-semibold shadow-sm transition"> + Contoh PDF
                                    </button> </div> @else
                                <div class="mt-4 text-xs font-semibold text-[#78956A] bg-[#F9F4EA] px-3 py-1.5 rounded-lg border border-[#78956A]/50"> Berkas terkunci selama peninjauan
                                </div> @endif
                            </div> <!-- State: Pratinjau Dokumen Ijazah --> <div x-show="documents.ijazah" x-cloak class="space-y-3"> <div class="flex items-center justify-between border-b border-[#78956A]/50 pb-2"> <span class="text-xs font-extrabold text-[#46571A] flex items-center gap-1.5"> <span class="px-1.5 py-0.5 rounded bg-[#FF9ABC] text-[#46571A] text-[10px] font-bold">PDF</span> 4. Pratinjau Ijazah Pendidikan
                                    </span> @if(!$isLocked)
                                    <div class="flex items-center gap-3"> <button type="button" @click="changeDoc('ijazah')" class="text-xs font-bold text-[#46571A] hover:text-[#46571A] underline"> Ganti Berkas
                                        </button> <button type="button" @click="removeDoc('ijazah')" class="text-xs font-bold text-[#46571A] hover:text-[#46571A] underline"> Hapus
                                        </button> </div> @endif
                                </div> <div class="p-4 bg-white rounded-xl border border-[#78956A]/50 shadow-sm space-y-2.5"> <div class="flex items-center justify-between border-b border-[#78956A]/50 pb-2"> <div class="flex items-center gap-2"> <span class="text-lg"></span> <div> <p class="text-[10px] font-bold uppercase text-[#46571A]">IJAZAH PENDIDIKAN KEPERAWATAN</p> <p class="text-[9px] text-[#78956A]">FAKULTAS KEPERAWATAN</p> </div> </div> <!-- BADGE STATUS: Neutral "Terlampir", bukan Terakreditasi  --> <span class="text-[10px] font-medium text-[#46571A] bg-[#F9F4EA] px-2 py-0.5 rounded border border-[#78956A]/50 flex items-center gap-1"> <span class="w-1.5 h-1.5 rounded-full bg-[#FF9ABC]"></span> Berkas Ijazah Terlampir
                                        </span> </div> <div class="bg-[#F9F4EA] p-2.5 rounded-lg border border-[#78956A]/50 space-y-1 text-[11px] text-[#46571A]"> <p class="font-bold text-[#46571A] flex items-center justify-between"> <span x-text="documents.ijazah?.name"></span> <span class="text-[10px] text-[#78956A] font-normal" x-text="documents.ijazah?.size"></span> </p> <p class="text-[10px] text-[#78956A]">Dokumen Ijazah diunggah &bull; Menunggu Pemeriksaan Admin</p> </div> </div> </div> </div> </div> </div> <!-- Amber Warning Box --> <div class="p-5 rounded-2xl bg-[#F9F4EA] border-2 border-[#78956A] text-[#46571A] flex items-start gap-4 shadow-sm"> <div class="w-10 h-10 rounded-xl bg-[#FF9ABC] text-[#46571A] flex items-center justify-center shrink-0 text-xl font-bold"> </div> <div class="text-xs sm:text-sm space-y-1"> <h4 class="font-extrabold text-[#46571A]">Perhatian Prosedur Audit Admin:</h4> <p class="leading-relaxed text-[#46571A]"> Dokumen akan divalidasi ke portal resmi Konsil Tenaga Kesehatan Indonesia (KTKI) oleh Admin CareAtHome. Akun perawat akan diaktifkan setelah dokumen terverifikasi sah. <em>Mohon tunggu konfirmasi melalui email dan notifikasi sistem dalam 1x24 jam kerja.</em> </p> </div> </div> <!-- Action Buttons: Simpan Draf vs Kirim Dokumen --> <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4"> <!-- Batal / Kembali ke Landing Page --> <a href="/" class="w-full sm:w-auto px-6 py-3 rounded-xl border border-[#78956A] text-[#46571A] hover:bg-[#F9F4EA] font-bold text-xs text-center transition flex items-center justify-center gap-1.5"> <svg class="w-4 h-4 text-[#78956A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"> <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path> </svg> <span>Kembali ke Beranda</span> </a> @if($isLocked)
                        <div class="w-full sm:w-auto flex flex-col sm:flex-row items-center gap-3"> @if(($nurseProfile->verification_status ?? '') === 'verified')
                                <div class="px-5 py-3 rounded-xl bg-[#D1EB8F] text-[#46571A] border border-[#78956A] font-bold text-xs flex items-center gap-2"> <span class="w-2 h-2 rounded-full bg-[#FF9ABC]"></span> <span> Akun Anda telah Terverifikasi Resmi. Formulir dikunci.</span> </div> <a href="{{ route('perawat.dashboard') }}" class="px-6 py-3 rounded-xl bg-[#FF9ABC] hover:bg-[#FF9ABC] text-[#46571A] font-extrabold text-xs shadow-sm transition"> Buka Dashboard Kerja &rarr;
                                </a> @else
                                <div class="px-5 py-3 rounded-xl bg-[#D1EB8F]/70 text-[#46571A] border border-[#78956A] font-bold text-xs flex items-center gap-2"> <span class="w-2 h-2 rounded-full bg-[#FF9ABC] animate-pulse"></span> <span> Dokumen Sedang Ditinjau Admin (Menunggu Review). Formulir dikunci.</span> </div> <a href="{{ route('perawat.dashboard') }}" class="px-5 py-3 rounded-xl bg-[#FF9ABC] hover:bg-[#FF9ABC] text-[#46571A] font-extrabold text-xs shadow-sm transition"> Ke Dashboard &rarr;
                                </a> @endif
                        </div> @else
                        <div class="w-full sm:w-auto flex flex-col sm:flex-row items-center gap-3"> <!-- Simpan Draf (Lanjut Nanti) --> <button 
                                type="button" 
                                @click="handleSaveDraft"
                                :disabled="isSavingDraft"
                                class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-white border border-[#78956A] hover:border-[#78956A] hover:bg-[#F9F4EA] text-[#46571A] font-bold text-xs transition flex items-center justify-center gap-1.5 shadow-sm disabled:opacity-60 disabled:cursor-not-allowed"> <span x-show="!isSavingDraft"> Simpan Sebagai Draf</span> <span x-show="isSavingDraft" x-cloak class="flex items-center gap-1.5"> <svg class="animate-spin w-4 h-4 text-[#78956A]" fill="none" viewBox="0 0 24 24"> <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle> <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path> </svg> Menyimpan...
                                </span> </button> <!-- Submit to Admin — @click calls handleSubmit yang submit form ke server --> <button 
                                type="button"
                                @click="handleSubmit"
                                class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-[#78956A] hover:bg-[#D1EB8F] text-[#46571A] font-extrabold text-sm shadow-lg shadow-[#78956A]/25 hover:shadow-[#78956A]/25 transform hover:-translate-y-0.5 active:translate-y-0 transition flex items-center justify-center gap-2 cursor-pointer"> <span>{{ ($nurseProfile->verification_status ?? '') === 'rejected' ? ' Kirim Ulang Hasil Revisi ke Admin' : 'Simpan dan Kirim Dokumen' }}</span> <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"> <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path> </svg> </button> </div> @endif
                </div> </form> </main> </div> <!-- Footer --> <footer class="bg-white border-t border-[#78956A]/50 py-6 mt-12 text-center text-xs text-[#78956A]"> &copy; 2026 CareAtHome Medical. Sistem Informasi Layanan Homecare Terintegrasi.
    </footer> </body>
</html>
