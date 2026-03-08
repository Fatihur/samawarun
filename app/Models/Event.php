<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Event extends Model
{
    use HasFactory;

    public const DISTANCE_5K = '5K';

    public const DISTANCE_7K = '7K';

    public const DISTANCE_10K = '10K';

    public const DISTANCES = [
        self::DISTANCE_5K,
        self::DISTANCE_7K,
        self::DISTANCE_10K,
    ];

    protected $fillable = [
        'event_code',
        'name',
        'poster',
        'description',
        'date',
        'start_time',
        'registration_deadline',
        'location',
        'price',
        'contact',
        'bank_account',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'start_time' => 'datetime:H:i',
            'registration_deadline' => 'datetime',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function isRegistrationOpen(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if (! $this->registration_deadline) {
            return true;
        }

        return now()->lessThanOrEqualTo($this->registration_deadline);
    }

    public function getRegistrationStatusLabelAttribute(): string
    {
        return $this->isRegistrationOpen() ? 'Registrasi Dibuka' : 'Pendaftaran Ditutup';
    }

    public function getRegistrationDeadlineForFormAttribute(): ?string
    {
        return $this->registration_deadline?->format('Y-m-d\TH:i');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    public function distanceCategories(): BelongsToMany
    {
        return $this->belongsToMany(DistanceCategory::class);
    }
}
