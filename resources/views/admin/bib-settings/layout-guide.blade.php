<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Panduan Tata Letak Nomor Dada</title>
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
        }

        .page-wrap {
            position: relative;
            width: 210mm;
            height: 148mm;
            background: #ffffff;
            overflow: hidden;
        }

        /* Grid lines */
        .grid-line-h {
            position: absolute;
            left: 0;
            right: 0;
            height: 0;
            border-top: 0.5px dashed #cbd5e1;
        }
        .grid-line-v {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 0;
            border-left: 0.5px dashed #cbd5e1;
        }

        /* Zone boxes */
        .zone {
            position: absolute;
            border: 1.5px dashed;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .zone-label {
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 1mm 3mm;
            border-radius: 2mm;
            color: #ffffff;
            position: absolute;
            top: 2mm;
            left: 2mm;
        }

        .zone-desc {
            font-size: 7px;
            color: #64748b;
            text-align: center;
            padding: 0 4mm;
            line-height: 1.4;
        }

        .zone-sample {
            font-weight: 800;
            text-align: center;
            color: #1e293b;
        }

        /* Measurement labels */
        .measure {
            position: absolute;
            font-size: 6px;
            color: #94a3b8;
            font-weight: 600;
        }

        /* Title bar */
        .title-bar {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 10mm;
            background: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .title-bar-text {
            color: #ffffff;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        /* Info panel */
        .info-panel {
            position: absolute;
            bottom: 5mm;
            right: 5mm;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 3mm;
            padding: 3mm 4mm;
            font-size: 6.5px;
            color: #475569;
            line-height: 1.6;
            max-width: 70mm;
        }
        .info-panel strong {
            color: #0f172a;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    {{-- Page 1: Layout Guide with Zones --}}
    <div class="page-wrap page-break">
        {{-- Title --}}
        <div class="title-bar">
            <span class="title-bar-text">Panduan Tata Letak Background Nomor Dada &mdash; A5 Landscape (210 x 148 mm)</span>
        </div>

        {{-- Grid lines --}}
        <div class="grid-line-h" style="top: 25%;"></div>
        <div class="grid-line-h" style="top: 50%;"></div>
        <div class="grid-line-h" style="top: 75%;"></div>
        <div class="grid-line-v" style="left: 25%;"></div>
        <div class="grid-line-v" style="left: 50%;"></div>
        <div class="grid-line-v" style="left: 75%;"></div>

        {{-- Measurements --}}
        <div class="measure" style="top: 11mm; left: 2mm;">0,0</div>
        <div class="measure" style="top: 11mm; right: 2mm;">210mm</div>
        <div class="measure" style="bottom: 2mm; left: 2mm;">148mm</div>

        {{-- Zone 1: Event Header --}}
        <div class="zone" style="top: 12mm; left: 20mm; right: 20mm; height: 18mm; border-color: #3b82f6; background: rgba(59,130,246,0.06);">
            <div class="zone-label" style="background: #3b82f6;">Zona Nama Event</div>
            <div class="zone-sample" style="font-size: 11px; margin-top: 5mm;">NAMA EVENT DI SINI</div>
            <div class="zone-desc" style="margin-top: 1mm;">Posisi: top 8mm, kiri-kanan 20mm<br>Berisi nama event + tanggal/lokasi</div>
        </div>

        {{-- Zone 2: BIB Title --}}
        <div class="zone" style="top: 32mm; left: 30mm; right: 30mm; height: 8mm; border-color: #8b5cf6; background: rgba(139,92,246,0.06);">
            <div class="zone-label" style="background: #8b5cf6;">Judul BIB</div>
            <div class="zone-desc">{{ $setting->template_title }} &mdash; Posisi: top 32mm</div>
        </div>

        {{-- Zone 3: BIB Number (main area) --}}
        <div class="zone" style="top: 42mm; left: 10mm; right: 10mm; height: 60mm; border-color: #ef4444; background: rgba(239,68,68,0.06);">
            <div class="zone-label" style="background: #ef4444;">Nomor Dada (Area Utama)</div>
            <div class="zone-sample" style="font-size: 48px; letter-spacing: 4px; margin-top: 2mm; color: #ef4444;">5001</div>
            <div class="zone-desc" style="margin-top: 1mm;">AREA TERBESAR — hindari desain background yang ramai di area ini<br>Posisi: top 38mm, kiri-kanan 10mm, font: {{ $setting->bib_font_size }}px</div>
        </div>

        {{-- Zone 4: Runner Name --}}
        <div class="zone" style="bottom: 28mm; left: 20mm; right: 20mm; height: 14mm; border-color: #10b981; background: rgba(16,185,129,0.06);">
            <div class="zone-label" style="background: #10b981;">Nama Peserta</div>
            <div class="zone-sample" style="font-size: 12px; margin-top: 4mm;">NAMA PESERTA</div>
            <div class="zone-desc">Posisi: bottom 30mm, font: {{ $setting->name_font_size }}px</div>
        </div>

        {{-- Zone 5: Distance Pill --}}
        <div class="zone" style="bottom: 14mm; left: 70mm; right: 70mm; height: 12mm; border-color: #f59e0b; background: rgba(245,158,11,0.06); border-radius: 999px;">
            <div class="zone-label" style="background: #f59e0b; top: auto; bottom: -5mm; left: 50%; transform: translateX(-50%); white-space: nowrap;">Kategori Jarak</div>
            <div class="zone-sample" style="font-size: 11px; color: #f59e0b;">5K / 7K / 10K</div>
        </div>

        {{-- Zone 6: Footer --}}
        <div class="zone" style="bottom: 3mm; left: 16mm; right: 16mm; height: 8mm; border-color: #64748b; background: rgba(100,116,139,0.06);">
            <div class="zone-desc" style="font-size: 6px;">FOOTER TEXT — Posisi: bottom 5mm, font: 7px</div>
        </div>

        {{-- Info Panel --}}
        <div class="info-panel">
            <strong>Panduan Desain Background:</strong><br>
            &bull; Ukuran canvas: <strong>210 x 148 mm</strong> (A5 landscape)<br>
            &bull; Resolusi minimal: <strong>2480 x 1748 px</strong> (300dpi)<br>
            &bull; Format: JPG atau PNG, maks 4MB<br>
            &bull; Zona <span style="color:#ef4444;">MERAH</span> = area nomor dada, jangan taruh elemen ramai<br>
            &bull; Area <span style="color:#3b82f6;">BIRU</span> & <span style="color:#10b981;">HIJAU</span> = nama event & peserta<br>
            &bull; Desain dekoratif idealnya di sudut-sudut / pinggiran<br>
            &bull; Background gelap? Gunakan warna teks terang di pengaturan
        </div>
    </div>

    {{-- Page 2: Clean canvas with safe zones only --}}
    <div class="page-wrap">
        {{-- Title --}}
        <div class="title-bar" style="background: #475569;">
            <span class="title-bar-text">Safe Zone — Area Aman untuk Desain Background</span>
        </div>

        {{-- Safe zone - corners & edges where decoration is safe --}}
        <div style="position:absolute; top: 12mm; left: 5mm; width: 14mm; height: 50mm; background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.3); border-radius: 2mm;"></div>
        <div style="position:absolute; top: 12mm; right: 5mm; width: 14mm; height: 50mm; background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.3); border-radius: 2mm;"></div>
        <div style="position:absolute; bottom: 12mm; left: 5mm; width: 14mm; height: 30mm; background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.3); border-radius: 2mm;"></div>
        <div style="position:absolute; bottom: 12mm; right: 5mm; width: 14mm; height: 30mm; background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.3); border-radius: 2mm;"></div>

        {{-- Top edge safe --}}
        <div style="position:absolute; top: 12mm; left: 22mm; right: 22mm; height: 6mm; background: rgba(59,130,246,0.1); border: 1px solid rgba(59,130,246,0.2); border-radius: 2mm;"></div>

        {{-- Danger zone - center --}}
        <div style="position:absolute; top: 30mm; left: 20mm; right: 20mm; bottom: 25mm; border: 2px dashed #ef4444; background: rgba(239,68,68,0.04); border-radius: 3mm;">
            <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); text-align:center;">
                <div style="font-size: 10px; font-weight: 800; color: #ef4444; letter-spacing: 2px; text-transform: uppercase;">Area Teks</div>
                <div style="font-size: 7px; color: #94a3b8; margin-top: 2mm;">Hindari elemen desain yang ramai di area ini</div>
            </div>
        </div>

        {{-- Legend --}}
        <div class="info-panel" style="bottom: 5mm; left: 5mm; right: auto;">
            <strong>Keterangan:</strong><br>
            <span style="color:#10b981;">&#9632;</span> Hijau = Area aman untuk logo/dekorasi<br>
            <span style="color:#3b82f6;">&#9632;</span> Biru = Area aman untuk desain ringan<br>
            <span style="color:#ef4444;">&#9632;</span> Merah = Area teks utama, hindari elemen ramai
        </div>
    </div>
</body>
</html>
