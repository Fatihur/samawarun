<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificateTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'background_image_path',
        'text_elements',
        'orientation',
    ];

    protected $casts = [
        'text_elements' => 'array',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function getDefaultTextElements(): array
    {
        return [
            [
                'placeholder' => 'participant_name',
                'label' => 'Nama Peserta',
                'x' => 50,
                'y' => 45,
                'fontSize' => 36,
                'fontFamily' => 'Great Vibes',
                'fontWeight' => 'normal',
                'fontStyle' => 'normal',
                'color' => '#000000',
                'textAlign' => 'center',
                'width' => 80,
            ],
            [
                'placeholder' => 'event_name',
                'label' => 'Nama Event',
                'x' => 50,
                'y' => 25,
                'fontSize' => 20,
                'fontFamily' => 'Inter',
                'fontWeight' => 'bold',
                'fontStyle' => 'normal',
                'color' => '#333333',
                'textAlign' => 'center',
                'width' => 70,
            ],
            [
                'placeholder' => 'distance_category',
                'label' => 'Kategori Jarak',
                'x' => 50,
                'y' => 55,
                'fontSize' => 18,
                'fontFamily' => 'Inter',
                'fontWeight' => 'normal',
                'fontStyle' => 'normal',
                'color' => '#333333',
                'textAlign' => 'center',
                'width' => 40,
            ],
            [
                'placeholder' => 'race_duration',
                'label' => 'Durasi Race',
                'x' => 50,
                'y' => 65,
                'fontSize' => 22,
                'fontFamily' => 'Inter',
                'fontWeight' => 'bold',
                'fontStyle' => 'normal',
                'color' => '#000000',
                'textAlign' => 'center',
                'width' => 40,
            ],
            [
                'placeholder' => 'event_date',
                'label' => 'Tanggal Event',
                'x' => 50,
                'y' => 75,
                'fontSize' => 14,
                'fontFamily' => 'Inter',
                'fontWeight' => 'normal',
                'fontStyle' => 'normal',
                'color' => '#555555',
                'textAlign' => 'center',
                'width' => 50,
            ],
        ];
    }
}
