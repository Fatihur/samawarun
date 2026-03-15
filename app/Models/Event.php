<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

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
        'slug',
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

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Event $event): void {
            if (empty($event->slug)) {
                $event->slug = $event->generateSlug();
            }
        });

        static::updating(function (Event $event): void {
            if (empty($event->slug)) {
                $event->slug = $event->generateSlug();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    private function generateSlug(): string
    {
        $slug = \Illuminate\Support\Str::slug($this->name);
        $count = static::where('slug', 'like', $slug . '%')->where('id', '!=', $this->id ?? 0)->count();

        return $count > 0 ? "{$slug}-{$count}" : $slug;
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

    public function certificateTemplate(): HasOne
    {
        return $this->hasOne(CertificateTemplate::class);
    }

    public function distanceCategories(): BelongsToMany
    {
        return $this->belongsToMany(DistanceCategory::class)
            ->withPivot('price')
            ->withTimestamps();
    }

    public function galleries(): HasMany
    {
        return $this->hasMany(EventGallery::class)->orderBy('sort_order');
    }

    public function getPriceSummaryAttribute(): string
    {
        $prices = $this->categoryPrices();

        if ($prices->isEmpty()) {
            return $this->formatCurrency((float) $this->price);
        }

        $minPrice = (float) $prices->min();
        $maxPrice = (float) $prices->max();

        if ($minPrice === $maxPrice) {
            return $this->formatCurrency($minPrice);
        }

        return 'Mulai dari '.$this->formatCurrency($minPrice);
    }

    public function getPriceRangeAttribute(): string
    {
        $prices = $this->categoryPrices();

        if ($prices->isEmpty()) {
            return $this->formatCurrency((float) $this->price);
        }

        $minPrice = (float) $prices->min();
        $maxPrice = (float) $prices->max();

        if ($minPrice === $maxPrice) {
            return $this->formatCurrency($minPrice);
        }

        return $this->formatCurrency($minPrice).' - '.$this->formatCurrency($maxPrice);
    }

    public function getCategoryPriceListAttribute(): Collection
    {
        return $this->distanceCategories
            ->map(function (DistanceCategory $category): array {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'price' => (float) ($category->pivot?->price ?? $this->price ?? 0),
                    'formatted_price' => $this->formatCurrency((float) ($category->pivot?->price ?? $this->price ?? 0)),
                ];
            })
            ->values();
    }

    public function priceForDistanceCategory(string $distanceCategory): float
    {
        $match = $this->distanceCategories
            ->first(fn (DistanceCategory $category): bool => strtoupper($category->name) === strtoupper($distanceCategory));

        return (float) ($match?->pivot?->price ?? $this->price ?? 0);
    }

    private function categoryPrices(): Collection
    {
        return $this->distanceCategories
            ->pluck('pivot.price')
            ->filter(fn ($price): bool => $price !== null)
            ->map(fn ($price): float => (float) $price)
            ->values();
    }

    private function formatCurrency(float $amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }
}
