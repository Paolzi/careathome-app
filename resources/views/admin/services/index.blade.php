<x-admin-layout>
    <x-slot name="header">
        Master Data Katalog Layanan &amp; CRUD Tarif (SCRUM-57)
    </x-slot>

    <div class="space-y-6">

        <!-- Top Controls: Search, Filter, and Add Button -->
        <div class="bg-white rounded-3xl border border-slate-200 p-5 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                
                <!-- Category Filter & Search Form -->
                <form action="{{ route('admin.services.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
                    <select name="category" onchange="this.form.submit()"
                        class="text-xs rounded-xl border-slate-200 font-semibold text-slate-700 focus:ring-indigo-500 focus:border-indigo-500 py-1.5 px-3">
                        <option value="all">Semua Kategori</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat }}" {{ $category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>

                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari layanan medis..."
                        class="text-xs rounded-xl border-slate-200 focus:ring-indigo-500 focus:border-indigo-500 py-1.5 px-3 w-48 sm:w-64">

                    <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800">Cari</button>
                    @if ($search || $category !== 'all')
                        <a href="{{ route('admin.services.index') }}" class="text-xs text-rose-600 font-bold hover:underline">Reset</a>
                    @endif
                </form>

                <a href="{{ route('admin.services.create') }}"
                    class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/20 transition flex items-center gap-1.5 self-start sm:self-auto">
                    <span>+ Tambah Layanan Baru</span>
                </a>

            </div>
        </div>

        <!-- Tabel Layanan -->
        <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-100">
                        <tr>
                            <th class="p-4">Tindakan Medis</th>
                            <th class="p-4">Kategori</th>
                            <th class="p-4">Durasi Estimasi</th>
                            <th class="p-4">Tarif Berlaku (Base Price)</th>
                            <th class="p-4">Riwayat Tarif</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($services as $s)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-2xl bg-slate-100 flex items-center justify-center text-2xl border border-slate-200 shrink-0">
                                            {{ $s->image_emoji }}
                                        </div>
                                        <div>
                                            <span class="font-bold text-slate-900 text-sm block">{{ $s->name }}</span>
                                            <span class="text-[11px] text-slate-400 block line-clamp-1 max-w-sm">{{ $s->description }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $s->category }}
                                    </span>
                                </td>
                                <td class="p-4 font-medium text-slate-800">
                                    {{ $s->estimated_duration }}
                                </td>
                                <td class="p-4">
                                    <span class="font-extrabold text-sm text-emerald-700 block">Rp {{ number_format($s->price, 0, ',', '.') }}</span>
                                </td>
                                <td class="p-4">
                                    <span class="text-[11px] text-slate-500 font-semibold block">
                                        {{ $s->tariffs->count() }} Perubahan Tarif
                                    </span>
                                    <span class="text-[10px] text-slate-400">
                                        Terakhir: {{ $s->tariffs->first()?->effective_from?->format('d M Y') ?? '-' }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $s->is_active ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $s->is_active ? 'Aktif di Katalog' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.services.edit', $s->id) }}"
                                            class="px-2.5 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 font-bold text-xs transition">
                                            Edit &amp; Ubah Tarif
                                        </a>

                                        <form action="{{ route('admin.services.destroy', $s->id) }}" method="POST"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan/menghapus layanan ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="px-2.5 py-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 font-bold text-xs transition">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-xs text-slate-400 italic">
                                    Belum ada layanan dalam katalog medis.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $services->links() }}
            </div>
        </div>

    </div>
</x-admin-layout>
