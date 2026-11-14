<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class NurseSchedule extends Model
{
    use HasFactory;

    public const DAYS = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];

    protected $fillable = [
        'nurse_id',
        'day_of_week',
        'day_name',
        'start_time',
        'end_time',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function nurse(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nurse_id');
    }

    /**
     * Pastikan perawat memiliki jadwal default 7 hari (Senin - Minggu, 08:00 - 20:00 WIB)
     * apabila belum pernah diatur sebelumnya.
     */
    public static function ensureDefaultSchedulesForNurse(int $nurseId): Collection
    {
        if (! Schema::hasTable('nurse_schedules')) {
            return new Collection();
        }

        $existing = self::where('nurse_id', $nurseId)->orderBy('day_of_week')->get();

        if ($existing->count() < 7) {
            foreach (self::DAYS as $dayNum => $dayName) {
                self::firstOrCreate(
                    [
                        'nurse_id' => $nurseId,
                        'day_of_week' => $dayNum,
                    ],
                    [
                        'day_name' => $dayName,
                        'start_time' => '08:00',
                        'end_time' => '21:00',
                        'is_active' => true,
                    ]
                );
            }
            $existing = self::where('nurse_id', $nurseId)->orderBy('day_of_week')->get();
        }

        return $existing;
    }

    /**
     * Ekstrak jam mulai dalam format HH:MM dari string jadwal (misal: "14:30 - 16:30" atau "14:30 WIB").
     */
    public static function extractStartTime(?string $timeStr, ?Carbon $fallback = null): string
    {
        $fallbackTime = ($fallback ?? Carbon::now('Asia/Jakarta'))->format('H:i');
        if (! $timeStr) {
            return $fallbackTime;
        }

        if (preg_match('/(\d{1,2})[:.](\d{2})/', $timeStr, $matches)) {
            $h = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $m = $matches[2];
            return "{$h}:{$m}";
        }

        return $fallbackTime;
    }

    /**
     * Cek ketersediaan jadwal perawat untuk tipe booking "now" (Pesan Sekarang) atau "scheduled" (Pesan Untuk Nanti).
     * Juga memeriksa apakah perawat sedang bentrok dengan order aktif pada tanggal dan jam tersebut.
     */
    public static function checkAvailability(
        int $nurseId,
        string $bookingType = 'now',
        ?string $scheduledDate = null,
        ?string $scheduledTime = null
    ): array {
        $nowWib = Carbon::now('Asia/Jakarta');

        if ($bookingType === 'now') {
            $targetDate = $nowWib->copy();
            $targetTimeStr = $nowWib->format('H:i');
        } else {
            try {
                $targetDate = $scheduledDate
                    ? Carbon::parse($scheduledDate, 'Asia/Jakarta')
                    : $nowWib->copy();
            } catch (\Throwable $e) {
                $targetDate = $nowWib->copy();
            }
            $targetTimeStr = self::extractStartTime($scheduledTime, $nowWib);
        }

        $dayOfWeek = (int) $targetDate->dayOfWeekIso; // 1 (Senin) .. 7 (Minggu)
        $dayName = self::DAYS[$dayOfWeek] ?? 'Hari Ini';

        $schedules = self::ensureDefaultSchedulesForNurse($nurseId);
        /** @var self|null $daySchedule */
        $daySchedule = $schedules->firstWhere('day_of_week', $dayOfWeek);

        if (! $daySchedule || ! $daySchedule->is_active) {
            return [
                'available' => false,
                'reason' => "Libur / tidak berpraktik pada hari {$dayName}",
                'day_of_week' => $dayOfWeek,
                'day_name' => $dayName,
                'schedule_window' => 'Libur',
                'target_date' => $targetDate->toDateString(),
                'target_time_wib' => $targetTimeStr . ' WIB',
            ];
        }

        $startTime = substr($daySchedule->start_time, 0, 5);
        $endTime = substr($daySchedule->end_time, 0, 5);
        $scheduleWindow = "{$startTime} - {$endTime} WIB";

        // Periksa apakah jam target berada di dalam rentang start_time s/d end_time
        if ($targetTimeStr < $startTime || $targetTimeStr > $endTime) {
            return [
                'available' => false,
                'reason' => "Di luar jam praktik {$dayName} ({$scheduleWindow})",
                'day_of_week' => $dayOfWeek,
                'day_name' => $dayName,
                'schedule_window' => $scheduleWindow,
                'target_date' => $targetDate->toDateString(),
                'target_time_wib' => $targetTimeStr . ' WIB',
            ];
        }

        // Periksa bentrok pesanan aktif pada tanggal tersebut
        $activeStatuses = ['menunggu_konfirmasi', 'diterima', 'dalam_perjalanan', 'tiba', 'tindakan_berlangsung'];
        $activeOrdersOnDate = Order::where('nurse_id', $nurseId)
            ->whereDate('scheduled_date', $targetDate->toDateString())
            ->whereIn('status', $activeStatuses)
            ->get();

        foreach ($activeOrdersOnDate as $existingOrder) {
            if ($bookingType === 'now' && in_array($existingOrder->status, ['dalam_perjalanan', 'tiba', 'tindakan_berlangsung'])) {
                return [
                    'available' => false,
                    'reason' => 'Sedang menangani kunjungan pasien aktif saat ini',
                    'day_of_week' => $dayOfWeek,
                    'day_name' => $dayName,
                    'schedule_window' => $scheduleWindow,
                    'target_date' => $targetDate->toDateString(),
                    'target_time_wib' => $targetTimeStr . ' WIB',
                ];
            }

            $existingStart = self::extractStartTime($existingOrder->scheduled_time);
            try {
                $t1 = Carbon::createFromFormat('H:i', $targetTimeStr, 'Asia/Jakarta');
                $t2 = Carbon::createFromFormat('H:i', $existingStart, 'Asia/Jakarta');
                if (abs($t1->diffInMinutes($t2)) < 90) {
                    return [
                        'available' => false,
                        'reason' => "Sudah terjadwal melayani pasien pada pukul {$existingStart} WIB",
                        'day_of_week' => $dayOfWeek,
                        'day_name' => $dayName,
                        'schedule_window' => $scheduleWindow,
                        'target_date' => $targetDate->toDateString(),
                        'target_time_wib' => $targetTimeStr . ' WIB',
                    ];
                }
            } catch (\Throwable $e) {
                // Abaikan jika gagal parse
            }
        }

        return [
            'available' => true,
            'reason' => "Tersedia hari {$dayName} ({$scheduleWindow})",
            'day_of_week' => $dayOfWeek,
            'day_name' => $dayName,
            'schedule_window' => $scheduleWindow,
            'target_date' => $targetDate->toDateString(),
            'target_time_wib' => $targetTimeStr . ' WIB',
        ];
    }
}

