<x-admin-layout>
    <x-slot name="header">
        Edit Layanan &amp; Penyesuaian Tarif Medis (SCRUM-57)
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">

        <!-- Form Edit Layanan -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Perbarui Tindakan Medis: {{ $service->name }}</h3>
                    <p class="text-xs text-slate-500">Ubah detail layanan dan sesuaikan tarif jika ada pembaruan plafon resmi.</p>
                </div>
                <div class="w-10 h-10 rounded-2xl bg-slate-100 text-2xl flex items-center justify-center border border-slate-200">
                    {{ $service->image_emoji }}
                </div>
            </div>

            <form action="{{ route('admin.services.update', $service->id) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <!-- Nama Layanan & Emoji -->
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div class="sm:col-span-3">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Layanan <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $service->name) }}" required
                            class="w-full text-xs rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Emoji Ikon</label>
                        <input type="text" name="image_emoji" value="{{ old('image_emoji', $service->image_emoji) }}" maxlength="10"
                            class="w-full text-xs rounded-xl border-slate-200 text-center focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <!-- Kategori & Durasi -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Kategori Medis <span class="text-rose-500">*</span></label>
                        <input type="text" name="category" value="{{ old('category', $service->category) }}" required
                            class="w-full text-xs rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Label Durasi <span class="text-rose-500">*</span></label>
                        <input type="text" name="estimated_duration" value="{{ old('estimated_duration', $service->estimated_duration) }}" required
                            class="w-full text-xs rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Menit Acuan <span class="text-rose-500">*</span></label>
                        <input type="number" name="estimated_duration_minutes" value="{{ old('estimated_duration_minutes', $service->estimated_duration_minutes) }}" min="15" max="480" required
                            class="w-full text-xs rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <!-- Plafon Tarif Medis (Base Price) -->
                <div class="p-4 rounded-2xl bg-indigo-50/70 border border-indigo-200/80 space-y-2">
                    <label class="block text-xs font-bold text-indigo-950">
                        Tarif Tindakan Berlaku (Base Price) <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-indigo-800">Rp</span>
                        <input type="number" name="price" value="{{ old('price', (int)$service->price) }}" min="10000" step="5000" required
                            class="w-full text-xs font-bold rounded-xl border-indigo-300 focus:border-indigo-600 focus:ring-indigo-600 bg-white">
                    </div>
                    <p class="text-[10px] text-indigo-800">
                        💡 Jika Anda mengubah nominal tarif di atas, sistem akan otomatis mencatat baris baru di tabel <code>service_tariffs</code> dengan waktu berlaku mulai sekarang. Pesanan lama yang telah dipesan tidak akan terpengaruh.
                    </p>
                </div>

                <!-- Deskripsi & Indikasi -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Deskripsi Lengkap Tindakan <span class="text-rose-500">*</span></label>
                        <textarea name="description" rows="3" required
                            class="w-full text-xs rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $service->description) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Indikasi Klinis</label>
                            <textarea name="indications" rows="2"
                                class="w-full text-xs rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">{{ old('indications', $service->indications) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Kontraindikasi Medis</label>
                            <textarea name="contraindications" rows="2"
                                class="w-full text-xs rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">{{ old('contraindications', $service->contraindications) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Status Aktif Checkbox -->
                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" name="is_active" value="1" id="is_active" {{ $service->is_active ? 'checked' : '' }}
                        class="rounded text-indigo-600 focus:ring-indigo-500">
                    <label for="is_active" class="text-xs font-bold text-slate-700 cursor-pointer">
                        Layanan aktif di Katalog Pasien
                    </label>
                </div>

                <!-- Tombol Aksi -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('admin.services.index') }}"
                        class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-xs font-bold text-slate-700 transition">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition">
                        Simpan Perubahan
                    </button>
                </div>

            </form>
        </div>

        <!-- RIWAYAT TARIF LAYANAN (SERVICE_TARIFFS TABLE AUDIT) -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h4 class="text-sm font-bold text-slate-900">Riwayat Perubahan Plafon Tarif (Tariff Audit Log)</h4>
                <p class="text-xs text-slate-500">Catatan kronologis penyesuaian tarif resmi untuk tindakan ini.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400">
                        <tr>
                            <th class="p-3 rounded-l-lg">Nominal Tarif</th>
                            <th class="p-3">Berlaku Mulai</th>
                            <th class="p-3">Diubah Oleh</th>
                            <th class="p-3 rounded-r-lg">Waktu Pembuatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($service->tariffs as $tariff)
                            <tr>
                                <td class="p-3 font-extrabold text-sm text-emerald-700">
                                    Rp {{ number_format($tariff->price, 0, ',', '.') }}
                                </td>
                                <td class="p-3 font-medium text-slate-800">
                                    {{ $tariff->effective_from->format('d M Y H:i') }}
                                </td>
                                <td class="p-3 text-slate-600">
                                    {{ $tariff->creator?->name ?? 'Administrator' }}
                                </td>
                                <td class="p-3 text-slate-400 text-[11px]">
                                    {{ $tariff->created_at->diffForHumans() }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-4 text-center text-xs text-slate-400 italic">
                                    Belum ada catatan riwayat tarif tersimpan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-admin-layout>
