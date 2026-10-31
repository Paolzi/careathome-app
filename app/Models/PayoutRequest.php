<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayoutRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'nurse_user_id',
        'amount',
        'bank_name',
        'account_number',
        'account_holder',
        'status',
        'proof_path',
        'transferred_at',
        'processed_by',
        'rejection_reason',
    ];

    protected $casts = [
        'amount' => 'integer',
        'transferred_at' => 'datetime',
    ];

    public function nurse(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nurse_user_id');
    }

    public function nurseUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nurse_user_id');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }
}
