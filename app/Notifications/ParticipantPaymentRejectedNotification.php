<?php

namespace App\Notifications;

use App\Models\Participant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class ParticipantPaymentRejectedNotification extends Notification implements
    ShouldQueue
{
    use Queueable;

    public Participant $participant;

    public function __construct(Participant $participant)
    {
        $this->participant = $participant;
    }

    public function via(object $notifiable): array
    {
        return ["mail"];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $participant = $this->participant->loadMissing("event");

        $paymentUrl = URL::temporarySignedRoute(
            "registrations.payment.create",
            $participant->payment_token_expires_at ?? now()->addDays(7),
            [
                "participant" => $participant->id,
                "token" => $participant->payment_token,
            ],
        );

        $mail = new MailMessage();
        $mail->subject("Pembayaran perlu diperbaiki");
        $mail->greeting("Halo " . $participant->name . ",");
        $mail->line("Bukti pembayaran Anda belum dapat kami setujui saat ini.");
        $mail->line(
            "Silakan pastikan file yang diupload jelas dan sesuai dengan nominal pembayaran kategori yang Anda pilih.",
        );
        $mail->line(
            "Silakan upload ulang bukti pembayaran melalui link berikut:",
        );
        $mail->action("Upload Ulang Pembayaran", $paymentUrl);
        $mail->line(
            "Jika memerlukan bantuan, silakan hubungi panitia pada kontak event.",
        );

        return $mail;
    }
}
