<?php

namespace App\Http\Controllers;

use App\Models\NurseDocument;
use App\Models\NurseProfile;
use App\Models\User;
use App\Services\KtkiValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class NurseDocumentController extends Controller
{
    protected KtkiValidationService $ktkiService;

    public function __construct(KtkiValidationService $ktkiService)
    {
        $this->ktkiService = $ktkiService;
    }

    /**
     * Mengunggah satu berkas dokumen legalitas perawat ke Cloud / Local Storage.
     */
    public function upload(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'document_type' => ['required', 'string', 'in:ktp,str,sip,ijazah'],
            'file' => ['required', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:5120'], // Max 5 MB
            'str_number' => ['nullable', 'string', 'max:50'],
            'sip_number' => ['nullable', 'string', 'max:100'],
        ]);

        $nurseProfile = NurseProfile::firstOrCreate(['user_id' => $user->id]);

        $disk = config('filesystems.nurse_documents_disk', env('NURSE_DOCUMENT_DISK', 'public'));
        $file = $request->file('file');
        $type = $validated['document_type'];

        // Simpan ke direktori aman: documents/nurses/{user_id}/
        $path = $file->store("documents/nurses/{$user->id}", $disk);

        $document = NurseDocument::updateOrCreate(
            [
                'nurse_profile_id' => $nurseProfile->id,
                'document_type' => $type,
            ],
            [
                'file_path' => $path,
                'storage_driver' => $disk,
                'str_number' => $request->input('str_number', $nurseProfile->str_number),
                'sip_number' => $request->input('sip_number', $nurseProfile->sip_number),
                'original_name' => $file->getClientOriginalName(),
                'file_size' => round($file->getSize() / (1024 * 1024), 2) . ' MB',
                'mime_type' => $file->getClientMimeType(),
                'status' => 'pending',
                'rejection_reason' => null,
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Berkas ' . strtoupper($type) . ' berhasil diunggah ke penyimpanan ' . strtoupper($disk) . '.',
            'data' => [
                'id' => $document->id,
                'document_type' => $document->document_type,
                'file_path' => $document->file_path,
                'storage_driver' => $document->storage_driver,
                'file_url' => $document->file_url,
                'original_name' => $document->original_name,
                'file_size' => $document->file_size,
            ],
        ]);
    }

    /**
     * Preview atau download dokumen secara aman (hanya Admin atau Perawat pemilik berkas).
     */
    public function preview(NurseDocument $document)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        // Otorisasi: Hanya Admin atau Perawat pemilik dokumen yang boleh membuka
        $ownerUserId = $document->nurseProfile?->user_id;
        if (!$currentUser->hasRole('admin') && $currentUser->id !== $ownerUserId) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat dokumen ini.');
        }

        // Jika merupakan contoh berkas sampel
        if (str_starts_with($document->file_path, 'samples/')) {
            $samplePath = public_path($document->file_path);
            if (file_exists($samplePath)) {
                return response()->file($samplePath, [
                    'Content-Type' => $document->mime_type ?: 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $document->original_name . '"',
                ]);
            }
        }

        $driver = $document->storage_driver ?: 'public';

        // Jika tersimpan di S3 Cloud Storage
        if ($driver === 's3') {
            $disk = Storage::disk('s3');
            if (method_exists($disk, 'temporaryUrl')) {
                return redirect()->away($disk->temporaryUrl($document->file_path, now()->addMinutes(15)));
            }
            return redirect()->away($disk->url($document->file_path));
        }

        // Jika tersimpan di local / public disk
        $disk = Storage::disk($driver);
        if (!$disk->exists($document->file_path)) {
            // Cek di disk public jika driver disetel lain
            if (Storage::disk('public')->exists($document->file_path)) {
                $disk = Storage::disk('public');
            } else {
                abort(404, 'Berkas fisik tidak ditemukan pada server.');
            }
        }

        $filePath = $disk->path($document->file_path);
        return response()->file($filePath, [
            'Content-Type' => $document->mime_type ?: mime_content_type($filePath),
            'Content-Disposition' => 'inline; filename="' . ($document->original_name ?: basename($filePath)) . '"',
        ]);
    }

    /**
     * Tandai satu notifikasi perawat sebagai sudah dibaca.
     */
    public function markNotificationAsRead(Request $request, string $id)
    {
        /** @var User $user */
        $user = Auth::user();

        $notification = $user->notifications()->where('id', $id)->first();
        if ($notification) {
            $notification->markAsRead();
        }

        $unreadCount = $user->unreadNotifications()->count();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Notifikasi telah ditandai sebagai sudah dibaca.',
                'unread_count' => $unreadCount,
            ]);
        }

        return redirect()->back()->with('status', 'Notifikasi telah ditandai sebagai sudah dibaca.');
    }

    /**
     * Tandai seluruh notifikasi perawat sebagai sudah dibaca.
     */
    public function markAllNotificationsAsRead(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Seluruh notifikasi telah ditandai sebagai sudah dibaca.',
                'unread_count' => 0,
            ]);
        }

        return redirect()->back()->with('status', 'Seluruh notifikasi telah ditandai sebagai sudah dibaca.');
    }
}

