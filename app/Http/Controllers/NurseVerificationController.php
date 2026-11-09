<?php

namespace App\Http\Controllers;

use App\Models\NurseDocument;
use App\Models\NurseProfile;
use App\Models\NurseVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class NurseVerificationController extends Controller
{
    /**
     * Tampilkan halaman formulir verifikasi nakes
     */
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        // Cegah akun non-Perawat masuk ke alur verifikasi Nakes
        if (!$user->hasRole('perawat')) {
            abort(403, 'Akses ditolak. Halaman ini khusus untuk verifikasi mitra perawat.');
        }

        // Cari atau buat profil perawat di database
        $nurseProfile = NurseProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'verification_status' => 'draft',
                'is_available' => false,
            ]
        );

        // Ambil seluruh dokumen fisik yang tersimpan di database
        $documents = NurseDocument::where('nurse_profile_id', $nurseProfile->id)
            ->get()
            ->keyBy('document_type');

        return view('pages.nurse.verification', compact('nurseProfile', 'documents'));
    }

    /**
     * Helper untuk memparsing berbagai format tanggal (d/m/Y, Y-m-d, d-m-Y)
     * menjadi format YYYY-MM-DD standar MySQL
     */
    private function parseDateSafely(?string $date): ?string
    {
        if (blank($date)) {
            return null;
        }

        try {
            $trimmed = trim($date);
            if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $trimmed, $matches)) {
                return sprintf('%04d-%02d-%02d', (int) $matches[3], (int) $matches[2], (int) $matches[1]);
            }
            return \Carbon\Carbon::parse($trimmed)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Simpan Sebagai Draf ke Database (Auto-save & Manual Draft)
     */
    public function saveDraft(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $nurseProfile = NurseProfile::firstOrCreate(
            ['user_id' => $user->id]
        );

        $strExpiry = $this->parseDateSafely($request->input('str_expiry'));
        $sipExpiry = $this->parseDateSafely($request->input('sip_expiry'));

        // Update data formulir ke database
        $nurseProfile->update([
            'nik' => $request->input('nik') !== null && $request->input('nik') !== '' ? $request->input('nik') : $nurseProfile->nik,
            'str_number' => $request->input('str_number') !== null && $request->input('str_number') !== '' ? $request->input('str_number') : $nurseProfile->str_number,
            'str_expiry' => $strExpiry ?: $nurseProfile->str_expiry,
            'str_expired_at' => $strExpiry ?: $nurseProfile->str_expired_at,
            'sip_number' => $request->input('sip_number') !== null && $request->input('sip_number') !== '' ? $request->input('sip_number') : $nurseProfile->sip_number,
            'sip_expiry' => $sipExpiry ?: $nurseProfile->sip_expiry,
            'sip_expired_at' => $sipExpiry ?: $nurseProfile->sip_expired_at,
            'sip_institution' => $request->input('sip_institution') ?: $nurseProfile->sip_institution,
            'specialization' => $request->input('specialization') ?: $nurseProfile->specialization,
            'verification_status' => in_array($nurseProfile->verification_status, ['pending', 'verified']) ? $nurseProfile->verification_status : 'draft',
        ]);

        // Simpan lampiran file fisik jika ada yang diunggah
        $disk = config('filesystems.nurse_documents_disk', env('NURSE_DOCUMENT_DISK', 'public'));
        $docTypes = ['ktp', 'str', 'sip', 'ijazah'];
        foreach ($docTypes as $type) {
            if ($request->hasFile($type)) {
                $file = $request->file($type);
                $path = $file->store("documents/nurses/{$user->id}", $disk);

                NurseDocument::updateOrCreate(
                    [
                        'nurse_profile_id' => $nurseProfile->id,
                        'document_type' => $type,
                    ],
                    [
                        'file_path' => $path,
                        'storage_driver' => $disk,
                        'str_number' => $type === 'str' ? ($request->input('str_number') ?: $nurseProfile->str_number) : null,
                        'sip_number' => $type === 'sip' ? ($request->input('sip_number') ?: $nurseProfile->sip_number) : null,
                        'original_name' => $file->getClientOriginalName(),
                        'file_size' => round($file->getSize() / (1024 * 1024), 2) . ' MB',
                        'mime_type' => $file->getClientMimeType(),
                        'status' => 'draft',
                    ]
                );
            } elseif ($request->filled('sample_' . $type)) {
                // Untuk demo testing (+ Contoh File) jika tidak unggah file asli
                $isPdf = in_array($type, ['str', 'sip', 'ijazah']);
                NurseDocument::updateOrCreate(
                    [
                        'nurse_profile_id' => $nurseProfile->id,
                        'document_type' => $type,
                    ],
                    [
                        'file_path' => 'samples/' . $type . ($isPdf ? '.pdf' : '.jpg'),
                        'storage_driver' => 'public',
                        'str_number' => $type === 'str' ? ($request->input('str_number') ?: $nurseProfile->str_number) : null,
                        'sip_number' => $type === 'sip' ? ($request->input('sip_number') ?: $nurseProfile->sip_number) : null,
                        'original_name' => strtoupper($type) . '_Resmi_Tervalidasi' . ($isPdf ? '.pdf' : '.jpg'),
                        'file_size' => '1.2 MB',
                        'mime_type' => $isPdf ? 'application/pdf' : 'image/jpeg',
                        'status' => 'draft',
                    ]
                );
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Draf berhasil disimpan ke database!',
                'saved_at' => now()->translatedFormat('d M Y H:i'),
            ]);
        }

        return redirect()->back()->with('draft_saved', 'Draf berhasil disimpan ke database! Data Anda tetap tersimpan utuh meskipun logout.');
    }

    /**
     * Kirim Dokumen ke Admin untuk Diaudit & Diverifikasi
     */
    public function submit(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // Normalisasi format tanggal input jika menggunakan format d/m/Y
        $rawStrExpiry = $request->input('str_expiry');
        $rawSipExpiry = $request->input('sip_expiry');
        $parsedStrExpiry = $this->parseDateSafely($rawStrExpiry);
        $parsedSipExpiry = $this->parseDateSafely($rawSipExpiry);

        $request->merge([
            'str_expiry' => $parsedStrExpiry ?: $rawStrExpiry,
            'sip_expiry' => $parsedSipExpiry ?: $rawSipExpiry,
        ]);

        $request->validate([
            'nik' => ['required', 'string', 'min:8', 'max:20'],
            'str_number' => ['required', 'string', 'max:50'],
            'str_expiry' => ['required', 'date'],
            'sip_number' => ['required', 'string', 'max:100'],
            'sip_expiry' => ['nullable', 'date'],
            'sip_institution' => ['nullable', 'string', 'max:255'],
            'specialization' => ['nullable', 'string', 'max:255'],
        ], [
            'nik.required' => 'Nomor Induk Kependudukan (NIK) wajib diisi.',
            'nik.min' => 'NIK minimal terdiri dari 8 karakter/angka.',
            'str_number.required' => 'Nomor STR wajib diisi.',
            'str_expiry.required' => 'Tanggal masa berlaku STR wajib diisi.',
            'str_expiry.date' => 'Format tanggal masa berlaku STR tidak valid.',
            'sip_number.required' => 'Nomor SIP wajib diisi.',
        ]);

        $nurseProfile = NurseProfile::firstOrCreate(['user_id' => $user->id]);

        // Jika sudah terverifikasi resmi oleh admin, cegah overwrite status
        if ($nurseProfile->verification_status === 'verified') {
            return redirect()->back()->with('status', 'Profil Anda telah terverifikasi resmi.');
        }

        // Update data profil perawat ke status pending (Menunggu Review)
        $nurseProfile->update([
            'nik' => $request->input('nik'),
            'str_number' => $request->input('str_number'),
            'str_expiry' => $parsedStrExpiry,
            'str_expired_at' => $parsedStrExpiry,
            'sip_number' => $request->input('sip_number'),
            'sip_expiry' => $parsedSipExpiry,
            'sip_expired_at' => $parsedSipExpiry,
            'sip_institution' => $request->input('sip_institution') ?: ($nurseProfile->sip_institution ?: 'Praktik Mandiri'),
            'specialization' => $request->input('specialization') ?: ($nurseProfile->specialization ?: 'Perawat Umum'),
            'verification_status' => 'pending',
            'rejection_reason' => null,
        ]);

        $disk = config('filesystems.nurse_documents_disk', env('NURSE_DOCUMENT_DISK', 'public'));

        // Simpan berkas jika ada file baru diunggah pada saat submit
        $docTypes = ['ktp', 'str', 'sip', 'ijazah'];
        foreach ($docTypes as $type) {
            if ($request->hasFile($type)) {
                $file = $request->file($type);
                $path = $file->store("documents/nurses/{$user->id}", $disk);

                NurseDocument::updateOrCreate(
                    [
                        'nurse_profile_id' => $nurseProfile->id,
                        'document_type' => $type,
                    ],
                    [
                        'file_path' => $path,
                        'storage_driver' => $disk,
                        'str_number' => $type === 'str' ? $request->input('str_number') : null,
                        'sip_number' => $type === 'sip' ? $request->input('sip_number') : null,
                        'original_name' => $file->getClientOriginalName(),
                        'file_size' => round($file->getSize() / (1024 * 1024), 2) . ' MB',
                        'mime_type' => $file->getClientMimeType(),
                        'status' => 'pending',
                        'rejection_reason' => null,
                    ]
                );
            } elseif ($request->filled('sample_' . $type)) {
                $isPdf = in_array($type, ['str', 'sip', 'ijazah']);
                NurseDocument::updateOrCreate(
                    [
                        'nurse_profile_id' => $nurseProfile->id,
                        'document_type' => $type,
                    ],
                    [
                        'file_path' => 'samples/' . $type . ($isPdf ? '.pdf' : '.jpg'),
                        'storage_driver' => 'public',
                        'str_number' => $type === 'str' ? $request->input('str_number') : null,
                        'sip_number' => $type === 'sip' ? $request->input('sip_number') : null,
                        'original_name' => strtoupper($type) . '_Resmi_Tervalidasi' . ($isPdf ? '.pdf' : '.jpg'),
                        'file_size' => '1.2 MB',
                        'mime_type' => $isPdf ? 'application/pdf' : 'image/jpeg',
                        'status' => 'pending',
                        'rejection_reason' => null,
                    ]
                );
            } else {
                // Update status dokumen yang sudah ada menjadi pending
                NurseDocument::where('nurse_profile_id', $nurseProfile->id)
                    ->where('document_type', $type)
                    ->update([
                        'status' => 'pending',
                        'str_number' => $type === 'str' ? $request->input('str_number') : null,
                        'sip_number' => $type === 'sip' ? $request->input('sip_number') : null,
                        'rejection_reason' => null,
                    ]);
            }
        }

        // Catat pengajuan verifikasi ke tabel nurse_verifications
        NurseVerification::create([
            'nurse_profile_id' => $nurseProfile->id,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('submitted', 'Seluruh dokumen berhasil dikirim ke Admin CareAtHome untuk diaudit!');
    }

    /**
     * Hapus Dokumen Tertentu
     */
    public function deleteDocument(Request $request, string $type)
    {
        /** @var User $user */
        $user = Auth::user();
        $nurseProfile = NurseProfile::where('user_id', $user->id)->first();

        if ($nurseProfile) {
            $doc = NurseDocument::where('nurse_profile_id', $nurseProfile->id)
                ->where('document_type', $type)
                ->first();

            if ($doc) {
                if (Storage::disk('public')->exists($doc->file_path)) {
                    Storage::disk('public')->delete($doc->file_path);
                }
                $doc->delete();
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Dokumen ' . strtoupper($type) . ' berhasil dihapus.',
            ]);
        }

        return redirect()->back()->with('status', 'Dokumen ' . strtoupper($type) . ' berhasil dihapus.');
    }
}
