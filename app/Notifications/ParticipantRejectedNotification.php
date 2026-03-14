<?php

namespace App\Notifications;

use App\Models\Participant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ParticipantRejectedNotification extends Notification implements
    ShouldQueue
{
    use Queueable;

    public Participant $participant;

    public function __construct(Participant $participant)
    {
        $this->participant = $participant;
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ["mail"];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $participant = $this->participant->loadMissing("event");

        $mail = new MailMessage();
        $mail->subject("Pendaftaran Anda ditolak");
        $mail->view("emails.participant-status-updated", [
            "participant" => $participant,
            "statusTitle" => "Pendaftaran Ditolak",
            "statusBadge" => "PENDAFTARAN DITOLAK",
            "statusColor" => "#b91c1c",
            "statusBackground" => "#fef2f2",
            "introLines" => [
                "Mohon maaf, pendaftaran Anda di Samawa Run belum dapat kami verifikasi.",
                "Silakan periksa kembali data pendaftaran Anda atau hubungi panitia jika membutuhkan bantuan.",
            ],
            "footerMessage" =>
                "Jika Anda memerlukan bantuan lebih lanjut, silakan hubungi panitia Samawa Run.",
            "barcode" => null,
            "barcodeValue" => null,
        ]);

        return $mail;
    }
}
