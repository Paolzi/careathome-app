<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NurseWallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'nurse_id',
        'balance',
        'pending_balance',
    ];

    protected $casts = [
        'balance' => 'integer',
        'pending_balance' => 'integer',
    ];

    public function nurse(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nurse_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class, 'nurse_wallet_id');
    }

    /**
     * Dapatkan atau buat dompet nakes berdasarkan user id.
     */
    public static function getOrCreateForNurse(int $nurseId): self
    {
        return static::firstOrCreate(
            ['nurse_id' => $nurseId],
            [
                'balance' => 0,
                'pending_balance' => 0,
            ]
        );
    }
}

