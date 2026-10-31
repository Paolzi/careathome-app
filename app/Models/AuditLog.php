<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper cepat untuk mencatat audit log.
     */
    public static function log(
        mixed $actionOrUserId,
        mixed $targetOrAction = null,
        mixed $oldValuesOrTarget = null,
        mixed $newValuesOrOld = null,
        mixed $userIdOrNew = null
    ): self {
        $userId = auth()->id();
        $action = '';
        $target = null;
        $oldValues = null;
        $newValues = null;

        if (is_numeric($actionOrUserId)) {
            // Dipanggil: log($userId, $action, $target, $oldValues, $newValues)
            $userId = (int) $actionOrUserId;
            $action = (string) $targetOrAction;
            $target = $oldValuesOrTarget;
            $oldValues = is_array($newValuesOrOld) ? $newValuesOrOld : null;
            $newValues = is_array($userIdOrNew) ? $userIdOrNew : null;
        } else {
            // Dipanggil: log($action, $target, $oldValues, $newValues, $userId)
            $action = (string) $actionOrUserId;
            $target = $targetOrAction;
            $oldValues = is_array($oldValuesOrTarget) ? $oldValuesOrTarget : null;
            $newValues = is_array($newValuesOrOld) ? $newValuesOrOld : null;
            if (is_numeric($userIdOrNew)) {
                $userId = (int) $userIdOrNew;
            }
        }

        $auditableType = null;
        $auditableId = null;

        if ($target instanceof Model) {
            $auditableType = get_class($target);
            $auditableId = $target->getKey();
        } elseif (is_string($target)) {
            $auditableType = $target;
        }

        return static::create([
            'user_id' => $userId,
            'action' => $action,
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()?->ip() ?? '127.0.0.1',
        ]);
    }
}
