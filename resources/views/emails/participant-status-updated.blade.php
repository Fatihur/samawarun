<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $statusTitle }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f5f1e8; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#f5f1e8; padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:680px; background-color:#ffffff; border-radius:18px; overflow:hidden;">
                    <tr>
                        <td style="background:linear-gradient(135deg, #0f766e 0%, #134e4a 100%); padding:28px 24px; text-align:center; color:#ffffff;">
                            <div style="font-size:30px; font-weight:700; letter-spacing:0.5px;">Samawa Run</div>
                            <div style="margin-top:8px; font-size:15px; opacity:0.92;">{{ $statusTitle }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px 24px 12px;">
                            @if ($statusBadge === 'TERVERIFIKASI')
                                <span style="display:inline-block; padding:8px 14px; border-radius:999px; background-color:#ecfdf5; color:#047857; font-size:12px; font-weight:700; letter-spacing:0.08em;">{{ $statusBadge }}</span>
                            @else
                                <span style="display:inline-block; padding:8px 14px; border-radius:999px; background-color:#fef2f2; color:#b91c1c; font-size:12px; font-weight:700; letter-spacing:0.08em;">{{ $statusBadge }}</span>
                            @endif
                            <p style="margin:20px 0 12px; font-size:16px; line-height:1.7;">Halo {{ $participant->name }},</p>
                            @foreach ($introLines as $line)
                                <p style="margin:0 0 12px; font-size:16px; line-height:1.7;">{{ $line }}</p>
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:12px 24px 0;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #e5e7eb; border-radius:16px; overflow:hidden; background-color:#fcfcfb;">
                                <tr>
                                    <td style="padding:18px 20px; border-bottom:1px solid #e5e7eb; background-color:#f8fafc;">
                                        <div style="font-size:18px; font-weight:700; color:#0f172a;">Struk Pendaftaran Peserta</div>
                                        <div style="margin-top:4px; font-size:13px; color:#64748b;">Ringkasan data peserta dan detail event</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:18px 20px;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td style="padding:0 0 12px; width:50%; vertical-align:top;">
                                                    <div style="font-size:11px; font-weight:700; letter-spacing:0.08em; color:#94a3b8; text-transform:uppercase;">Nama</div>
                                                    <div style="margin-top:4px; font-size:15px; color:#0f172a;">{{ $participant->name }}</div>
                                                </td>
                                                <td style="padding:0 0 12px; width:50%; vertical-align:top;">
                                                    <div style="font-size:11px; font-weight:700; letter-spacing:0.08em; color:#94a3b8; text-transform:uppercase;">Email</div>
                                                    <div style="margin-top:4px; font-size:15px; color:#0f172a;">{{ $participant->email }}</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:0 0 12px; width:50%; vertical-align:top;">
                                                    <div style="font-size:11px; font-weight:700; letter-spacing:0.08em; color:#94a3b8; text-transform:uppercase;">Telepon</div>
                                                    <div style="margin-top:4px; font-size:15px; color:#0f172a;">{{ $participant->phone }}</div>
                                                </td>
                                                <td style="padding:0 0 12px; width:50%; vertical-align:top;">
                                                    <div style="font-size:11px; font-weight:700; letter-spacing:0.08em; color:#94a3b8; text-transform:uppercase;">Jenis Kelamin</div>
                                                    <div style="margin-top:4px; font-size:15px; color:#0f172a;">{{ $participant->gender === 'male' ? 'Laki-laki' : 'Perempuan' }}</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:0 0 12px; width:50%; vertical-align:top;">
                                                    <div style="font-size:11px; font-weight:700; letter-spacing:0.08em; color:#94a3b8; text-transform:uppercase;">Tanggal Lahir</div>
                                                    <div style="margin-top:4px; font-size:15px; color:#0f172a;">{{ $participant->birth_date?->format('d M Y') ?? '-' }}</div>
                                                </td>
                                                <td style="padding:0 0 12px; width:50%; vertical-align:top;">
                                                    <div style="font-size:11px; font-weight:700; letter-spacing:0.08em; color:#94a3b8; text-transform:uppercase;">NIK</div>
                                                    <div style="margin-top:4px; font-size:15px; color:#0f172a;">{{ $participant->nik }}</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:0 0 12px; width:50%; vertical-align:top;">
                                                    <div style="font-size:11px; font-weight:700; letter-spacing:0.08em; color:#94a3b8; text-transform:uppercase;">Event</div>
                                                    <div style="margin-top:4px; font-size:15px; color:#0f172a;">{{ $participant->event?->name ?? '-' }}</div>
                                                </td>
                                                <td style="padding:0 0 12px; width:50%; vertical-align:top;">
                                                    <div style="font-size:11px; font-weight:700; letter-spacing:0.08em; color:#94a3b8; text-transform:uppercase;">Tanggal Event</div>
                                                    <div style="margin-top:4px; font-size:15px; color:#0f172a;">{{ $participant->event?->date?->format('d M Y') ?? '-' }}</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:0 0 12px; width:50%; vertical-align:top;">
                                                    <div style="font-size:11px; font-weight:700; letter-spacing:0.08em; color:#94a3b8; text-transform:uppercase;">Lokasi</div>
                                                    <div style="margin-top:4px; font-size:15px; color:#0f172a;">{{ $participant->event?->location ?? '-' }}</div>
                                                </td>
                                                <td style="padding:0 0 12px; width:50%; vertical-align:top;">
                                                    <div style="font-size:11px; font-weight:700; letter-spacing:0.08em; color:#94a3b8; text-transform:uppercase;">Kategori Jarak</div>
                                                    <div style="margin-top:4px; font-size:15px; color:#0f172a;">{{ $participant->distance_category }}</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:0 0 12px; width:50%; vertical-align:top;">
                                                    <div style="font-size:11px; font-weight:700; letter-spacing:0.08em; color:#94a3b8; text-transform:uppercase;">Ukuran Jersey</div>
                                                    <div style="margin-top:4px; font-size:15px; color:#0f172a;">{{ $participant->jersey_size }}</div>
                                                </td>
                                                <td style="padding:0 0 12px; width:50%; vertical-align:top;">
                                                    <div style="font-size:11px; font-weight:700; letter-spacing:0.08em; color:#94a3b8; text-transform:uppercase;">Kontak Darurat</div>
                                                    <div style="margin-top:4px; font-size:15px; color:#0f172a;">{{ $participant->emergency_contact }}</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:0; width:50%; vertical-align:top;">
                                                    <div style="font-size:11px; font-weight:700; letter-spacing:0.08em; color:#94a3b8; text-transform:uppercase;">BIB</div>
                                                    <div style="margin-top:4px; font-size:18px; font-weight:700; color:#0f766e;">{{ $participant->bib_number ?? '-' }}</div>
                                                </td>
                                                <td style="padding:0; width:50%; vertical-align:top;">
                                                    <div style="font-size:11px; font-weight:700; letter-spacing:0.08em; color:#94a3b8; text-transform:uppercase;">Status</div>
                                                    @if ($statusBadge === 'TERVERIFIKASI')
                                                        <div style="margin-top:4px; font-size:15px; font-weight:700; color:#047857;">{{ $statusBadge }}</div>
                                                    @else
                                                        <div style="margin-top:4px; font-size:15px; font-weight:700; color:#b91c1c;">{{ $statusBadge }}</div>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @if ($barcode)
                        <tr>
                            <td style="padding:20px 24px 0;">
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #d1fae5; border-radius:16px; background-color:#f0fdf4;">
                                    <tr>
                                        <td style="padding:20px; text-align:center;">
                                            <div style="font-size:15px; font-weight:700; color:#065f46;">Barcode Peserta</div>
                                            <div style="margin-top:6px; font-size:13px; color:#047857;">Gunakan barcode ini bersama email ini sebagai bukti registrasi.</div>
                                            <img src="data:image/png;base64,{{ $barcode }}" alt="Barcode Peserta" style="display:block; margin:18px auto 0; width:280px; max-width:100%; height:auto;">
                                            <div style="margin-top:12px; font-size:13px; font-weight:700; letter-spacing:0.18em; color:#065f46;">{{ $barcodeValue }}</div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td style="padding:24px;">
                            <p style="margin:0; font-size:14px; line-height:1.7; color:#475569;">{{ $footerMessage }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
