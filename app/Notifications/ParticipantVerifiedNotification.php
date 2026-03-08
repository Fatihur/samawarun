<?php

namespace App\Notifications;

use App\Models\Participant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use Picqer\Barcode\BarcodeGeneratorPNG;

class ParticipantVerifiedNotification extends Notification
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

        $barcodeGenerator = new BarcodeGeneratorPNG();
        $barcodeValue = $participant->bib_number ?: 'PT-'.$participant->id;
        $barcode = base64_encode(
            $barcodeGenerator->getBarcode($barcodeValue, $barcodeGenerator::TYPE_CODE_128, 2, 70)
        );

        $pdf = app('dompdf.wrapper')
            ->loadView('pdf.participant-receipt', [
                'participant' => $participant,
                'barcode' => $barcode,
                'barcodeValue' => $barcodeValue,
            ])
            ->setPaper('A4')
            ->output();

        $fileName = 'struk-pendaftaran-'.Str::slug($participant->name).'-'.$participant->id.'.pdf';

        return (new MailMessage)
            ->subject('Pendaftaran Anda berhasil diverifikasi')
            ->view('emails.participant-status-updated', [
                'participant' => $participant,
                'statusTitle' => 'Pendaftaran Berhasil Diverifikasi',
                'statusBadge' => 'TERVERIFIKASI',
                'statusColor' => '#047857',
                'statusBackground' => '#ecfdf5',
                'introLines' => [
                    'Selamat, pendaftaran Anda di Samawa Run telah diverifikasi oleh admin.',
                    'Berikut kami lampirkan detail peserta dan struk pendaftaran Anda.',
                ],
                'footerMessage' => 'Simpan email ini sebagai bukti pendaftaran. Tunjukkan barcode dan BIB saat diperlukan pada hari pelaksanaan.',
                'barcode' => $barcode,
                'barcodeValue' => $barcodeValue,
            ])
            ->attachData($pdf, $fileName, [
                'mime' => 'application/pdf',
            ]);
    }
}
