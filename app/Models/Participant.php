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

    public const EMERGENCY_RELATIONSHIP_FATHER = 'father';

    public const EMERGENCY_RELATIONSHIP_MOTHER = 'mother';

    public const EMERGENCY_RELATIONSHIP_HUSBAND = 'husband';

    public const EMERGENCY_RELATIONSHIP_WIFE = 'wife';

    public const EMERGENCY_RELATIONSHIP_CHILD = 'child';

    public const EMERGENCY_RELATIONSHIP_OTHER_FAMILY = 'other_family';

    public const EMERGENCY_RELATIONSHIPS = [
        self::EMERGENCY_RELATIONSHIP_FATHER,
        self::EMERGENCY_RELATIONSHIP_MOTHER,
        self::EMERGENCY_RELATIONSHIP_HUSBAND,
        self::EMERGENCY_RELATIONSHIP_WIFE,
        self::EMERGENCY_RELATIONSHIP_CHILD,
        self::EMERGENCY_RELATIONSHIP_OTHER_FAMILY,
    ];

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
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
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

    public function getEmergencyContactRelationshipLabelAttribute(): string
    {
        return match ($this->emergency_contact_relationship) {
            self::EMERGENCY_RELATIONSHIP_FATHER => 'Ayah',
            self::EMERGENCY_RELATIONSHIP_MOTHER => 'Ibu',
            self::EMERGENCY_RELATIONSHIP_HUSBAND => 'Suami',
            self::EMERGENCY_RELATIONSHIP_WIFE => 'Istri',
            self::EMERGENCY_RELATIONSHIP_CHILD => 'Anak',
            self::EMERGENCY_RELATIONSHIP_OTHER_FAMILY => 'Keluarga Lain',
            default => '-',
        };
    }

    public function getEmergencyContactDisplayAttribute(): string
    {
        $segments = array_filter([
            $this->emergency_contact_relationship_label !== '-' ? $this->emergency_contact_relationship_label : null,
            $this->emergency_contact_name,
            $this->emergency_contact_phone,
        ]);

        return $segments === [] ? '-' : implode(' - ', $segments);
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
