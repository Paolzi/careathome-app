<x-admin-layout>
    <x-slot name="header">
        Tambah Layanan Medis Baru &amp; Plafon Tarif
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <div class="bg-white rounded-3xl border border-[#78956A]/50 p-6 sm:p-8 shadow-xs space-y-6">
            <div class="border-b border-[#78956A]/50 pb-4">
                <h3 class="text-base font-bold text-[#46571A]">Formulir Master Layanan Medis Baru</h3>
                <p class="text-xs text-[#78956A]">Isi kelengkapan data tindakan keperawatan dan tentukan plafon tarif resmi awal.</p>
            </div>

            <form action="{{ route('admin.services.store') }}" method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="badge_color" value="emerald">

                <!-- Nama Layanan & Emoji -->
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div class="sm:col-span-3">
                        <label class="block text-xs font-bold text-[#46571A] mb-1.5">Nama Layanan / Tindakan <span class="text-[#46571A]">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            placeholder="Mis. Perawatan Luka Post-Operasi & Pasang Verban"
                            class="w-full text-xs rounded-xl border-[#78956A]/50 focus:border-[#78956A] focus:ring-[#78956A]">
                        @error('name') <span class="text-[10px] text-[#46571A]">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#46571A] mb-1.5">Emoji Ikon</label>
                        <input type="text" name="image_emoji" value="{{ old('image_emoji', '🩺') }}" maxlength="10"
                            class="w-full text-xs rounded-xl border-[#78956A]/50 text-center focus:border-[#78956A] focus:ring-[#78956A]">
                    </div>
                </div>

                <!-- Kategori & Durasi -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-[#46571A] mb-1.5">Kategori Medis <span class="text-[#46571A]">*</span></label>
                        <input type="text" name="category" value="{{ old('category', 'Prosedur Medis') }}" required
                            placeholder="Mis. Perawatan Khusus, Terapi & Injeksi"
                            class="w-full text-xs rounded-xl border-[#78956A]/50 focus:border-[#78956A] focus:ring-[#78956A]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#46571A] mb-1.5">Label Durasi <span class="text-[#46571A]">*</span></label>
                        <input type="text" name="estimated_duration" value="{{ old('estimated_duration', '45 - 60 Menit') }}" required
                            placeholder="Mis. 45 - 60 Menit"
                            class="w-full text-xs rounded-xl border-[#78956A]/50 focus:border-[#78956A] focus:ring-[#78956A]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#46571A] mb-1.5">Menit Acuan <span class="text-[#46571A]">*</span></label>
                        <input type="number" name="estimated_duration_minutes" value="{{ old('estimated_duration_minutes', 60) }}" min="15" max="480" required
                            class="w-full text-xs rounded-xl border-[#78956A]/50 focus:border-[#78956A] focus:ring-[#78956A]">
                    </div>
                </div>

                <!-- Plafon Tarif Medis (Base Price) -->
                <div class="p-4 rounded-2xl bg-[#D1EB8F]/45 border border-[#78956A]/50 space-y-2">
                    <label class="block text-xs font-bold text-[#46571A]">
                        Plafon Tarif Medis (Base Price) <span class="text-[#46571A]">*</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-[#46571A]">Rp</span>
                        <input type="number" name="price" value="{{ old('price', 150000) }}" min="10000" step="5000" required
                            class="w-full text-xs font-bold rounded-xl border-[#78956A] focus:border-[#78956A] focus:ring-[#78956A] bg-white">
                    </div>
                    <p class="text-[10px] text-[#46571A]">
                        Tarif ini akan dicatat ke dalam riwayat tarif sebagai tarif perdana yang berlaku efektif mulai hari ini.
                    </p>
                </div>

                <!-- Deskripsi & Indikasi -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-[#46571A] mb-1.5">Deskripsi Lengkap Tindakan <span class="text-[#46571A]">*</span></label>
                        <textarea name="description" rows="3" required
                            placeholder="Jelaskan standar pelayanan tindakan medis ini..."
                            class="w-full text-xs rounded-xl border-[#78956A]/50 focus:border-[#78956A] focus:ring-[#78956A]">{{ old('description') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-[#46571A] mb-1.5">Indikasi Klinis</label>
                            <textarea name="indications" rows="2"
                                placeholder="Kasus/kondisi pasien yang memerlukan tindakan ini..."
                                class="w-full text-xs rounded-xl border-[#78956A]/50 focus:border-[#78956A] focus:ring-[#78956A]">{{ old('indications') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#46571A] mb-1.5">Kontraindikasi Medis</label>
                            <textarea name="contraindications" rows="2"
                                placeholder="Kondisi darurat/bahaya yang tidak boleh dilayani via homecare..."
                                class="w-full text-xs rounded-xl border-[#78956A]/50 focus:border-[#78956A] focus:ring-[#78956A]">{{ old('contraindications') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Status Aktif Checkbox -->
                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" name="is_active" value="1" id="is_active" checked
                        class="rounded text-[#78956A] focus:ring-[#78956A]">
                    <label for="is_active" class="text-xs font-bold text-[#46571A] cursor-pointer">
                        Aktifkan layanan ini langsung di Katalog Pasien
                    </label>
                </div>

                <!-- Tombol Aksi -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#78956A]/50">
                    <a href="{{ route('admin.services.index') }}"
                        class="px-4 py-2 rounded-xl border border-[#78956A]/50 bg-white hover:bg-[#F9F4EA] text-xs font-bold text-[#46571A] transition">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-[#FF9ABC] hover:bg-[#FF9ABC] text-[#46571A] font-bold text-xs shadow-xs transition">
                        Simpan Layanan Baru
                    </button>
                </div>

            </form>
        </div>
    </div>
</x-admin-layout>
