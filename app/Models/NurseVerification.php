<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NurseVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'nurse_profile_id',
        'admin_id',
        'status',
        'rejection_reason',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }

    public function nurseProfile(): BelongsTo
    {
        return $this->belongsTo(NurseProfile::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
