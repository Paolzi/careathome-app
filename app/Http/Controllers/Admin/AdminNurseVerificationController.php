<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\NurseProfile;
use App\Models\NurseVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminNurseVerificationController extends Controller
{
    /**
     * [SCRUM-55] Daftar Verifikasi Dokumen Legalitas STR/SIP Nakes.
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
                'rejection_reason' => null,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            // Catat ke riwayat nurse_verifications
            NurseVerification::create([
                'nurse_profile_id' => $nurseProfile->id,
                'admin_id' => $admin->id,
                'status' => 'verified',
                'verified_at' => now(),
            ]);

            // Update status dokumen menjadi verified
            $nurseProfile->documents()->update(['status' => 'verified']);

            // Catat audit_log
            AuditLog::log(
                'nurse_approved',
                $nurseProfile,
                ['verification_status' => $oldStatus],
                ['verification_status' => 'verified', 'reviewed_by' => $admin->id]
            );
        });

        return back()->with('status', "Legalitas perawat {$nurseProfile->user?->name} berhasil diverifikasi & disetujui.");
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

            // Update status dokumen
            $nurseProfile->documents()->update([
                'status' => 'rejected',
                'notes' => $reason,
            ]);

            // Catat audit_log
            AuditLog::log(
                'nurse_rejected',
                $nurseProfile,
                ['verification_status' => $oldStatus],
                ['verification_status' => 'rejected', 'rejection_reason' => $reason, 'reviewed_by' => $admin->id]
            );
        });

        return back()->with('status', "Berkas legalitas perawat {$nurseProfile->user?->name} telah ditolak dengan alasan yang tercatat.");
    }
}
