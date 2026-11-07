<?php

namespace App\Notifications;

use App\Models\NurseProfile;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NurseDocumentApprovedNotification extends Notification
{
    use Queueable;

    public NurseProfile $nurseProfile;
    public ?User $admin;

    /**
     * Create a new notification instance.
     */
    public function __construct(NurseProfile $nurseProfile, ?User $admin = null)
    {
        $this->nurseProfile = $nurseProfile;
        $this->admin = $admin;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $nurseName = $notifiable->name ?? 'Mitra Perawat';
        $dashboardUrl = route('perawat.dashboard');

        return (new MailMessage)
            ->subject('[CareAtHome] Selamat! Berkas Legalitas STR & SIP Anda Berhasil Diverifikasi')
            ->greeting("Halo Ns. {$nurseName},")
            ->line('Kabar gembira! Seluruh dokumen legalitas profesi keperawatan Anda (Swafoto KTP, STR, SIP, dan Ijazah) telah berhasil diaudit, divalidasi keabsahannya, dan disetujui oleh tim Administrator CareAtHome.')
            ->line('Status kemitraan Anda saat ini resmi aktif: **TERVERIFIKASI (VERIFIED)**.')
            ->line('Anda sudah dapat mengaktifkan toggle **"Sedang Bekerja (Online)"** di dashboard Anda untuk mulai menerima pesanan kunjungan homecare dari pasien dalam radius 10 km.')
            ->action('Buka Dashboard Mitra Perawat', $dashboardUrl)
            ->line('Terima kasih atas komitmen Anda memberikan layanan keperawatan profesional yang aman dan berstandar bersama CareAtHome.');
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'document_approved',
            'title' => 'Dokumen STR & SIP Berhasil Diverifikasi',
            'message' => 'Selamat! Berkas legalitas nakes Anda telah disetujui oleh Administrator. Akun Anda telah aktif dan siap melayani pasien.',
            'url' => route('perawat.dashboard'),
            'admin_id' => $this->admin?->id,
            'admin_name' => $this->admin?->name ?? 'Administrator CareAtHome',
            'verified_at' => now()->toIso8601String(),
            'formatted_time' => now()->translatedFormat('d M Y H:i') . ' WIB',
        ];
    }
}

