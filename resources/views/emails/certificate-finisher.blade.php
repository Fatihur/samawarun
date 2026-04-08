<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat Finisher - {{ $event->name ?? 'Samawa Run' }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f5f1e8; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#f5f1e8; padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:680px; background-color:#ffffff; border-radius:18px; overflow:hidden;">
                    <tr>
                        <td style="background:linear-gradient(135deg, #0f766e 0%, #134e4a 100%); padding:28px 24px; text-align:center; color:#ffffff;">
                            <div style="font-size:30px; font-weight:700; letter-spacing:0.5px;">Samawa Run</div>
                            <div style="margin-top:8px; font-size:15px; opacity:0.92;">Sertifikat Finisher</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px 24px 12px;">
                            <span style="display:inline-block; padding:8px 14px; border-radius:999px; background-color:#ecfdf5; color:#047857; font-size:12px; font-weight:700; letter-spacing:0.08em;">FINISHER</span>
                            <p style="margin:20px 0 12px; font-size:16px; line-height:1.7;">Halo {{ $participant->name }},</p>
                            <p style="margin:0 0 12px; font-size:16px; line-height:1.7;">Selamat! Anda telah berhasil menyelesaikan race <strong>{{ $event->name ?? '-' }}</strong>.</p>
                            <p style="margin:0 0 12px; font-size:16px; line-height:1.7;">Sertifikat finisher Anda telah tersedia dan dilampirkan pada email ini.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:12px 24px 0;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #e5e7eb; border-radius:16px; overflow:hidden; background-color:#fcfcfb;">
                                <tr>
                                    <td style="padding:18px 20px; border-bottom:1px solid #e5e7eb; background-color:#f8fafc;">
                                        <div style="font-size:18px; font-weight:700; color:#0f172a;">Detail Finisher</div>
                                        <div style="margin-top:4px; font-size:13px; color:#64748b;">Informasi race yang telah Anda selesaikan</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:18px 20px;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td style="padding:0 0 12px; width:50%; vertical-align:top;">
                                                    <div style="font-size:11px; font-weight:700; letter-spacing:0.08em; color:#94a3b8; text-transform:uppercase;">Nama Peserta</div>
                                                    <div style="margin-top:4px; font-size:15px; color:#0f172a;">{{ $participant->name }}</div>
                                                </td>
                                                <td style="padding:0 0 12px; width:50%; vertical-align:top;">
                                                    <div style="font-size:11px; font-weight:700; letter-spacing:0.08em; color:#94a3b8; text-transform:uppercase;">Nomor BIB</div>
                                                    <div style="margin-top:4px; font-size:15px; color:#0f172a;">{{ $participant->bib_number ?? '-' }}</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:0 0 12px; width:50%; vertical-align:top;">
                                                    <div style="font-size:11px; font-weight:700; letter-spacing:0.08em; color:#94a3b8; text-transform:uppercase;">Event</div>
                                                    <div style="margin-top:4px; font-size:15px; color:#0f172a;">{{ $event->name ?? '-' }}</div>
                                                </td>
                                                <td style="padding:0 0 12px; width:50%; vertical-align:top;">
                                                    <div style="font-size:11px; font-weight:700; letter-spacing:0.08em; color:#94a3b8; text-transform:uppercase;">Tanggal Event</div>
                                                    <div style="margin-top:4px; font-size:15px; color:#0f172a;">{{ $event->date?->format('d M Y') ?? '-' }}</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:0 0 12px; width:50%; vertical-align:top;">
                                                    <div style="font-size:11px; font-weight:700; letter-spacing:0.08em; color:#94a3b8; text-transform:uppercase;">Kategori Jarak</div>
                                                    <div style="margin-top:4px; font-size:15px; color:#0f172a;">{{ $participant->distance_category }}</div>
                                                </td>
                                                <td style="padding:0 0 12px; width:50%; vertical-align:top;">
                                                    <div style="font-size:11px; font-weight:700; letter-spacing:0.08em; color:#94a3b8; text-transform:uppercase;">Waktu Finish</div>
                                                    <div style="margin-top:4px; font-size:15px; color:#0f172a;">{{ $finishTime ?? '-' }}</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:0; width:50%; vertical-align:top;">
                                                    <div style="font-size:11px; font-weight:700; letter-spacing:0.08em; color:#94a3b8; text-transform:uppercase;">Durasi Race</div>
                                                    <div style="margin-top:4px; font-size:18px; font-weight:700; color:#0f766e;">{{ $duration ?? '-' }}</div>
                                                </td>
                                                <td style="padding:0; width:50%; vertical-align:top;">
                                                    <div style="font-size:11px; font-weight:700; letter-spacing:0.08em; color:#94a3b8; text-transform:uppercase;">Status</div>
                                                    <div style="margin-top:4px; font-size:15px; font-weight:700; color:#047857;">FINISHER</div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 24px 0;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #d1fae5; border-radius:16px; background-color:#f0fdf4;">
                                <tr>
                                    <td style="padding:20px; text-align:center;">
                                        <div style="font-size:15px; font-weight:700; color:#065f46;">Sertifikat Finisher</div>
                                        <div style="margin-top:6px; font-size:13px; color:#047857;">File PDF sertifikat Anda telah dilampirkan pada email ini.</div>
                                        <div style="margin-top:12px; font-size:12px; color:#065f46;">
                                            <span style="display:inline-block; padding:8px 16px; background-color:#047857; color:#ffffff; border-radius:8px;">
                                                📄 Sertifikat Finisher.pdf
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px;">
                            <p style="margin:0; font-size:14px; line-height:1.7; color:#475569;">Terima kasih telah berpartisipasi dalam event Samawa Run. Simpan sertifikat ini sebagai bukti prestasi Anda. Kami berharap dapat melihat Anda di event-event berikutnya!</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
