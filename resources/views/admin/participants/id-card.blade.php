<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nomor Dada Peserta</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        @page {
            size: A5 landscape;
            margin: 0;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            background: #ffffff;
            color: #000000;
        }

        .page-wrap {
            position: relative;
            width: 210mm;
            height: 148mm;
            overflow: hidden;
            background-color: #ffffff;
        }

        .page-break {
            page-break-after: always;
        }

        /* Background image - FULL, 100% opacity */
        .bg-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 210mm;
            height: 148mm;
            object-fit: cover;
            z-index: 0;
        }

        /* Center Content Container */
        .content-container {
            position: absolute;
            top: 25mm;
            left: 0;
            width: 210mm;
            height: 98mm; /* 148 - 25 - 25 */
            text-align: center;
            z-index: 1; /* Lay on top of background */
        }

        .event-name {
            margin-top: 8mm;
            font-size: 12px;
            font-weight: normal;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #333;
        }

        .bib-number {
            margin-top: 2mm;
            font-size: {{ (int) ($setting->bib_font_size ?? 108) }}px;
            font-weight: 900;
            letter-spacing: 1px;
            line-height: 0.92;
        }

        .barcode-container {
            position: absolute;
            top: 78mm;
            right: 14mm;
            text-align: center;
            z-index: 2;
        }
        
        .barcode-container img {
            width: 22mm;
            height: 22mm;
            object-fit: contain;
        }

        .participant-name {
            margin-top: 6mm;
            font-size: 18px;
            font-weight: normal;
        }

        .distance-category {
            margin-top: 4mm;
            font-size: 22px;
            font-weight: normal;
        }
    </style>
</head>
<body>
    @foreach ($participants as $participant)
        @php
            // Use BIB if available, fallback to str-padded ID if not
            $barcodeText = $participant->bib_number ?? str_pad($participant->id, 4, '0', STR_PAD_LEFT);
            $barcodeBase64 = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->margin(1)->size(200)->generate($barcodeText));
        @endphp
        <div class="page-wrap {{ !$loop->last ? 'page-break' : '' }}">
            @if ($setting->background_image_path)
                <img src="{{ public_path('storage/'.$setting->background_image_path) }}" alt="" class="bg-image">
            @endif
            
            <div class="content-container">
                <div class="event-name">{{ $participant->event?->name ?? 'NAMA EVENT' }}</div>
                
                <div class="bib-number">{{ $participant->bib_number ?? '0000' }}</div>
                
                <div class="barcode-container">
                    <img src="data:image/svg+xml;base64,{{ $barcodeBase64 }}" alt="QR Code">
                </div>

                <div class="participant-name">{{ $participant->name }}</div>
                
                <div class="distance-category">{{ $participant->distance_category }}</div>
            </div>


        </div>
    @endforeach
</body>
</html>
