<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\NurseDocument;
use App\Models\NurseProfile;
use App\Models\NurseVerification;
use App\Notifications\NurseDocumentApprovedNotification;
use App\Notifications\NurseDocumentRejectedNotification;
use App\Services\KtkiValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminNurseVerificationController extends Controller
{
    /**
     * Daftar Verifikasi Dokumen Legalitas STR/SIP Nakes.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status', 'all');
        $search = $request->query('search');

        $query = NurseProfile::with(['user', 'documents', 'verifications', 'reviewer']);

        if ($status !== 'all') {
            $query->where('verification_status', $status);
        }

        if (filled($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                    ->orWhere('str_number', 'like', "%{$search}%")
                    ->orWhere('sip_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $nurses = $query->latest()->paginate(10)->withQueryString();

        $counts = [
            'all' => NurseProfile::count(),
            'pending' => NurseProfile::where('verification_status', 'pending')->count(),
            'draft' => NurseProfile::where('verification_status', 'draft')->count(),
            'verified' => NurseProfile::where('verification_status', 'verified')->count(),
            'rejected' => NurseProfile::where('verification_status', 'rejected')->count(),
        ];

        return view('admin.nurses.index', [
            'nurses' => $nurses,
            'status' => $status,
            'search' => $search,
            'counts' => $counts,
        ]);
    }

    /**
     * Setujui berkas legalitas STR/SIP perawat (Status: Verified).
     */
    public function approve(Request $request, NurseProfile $nurseProfile)
    {
        $admin = Auth::user();
        $oldStatus = $nurseProfile->verification_status;

        DB::transaction(function () use ($nurseProfile, $admin, $oldStatus) {
            $nurseProfile->update([
                'verification_status' => 'verified',
                'is_available' => true,
                'latitude' => $nurseProfile->latitude ?? -7.3144,
                'longitude' => $nurseProfile->longitude ?? 112.7304,
                'location_updated_at' => $nurseProfile->location_updated_at ?? now(),
                'rejection_reason' => null,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            // Pastikan jadwal harian 7 hari (Senin - Minggu) otomatis tersedia untuk perawat ini
            if ($nurseProfile->user_id) {
                \App\Models\NurseSchedule::ensureDefaultSchedulesForNurse((int) $nurseProfile->user_id);
            }

            // Jika perawat belum memilih layanan tindakan, kaitkan dengan katalog layanan aktif
            if ($nurseProfile->services()->count() === 0) {
                $defaultServiceIds = \App\Models\ServiceCatalog::where('is_active', true)->pluck('id')->toArray();
                if (! empty($defaultServiceIds)) {
                    $nurseProfile->services()->sync($defaultServiceIds);
                }
            }

            // Catat ke riwayat nurse_verifications
            NurseVerification::create([
                'nurse_profile_id' => $nurseProfile->id,
                'admin_id' => $admin->id,
                'status' => 'verified',
                'verified_at' => now(),
            ]);

            // Update status dokumen menjadi verified dan set ktki_verified_at jika belum ada
            $nurseProfile->documents()->update([
                'status' => 'verified',
                'rejection_reason' => null,
                'ktki_verified_at' => now(),
            ]);

            // Catat audit_log
            AuditLog::log(
                'nurse_approved',
                $nurseProfile,
                ['verification_status' => $oldStatus],
                ['verification_status' => 'verified', 'reviewed_by' => $admin->id]
            );
        });

        // Trigger Notifikasi Otomatis (Email & In-App Database Notification)
        try {
            $nurseProfile->user?->notify(new NurseDocumentApprovedNotification($nurseProfile, $admin));
        } catch (\Throwable $e) {
            // Log jika terjadi kegagalan mailer lokal, sistem tetap berjalan lancar
            report($e);
        }

        return back()->with('status', "Legalitas perawat {$nurseProfile->user?->name} berhasil diverifikasi & disetujui. Notifikasi telah dikirimkan ke perawat.");
    }

    /**
     * Tolak berkas legalitas STR/SIP perawat (Status: Rejected) dengan alasan wajib.
     */
    public function reject(Request $request, NurseProfile $nurseProfile)
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi agar perawat mengetahui dokumen yang perlu diperbaiki.',
        ]);

        $admin = Auth::user();
        $reason = $request->input('rejection_reason');
        $oldStatus = $nurseProfile->verification_status;

        DB::transaction(function () use ($nurseProfile, $admin, $reason, $oldStatus) {
            $nurseProfile->update([
                'verification_status' => 'rejected',
                'rejection_reason' => $reason,
                'is_online' => false, // Perawat ditolak otomatis OFF
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            // Catat ke riwayat nurse_verifications
            NurseVerification::create([
                'nurse_profile_id' => $nurseProfile->id,
                'admin_id' => $admin->id,
                'status' => 'rejected',
                'rejection_reason' => $reason,
                'verified_at' => now(),
            ]);

            // Update status dokumen dan alasan penolakan
            $nurseProfile->documents()->update([
                'status' => 'rejected',
                'notes' => $reason,
                'rejection_reason' => $reason,
            ]);

            // Catat audit_log
            AuditLog::log(
                'nurse_rejected',
                $nurseProfile,
                ['verification_status' => $oldStatus],
                ['verification_status' => 'rejected', 'rejection_reason' => $reason, 'reviewed_by' => $admin->id]
            );
        });

        // Trigger Notifikasi Otomatis (Email & In-App Database Notification)
        try {
            $nurseProfile->user?->notify(new NurseDocumentRejectedNotification($nurseProfile, $reason, $admin));
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('status', "Berkas legalitas perawat {$nurseProfile->user?->name} telah ditolak dengan alasan tercatat dan dikirimkan via notifikasi.");
    }

    /**
     * Simulasi Pengecekan Keabsahan STR/SIP ke Portal Kemenkes/KTKI.
     */
    public function verifyKtki(Request $request, NurseProfile $nurseProfile, KtkiValidationService $ktkiService): JsonResponse
    {
        $result = $ktkiService->validateNurseProfile($nurseProfile);

        // Jika valid, catat waktu verifikasi KTKI ke berkas dokumen STR
        if ($result['is_valid']) {
            NurseDocument::where('nurse_profile_id', $nurseProfile->id)
                ->where('document_type', 'str')
                ->update([
                    'ktki_verified_at' => now(),
                    'str_number' => $result['str_number'],
                ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ]);
    }

    /**
     * Preview dokumen aman khusus Admin.
     */
    public function previewDocument(NurseDocument $document)
    {
        // Jika file sample
        if (str_starts_with($document->file_path, 'samples/')) {
            $samplePath = public_path($document->file_path);
            if (file_exists($samplePath)) {
                return response()->file($samplePath, [
                    'Content-Type' => $document->mime_type ?: 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . ($document->original_name ?: 'dokumen_nakes.pdf') . '"',
                ]);
            }
        }

        $driver = $document->storage_driver ?: 'public';

        // Jika tersimpan di S3
        if ($driver === 's3') {
            try {
                return redirect()->away(Storage::temporaryUrl($document->file_path, now()->addMinutes(15)));
            } catch (\Throwable $e) {
                return redirect()->away(Storage::url($document->file_path));
            }
        }

        // Jika local / public
        if (!Storage::disk($driver)->exists($document->file_path)) {
            if (Storage::disk('public')->exists($document->file_path)) {
                $driver = 'public';
            } else {
                abort(404, 'Berkas dokumen tidak ditemukan di server.');
            }
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk($driver);
        $filePath = $disk->path($document->file_path);

        return response()->file($filePath, [
            'Content-Type' => $document->mime_type ?: (file_exists($filePath) ? mime_content_type($filePath) : 'application/octet-stream'),
            'Content-Disposition' => 'inline; filename="' . ($document->original_name ?: basename($filePath)) . '"',
        ]);
    }
}
