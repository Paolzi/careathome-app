<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Notifications\SystemActivityNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OrderReminderService
{
    /**
     * Periksa pesanan aktif hari ini yang akan dimulai dalam 60 menit ke depan (WIB),
     * lalu kirim notifikasi pengingat 1x ke Perawat dan Pasien terkait.
     */
    public static function dispatchDueReminders(): void
    {
        if (Cache::has('careathome_order_reminders_ran')) {
            return;
        }
        Cache::put('careathome_order_reminders_ran', true, 60);

        $nowWib = Carbon::now('Asia/Jakarta');
        $todayStr = $nowWib->format('Y-m-d');

        $orders = Order::with(['serviceCatalog', 'nurse', 'patient'])
            ->whereDate('scheduled_date', $todayStr)
            ->whereIn('status', ['diterima', 'menunggu_konfirmasi'])
            ->get();

        foreach ($orders as $order) {
            if (!preg_match('/(\d{1,2})[:.](\d{2})/', (string) $order->scheduled_time, $m)) {
                continue;
            }

            $startTime = Carbon::createFromFormat(
                'Y-m-d H:i',
                $todayStr . ' ' . sprintf('%02d:%02d', (int) $m[1], (int) $m[2]),
                'Asia/Jakarta'
            );

            $diffMinutes = $nowWib->diffInMinutes($startTime, false);

            // Kirim pengingat jika jadwal 0..60 menit lagi atau baru lewat <= 15 menit
            if ($diffMinutes < -15 || $diffMinutes > 60) {
                continue;
            }

            $serviceName = $order->serviceCatalog->name ?? 'Layanan Keperawatan';
            $timeLabel = $order->scheduled_time;

            if ($order->nurse_id && $order->nurse) {
                self::notifyOnce(
                    $order->nurse,
                    $order->id,
                    'Pengingat Jam Order Kunjungan',
                    "Pesanan #{$order->order_number} ({$serviceName}) untuk pasien {$order->patient_name} dijadwalkan pukul {$timeLabel}. Segera bersiap menuju lokasi.",
                    ['order_number' => $order->order_number, 'status' => 'reminder']
                );
            }

            if ($order->patient_id && $order->patient) {
                $nurseName = $order->nurse?->name ?? 'Mitra Perawat';
                self::notifyOnce(
                    $order->patient,
                    $order->id,
                    'Pengingat Jadwal Kunjungan Perawat',
                    "Kunjungan {$serviceName} oleh {$nurseName} (#{$order->order_number}) akan segera dimulai pukul {$timeLabel}.",
                    ['order_number' => $order->order_number, 'status' => 'reminder']
                );
            }
        }
    }

    protected static function notifyOnce(User $user, int $orderId, string $title, string $message, array $extra = []): void
    {
        $alreadySent = DB::table('notifications')
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->where('data->type', 'upcoming_order_reminder')
            ->where('data->order_id', $orderId)
            ->exists();

        if (!$alreadySent) {
            $user->notify(new SystemActivityNotification(
                'upcoming_order_reminder',
                $title,
                $message,
                array_merge(['order_id' => $orderId], $extra)
            ));
        }
    }
}
