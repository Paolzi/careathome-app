<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'order_number',
        'transaction_id',
        'payment_method',
        'payment_type',
        'bank',
        'va_number',
        'gross_amount',
        'currency',
        'payment_status',
        'escrow_status',
        'snap_token',
        'snap_redirect_url',
        'payment_payload',
        'paid_at',
        'settlement_time',
        'refund_amount',
        'refunded_at',
        'refund_notes',
    ];

    protected $casts = [
        'gross_amount' => 'float',
        'refund_amount' => 'float',
        'payment_payload' => 'array',
        'paid_at' => 'datetime',
        'settlement_time' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    /**
     * Relasi ke entitas Pesanan Kunjungan (Order).
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Cek apakah pembayaran telah lunas (settled) dan dana aman di Escrow.
     */
    public function isSettled(): bool
    {
        return in_array($this->payment_status, ['settlement', 'paid', 'capture']);
    }

    /**
     * Cek apakah dana escrow telah dikembalikan ke pasien.
     */
    public function isRefunded(): bool
    {
        return $this->payment_status === 'refunded' || $this->escrow_status === 'refunded';
    }

    /**
     * Label representasi status escrow.
     */
    public function getEscrowStatusLabelAttribute(): string
    {
        return match ($this->escrow_status) {
            'held' => 'Dana Tertahan di Rekening Escrow',
            'released' => 'Dana Diteruskan ke Perawat',
            'refunded' => 'Dana Escrow Dikembalikan 100%',
            default => 'Belum Ada Dana Escrow',
        };
    }

    /**
     * Label representasi status pembayaran.
     */
    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            'settlement', 'paid' => 'Lunas (Settled)',
            'pending' => 'Menunggu Pembayaran',
            'expire' => 'Kedaluwarsa',
            'cancel' => 'Dibatalkan',
            'refunded' => 'Dikembalikan (Refunded)',
            'failed' => 'Gagal',
            default => ucfirst($this->payment_status),
        };
    }
}
