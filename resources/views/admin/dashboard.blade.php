<x-admin-layout> <x-slot name="header"> Dashboard Analitik Eksekutif &amp; Kokpit Finansial (WIB)
    </x-slot> <!-- HERO BANNER EKSEKUTIF ADMIN --> <div class="bg-[#78956A] rounded-3xl p-6 sm:p-8 text-[#F9F4EA] shadow-xl relative overflow-hidden border border-[#78956A]"> <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-[#FF9ABC]/15 rounded-full blur-3xl pointer-events-none"></div> <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10"> <div class="space-y-2 max-w-2xl"> <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#FF9ABC]/20 border border-[#FF9ABC]/40 text-[#F9F4EA] text-xs font-bold"> <span class="w-2 h-2 rounded-full bg-[#FF9ABC] animate-pulse"></span> <span>PUSAT KENDALI CAREATHOME • ZONA WAKTU WIB ({{ now('Asia/Jakarta')->format('d M Y, H:i') }} WIB)</span> </div> <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight"> Dashboard Analitik &amp; Manajemen Operasional
                </h1> <p class="text-xs sm:text-sm text-[#F9F4EA] leading-relaxed"> Pantau grafik tren pemesanan (<strong>Pesan Sekarang vs Pesan Untuk Nanti</strong>), perbandingan arus kas masuk &amp; penarikan saldo (payout) perawat, distribusi status pesanan, serta verifikasi registrasi manual perawat.
                </p> </div> <!-- Filter Rentang Waktu Global --> <form action="{{ route('admin.dashboard') }}" method="GET" class="bg-white/10 backdrop-blur-md p-4 rounded-2xl border border-white/15 flex flex-col gap-2 min-w-[210px]"> <label class="text-[10px] uppercase font-extrabold text-[#F9F4EA] tracking-wider">Rentang Analitik Grafik</label> <select name="days" onchange="this.form.submit()"
                    class="text-xs rounded-xl border-white/20 bg-white text-[#46571A] font-extrabold focus:ring-[#FF9ABC] focus:border-[#FF9ABC] py-2 px-3 cursor-pointer"> <option value="7" {{ $days == 7 ? 'selected' : '' }}>7 Hari Terakhir</option> <option value="14" {{ $days == 14 ? 'selected' : '' }}>14 Hari Terakhir</option> <option value="30" {{ $days == 30 ? 'selected' : '' }}>30 Hari Terakhir</option> <option value="90" {{ $days == 90 ? 'selected' : '' }}>90 Hari Terakhir</option> </select> </form> </div> </div> <!-- 1. 4 STAT CARDS UTAMA KOKPIT FINANSIAL & OPERASIONAL --> <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5"> <!-- Stat 1: Total GMV Transaksi Bruto --> <div class="bg-white rounded-3xl border border-[#78956A]/50 p-6 shadow-sm space-y-3 relative overflow-hidden group hover:border-[#FF9ABC] transition"> <div class="flex items-center justify-between"> <span class="text-[10px] font-extrabold text-[#78956A] uppercase tracking-wider">Total Pendapatan Masuk (GMV)</span> <span class="w-10 h-10 rounded-2xl bg-[#D1EB8F]/45 text-[#78956A] flex items-center justify-center text-lg border border-[#78956A]/50"><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" /></svg></span> </div> <div> <span class="text-2xl font-black text-[#46571A] tracking-tight block"> Rp {{ number_format($totalGmv, 0, ',', '.') }}
                </span> <span class="text-[11px] text-[#78956A] font-bold flex items-center gap-1 mt-0.5"> <span>Komisi Platform (15%): Rp {{ number_format($totalPlatformFee, 0, ',', '.') }}</span> </span> </div> </div> <!-- Stat 2: Total Saldo & Payout Perawat --> <div class="bg-white rounded-3xl border border-[#78956A]/50 p-6 shadow-sm space-y-3 relative overflow-hidden group hover:border-[#FF9ABC] transition"> <div class="flex items-center justify-between"> <span class="text-[10px] font-extrabold text-[#78956A] uppercase tracking-wider">Hak Bersih &amp; Payout Perawat</span> <span class="w-10 h-10 rounded-2xl bg-[#D1EB8F]/45 text-[#46571A] flex items-center justify-center text-lg border border-[#78956A]/50"><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" /></svg></span> </div> <div> <span class="text-2xl font-black text-[#78956A] tracking-tight block"> Rp {{ number_format($totalNurseEarnings, 0, ',', '.') }}
                </span> <span class="text-[11px] text-[#78956A] flex items-center gap-1 mt-0.5"> <span>Total ditarik (Payout): <strong class="text-[#46571A]">Rp {{ number_format($totalPayoutWithdrawn ?? 0, 0, ',', '.') }}</strong></span> </span> </div> </div> <!-- Stat 3: Volume Pesanan & Transaksi --> <div class="bg-white rounded-3xl border border-[#78956A]/50 p-6 shadow-sm space-y-3 relative overflow-hidden group hover:border-[#FF9ABC] transition"> <div class="flex items-center justify-between"> <span class="text-[10px] font-extrabold text-[#46571A] uppercase tracking-wider">Volume Layanan Homecare</span> <span class="w-10 h-10 rounded-2xl bg-[#D1EB8F]/45 text-[#46571A] flex items-center justify-center text-lg border border-[#78956A]/50"><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg></span> </div> <div> <span class="text-2xl font-black text-[#46571A] tracking-tight block"> {{ $totalOrdersCount }} <span class="text-xs font-normal text-[#78956A]">Total Pesanan</span> </span> <span class="text-[11px] text-[#78956A] flex items-center gap-1 mt-0.5"> <span><strong>{{ $completedOrdersCount }}</strong> selesai &bull; <strong>{{ $successfulTransactionsCount }}</strong> lunas</span> </span> </div> </div> <!-- Stat 4: Verifikasi Registrasi Perawat --> <div class="bg-white rounded-3xl border border-[#78956A]/50 p-6 shadow-sm space-y-3 relative overflow-hidden group hover:border-[#FF9ABC] transition"> <div class="flex items-center justify-between"> <span class="text-[10px] font-extrabold text-[#78956A] uppercase tracking-wider">Mitra Perawat Terdaftar</span> <span class="w-10 h-10 rounded-2xl bg-[#F9F4EA] text-[#46571A] flex items-center justify-center text-lg border border-[#78956A]/50"><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg></span> </div> <div> <span class="text-2xl font-black text-[#46571A] tracking-tight block"> {{ $verifiedNursesCount }} <span class="text-xs font-normal text-[#78956A]">/ {{ $totalNursesCount }} Terverifikasi</span> </span> <div class="flex items-center justify-between mt-0.5"> <span class="text-[11px] text-[#46571A] font-bold"> {{ $pendingNursesCount }} menunggu verifikasi
                    </span> <a href="{{ route('admin.nurses.index') }}" class="text-[11px] font-extrabold text-[#78956A] hover:underline"> Verifikasi &rarr;
                    </a> </div> </div> </div> </div> <!-- 2. TIGA GRAFIK VISUALISASI INTERAKTIF (CHART.JS) --> <div class="grid grid-cols-1 lg:grid-cols-3 gap-6"> <!-- CHART 1: GRAFIK TREN PEMESANAN ("PESAN SEKARANG" VS "PESAN UNTUK NANTI") --> <div class="lg:col-span-2 bg-white rounded-3xl border border-[#78956A]/50 p-6 shadow-sm space-y-4"> <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#78956A]/50 pb-3"> <div> <span class="text-[10px] uppercase font-extrabold text-[#78956A] tracking-wider block">Visualisasi 1 • Line / Area Chart</span> <h3 class="text-base font-extrabold text-[#46571A]">Grafik Tren Pemesanan Harian (WIB)</h3> <p class="text-xs text-[#78956A]">Perbandingan jumlah pesanan tipe <strong>Pesan Sekarang</strong> vs <strong>Pesan Untuk Nanti (Terjadwal)</strong>.</p> </div> <div class="flex flex-wrap items-center gap-3 text-xs font-bold"> <span class="inline-flex items-center gap-1.5 text-[#46571A] whitespace-nowrap"> <span class="w-3 h-3 rounded-full bg-[#46571A] shrink-0"></span> Pesan Sekarang
                    </span> <span class="inline-flex items-center gap-1.5 text-[#46571A] whitespace-nowrap"> <span class="w-3 h-3 rounded-full bg-[#FF9ABC] shrink-0"></span> Pesan Untuk Nanti
                    </span> </div> </div> <div class="h-72 w-full"> <canvas id="bookingTrendChart"></canvas> </div> </div> <!-- CHART 3: DISTRIBUSI STATUS PESANAN (DONUT CHART) --> <div class="lg:col-span-1 bg-white rounded-3xl border border-[#78956A]/50 p-6 shadow-sm flex flex-col justify-between space-y-4"> <div class="border-b border-[#78956A]/50 pb-3"> <span class="text-[10px] uppercase font-extrabold text-[#78956A] tracking-wider block">Visualisasi 3 • Donut Chart</span> <h3 class="text-base font-extrabold text-[#46571A]">Distribusi Status Pesanan</h3> <p class="text-xs text-[#78956A]">Proporsi status seluruh pesanan perawatan di sistem.</p> </div> <div class="h-56 w-full flex items-center justify-center relative"> <canvas id="orderStatusDonutChart"></canvas> </div> <div class="grid grid-cols-2 gap-2 pt-2 border-t border-[#78956A]/50 text-xs"> <div class="p-2.5 rounded-xl bg-[#D1EB8F]/45 border border-[#78956A]/50 flex items-center justify-between"> <span class="font-bold text-[#78956A]"> Selesai</span> <strong class="text-[#78956A]">{{ $orderStatusDistribution['counts'][0] ?? 0 }}</strong> </div> <div class="p-2.5 rounded-xl bg-[#D1EB8F]/45 border border-[#78956A]/50 flex items-center justify-between"> <span class="font-bold text-[#46571A]"> Berlangsung</span> <strong class="text-[#46571A]">{{ $orderStatusDistribution['counts'][1] ?? 0 }}</strong> </div> <div class="p-2.5 rounded-xl bg-[#F9F4EA] border border-[#78956A]/50 flex items-center justify-between"> <span class="font-bold text-[#46571A]"> Terjadwal</span> <strong class="text-[#46571A]">{{ $orderStatusDistribution['counts'][2] ?? 0 }}</strong> </div> <div class="p-2.5 rounded-xl bg-[#FF9ABC]/25 border border-[#FF9ABC] flex items-center justify-between"> <span class="font-bold text-[#46571A]"> Batal/Tolak</span> <strong class="text-[#46571A]">{{ $orderStatusDistribution['counts'][3] ?? 0 }}</strong> </div> </div> </div> </div> <!-- CHART 2: GRAFIK PENDAPATAN TRANSAKSI MASUK VS PENARIKAN PAYOUT PERAWAT (BAR CHART) --> <div class="bg-white rounded-3xl border border-[#78956A]/50 p-6 sm:p-7 shadow-sm space-y-4"> <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#78956A]/50 pb-3"> <div> <span class="text-[10px] uppercase font-extrabold text-[#78956A] tracking-wider block">Visualisasi 2 • Interactive Bar Chart</span> <h3 class="text-base font-extrabold text-[#46571A]">Grafik Pendapatan Transaksi Masuk vs Penarikan Saldo (Payout) Perawat</h3> <p class="text-xs text-[#78956A]">Perbandingan harian total pembayaran pasien yang masuk dengan total penarikan dana (payout) oleh perawat.</p> </div> <div class="flex flex-wrap items-center gap-4 text-xs font-bold"> <span class="inline-flex items-center gap-1.5 text-[#46571A] whitespace-nowrap"> <span class="w-3 h-3 rounded bg-[#6E8555] shrink-0"></span> Total Transaksi Masuk (Rp)
                </span> <span class="inline-flex items-center gap-1.5 text-[#46571A] whitespace-nowrap"> <span class="w-3 h-3 rounded bg-[#FF9ABC] shrink-0"></span> Penarikan Payout Perawat (Rp)
                </span> </div> </div> <div class="h-72 w-full"> <canvas id="revenuePayoutBarChart"></canvas> </div> </div> <!-- 3. DUA PANEL MANAJEMEN: VERIFIKASI & JADWAL PERAWAT + LOG PEMOTONGAN SALDO PAYOUT --> <div class="grid grid-cols-1 lg:grid-cols-2 gap-6"> <!-- Panel Kiri: Manajemen Verifikasi & Jadwal Perawat (Registrasi Manual) --> <div class="bg-white rounded-3xl border border-[#78956A]/50 p-6 shadow-sm space-y-4"> <div class="flex items-center justify-between gap-3 border-b border-[#78956A]/50 pb-3"> <div> <h3 class="text-sm font-extrabold text-[#46571A]">Status Verifikasi, Jadwal &amp; Saldo Perawat</h3> <p class="text-[11px] text-[#78956A]">Daftar perawat hasil registrasi beserta jadwal aktif mingguan dan saldo dompet.</p> </div> <a href="{{ route('admin.nurses.index') }}" class="inline-flex items-center justify-center whitespace-nowrap shrink-0 px-3 py-1.5 rounded-xl bg-[#78956A] hover:bg-[#46571A] text-[#F9F4EA] text-xs font-bold transition"> Kelola Verifikasi &rarr;
                </a> </div> @if ($topNurses->isEmpty())
                <div class="p-8 text-center rounded-2xl bg-[#F9F4EA] border border-[#78956A]/50 space-y-2"> <span class="text-2xl block"></span> <p class="text-xs font-bold text-[#46571A]">Belum Ada Akun Perawat Terdaftar</p> <p class="text-[11px] text-[#78956A]">Perawat baru yang melakukan registrasi manual akan muncul di sini untuk diverifikasi Admin.</p> </div> @else
                <div class="space-y-3 max-h-96 overflow-y-auto pr-1"> @foreach ($topNurses as $nurse)
                        <div class="p-3.5 rounded-2xl border border-[#78956A]/50 flex items-center justify-between gap-3 text-xs hover:border-[#FF9ABC] transition bg-[#F9F4EA]"> <div class="flex items-center gap-3 min-w-0"> <div class="w-10 h-10 rounded-xl bg-[#78956A] text-[#F9F4EA] font-black flex items-center justify-center shadow-xs shrink-0"> {{ strtoupper(substr($nurse['name'], 0, 2)) }}
                                </div> <div class="min-w-0"> <div class="flex items-center gap-2 flex-wrap"> <h4 class="font-extrabold text-[#46571A] truncate">{{ $nurse['name'] }}</h4> <span class="inline-flex items-center justify-center whitespace-nowrap shrink-0 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold {{ $nurse['verification_status'] === 'verified' ? 'bg-[#D1EB8F] text-[#46571A]' : ($nurse['verification_status'] === 'rejected' ? 'bg-[#FF9ABC]/40 text-[#46571A]' : 'bg-[#D1EB8F]/70 text-[#46571A]') }}"> {{ ucfirst($nurse['verification_status']) }}
                                        </span> </div> <span class="text-[11px] text-[#78956A] block mt-0.5"> {{ $nurse['active_days_count'] }}/7 Hari Praktik Aktif &bull; ⭐ {{ number_format($nurse['rating_avg'], 1) }}
                                    </span> </div> </div> <div class="text-right shrink-0"> <span class="text-[10px] uppercase font-bold text-[#78956A] block">Saldo Aktif</span> <span class="font-extrabold text-sm text-[#46571A] whitespace-nowrap">Rp {{ number_format($nurse['balance'], 0, ',', '.') }}</span> </div> </div> @endforeach
                </div> @endif
        </div> <!-- Panel Kanan: Log Pemotongan Saldo Payout Perawat --> <div class="bg-white rounded-3xl border border-[#78956A]/50 p-6 shadow-sm space-y-4"> <div class="flex items-center justify-between gap-3 border-b border-[#78956A]/50 pb-3"> <div> <h3 class="text-sm font-extrabold text-[#46571A]">Log Penarikan Saldo (Payout) Perawat</h3> <p class="text-[11px] text-[#78956A]">Pemantauan pemotongan saldo otomatis saat perawat menarik dana.</p> </div> <span class="inline-flex items-center justify-center whitespace-nowrap shrink-0 px-3 py-1 rounded-full bg-[#D1EB8F]/45 text-[#46571A] border border-[#78956A]/50 text-[10px] font-extrabold"> Potong Saldo Otomatis
                </span> </div> @if ($recentPayouts->isEmpty())
                <div class="p-8 text-center rounded-2xl bg-[#F9F4EA] border border-[#78956A]/50 space-y-2"> <span class="text-2xl block"><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" /></svg></span> <p class="text-xs font-bold text-[#46571A]">Belum Ada Riwayat Penarikan Saldo</p> <p class="text-[11px] text-[#78956A]">Setiap penarikan nominal saldo oleh perawat akan tercatat otomatis di sini.</p> </div> @else
                <div class="overflow-x-auto"> <table class="w-full text-left text-xs text-[#46571A]/85"> <thead class="bg-[#F9F4EA] text-[10px] uppercase font-bold text-[#78956A] border-b border-[#78956A]/50"> <tr> <th class="px-4 py-3 whitespace-nowrap">Waktu (WIB)</th> <th class="px-4 py-3 whitespace-nowrap">Nama Perawat</th> <th class="px-4 py-3 text-right whitespace-nowrap">Nominal Ditarik</th> <th class="px-4 py-3 text-center whitespace-nowrap">Status</th> </tr> </thead> <tbody class="divide-y divide-[#78956A]/30"> @foreach ($recentPayouts as $po)
                                <tr class="hover:bg-[#F9F4EA] align-middle"> <td class="px-4 py-3 align-middle text-[11px] font-mono text-[#78956A] whitespace-nowrap"> {{ $po->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                                    </td> <td class="px-4 py-3 align-middle font-bold text-[#46571A] whitespace-nowrap"> {{ $po->nurse?->name ?? $po->account_holder ?? 'Mitra Perawat' }}
                                    </td> <td class="px-4 py-3 align-middle text-right font-extrabold text-[#46571A] whitespace-nowrap"> - Rp {{ number_format($po->amount, 0, ',', '.') }}
                                    </td> <td class="px-4 py-3 align-middle text-center"> <span class="inline-flex items-center justify-center whitespace-nowrap shrink-0 px-3 py-1 rounded-full text-[10px] font-bold bg-[#D1EB8F]/45 text-[#46571A] border border-[#78956A]/50"> Saldo Terpotong
                                        </span> </td> </tr> @endforeach
                        </tbody> </table> </div> @endif
        </div> </div> <!-- 4. TABEL MONITORING MUTASI TRANSAKSI REAL-TIME (AUTOMATED LEDGER) --> <div class="bg-white rounded-3xl border border-[#78956A]/50 shadow-sm overflow-hidden space-y-4"> <div class="p-6 border-b border-[#78956A]/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3"> <div> <h3 class="text-base font-extrabold text-[#46571A] tracking-tight flex flex-wrap items-center gap-2"> <span>Buku Besar Mutasi Transaksi &amp; Pembayaran (WIB)</span> <span class="inline-flex items-center justify-center whitespace-nowrap shrink-0 text-xs px-3 py-1 rounded-full bg-[#D1EB8F]/45 text-[#46571A] border border-[#78956A]/50 font-bold"> Real-Time Ledger
                    </span> </h3> <p class="text-xs text-[#78956A] mt-0.5"> Log seluruh arus masuk pembayaran pasien, distribusi komisi 15% platform, 85% pendapatan bersih perawat, dan penarikan saldo.
                </p> </div> <a href="{{ route('admin.escrow.index') }}" class="text-xs font-bold text-[#78956A] hover:text-[#46571A] hover:underline flex items-center gap-1 whitespace-nowrap shrink-0"> <span>Buka Monitoring Transaksi Lengkap &rarr;</span> </a> </div> @if ($recentMutations->isEmpty())
            <div class="p-12 text-center text-xs text-[#78956A] space-y-2"> <span class="text-3xl block"><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg></span> <p class="font-bold">Belum ada mutasi transaksi otomatis yang tercatat.</p> </div> @else
            <div class="overflow-x-auto"> <table class="w-full text-left text-xs border-collapse"> <thead class="bg-[#78956A] text-[#F9F4EA] text-[10px] uppercase font-bold tracking-wider"> <tr> <th class="py-3 px-4 whitespace-nowrap">Waktu (WIB)</th> <th class="py-3 px-4 whitespace-nowrap">No. Pesanan / ID</th> <th class="py-3 px-4 whitespace-nowrap">Keterangan Mutasi</th> <th class="py-3 px-4 whitespace-nowrap">Pemilik Dompet</th> <th class="py-3 px-4 text-right whitespace-nowrap">Perubahan Nominal</th> <th class="py-3 px-4 text-right whitespace-nowrap">Komisi Platform (15%)</th> <th class="py-3 px-4 text-right whitespace-nowrap">Saldo Akhir</th> <th class="py-3 px-4 text-center whitespace-nowrap">Status</th> </tr> </thead> <tbody class="divide-y divide-[#78956A]/30 text-[#46571A] font-medium"> @foreach ($recentMutations as $mut)
                            <tr class="hover:bg-[#F9F4EA] transition align-middle"> <td class="py-3 px-4 align-middle text-[11px] font-mono text-[#78956A] whitespace-nowrap"> {{ $mut->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                                </td> <td class="py-3 px-4 align-middle font-mono font-bold text-[#46571A] whitespace-nowrap"> {{ $mut->payment?->order_number ?? ($mut->order_id ? 'ORD-' . $mut->order_id : 'TX-' . $mut->id) }}
                                </td> <td class="py-3 px-4 align-middle"> <span class="font-bold text-[#46571A] block truncate max-w-[240px]" title="{{ $mut->description }}"> {{ $mut->description ?: 'Mutasi transaksi otomatis' }}
                                    </span> </td> <td class="py-3 px-4 align-middle whitespace-nowrap"> @if ($mut->wallet?->user)
                                        <div class="flex items-center gap-1.5"> <span class="text-xs shrink-0"><svg class="w-4 h-4 inline-block shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg></span> <span class="font-bold text-[#46571A] text-xs">{{ $mut->wallet->user->name }}</span> <span class="inline-flex items-center justify-center whitespace-nowrap shrink-0 text-[9px] px-2 py-0.5 rounded-full font-bold uppercase {{ $mut->wallet->user->hasRole('admin') ? 'bg-[#D1EB8F]/60 text-[#46571A] border border-[#78956A]/50' : 'bg-[#D1EB8F]/45 text-[#46571A] border border-[#78956A]/50' }}"> {{ $mut->wallet->user->hasRole('admin') ? 'Admin' : 'Perawat' }}
                                            </span> </div> @else
                                        <span class="text-[#78956A] italic">Sistem Platform</span> @endif
                                </td> <td class="py-3 px-4 align-middle text-right font-black whitespace-nowrap {{ $mut->amount >= 0 ? 'text-[#78956A]' : 'text-[#46571A]' }}"> {{ $mut->amount >= 0 ? '+ ' : '- ' }} Rp {{ number_format(abs($mut->net_amount ?: $mut->amount), 0, ',', '.') }}
                                </td> <td class="py-3 px-4 align-middle text-right text-[#78956A] whitespace-nowrap"> {{ $mut->platform_fee > 0 ? 'Rp ' . number_format($mut->platform_fee, 0, ',', '.') : '-' }}
                                </td> <td class="py-3 px-4 align-middle text-right font-bold text-[#46571A] whitespace-nowrap"> Rp {{ number_format($mut->balance_after, 0, ',', '.') }}
                                </td> <td class="py-3 px-4 align-middle text-center whitespace-nowrap"> <span class="inline-flex items-center justify-center whitespace-nowrap shrink-0 px-3 py-1 rounded-full text-[10px] font-extrabold bg-[#D1EB8F]/45 text-[#46571A] border border-[#78956A]/50"> Selesai
                                    </span> </td> </tr> @endforeach
                    </tbody> </table> </div> @endif
    </div> <!-- Script Inisialisasi 3 Grafik Interaktif Chart.js --> <script> document.addEventListener('DOMContentLoaded', function () {
            const labels = @json($chartLabels);
            const dataBookingNow = @json($chartBookingNow);
            const dataBookingScheduled = @json($chartBookingScheduled);
            const dataRevenueIn = @json($chartRevenueIn);
            const dataPayoutOut = @json($chartPayoutOut);
            const statusDist = @json($orderStatusDistribution);

            // 1. Line/Area Chart: Tren Pemesanan (Pesan Sekarang vs Pesan Untuk Nanti)
            const ctxTrend = document.getElementById('bookingTrendChart');
            if (ctxTrend) {
                new Chart(ctxTrend.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Pesan Sekarang (On-Demand)',
                                data: dataBookingNow,
                                borderColor: '#46571A',
                                backgroundColor: 'rgba(70, 87, 26, 0.15)',
                                pointBackgroundColor: '#46571A',
                                pointBorderColor: '#46571A',
                                fill: true,
                                tension: 0.35,
                                borderWidth: 2.5,
                                pointRadius: 3.5,
                            },
                            {
                                label: 'Pesan Untuk Nanti (Terjadwal)',
                                data: dataBookingScheduled,
                                borderColor: '#FF9ABC',
                                backgroundColor: 'rgba(255, 154, 188, 0.20)',
                                pointBackgroundColor: '#FF9ABC',
                                pointBorderColor: '#FF9ABC',
                                fill: true,
                                tension: 0.35,
                                borderWidth: 2.5,
                                pointRadius: 3.5,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: { color: '#46571A', font: { family: 'Plus Jakarta Sans', size: 11, weight: 'bold' } }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { stepSize: 1, color: '#46571A', font: { family: 'Plus Jakarta Sans', size: 10 } },
                                grid: { color: 'rgba(120, 149, 106, 0.15)' }
                            },
                            x: {
                                ticks: { color: '#46571A', font: { family: 'Plus Jakarta Sans', size: 10 } },
                                grid: { display: false }
                            }
                        }
                    }
                });
            }

            // 2. Bar Chart: Pendapatan Masuk vs Penarikan Payout Perawat
            const ctxBar = document.getElementById('revenuePayoutBarChart');
            if (ctxBar) {
                new Chart(ctxBar.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Total Transaksi Masuk (Rp)',
                                data: dataRevenueIn,
                                backgroundColor: '#6E8555',
                                borderColor: '#46571A',
                                borderRadius: 6,
                            },
                            {
                                label: 'Penarikan Saldo / Payout Perawat (Rp)',
                                data: dataPayoutOut,
                                backgroundColor: '#FF9ABC',
                                borderColor: '#FF9ABC',
                                borderRadius: 6,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: { color: '#46571A', font: { family: 'Plus Jakarta Sans', size: 11, weight: 'bold' } }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': Rp ' + Number(context.raw).toLocaleString('id-ID');
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'Rp ' + Number(value).toLocaleString('id-ID');
                                    },
                                    color: '#46571A',
                                    font: { family: 'Plus Jakarta Sans', size: 10 }
                                },
                                grid: { color: 'rgba(120, 149, 106, 0.15)' }
                            },
                            x: {
                                ticks: { color: '#46571A', font: { family: 'Plus Jakarta Sans', size: 10 } },
                                grid: { display: false }
                            }
                        }
                    }
                });
            }

            // 3. Donut Chart: Distribusi Status Pesanan
            const ctxDonut = document.getElementById('orderStatusDonutChart');
            if (ctxDonut) {
                const counts = statusDist.counts || [0, 0, 0, 0];
                const totalAll = counts.reduce((a, b) => a + b, 0);
                new Chart(ctxDonut.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: statusDist.labels || ['Selesai', 'Berlangsung', 'Terjadwal', 'Dibatalkan/Ditolak'],
                        datasets: [{
                            data: totalAll > 0 ? counts : [1, 0, 0, 0],
                            backgroundColor: totalAll > 0
                                ? ['#D1EB8F', '#78956A', '#46571A', '#FF9ABC']
                                : ['#F9F4EA', '#F9F4EA', '#F9F4EA', '#F9F4EA'],
                            borderWidth: 2,
                            borderColor: '#ffffff',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '66%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { font: { family: 'Plus Jakarta Sans', size: 10, weight: 'bold' }, boxWidth: 10 }
                            }
                        }
                    }
                });
            }
        });
    </script>

    <!-- MONITORING PERAWAT DISUSPEND SEMENTARA & LOG AKTIVITAS SISTEM -->
    <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Panel 1: Perawat Disuspend Sementara (Sanksi 1 Hari) -->
        <div class="bg-[#FFFDF9] rounded-3xl border border-[#78956A] p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-[#78956A]/30 pb-3">
                <div>
                    <h3 class="text-sm font-extrabold text-[#46571A]">Perawat Disuspend Sementara (Sanksi 1 Hari)</h3>
                    <p class="text-[11px] text-[#78956A]">Otomatis disuspend jika tidak berangkat hingga jam kunjungan berakhir.</p>
                </div>
                <span class="inline-flex items-center justify-center whitespace-nowrap shrink-0 px-3 py-1 rounded-full text-xs font-bold bg-[#FF9ABC] text-[#46571A] border border-[#78956A]">
                    {{ ($suspendedNurses ?? collect())->count() }} Nakes
                </span>
            </div>

            @if (($suspendedNurses ?? collect())->isEmpty())
                <div class="p-6 text-center text-xs text-[#78956A] bg-[#F9F4EA] rounded-2xl border border-[#78956A]/30">
                    Tidak ada perawat yang sedang dalam masa suspend sementara saat ini.
                </div>
            @else
                <div class="space-y-3 max-h-80 overflow-y-auto">
                    @foreach ($suspendedNurses as $sp)
                        <div class="p-4 rounded-2xl bg-[#FF9ABC]/20 border border-[#78956A] flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                            <div class="space-y-1">
                                <span class="font-extrabold text-[#46571A] block">{{ $sp->user?->name ?? 'Perawat #' . $sp->user_id }}</span>
                                <span class="text-[11px] text-[#46571A]/85 block">{{ $sp->suspension_reason ?? 'Sanksi ketidakhadiran kunjungan.' }}</span>
                                <span class="text-[10px] font-bold text-[#78956A] block">
                                    Berakhir: {{ $sp->suspended_until?->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                                </span>
                            </div>
                            <form action="{{ route('admin.nurses.unsuspend', $sp->id) }}" method="POST" class="shrink-0">
                                @csrf
                                <button type="submit" class="px-3.5 py-2 rounded-xl bg-[#D1EB8F] hover:bg-[#FF9ABC] text-[#46571A] border border-[#78956A] text-xs font-bold transition cursor-pointer">
                                    Cabut Suspend
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Panel 2: Log Aktivitas Sistem & Pelanggaran Nakes -->
        <div class="bg-[#FFFDF9] rounded-3xl border border-[#78956A] p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-[#78956A]/30 pb-3">
                <div>
                    <h3 class="text-sm font-extrabold text-[#46571A]">Log Aktivitas &amp; Sanksi Sistem (Audit Log)</h3>
                    <p class="text-[11px] text-[#78956A]">Rekam jejak aktivitas pesanan, pembayaran, pembatalan, dan auto-suspend.</p>
                </div>
                <span class="inline-flex items-center justify-center whitespace-nowrap shrink-0 px-3 py-1 rounded-full text-xs font-bold bg-[#D1EB8F] text-[#46571A] border border-[#78956A]">
                    {{ ($auditLogs ?? collect())->count() }} Terbaru
                </span>
            </div>

            <div class="space-y-2 max-h-80 overflow-y-auto divide-y divide-[#78956A]/20 text-xs">
                @forelse ($auditLogs ?? [] as $log)
                    <div class="pt-2.5 first:pt-0 flex items-start justify-between gap-3">
                        <div class="space-y-0.5 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="inline-flex items-center justify-center whitespace-nowrap shrink-0 px-2.5 py-0.5 rounded-full text-[10px] font-bold border border-[#78956A] {{ str_contains($log->action, 'suspend') || str_contains($log->action, 'cancel') ? 'bg-[#FF9ABC]/40 text-[#46571A]' : 'bg-[#D1EB8F]/60 text-[#46571A]' }}">
                                    {{ $log->action }}
                                </span>
                                <span class="font-bold text-[#46571A] truncate">{{ $log->user?->name ?? 'Sistem Otomatis' }}</span>
                            </div>
                            <p class="text-[11px] text-[#78956A] truncate">
                                {{ is_array($log->new_values) ? ($log->new_values['reason'] ?? ($log->new_values['order_number'] ?? json_encode($log->new_values))) : '-' }}
                            </p>
                        </div>
                        <span class="text-[10px] text-[#78956A] whitespace-nowrap shrink-0">
                            {{ $log->created_at?->timezone('Asia/Jakarta')->format('d M, H:i') }} WIB
                        </span>
                    </div>
                @empty
                    <div class="p-6 text-center text-[#78956A] text-xs">Belum ada log aktivitas tercatat.</div>
                @endforelse
            </div>
        </div>
    </div>
</x-admin-layout>
