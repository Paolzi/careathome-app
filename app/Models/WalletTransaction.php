<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'nurse_wallet_id',
        'order_id',
        'type',
        'amount',
        'platform_fee',
        'net_amount',
        'status',
        'balance_after',
        'payment_id',
        'payout_request_id',
        'description',
    ];

    protected $casts = [
        'amount' => 'integer',
        'platform_fee' => 'integer',
        'net_amount' => 'integer',
        'balance_after' => 'integer',
    ];

    /**
     * Pastikan tabel wallet_transactions adalah append-only.
     */
    protected static function booted(): void
    {
        static::updating(function () {
            throw new \RuntimeException('Wallet transactions are immutable and cannot be updated.');
        });

        static::deleting(function () {
            throw new \RuntimeException('Wallet transactions are immutable and cannot be deleted.');
        });
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function nurseWallet(): BelongsTo
    {
        return $this->belongsTo(NurseWallet::class, 'nurse_wallet_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function payoutRequest(): BelongsTo
    {
        return $this->belongsTo(PayoutRequest::class);
    }
}
