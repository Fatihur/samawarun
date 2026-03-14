<?php

namespace App\Notifications;

use App\Models\Participant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ParticipantRegistrationThankYouNotification
    extends Notification
    implements ShouldQueue
{
    use Queueable;

    public Participant $participant;

    public function __construct(Participant $participant)
    {
        $this->participant = $participant;
    }

    /**
     * Get the notification's delivery channels.
     *
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
        $mail->subject("Pendaftaran berhasil diterima");
        $mail->view("emails.participant-registration-thank-you", [
            "participant" => $participant,
        ]);

        return $mail;
    }
}
