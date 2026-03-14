<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sertifikat</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        @page {
            size: A4 {{ $orientation }};
            margin: 0;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            background: #ffffff;
            color: #000000;
        }

        .page-wrap {
            position: relative;
            @if ($orientation === 'landscape')
                width: 297mm;
                height: 210mm;
            @else
                width: 210mm;
                height: 297mm;
            @endif
            overflow: hidden;
            background-color: #ffffff;
        }

        .page-break {
            page-break-after: always;
        }

        .bg-image {
            position: absolute;
            top: 0;
            left: 0;
            @if ($orientation === 'landscape')
                width: 297mm;
                height: 210mm;
            @else
                width: 210mm;
                height: 297mm;
            @endif
            z-index: 0;
        }

        .text-element {
            position: absolute;
            z-index: 1;
            line-height: 1.3;
            word-wrap: break-word;
        }
    </style>
</head>
<body>
    @foreach ($pages as $pageIndex => $page)
        <div class="page-wrap {{ ! $loop->last ? 'page-break' : '' }}">
            @if ($backgroundBase64)
                <img src="{{ $backgroundBase64 }}" alt="" class="bg-image">
            @endif

            @foreach ($page['elements'] as $element)
                @php
                    $x = floatval($element['x'] ?? 50);
                    $y = floatval($element['y'] ?? 50);
                    $w = floatval($element['width'] ?? 50);
                    $fontSize = intval($element['fontSize'] ?? 16);
                    $fontFamily = $element['fontFamily'] ?? 'DejaVu Sans';
                    $fontWeight = $element['fontWeight'] ?? 'normal';
                    $fontStyle = $element['fontStyle'] ?? 'normal';
                    $textDecoration = $element['textDecoration'] ?? 'none';
                    $textTransform = $element['textTransform'] ?? 'none';
                    $color = $element['color'] ?? '#000000';
                    $textAlign = $element['textAlign'] ?? 'center';

                    if ($orientation === 'landscape') {
                        $pageW = 297;
                        $pageH = 210;
                    } else {
                        $pageW = 210;
                        $pageH = 297;
                    }

                    $leftMm = ($x / 100) * $pageW - ($w / 100 * $pageW / 2);
                    $topMm = ($y / 100) * $pageH;
                    $widthMm = ($w / 100) * $pageW;
                    
                @endphp
                <div class="text-element" style="
                    left: {{ $leftMm }}mm;
                    top: {{ $topMm }}mm;
                    width: {{ $widthMm }}mm;
                    font-family: '{{ $fontFamily }}', sans-serif;
                    font-size: {{ $fontSize }}px;
                    font-weight: {{ $fontWeight }};
                    font-style: {{ $fontStyle }};
                    text-decoration: {{ $textDecoration }};
                    text-transform: {{ $textTransform }};
                    color: {{ $color }};
                    text-align: {{ $textAlign }};
                ">
                    {{ $element['value'] ?? '' }}
                </div>
            @endforeach
        </div>
    @endforeach
</body>
</html>
