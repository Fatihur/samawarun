<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

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

    public const WORKFLOW_SUBMITTED = 'submitted';

    public const WORKFLOW_REGISTRATION_REJECTED = 'registration_rejected';

    public const WORKFLOW_APPROVED_WAITING_PAYMENT = 'approved_waiting_payment';

    public const WORKFLOW_PAYMENT_SUBMITTED = 'payment_submitted';

    public const WORKFLOW_PAYMENT_REJECTED = 'payment_rejected';

    public const WORKFLOW_COMPLETED = 'completed';

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
        'workflow_status',
        'registration_reviewed_at',
        'payment_requested_at',
        'payment_token',
        'payment_token_expires_at',
        'payment_submitted_at',
        'payment_reviewed_at',
        'race_finished_at',
        'race_duration_seconds',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'registration_reviewed_at' => 'datetime',
            'payment_requested_at' => 'datetime',
            'payment_token_expires_at' => 'datetime',
            'payment_submitted_at' => 'datetime',
            'payment_reviewed_at' => 'datetime',
            'race_finished_at' => 'datetime',
        ];
    }

    public function getWorkflowStatusLabelAttribute(): string
    {
        return match ($this->workflow_status) {
            self::WORKFLOW_SUBMITTED => 'Menunggu Review Pendaftaran',
            self::WORKFLOW_APPROVED_WAITING_PAYMENT => 'Menunggu Pembayaran',
            self::WORKFLOW_PAYMENT_SUBMITTED => 'Pembayaran Direview',
            self::WORKFLOW_PAYMENT_REJECTED => 'Pembayaran Ditolak',
            self::WORKFLOW_REGISTRATION_REJECTED => 'Pendaftaran Ditolak',
            self::WORKFLOW_COMPLETED => 'Selesai',
            default => ucfirst((string) $this->workflow_status),
        };
    }

    public function canUploadPaymentProof(): bool
    {
        return in_array($this->workflow_status, [
            self::WORKFLOW_APPROVED_WAITING_PAYMENT,
            self::WORKFLOW_PAYMENT_REJECTED,
        ], true)
            && $this->status === self::STATUS_PENDING
            && $this->payment_token !== null
            && $this->payment_token_expires_at?->isFuture();
    }

    public function issuePaymentToken(): void
    {
        $this->forceFill([
            'payment_token' => Str::random(40),
            'payment_requested_at' => now(),
            'payment_token_expires_at' => now()->addDays(7),
        ])->save();
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

    public function getPaymentAmountAttribute(): float
    {
        if (! $this->relationLoaded('event') && ! $this->event()->exists()) {
            return 0;
        }

        return (float) ($this->event?->priceForDistanceCategory((string) $this->distance_category) ?? 0);
    }

    public function getFormattedPaymentAmountAttribute(): string
    {
        return 'Rp '.number_format($this->payment_amount, 0, ',', '.');
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
