<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class EReportPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'e_report_id',
        'photo_path',
        'caption',
    ];

    public function eReport(): BelongsTo
    {
        return $this->belongsTo(EReport::class, 'e_report_id');
    }

    /**
     * URL foto tindakan medis.
     */
    public function getUrlAttribute(): string
    {
        if (empty($this->photo_path)) {
            return asset('images/placeholder-medical.svg');
        }

        if (str_starts_with($this->photo_path, 'http://') || str_starts_with($this->photo_path, 'https://')) {
            return $this->photo_path;
        }

        return Storage::url($this->photo_path);
    }
}

