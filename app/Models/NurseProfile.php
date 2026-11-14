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
        'status_verifikasi',
        'status_legalitas',
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

    protected static function booted(): void
    {
        static::saving(function (NurseProfile $profile) {
            $status = $profile->verification_status
                ?? $profile->status_verifikasi
                ?? $profile->status_legalitas
                ?? 'draft';

            $profile->verification_status = $status;
            $profile->status_verifikasi = $status;
            $profile->status_legalitas = $status;
        });
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

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'nurse_id', 'user_id');
    }

    /**
     * Scope untuk perawat yang terverifikasi dan akunnya aktif (tidak disuspend).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function ($q) {
                $q->where('status_verifikasi', 'verified')
                  ->orWhere('verification_status', 'verified')
                  ->orWhere('status_legalitas', 'verified');
            })
            ->whereHas('user', function ($q) {
                $q->where('status', '!=', 'suspended');
            });
    }

    /**
     * Scope untuk mengurutkan perawat dari jarak terdekat tanpa membatasi radius 10 km.
     */
    public function scopeNearby(Builder $query, float $latitude, float $longitude, ?float $radiusKm = null): Builder
    {
        $haversine = "(6371 * acos(least(1.0, greatest(-1.0, cos(radians(?)) * cos(radians(coalesce(latitude, -7.3144))) * cos(radians(coalesce(longitude, 112.7304)) - radians(?)) + sin(radians(?)) * sin(radians(coalesce(latitude, -7.3144)))))))";

        $query->select('nurse_profiles.*')
            ->selectRaw("round({$haversine} * 1.35, 2) AS distance", [$latitude, $longitude, $latitude])
            ->selectRaw("round({$haversine} * 1.35, 2) AS distance_km", [$latitude, $longitude, $latitude])
            ->orderBy('distance', 'asc');

        if ($radiusKm !== null && $radiusKm > 0) {
            $query->having('distance', '<=', $radiusKm);
        }

        return $query;
    }

    /**
     * Hitung jarak rute jalan raya sebenarnya (via OSRM Public API) dari koordinat perawat ini ke koordinat tujuan.
     */
    public function distanceTo(float $targetLat, float $targetLng): float
    {
        $lat = $this->latitude !== null ? (float) $this->latitude : -7.3144;
        $lng = $this->longitude !== null ? (float) $this->longitude : 112.7304;

        return \App\Services\OsrmRoutingService::getDrivingDistanceKm($lat, $lng, $targetLat, $targetLng);
    }
}
