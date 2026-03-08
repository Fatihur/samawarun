<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

class Participant extends Model
{
    use HasFactory, Notifiable;

    public const STATUS_PENDING = 'pending';

    public const STATUS_VERIFIED = 'verified';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'event_id',
        'bib_number',
        'name',
        'birth_date',
        'gender',
        'nik',
        'ktp_file',
        'phone',
        'email',
        'address',
        'distance_category',
        'jersey_size',
        'emergency_contact',
        'transfer_proof',
        'status',
        'race_finished_at',
        'race_duration_seconds',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'race_finished_at' => 'datetime',
        ];
    }

    public function getFormattedRaceDurationAttribute(): ?string
    {
        if ($this->race_duration_seconds === null) {
            return null;
        }

        $hours = intdiv($this->race_duration_seconds, 3600);
        $minutes = intdiv($this->race_duration_seconds % 3600, 60);
        $seconds = $this->race_duration_seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    public function getRaceStartedAtAttribute(): ?Carbon
    {
        if (! $this->event?->date || ! $this->event?->start_time) {
            return null;
        }

        return Carbon::parse($this->event->date->format('Y-m-d').' '.$this->event->start_time->format('H:i:s'));
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
