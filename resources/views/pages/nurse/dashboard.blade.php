<x-app-layout>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <div class="py-8" x-data="nurseDashboardApp({{ $profile->is_online ? 'true' : 'false' }}, '{{ $profile->verification_status }}', {{ $profile->latitude ?? 'null' }}, {{ $profile->longitude ?? 'null' }})"> <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6"> <!-- Banner Status Legalitas & Notifikasi Verifikasi (Approved / Rejected / Pending) --> @php
                $latestAuditNotif = auth()->user() ? auth()->user()->notifications()->whereIn('data->type', ['document_approved', 'document_rejected'])->first() : null;
            @endphp

            @if ($profile->verification_status === 'rejected')
                <div class="p-5 sm:p-6 rounded-3xl bg-[#78956A] text-[#F9F4EA] shadow-xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-2 border-[#FF9ABC]"> <div class="flex items-start gap-3.5"> <span class="text-3xl shrink-0"><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg></span> <div class="space-y-1"> <span class="inline-block px-2.5 py-0.5 rounded-full bg-[#FF9ABC] border border-[#FF9ABC] text-[#F9F4EA] text-[10px] font-black uppercase tracking-wider"> PEMBERITAHUAN PENTING: BERKAS LEGALITAS DITOLAK ADMIN
                            </span> <h4 class="font-extrabold text-base text-white">Pengajuan Legalitas STR &amp; SIP Anda Ditolak — Silakan Revisi</h4> <p class="text-xs text-[#F9F4EA] leading-relaxed"> <strong>Alasan Penolakan Admin:</strong> &ldquo;{{ $profile->rejection_reason ?? ($latestAuditNotif->data['rejection_reason'] ?? 'Berkas dokumen belum sesuai standar atau kurang jelas terbaca.') }}&rdquo;
                            </p> </div> </div> <div class="flex items-center gap-2 shrink-0"> <a href="{{ route('perawat.verifikasi') }}" class="px-5 py-3 rounded-2xl bg-white text-[#46571A] font-black text-xs shadow-md hover:bg-[#FF9ABC]/25 transition whitespace-nowrap flex items-center gap-1.5"> <span> Revisi &amp; Unggah Ulang Dokumen &rarr;</span> </a> </div> </div> @elseif ($profile->verification_status === 'pending')
                <div class="p-5 rounded-3xl bg-[#78956A] text-[#F9F4EA] shadow-lg flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-2 border-[#78956A]"> <div class="flex items-center gap-3.5"> <span class="text-3xl shrink-0"><svg class="w-4 h-4 inline-block shrink-0 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></span> <div class="space-y-0.5"> <h4 class="font-extrabold text-sm text-white">Berkas Legalitas Anda Sedang Diaudit oleh Administrator</h4> <p class="text-xs text-[#F9F4EA]">Tim Admin sedang memverifikasi keabsahan STR &amp; SIP Anda. Anda akan menerima notifikasi begitu disetujui.</p> </div> </div> <a href="{{ route('perawat.verifikasi') }}" class="px-4 py-2.5 rounded-xl bg-[#D1EB8F] text-[#46571A] font-black text-xs shadow-xs hover:bg-[#FF9ABC] transition whitespace-nowrap shrink-0"> Lihat Status Berkas &rarr;
                    </a> </div> @elseif ($profile->verification_status === 'verified' && $latestAuditNotif && is_null($latestAuditNotif->read_at) && ($latestAuditNotif->data['type'] ?? '') === 'document_approved')
                <div id="notif-banner-{{ $latestAuditNotif->id }}" class="audit-notif-banner p-5 sm:p-6 rounded-3xl bg-[#78956A] text-[#F9F4EA] shadow-xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-2 border-[#78956A]"> <div class="flex items-center gap-3.5"> <span class="text-3xl shrink-0"><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></span> <div class="space-y-1"> <span class="inline-block px-2.5 py-0.5 rounded-full bg-[#D1EB8F] border border-[#78956A] text-[#F9F4EA] text-[10px] font-black uppercase tracking-wider"> LEGALITAS DISETUJUI &bull; AKUN RESMI AKTIF
                            </span> <h4 class="font-extrabold text-base text-white">Selamat! Berkas Legalitas STR &amp; SIP Anda Telah Diverifikasi Admin</h4> <p class="text-xs text-[#F9F4EA]">Seluruh fitur Mitra Perawat telah aktif! Anda kini dapat mengatur Jadwal Harian (WIB), menerima pesanan homecare di Feed Order, mengisi E-Report Klinis, dan menarik saldo Payout.</p> </div> </div> <button type="button" 
                        onclick="dismissAuditBanner(event, '{{ route('perawat.notifications.read', $latestAuditNotif->id) }}', '{{ $latestAuditNotif->id }}')"
                        class="px-4 py-2.5 rounded-xl bg-[#FF9ABC] text-[#46571A] font-black text-xs shadow-xs hover:bg-[#D1EB8F] transition whitespace-nowrap cursor-pointer shrink-0"> Mengerti &amp; Mulai Bekerja
                    </button> </div> @endif

            <!-- Flash Status Messages --> @if (session('status'))
                <div class="p-4 rounded-2xl bg-[#D1EB8F]/45 border border-[#78956A]/50 text-xs font-semibold text-[#46571A] flex items-center gap-2"> <span><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg></span> <span>{{ session('status') }}</span> </div> @endif

            @if (session('error'))
                <div class="p-4 rounded-2xl bg-[#FF9ABC]/25 border border-[#FF9ABC] text-xs font-semibold text-[#46571A] flex items-center gap-2"> <span><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg></span> <span>{{ session('error') }}</span> </div> @endif

            @if ($errors->any())
                <div class="p-4 rounded-2xl bg-[#FF9ABC]/25 border border-[#FF9ABC] text-xs font-semibold text-[#46571A] space-y-1"> <div class="flex items-center gap-2 font-bold"> <span><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg></span> <span>Mohon periksa kembali isian formulir Anda:</span> </div> <ul class="list-disc list-inside pl-4 space-y-0.5"> @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li> @endforeach
                    </ul> </div> @endif

            <!-- Toast Alert Frontend --> <div x-show="toastMessage" x-cloak
                class="p-4 rounded-2xl flex items-center justify-between shadow-lg text-xs font-semibold transition"
                :class="toastType === 'success' ? 'bg-[#FF9ABC] text-[#46571A]' : 'bg-[#FF9ABC] text-[#46571A]'"> <div class="flex items-center gap-2"> <span x-show="toastType === 'success'"><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></span><span x-show="toastType !== 'success'"><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg></span> <span x-text="toastMessage"></span> </div> <button @click="toastMessage = ''" class="text-white/80 hover:text-white px-2 py-0.5">Tutup</button> </div> <!-- 1. HERO BANNER & JADWAL OPERASIONAL HARIAN (WIB) --> @php
                $nowWib = now('Asia/Jakarta');
                $currentTimeHi = $nowWib->format('H:i');
                $todaySlots = (isset($todaySchedule) && $todaySchedule && $todaySchedule->is_active)
                    ? $todaySchedule->getEffectiveTimeSlots()
                    : [];
                $nowAvailability = \App\Models\NurseSchedule::checkAvailability($user->id, 'now');
                $isOperatingNow = $nowAvailability['available'] ?? false;
            @endphp
            <div class="bg-[#78956A] rounded-3xl p-6 sm:p-8 text-[#F9F4EA] shadow-xl relative overflow-hidden border border-[#78956A]"> <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-[#FF9ABC]/15 rounded-full blur-3xl pointer-events-none"></div> <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10"> <div class="space-y-3 max-w-xl"> <div class="flex flex-wrap items-center gap-2"> <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full {{ $isOperatingNow ? 'bg-[#FF9ABC]/25 border-[#FF9ABC]/40 text-[#F9F4EA]' : 'bg-[#FF9ABC] border-[#78956A] text-[#F9F4EA]' }} border text-xs font-bold"> <span class="w-2 h-2 rounded-full {{ $isOperatingNow ? 'bg-[#FF9ABC] animate-pulse' : 'bg-[#D1EB8F]' }}"></span> <span>{{ $isOperatingNow ? 'SIAP MENERIMA PESANAN SAAT INI (WIB)' : 'SEDANG TERJADWAL / DI LUAR JAM KERJA' }}</span> </span> <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 border border-white/15 text-[#F9F4EA] text-xs font-mono font-bold"> <span><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></span> <span x-text="currentWibClock">{{ $nowWib->format('H:i') }} WIB</span> </span> </div> <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight"> Halo, {{ $user->name }}
                        </h1> <p class="text-xs sm:text-sm text-[#F9F4EA] leading-relaxed"> Ketersediaan Anda diatur berdasarkan <strong>Daftar Jam Kerja Harian (WIB)</strong> yang Anda tambahkan per hari. Pasien hanya dapat memesan pada jam kerja aktif yang belum dipesan pasien lain.
                        </p> <div class="text-[11px] text-[#F9F4EA] flex flex-wrap items-center gap-3 pt-1"> <span> Titik Koordinat Rute OSRM: <strong x-text="lat && lng ? (lat.toFixed(4) + ', ' + lng.toFixed(4)) : 'Surabaya Default'"></strong></span> <span>•</span> <span> Sinkronisasi GPS: <strong x-text="lastUpdatedText || '{{ $profile->location_updated_at ? $profile->location_updated_at->diffForHumans() : 'Siap' }}'"></strong></span> </div> <div class="pt-2 flex flex-wrap items-center gap-2.5"> <a href="{{ route('perawat.feed') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#FF9ABC] hover:bg-[#D1EB8F] text-[#46571A] font-black text-xs shadow-md shadow-[#78956A]/25 transition transform active:scale-95"> <span> Buka Feed Order Masuk &rarr;</span> </a> <button type="button" @click="scheduleModalOpen = true" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-white text-[#78956A] hover:bg-[#D1EB8F]/45 font-extrabold text-xs shadow-sm transition cursor-pointer"> <span>＋ Atur Jam Kerja Harian (WIB)</span> </button> <button type="button" @click="saveManualLocation()" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-[#FF9ABC] hover:bg-[#FF9ABC] text-[#F9F4EA] font-bold text-xs border border-[#78956A] transition cursor-pointer"> <span> Perbarui Titik GPS Saya</span> </button> <a href="{{ route('perawat.finansial') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs border border-white/20 transition"> <span> Riwayat Finansial</span> </a> </div> </div> <!-- KARTU RINGKASAN JADWAL HARI INI --> <div class="bg-white/10 backdrop-blur-md p-5 rounded-2xl border border-white/15 text-left min-w-[270px] max-w-xs space-y-3"> <div class="flex items-center justify-between border-b border-white/10 pb-2"> <span class="text-[11px] uppercase font-extrabold text-[#F9F4EA] tracking-wider">Jam Kerja Hari Ini</span> <span class="px-2 py-0.5 rounded-lg bg-[#FF9ABC]/20 text-[#F9F4EA] text-[10px] font-bold"> {{ $todaySchedule->day_name ?? 'Hari Ini' }}
                            </span> </div> @if (!empty($todaySlots))
                            <div class="space-y-2"> <span class="text-xs text-[#F9F4EA] block">Slot Jam Praktik Aktif (WIB):</span> <div class="flex flex-wrap gap-1.5"> @foreach ($todaySlots as $slot)
                                        <span class="px-2 py-1 rounded-lg bg-white/15 border border-white/20 text-white font-mono text-xs font-extrabold"> {{ $slot }} WIB
                                        </span> @endforeach
                                </div> <span class="inline-flex items-center gap-1 text-[11px] text-[#D1EB8F] font-semibold"> {{ count($todaySlots) }} sesi jam kerja aktif hari ini
                                </span> </div> @else
                            <div class="space-y-1"> <span class="text-base font-extrabold text-[#D1EB8F] block">Libur / Tidak Aktif</span> <p class="text-[11px] text-[#F9F4EA]">Anda sedang mengatur hari ini sebagai libur. Pasien tetap dapat memesan untuk hari aktif lainnya.</p> </div> @endif

                        <button type="button" @click="scheduleModalOpen = true"
                            class="w-full py-2 px-3 rounded-xl bg-[#FF9ABC] hover:bg-[#D1EB8F] text-[#46571A] font-extrabold text-xs transition flex items-center justify-center gap-1.5 cursor-pointer shadow-xs"> <span>＋ Tambah / Edit Jam Kerja</span> </button> </div> </div> </div> <!-- 1.5 TIGA KARTU RINGKASAN EKSEKUTIF FINANSIAL PERAWAT --> <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5"> <!-- Card 1: Total Saldo Utama --> <div class="bg-[#78956A] text-[#F9F4EA] rounded-3xl p-6 shadow-md shadow-[#78956A]/25 relative overflow-hidden flex flex-col justify-between space-y-4"> <div class="absolute -right-4 -bottom-4 w-32 h-32 rounded-full bg-white/10 blur-xl pointer-events-none"></div> <div class="flex items-center justify-between"> <span class="text-xs font-bold text-[#F9F4EA] uppercase tracking-wider flex items-center gap-1.5"> <span class="p-1.5 rounded-xl bg-white/20 text-sm"><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" /></svg></span> <span>Total Saldo Utama</span> </span> <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-white/20 text-white border border-white/20"> Siap Dicairkan
                        </span> </div> <div class="space-y-1"> <span class="text-3xl font-black tracking-tight block"> Rp {{ number_format($totalBalance, 0, ',', '.') }}
                        </span> <p class="text-xs text-[#F9F4EA]"> Pendapatan bersih 85% tindakan + 100% ongkos transport pasien.
                        </p> </div> <div class="pt-2"> <button type="button" @click="openPayoutModal()"
                            class="w-full py-2.5 px-4 rounded-xl bg-white text-[#46571A] hover:bg-[#D1EB8F]/45 font-extrabold text-xs shadow-sm transition flex items-center justify-center gap-1.5 cursor-pointer"> <span> Tarik Saldo (Payout)</span> </button> </div> </div> <!-- Card 2: Saldo Pending / Hold --> <div class="bg-white rounded-3xl border border-[#78956A]/50 p-6 shadow-sm flex flex-col justify-between space-y-4"> <div class="flex items-center justify-between"> <span class="text-xs font-bold text-[#78956A] uppercase tracking-wider flex items-center gap-1.5"> <span class="p-1.5 rounded-xl bg-[#F9F4EA] text-[#78956A] text-sm border border-[#78956A]/50"><svg class="w-4 h-4 inline-block shrink-0 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></span> <span>Saldo Pending / Payout Hold</span> </span> <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-[#F9F4EA] text-[#46571A] border border-[#78956A]/50"> Menunggu Otorisasi
                        </span> </div> <div class="space-y-1"> <span class="text-3xl font-black text-[#46571A] tracking-tight block"> Rp {{ number_format($pendingPayout, 0, ',', '.') }}
                        </span> <p class="text-xs text-[#78956A]"> Permintaan penarikan yang sedang diverifikasi dan diproses oleh admin.
                        </p> </div> <div class="pt-2 text-xs font-semibold text-[#46571A] flex items-center gap-1"> <span><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg></span> <span>Estimasi transfer 1x24 jam kerja</span> </div> </div> <!-- Card 3: Total Penarikan / Akumulasi Pendapatan --> <div class="bg-white rounded-3xl border border-[#78956A]/50 p-6 shadow-sm flex flex-col justify-between space-y-4"> <div class="flex items-center justify-between"> <span class="text-xs font-bold text-[#78956A] uppercase tracking-wider flex items-center gap-1.5"> <span class="p-1.5 rounded-xl bg-[#D1EB8F]/45 text-[#46571A] text-sm border border-[#78956A]/50"><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" /></svg></span> <span>Total Penarikan Dana</span> </span> <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-[#F9F4EA] text-[#46571A]"> Riwayat Transfer
                        </span> </div> <div class="space-y-1"> <span class="text-3xl font-black text-[#46571A] tracking-tight block"> Rp {{ number_format($totalWithdrawn, 0, ',', '.') }}
                        </span> <p class="text-xs text-[#78956A]"> Dana yang berhasil dicairkan ke rekening bank perawat.
                        </p> </div> <div class="pt-2 border-t border-[#78956A]/50 text-xs font-bold text-[#46571A]/85 flex items-center justify-between"> <span>Total Akumulasi Pendapatan:</span> <span class="text-[#46571A] font-extrabold">Rp {{ number_format($totalEarnings, 0, ',', '.') }}</span> </div> </div> </div> <!-- 1.6 SECTION ATUR JADWAL OPERASIONAL HARIAN (SENIN - MINGGU WIB) --> <div class="bg-white rounded-3xl border border-[#78956A]/50 p-6 sm:p-7 shadow-sm space-y-5"> <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#78956A]/50 pb-4"> <div class="space-y-1"> <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#D1EB8F]/45 text-[#78956A] text-[11px] font-bold border border-[#78956A]/50"> <span><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg></span> <span>Manajemen Jam Kerja Harian Per Hari (Zona Waktu WIB)</span> </div> <h3 class="text-lg font-bold text-[#46571A] tracking-tight">Daftar Jam Kerja Perawat per Hari (Senin – Minggu)</h3> <p class="text-xs text-[#78956A]"> Tambahkan jam berapa saja Anda bisa bekerja pada masing-masing hari dengan tombol <strong>(+)</strong>. Pasien yang memilih <strong>Pesan Sekarang</strong> maupun <strong>Pesan Untuk Nanti</strong> hanya bisa memesan pada jam kerja yang Anda tambahkan dan belum terisi pesanan lain.
                        </p> </div> <button type="button" @click="scheduleModalOpen = true"
                        class="px-4 py-2.5 rounded-xl bg-[#78956A] hover:bg-[#78956A] text-[#F9F4EA] font-extrabold text-xs shadow-sm transition flex items-center gap-1.5 cursor-pointer shrink-0"> <span>＋ Tambah / Atur Jam Kerja</span> </button> </div> @if (isset($schedules) && $schedules->isNotEmpty())
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 gap-3"> @foreach ($schedules as $sch)
                            @php
                                $isTodayCard = (int) now('Asia/Jakarta')->dayOfWeekIso === (int) $sch->day_of_week;
                                $daySlots = $sch->getEffectiveTimeSlots();
                            @endphp
                            <div class="p-3.5 rounded-2xl border {{ $isTodayCard ? 'border-[#FF9ABC] bg-[#D1EB8F]/45 ring-2 ring-[#FF9ABC]/20' : ($sch->is_active && !empty($daySlots) ? 'border-[#78956A]/50 bg-white' : 'border-[#78956A]/50 bg-[#F9F4EA] opacity-75') }} flex flex-col justify-between space-y-3"> <div class="space-y-2"> <div class="flex items-center justify-between"> <span class="text-xs font-extrabold text-[#46571A]">{{ $sch->day_name }}</span> @if ($isTodayCard)
                                            <span class="px-1.5 py-0.5 rounded bg-[#78956A] text-[#F9F4EA] text-[9px] font-bold">Hari Ini</span> @endif
                                    </div> @if ($sch->is_active && !empty($daySlots))
                                        <div> <span class="inline-block px-2 py-0.5 rounded-full bg-[#D1EB8F] text-[#78956A] text-[10px] font-bold mb-2"> {{ count($daySlots) }} Jam Kerja
                                            </span> <div class="flex flex-wrap gap-1"> @foreach ($daySlots as $slot)
                                                    <span class="px-2 py-0.5 rounded-lg bg-[#F9F4EA] border border-[#78956A]/50 text-[#46571A] text-[11px] font-mono font-bold"> {{ $slot }}
                                                    </span> @endforeach
                                            </div> </div> @else
                                        <div> <span class="inline-block px-2 py-0.5 rounded-full bg-[#FF9ABC]/40 text-[#46571A] text-[10px] font-bold mb-1"> Libur / Off
                                            </span> <p class="text-[11px] text-[#78956A]">Tidak ada jam kerja</p> </div> @endif
                                </div> <button type="button" @click="scheduleModalOpen = true"
                                    class="w-full py-1.5 px-2 rounded-xl border border-[#78956A]/50 bg-[#D1EB8F]/45 hover:bg-[#D1EB8F] text-[#78956A] text-[11px] font-extrabold transition flex items-center justify-center gap-1 cursor-pointer"> <span>＋</span> <span>Atur Jam</span> </button> </div> @endforeach
                    </div> @endif
            </div> <!-- 1.8 SECTION PENUGASAN MEDIS & E-REPORT KLINIS --> <div class="bg-white rounded-3xl border border-[#78956A]/50 p-6 sm:p-7 shadow-sm space-y-5"> <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#78956A]/50 pb-4"> <div class="space-y-1"> <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#D1EB8F]/45 text-[#78956A] text-[11px] font-bold border border-[#78956A]/50"> <span><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg></span> <span>Asuhan Keperawatan Digital &amp; Validasi Waktu Kerja (WIB)</span> </div> <h3 class="text-lg font-bold text-[#46571A] tracking-tight">Penugasan Medis &amp; E-Report Klinis Pasien</h3> <p class="text-xs text-[#78956A]"> Tombol aksi perjalanan dan perawatan aktif maksimal <strong>30 menit sebelum jadwal perawatan (WIB)</strong>. Wajib melengkapi E-Report untuk penyelesaian pesanan.
                        </p> </div> <div class="flex items-center gap-2"> <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-[#F9F4EA] text-[#46571A] border border-[#78956A]/50 flex items-center gap-1.5"> <span><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg></span> <span>{{ $ordersNeedingReport->count() }} Penugasan Aktif</span> </span> </div> </div> <!-- List Pesanan Menunggu E-Report --> @if ($ordersNeedingReport->isNotEmpty())
                    <div class="space-y-3"> <h4 class="text-xs uppercase font-extrabold text-[#78956A] tracking-wider">Tugas Kunjungan yang Perlu Diselesaikan:</h4> <div class="grid grid-cols-1 md:grid-cols-2 gap-4"> @foreach ($ordersNeedingReport as $ord)
                                @php
                                    $actionCheck = $ord->canNursePerformAction();
                                    $canAct = $actionCheck['allowed'] ?? true;
                                @endphp
                                <div class="p-5 rounded-2xl border-2 {{ $canAct ? 'border-[#78956A]/50 bg-[#D1EB8F]/45' : 'border-[#78956A]/50 bg-[#F9F4EA]' }} space-y-4 hover:border-[#FF9ABC] transition shadow-2xs"> <div class="flex items-start justify-between gap-2"> <div> <div class="flex items-center gap-1.5"> <span class="text-[10px] font-mono font-bold text-[#78956A]">{{ $ord->order_number }}</span> <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold {{ ($ord->booking_type ?? 'now') === 'now' ? 'bg-[#D1EB8F] text-[#78956A]' : 'bg-[#D1EB8F] text-[#46571A]' }}"> {{ ($ord->booking_type ?? 'now') === 'now' ? ' Pesan Sekarang' : ' Terjadwal' }}
                                                </span> </div> <h5 class="font-extrabold text-sm text-[#46571A] mt-1">{{ $ord->serviceCatalog?->name }}</h5> <p class="text-xs text-[#46571A]/85 mt-0.5">Pasien: <strong>{{ $ord->patient_name }}</strong> ({{ $ord->patient_phone }})</p> </div> <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-[#D1EB8F]/70 text-[#46571A] border border-[#78956A]/50"> {{ $ord->status_label }}
                                        </span> </div> <div class="text-xs text-[#46571A]/85 space-y-1.5 bg-white p-3.5 rounded-xl border border-[#78956A]/50"> <div class="flex items-center justify-between"> <span class="text-[#78956A]">Jadwal Perawatan:</span> <span class="font-bold text-[#46571A]"> {{ $ord->scheduled_date?->format('d M Y') }} • {{ str_contains($ord->scheduled_time ?? '', 'WIB') ? $ord->scheduled_time : ($ord->scheduled_time . ' WIB') }}
                                            </span> </div> <div class="flex items-center justify-between"> <span class="text-[#78956A]">Jarak Rute Jalan (OSRM):</span> <span class="font-bold text-[#78956A]">{{ number_format((float) $ord->distance_km, 1, ',', '.') }} km</span> </div> <div class="flex items-start justify-between gap-2"> <span class="text-[#78956A] shrink-0">Lokasi Pasien:</span> <span class="text-right text-[#46571A] truncate max-w-[220px]" title="{{ $ord->address }}">{{ $ord->address }}</span> </div> <div class="flex items-center justify-between pt-1.5 border-t border-[#78956A]/50 text-[#78956A] font-extrabold"> <span>Pendapatan Bersih Perawat:</span> <span>Rp {{ number_format($ord->nurse_earnings, 0, ',', '.') }}</span> </div> </div> @if (!$canAct)
                                        <div class="p-3 rounded-xl bg-[#D1EB8F]/70 border border-[#78956A] text-[11px] text-[#46571A] flex items-start gap-2"> <span class="text-sm shrink-0"><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg></span> <div> <strong class="font-extrabold block">Menunggu Jadwal Perawatan (WIB)</strong> <span>{{ $actionCheck['message'] }}</span> </div> </div> @endif

                                    <!-- Rute ke Rumah Pasien (Peta Aplikasi & Google Maps) -->
                                    @php
                                        $patLat = $ord->latitude ?? $ord->patient_latitude;
                                        $patLng = $ord->longitude ?? $ord->patient_longitude;
                                        $gmapsUrl = ($patLat && $patLng)
                                            ? ('https://www.google.com/maps/dir/?api=1&' . ($profile->latitude && $profile->longitude ? "origin={$profile->latitude},{$profile->longitude}&" : '') . "destination={$patLat},{$patLng}&travelmode=driving")
                                            : ('https://www.google.com/maps/search/?api=1&query=' . urlencode($ord->address));
                                    @endphp
                                    <div class="grid grid-cols-2 gap-2">
                                        <button type="button" @click="openRouteModal(@js($ord))"
                                            class="py-2 px-3 rounded-xl bg-[#D1EB8F] hover:bg-[#78956A] text-[#46571A] hover:text-[#F9F4EA] border border-[#78956A] text-[11px] font-bold transition flex items-center justify-center gap-1.5 cursor-pointer">
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z" /></svg>
                                            <span>Lihat Rute (Peta App)</span>
                                        </button>
                                        <a href="{{ $gmapsUrl }}" target="_blank" rel="noopener noreferrer"
                                            class="py-2 px-3 rounded-xl bg-[#FFFDF9] hover:bg-[#D1EB8F] text-[#46571A] border border-[#78956A] text-[11px] font-bold transition flex items-center justify-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                                            <span>Buka Google Maps</span>
                                        </a>
                                    </div>

                                    <!-- Aksi Tahapan Kunjungan Nakes (Validasi 30 Menit Sebelum Jam Perawatan) --> <div class="space-y-2"> @if ($ord->status === 'diterima')
                                            <button type="button"
                                                @if($canAct) @click="updateOrderStatus({{ $ord->id }}, 'dalam_perjalanan')" @else disabled @endif
                                                class="w-full py-2.5 px-3 rounded-xl font-bold text-xs shadow-xs transition flex items-center justify-center gap-1.5 {{ $canAct ? 'bg-[#FF9ABC] hover:bg-[#78956A] text-[#46571A] cursor-pointer' : 'bg-[#D1EB8F]/50 text-[#78956A] cursor-not-allowed opacity-75' }}"> <span><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" /></svg></span> <span>Mulai Perjalanan</span> </button> @elseif ($ord->status === 'dalam_perjalanan')
                                            <button type="button"
                                                @if($canAct) @click="updateOrderStatus({{ $ord->id }}, 'tiba')" @else disabled @endif
                                                class="w-full py-2.5 px-3 rounded-xl font-bold text-xs shadow-xs transition flex items-center justify-center gap-1.5 {{ $canAct ? 'bg-[#FF9ABC] hover:bg-[#D1EB8F] text-[#46571A] cursor-pointer' : 'bg-[#D1EB8F]/50 text-[#78956A] cursor-not-allowed opacity-75' }}"> <span><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg></span> <span>Sampai di Lokasi</span> </button> @elseif ($ord->status === 'tiba')
                                            <button type="button"
                                                @if($canAct) @click="updateOrderStatus({{ $ord->id }}, 'tindakan_berlangsung')" @else disabled @endif
                                                class="w-full py-2.5 px-3 rounded-xl font-bold text-xs shadow-xs transition flex items-center justify-center gap-1.5 {{ $canAct ? 'bg-[#FF9ABC] hover:bg-[#FF9ABC] text-[#46571A] cursor-pointer' : 'bg-[#D1EB8F]/50 text-[#78956A] cursor-not-allowed opacity-75' }}"> <span><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg></span> <span>Mulai Perawatan</span> </button> @endif

                                        <button type="button"
                                            @if($canAct) @click="openEReportModal(@js($ord))" @else disabled @endif
                                            class="w-full py-2.5 px-4 rounded-xl font-extrabold text-xs shadow-md transition flex items-center justify-center gap-2 {{ $canAct ? 'bg-[#78956A] hover:bg-[#D1EB8F] text-[#46571A] shadow-[#78956A]/25 cursor-pointer' : 'bg-[#D1EB8F]/50 text-[#78956A] cursor-not-allowed opacity-75' }}"> <span><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg></span> <span>Selesai Perawatan &amp; Isi E-Report</span> </button> </div> </div> @endforeach
                        </div> </div> @else
                    <div class="p-6 text-center rounded-2xl bg-[#F9F4EA] border border-[#78956A]/50 space-y-2"> <span class="text-3xl block"><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg></span> <h4 class="text-xs font-bold text-[#46571A]">Tidak Ada Tugas Kunjungan Tertunda</h4> <p class="text-[11px] text-[#78956A] max-w-md mx-auto"> Semua tugas kunjungan medis Anda telah dilengkapi E-Report. Pastikan jadwal harian Anda aktif agar pasien dapat memesan layanan Anda.
                        </p> </div> @endif

                <!-- Riwayat Pesanan yang Telah Selesai dengan E-Report & Review Pasien --> @if ($completedOrdersWithReport->isNotEmpty())
                    <div class="pt-4 border-t border-[#78956A]/50 space-y-3"> <h4 class="text-xs uppercase font-extrabold text-[#78956A] tracking-wider">Riwayat E-Report yang Telah Diterbitkan:</h4> <div class="space-y-3 max-h-80 overflow-y-auto pr-1"> @foreach ($completedOrdersWithReport as $cord)
                                <div class="p-4 rounded-2xl bg-[#F9F4EA] border border-[#78956A]/50 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3"> <div class="space-y-1"> <div class="flex items-center gap-2"> <span class="font-bold text-[#46571A]">{{ $cord->serviceCatalog?->name }}</span> <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-[#D1EB8F] text-[#46571A]"> Selesai</span> </div> <p class="text-[11px] text-[#78956A]">Pasien: {{ $cord->patient_name }} • Tanggal: {{ $cord->scheduled_date?->format('d M Y') }}</p> @if ($cord->review)
                                            <div class="flex items-center gap-1.5 pt-1 text-[#78956A] font-bold text-[11px]"> <span>⭐ {{ $cord->review->rating }}/5:</span> <span class="text-[#46571A]/85 font-normal italic">"{{ $cord->review->comment ?: 'Pelayanan sangat memuaskan!' }}"</span> </div> @endif
                                    </div> <div class="flex items-center gap-2 shrink-0"> <span class="font-extrabold text-[#46571A]"> +Rp {{ number_format($cord->nurse_earnings, 0, ',', '.') }}
                                        </span> </div> </div> @endforeach
                        </div> </div> @endif
            </div> <!-- 2. GRID UTAMA: LAYANAN MEDIS & RIWAYAT MUTASI DOMPET --> <div class="grid grid-cols-1 lg:grid-cols-3 gap-6"> <!-- KOLOM KIRI: PILIHAN LAYANAN MEDIS --> <div class="lg:col-span-1 space-y-6"> <!-- Pilihan Layanan Medis yang Disediakan --> <div class="bg-white rounded-3xl border border-[#78956A]/50 p-6 shadow-sm space-y-4"> <div class="flex items-center justify-between border-b border-[#78956A]/50 pb-3"> <div> <h3 class="text-sm font-bold text-[#46571A]">Layanan yang Anda Sediakan</h3> <p class="text-[11px] text-[#78956A]">Pilih tindakan medis keahlian Anda.</p> </div> <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-[#D1EB8F]/45 text-[#46571A]"> {{ count($selectedServiceIds) }} Terpilih
                            </span> </div> <form action="{{ route('perawat.layanan.update') }}" method="POST" class="space-y-4"> @csrf
                            <div class="space-y-2.5 max-h-96 overflow-y-auto pr-1"> @foreach ($services as $service)
                                    <label x-data="{ checked: {{ in_array($service->id, $selectedServiceIds) ? 'true' : 'false' }} }"
                                        class="p-3 rounded-2xl border transition flex items-start gap-3 cursor-pointer hover:bg-[#F9F4EA]"
                                        :class="checked ? 'border-[#78956A] bg-[#D1EB8F]/45' : 'border-[#78956A]/50'"> <input type="checkbox" name="service_ids[]" value="{{ $service->id }}"
                                            {{ in_array($service->id, $selectedServiceIds) ? 'checked' : '' }}
                                            @change="checked = $event.target.checked"
                                            class="rounded text-[#78956A] focus:ring-[#78956A] mt-0.5"> <div class="space-y-0.5 text-xs"> <div class="flex items-center gap-1.5"> <span>{{ $service->image_emoji }}</span> <span class="font-bold text-[#46571A]">{{ $service->name }}</span> </div> <p class="text-[11px] text-[#78956A]">{{ $service->category }} • {{ $service->estimated_duration }}</p> <span class="text-[10px] font-semibold text-[#46571A] block">Tarif: Rp {{ number_format($service->price, 0, ',', '.') }}</span> </div> </label> @endforeach
                            </div> <button type="submit" class="w-full py-2.5 rounded-xl bg-[#FF9ABC] hover:bg-[#FF9ABC] text-[#46571A] font-bold text-xs shadow transition"> Simpan Pilihan Layanan
                            </button> </form> </div> <!-- Riwayat Permintaan Payout Mini --> <div class="bg-white rounded-3xl border border-[#78956A]/50 p-6 shadow-sm space-y-3"> <div class="flex items-center justify-between border-b border-[#78956A]/50 pb-2"> <h4 class="text-xs uppercase font-bold text-[#78956A] tracking-wider">Status Pencairan</h4> <button type="button" @click="openPayoutModal()" class="text-[11px] font-bold text-[#46571A] hover:underline"> + Tarik Saldo
                            </button> </div> @if ($payoutRequests->isEmpty())
                            <p class="text-xs text-[#78956A] italic">Belum ada riwayat penarikan dana.</p> @else
                            <div class="space-y-2.5 max-h-60 overflow-y-auto pr-1"> @foreach ($payoutRequests as $pr)
                                    <div class="p-3 rounded-2xl bg-[#F9F4EA] border border-[#78956A]/50 text-xs space-y-1"> <div class="flex items-center justify-between"> <span class="font-bold text-[#46571A]">Rp {{ number_format($pr->amount, 0, ',', '.') }}</span> <span class="px-2 py-0.5 rounded-full text-[10px] font-bold 
                                                {{ $pr->status === 'transferred' ? 'bg-[#D1EB8F] text-[#46571A]' : ($pr->status === 'rejected' ? 'bg-[#FF9ABC]/40 text-[#46571A]' : 'bg-[#D1EB8F]/70 text-[#46571A]') }}"> {{ ucfirst($pr->status) }}
                                            </span> </div> <p class="text-[11px] text-[#78956A]">{{ $pr->bank_name }} - {{ $pr->account_number }}</p> </div> @endforeach
                            </div> @endif
                    </div> </div> <!-- KOLOM KANAN: TABEL MUTASI FINANSIAL & DOMPET --> <div class="lg:col-span-2 space-y-6"> <!-- Riwayat Mutasi Finansial Dompet --> <div class="bg-white rounded-3xl border border-[#78956A]/50 p-6 shadow-sm space-y-4"> <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#78956A]/50 pb-3"> <div> <h3 class="text-sm font-bold text-[#46571A]">Tabel Mutasi Finansial &amp; Riwayat Bagi Hasil</h3> <p class="text-xs text-[#78956A]">Transparansi potongan platform fee 15% dan pendapatan bersih nakes 85% + transport.</p> </div> <span class="text-xs text-[#78956A]">15 Transaksi Terakhir</span> </div> @if ($recentTransactions->isEmpty())
                            <p class="text-xs text-[#78956A] italic py-8 text-center">Belum ada transaksi pendapatan atau penarikan saldo tercatat.</p> @else
                            <div class="overflow-x-auto"> <table class="w-full text-left text-xs text-[#46571A]"> <thead class="text-[10px] uppercase font-bold text-[#78956A] bg-[#F9F4EA] border-b border-[#78956A]/50"> <tr> <th class="py-2.5 px-3">Waktu</th> <th class="py-2.5 px-3">Keterangan</th> <th class="py-2.5 px-3">Platform Fee (15%)</th> <th class="py-2.5 px-3">Net Nakes</th> <th class="py-2.5 px-3 text-right">Perubahan Saldo</th> <th class="py-2.5 px-3 text-center">Status</th> </tr> </thead> <tbody class="divide-y divide-[#78956A]/30 font-medium"> @foreach ($recentTransactions as $tx)
                                            <tr class="hover:bg-[#F9F4EA] transition"> <td class="py-3 px-3 whitespace-nowrap text-[11px] text-[#78956A] font-mono"> {{ $tx->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                                                </td> <td class="py-3 px-3"> <span class="font-bold text-[#46571A] block">{{ $tx->description }}</span> <span class="text-[10px] text-[#78956A] font-mono">Tipe: {{ $tx->type }}</span> </td> <td class="py-3 px-3 text-[#46571A] font-semibold whitespace-nowrap"> {{ $tx->platform_fee > 0 ? 'Rp ' . number_format($tx->platform_fee, 0, ',', '.') : '-' }}
                                                </td> <td class="py-3 px-3 text-[#46571A] font-semibold whitespace-nowrap"> {{ $tx->net_amount > 0 ? 'Rp ' . number_format($tx->net_amount, 0, ',', '.') : '-' }}
                                                </td> <td class="py-3 px-3 text-right font-extrabold whitespace-nowrap {{ $tx->amount > 0 ? 'text-[#78956A]' : 'text-[#46571A]' }}"> {{ $tx->amount > 0 ? '+' : '' }}Rp {{ number_format($tx->amount, 0, ',', '.') }}
                                                </td> <td class="py-3 px-3 text-center"> <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $tx->status === 'completed' ? 'bg-[#D1EB8F]/45 text-[#46571A] border border-[#78956A]/50' : 'bg-[#F9F4EA] text-[#46571A] border border-[#78956A]/50' }}"> {{ ucfirst($tx->status ?: 'Selesai') }}
                                                    </span> </td> </tr> @endforeach
                                    </tbody> </table> </div> @endif
                    </div> </div> </div> </div> <!-- ============================================================= --> <!-- MODAL FORMULIR E-REPORT KLINIS PERAWAT                        --> <!-- ============================================================= --> <div x-show="ereportModalOpen" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto bg-[#46571A]/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-6"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"> <div @click.away="ereportModalOpen = false"
                class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl border border-[#78956A]/50 overflow-hidden text-[#46571A] max-h-[92vh] flex flex-col"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"> <!-- Header Modal --> <div class="px-6 py-5 bg-[#78956A] text-[#F9F4EA] flex items-center justify-between shrink-0"> <div class="flex items-center gap-3"> <div class="w-10 h-10 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center text-xl"> </div> <div> <span class="text-[10px] uppercase tracking-wider font-extrabold text-[#D1EB8F] block">Formulir Rekam Medis Digital</span> <h3 class="text-base font-extrabold leading-tight">Input E-Report Asuhan Keperawatan</h3> </div> </div> <button type="button" @click="ereportModalOpen = false"
                        class="p-2 rounded-xl bg-white/10 hover:bg-white/20 text-white transition text-xs"> </button> </div> <!-- Form Content (Scrollable) --> <form :action="ereportFormAction" method="POST" enctype="multipart/form-data" class="overflow-y-auto grow p-6 space-y-6"> @csrf

                    <!-- CARD 1: RINGKASAN PASIEN & PESANAN --> <div class="p-4 rounded-2xl bg-[#F9F4EA] border border-[#78956A]/50 space-y-2"> <div class="flex items-center justify-between"> <span class="text-xs uppercase font-extrabold text-[#78956A] tracking-wider">Target Tindakan Medis</span> <span class="text-xs font-mono font-bold text-[#46571A]" x-text="activeOrderForReport?.order_number"></span> </div> <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs pt-1"> <div> <span class="text-[#78956A] block text-[11px]">Pasien:</span> <strong class="text-[#46571A] text-sm" x-text="activeOrderForReport?.patient_name"></strong> <span class="text-[#78956A] block text-[11px]" x-text="'Telp: ' + (activeOrderForReport?.patient_phone || '-')"></span> </div> <div> <span class="text-[#78956A] block text-[11px]">Layanan:</span> <strong class="text-[#46571A]" x-text="activeOrderForReport?.service_catalog?.name"></strong> <span class="text-[#78956A] block text-[11px]" x-text="activeOrderForReport?.scheduled_date + ' • ' + (activeOrderForReport?.scheduled_time || '')"></span> </div> </div> </div> <!-- CARD 2: TANDA-TANDA VITAL (TTV) --> <div class="space-y-4"> <div class="flex items-center justify-between border-b border-[#78956A]/50 pb-2"> <div class="flex items-center gap-2"> <span class="text-[#78956A] font-bold">①</span> <h4 class="text-xs font-bold text-[#46571A] uppercase tracking-wider">Pengukuran Tanda-Tanda Vital (TTV) Pasien</h4> </div> <span class="text-[10px] text-[#46571A] font-semibold">*Wajib Diisi</span> </div> <div class="grid grid-cols-1 sm:grid-cols-2 gap-4"> <!-- Tekanan Darah Sistolik --> <div> <label class="block text-xs font-bold text-[#46571A] mb-1">Tekanan Darah Sistolik</label> <div class="relative rounded-xl shadow-xs"> <input type="number" name="systolic" min="50" max="300" required value="120" placeholder="Mis. 120"
                                        class="w-full text-xs rounded-xl border-[#78956A]/50 pr-16 focus:border-[#78956A] focus:ring-[#78956A]"> <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#78956A] text-xs font-bold"> mmHg
                                    </span> </div> </div> <!-- Tekanan Darah Diastolik --> <div> <label class="block text-xs font-bold text-[#46571A] mb-1">Tekanan Darah Diastolik</label> <div class="relative rounded-xl shadow-xs"> <input type="number" name="diastolic" min="30" max="200" required value="80" placeholder="Mis. 80"
                                        class="w-full text-xs rounded-xl border-[#78956A]/50 pr-16 focus:border-[#78956A] focus:ring-[#78956A]"> <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#78956A] text-xs font-bold"> mmHg
                                    </span> </div> </div> <!-- Suhu Tubuh --> <div> <label class="block text-xs font-bold text-[#46571A] mb-1">Suhu Tubuh</label> <div class="relative rounded-xl shadow-xs"> <input type="number" step="0.1" name="body_temp" min="30" max="45" required value="36.5" placeholder="Mis. 36.5"
                                        class="w-full text-xs rounded-xl border-[#78956A]/50 pr-12 focus:border-[#78956A] focus:ring-[#78956A]"> <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#78956A] text-xs font-bold"> °C
                                    </span> </div> </div> <!-- Denyut Nadi --> <div> <label class="block text-xs font-bold text-[#46571A] mb-1">Denyut Nadi</label> <div class="relative rounded-xl shadow-xs"> <input type="number" name="pulse_rate" min="30" max="250" required value="82" placeholder="Mis. 80"
                                        class="w-full text-xs rounded-xl border-[#78956A]/50 pr-14 focus:border-[#78956A] focus:ring-[#78956A]"> <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#78956A] text-xs font-bold"> bpm
                                    </span> </div> </div> <!-- Saturasi Oksigen SpO2 --> <div> <label class="block text-xs font-bold text-[#46571A] mb-1">Saturasi Oksigen (SpO2)</label> <div class="relative rounded-xl shadow-xs"> <input type="number" name="spo2" min="50" max="100" required value="98" placeholder="Mis. 98"
                                        class="w-full text-xs rounded-xl border-[#78956A]/50 pr-12 focus:border-[#78956A] focus:ring-[#78956A]"> <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#78956A] text-xs font-bold"> %
                                    </span> </div> </div> <!-- Gula Darah Sewaktu (GDS) --> <div> <label class="block text-xs font-bold text-[#46571A] mb-1"> Gula Darah Sewaktu (GDS) <span class="text-[10px] text-[#78956A] font-normal">(Opsional)</span> </label> <div class="relative rounded-xl shadow-xs"> <input type="number" name="gds" min="20" max="800" placeholder="Mis. 110"
                                        class="w-full text-xs rounded-xl border-[#78956A]/50 pr-16 focus:border-[#78956A] focus:ring-[#78956A]"> <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#78956A] text-xs font-bold"> mg/dL
                                    </span> </div> </div> </div> </div> <!-- CARD 3: CATATAN ASUHAN & EDUKASI KELUARGA --> <div class="space-y-4"> <div class="flex items-center gap-2 border-b border-[#78956A]/50 pb-2"> <span class="text-[#78956A] font-bold">②</span> <h4 class="text-xs font-bold text-[#46571A] uppercase tracking-wider">Catatan Asuhan Keperawatan &amp; Edukasi Pasien</h4> </div> <div> <label class="block text-xs font-bold text-[#46571A] mb-1"> Ringkasan Catatan Klinis / Asuhan Keperawatan (SOAP) <span class="text-[#46571A]">*</span> </label> <textarea name="clinical_notes" rows="3" required
                                placeholder="Tuliskan keluhan pasien, tindakan medis yang telah diberikan, respons pasien, serta kondisi terkini..."
                                class="w-full text-xs rounded-xl border-[#78956A]/50 focus:border-[#78956A] focus:ring-[#78956A]"></textarea> </div> <div> <label class="block text-xs font-bold text-[#46571A] mb-1"> Instruksi &amp; Edukasi Keluarga Pasien <span class="text-[10px] text-[#78956A] font-normal">(Opsional)</span> </label> <textarea name="family_education" rows="2"
                                placeholder="Edukasi mengenai jadwal konsumsi obat, perawatan luka mandiri, pantangan makanan, atau jadwal kontrol lanjutan..."
                                class="w-full text-xs rounded-xl border-[#78956A]/50 focus:border-[#78956A] focus:ring-[#78956A]"></textarea> </div> </div> <!-- CARD 4: UPLOAD DOKUMENTASI FOTO TINDAKAN --> <div class="space-y-3"> <div class="flex items-center justify-between border-b border-[#78956A]/50 pb-2"> <div class="flex items-center gap-2"> <span class="text-[#78956A] font-bold">③</span> <h4 class="text-xs font-bold text-[#46571A] uppercase tracking-wider">Dokumentasi Foto Tindakan / Kondisi Luka</h4> </div> <span class="text-[10px] text-[#78956A]">Maks. 5 Foto (JPEG/PNG)</span> </div> <div class="p-4 rounded-2xl border-2 border-dashed border-[#78956A]/50 hover:border-[#78956A] bg-[#F9F4EA] transition text-center space-y-2"> <input type="file" id="ereportPhotoInput" name="photos[]" multiple accept="image/*" @change="handlePhotoUpload($event)"
                                class="hidden"> <label for="ereportPhotoInput" class="cursor-pointer inline-flex flex-col items-center gap-1.5 text-xs text-[#46571A]/85 hover:text-[#46571A] font-semibold"> <span class="text-2xl"></span> <span>Klik di sini untuk memilih foto dokumentasi tindakan</span> <span class="text-[10px] text-[#78956A] font-normal">Ukuran maks. 5MB per file</span> </label> </div> <!-- Preview Foto yang Dipilih --> <div x-show="photoPreviews.length > 0" class="grid grid-cols-2 sm:grid-cols-3 gap-3 pt-2"> <template x-for="(preview, idx) in photoPreviews" :key="idx"> <div class="relative rounded-xl border border-[#78956A]/50 overflow-hidden bg-[#F9F4EA] p-1 space-y-1"> <img :src="preview.url" class="w-full h-24 object-cover rounded-lg"> <input type="text" :name="'captions[' + idx + ']'" placeholder="Keterangan foto"
                                        class="w-full text-[10px] py-1 px-1.5 rounded border border-[#78956A]/50"> </div> </template> </div> </div> <!-- CARD 5: INFORMASI SETTLEMENT DANA OTOMATIS --> <div class="p-4 rounded-2xl bg-[#D1EB8F]/40 border border-[#78956A]/50 text-xs text-[#46571A] space-y-2"> <div class="flex items-center gap-1.5 font-bold"> <span><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg></span> <span>Automated Settlement CareAtHome</span> </div> <p class="text-[11px] text-[#46571A] leading-relaxed"> Setelah tombol di bawah ditekan, pesanan akan secara otomatis dinyatakan <strong>Selesai</strong> dan dana bagi hasil (85% biaya tindakan + 100% ongkos transport) akan langsung ditransfer ke Dompet Anda secara otomatis.
                        </p> </div> <!-- Tombol Aksi --> <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#78956A]/50"> <button type="button" @click="ereportModalOpen = false"
                            class="px-5 py-2.5 rounded-xl border border-[#78956A]/50 text-[#46571A]/85 hover:bg-[#F9F4EA] text-xs font-bold transition"> Batal
                        </button> <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-[#78956A] hover:bg-[#D1EB8F] text-[#46571A] font-extrabold text-xs shadow-md shadow-[#78956A]/25 transition flex items-center gap-2 cursor-pointer"> <span><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg></span> <span>Simpan E-Report &amp; Selesaikan Layanan</span> </button> </div> </form> </div> </div> <!-- ============================================================= --> <!-- MODAL ATUR JADWAL OPERASIONAL HARIAN (SENIN - MINGGU WIB)     --> <!-- ============================================================= --> <div x-show="scheduleModalOpen" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto bg-[#46571A]/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-6"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"> <div @click.away="scheduleModalOpen = false"
                class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl border border-[#78956A]/50 overflow-hidden text-[#46571A] max-h-[92vh] flex flex-col"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"> <div class="px-6 py-5 bg-[#78956A] text-[#F9F4EA] flex items-center justify-between shrink-0"> <div class="flex items-center gap-3"> <div class="w-10 h-10 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center text-xl"> </div> <div> <span class="text-[10px] uppercase tracking-wider font-extrabold text-[#D1EB8F] block">Manajemen Ketersediaan Harian (WIB)</span> <h3 class="text-base font-extrabold leading-tight">Atur Jadwal Praktik Perawat (Senin – Minggu)</h3> </div> </div> <button type="button" @click="scheduleModalOpen = false" class="p-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs"> </button> </div> <form action="{{ route('perawat.jadwal.update') }}" method="POST" class="overflow-y-auto grow p-6 space-y-4"> @csrf

                    <div class="p-3.5 rounded-2xl bg-[#D1EB8F]/45 border border-[#78956A]/50 text-xs text-[#78956A] leading-relaxed"> <strong> Cara Kerja Jadwal Per Hari (WIB):</strong> Pada setiap hari, klik tombol <strong>(＋ Tambah Jam)</strong> untuk menambahkan jam berapa saja Anda bisa bekerja. Pasien hanya dapat memesan <strong>Pesan Sekarang</strong> maupun <strong>Pesan Untuk Nanti</strong> sesuai daftar jam yang Anda tambahkan, dan jam yang sudah dipesan pasien lain otomatis terkunci.
                    </div> <div class="space-y-3"> @if (isset($schedules))
                            @foreach ($schedules as $idx => $sch)
                                @php
                                    $initialSlots = $sch->getEffectiveTimeSlots();
                                    if (empty($initialSlots)) {
                                        $initialSlots = \App\Models\NurseSchedule::normalizeTimeSlots($sch->time_slots ?? []);
                                    }
                                @endphp
                                <div x-data="{
                                        activeDay: {{ ($sch->is_active && !empty($initialSlots)) ? 'true' : 'false' }},
                                        slots: {{ json_encode(array_values($initialSlots)) }},
                                        newTime: '09:00',
                                        quickHours: ['08:00', '10:00', '13:00', '15:00', '17:00', '19:00', '21:00'],
                                        addSlot(customVal = null) {
                                            const val = String(customVal || this.newTime || '').trim().substring(0, 5);
                                            if (!/^\d{2}:\d{2}$/.test(val)) return;
                                            if (!this.slots.includes(val)) {
                                                this.slots.push(val);
                                                this.slots.sort();
                                            }
                                            this.activeDay = true;
                                        },
                                        removeSlot(sIdx) {
                                            this.slots.splice(sIdx, 1);
                                            if (this.slots.length === 0) {
                                                this.activeDay = false;
                                            }
                                        },
                                        toggleDay(checked) {
                                            this.activeDay = checked;
                                            if (checked && this.slots.length === 0) {
                                                this.slots = ['08:00', '10:00', '13:00', '15:00', '19:00'];
                                            }
                                        }
                                    }"
                                    class="p-4 rounded-2xl border transition space-y-3"
                                    :class="activeDay ? 'border-[#78956A] bg-[#D1EB8F]/45' : 'border-[#78956A]/50 bg-[#F9F4EA]'"> <!-- Baris Atas: Nama Hari + Toggle + Input Tambah Jam (+) --> <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5"> <div class="flex items-center gap-3"> <input type="hidden" name="schedules[{{ $idx }}][day_of_week]" value="{{ $sch->day_of_week }}"> <label class="inline-flex items-center gap-2.5 cursor-pointer"> <input type="checkbox" name="schedules[{{ $idx }}][is_active]" value="1"
                                                    :checked="activeDay"
                                                    @change="toggleDay($event.target.checked)"
                                                    class="w-4 h-4 rounded text-[#78956A] focus:ring-[#FF9ABC]"> <span class="font-extrabold text-sm text-[#46571A] w-20">{{ $sch->day_name }}</span> </label> <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                                                :class="activeDay ? 'bg-[#D1EB8F] text-[#78956A]' : 'bg-[#FF9ABC]/40 text-[#46571A]'"
                                                x-text="activeDay ? ('Buka • ' + slots.length + ' Jam Kerja') : 'Libur / Tidak Aktif'"> </span> </div> <!-- Kontrol (+) Tambah Jam Berapa Saja --> <div class="flex items-center gap-1.5" :class="!activeDay ? 'opacity-60' : ''"> <input type="time" x-model="newTime"
                                                class="text-xs font-mono font-bold rounded-xl border-[#78956A] bg-white py-1.5 px-2.5 focus:border-[#FF9ABC] focus:ring-[#FF9ABC]"> <button type="button" @click="addSlot()"
                                                class="px-3 py-1.5 rounded-xl bg-[#78956A] hover:bg-[#78956A] text-[#F9F4EA] text-xs font-extrabold shadow-xs transition flex items-center gap-1 cursor-pointer shrink-0"> <span class="text-sm leading-none">＋</span> <span>Tambah Jam</span> </button> </div> </div> <!-- Daftar Chip Jam Kerja Aktif Hari Ini --> <div x-show="activeDay" class="space-y-2 pt-1 border-t border-[#78956A]/50"> <div class="flex flex-wrap items-center gap-1.5"> <template x-for="(slot, sIdx) in slots" :key="slot"> <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border-2 border-[#FF9ABC] text-[#78956A] text-xs font-mono font-extrabold shadow-2xs"> <span><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></span> <span x-text="slot + ' WIB'"></span> <input type="hidden" :name="'schedules[{{ $idx }}][time_slots][]'" :value="slot"> <button type="button" @click="removeSlot(sIdx)"
                                                        title="Hapus jam ini"
                                                        class="ml-0.5 w-4 h-4 rounded-full bg-[#FF9ABC]/40 hover:bg-[#FF9ABC] text-[#46571A] hover:text-[#46571A] flex items-center justify-center text-[10px] font-black transition cursor-pointer"> </button> </div> </template> <template x-if="slots.length === 0"> <span class="text-[11px] text-[#46571A] font-semibold italic">Belum ada jam dipilih. Pilih jam lalu tekan ＋ Tambah Jam.</span> </template> </div> <!-- Pilihan Cepat (+) Jam Populer --> <div class="flex flex-wrap items-center gap-1 pt-0.5"> <span class="text-[10px] font-bold text-[#78956A] mr-1">Cepat tambah:</span> <template x-for="qh in quickHours" :key="qh"> <button type="button"
                                                    x-show="!slots.includes(qh)"
                                                    @click="addSlot(qh)"
                                                    class="px-2 py-0.5 rounded-lg border border-dashed border-[#78956A] bg-white hover:bg-[#D1EB8F] text-[#78956A] text-[10px] font-mono font-bold transition cursor-pointer"> ＋ <span x-text="qh"></span> </button> </template> </div> </div> </div> @endforeach
                        @endif
                    </div> <div class="pt-3 border-t border-[#78956A]/50 flex items-center justify-end gap-2"> <button type="button" @click="scheduleModalOpen = false"
                            class="px-4 py-2.5 rounded-xl border border-[#78956A]/50 text-xs font-bold text-[#46571A]/85 hover:bg-[#F9F4EA] transition"> Batal
                        </button> <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-[#78956A] hover:bg-[#78956A] text-[#F9F4EA] text-xs font-extrabold shadow transition cursor-pointer"> Simpan Jam Kerja Harian (WIB)
                        </button> </div> </form> </div> </div> <!-- ============================================================= --> <!-- MODAL FORMULIR TARIK SALDO / PAYOUT INSTAN (TANPA REKENING)   --> <!-- ============================================================= --> <div x-show="payoutModalOpen" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto bg-[#46571A]/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-6"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"> <div @click.away="payoutModalOpen = false"
                class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-[#78956A]/50 overflow-hidden text-[#46571A]"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"> <div class="px-6 py-5 bg-[#78956A] text-[#F9F4EA] flex items-center justify-between"> <div> <span class="text-[10px] uppercase tracking-wider font-extrabold text-[#D1EB8F] block">Pencairan Saldo Instan Perawat</span> <h3 class="text-base font-extrabold leading-tight">Tarik Saldo Dompet</h3> </div> <button type="button" @click="payoutModalOpen = false" class="p-1.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs"> </button> </div> <form action="{{ route('perawat.payout.request') }}" method="POST" class="p-6 space-y-4"> @csrf

                    <!-- Info Saldo Tersedia --> <div class="p-4 rounded-2xl bg-[#D1EB8F]/45 border border-[#78956A]/50 flex items-center justify-between"> <span class="text-xs font-semibold text-[#78956A]">Saldo Aktif Tersedia:</span> <strong class="text-lg font-black text-[#78956A]">Rp {{ number_format($totalBalance, 0, ',', '.') }}</strong> </div> <!-- Pilihan Nominal Cepat (Quick Chips) --> <div> <label class="block text-xs font-bold text-[#46571A] mb-1.5">Pilihan Nominal Cepat</label> <div class="grid grid-cols-2 sm:grid-cols-4 gap-2"> <button type="button" @click="setPayoutAmount(50000)"
                                :class="payoutAmount === 50000 ? 'bg-[#78956A] text-[#46571A] border-[#78956A] font-bold' : 'bg-[#F9F4EA] text-[#46571A] border-[#78956A]/50 hover:bg-[#D1EB8F]/45'"
                                class="py-2 px-2 rounded-xl text-xs border text-center transition cursor-pointer"> Rp 50rb
                            </button> <button type="button" @click="setPayoutAmount(100000)"
                                :class="payoutAmount === 100000 ? 'bg-[#78956A] text-[#46571A] border-[#78956A] font-bold' : 'bg-[#F9F4EA] text-[#46571A] border-[#78956A]/50 hover:bg-[#D1EB8F]/45'"
                                class="py-2 px-2 rounded-xl text-xs border text-center transition cursor-pointer"> Rp 100rb
                            </button> <button type="button" @click="setPayoutAmount(250000)"
                                :class="payoutAmount === 250000 ? 'bg-[#78956A] text-[#46571A] border-[#78956A] font-bold' : 'bg-[#F9F4EA] text-[#46571A] border-[#78956A]/50 hover:bg-[#D1EB8F]/45'"
                                class="py-2 px-2 rounded-xl text-xs border text-center transition cursor-pointer"> Rp 250rb
                            </button> <button type="button" @click="setPayoutAmount({{ (int) $totalBalance }})"
                                :class="payoutAmount === {{ (int) $totalBalance }} ? 'bg-[#78956A] text-[#46571A] border-[#78956A] font-bold' : 'bg-[#F9F4EA] text-[#46571A] border-[#78956A]/50 hover:bg-[#D1EB8F]/45'"
                                class="py-2 px-2 rounded-xl text-xs border text-center transition cursor-pointer"> Semua
                            </button> </div> </div> <div> <label class="block text-xs font-bold text-[#46571A] mb-1">Masukkan Nominal Penarikan (Rp) <span class="text-[#46571A]">*</span></label> <input type="number" name="amount" x-model.number="payoutAmount" min="10000" max="{{ max(10000, (int) $totalBalance) }}" required
                            class="w-full text-sm font-bold rounded-xl border-[#78956A]/50 focus:border-[#FF9ABC] focus:ring-[#FF9ABC]"> <span class="text-[10px] text-[#78956A] mt-1 block"> Minimal Rp 10.000 &amp; maksimal sesuai saldo aktif (Rp {{ number_format($totalBalance, 0, ',', '.') }}).
                        </span> </div> <div class="p-3.5 rounded-2xl bg-[#F9F4EA] border border-[#78956A]/50 text-[11px] text-[#46571A]/85 leading-relaxed"> <strong>Pencairan Otomatis Satu Klik:</strong> Anda cukup menginputkan nominal penarikan tanpa perlu mengisi rekening manual. Saldo aktif akan langsung terpotong otomatis dan tercatat di riwayat transaksi serta panel Admin.
                    </div> <div class="pt-2 flex items-center justify-end gap-2"> <button type="button" @click="payoutModalOpen = false"
                            class="px-4 py-2.5 rounded-xl border border-[#78956A]/50 text-xs font-bold text-[#46571A]/85 hover:bg-[#F9F4EA] transition"> Batal
                        </button> <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-[#FF9ABC] hover:bg-[#FF9ABC] text-[#46571A] hover:text-[#46571A] text-xs font-extrabold shadow transition cursor-pointer"> Tarik Saldo Sekarang
                        </button> </div> </form> </div> </div>

    <!-- MODAL PETA RUTE MENUJU RUMAH PASIEN (IN-APP OSRM + GOOGLE MAPS) -->
    <div x-show="routeModalOpen" x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-[#46571A]/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-6">
        <div @click.away="routeModalOpen = false"
            class="bg-[#FFFDF9] rounded-3xl max-w-2xl w-full shadow-2xl border border-[#78956A] overflow-hidden text-[#46571A] flex flex-col">
            <div class="px-6 py-4 bg-[#78956A] text-[#F9F4EA] flex items-center justify-between">
                <div>
                    <span class="text-[10px] uppercase font-bold tracking-wider text-[#D1EB8F]" x-text="'Navigasi Kunjungan #' + (routeOrder?.order_number || '')"></span>
                    <h3 class="text-base font-bold mt-0.5" x-text="'Rute ke Rumah ' + (routeOrder?.patient_name || 'Pasien')"></h3>
                </div>
                <button type="button" @click="routeModalOpen = false" class="p-1.5 rounded-xl hover:bg-white/10 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="p-5 space-y-3 text-xs">
                <div class="grid grid-cols-2 gap-3 p-3 rounded-2xl bg-[#F9F4EA] border border-[#78956A]/40">
                    <div>
                        <span class="text-[10px] font-bold uppercase text-[#78956A] block">Jarak Rute Jalan</span>
                        <span class="text-sm font-extrabold text-[#46571A]" x-text="routeDistanceKm"></span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase text-[#78956A] block">Estimasi Waktu Tempuh</span>
                        <span class="text-sm font-extrabold text-[#46571A]" x-text="routeDurationMin"></span>
                    </div>
                </div>
                <p class="text-[11px] text-[#46571A]/85"><strong>Alamat Pasien:</strong> <span x-text="routeOrder?.address || '-'"></span></p>
                <div id="nurseRouteLeafletMap" class="w-full h-72 rounded-2xl border border-[#78956A] z-0"></div>
            </div>
            <div class="px-6 py-4 bg-[#F9F4EA] border-t border-[#78956A]/30 flex flex-wrap items-center justify-between gap-2">
                <a :href="routeOrder ? ('https://www.google.com/maps/dir/?api=1&' + (lat && lng ? `origin=${lat},${lng}&` : '') + `destination=${routeOrder.latitude || routeOrder.patient_latitude},${routeOrder.longitude || routeOrder.patient_longitude}&travelmode=driving`) : '#'"
                    target="_blank" rel="noopener noreferrer"
                    class="px-4 py-2 rounded-xl bg-[#FF9ABC] hover:bg-[#D1EB8F] text-[#46571A] border border-[#78956A] text-xs font-bold transition flex items-center gap-1.5">
                    <span>Buka Navigasi di Google Maps</span>
                </a>
                <button type="button" @click="routeModalOpen = false"
                    class="px-4 py-2 rounded-xl border border-[#78956A] bg-[#FFFDF9] text-xs font-bold text-[#46571A] cursor-pointer">Tutup</button>
            </div>
        </div>
    </div>
    </div> <!-- Alpine App Logic for Nurse Dashboard --> <script> function nurseDashboardApp(initialOnline, verificationStatus, initialLat, initialLng) {
            return {
                isOnline: initialOnline,
                verificationStatus: verificationStatus,
                lat: initialLat,
                lng: initialLng,
                togglingStatus: false,
                watchId: null,
                lastUpdatedText: '',
                lastLocationSentAt: 0,
                toastMessage: '',
                toastType: 'success',
                currentWibClock: '',

                // Modals State
                scheduleModalOpen: false,
                ereportModalOpen: false,
                activeOrderForReport: null,
                ereportFormAction: '',
                photoPreviews: [],
                payoutModalOpen: false,
                payoutAmount: {{ $totalBalance >= 100000 ? 100000 : max(10000, (int) $totalBalance) }},
                routeModalOpen: false,
                routeOrder: null,
                routeMap: null,
                routeDistanceKm: '-',
                routeDurationMin: '-',

                openRouteModal(order) {
                    this.routeOrder = order;
                    this.routeDistanceKm = order.distance_km ? `${Number(order.distance_km).toFixed(1)} km` : '-';
                    this.routeDurationMin = order.distance_km ? `~${Math.max(3, Math.round(Number(order.distance_km) * 3))} menit` : '-';
                    this.routeModalOpen = true;

                    this.$nextTick(async () => {
                        if (typeof L === 'undefined') return;
                        const nLat = parseFloat(this.lat || -7.307743);
                        const nLng = parseFloat(this.lng || 112.729963);
                        const pLat = parseFloat(order.latitude || order.patient_latitude || nLat);
                        const pLng = parseFloat(order.longitude || order.patient_longitude || nLng);

                        if (this.routeMap) {
                            this.routeMap.remove();
                            this.routeMap = null;
                        }

                        this.routeMap = L.map('nurseRouteLeafletMap').setView([(nLat + pLat) / 2, (nLng + pLng) / 2], 14);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; OpenStreetMap'
                        }).addTo(this.routeMap);

                        const nurseMarker = L.marker([nLat, nLng]).addTo(this.routeMap).bindPopup('<strong>Posisi Anda (Perawat)</strong>').openPopup();
                        const patientMarker = L.marker([pLat, pLng]).addTo(this.routeMap).bindPopup(`<strong>Rumah Pasien: ${order.patient_name || 'Pasien'}</strong><br>${order.address || ''}`);

                        try {
                            const url = `https://router.project-osrm.org/route/v1/driving/${nLng},${nLat};${pLng},${pLat}?overview=full&geometries=geojson`;
                            const res = await fetch(url);
                            const json = await res.json();
                            if (json && json.routes && json.routes[0]) {
                                const r = json.routes[0];
                                const coords = r.geometry.coordinates.map(c => [c[1], c[0]]);
                                const poly = L.polyline(coords, { color: '#46571A', weight: 5, opacity: 0.85 }).addTo(this.routeMap);
                                this.routeMap.fitBounds(poly.getBounds().pad(0.25));
                                this.routeDistanceKm = (Math.max(0.1, r.distance / 1000)).toFixed(1) + ' km';
                                this.routeDurationMin = '~' + Math.max(2, Math.round(r.duration / 60)) + ' menit';
                            } else {
                                this.routeMap.fitBounds(L.featureGroup([nurseMarker, patientMarker]).getBounds().pad(0.3));
                            }
                        } catch (e) {
                            this.routeMap.fitBounds(L.featureGroup([nurseMarker, patientMarker]).getBounds().pad(0.3));
                        }
                        setTimeout(() => this.routeMap && this.routeMap.invalidateSize(), 150);
                    });
                },

                openEReportModal(order) {
                    this.activeOrderForReport = order;
                    this.ereportFormAction = '/perawat/orders/' + order.id + '/ereport';
                    this.photoPreviews = [];
                    this.ereportModalOpen = true;
                },

                async updateOrderStatus(orderId, newStatus) {
                    if (!confirm(`Konfirmasi perbarui tahapan kunjungan medis ini?`)) {
                        return;
                    }

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const response = await fetch(`/perawat/orders/${orderId}/status`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({ status: newStatus }),
                        });
                        const data = await response.json();
                        if (response.ok && data.status === 'success') {
                            alert(data.message);
                            window.location.reload();
                        } else {
                            alert(data.message || 'Gagal memperbarui status kunjungan.');
                        }
                    } catch (e) {
                        alert('Terjadi kesalahan jaringan.');
                    }
                },

                handlePhotoUpload(event) {
                    const files = event.target.files;
                    this.photoPreviews = [];
                    if (files && files.length > 0) {
                        for (let i = 0; i < Math.min(files.length, 5); i++) {
                            const file = files[i];
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                this.photoPreviews.push({
                                    url: e.target.result,
                                    name: file.name
                                });
                            };
                            reader.readAsDataURL(file);
                        }
                    }
                },

                openPayoutModal() {
                    this.payoutModalOpen = true;
                },

                setPayoutAmount(amount) {
                    this.payoutAmount = amount;
                },

                init() {
                    this.updateClock();
                    setInterval(() => this.updateClock(), 1000);
                    this.startLocationWatch();
                },

                updateClock() {
                    const fmt = new Intl.DateTimeFormat('id-ID', {
                        timeZone: 'Asia/Jakarta',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: false
                    });
                    this.currentWibClock = fmt.format(new Date()).replace(/\./g, ':') + ' WIB';
                },

                showToast(msg, type = 'success') {
                    this.toastMessage = msg;
                    this.toastType = type;
                    setTimeout(() => { this.toastMessage = ''; }, 4000);
                },

                async saveManualLocation() {
                    const fallbackLat = this.lat || -7.3144;
                    const fallbackLng = this.lng || 112.7304;

                    if (!navigator.geolocation) {
                        await this.sendLocationUpdate(fallbackLat, fallbackLng, true);
                        return;
                    }

                    navigator.geolocation.getCurrentPosition(
                        async (pos) => {
                            this.lat = parseFloat(pos.coords.latitude.toFixed(6));
                            this.lng = parseFloat(pos.coords.longitude.toFixed(6));
                            await this.sendLocationUpdate(this.lat, this.lng, true);
                        },
                        async () => {
                            this.lat = fallbackLat;
                            this.lng = fallbackLng;
                            await this.sendLocationUpdate(this.lat, this.lng, true);
                        },
                        { enableHighAccuracy: true, timeout: 6000, maximumAge: 0 }
                    );
                },

                async handleToggleOnline() {
                    if (this.togglingStatus) return;

                    const targetState = !this.isOnline;
                    this.togglingStatus = true;

                    if (targetState) {
                        if (!navigator.geolocation) {
                            const fallbackLat = this.lat || -7.3144;
                            const fallbackLng = this.lng || 112.7304;
                            await this.sendToggleRequest(true, fallbackLat, fallbackLng);
                            this.togglingStatus = false;
                            return;
                        }

                        navigator.geolocation.getCurrentPosition(
                            async (pos) => {
                                this.lat = parseFloat(pos.coords.latitude.toFixed(6));
                                this.lng = parseFloat(pos.coords.longitude.toFixed(6));

                                await this.sendToggleRequest(true, this.lat, this.lng);
                                this.startLocationWatch();
                                this.togglingStatus = false;
                            },
                            async (err) => {
                                console.warn('Izin lokasi gagal/ditolak, menggunakan koordinat tersimpan/default:', err);
                                const fallbackLat = this.lat || -7.3144;
                                const fallbackLng = this.lng || 112.7304;
                                this.lat = fallbackLat;
                                this.lng = fallbackLng;
                                await this.sendToggleRequest(true, fallbackLat, fallbackLng);
                                this.togglingStatus = false;
                            },
                            { enableHighAccuracy: true, timeout: 6000, maximumAge: 0 }
                        );
                    } else {
                        this.stopLocationWatch();
                        await this.sendToggleRequest(false);
                        this.togglingStatus = false;
                    }
                },

                async sendToggleRequest(isOnline, lat = null, lng = null) {
                    try {
                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const res = await fetch('/perawat/toggle-online', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                is_online: isOnline,
                                latitude: lat,
                                longitude: lng
                            })
                        });

                        const data = await res.json();
                        if (res.ok && data.status === 'success') {
                            this.isOnline = isOnline;
                            if (data.data?.latitude && data.data?.longitude) {
                                this.lat = parseFloat(data.data.latitude);
                                this.lng = parseFloat(data.data.longitude);
                            }
                            this.showToast(data.message, 'success');
                            this.lastUpdatedText = 'Baru saja';
                        } else {
                            this.isOnline = !isOnline;
                            this.showToast(data.message || 'Gagal mengubah status kerja.', 'error');
                        }
                    } catch (e) {
                        this.isOnline = !isOnline;
                        this.showToast('Terjadi kesalahan jaringan.', 'error');
                    }
                },

                startLocationWatch() {
                    if (!navigator.geolocation) return;

                    this.stopLocationWatch();

                    this.watchId = navigator.geolocation.watchPosition(
                        (pos) => {
                            this.lat = pos.coords.latitude;
                            this.lng = pos.coords.longitude;

                            const now = Date.now();
                            // Throttle pengiriman: maksimal sekali tiap 30 detik
                            if (now - this.lastLocationSentAt >= 30000) {
                                this.lastLocationSentAt = now;
                                this.sendLocationUpdate(this.lat, this.lng);
                            }
                        },
                        (err) => {
                            console.warn('Geolocation error:', err);
                            if (this.isOnline) {
                                this.showToast('Koneksi GPS terputus. Pastikan izin lokasi aktif.', 'error');
                            }
                        },
                        { enableHighAccuracy: true, maximumAge: 30000 }
                    );
                },

                stopLocationWatch() {
                    if (this.watchId !== null) {
                        navigator.geolocation.clearWatch(this.watchId);
                        this.watchId = null;
                    }
                },

                async sendLocationUpdate(lat, lng, showAlert = false) {
                    try {
                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const res = await fetch('/perawat/lokasi', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ latitude: lat, longitude: lng })
                        });

                        const data = await res.json();
                        if (res.ok && data.status === 'success') {
                            this.lastUpdatedText = 'Baru saja';
                            if (showAlert) {
                                this.showToast(data.message || 'Lokasi kerja berhasil disimpan!', 'success');
                            }
                        } else if (showAlert) {
                            this.showToast(data.message || 'Gagal menyimpan lokasi.', 'error');
                        }
                    } catch (e) {
                        console.warn('Gagal sinkron lokasi:', e);
                        if (showAlert) {
                            this.showToast('Terjadi kesalahan jaringan saat menyimpan lokasi.', 'error');
                        }
                    }
                }
            };
        }

        async function dismissAuditBanner(e, url, id) {
            if (e) e.preventDefault();
            const banner = document.getElementById('notif-banner-' + id);
            if (banner) banner.remove();
            try {
                const token = document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}';
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                if (res.ok) {
                    const data = await res.json();
                    window.dispatchEvent(new CustomEvent('notification-marked-read', { 
                        detail: { 
                            id: id, 
                            unread_count: data.unread_count 
                        } 
                    }));
                }
            } catch(err) {
                console.error('Error dismissing audit banner:', err);
            }
        }
    </script>
</x-app-layout>
