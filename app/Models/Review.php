<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'patient_id',
        'nurse_id',
        'rating',
        'comment',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    protected static function booted(): void
    {
        // Otomatis sinkronisasi rating rata-rata & jumlah ulasan ke profil perawat
        static::saved(function (Review $review) {
            self::syncNurseRating($review->nurse_id);
        });

        static::deleted(function (Review $review) {
            self::syncNurseRating($review->nurse_id);
        });
    }

    /**
     * Hitung ulang dan simpan rating rata-rata ke profil perawat.
     */
    public static function syncNurseRating(int $nurseId): void
    {
        $profile = NurseProfile::where('user_id', $nurseId)->first();
        if ($profile) {
            $avgRating = self::where('nurse_id', $nurseId)->avg('rating') ?: 0.0;
            $totalCount = self::where('nurse_id', $nurseId)->count();

            $profile->update([
                'rating_avg' => round((float) $avgRating, 2),
                'total_reviews' => (int) $totalCount,
            ]);
        }
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function nurse(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nurse_id');
    }
}

