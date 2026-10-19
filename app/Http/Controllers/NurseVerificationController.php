<?php

namespace App\Http\Controllers;

use App\Models\NurseDocument;
use App\Models\NurseProfile;
use App\Models\NurseVerification;
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
        $user = Auth::user();

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
     * Simpan Sebagai Draf ke Database (Auto-save & Manual Draft)
     */
    public function saveDraft(Request $request)
    {
        $user = Auth::user();

        $nurseProfile = NurseProfile::firstOrCreate(
            ['user_id' => $user->id]
        );

        // Update data formulir ke database
        $nurseProfile->update([
            'nik' => $request->input('nik'),
            'str_number' => $request->input('str_number'),
            'str_expiry' => $request->input('str_expiry') ?: null,
            'sip_number' => $request->input('sip_number'),
            'verification_status' => 'draft',
        ]);

        // Simpan lampiran file fisik jika ada yang diunggah
        $docTypes = ['ktp', 'str', 'sip', 'ijazah'];
        foreach ($docTypes as $type) {
            if ($request->hasFile($type)) {
                $file = $request->file($type);
                $path = $file->store('nurse_documents/' . $user->id, 'public');

                NurseDocument::updateOrCreate(
                    [
                        'nurse_profile_id' => $nurseProfile->id,
                        'document_type' => $type,
                    ],
                    [
                        'file_path' => $path,
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
        $user = Auth::user();

        $request->validate([
            'nik' => ['required', 'string', 'min:16', 'max:16'],
            'str_number' => ['required', 'string', 'max:50'],
            'str_expiry' => ['required', 'date'],
            'sip_number' => ['required', 'string', 'max:100'],
        ]);

        $nurseProfile = NurseProfile::firstOrCreate(['user_id' => $user->id]);

        // Update data profil perawat
        $nurseProfile->update([
            'nik' => $request->input('nik'),
            'str_number' => $request->input('str_number'),
            'str_expiry' => $request->input('str_expiry'),
            'sip_number' => $request->input('sip_number'),
            'verification_status' => 'pending',
        ]);

        // Simpan berkas jika ada file baru diunggah pada saat submit
        $docTypes = ['ktp', 'str', 'sip', 'ijazah'];
        foreach ($docTypes as $type) {
            if ($request->hasFile($type)) {
                $file = $request->file($type);
                $path = $file->store('nurse_documents/' . $user->id, 'public');

                NurseDocument::updateOrCreate(
                    [
                        'nurse_profile_id' => $nurseProfile->id,
                        'document_type' => $type,
                    ],
                    [
                        'file_path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'file_size' => round($file->getSize() / (1024 * 1024), 2) . ' MB',
                        'mime_type' => $file->getClientMimeType(),
                        'status' => 'pending',
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
                        'original_name' => strtoupper($type) . '_Resmi_Tervalidasi' . ($isPdf ? '.pdf' : '.jpg'),
                        'file_size' => '1.2 MB',
                        'mime_type' => $isPdf ? 'application/pdf' : 'image/jpeg',
                        'status' => 'pending',
                    ]
                );
            } else {
                // Update status dokumen yang sudah ada menjadi pending
                NurseDocument::where('nurse_profile_id', $nurseProfile->id)
                    ->where('document_type', $type)
                    ->update(['status' => 'pending']);
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
    public function deleteDocument($type)
    {
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

        return redirect()->back()->with('status', 'Dokumen ' . strtoupper($type) . ' berhasil dihapus.');
    }
}
