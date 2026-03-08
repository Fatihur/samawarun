<?php

namespace App\Notifications;

use App\Models\Participant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ParticipantRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(public Participant $participant) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $participant = $this->participant->loadMissing('event');

        return (new MailMessage)
            ->subject('Pendaftaran Anda ditolak')
            ->view('emails.participant-status-updated', [
                'participant' => $participant,
                'statusTitle' => 'Pendaftaran Ditolak',
                'statusBadge' => 'DITOLAK',
                'statusColor' => '#b91c1c',
                'statusBackground' => '#fef2f2',
                'introLines' => [
                    'Mohon maaf, pendaftaran Anda di Samawa Run belum dapat kami verifikasi.',
                    'Berikut detail data pendaftaran yang tercatat pada sistem kami.',
                ],
                'footerMessage' => 'Jika Anda memerlukan bantuan lebih lanjut, silakan hubungi panitia Samawa Run.',
                'barcode' => null,
                'barcodeValue' => null,
            ]);
    }
}
