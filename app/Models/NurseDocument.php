<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class NurseDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'nurse_profile_id',
        'document_type',
        'file_path',
        'storage_driver',
        'str_number',
        'sip_number',
        'ktki_verified_at',
        'original_name',
        'file_size',
        'mime_type',
        'status',
        'notes',
        'rejection_reason',
    ];

    protected $casts = [
        'ktki_verified_at' => 'datetime',
    ];

    protected $appends = [
        'file_url',
        'is_ktki_verified',
    ];

    public function nurseProfile(): BelongsTo
    {
        return $this->belongsTo(NurseProfile::class);
    }

    /**
     * Mendapatkan URL akses dokumen yang aman (Signed / Cloud / Local)
     */
    public function getFileUrlAttribute(): string
    {
        if (empty($this->file_path)) {
            return '';
        }

        // Jika merupakan file contoh testing lokal
        if (str_starts_with($this->file_path, 'samples/')) {
            return asset($this->file_path);
        }

        $driver = $this->storage_driver ?: 'public';

        try {
            // Jika disimpan di Amazon S3 / Cloud Storage
            if ($driver === 's3') {
                $disk = Storage::disk('s3');
                if (method_exists($disk, 'temporaryUrl')) {
                    return $disk->temporaryUrl($this->file_path, now()->addMinutes(30));
                }
                return $disk->url($this->file_path);
            }

            // Jika disimpan di driver public local
            return Storage::disk('public')->url($this->file_path);
        } catch (\Throwable $e) {
            return asset('storage/' . $this->file_path);
        }
    }

    /**
     * Cek apakah dokumen ini sudah divalidasi oleh KTKI Kemenkes
     */
    public function getIsKtkiVerifiedAttribute(): bool
    {
        return !is_null($this->ktki_verified_at);
    }
}
