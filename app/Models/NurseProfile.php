<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NurseProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nik',
        'str_number',
        'str_expiry',
        'sip_number',
        'sip_expiry',
        'sip_institution',
        'specialization',
        'verification_status',
        'is_available',
        'balance',
        'rating_avg',
        'total_reviews',
    ];

    protected function casts(): array
    {
        return [
            'str_expiry' => 'date',
            'sip_expiry' => 'date',
            'is_available' => 'boolean',
            'balance' => 'decimal:2',
            'rating_avg' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(NurseDocument::class);
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(NurseVerification::class);
    }
}
