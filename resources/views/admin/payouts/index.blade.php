@extends('admin.layout')

@section('title', 'Manajemen Otorisasi Payout')

@section('content')
<div class="space-y-8" x-data="{ 
    transferModal: false, 
    rejectModal: false, 
    activePayout: {
        id: null,
        amountFormatted: '',
        nurseName: '',
        bankName: '',
        accountNumber: '',
        accountHolder: ''
    },
    openTransfer(id, formatted, nurse, bank, number, holder) {
        this.activePayout = { id, amountFormatted: formatted, nurseName: nurse, bankName: bank, accountNumber: number, accountHolder: holder };
        this.transferModal = true;
    },
    openReject(id, formatted, nurse) {
        this.activePayout = { id, amountFormatted: formatted, nurseName: nurse, bankName: '', accountNumber: '', accountHolder: '' };
        this.rejectModal = true;
    }
}">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-[#46571A] tracking-tight">Otorisasi Payout Perawat</h1>
            <p class="text-[#78956A] text-sm mt-1">Review dan verifikasi pencairan saldo perawat beserta input bukti transfer.</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-3xl border border-[#78956A]/50 shadow-sm">
            <div class="text-xs font-bold text-[#78956A] uppercase tracking-wider">Menunggu Diproses</div>
            <div class="text-2xl font-black text-[#46571A] mt-2">{{ $stats['pending'] }} Permintaan</div>
            <div class="text-xs text-[#78956A] mt-1 font-semibold">Rp {{ number_format($stats['total_pending_amount'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-[#78956A]/50 shadow-sm">
            <div class="text-xs font-bold text-[#78956A] uppercase tracking-wider">Berhasil Ditransfer</div>
            <div class="text-2xl font-black text-[#78956A] mt-2">{{ $stats['transferred'] }} Selesai</div>
            <div class="text-xs text-[#78956A] mt-1">Dana telah cair ke rekening</div>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-[#78956A]/50 shadow-sm">
            <div class="text-xs font-bold text-[#46571A] uppercase tracking-wider">Ditolak</div>
            <div class="text-2xl font-black text-[#46571A] mt-2">{{ $stats['rejected'] }} Permintaan</div>
            <div class="text-xs text-[#78956A] mt-1">Saldo dikembalikan ke perawat</div>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-[#78956A]/50 shadow-sm">
            <div class="text-xs font-bold text-[#78956A] uppercase tracking-wider">Total Penarikan</div>
            <div class="text-2xl font-black text-[#46571A] mt-2">{{ $stats['pending'] + $stats['transferred'] + $stats['rejected'] }}</div>
            <div class="text-xs text-[#78956A] mt-1">Seluruh riwayat</div>
        </div>
    </div>

    <!-- Filter Status -->
    <div class="flex items-center gap-2 border-b border-[#78956A]/50 pb-4 overflow-x-auto">
        <a href="{{ route('admin.payouts.index') }}" class="inline-flex items-center justify-center whitespace-nowrap shrink-0 px-4 py-2 rounded-xl text-sm font-semibold transition {{ !request('status') ? 'bg-[#78956A] text-[#F9F4EA]' : 'bg-white text-[#46571A] border border-[#78956A]/30 hover:bg-[#F9F4EA]' }}">
            Semua
        </a>
        <a href="{{ route('admin.payouts.index', ['status' => 'pending']) }}" class="inline-flex items-center justify-center whitespace-nowrap shrink-0 px-4 py-2 rounded-xl text-sm font-semibold transition {{ request('status') === 'pending' ? 'bg-[#FF9ABC] text-[#46571A]' : 'bg-white text-[#46571A]/85 border border-[#78956A]/30 hover:bg-[#F9F4EA]' }}">
            Pending Review ({{ $stats['pending'] }})
        </a>
        <a href="{{ route('admin.payouts.index', ['status' => 'transferred']) }}" class="inline-flex items-center justify-center whitespace-nowrap shrink-0 px-4 py-2 rounded-xl text-sm font-semibold transition {{ request('status') === 'transferred' ? 'bg-[#FF9ABC] text-[#46571A]' : 'bg-white text-[#46571A]/85 border border-[#78956A]/30 hover:bg-[#F9F4EA]' }}">
            Ditransfer ({{ $stats['transferred'] }})
        </a>
        <a href="{{ route('admin.payouts.index', ['status' => 'rejected']) }}" class="inline-flex items-center justify-center whitespace-nowrap shrink-0 px-4 py-2 rounded-xl text-sm font-semibold transition {{ request('status') === 'rejected' ? 'bg-[#FF9ABC] text-[#46571A]' : 'bg-white text-[#46571A]/85 border border-[#78956A]/30 hover:bg-[#F9F4EA]' }}">
            Ditolak ({{ $stats['rejected'] }})
        </a>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-3xl border border-[#78956A]/50 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-[#F9F4EA] border-b border-[#78956A]/50 text-[#78956A] text-xs font-bold uppercase tracking-wider">
                        <th class="px-4 py-3 pl-6 whitespace-nowrap">ID & Tanggal</th>
                        <th class="px-4 py-3 whitespace-nowrap">Perawat</th>
                        <th class="px-4 py-3 whitespace-nowrap">Rekening Tujuan</th>
                        <th class="px-4 py-3 text-right whitespace-nowrap">Nominal</th>
                        <th class="px-4 py-3 whitespace-nowrap">Status</th>
                        <th class="px-4 py-3 pr-6 text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#78956A]/30">
                    @forelse($payouts as $p)
                        <tr class="hover:bg-[#F9F4EA] transition align-middle">
                            <td class="px-4 py-3 pl-6 align-middle font-mono text-xs whitespace-nowrap">
                                <span class="font-bold text-[#46571A]">#PO-{{ $p->id }}</span>
                                <div class="text-[#78956A] text-[11px]">{{ $p->created_at->format('d/m/Y H:i') }}</div>
                            </td>
                            <td class="px-4 py-3 align-middle whitespace-nowrap">
                                <div class="font-semibold text-[#46571A]">{{ $p->nurseUser->name ?? '-' }}</div>
                                <div class="text-xs text-[#78956A]">{{ $p->nurseUser->email ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 align-middle whitespace-nowrap">
                                <div class="font-bold text-[#46571A]">{{ $p->bank_name }} - {{ $p->account_number }}</div>
                                <div class="text-xs text-[#78956A]">a.n. {{ $p->account_holder }}</div>
                            </td>
                            <td class="px-4 py-3 align-middle text-right font-black text-[#46571A] whitespace-nowrap">
                                Rp {{ number_format($p->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 align-middle whitespace-nowrap">
                                @if($p->status === 'pending')
                                    <span class="inline-flex items-center justify-center whitespace-nowrap shrink-0 px-3 py-1 rounded-full text-xs font-semibold bg-[#D1EB8F]/70 text-[#46571A]">
                                        Pending Review
                                    </span>
                                @elseif($p->status === 'transferred')
                                    <span class="inline-flex items-center justify-center whitespace-nowrap shrink-0 px-3 py-1 rounded-full text-xs font-semibold bg-[#D1EB8F] text-[#46571A]">
                                        Ditransfer
                                    </span>
                                    <div class="text-[11px] text-[#78956A] mt-1">{{ optional($p->transferred_at)->format('d/m/Y') }}</div>
                                @elseif($p->status === 'rejected')
                                    <span class="inline-flex items-center justify-center whitespace-nowrap shrink-0 px-3 py-1 rounded-full text-xs font-semibold bg-[#FF9ABC]/40 text-[#46571A]">
                                        Ditolak
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 pr-6 align-middle text-right whitespace-nowrap">
                                @if($p->status === 'pending')
                                    <div class="flex items-center justify-end gap-2">
                                        <button 
                                            type="button"
                                            @click="openTransfer({{ $p->id }}, 'Rp {{ number_format($p->amount, 0, ',', '.') }}', '{{ addslashes($p->nurseUser->name ?? $p->nurse->name ?? 'Perawat') }}', '{{ addslashes($p->bank_name) }}', '{{ addslashes($p->account_number) }}', '{{ addslashes($p->account_holder) }}')"
                                            class="inline-flex items-center justify-center whitespace-nowrap shrink-0 px-3 py-1.5 bg-[#FF9ABC] hover:bg-[#FF9ABC]/85 text-[#46571A] rounded-xl text-xs font-bold shadow-sm transition">
                                            Transfer
                                        </button>
                                        <button 
                                            type="button"
                                            @click="openReject({{ $p->id }}, 'Rp {{ number_format($p->amount, 0, ',', '.') }}', '{{ addslashes($p->nurseUser->name ?? $p->nurse->name ?? 'Perawat') }}')"
                                            class="inline-flex items-center justify-center whitespace-nowrap shrink-0 px-3 py-1.5 bg-[#FF9ABC]/25 hover:bg-[#FF9ABC]/40 text-[#46571A] border border-[#FF9ABC] rounded-xl text-xs font-bold transition">
                                            Tolak
                                        </button>
                                    </div>
                                @elseif($p->status === 'transferred' && $p->proof_path)
                                    <a href="{{ asset('storage/' . $p->proof_path) }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-[#78956A] hover:text-[#46571A] font-semibold underline whitespace-nowrap">
                                        Lihat Bukti
                                    </a>
                                @elseif($p->status === 'rejected')
                                    <span class="text-xs text-[#78956A] italic" title="{{ $p->rejection_reason }}">
                                        {{ Str::limit($p->rejection_reason, 25) }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-[#78956A]">Tidak ada permohonan penarikan saldo.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-[#78956A]/50">
            {{ $payouts->links() }}
        </div>
    </div>

    <!-- Modal Transfer -->
    <div x-show="transferModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-[#46571A]/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl p-6 sm:p-8 space-y-6" @click.away="transferModal = false">
            <div>
                <h3 class="text-xl font-black text-[#46571A]">Upload Bukti Transfer Dana</h3>
                <p class="text-xs text-[#78956A] mt-1">
                    Konfirmasi transfer untuk <span class="font-bold text-[#46571A]" x-text="activePayout.nurseName"></span> sebesar <span class="font-bold text-[#78956A]" x-text="activePayout.amountFormatted"></span>.
                </p>
                <div class="mt-3 p-3 bg-[#F9F4EA] rounded-2xl text-xs text-[#46571A]/85 space-y-1">
                    <div>Bank: <strong x-text="activePayout.bankName"></strong></div>
                    <div>No Rekening: <strong x-text="activePayout.accountNumber"></strong></div>
                    <div>Atas Nama: <strong x-text="activePayout.accountHolder"></strong></div>
                </div>
            </div>

            <form x-bind:action="'/admin/payouts/' + activePayout.id + '/transfer'" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-[#46571A] uppercase mb-1">Tanggal Transfer <span class="text-[#46571A]">*</span></label>
                    <input type="date" name="transferred_at" value="{{ date('Y-m-d') }}" required class="w-full text-sm border-[#78956A] rounded-xl focus:ring-[#78956A] focus:border-[#78956A]">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#46571A] uppercase mb-1">Upload File Bukti Transfer (JPG/PNG/PDF, max 2MB) <span class="text-[#46571A]">*</span></label>
                    <input type="file" name="proof_file" accept=".jpg,.jpeg,.png,.pdf" required class="w-full text-sm text-[#78956A] file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#D1EB8F]/45 file:text-[#46571A] hover:file:bg-[#D1EB8F]">
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t border-[#78956A]/50">
                    <button type="button" @click="transferModal = false" class="px-4 py-2 text-[#46571A]/85 hover:bg-[#F9F4EA] rounded-xl text-sm font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-[#FF9ABC] hover:bg-[#FF9ABC]/85 text-[#46571A] rounded-xl text-sm font-bold shadow-md shadow-[#78956A]/25">Konfirmasi Transfer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Reject -->
    <div x-show="rejectModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-[#46571A]/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl p-6 sm:p-8 space-y-6" @click.away="rejectModal = false">
            <div>
                <h3 class="text-xl font-black text-[#46571A]">Tolak Permintaan Penarikan</h3>
                <p class="text-xs text-[#78956A] mt-1">
                    Saldo yang ditahan sebesar <span class="font-bold text-[#46571A]" x-text="activePayout.amountFormatted"></span> untuk <span class="font-bold text-[#46571A]" x-text="activePayout.nurseName"></span> akan otomatis dikembalikan ke saldo dompet perawat.
                </p>
            </div>

            <form x-bind:action="'/admin/payouts/' + activePayout.id + '/reject'" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-[#46571A] uppercase mb-1">Alasan Penolakan <span class="text-[#46571A]">*</span></label>
                    <textarea name="rejection_reason" rows="3" required placeholder="Contoh: Nomor rekening tidak sesuai dengan nama pemilik akun..." class="w-full text-sm border-[#78956A] rounded-xl focus:ring-[#78956A] focus:border-[#FF9ABC]"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t border-[#78956A]/50">
                    <button type="button" @click="rejectModal = false" class="px-4 py-2 text-[#46571A]/85 hover:bg-[#F9F4EA] rounded-xl text-sm font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-[#FF9ABC] hover:bg-[#FF9ABC]/85 text-[#46571A] rounded-xl text-sm font-bold shadow-md shadow-[#78956A]/25">Tolak & Kembalikan Saldo</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
