<?php

namespace App\Notifications;

use App\Models\Participant;
use App\Services\CertificateTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class CertificateEmailNotification extends Notification implements ShouldQueue
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
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $participant = $this->participant->loadMissing(['event.certificateTemplate']);
        $template = $participant->event?->certificateTemplate;

        if (!$template || !$template->background_image_path) {
            throw new \Exception('Template sertifikat tidak tersedia untuk peserta ini.');
        }

        // Generate PDF certificate
        $certificateService = app(CertificateTemplateService::class);
        $pdf = $certificateService->generatePdf($template, $participant);
        $pdfContent = $pdf->output();

        $fileName = 'sertifikat-' . Str::slug($participant->name) . '-' . $participant->id . '.pdf';

        $mail = new MailMessage();
        $mail->subject('Sertifikat Finisher - ' . $participant->event?->name);
        $mail->view('emails.certificate-finisher', [
            'participant' => $participant,
            'event' => $participant->event,
            'finishTime' => $participant->race_finished_at?->format('d M Y H:i:s'),
            'duration' => $participant->formatted_race_duration,
        ]);
        $mail->attachData($pdfContent, $fileName, [
            'mime' => 'application/pdf',
        ]);

        return $mail;
    }
}
