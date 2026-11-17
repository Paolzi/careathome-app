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
            <h1 class="text-2xl font-black text-[#46571A] tracking-tight">Manajemen Pengguna</h1>
            <p class="text-[#78956A] text-sm mt-1">Daftar semua akun sistem (Admin, Perawat, Pasien) dan kontrol status penangguhan.</p>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white p-6 rounded-3xl border border-[#78956A]/50 shadow-sm">
        <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-xs font-bold text-[#78956A] uppercase mb-1">Cari Nama / Email</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari pengguna..." class="w-full text-sm border-[#78956A] rounded-xl focus:border-[#78956A] focus:ring-[#78956A]">
            </div>
            <div>
                <label class="block text-xs font-bold text-[#78956A] uppercase mb-1">Role Akun</label>
                <select name="role" class="w-full text-sm border-[#78956A] rounded-xl focus:border-[#78956A] focus:ring-[#78956A]">
                    <option value="">Semua Role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-[#78956A] uppercase mb-1">Status Akun</label>
                <select name="status" class="w-full text-sm border-[#78956A] rounded-xl focus:border-[#78956A] focus:ring-[#78956A]">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Ditangguhkan (Suspended)</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-[#FF9ABC] text-[#46571A] rounded-xl text-sm font-bold hover:bg-[#FF9ABC] transition">Filter</button>
                <a href="{{ route('admin.users.index') }}" class="px-3 py-2 bg-[#F9F4EA] text-[#46571A]/85 rounded-xl text-sm font-semibold hover:bg-[#D1EB8F]/50 transition">Reset</a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-3xl border border-[#78956A]/50 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-[#F9F4EA] border-b border-[#78956A]/50 text-[#78956A] text-xs font-bold uppercase tracking-wider">
                        <th class="p-4 pl-6">Pengguna</th>
                        <th class="p-4">Role</th>
                        <th class="p-4">Status & Info</th>
                        <th class="p-4">Terdaftar</th>
                        <th class="p-4 pr-6 text-right">Aksi Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#78956A]/30">
                    @forelse($users as $u)
                        <tr class="hover:bg-[#F9F4EA] transition">
                            <td class="p-4 pl-6">
                                <div class="font-bold text-[#46571A]">{{ $u->name }}</div>
                                <div class="text-xs text-[#78956A]">{{ $u->email }}</div>
                            </td>
                            <td class="p-4">
                                @foreach($u->roles as $role)
                                    @php
                                        $badgeColor = match($role->name) {
                                            'admin' => 'bg-[#D1EB8F] text-[#46571A]',
                                            'perawat' => 'bg-[#D1EB8F] text-[#46571A]',
                                            default => 'bg-[#D1EB8F] text-[#46571A]',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $badgeColor }}">
                                        {{ ucfirst($role->name) }}
                                    </span>
                                @endforeach
                            </td>
                            <td class="p-4">
                                @if($u->status === 'active')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-[#D1EB8F]/45 text-[#46571A] border border-[#78956A]/50">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#FF9ABC]"></span>
                                        Aktif
                                    </span>
                                    @if($u->nurseProfile)
                                        <div class="text-[11px] text-[#78956A] mt-1">
                                            Status Nakes: {{ ucfirst($u->nurseProfile->verification_status) }} 
                                            ({{ $u->nurseProfile->is_online ? 'Sedang Bekerja' : 'Offline' }})
                                        </div>
                                    @endif
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-[#FF9ABC]/25 text-[#46571A] border border-[#FF9ABC]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#FF9ABC]"></span>
                                        Suspended
                                    </span>
                                    @if($u->suspended_reason)
                                        <div class="text-[11px] text-[#46571A] mt-1 italic max-w-xs truncate" title="{{ $u->suspended_reason }}">
                                            Alasan: {{ $u->suspended_reason }}
                                        </div>
                                    @endif
                                @endif
                            </td>
                            <td class="p-4 text-xs text-[#78956A]">
                                {{ $u->created_at->format('d M Y') }}
                            </td>
                            <td class="p-4 pr-6 text-right">
                                @if(auth()->id() === $u->id)
                                    <span class="text-xs text-[#78956A] italic">Akun Anda</span>
                                @else
                                    @if($u->status === 'active')
                                        <button 
                                            type="button"
                                            @click="openSuspend({{ $u->id }}, '{{ addslashes($u->name) }}')"
                                            class="px-3 py-1.5 bg-[#FF9ABC]/25 hover:bg-[#FF9ABC]/40 text-[#46571A] border border-[#FF9ABC] rounded-xl text-xs font-bold transition">
                                            Tangguhkan
                                        </button>
                                    @else
                                        <form action="{{ route('admin.users.toggleStatus', $u->id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="status" value="active">
                                            <button type="submit" onclick="return confirm('Aktifkan kembali akun pengguna ini?')" class="px-3 py-1.5 bg-[#D1EB8F]/45 hover:bg-[#D1EB8F] text-[#46571A] border border-[#78956A]/50 rounded-xl text-xs font-bold transition">
                                                Aktifkan
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-[#78956A]">Tidak ada pengguna ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-[#78956A]/50">
            {{ $users->links() }}
        </div>
    </div>

    <!-- Modal Suspend -->
    <div x-show="suspendModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-[#46571A]/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl p-6 sm:p-8 space-y-6" @click.away="suspendModal = false">
            <div>
                <h3 class="text-xl font-black text-[#46571A]">Tangguhkan Akun Pengguna</h3>
                <p class="text-xs text-[#78956A] mt-1">
                    Pengguna <strong class="text-[#46571A]" x-text="activeUserName"></strong> tidak akan bisa login ke aplikasi. Jika merupakan perawat, status kerja otomatis diubah menjadi OFFLINE.
                </p>
            </div>

            <form x-bind:action="'/admin/users/' + activeUserId + '/toggle-status'" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="status" value="suspended">
                <div>
                    <label class="block text-xs font-bold text-[#46571A] uppercase mb-1">Alasan Penangguhan <span class="text-[#46571A]">*</span></label>
                    <textarea name="suspended_reason" rows="3" required placeholder="Jelaskan alasan pelanggaran aturan / suspensi..." class="w-full text-sm border-[#78956A] rounded-xl focus:ring-[#78956A] focus:border-[#FF9ABC]"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t border-[#78956A]/50">
                    <button type="button" @click="suspendModal = false" class="px-4 py-2 text-[#46571A]/85 hover:bg-[#F9F4EA] rounded-xl text-sm font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-[#FF9ABC] hover:bg-[#FF9ABC] text-[#46571A] rounded-xl text-sm font-bold shadow-md shadow-[#78956A]/25">Konfirmasi Tangguhkan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
