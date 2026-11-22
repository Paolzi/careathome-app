<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $order_number
 * @property int|null $patient_id
 * @property int|null $service_catalog_id
 * @property int|null $nurse_id
 * @property string $status
 * @property string $payment_status
 * @property float|null $calculated_distance
 * @property int|float|null $estimated_nurse_earnings
 * @property-read string $status_label
 * @property-read \App\Models\User|null $patient
 * @property-read \App\Models\User|null $nurse
 * @property-read \App\Models\ServiceCatalog|null $serviceCatalog
 * @property-read \App\Models\EReport|null $eReport
 * @property-read \App\Models\Review|null $review
 */
class Order extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'menunggu_konfirmasi';
    public const STATUS_ACCEPTED = 'diterima';
    public const STATUS_ON_THE_WAY = 'dalam_perjalanan';
    public const STATUS_ARRIVED = 'tiba';
    public const STATUS_IN_PROGRESS = 'tindakan_berlangsung';
    public const STATUS_COMPLETED = 'selesai';
    public const STATUS_CANCELLED = 'dibatalkan';
    public const STATUS_REJECTED = 'ditolak';

    protected $fillable = [
        'order_number',
        'booking_type',
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
        'patient_latitude',
        'patient_longitude',
        'distance_km',
        'estimated_travel_minutes',
        'estimated_arrival_at',
        'departed_at',
        'arrived_at',
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
        'refund_status',
        'refund_amount',
        'refunded_at',
        'refund_notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'latitude' => 'float',
        'longitude' => 'float',
        'patient_latitude' => 'float',
        'patient_longitude' => 'float',
        'distance_km' => 'float',
        'estimated_travel_minutes' => 'integer',
        'estimated_arrival_at' => 'datetime',
        'departed_at' => 'datetime',
        'arrived_at' => 'datetime',
        'base_price' => 'float',
        'transport_fee' => 'float',
        'platform_fee' => 'float',
        'total_amount' => 'float',
        'commission_amount' => 'float',
        'nurse_earnings' => 'float',
        'refund_amount' => 'float',
        'cancelled_at' => 'datetime',
        'refunded_at' => 'datetime',
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
     * Relasi ke riwayat pembayaran & transaksi gateway escrow.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'order_id');
    }

    /**
     * Relasi ke pembayaran terakhir yang tercatat.
     */
    public function latestPayment()
    {
        return $this->hasOne(Payment::class, 'order_id')->latestOfMany();
    }

    /**
     * Relasi ke Lembar E-Report Klinis.
     */
    public function eReport(): HasOne
    {
        return $this->hasOne(EReport::class, 'order_id');
    }

    /**
     * Relasi ke Ulasan & Rating Pasien.
     */
    public function review(): HasOne
    {
        return $this->hasOne(Review::class, 'order_id');
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
        $basePriceInt = (int) round($basePrice);

        // Kalkulasi ongkos transport realistis per jarak (mulai Rp 3.000 untuk <= 0.5 km, lalu Rp 2.500/km dibulatkan ke Rp 500)
        if ($distance <= 0.5) {
            $transportFee = 3000;
        } else {
            $extraKm = $distance - 0.5;
            $rawTransport = 3000 + ($extraKm * 2500);
            $transportFee = (int) (ceil($rawTransport / 500) * 500);
        }

        $platformFee = 5000;
        $totalAmount = $basePriceInt + $transportFee + $platformFee;

        // Pembagian finansial presisi (invariant: nurseEarnings + adminTotalShare === totalAmount)
        $commissionAmount = (int) round($basePriceInt * 0.15);
        $nurseServiceShare = $basePriceInt - $commissionAmount;
        $nurseEarnings = $nurseServiceShare + $transportFee;

        return [
            'base_price' => (float) $basePriceInt,
            'distance_km' => $distance,
            'transport_fee' => (float) $transportFee,
            'platform_fee' => (float) $platformFee,
            'total_amount' => (float) $totalAmount,
            'commission_amount' => (float) $commissionAmount,
            'nurse_earnings' => (float) $nurseEarnings,
            'formatted' => [
                'base_price' => 'Rp ' . number_format($basePriceInt, 0, ',', '.'),
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
            'completed' => 'Selesai',
            'dibatalkan' => 'Dibatalkan',
            'ditolak' => 'Ditolak Perawat',
            'declined' => 'Ditolak Perawat',
            default => ucfirst($this->status),
        };
    }

    /**
     * Memeriksa apakah pesanan masih memenuhi syarat untuk dibatalkan oleh pasien.
     */
    public function isCancellable(): bool
    {
        return !in_array($this->status, ['selesai', 'completed', 'dibatalkan', 'ditolak', 'declined']);
    }

    /**
     * Label status refund escrow.
     */
    public function getRefundStatusLabelAttribute(): string
    {
        return match ($this->refund_status) {
            'refunded' => 'Dana Escrow Dikembalikan (100% Refund)',
            'pending' => 'Proses Pengembalian Escrow',
            default => 'Tidak Ada Refund',
        };
    }

    /**
     * Dapatkan objek Carbon (Asia/Jakarta) dari tanggal & jam mulai perawatan.
     */
    public function getScheduledStartCarbonWib(): \Carbon\Carbon
    {
        $nowWib = \Carbon\Carbon::now('Asia/Jakarta');
        $dateStr = $this->scheduled_date
            ? \Carbon\Carbon::parse($this->scheduled_date)->format('Y-m-d')
            : $nowWib->format('Y-m-d');

        $startTime = NurseSchedule::extractStartTime($this->scheduled_time, $nowWib);

        try {
            return \Carbon\Carbon::createFromFormat('Y-m-d H:i', "{$dateStr} {$startTime}", 'Asia/Jakarta');
        } catch (\Throwable $e) {
            return $nowWib;
        }
    }

    /**
     * Validasi Waktu Aksi Perawat:
     * Tombol aksi perjalanan/perawatan ("Mulai Perjalanan", "Sampai di Lokasi", "Mulai Perawatan", "Selesai Perawatan")
     * baru akan aktif maksimal 30 menit sebelum jadwal jam perawatan dimulai (WIB).
     */
    public function canNursePerformAction(): array
    {
        $nowWib = \Carbon\Carbon::now('Asia/Jakarta');
        $scheduledStart = $this->getScheduledStartCarbonWib();
        $unlockAt = $scheduledStart->copy()->subMinutes(30);

        if (($this->booking_type ?? 'scheduled') === 'now') {
            return [
                'allowed' => true,
                'scheduled_start_wib' => $scheduledStart->format('H:i') . ' WIB',
                'unlock_time_wib' => $unlockAt->format('H:i') . ' WIB',
                'unlock_datetime_iso' => $unlockAt->toIso8601String(),
                'minutes_until_unlock' => 0,
                'message' => 'Siap dijalankan sekarang (Pesan Sekarang - WIB)',
            ];
        }

        $isAllowed = $nowWib->greaterThanOrEqualTo($unlockAt);
        $minutesUntilUnlock = $isAllowed ? 0 : (int) ceil($nowWib->diffInSeconds($unlockAt) / 60);

        return [
            'allowed' => $isAllowed,
            'scheduled_start_wib' => $scheduledStart->format('H:i') . ' WIB',
            'unlock_time_wib' => $unlockAt->format('H:i') . ' WIB',
            'unlock_datetime_iso' => $unlockAt->toIso8601String(),
            'minutes_until_unlock' => $minutesUntilUnlock,
            'message' => $isAllowed
                ? 'Waktu kunjungan telah memasuki jendela aktif (WIB)'
                : 'Tombol akan aktif 30 menit sebelum jadwal perawatan (Pukul ' . $unlockAt->format('H:i') . ' WIB)',
        ];
    }

    /**
     * Ringkasan estimasi waktu kedatangan perawat (ETA) yang logis untuk ditampilkan di jadwal pasien.
     */
    public function getArrivalEstimateSummary(): array
    {
        $nowWib = \Carbon\Carbon::now('Asia/Jakarta');
        $distance = max(0.1, (float) ($this->distance_km ?? 2.5));
        $travelMinutes = $this->estimated_travel_minutes
            ? (int) $this->estimated_travel_minutes
            : max(3, (int) ceil(($distance / 30.0) * 60.0));

        if (in_array($this->status, ['tiba', 'tindakan_berlangsung', 'selesai', 'completed'], true)) {
            $arrivedAt = $this->arrived_at ? $this->arrived_at->timezone('Asia/Jakarta') : null;

            return [
                'state' => 'arrived',
                'travel_minutes' => $travelMinutes,
                'remaining_minutes' => 0,
                'eta_time_wib' => $arrivedAt ? $arrivedAt->format('H:i') . ' WIB' : null,
                'headline' => $arrivedAt
                    ? 'Tiba di lokasi pukul ' . $arrivedAt->format('H:i') . ' WIB'
                    : 'Perawat telah tiba di lokasi',
                'subtext' => $this->status === 'tindakan_berlangsung'
                    ? 'Tindakan keperawatan sedang berlangsung'
                    : 'Perawat sudah berada di rumah pasien',
            ];
        }

        if ($this->status === 'dalam_perjalanan') {
            $etaAt = ($this->estimated_arrival_at ?? $nowWib->copy()->addMinutes($travelMinutes))
                ->copy()
                ->timezone('Asia/Jakarta');
            $remainingSeconds = $nowWib->diffInSeconds($etaAt, false);
            $remainingMinutes = max(1, (int) ceil(max(0, $remainingSeconds) / 60));

            return [
                'state' => 'on_the_way',
                'travel_minutes' => $travelMinutes,
                'remaining_minutes' => $remainingMinutes,
                'eta_time_wib' => $etaAt->format('H:i') . ' WIB',
                'headline' => 'Perkiraan Tiba ~' . $etaAt->format('H:i') . ' WIB',
                'subtext' => "Dalam perjalanan (± {$remainingMinutes} menit lagi)",
            ];
        }

        if (($this->booking_type ?? 'scheduled') === 'now') {
            $baseTime = $this->created_at
                ? $this->created_at->copy()->timezone('Asia/Jakarta')
                : $nowWib->copy();
            $etaAt = ($this->estimated_arrival_at ?? $baseTime->addMinutes($travelMinutes))
                ->copy()
                ->timezone('Asia/Jakarta');

            return [
                'state' => 'scheduled',
                'travel_minutes' => $travelMinutes,
                'remaining_minutes' => $travelMinutes,
                'eta_time_wib' => $etaAt->format('H:i') . ' WIB',
                'headline' => 'Estimasi Tiba ~' . $etaAt->format('H:i') . ' WIB',
                'subtext' => "Pesan Sekarang • Perkiraan tempuh ± {$travelMinutes} menit",
            ];
        }

        $scheduledStart = $this->getScheduledStartCarbonWib();
        $etaAt = ($this->estimated_arrival_at ?? $scheduledStart)->copy()->timezone('Asia/Jakarta');

        return [
            'state' => 'scheduled',
            'travel_minutes' => $travelMinutes,
            'remaining_minutes' => $travelMinutes,
            'eta_time_wib' => $etaAt->format('H:i') . ' WIB',
            'headline' => 'Target Tiba ' . $etaAt->format('H:i') . ' WIB',
            'subtext' => "Sesuai jadwal • Estimasi tempuh perawat ± {$travelMinutes} menit",
        ];
    }
}
