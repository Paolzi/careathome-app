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
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Otorisasi Payout Perawat</h1>
            <p class="text-slate-500 text-sm mt-1">Review dan verifikasi pencairan saldo perawat beserta input bukti transfer.</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm">
            <div class="text-xs font-bold text-amber-600 uppercase tracking-wider">Menunggu Diproses</div>
            <div class="text-2xl font-black text-slate-900 mt-2">{{ $stats['pending'] }} Permintaan</div>
            <div class="text-xs text-amber-600 mt-1 font-semibold">Rp {{ number_format($stats['total_pending_amount'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm">
            <div class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Berhasil Ditransfer</div>
            <div class="text-2xl font-black text-emerald-600 mt-2">{{ $stats['transferred'] }} Selesai</div>
            <div class="text-xs text-slate-400 mt-1">Dana telah cair ke rekening</div>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm">
            <div class="text-xs font-bold text-rose-600 uppercase tracking-wider">Ditolak</div>
            <div class="text-2xl font-black text-rose-600 mt-2">{{ $stats['rejected'] }} Permintaan</div>
            <div class="text-xs text-slate-400 mt-1">Saldo dikembalikan ke perawat</div>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm">
            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Penarikan</div>
            <div class="text-2xl font-black text-slate-900 mt-2">{{ $stats['pending'] + $stats['transferred'] + $stats['rejected'] }}</div>
            <div class="text-xs text-slate-400 mt-1">Seluruh riwayat</div>
        </div>
    </div>

    <!-- Filter Status -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-4 overflow-x-auto">
        <a href="{{ route('admin.payouts.index') }}" class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ !request('status') ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 hover:bg-slate-100' }}">
            Semua
        </a>
        <a href="{{ route('admin.payouts.index', ['status' => 'pending']) }}" class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ request('status') === 'pending' ? 'bg-amber-500 text-white' : 'bg-white text-slate-600 hover:bg-slate-100' }}">
            Pending Review ({{ $stats['pending'] }})
        </a>
        <a href="{{ route('admin.payouts.index', ['status' => 'transferred']) }}" class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ request('status') === 'transferred' ? 'bg-emerald-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-100' }}">
            Ditransfer ({{ $stats['transferred'] }})
        </a>
        <a href="{{ route('admin.payouts.index', ['status' => 'rejected']) }}" class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ request('status') === 'rejected' ? 'bg-rose-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-100' }}">
            Ditolak ({{ $stats['rejected'] }})
        </a>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-100 text-slate-500 text-xs font-bold uppercase tracking-wider">
                        <th class="p-4 pl-6">ID & Tanggal</th>
                        <th class="p-4">Perawat</th>
                        <th class="p-4">Rekening Tujuan</th>
                        <th class="p-4 text-right">Nominal</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 pr-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payouts as $p)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="p-4 pl-6 font-mono text-xs">
                                <span class="font-bold text-slate-900">#PO-{{ $p->id }}</span>
                                <div class="text-slate-400 text-[11px]">{{ $p->created_at->format('d/m/Y H:i') }}</div>
                            </td>
                            <td class="p-4">
                                <div class="font-semibold text-slate-900">{{ $p->nurseUser->name ?? '-' }}</div>
                                <div class="text-xs text-slate-400">{{ $p->nurseUser->email ?? '-' }}</div>
                            </td>
                            <td class="p-4">
                                <div class="font-bold text-slate-800">{{ $p->bank_name }} - {{ $p->account_number }}</div>
                                <div class="text-xs text-slate-500">a.n. {{ $p->account_holder }}</div>
                            </td>
                            <td class="p-4 text-right font-black text-slate-900">
                                Rp {{ number_format($p->amount, 0, ',', '.') }}
                            </td>
                            <td class="p-4">
                                @if($p->status === 'pending')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                        Pending Review
                                    </span>
                                @elseif($p->status === 'transferred')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        Ditransfer
                                    </span>
                                    <div class="text-[11px] text-slate-400 mt-1">{{ optional($p->transferred_at)->format('d/m/Y') }}</div>
                                @elseif($p->status === 'rejected')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                                        Ditolak
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 pr-6 text-right">
                                @if($p->status === 'pending')
                                    <div class="flex items-center justify-end gap-2">
                                        <button 
                                            type="button"
                                            @click="openTransfer({{ $p->id }}, 'Rp {{ number_format($p->amount, 0, ',', '.') }}', '{{ addslashes($p->nurseUser->name ?? $p->nurse->name ?? 'Perawat') }}', '{{ addslashes($p->bank_name) }}', '{{ addslashes($p->account_number) }}', '{{ addslashes($p->account_holder) }}')"
                                            class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm transition">
                                            Transfer
                                        </button>
                                        <button 
                                            type="button"
                                            @click="openReject({{ $p->id }}, 'Rp {{ number_format($p->amount, 0, ',', '.') }}', '{{ addslashes($p->nurseUser->name ?? $p->nurse->name ?? 'Perawat') }}')"
                                            class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 rounded-xl text-xs font-bold transition">
                                            Tolak
                                        </button>
                                    </div>
                                @elseif($p->status === 'transferred' && $p->proof_path)
                                    <a href="{{ asset('storage/' . $p->proof_path) }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-emerald-600 hover:text-emerald-700 font-semibold underline">
                                        Lihat Bukti
                                    </a>
                                @elseif($p->status === 'rejected')
                                    <span class="text-xs text-slate-400 italic" title="{{ $p->rejection_reason }}">
                                        {{ Str::limit($p->rejection_reason, 25) }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400">Tidak ada permohonan penarikan saldo.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $payouts->links() }}
        </div>
    </div>

    <!-- Modal Transfer -->
    <div x-show="transferModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl p-6 sm:p-8 space-y-6" @click.away="transferModal = false">
            <div>
                <h3 class="text-xl font-black text-slate-900">Upload Bukti Transfer Dana</h3>
                <p class="text-xs text-slate-500 mt-1">
                    Konfirmasi transfer untuk <span class="font-bold text-slate-800" x-text="activePayout.nurseName"></span> sebesar <span class="font-bold text-emerald-600" x-text="activePayout.amountFormatted"></span>.
                </p>
                <div class="mt-3 p-3 bg-slate-50 rounded-2xl text-xs text-slate-600 space-y-1">
                    <div>Bank: <strong x-text="activePayout.bankName"></strong></div>
                    <div>No Rekening: <strong x-text="activePayout.accountNumber"></strong></div>
                    <div>Atas Nama: <strong x-text="activePayout.accountHolder"></strong></div>
                </div>
            </div>

            <form x-bind:action="'/admin/payouts/' + activePayout.id + '/transfer'" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tanggal Transfer <span class="text-rose-500">*</span></label>
                    <input type="date" name="transferred_at" value="{{ date('Y-m-d') }}" required class="w-full text-sm border-slate-300 rounded-xl focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Upload File Bukti Transfer (JPG/PNG/PDF, max 2MB) <span class="text-rose-500">*</span></label>
                    <input type="file" name="proof_file" accept=".jpg,.jpeg,.png,.pdf" required class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="transferModal = false" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-xl text-sm font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold shadow-md shadow-emerald-500/20">Konfirmasi Transfer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Reject -->
    <div x-show="rejectModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl p-6 sm:p-8 space-y-6" @click.away="rejectModal = false">
            <div>
                <h3 class="text-xl font-black text-rose-600">Tolak Permintaan Penarikan</h3>
                <p class="text-xs text-slate-500 mt-1">
                    Saldo yang ditahan sebesar <span class="font-bold text-slate-800" x-text="activePayout.amountFormatted"></span> untuk <span class="font-bold text-slate-800" x-text="activePayout.nurseName"></span> akan otomatis dikembalikan ke saldo dompet perawat.
                </p>
            </div>

            <form x-bind:action="'/admin/payouts/' + activePayout.id + '/reject'" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Alasan Penolakan <span class="text-rose-500">*</span></label>
                    <textarea name="rejection_reason" rows="3" required placeholder="Contoh: Nomor rekening tidak sesuai dengan nama pemilik akun..." class="w-full text-sm border-slate-300 rounded-xl focus:ring-rose-500 focus:border-rose-500"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="rejectModal = false" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-xl text-sm font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-sm font-bold shadow-md shadow-rose-500/20">Tolak & Kembalikan Saldo</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
