<?php

namespace App\Notifications;

use App\Models\Participant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewParticipantNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $participant;

    /**
     * Create a new notification instance.
     */
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
        return ["database"];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            "participant_id" => $this->participant->id,
            "participant_name" => $this->participant->name,
            "event_id" => $this->participant->event->id ?? null,
            "event_name" => $this->participant->event->name ?? "Unknown Event",
            "message" =>
                "Peserta baru mendaftar di event " .
                ($this->participant->event->name ?? ""),
        ];
    }
}
