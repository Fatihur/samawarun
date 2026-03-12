<?php

namespace App\Notifications;

use App\Models\Participant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ParticipantRegistrationThankYouNotification extends Notification
{
    use Queueable;

    public function __construct(public Participant $participant) {}

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
        $participant = $this->participant->loadMissing('event');

        return (new MailMessage())
            ->subject('Pendaftaran berhasil diterima')
            ->view('emails.participant-registration-thank-you', [
                'participant' => $participant,
            ]);
    }
}
