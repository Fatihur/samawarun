<?php

return [
    'storage_path' => storage_path('app/word'),
    'template_path' => resource_path('word-templates'),
    'placeholder' => [
        'prefix' => '${',
        'suffix' => '}',
        'trim_whitespace' => true,
    ],
    'filename' => [
        'prefix' => 'document_',
        'suffix' => '',
        'timestamp' => true,
        'extension' => 'docx',
    ],
    'formats' => [
        'docx' => 'Word2007',
        'pdf' => 'PDF',
    ],
    'pdf_renderer' => 'DomPDF',
    'pdf_renderer_path' => base_path('vendor/dompdf/dompdf'),
    'default_font' => [
        'name' => 'Arial',
        'size' => 12,
        'color' => '000000',
    ],
    'watermark' => [
        'text' => [
            'font' => 'Arial',
            'size' => 48,
            'color' => 'CCCCCC',
            'rotation' => 45,
            'opacity' => 0.2,
        ],
        'image' => [
            'width' => 200,
            'height' => 200,
            'margin_top' => 0,
            'margin_left' => 0,
            'opacity' => 0.2,
        ],
    ],
    'chart' => [
        'width' => 600,
        'height' => 400,
        'font' => 'Arial',
        'font_size' => 12,
    ],
    'signature' => [
        'width' => 150,
        'height' => 50,
        'image_path' => null,
        'certificate_path' => null,
        'certificate_password' => null,
    ],
    'page' => [
        'size' => 'A4',
        'orientation' => 'portrait',
        'margin_top' => 1440,
        'margin_right' => 1440,
        'margin_bottom' => 1440,
        'margin_left' => 1440,
    ],
    'debug' => env('WORD_DEBUG', false),
    'image' => [
        'width' => 300,
        'height' => 200,
        'alignment' => 'center',
    ],
    'builder' => [
        'default_font' => [
            'name' => 'Arial',
            'size' => 12,
            'color' => '000000',
        ],
        'paragraph' => [
            'alignment' => 'left',
            'spaceAfter' => 120,
        ],
        'heading_styles' => [
            1 => ['size' => 16, 'bold' => true],
            2 => ['size' => 14, 'bold' => true],
            3 => ['size' => 12, 'bold' => true],
        ],
    ],
    'download' => [
        'headers' => [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ],
        'delete_after_send' => true,
    ],
    'events' => [
        'before_save' => null,
        'after_save' => null,
        'before_download' => null,
        'after_download' => null,
    ],
];
