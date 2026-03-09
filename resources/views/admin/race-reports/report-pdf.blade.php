<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Race Samawa Run</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 10px; color: #334155; }
        .header { text-align: center; margin-bottom: 18px; border-bottom: 1px solid #cbd5e1; padding-bottom: 14px; }
        .header h1 { margin: 0; font-size: 18px; color: #0f172a; }
        .header p { margin: 4px 0 0; color: #64748b; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #dbe2ea; padding: 7px 6px; text-align: left; vertical-align: top; }
        th { background: #f8fafc; font-size: 9px; text-transform: uppercase; color: #475569; }
        tr:nth-child(even) { background: #f8fafc; }
        .text-center { text-align: center; }
        .text-mono { font-family: monospace; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Pencatatan Race Samawa Run</h1>
        <p>
            Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }}
            | Total: {{ $participants->count() }} peserta
            @if ($selectedEvent)
                | Event: {{ $selectedEvent->name }}
            @endif
            @if ($timingStatus === 'recorded')
                | Filter: Sudah Dicatat
            @elseif ($timingStatus === 'unrecorded')
                | Filter: Belum Dicatat
            @endif
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 4%">No.</th>
                <th style="width: 20%">Peserta</th>
                <th style="width: 17%">Event</th>
                <th style="width: 10%">BIB</th>
                <th style="width: 8%">Kategori</th>
                <th style="width: 11%">Status Peserta</th>
                <th style="width: 12%">Status Race</th>
                <th style="width: 10%">Finish</th>
                <th style="width: 8%">Durasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($participants as $index => $participant)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $participant->name }}</strong><br>
                        <span style="font-size: 8px; color: #64748b;">{{ $participant->email }}</span>
                    </td>
                    <td>{{ $participant->event?->name ?? 'N/A' }}</td>
                    <td class="text-mono">{{ $participant->bib_number ?? '-' }}</td>
                    <td class="text-center">{{ $participant->distance_category }}</td>
                    <td>{{ ucfirst($participant->status) }}</td>
                    <td>{{ $participant->race_finished_at ? 'Sudah Dicatat' : 'Belum Dicatat' }}</td>
                    <td>{{ $participant->race_finished_at?->format('d/m/Y H:i:s') ?? '-' }}</td>
                    <td class="text-mono">{{ $participant->formatted_race_duration ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 20px;">Tidak ada data laporan race.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">Generate by Samawa Run System &copy; {{ date('Y') }}</div>
</body>
</html>
