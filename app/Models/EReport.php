<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'nurse_id',
        'patient_id',
        'systolic',
        'diastolic',
        'body_temp',
        'pulse_rate',
        'spo2',
        'gds',
        'clinical_notes',
        'family_education',
    ];

    protected $casts = [
        'systolic' => 'integer',
        'diastolic' => 'integer',
        'body_temp' => 'float',
        'pulse_rate' => 'integer',
        'spo2' => 'integer',
        'gds' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function nurse(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nurse_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(EReportPhoto::class, 'e_report_id');
    }

    /**
     * Kategori Tekanan Darah (AHA / Kemenkes).
     */
    public function getBpCategoryAttribute(): array
    {
        $sys = $this->systolic;
        $dia = $this->diastolic;

        if ($sys < 120 && $dia < 80) {
            return ['label' => 'Normal', 'color' => 'emerald', 'bg' => 'bg-emerald-50 text-emerald-800 border-emerald-200'];
        } elseif (($sys >= 120 && $sys <= 129) && $dia < 80) {
            return ['label' => 'Meningkat', 'color' => 'amber', 'bg' => 'bg-amber-50 text-amber-800 border-amber-200'];
        } elseif (($sys >= 130 && $sys <= 139) || ($dia >= 80 && $dia <= 89)) {
            return ['label' => 'Hipertensi Tk. 1', 'color' => 'orange', 'bg' => 'bg-orange-50 text-orange-800 border-orange-200'];
        } else {
            return ['label' => 'Hipertensi Tk. 2', 'color' => 'rose', 'bg' => 'bg-rose-50 text-rose-800 border-rose-200'];
        }
    }

    /**
     * Kategori Suhu Tubuh.
     */
    public function getTempCategoryAttribute(): array
    {
        $t = (float) $this->body_temp;
        if ($t < 36.0) {
            return ['label' => 'Hipotermia', 'color' => 'blue', 'bg' => 'bg-blue-50 text-blue-800 border-blue-200'];
        } elseif ($t <= 37.5) {
            return ['label' => 'Normal', 'color' => 'emerald', 'bg' => 'bg-emerald-50 text-emerald-800 border-emerald-200'];
        } elseif ($t <= 38.5) {
            return ['label' => 'Subfebris / Demam Ringan', 'color' => 'amber', 'bg' => 'bg-amber-50 text-amber-800 border-amber-200'];
        } else {
            return ['label' => 'Demam Tinggi', 'color' => 'rose', 'bg' => 'bg-rose-50 text-rose-800 border-rose-200'];
        }
    }

    /**
     * Kategori Saturasi Oksigen (SpO2).
     */
    public function getSpo2CategoryAttribute(): array
    {
        $s = $this->spo2;
        if ($s >= 95) {
            return ['label' => 'Normal', 'color' => 'emerald', 'bg' => 'bg-emerald-50 text-emerald-800 border-emerald-200'];
        } elseif ($s >= 90) {
            return ['label' => 'Hipoksia Ringan', 'color' => 'amber', 'bg' => 'bg-amber-50 text-amber-800 border-amber-200'];
        } else {
            return ['label' => 'Hipoksia Berat', 'color' => 'rose', 'bg' => 'bg-rose-50 text-rose-800 border-rose-200'];
        }
    }

    /**
     * Kategori Denyut Nadi.
     */
    public function getPulseCategoryAttribute(): array
    {
        $p = $this->pulse_rate;
        if ($p < 60) {
            return ['label' => 'Bradikardia', 'color' => 'amber', 'bg' => 'bg-amber-50 text-amber-800 border-amber-200'];
        } elseif ($p <= 100) {
            return ['label' => 'Normal', 'color' => 'emerald', 'bg' => 'bg-emerald-50 text-emerald-800 border-emerald-200'];
        } else {
            return ['label' => 'Takikardia', 'color' => 'rose', 'bg' => 'bg-rose-50 text-rose-800 border-rose-200'];
        }
    }

    /**
     * Kategori Gula Darah Sewaktu (GDS).
     */
    public function getGdsCategoryAttribute(): ?array
    {
        if (is_null($this->gds)) {
            return null;
        }

        $g = $this->gds;
        if ($g < 70) {
            return ['label' => 'Hipoglikemia', 'color' => 'rose', 'bg' => 'bg-rose-50 text-rose-800 border-rose-200'];
        } elseif ($g <= 140) {
            return ['label' => 'Normal', 'color' => 'emerald', 'bg' => 'bg-emerald-50 text-emerald-800 border-emerald-200'];
        } elseif ($g <= 199) {
            return ['label' => 'Pre-Diabetes', 'color' => 'amber', 'bg' => 'bg-amber-50 text-amber-800 border-amber-200'];
        } else {
            return ['label' => 'Hiperglikemia', 'color' => 'rose', 'bg' => 'bg-rose-50 text-rose-800 border-rose-200'];
        }
    }
}

