<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCatalog extends Model
{
    use HasFactory;

    protected $table = 'service_catalogs';

    protected $fillable = [
        'name',
        'slug',
        'category',
        'category_badge',
        'badge_color',
        'image_emoji',
        'description',
        'indications',
        'contraindications',
        'inclusions',
        'estimated_duration',
        'estimated_duration_minutes',
        'price',
        'is_active',
    ];

    protected $casts = [
        'inclusions' => 'array',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'estimated_duration_minutes' => 'integer',
    ];

    /**
     * Scope query untuk hanya layanan yang aktif
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query untuk pencarian nama atau deskripsi
     */
    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        if (blank($keyword)) {
            return $query;
        }

        return $query->where(function ($q) use ($keyword) {
            $q->where('name', 'like', "%{$keyword}%")
              ->orWhere('description', 'like', "%{$keyword}%")
              ->orWhere('category', 'like', "%{$keyword}%");
        });
    }

    /**
     * Scope query berdasarkan kategori
     */
    public function scopeCategory(Builder $query, ?string $category): Builder
    {
        if (blank($category) || $category === 'Semua') {
            return $query;
        }

        return $query->where('category', $category);
    }

    /**
     * Scope query berdasarkan rentang harga
     */
    public function scopePriceRange(Builder $query, ?float $min, ?float $max): Builder
    {
        if (!is_null($min)) {
            $query->where('price', '>=', $min);
        }

        if (!is_null($max)) {
            $query->where('price', '<=', $max);
        }

        return $query;
    }

    /**
     * Scope query pengurutan
     */
    public function scopeSort(Builder $query, ?string $sortBy): Builder
    {
        return match ($sortBy) {
            'termurah' => $query->orderBy('price', 'asc'),
            'termahal' => $query->orderBy('price', 'desc'),
            'durasi_singkat' => $query->orderBy('estimated_duration_minutes', 'asc'),
            'terlama' => $query->orderBy('estimated_duration_minutes', 'desc'),
            'terbaru' => $query->orderBy('created_at', 'desc'),
            default => $query->orderBy('name', 'asc'),
        };
    }

    /**
     * Format harga ke format Rupiah
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }
}
