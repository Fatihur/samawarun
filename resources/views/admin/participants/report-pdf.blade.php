<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Peserta Samawa Run</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #0f172a;
        }
        .header p {
            margin: 5px 0 0 0;
            color: #64748b;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px 6px;
            text-align: left;
        }
        th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
        }
        tr:nth-child(even) {
            background-color: #f1f5f9;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        .status-pending { color: #d97706; }
        .status-verified { color: #16a34a; }
        .status-rejected { color: #dc2626; }
        
        .footer {
            margin-top: 30px;
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Laporan Data Peserta Samawa Run</h1>
        <p>Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }} | Total: {{ $participants->count() }} Peserta</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 3%">No.</th>
                <th style="width: 12%">Nomor BIB</th>
                <th style="width: 15%">Event</th>
                <th style="width: 20%">Nama Peserta</th>
                <th style="width: 13%">Kontak</th>
                <th style="width: 16%">Kontak Darurat</th>
                <th style="width: 9%">Kategori</th>
                <th style="width: 7%">Jersey</th>
                <th style="width: 15%">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($participants as $index => $participant)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td style="font-family: monospace; font-weight: bold;">{{ $participant->bib_number ?? '-' }}</td>
                    <td>{{ $participant->event?->name ?? 'N/A' }}</td>
                    <td>
                        <strong>{{ $participant->name }}</strong><br>
                        <span style="font-size: 8px; color: #64748b;">NIK: {{ $participant->nik }}</span>
                    </td>
                    <td>
                        {{ $participant->phone }}<br>
                        <span style="font-size: 8px; color: #64748b;">{{ $participant->email }}</span>
                    </td>
                    <td>
                        <strong>{{ $participant->emergency_contact_name }}</strong><br>
                        <span style="font-size: 8px; color: #64748b;">{{ $participant->emergency_contact_relationship_label }} - {{ $participant->emergency_contact_phone }}</span>
                    </td>
                    <td class="text-center"><strong>{{ $participant->distance_category }}</strong></td>
                    <td class="text-center">{{ $participant->jersey_size }}</td>
                    <td>
                        @if($participant->status === \App\Models\Participant::STATUS_PENDING)
                            <span class="status-pending">Pending</span>
                        @elseif($participant->status === \App\Models\Participant::STATUS_VERIFIED)
                            <span class="status-verified">Verified</span>
                        @elseif($participant->status === \App\Models\Participant::STATUS_REJECTED)
                            <span class="status-rejected">Rejected</span>
                        @else
                            {{ $participant->status }}
                        @endif
                    </td>
                </tr>
            @endforeach
            @if($participants->isEmpty())
                <tr>
                    <td colspan="9" class="text-center" style="padding: 20px;">Tidak ada data peserta.</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        Generate by Samawa Run System &copy; {{ date('Y') }}
    </div>

</body>
</html>
