<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengingat Pembayaran - {{ $participant->event?->name ?? 'Samawa Run' }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f5f1e8; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#f5f1e8; padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:680px; background-color:#ffffff; border-radius:18px; overflow:hidden;">
                    <tr>
                        <td style="background:linear-gradient(135deg, #0f766e 0%, #134e4a 100%); padding:30px 24px; text-align:center; color:#ffffff;">
                            <div style="font-size:30px; font-weight:700; letter-spacing:0.5px;">Samawa Run</div>
                            <div style="margin-top:8px; font-size:15px; opacity:0.92;">Pengingat Pembayaran</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px 24px 12px;">
                            <span style="display:inline-block; padding:8px 14px; border-radius:999px; background-color:#fef3c7; color:#b45309; font-size:12px; font-weight:700; letter-spacing:0.08em;">MENUNGGU PEMBAYARAN</span>
                            
                            <p style="margin:20px 0 12px; font-size:16px; line-height:1.7;">Halo {{ $participant->name }},</p>
                            
                            <p style="margin:0 0 12px; font-size:16px; line-height:1.7;">Terima kasih telah mendaftar di <strong>{{ $participant->event?->name ?? 'Samawa Run' }}</strong> kategori <strong>{{ $participant->distance_category }}</strong>. Kami sangat senang Anda bergabung bersama kami!</p>
                            
                            @if ($waitingDays > 0)
                                <p style="margin:0 0 12px; font-size:16px; line-height:1.7;">Kami mencatat bahwa pendaftaran Anda sudah berlangsung selama <strong>{{ $waitingDays }} hari</strong> namun kami belum menerima bukti pembayaran.</p>
                            @endif
                            
                            <p style="margin:0 0 12px; font-size:16px; line-height:1.7;">Untuk melanjutkan proses pendaftaran Anda dan mengamankan slot di event ini, mohon kesediannya untuk menyelesaikan pembayaran sebelum batas waktu yang telah ditentukan.</p>
                            
                            <p style="margin:0 0 12px; font-size:16px; line-height:1.7;">Detail pembayaran dan link upload bukti transfer dapat dilihat di bawah ini:</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 24px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #fef3c7; border-radius:18px; overflow:hidden; background:linear-gradient(180deg, #fffbeb 0%, #fef3c7 100%);">
                                <tr>
                                    <td style="padding:22px 20px; text-align:center;">
                                        <div style="font-size:11px; font-weight:700; letter-spacing:0.16em; color:#b45309; text-transform:uppercase;">Nominal yang harus dibayar</div>
                                        <div style="margin-top:8px; font-size:34px; font-weight:800; color:#0f172a;">{{ $paymentAmount }}</div>
                                        <div style="margin-top:8px; font-size:13px; color:#475569;">Mohon transfer sesuai nominal agar verifikasi lebih cepat.</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 24px 0;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #e5e7eb; border-radius:16px; overflow:hidden; background-color:#fcfcfb;">
                                <tr>
                                    <td style="padding:18px 20px; border-bottom:1px solid #e5e7eb; background-color:#f8fafc;">
                                        <div style="font-size:17px; font-weight:700; color:#0f172a;">Detail Pembayaran</div>
                                        <div style="margin-top:4px; font-size:13px; color:#64748b;">Gunakan informasi ini saat melakukan transfer.</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:18px 20px;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td style="padding:0 0 12px; width:45%; vertical-align:top; font-size:12px; font-weight:700; letter-spacing:0.08em; color:#94a3b8; text-transform:uppercase;">Event</td>
                                                <td style="padding:0 0 12px; width:55%; vertical-align:top; font-size:15px; color:#0f172a;">{{ $participant->event?->name ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:0 0 12px; font-size:12px; font-weight:700; letter-spacing:0.08em; color:#94a3b8; text-transform:uppercase;">Kategori</td>
                                                <td style="padding:0 0 12px; font-size:15px; color:#0f172a;">{{ $participant->distance_category }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:0 0 12px; font-size:12px; font-weight:700; letter-spacing:0.08em; color:#94a3b8; text-transform:uppercase;">Nominal</td>
                                                <td style="padding:0 0 12px; font-size:15px; font-weight:700; color:#047857;">{{ $paymentAmount }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:0 0 12px; font-size:12px; font-weight:700; letter-spacing:0.08em; color:#94a3b8; text-transform:uppercase;">Rekening Tujuan</td>
                                                <td style="padding:0 0 12px; font-size:15px; color:#0f172a;">{{ $bankAccount ?: '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:0 0 12px; font-size:12px; font-weight:700; letter-spacing:0.08em; color:#94a3b8; text-transform:uppercase;">Batas Upload</td>
                                                <td style="padding:0 0 12px; font-size:15px; color:#0f172a;">{{ $paymentDeadline }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:0; font-size:12px; font-weight:700; letter-spacing:0.08em; color:#94a3b8; text-transform:uppercase;">Referensi</td>
                                                <td style="padding:0; font-size:15px; color:#0f172a;">{{ $referenceCode }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:24px;">
                            <a href="{{ $paymentUrl }}" style="display:inline-block; padding:14px 26px; border-radius:12px; background-color:#0f766e; color:#ffffff; text-decoration:none; font-size:15px; font-weight:700;">Upload Bukti Pembayaran</a>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 24px 24px;">
                            <div style="padding:16px 18px; border-radius:14px; background-color:#f8fafc; font-size:14px; line-height:1.7; color:#475569;">
                                <div style="font-weight:700; color:#0f172a; margin-bottom:6px;">Catatan penting</div>
                                <div>1. Mohon transfer sesuai nominal yang tertera agar verifikasi lebih cepat.</div>
                                <div>2. Segera upload bukti transfer setelah melakukan pembayaran.</div>
                                <div>3. Jika sudah membayar namun belum upload, silakan klik tombol di atas.</div>
                                <div>4. Apabila ada kendala, jangan ragu untuk menghubungi panitia.</div>
                                @if ($contact)
                                    <div style="margin-top:8px;">Kontak panitia: <strong style="color:#0f172a;">{{ $contact }}</strong></div>
                                @endif
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 24px 24px;">
                            <div style="text-align:center; font-size:14px; line-height:1.7; color:#64748b; border-top:1px solid #e5e7eb; padding-top:20px;">
                                <p style="margin:0 0 8px;">Terima kasih atas perhatian dan kerja samanya.</p>
                                <p style="margin:0;">Kami tunggu kehadiran Anda di event ini!</p>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
