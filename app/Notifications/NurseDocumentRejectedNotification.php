<?php

namespace App\Notifications;

use App\Models\NurseProfile;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NurseDocumentRejectedNotification extends Notification
{
    use Queueable;

    public NurseProfile $nurseProfile;
    public string $rejectionReason;
    public ?User $admin;

    /**
     * Create a new notification instance.
     */
    public function __construct(NurseProfile $nurseProfile, string $rejectionReason, ?User $admin = null)
    {
        $this->nurseProfile = $nurseProfile;
        $this->rejectionReason = $rejectionReason;
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
        $verificationUrl = route('perawat.verifikasi');

        return (new MailMessage)
            ->subject('[CareAtHome] Pemberitahuan Perbaikan Berkas Dokumen Legalitas Nakes')
            ->greeting("Halo Ns. {$nurseName},")
            ->line('Terima kasih telah mengajukan dokumen legalitas profesi keperawatan Anda di platform CareAtHome.')
            ->line('Setelah dilakukan audit dan pengecekan oleh tim Administrator, berkas Anda saat ini berstatus: **PERLU PERBAIKAN (REJECTED)**.')
            ->line("**Alasan Penolakan dari Admin:**")
            ->line("> {$this->rejectionReason}")
            ->line('Harap lakukan pembaruan atau unggah ulang dokumen yang dimaksud agar akun kemitraan Anda dapat segera divalidasi dan diaktifkan kembali.')
            ->action('Perbaiki & Unggah Ulang Dokumen', $verificationUrl)
            ->line('Apabila Anda membutuhkan bantuan atau informasi lebih lanjut, silakan hubungi tim layanan dukungan CareAtHome.');
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'document_rejected',
            'title' => 'Berkas Dokumen Perlu Perbaikan',
            'message' => 'Dokumen legalitas Anda belum disetujui. Alasan: ' . $this->rejectionReason,
            'rejection_reason' => $this->rejectionReason,
            'url' => route('perawat.verifikasi'),
            'admin_id' => $this->admin?->id,
            'admin_name' => $this->admin?->name ?? 'Administrator CareAtHome',
            'rejected_at' => now()->toIso8601String(),
            'formatted_time' => now()->translatedFormat('d M Y H:i') . ' WIB',
        ];
    }
}

