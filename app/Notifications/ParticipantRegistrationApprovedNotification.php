<?php

namespace App\Notifications;

use App\Models\Participant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class ParticipantRegistrationApprovedNotification
    extends Notification
    implements ShouldQueue
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
        $paymentAmount = $participant->formatted_payment_amount;

        $paymentUrl = URL::temporarySignedRoute(
            "registrations.payment.create",
            $participant->payment_token_expires_at ?? now()->addDays(7),
            [
                "participant" => $participant->id,
                "token" => $participant->payment_token,
            ],
        );

        $mail = new MailMessage();
        $mail->subject("Pendaftaran disetujui, lanjutkan pembayaran");
        $mail->view("emails.participant-payment-approved", [
            "participant" => $participant,
            "paymentUrl" => $paymentUrl,
            "paymentAmount" => $paymentAmount,
            "paymentDeadline" =>
                $participant->payment_token_expires_at?->translatedFormat(
                    "d F Y, H:i",
                ) ?? "-",
            "bankAccount" => $participant->event?->bank_account,
            "contact" => $participant->event?->contact,
            "referenceCode" =>
                "PAY-" . Str::padLeft((string) $participant->id, 6, "0"),
        ]);

        return $mail;
    }
}
