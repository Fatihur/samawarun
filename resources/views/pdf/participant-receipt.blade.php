<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk Pendaftaran Peserta</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            margin: 0;
            padding: 24px;
            background: #f8fafc;
        }

        .card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            overflow: hidden;
        }

        .header {
            background: #0f766e;
            color: #ffffff;
            padding: 24px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .header p {
            margin: 8px 0 0;
            font-size: 12px;
        }

        .content {
            padding: 24px;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: bold;
            color: #047857;
            background: #ecfdf5;
            margin-bottom: 16px;
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin: 0 0 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            width: 50%;
            vertical-align: top;
            padding: 0 0 14px;
        }

        .label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94a3b8;
            font-weight: bold;
        }

        .value {
            margin-top: 4px;
            font-size: 14px;
            color: #0f172a;
        }

        .bib {
            font-size: 20px;
            font-weight: bold;
            color: #0f766e;
        }

        .barcode-wrap {
            margin-top: 18px;
            padding: 18px;
            border: 1px solid #d1fae5;
            background: #f0fdf4;
            border-radius: 16px;
            text-align: center;
        }

        .barcode-wrap img {
            width: 280px;
            max-width: 100%;
            height: auto;
        }

        .barcode-text {
            margin-top: 10px;
            font-size: 12px;
            letter-spacing: 0.16em;
            font-weight: bold;
            color: #065f46;
        }

        .footer {
            margin-top: 18px;
            font-size: 12px;
            color: #475569;
            line-height: 1.7;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>Samawa Run</h1>
            <p>Struk Pendaftaran Peserta</p>
        </div>

        <div class="content">
            <div class="badge">TERVERIFIKASI</div>
            <p class="section-title">Detail Peserta</p>

            <table>
                <tr>
                    <td>
                        <div class="label">Nama</div>
                        <div class="value">{{ $participant->name }}</div>
                    </td>
                    <td>
                        <div class="label">Email</div>
                        <div class="value">{{ $participant->email }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="label">Telepon</div>
                        <div class="value">{{ $participant->phone }}</div>
                    </td>
                    <td>
                        <div class="label">Jenis Kelamin</div>
                        <div class="value">{{ $participant->gender === 'male' ? 'Laki-laki' : 'Perempuan' }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="label">Tanggal Lahir</div>
                        <div class="value">{{ $participant->birth_date?->format('d M Y') ?? '-' }}</div>
                    </td>
                    <td>
                        <div class="label">NIK</div>
                        <div class="value">{{ $participant->nik }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="label">Event</div>
                        <div class="value">{{ $participant->event?->name ?? '-' }}</div>
                    </td>
                    <td>
                        <div class="label">Tanggal Event</div>
                        <div class="value">{{ $participant->event?->date?->format('d M Y') ?? '-' }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="label">Lokasi</div>
                        <div class="value">{{ $participant->event?->location ?? '-' }}</div>
                    </td>
                    <td>
                        <div class="label">Kategori Jarak</div>
                        <div class="value">{{ $participant->distance_category }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="label">Ukuran Jersey</div>
                        <div class="value">{{ $participant->jersey_size }}</div>
                    </td>
                    <td>
                        <div class="label">Kontak Darurat</div>
                        <div class="value">{{ $participant->emergency_contact_display }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="label">BIB</div>
                        <div class="value bib">{{ $participant->bib_number ?? '-' }}</div>
                    </td>
                    <td>
                        <div class="label">Status</div>
                        <div class="value">TERVERIFIKASI</div>
                    </td>
                </tr>
            </table>

            <div class="barcode-wrap">
                <div style="font-size: 14px; font-weight: bold; color: #065f46; margin-bottom: 10px;">QR Code Peserta</div>
                <img src="data:image/svg+xml;base64,{{ $barcode }}" alt="QR Code Peserta">
                <div class="barcode-text">{{ $barcodeValue }}</div>
            </div>

            <div class="footer">
                Simpan dokumen ini sebagai bukti pendaftaran. Tunjukkan QR Code dan BIB saat diperlukan pada hari pelaksanaan.
            </div>
        </div>
    </div>
</body>
</html>
