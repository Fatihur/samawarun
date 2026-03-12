<?php

namespace App\Notifications;

use App\Models\Participant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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

        $barcodeValue = $participant->bib_number ?: 'PT-'.$participant->id;
        $barcode = base64_encode(
            QrCode::format('svg')->margin(1)->size(200)->generate($barcodeValue)
        );

        $pdf = app('dompdf.wrapper')
            ->loadView('pdf.participant-receipt', [
                'participant' => $participant,
                'barcode' => $barcode,
                'barcodeValue' => $barcodeValue,
            ])
            ->setPaper([0, 0, 226.77, 920])
            ->output();

        $fileName = 'struk-pendaftaran-'.Str::slug($participant->name).'-'.$participant->id.'.pdf';

        return (new MailMessage)
            ->subject('Pembayaran disetujui, pendaftaran Anda lengkap')
            ->view('emails.participant-status-updated', [
                'participant' => $participant,
                'statusTitle' => 'Pendaftaran Lengkap & BIB Tersedia',
                'statusBadge' => 'PEMBAYARAN DISETUJUI',
                'statusColor' => '#047857',
                'statusBackground' => '#ecfdf5',
                'introLines' => [
                    'Selamat, pembayaran Anda telah disetujui oleh admin Samawa Run.',
                    'Berikut kami lampirkan bukti pendaftaran lengkap beserta nomor BIB dan QR code peserta.',
                ],
                'footerMessage' => 'Simpan email ini sebagai bukti pendaftaran resmi. Tunjukkan QR Code dan BIB saat diperlukan pada hari pelaksanaan.',
                'barcode' => $barcode,
                'barcodeValue' => $barcodeValue,
            ])
            ->attachData($pdf, $fileName, [
                'mime' => 'application/pdf',
            ]);
    }
}
