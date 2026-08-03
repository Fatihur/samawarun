<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BibSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_prefixes',
        'category_start_numbers',
        'number_padding',
        'template_title',
        'footer_text',
        'primary_color',
        'accent_color',
        'text_color',
        'meta_text_color',
        'bib_font_size',
        'name_font_size',
        'show_event_date',
        'show_event_location',
        'background_image_path',
        'kiosk_header_logos',
        'kiosk_footer_logos',
        'kiosk_sponsor_text',
    ];

    protected function casts(): array
    {
        return [
            'category_prefixes' => 'array',
            'category_start_numbers' => 'array',
            'number_padding' => 'integer',
            'bib_font_size' => 'integer',
            'name_font_size' => 'integer',
            'show_event_date' => 'boolean',
            'show_event_location' => 'boolean',
            'kiosk_header_logos' => 'array',
            'kiosk_footer_logos' => 'array',
        ];
    }

    public static function defaults(): array
    {
        return [
            'category_prefixes' => [],
            'category_start_numbers' => [],
            'number_padding' => 3,
            'template_title' => 'Nomor Dada',
            'footer_text' => 'Nomor dada resmi peserta. Dokumen ini bukan nota/struk pembayaran.',
            'primary_color' => '#0f172a',
            'accent_color' => '#cbd5e1',
            'text_color' => '#0f172a',
            'meta_text_color' => '#334155',
            'bib_font_size' => 220,
            'name_font_size' => 35,
            'show_event_date' => true,
            'show_event_location' => true,
            'background_image_path' => null,
            'kiosk_header_logos' => null,
            'kiosk_footer_logos' => null,
            'kiosk_sponsor_text' => 'Sponsored by',
        ];
    }

    public static function current(): self
    {
        return self::query()->firstOrCreate([], self::defaults());
    }
}
