<x-admin-layout>
    <x-slot name="header">
        Audit Legalitas Nakes STR &amp; SIP
    </x-slot>

    <div class="space-y-6" x-data="{
        selectedNurse: null,
        modalOpen: false,
        rejectionOpen: false,
        rejectionReason: '',
        openModal(nurse) {
            this.selectedNurse = nurse;
            this.rejectionOpen = false;
            this.rejectionReason = '';
            this.modalOpen = true;
        }
    }">

        <!-- Filter Tabs & Search Bar -->
        <div class="bg-white rounded-3xl border border-slate-200 p-5 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                
                <!-- Status Tabs -->
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('admin.nurses.index', ['status' => 'all', 'search' => $search]) }}"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ $status === 'all' ? 'bg-emerald-700 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-900' }}">
                        <span>Semua Nakes</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $status === 'all' ? 'bg-emerald-800 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $counts['all'] }}</span>
                    </a>

                    <a href="{{ route('admin.nurses.index', ['status' => 'pending', 'search' => $search]) }}"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ $status === 'pending' ? 'bg-amber-600 text-white shadow-xs' : 'bg-amber-50 text-amber-800 hover:bg-amber-100' }}">
                        <span>Menunggu Review</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $status === 'pending' ? 'bg-amber-700 text-white' : 'bg-amber-200 text-amber-900' }}">{{ $counts['pending'] }}</span>
                    </a>

                    <a href="{{ route('admin.nurses.index', ['status' => 'draft', 'search' => $search]) }}"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ $status === 'draft' ? 'bg-slate-800 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                        <span>Draf (Draft)</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $status === 'draft' ? 'bg-slate-700 text-white' : 'bg-slate-200 text-slate-800' }}">{{ $counts['draft'] ?? 0 }}</span>
                    </a>

                    <a href="{{ route('admin.nurses.index', ['status' => 'verified', 'search' => $search]) }}"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ $status === 'verified' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}">
                        <span>Terverifikasi (Verified)</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $status === 'verified' ? 'bg-emerald-700 text-white' : 'bg-emerald-200 text-emerald-900' }}">{{ $counts['verified'] }}</span>
                    </a>

                    <a href="{{ route('admin.nurses.index', ['status' => 'rejected', 'search' => $search]) }}"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ $status === 'rejected' ? 'bg-rose-600 text-white shadow-xs' : 'bg-rose-50 text-rose-800 hover:bg-rose-100' }}">
                        <span>Ditolak (Rejected)</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $status === 'rejected' ? 'bg-rose-700 text-white' : 'bg-rose-200 text-rose-900' }}">{{ $counts['rejected'] }}</span>
                    </a>
                </div>

                <!-- Search Input -->
                <form action="{{ route('admin.nurses.index') }}" method="GET" class="flex items-center gap-2">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, NIK, STR/SIP..."
                        class="text-xs rounded-xl border-slate-200 focus:ring-emerald-500 focus:border-emerald-500 py-1.5 px-3 w-48 sm:w-64">
                    <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition">Cari</button>
                    @if ($search)
                        <a href="{{ route('admin.nurses.index', ['status' => $status]) }}" class="text-xs text-rose-600 font-bold hover:underline">Reset</a>
                    @endif
                </form>

            </div>
        </div>

        <!-- Tabel Perawat -->
        <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-100">
                        <tr>
                            <th class="p-4">Perawat &amp; NIK</th>
                            <th class="p-4">Nomor STR &amp; Masa Berlaku</th>
                            <th class="p-4">Nomor SIP &amp; Institusi</th>
                            <th class="p-4">Dokumen</th>
                            <th class="p-4">Status Legalitas</th>
                            <th class="p-4">Reviewer</th>
                            <th class="p-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($nurses as $nurse)
                            @php $nurseData = $nurse->toArray(); @endphp
                            <tr class="hover:bg-emerald-50/30 transition cursor-pointer" @click="openModal(@js($nurseData))">
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 font-black text-xs flex items-center justify-center border border-emerald-100">
                                            {{ strtoupper(substr($nurse->user->name ?? 'NS', 0, 2)) }}
                                        </div>
                                        <div>
                                            <span class="font-bold text-slate-900 block">{{ $nurse->user->name ?? 'N/A' }}</span>
                                            <span class="text-[11px] text-slate-400 block">{{ $nurse->user->email ?? '-' }} &bull; NIK: {{ $nurse->nik ?? '-' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="font-mono font-semibold text-slate-800 block">{{ $nurse->str_number ?? '-' }}</span>
                                    <span class="text-[10px] text-slate-400">Exp: {{ $nurse->str_expiry ? $nurse->str_expiry->format('d M Y') : ($nurse->str_expired_at ? $nurse->str_expired_at->format('d M Y') : '-') }}</span>
                                </td>
                                <td class="p-4">
                                    <span class="font-mono font-semibold text-slate-800 block">{{ $nurse->sip_number ?? '-' }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $nurse->sip_institution ?? 'Praktik Mandiri' }}</span>
                                </td>
                                <td class="p-4">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $nurse->documents->count() > 0 ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $nurse->documents->count() }} Berkas
                                    </span>
                                </td>
                                <td class="p-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold 
                                        {{ $nurse->verification_status === 'verified' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : ($nurse->verification_status === 'rejected' ? 'bg-rose-50 text-rose-800 border border-rose-200' : ($nurse->verification_status === 'pending' ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-slate-100 text-slate-700 border border-slate-200')) }}">
                                        {{ ucfirst($nurse->verification_status) }}
                                    </span>
                                </td>
                                <td class="p-4 text-[11px]">
                                    @if ($nurse->reviewer)
                                        <span class="font-semibold text-slate-800 block">{{ $nurse->reviewer->name }}</span>
                                        <span class="text-slate-400 text-[10px]">{{ $nurse->reviewed_at?->diffForHumans() }}</span>
                                    @else
                                        <span class="text-slate-400 italic">Belum direview</span>
                                    @endif
                                </td>
                                <td class="p-4 text-right" @click.stop>
                                    <button type="button" @click="openModal(@js($nurseData))"
                                        class="px-3.5 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 font-bold text-xs transition shadow-2xs">
                                        Audit Berkas &rarr;
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-xs text-slate-400 italic">
                                    Tidak ada data perawat yang sesuai dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-4 border-t border-slate-100">
                {{ $nurses->links() }}
            </div>
        </div>

        <!-- MODAL AUDIT LEGALITAS & PREVIEW DOKUMEN -->
        <div x-show="modalOpen" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            
            <div @click.away="modalOpen = false"
                class="bg-white rounded-3xl max-w-3xl w-full shadow-2xl border border-slate-200 overflow-hidden text-slate-800 flex flex-col max-h-[90vh]">
                
                <!-- Modal Header -->
                <div class="px-6 py-4 bg-gradient-to-r from-emerald-950 via-emerald-900 to-teal-950 text-white flex items-center justify-between shrink-0">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400 block">Audit Berkas Legalitas Profesi Nakes</span>
                        <h3 class="text-base font-bold text-white mt-0.5" x-text="selectedNurse?.user?.name || 'Profil Perawat'"></h3>
                    </div>
                    <button type="button" @click="modalOpen = false" class="text-slate-300 hover:text-white p-1 rounded-xl text-lg font-bold">
                        ✕
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 overflow-y-auto space-y-5 text-xs">
                    
                    <!-- Rincian Identitas & Nomor Izin -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 p-4 rounded-2xl bg-emerald-50/40 border border-emerald-100">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">NIK KTP</span>
                            <strong class="text-slate-800 font-mono" x-text="selectedNurse?.nik || '-'"></strong>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Nomor STR</span>
                            <strong class="text-slate-800 font-mono" x-text="selectedNurse?.str_number || '-'"></strong>
                            <span class="text-[10px] text-slate-500 block" x-text="'Masa Berlaku: ' + (selectedNurse?.str_expiry || selectedNurse?.str_expired_at || '-')"></span>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Nomor SIP</span>
                            <strong class="text-slate-800 font-mono" x-text="selectedNurse?.sip_number || '-'"></strong>
                            <span class="text-[10px] text-slate-500 block" x-text="'Masa Berlaku: ' + (selectedNurse?.sip_expiry || selectedNurse?.sip_expired_at || '-')"></span>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Institusi SIP</span>
                            <span class="text-slate-700" x-text="selectedNurse?.sip_institution || 'Praktik Mandiri'"></span>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Spesialisasi</span>
                            <span class="text-slate-700" x-text="selectedNurse?.specialization || 'Perawat Umum'"></span>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Status Saat Ini</span>
                            <span class="font-extrabold uppercase text-emerald-800" x-text="selectedNurse?.verification_status"></span>
                        </div>
                    </div>

                    <!-- Dokumen Legalitas (Preview / Unduh) -->
                    <div class="space-y-2">
                        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center justify-between">
                            <span>Berkas Legalitas yang Diunggah</span>
                            <span class="text-[10px] text-slate-400 font-normal" x-text="(selectedNurse?.documents?.length || 0) + ' Berkas terlampir'"></span>
                        </h4>
                        
                        <template x-if="!selectedNurse?.documents || selectedNurse?.documents?.length === 0">
                            <div class="p-6 text-center rounded-2xl bg-slate-50 border border-slate-200 text-slate-400">
                                <span class="text-2xl block mb-1">📂</span>
                                <span class="text-xs">Perawat belum mengunggah dokumen fisik / masih dalam proses pengisian.</span>
                            </div>
                        </template>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" x-show="selectedNurse?.documents?.length > 0">
                            <template x-for="doc in selectedNurse?.documents" :key="doc.id">
                                <div class="p-3 rounded-2xl border border-slate-200 flex items-center justify-between hover:bg-emerald-50/30 transition">
                                    <div class="flex items-center gap-2.5">
                                        <span class="text-lg">📄</span>
                                        <div>
                                            <span class="font-bold text-slate-800 uppercase text-[11px] block" x-text="doc.document_type"></span>
                                            <span class="text-[10px] text-slate-400 truncate max-w-[140px] block" x-text="doc.original_name || 'Berkas Dokumen'"></span>
                                        </div>
                                    </div>
                                    <a :href="'/storage/' + doc.file_path" target="_blank"
                                        class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 font-bold text-[10px] hover:bg-emerald-100 transition">
                                        Preview Dokumen &rarr;
                                    </a>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Box Alasan Penolakan Jika Sebelumnya Ditolak -->
                    <template x-if="selectedNurse?.rejection_reason">
                        <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs">
                            <strong>Catatan Penolakan Sebelumnya:</strong>
                            <p class="mt-1 italic" x-text="selectedNurse.rejection_reason"></p>
                        </div>
                    </template>

                    <!-- Form Input Alasan Penolakan -->
                    <div x-show="rejectionOpen" x-cloak class="p-4 rounded-2xl bg-rose-50 border border-rose-200 space-y-3">
                        <label class="block font-bold text-rose-900 text-xs">
                            Alasan Penolakan Dokumen <span class="text-rose-600">*</span>:
                        </label>
                        <form x-bind:action="'/admin/verifikasi-nakes/' + selectedNurse?.id + '/tolak'" method="POST" class="space-y-3">
                            @csrf
                            <textarea name="rejection_reason" rows="3" required
                                placeholder="Jelaskan bagian berkas yang tidak valid (mis. Foto STR buram, masa berlaku STR telah kadaluarsa)..."
                                class="w-full text-xs rounded-xl border-rose-200 focus:border-rose-500 focus:ring-rose-500"></textarea>

                            <div class="flex items-center justify-end gap-2">
                                <button type="button" @click="rejectionOpen = false" class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-600 hover:bg-slate-100">Batal</button>
                                <button type="submit" class="px-4 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-xs">Kirim Penolakan</button>
                            </div>
                        </form>
                    </div>

                </div>

                <!-- Modal Footer -->
                <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between shrink-0">
                    <button type="button" @click="modalOpen = false"
                        class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-xs font-bold text-slate-700">
                        Tutup
                    </button>

                    <div class="flex items-center gap-2">
                        <!-- Tombol Tolak -->
                        <button type="button" @click="rejectionOpen = true" x-show="!rejectionOpen"
                            class="px-4 py-2 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 font-bold text-xs transition">
                            Tolak Berkas
                        </button>

                        <!-- Form Setujui -->
                        <form x-bind:action="'/admin/verifikasi-nakes/' + selectedNurse?.id + '/setujui'" method="POST"
                            onsubmit="return confirm('Apakah Anda yakin ingin menyetujui berkas nakes ini? Perawat akan diizinkan aktif bekerja.');">
                            @csrf
                            <button type="submit"
                                class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition">
                                ✓ Setujui Legalitas Nakes
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>

    </div>
</x-admin-layout>
