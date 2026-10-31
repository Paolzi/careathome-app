<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class NurseProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nik',
        'str_number',
        'str_expiry',
        'str_expired_at',
        'sip_number',
        'sip_expiry',
        'sip_expired_at',
        'sip_institution',
        'specialization',
        'verification_status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
        'is_available',
        'is_online',
        'latitude',
        'longitude',
        'location_updated_at',
        'balance',
        'rating_avg',
        'total_reviews',
    ];

    protected function casts(): array
    {
        return [
            'str_expiry' => 'date',
            'str_expired_at' => 'date',
            'sip_expiry' => 'date',
            'sip_expired_at' => 'date',
            'is_available' => 'boolean',
            'is_online' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
            'location_updated_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'balance' => 'decimal:2',
            'rating_avg' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(NurseDocument::class);
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(NurseVerification::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(ServiceCatalog::class, 'nurse_services', 'nurse_profile_id', 'service_id')
            ->withTimestamps();
    }

    /**
     * Scope untuk perawat yang benar-benar aktif:
     * 1. verification_status == 'verified'
     * 2. is_online == true
     * 3. location_updated_at tidak lebih tua dari batas timeout (mis. 15 menit)
     * 4. User tidak dalam status suspended
     */
    public function scopeActive(Builder $query): Builder
    {
        $timeoutMinutes = Setting::getInt('location_timeout_minutes', 15);
        $thresholdTime = now()->subMinutes($timeoutMinutes);

        return $query->where('verification_status', 'verified')
            ->where('is_online', true)
            ->whereNotNull('location_updated_at')
            ->where('location_updated_at', '>=', $thresholdTime)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereHas('user', function ($q) {
                $q->where('status', '!=', 'suspended');
            });
    }

    /**
     * Scope untuk mencari perawat aktif dalam radius tertentu (default 10 km).
     * Menggunakan Bounding Box awal untuk performa indeks, lalu rumus Haversine presisi.
     */
    public function scopeNearby(Builder $query, float $latitude, float $longitude, ?float $radiusKm = null): Builder
    {
        $radius = $radiusKm ?? Setting::getFloat('max_radius_km', 10.0);

        // Derajat perkiraan bounding box
        // 1 deg lat ~ 111.045 km
        $latDelta = $radius / 111.045;
        // 1 deg lng ~ 111.045 * cos(lat) km
        $lngDelta = $radius / (111.045 * max(cos(deg2rad($latitude)), 0.0001));

        $minLat = $latitude - $latDelta;
        $maxLat = $latitude + $latDelta;
        $minLng = $longitude - $lngDelta;
        $maxLng = $longitude + $lngDelta;

        // Bounding box filter
        $query->whereBetween('latitude', [$minLat, $maxLat])
            ->whereBetween('longitude', [$minLng, $maxLng]);

        // Haversine SQL formula (radius bumi ~ 6371 km)
        $haversine = "(6371 * acos(least(1.0, greatest(-1.0, cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude))))))";

        $query->select('nurse_profiles.*')
            ->selectRaw("round({$haversine}, 2) AS distance_km", [$latitude, $longitude, $latitude])
            ->havingRaw("distance_km <= ?", [$radius])
            ->orderBy('distance_km', 'asc');

        return $query;
    }

    /**
     * Hitung jarak langsung (Haversine) dari koordinat perawat ini ke koordinat tujuan.
     */
    public function distanceTo(float $targetLat, float $targetLng): float
    {
        if ($this->latitude === null || $this->longitude === null) {
            return 999999.0;
        }

        $earthRadius = 6371.0; // km
        $dLat = deg2rad($targetLat - $this->latitude);
        $dLon = deg2rad($targetLng - $this->longitude);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($this->latitude)) * cos(deg2rad($targetLat)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }
}
