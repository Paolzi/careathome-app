<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NurseDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'nurse_profile_id',
        'document_type',
        'file_path',
        'original_name',
        'file_size',
        'mime_type',
        'status',
        'notes',
    ];

    public function nurseProfile(): BelongsTo
    {
        return $this->belongsTo(NurseProfile::class);
    }
}
