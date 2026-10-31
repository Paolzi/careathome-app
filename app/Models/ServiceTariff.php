<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceTariff extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'price',
        'effective_from',
        'created_by',
    ];

    protected $casts = [
        'price' => 'integer',
        'effective_from' => 'datetime',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(ServiceCatalog::class, 'service_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
