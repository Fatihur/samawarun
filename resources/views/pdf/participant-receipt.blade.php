<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk Pendaftaran Peserta</title>
    <style>
        @page {
            margin: 6px 8px 10px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #111827;
            margin: 0;
            font-size: 8px;
            line-height: 1.25;
        }

        .receipt {
            border: 1px dashed #6b7280;
            padding: 8px 6px 10px;
        }

        .center {
            text-align: center;
        }

        .brand {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .subtitle {
            margin-top: 2px;
            font-size: 7px;
            color: #4b5563;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .badge {
            margin: 6px auto 0;
            display: inline-block;
            padding: 2px 7px;
            border: 1px solid #111827;
            color: #111827;
            font-size: 7px;
            font-weight: 700;
            letter-spacing: 0.06em;
        }

        .divider {
            border-top: 1px dashed #4b5563;
            margin: 8px 0;
        }

        .section-title {
            margin: 0 0 5px;
            font-size: 7px;
            font-weight: 700;
            color: #6b7280;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 0 0 4px;
            vertical-align: top;
        }

        .label-col {
            width: 38%;
            color: #6b7280;
            padding-right: 6px;
        }

        .value-col {
            width: 62%;
            text-align: right;
            font-weight: 700;
            color: #111827;
            word-wrap: break-word;
            overflow-wrap: anywhere;
        }

        .bib {
            font-size: 11px;
            color: #111827;
            letter-spacing: 0.08em;
        }

        .amount {
            font-size: 10px;
            color: #111827;
        }

        .qr-wrap {
            border: 1px dashed #6b7280;
            padding: 8px 6px;
            text-align: center;
        }

        .qr-wrap img {
            width: 92px;
            height: 92px;
        }

        .qr-code {
            margin-top: 4px;
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 0.08em;
            color: #111827;
        }

        .muted {
            color: #6b7280;
        }

        .receipt-note {
            text-align: center;
            font-size: 7px;
            color: #4b5563;
            line-height: 1.35;
        }

        .footer {
            margin-top: 8px;
            font-size: 7px;
            color: #4b5563;
            text-align: center;
            line-height: 1.35;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="center">
            <div class="brand">Samawa Run</div>
            <div class="subtitle">Struk Pendaftaran Peserta</div>
            <div class="badge">TERVERIFIKASI</div>
            <div class="receipt-note" style="margin-top:6px;">
                {{ $participant->event?->name ?? '-' }}<br>
                {{ $participant->event?->date?->format('d M Y') ?? '-' }} {{ $participant->event?->location ? ' - '.$participant->event->location : '' }}
            </div>
        </div>

        <div class="divider"></div>

        <div>
            <p class="section-title">Ringkasan</p>
            <table>
                <tr><td class="label-col">Nama</td><td class="value-col">{{ $participant->name }}</td></tr>
                <tr><td class="label-col">No. BIB</td><td class="value-col bib">{{ $participant->bib_number ?? '-' }}</td></tr>
                <tr><td class="label-col">Status</td><td class="value-col">PEMBAYARAN DISETUJUI</td></tr>
                <tr><td class="label-col">Nominal</td><td class="value-col amount">{{ $participant->formatted_payment_amount }}</td></tr>
                <tr><td class="label-col">Kategori</td><td class="value-col">{{ $participant->distance_category }}</td></tr>
                <tr><td class="label-col">Jersey</td><td class="value-col">{{ $participant->jersey_size }}</td></tr>
            </table>
        </div>

        <div class="divider"></div>

        <div>
            <p class="section-title">Data Peserta</p>
            <table>
                <tr><td class="label-col">Email</td><td class="value-col">{{ $participant->email }}</td></tr>
                <tr><td class="label-col">Telepon</td><td class="value-col">{{ $participant->phone }}</td></tr>
                <tr><td class="label-col">Gender</td><td class="value-col">{{ $participant->gender === 'male' ? 'Laki-laki' : 'Perempuan' }}</td></tr>
                <tr><td class="label-col">Tgl. Lahir</td><td class="value-col">{{ $participant->birth_date?->format('d M Y') ?? '-' }}</td></tr>
                <tr><td class="label-col">NIK</td><td class="value-col">{{ $participant->nik }}</td></tr>
                <tr><td class="label-col">Kontak Darurat</td><td class="value-col">{{ $participant->emergency_contact_display }}</td></tr>
            </table>
        </div>

        <div class="divider"></div>

        <div class="qr-wrap">
            <div style="font-size:7px; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#111827;">QR Code Peserta</div>
            <img src="data:image/svg+xml;base64,{{ $barcode }}" alt="QR Code Peserta">
            <div class="qr-code">{{ $barcodeValue }}</div>
        </div>

        <div class="footer">
            Simpan struk ini sebagai bukti pendaftaran resmi.<br>
            Tunjukkan QR Code dan nomor BIB saat diperlukan.
        </div>
    </div>
</body>
</html>
