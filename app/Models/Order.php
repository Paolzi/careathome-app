<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'patient_id',
        'service_catalog_id',
        'nurse_id',
        'scheduled_date',
        'scheduled_time',
        'patient_name',
        'patient_phone',
        'address',
        'latitude',
        'longitude',
        'distance_km',
        'clinical_notes',
        'medical_photo_path',
        'base_price',
        'transport_fee',
        'platform_fee',
        'total_amount',
        'commission_amount',
        'nurse_earnings',
        'status',
        'payment_status',
        'cancellation_reason',
        'cancelled_at',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'latitude' => 'float',
        'longitude' => 'float',
        'distance_km' => 'float',
        'base_price' => 'float',
        'transport_fee' => 'float',
        'platform_fee' => 'float',
        'total_amount' => 'float',
        'commission_amount' => 'float',
        'nurse_earnings' => 'float',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Relasi ke akun Pasien (pemesan).
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    /**
     * Relasi ke Katalog Layanan Medis.
     */
    public function serviceCatalog(): BelongsTo
    {
        return $this->belongsTo(ServiceCatalog::class, 'service_catalog_id');
    }

    /**
     * Relasi ke Mitra Perawat yang ditugaskan/menerima order.
     */
    public function nurse(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nurse_id');
    }

    /**
     * Engine Perhitungan Price Breakdown.
     * Aturan:
     * - Base Price: Tarif tindakan katalog
     * - Transport Fee: Rp 10.000 (3 km pertama) + Rp 2.500/km berikutnya (maksimal radius 10 km)
     * - Platform Service Fee: Flat Rp 5.000
     * - Total: Base Price + Transport Fee + Platform Fee
     * - Komisi Platform: 15% dari Base Price
     * - Porsi Perawat: 85% Base Price + 100% Transport Fee
     */
    public static function calculatePriceBreakdown(float $basePrice, float $distanceKm = 0.0): array
    {
        $distance = max(0.0, round($distanceKm, 2));

        // Kalkulasi ongkos transport bertahap
        if ($distance <= 0.0) {
            $transportFee = 10000.0; // Minimal radius awal
        } elseif ($distance <= 3.0) {
            $transportFee = 10000.0;
        } else {
            $extraKm = ceil($distance - 3.0);
            $transportFee = 10000.0 + ($extraKm * 2500.0);
        }

        $platformFee = 5000.0;
        $totalAmount = $basePrice + $transportFee + $platformFee;

        // Pembagian finansial (KF-10 & BAB IV)
        $commissionAmount = round($basePrice * 0.15, 2);
        $nurseEarnings = round(($basePrice * 0.85) + $transportFee, 2);

        return [
            'base_price' => $basePrice,
            'distance_km' => $distance,
            'transport_fee' => $transportFee,
            'platform_fee' => $platformFee,
            'total_amount' => $totalAmount,
            'commission_amount' => $commissionAmount,
            'nurse_earnings' => $nurseEarnings,
            'formatted' => [
                'base_price' => 'Rp ' . number_format($basePrice, 0, ',', '.'),
                'transport_fee' => 'Rp ' . number_format($transportFee, 0, ',', '.'),
                'platform_fee' => 'Rp ' . number_format($platformFee, 0, ',', '.'),
                'total_amount' => 'Rp ' . number_format($totalAmount, 0, ',', '.'),
                'commission_amount' => 'Rp ' . number_format($commissionAmount, 0, ',', '.'),
                'nurse_earnings' => 'Rp ' . number_format($nurseEarnings, 0, ',', '.'),
            ],
        ];
    }

    /**
     * Generator nomor pesanan unik resmi (e.g. CAH-20261025-0001).
     */
    public static function generateOrderNumber(): string
    {
        $prefix = 'CAH-' . date('Ymd') . '-';
        $todayCount = self::whereDate('created_at', today())->count() + 1;
        $orderNumber = $prefix . str_pad($todayCount, 4, '0', STR_PAD_LEFT);

        // Pastikan tidak ada duplikasi
        while (self::where('order_number', $orderNumber)->exists()) {
            $todayCount++;
            $orderNumber = $prefix . str_pad($todayCount, 4, '0', STR_PAD_LEFT);
        }

        return $orderNumber;
    }

    /**
     * Label status manusiawi dan warna badge.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
            'diterima' => 'Diterima Perawat',
            'dalam_perjalanan' => 'Perawat Dalam Perjalanan',
            'tiba' => 'Perawat Tiba di Lokasi',
            'tindakan_berlangsung' => 'Tindakan Berlangsung',
            'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }
}
