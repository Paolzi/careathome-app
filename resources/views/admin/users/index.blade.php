@extends('admin.layout')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="space-y-8" x-data="{ 
    suspendModal: false, 
    activeUserId: null, 
    activeUserName: '',
    openSuspend(id, name) {
        this.activeUserId = id;
        this.activeUserName = name;
        this.suspendModal = true;
    }
}">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Manajemen Pengguna</h1>
            <p class="text-slate-500 text-sm mt-1">Daftar semua akun sistem (Admin, Perawat, Pasien) dan kontrol status penangguhan.</p>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Cari Nama / Email</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari pengguna..." class="w-full text-sm border-slate-300 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Role Akun</label>
                <select name="role" class="w-full text-sm border-slate-300 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Semua Role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Status Akun</label>
                <select name="status" class="w-full text-sm border-slate-300 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Ditangguhkan (Suspended)</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-emerald-600 text-white rounded-xl text-sm font-bold hover:bg-emerald-700 transition">Filter</button>
                <a href="{{ route('admin.users.index') }}" class="px-3 py-2 bg-slate-100 text-slate-600 rounded-xl text-sm font-semibold hover:bg-slate-200 transition">Reset</a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-100 text-slate-500 text-xs font-bold uppercase tracking-wider">
                        <th class="p-4 pl-6">Pengguna</th>
                        <th class="p-4">Role</th>
                        <th class="p-4">Status & Info</th>
                        <th class="p-4">Terdaftar</th>
                        <th class="p-4 pr-6 text-right">Aksi Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $u)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="p-4 pl-6">
                                <div class="font-bold text-slate-900">{{ $u->name }}</div>
                                <div class="text-xs text-slate-400">{{ $u->email }}</div>
                            </td>
                            <td class="p-4">
                                @foreach($u->roles as $role)
                                    @php
                                        $badgeColor = match($role->name) {
                                            'admin' => 'bg-purple-100 text-purple-800',
                                            'perawat' => 'bg-emerald-100 text-emerald-800',
                                            default => 'bg-blue-100 text-blue-800',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $badgeColor }}">
                                        {{ ucfirst($role->name) }}
                                    </span>
                                @endforeach
                            </td>
                            <td class="p-4">
                                @if($u->status === 'active')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Aktif
                                    </span>
                                    @if($u->nurseProfile)
                                        <div class="text-[11px] text-slate-400 mt-1">
                                            Status Nakes: {{ ucfirst($u->nurseProfile->verification_status) }} 
                                            ({{ $u->nurseProfile->is_online ? 'Sedang Bekerja' : 'Offline' }})
                                        </div>
                                    @endif
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Suspended
                                    </span>
                                    @if($u->suspended_reason)
                                        <div class="text-[11px] text-rose-500 mt-1 italic max-w-xs truncate" title="{{ $u->suspended_reason }}">
                                            Alasan: {{ $u->suspended_reason }}
                                        </div>
                                    @endif
                                @endif
                            </td>
                            <td class="p-4 text-xs text-slate-500">
                                {{ $u->created_at->format('d M Y') }}
                            </td>
                            <td class="p-4 pr-6 text-right">
                                @if(auth()->id() === $u->id)
                                    <span class="text-xs text-slate-400 italic">Akun Anda</span>
                                @else
                                    @if($u->status === 'active')
                                        <button 
                                            type="button"
                                            @click="openSuspend({{ $u->id }}, '{{ addslashes($u->name) }}')"
                                            class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 rounded-xl text-xs font-bold transition">
                                            Tangguhkan
                                        </button>
                                    @else
                                        <form action="{{ route('admin.users.toggleStatus', $u->id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="status" value="active">
                                            <button type="submit" onclick="return confirm('Aktifkan kembali akun pengguna ini?')" class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-xl text-xs font-bold transition">
                                                Aktifkan
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-400">Tidak ada pengguna ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $users->links() }}
        </div>
    </div>

    <!-- Modal Suspend -->
    <div x-show="suspendModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl p-6 sm:p-8 space-y-6" @click.away="suspendModal = false">
            <div>
                <h3 class="text-xl font-black text-rose-600">Tangguhkan Akun Pengguna</h3>
                <p class="text-xs text-slate-500 mt-1">
                    Pengguna <strong class="text-slate-800" x-text="activeUserName"></strong> tidak akan bisa login ke aplikasi. Jika merupakan perawat, status kerja otomatis diubah menjadi OFFLINE.
                </p>
            </div>

            <form x-bind:action="'/admin/users/' + activeUserId + '/toggle-status'" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="status" value="suspended">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Alasan Penangguhan <span class="text-rose-500">*</span></label>
                    <textarea name="suspended_reason" rows="3" required placeholder="Jelaskan alasan pelanggaran aturan / suspensi..." class="w-full text-sm border-slate-300 rounded-xl focus:ring-rose-500 focus:border-rose-500"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="suspendModal = false" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-xl text-sm font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-sm font-bold shadow-md shadow-rose-500/20">Konfirmasi Tangguhkan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
