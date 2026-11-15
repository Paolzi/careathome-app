<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    /**
     * Default daftar jam kerja per hari (format HH:MM WIB).
     * Perawat dapat menambah/menghapus jam berapa saja per hari menggunakan tombol (+).
     */
    public const DEFAULT_TIME_SLOTS = [
        '08:00',
        '10:00',
        '13:00',
        '15:00',
        '19:00',
        '21:00',
    ];

    /**
     * Durasi standar blok waktu per sesi pesanan (dalam menit).
     */
    public const SLOT_DURATION_MINUTES = 120;

    protected $fillable = [
        'nurse_id',
        'day_of_week',
        'day_name',
        'start_time',
        'end_time',
        'time_slots',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'time_slots' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function nurse(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nurse_id');
    }

    /**
     * Normalisasi dan urutkan daftar jam kerja (HH:MM).
     */
    public static function normalizeTimeSlots(mixed $slots): array
    {
        if (is_string($slots)) {
            $decoded = json_decode($slots, true);
            $slots = is_array($decoded) ? $decoded : array_filter(array_map('trim', explode(',', $slots)));
        }

        if (!is_array($slots)) {
            return [];
        }

        $normalized = [];
        foreach ($slots as $slot) {
            if (!is_string($slot) && !is_numeric($slot)) {
                continue;
            }
            $str = trim((string) $slot);
            if (preg_match('/^(\d{1,2})[:.](\d{2})/', $str, $m)) {
                $h = max(0, min(23, (int) $m[1]));
                $min = max(0, min(59, (int) $m[2]));
                $normalized[] = sprintf('%02d:%02d', $h, $min);
            }
        }

        $normalized = array_values(array_unique($normalized));
        sort($normalized);

        return $normalized;
    }

    /**
     * Ambil daftar slot jam kerja aktif pada hari ini.
     */
    public function getEffectiveTimeSlots(): array
    {
        if (!$this->is_active) {
            return [];
        }

        $slots = self::normalizeTimeSlots($this->time_slots);
        if (!empty($slots)) {
            return $slots;
        }

        // Fallback jika kolom time_slots belum diisi
        return self::DEFAULT_TIME_SLOTS;
    }

    /**
     * Format jam kerja menjadi rentang sesi (misal "08:00" -> "08:00 - 10:00").
     */
    public static function formatSlotRange(string $slotTime, int $durationMinutes = self::SLOT_DURATION_MINUTES): string
    {
        $minutes = self::parseHourMinuteToMinutes($slotTime);
        if ($minutes === null) {
            return $slotTime;
        }
        $startH = intdiv($minutes, 60);
        $startM = $minutes % 60;
        $endTotal = min(23 * 60 + 59, $minutes + $durationMinutes);
        $endH = intdiv($endTotal, 60);
        $endM = $endTotal % 60;

        return sprintf('%02d:%02d - %02d:%02d', $startH, $startM, $endH, $endM);
    }

    /**
     * Label waktu ramah pengguna berdasarkan jam.
     */
    public static function formatSlotLabel(string $slotTime): string
    {
        $minutes = self::parseHourMinuteToMinutes($slotTime) ?? 480;
        $hour = intdiv($minutes, 60);
        $prefix = match (true) {
            $hour < 10 => 'Pagi',
            $hour < 12 => 'Menjelang Siang',
            $hour < 15 => 'Siang',
            $hour < 18 => 'Sore',
            default => 'Malam',
        };

        return $prefix . ' (' . self::formatSlotRange($slotTime) . ' WIB)';
    }

    /**
     * Pastikan seorang perawat memiliki 7 baris jadwal harian (Senin - Minggu) default jika belum ada.
     */
    public static function ensureDefaultSchedulesForNurse(int $nurseId): \Illuminate\Support\Collection
    {
        $existing = self::where('nurse_id', $nurseId)->orderBy('day_of_week')->get();

        if ($existing->count() < 7) {
            $existingDays = $existing->pluck('day_of_week')->toArray();
            foreach (self::DAYS as $dayNum => $dayName) {
                if (!in_array($dayNum, $existingDays, true)) {
                    self::create([
                        'nurse_id' => $nurseId,
                        'day_of_week' => $dayNum,
                        'day_name' => $dayName,
                        'start_time' => '08:00:00',
                        'end_time' => '21:00:00',
                        'time_slots' => self::DEFAULT_TIME_SLOTS,
                        'is_active' => true,
                    ]);
                }
            }
            $existing = self::where('nurse_id', $nurseId)->orderBy('day_of_week')->get();
        }

        // Pastikan setiap jadwal yang aktif memiliki time_slots terisi (termasuk slot malam 21:00 jika masih default lama)
        foreach ($existing as $sch) {
            $raw = $sch->time_slots;
            if (empty($raw) || !is_array($raw)) {
                $sch->time_slots = self::DEFAULT_TIME_SLOTS;
                $sch->save();
            }
        }

        return $existing;
    }

    /**
     * Ambil daftar pesanan aktif perawat pada tanggal tertentu beserta rentang menit yang diblokir.
     */
    public static function getActiveOrdersOnDate(int $nurseId, string $dateStr): \Illuminate\Support\Collection
    {
        return Order::where('nurse_id', $nurseId)
            ->whereDate('scheduled_date', $dateStr)
            ->whereIn('status', [
                'menunggu_konfirmasi',
                'diterima',
                'dalam_perjalanan',
                'tiba',
                'tindakan_berlangsung',
            ])
            ->get();
    }

    /**
     * Ambil rincian lengkap setiap slot jam kerja perawat pada tanggal tertentu:
     * apakah tersedia, sudah lewat (real-time WIB), atau sudah dipesan pasien lain
     * (baik melalui Pesan Sekarang maupun Pesan Untuk Nanti).
     */
    public static function getSlotDetailsForDate(int $nurseId, ?string $dateStr = null): array
    {
        $nowWib = Carbon::now('Asia/Jakarta');
        $targetDate = !empty($dateStr)
            ? Carbon::parse($dateStr, 'Asia/Jakarta')->startOfDay()
            : $nowWib->copy()->startOfDay();

        $targetDateStr = $targetDate->toDateString();
        $isToday = $targetDateStr === $nowWib->toDateString();
        $nowMinutes = ((int) $nowWib->format('H') * 60) + (int) $nowWib->format('i');

        $dayOfWeekIso = (int) $targetDate->dayOfWeekIso;
        $schedules = self::ensureDefaultSchedulesForNurse($nurseId);
        /** @var self|null $daySchedule */
        $daySchedule = $schedules->firstWhere('day_of_week', $dayOfWeekIso);

        if (!$daySchedule || !$daySchedule->is_active) {
            return [];
        }

        $slots = $daySchedule->getEffectiveTimeSlots();
        $activeOrders = self::getActiveOrdersOnDate($nurseId, $targetDateStr);

        $details = [];
        foreach ($slots as $slotTime) {
            $slotMin = self::parseHourMinuteToMinutes($slotTime);
            if ($slotMin === null) {
                continue;
            }

            $rangeStr = self::formatSlotRange($slotTime);
            $labelStr = self::formatSlotLabel($slotTime);

            // Cek apakah jam slot ini sudah lewat di hari ini (real-time WIB)
            // Slot dianggap sudah lewat jika waktu sekarang sudah melewati (slotStart + 90 menit)
            $isPast = $isToday && ($nowMinutes > ($slotMin + 90));

            // Cek apakah slot ini bertabrakan dengan pesanan aktif (Pesan Sekarang atau Pesan Untuk Nanti)
            $isBooked = false;
            $bookedReason = null;
            $conflictingOrderTime = null;

            foreach ($activeOrders as $ord) {
                $ordMin = self::parseHourMinuteToMinutes((string) $ord->scheduled_time);
                if ($ordMin === null) {
                    continue;
                }
                // Dua sesi bertabrakan jika selisih waktu mulai kurang dari durasi sesi (120 menit)
                // atau jika waktu pesanan (misal Pesan Sekarang 19:28 WIB) berada di dalam rentang slot (19:00 - 21:00 WIB)
                if (abs($slotMin - $ordMin) < self::SLOT_DURATION_MINUTES) {
                    $isBooked = true;
                    $conflictingOrderTime = $ord->scheduled_time;
                    $bookedReason = ($ord->booking_type === 'now')
                        ? "Sudah dipesan (Pesan Sekarang • {$ord->scheduled_time})"
                        : "Sudah dipesan pada jam {$ord->scheduled_time}";
                    break;
                }
            }

            $details[] = [
                'time' => $slotTime,
                'range' => $rangeStr,
                'label' => $labelStr,
                'is_booked' => $isBooked,
                'is_past' => $isPast,
                'is_available' => !$isBooked && !$isPast,
                'booked_reason' => $bookedReason,
                'conflicting_order_time' => $conflictingOrderTime,
            ];
        }

        return $details;
    }

    /**
     * Periksa apakah perawat tersedia pada waktu yang diminta (dalam Zona Waktu Indonesia Barat / WIB).
     * Mengintegrasikan jadwal jam kerja perawat (time_slots), waktu real-time WIB,
     * serta saling mengunci antara "Pesan Sekarang" dan "Pesan Untuk Nanti".
     *
     * @return array{available: bool, reason: string|null, day_name: string, schedule_hours: string|null, schedule_window: string|null, matched_slot: string|null, available_slots: array, booked_slots: array}
     */
    public static function checkAvailability(
        int $nurseId,
        string $bookingType = 'now',
        ?string $scheduledDate = null,
        ?string $scheduledTime = null
    ): array {
        $nowWib = Carbon::now('Asia/Jakarta');

        if ($bookingType === 'now' || empty($scheduledDate)) {
            $targetDate = $nowWib->copy();
            $requestedMinutes = ((int) $nowWib->format('H') * 60) + (int) $nowWib->format('i');
        } else {
            try {
                $targetDate = Carbon::parse($scheduledDate, 'Asia/Jakarta');
            } catch (\Throwable $e) {
                $targetDate = $nowWib->copy();
            }
            $requestedMinutes = self::parseHourMinuteToMinutes($scheduledTime ?? '08:00');
        }

        $targetDateStr = $targetDate->toDateString();
        $dayOfWeekIso = (int) $targetDate->dayOfWeekIso; // 1 (Senin) .. 7 (Minggu)
        $dayName = self::DAYS[$dayOfWeekIso] ?? 'Hari Ini';

        $schedules = self::ensureDefaultSchedulesForNurse($nurseId);
        /** @var self|null $daySchedule */
        $daySchedule = $schedules->firstWhere('day_of_week', $dayOfWeekIso);

        $slots = $daySchedule ? $daySchedule->getEffectiveTimeSlots() : [];
        $scheduleHoursLabel = !empty($slots)
            ? implode(', ', array_map(fn ($s) => $s . ' WIB', $slots))
            : null;

        $slotDetails = self::getSlotDetailsForDate($nurseId, $targetDateStr);
        $availableSlots = array_values(array_filter($slotDetails, fn ($d) => $d['is_available']));
        $bookedSlots = array_values(array_filter($slotDetails, fn ($d) => $d['is_booked']));

        if (!$daySchedule || !$daySchedule->is_active || empty($slots)) {
            return [
                'available' => false,
                'reason' => "Perawat sedang libur / tidak membuka jam praktik pada hari {$dayName}.",
                'day_name' => $dayName,
                'schedule_hours' => null,
                'schedule_window' => null,
                'matched_slot' => null,
                'available_slots' => [],
                'booked_slots' => [],
            ];
        }

        // Ambil semua pesanan aktif perawat di tanggal target
        $activeOrders = self::getActiveOrdersOnDate($nurseId, $targetDateStr);

        if ($bookingType === 'now') {
            // 1. Cek apakah ada pesanan aktif yang sedang berlangsung atau bertabrakan dengan jam real-time sekarang
            foreach ($activeOrders as $ord) {
                $ordMin = self::parseHourMinuteToMinutes((string) $ord->scheduled_time);
                if ($ordMin !== null && abs($requestedMinutes - $ordMin) < self::SLOT_DURATION_MINUTES) {
                    return [
                        'available' => false,
                        'reason' => "Perawat sudah dipesan pasien lain pada jam ini ({$ord->scheduled_time}). Silakan pilih jam lain atau perawat lain.",
                        'day_name' => $dayName,
                        'schedule_hours' => $scheduleHoursLabel,
                        'schedule_window' => $scheduleHoursLabel,
                        'matched_slot' => null,
                        'available_slots' => $availableSlots,
                        'booked_slots' => $bookedSlots,
                    ];
                }
            }

            // 2. Cek apakah waktu real-time sekarang masuk ke dalam salah satu jam kerja yang diatur perawat hari ini
            // Sebuah slot jam kerja (misal 19:00) mencakup rentang [slotMin - 30 menit, slotMin + 120 menit]
            $matchedSlot = null;
            foreach ($slotDetails as $detail) {
                $slotMin = self::parseHourMinuteToMinutes($detail['time']);
                if ($slotMin === null) {
                    continue;
                }
                if ($requestedMinutes >= ($slotMin - 30) && $requestedMinutes <= ($slotMin + self::SLOT_DURATION_MINUTES)) {
                    if ($detail['is_booked']) {
                        return [
                            'available' => false,
                            'reason' => "Sesi jam kerja {$detail['range']} WIB saat ini sudah dipesan pasien lain.",
                            'day_name' => $dayName,
                            'schedule_hours' => $scheduleHoursLabel,
                            'schedule_window' => $scheduleHoursLabel,
                            'matched_slot' => $detail['time'],
                            'available_slots' => $availableSlots,
                            'booked_slots' => $bookedSlots,
                        ];
                    }
                    $matchedSlot = $detail['time'];
                    break;
                }
            }

            if ($matchedSlot === null) {
                $nowStr = $nowWib->format('H:i');
                return [
                    'available' => false,
                    'reason' => "Saat ini pukul {$nowStr} WIB berada di luar jam kerja perawat hari {$dayName} ({$scheduleHoursLabel}).",
                    'day_name' => $dayName,
                    'schedule_hours' => $scheduleHoursLabel,
                    'schedule_window' => $scheduleHoursLabel,
                    'matched_slot' => null,
                    'available_slots' => $availableSlots,
                    'booked_slots' => $bookedSlots,
                ];
            }

            return [
                'available' => true,
                'reason' => null,
                'day_name' => $dayName,
                'schedule_hours' => $scheduleHoursLabel,
                'schedule_window' => $scheduleHoursLabel,
                'matched_slot' => $matchedSlot,
                'available_slots' => $availableSlots,
                'booked_slots' => $bookedSlots,
            ];
        }

        // BOOKING TYPE === 'scheduled' (Pesan Untuk Nanti)
        if ($requestedMinutes === null) {
            return [
                'available' => false,
                'reason' => 'Format jam perawatan tidak valid.',
                'day_name' => $dayName,
                'schedule_hours' => $scheduleHoursLabel,
                'schedule_window' => $scheduleHoursLabel,
                'matched_slot' => null,
                'available_slots' => $availableSlots,
                'booked_slots' => $bookedSlots,
            ];
        }

        // 1. Cek jika tanggal adalah hari ini dan jam yang dipilih sudah terlewat di waktu real-time WIB
        $isToday = $targetDateStr === $nowWib->toDateString();
        $nowMinutes = ((int) $nowWib->format('H') * 60) + (int) $nowWib->format('i');
        if ($isToday && $nowMinutes > ($requestedMinutes + 90)) {
            $reqStr = sprintf('%02d:%02d', intdiv($requestedMinutes, 60), $requestedMinutes % 60);
            return [
                'available' => false,
                'reason' => "Jam perawatan {$reqStr} WIB hari ini sudah lewat (Sekarang pukul {$nowWib->format('H:i')} WIB).",
                'day_name' => $dayName,
                'schedule_hours' => $scheduleHoursLabel,
                'schedule_window' => $scheduleHoursLabel,
                'matched_slot' => null,
                'available_slots' => $availableSlots,
                'booked_slots' => $bookedSlots,
            ];
        }

        // 2. Cek apakah ada pesanan aktif (baik Pesan Sekarang maupun Pesan Untuk Nanti) yang bentrok dengan jam ini
        foreach ($activeOrders as $ord) {
            $ordMin = self::parseHourMinuteToMinutes((string) $ord->scheduled_time);
            if ($ordMin !== null && abs($requestedMinutes - $ordMin) < self::SLOT_DURATION_MINUTES) {
                $conflictInfo = ($ord->booking_type === 'now')
                    ? "Pesan Sekarang pada pukul {$ord->scheduled_time}"
                    : "jadwal pukul {$ord->scheduled_time}";
                return [
                    'available' => false,
                    'reason' => "Jam ini sudah dipesan pasien lain ({$conflictInfo}) pada tanggal {$targetDate->format('d M Y')}.",
                    'day_name' => $dayName,
                    'schedule_hours' => $scheduleHoursLabel,
                    'schedule_window' => $scheduleHoursLabel,
                    'matched_slot' => null,
                    'available_slots' => $availableSlots,
                    'booked_slots' => $bookedSlots,
                ];
            }
        }

        // 3. Cek apakah jam yang diminta sesuai dengan salah satu slot jam kerja yang diatur perawat pada hari tersebut
        $matchedSlot = null;
        foreach ($slots as $slotTime) {
            $slotMin = self::parseHourMinuteToMinutes($slotTime);
            if ($slotMin !== null && abs($requestedMinutes - $slotMin) < 60) {
                $matchedSlot = $slotTime;
                break;
            }
        }

        if ($matchedSlot === null) {
            $reqStr = sprintf('%02d:%02d', intdiv($requestedMinutes, 60), $requestedMinutes % 60);
            return [
                'available' => false,
                'reason' => "Perawat tidak membuka jadwal pada pukul {$reqStr} WIB di hari {$dayName} (Jam kerja: {$scheduleHoursLabel}).",
                'day_name' => $dayName,
                'schedule_hours' => $scheduleHoursLabel,
                'schedule_window' => $scheduleHoursLabel,
                'matched_slot' => null,
                'available_slots' => $availableSlots,
                'booked_slots' => $bookedSlots,
            ];
        }

        return [
            'available' => true,
            'reason' => null,
            'day_name' => $dayName,
            'schedule_hours' => $scheduleHoursLabel,
            'schedule_window' => $scheduleHoursLabel,
            'matched_slot' => $matchedSlot,
            'available_slots' => $availableSlots,
            'booked_slots' => $bookedSlots,
        ];
    }

    /**
     * Ekstrak jam mulai (format HH:MM) dari string scheduled_time (misal "08:00 - 10:00 WIB" -> "08:00").
     */
    public static function extractStartTime(?string $scheduledTime, ?Carbon $fallbackNow = null): string
    {
        if (!empty($scheduledTime) && preg_match('/(\d{1,2})[:.](\d{2})/', $scheduledTime, $matches)) {
            $h = max(0, min(23, (int) $matches[1]));
            $m = max(0, min(59, (int) $matches[2]));
            return sprintf('%02d:%02d', $h, $m);
        }

        $now = $fallbackNow ?: Carbon::now('Asia/Jakarta');
        return $now->format('H:i');
    }

    /**
     * Ekstrak jam & menit pertama dari string seperti "08:00:00", "08:00 - 10:00", atau "14:30 WIB".
     */
    public static function parseHourMinuteToMinutes(string $timeString): ?int
    {
        if (preg_match('/(\d{1,2})[:.](\d{2})/', $timeString, $matches)) {
            $hour = (int) $matches[1];
            $minute = (int) $matches[2];
            return ($hour * 60) + $minute;
        }

        return null;
    }
}
