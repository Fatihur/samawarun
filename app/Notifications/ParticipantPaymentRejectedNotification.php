<?php

namespace App\Notifications;

use App\Models\Participant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class ParticipantPaymentRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(public Participant $participant) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $participant = $this->participant->loadMissing('event');

        $paymentUrl = URL::temporarySignedRoute(
            'registrations.payment.create',
            $participant->payment_token_expires_at ?? now()->addDays(7),
            [
                'participant' => $participant->id,
                'token' => $participant->payment_token,
            ]
        );

        return (new MailMessage())
            ->subject('Pembayaran perlu diperbaiki')
            ->greeting('Halo '.$participant->name.',')
            ->line('Bukti pembayaran Anda belum dapat kami setujui saat ini.')
            ->line('Silakan pastikan file yang diupload jelas dan sesuai dengan nominal pembayaran kategori yang Anda pilih.')
            ->line('Silakan upload ulang bukti pembayaran melalui link berikut:')
            ->action('Upload Ulang Pembayaran', $paymentUrl)
            ->line('Jika memerlukan bantuan, silakan hubungi panitia pada kontak event.');
    }
}
