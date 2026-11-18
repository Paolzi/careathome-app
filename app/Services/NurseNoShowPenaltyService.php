<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\NurseProfile;
use App\Models\Order;
use App\Models\User;
use App\Notifications\PatientOrderStatusNotification;
use App\Notifications\SystemActivityNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class NurseNoShowPenaltyService
{
    /**
     * Periksa pesanan yang jam kunjungannya telah berakhir namun perawat belum berangkat
     * (status masih 'diterima' atau 'menunggu_konfirmasi').
     * Otomatis:
     * 1. Batalkan pesanan & refund 100% dana pasien (jika sudah dibayar).
     * 2. Suspend perawat selama 1 hari (24 jam) agar tidak dapat bekerja.
     * 3. Catat AuditLog dan kirim notifikasi ke Perawat, Pasien, dan Admin.
     */
    public static function processOverdueOrders(bool $force = false): int
    {
        if (!$force && Cache::has('careathome_noshow_check')) {
            return 0;
        }
        Cache::put('careathome_noshow_check', true, 30);

        $nowWib = Carbon::now('Asia/Jakarta');

        $overdueCandidates = Order::with(['serviceCatalog', 'nurse', 'patient'])
            ->whereIn('status', ['diterima', 'menunggu_konfirmasi'])
            ->whereDate('scheduled_date', '<=', $nowWib->toDateString())
            ->get();

        if ($overdueCandidates->isEmpty()) {
            return 0;
        }

        $processedCount = 0;

        /** @var OrderPaymentService $paymentService */
        $paymentService = app(OrderPaymentService::class);

        foreach ($overdueCandidates as $order) {
            if (!$order->scheduled_date) {
                continue;
            }

            $deadlineTime = self::resolvePenaltyDeadlineTime($order->scheduled_date->format('Y-m-d'), (string) $order->scheduled_time);
            if (!$deadlineTime || $nowWib->lessThanOrEqualTo($deadlineTime)) {
                continue;
            }

            $reason = "Dibatalkan otomatis oleh sistem: Perawat tidak melakukan tindakan lebih dari 1 jam sejak jadwal kunjungan ({$order->scheduled_time}).";

            // 1. Batalkan pesanan & auto-refund 100% jika sudah dibayar
            $cancelledOrder = $paymentService->cancelOrder($order, $reason);

            // 2. Berikan sanksi suspend 1 hari kepada perawat terkait
            // Dihitung tepat 24 jam (+1 hari) dari batas waktu 1 jam jadwal yang dipesan pasien
            $suspendUntil = $deadlineTime->copy()->addDay();
            if ($order->nurse_id && $suspendUntil->greaterThan($nowWib)) {
                $profile = NurseProfile::where('user_id', $order->nurse_id)->first();
                if ($profile) {
                    $suspendReason = "Sanksi ketidakhadiran otomatis: tidak datang pada pesanan #{$order->order_number} ({$order->scheduled_date->format('d M Y')} {$order->scheduled_time}).";

                    $profile->update([
                        'suspended_until' => $suspendUntil,
                        'suspension_reason' => $suspendReason,
                        'is_online' => false,
                    ]);

                    AuditLog::log(
                        'nurse_auto_suspended_no_show',
                        $profile,
                        ['is_online' => true, 'suspended_until' => null],
                        [
                            'suspended_until' => $suspendUntil->toIso8601String(),
                            'order_number' => $order->order_number,
                            'reason' => $suspendReason,
                        ]
                    );

                    // Notifikasi ke Perawat
                    if ($nurseUser = $order->nurse) {
                        $nurseUser->notify(new SystemActivityNotification(
                            'nurse_suspended_1day',
                            'Sanksi Suspend 1 Hari (Tidak Hadir)',
                            "Anda tidak memulai perjalanan pada pesanan #{$order->order_number} hingga jam kunjungan berakhir. Akun Anda disuspend hingga {$suspendUntil->format('d M Y, H:i')} WIB.",
                            ['order_id' => $order->id, 'order_number' => $order->order_number]
                        ));
                    }

                    // Notifikasi ke Admin
                    foreach (User::role('admin')->get() as $adminUser) {
                        $adminUser->notify(new SystemActivityNotification(
                            'nurse_auto_suspended_no_show',
                            'Pelanggaran Nakes: Auto-Suspend 1 Hari',
                            "Perawat " . ($order->nurse?->name ?? '-') . " tidak hadir pada pesanan #{$order->order_number}. Pesanan dibatalkan & dana pasien dikembalikan; perawat disuspend 1 hari.",
                            ['order_id' => $order->id, 'nurse_user_id' => $order->nurse_id]
                        ));
                    }
                }
            }

            // 3. Notifikasi tambahan ke Pasien
            if ($patientUser = $order->patient) {
                $refundNote = $cancelledOrder->refund_amount > 0
                    ? ' Dana Anda sebesar Rp ' . number_format($cancelledOrder->refund_amount, 0, ',', '.') . ' telah dikembalikan 100%.'
                    : '';
                $patientUser->notify(new PatientOrderStatusNotification(
                    $cancelledOrder,
                    'dibatalkan',
                    "Pesanan #{$order->order_number} dibatalkan otomatis karena perawat tidak berangkat hingga jam kunjungan berakhir.{$refundNote}"
                ));
            }

            $processedCount++;
        }

        return $processedCount;
    }

    /**
     * Tentukan batas waktu maksimal perawat memulai tindakan (WIB):
     * tepat 1 jam (+60 menit) setelah jam mulai jadwal yang dipesan.
     */
    protected static function resolvePenaltyDeadlineTime(string $dateStr, string $timeStr): ?Carbon
    {
        if (preg_match('/(\d{1,2})[:.](\d{2})/', $timeStr, $match)) {
            return Carbon::createFromFormat(
                'Y-m-d H:i',
                $dateStr . ' ' . sprintf('%02d:%02d', (int) $match[1], (int) $match[2]),
                'Asia/Jakarta'
            )->addHour();
        }

        return null;
    }
}
