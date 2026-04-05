<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Participant;
use App\Models\User;
use App\Notifications\ParticipantRegistrationApprovedNotification;
use App\Notifications\ParticipantRejectedNotification;
use App\Notifications\ParticipantVerifiedNotification;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ParticipantStatusNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::table('participants', function (Blueprint $table): void {
            $table->dropUnique(['event_id', 'bib_number']);
        });
    }

    public function test_verify_sends_registration_approved_email_notification(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['is_admin' => true]);

        $event = Event::create([
            'event_code' => 'SMR-2026',
            'name' => 'Samawa Run 2026',
            'description' => 'Event lari tahunan.',
            'date' => now()->addMonth(),
            'location' => 'Sumbawa',
            'price' => 150000,
            'contact' => '08123456789',
            'bank_account' => 'BCA 1234567890',
            'is_active' => true,
        ]);

        $participant = Participant::create([
            'event_id' => $event->id,
            'name' => 'Budi Pelari',
            'birth_date' => '1998-01-20',
            'gender' => 'male',
            'nik' => '1234567890123456',
            'phone' => '081234567890',
            'email' => 'budi@example.com',
            'address' => 'Sumbawa Besar',
            'distance_category' => '5K',
            'jersey_size' => 'L',
            'emergency_contact_name' => 'Ayah Budi',
            'emergency_contact_phone' => '081111111111',
            'emergency_contact_relationship' => Participant::EMERGENCY_RELATIONSHIP_FATHER,
            'transfer_proof' => null,
            'status' => Participant::STATUS_PENDING,
            'workflow_status' => Participant::WORKFLOW_SUBMITTED,
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.participants.verify', $participant));

        $response->assertRedirect();
        $participant->refresh();

        $this->assertSame(Participant::STATUS_PENDING, $participant->status);
        $this->assertSame(Participant::WORKFLOW_APPROVED_WAITING_PAYMENT, $participant->workflow_status);
        $this->assertNotNull($participant->payment_token);

        Notification::assertSentTo([$participant], ParticipantRegistrationApprovedNotification::class);
    }

    public function test_reject_sends_rejected_email_notification(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['is_admin' => true]);

        $event = Event::create([
            'event_code' => 'SMR-2026',
            'name' => 'Samawa Run 2026',
            'description' => 'Event lari tahunan.',
            'date' => now()->addMonth(),
            'location' => 'Sumbawa',
            'price' => 150000,
            'contact' => '08123456789',
            'bank_account' => 'BCA 1234567890',
            'is_active' => true,
        ]);

        $participant = Participant::create([
            'event_id' => $event->id,
            'name' => 'Siti Pelari',
            'birth_date' => '1999-03-12',
            'gender' => 'female',
            'nik' => '1234567890123999',
            'phone' => '081222222222',
            'email' => 'siti@example.com',
            'address' => 'Moyo Hilir',
            'distance_category' => '7K',
            'jersey_size' => 'M',
            'emergency_contact_name' => 'Ibu Siti',
            'emergency_contact_phone' => '082222222222',
            'emergency_contact_relationship' => Participant::EMERGENCY_RELATIONSHIP_MOTHER,
            'transfer_proof' => null,
            'status' => Participant::STATUS_PENDING,
            'workflow_status' => Participant::WORKFLOW_SUBMITTED,
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.participants.reject', $participant));

        $response->assertRedirect();
        $participant->refresh();

        $this->assertSame(Participant::STATUS_REJECTED, $participant->status);
        $this->assertSame(Participant::WORKFLOW_REGISTRATION_REJECTED, $participant->workflow_status);
        $this->assertNull($participant->bib_number);

        Notification::assertSentTo([$participant], ParticipantRejectedNotification::class);
    }

    public function test_approve_payment_sends_final_verified_email_notification(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['is_admin' => true]);

        $event = Event::create([
            'event_code' => 'SMR-2027',
            'name' => 'Samawa Run 2027',
            'description' => 'Event lari tahunan.',
            'date' => now()->addMonth(),
            'location' => 'Sumbawa',
            'price' => 150000,
            'contact' => '08123456789',
            'bank_account' => 'BCA 1234567890',
            'is_active' => true,
        ]);

        app(\App\Models\BibSetting::class)::current();

        $participant = Participant::create([
            'event_id' => $event->id,
            'name' => 'Andi Pelari',
            'birth_date' => '1998-01-20',
            'gender' => 'male',
            'nik' => '1234567890123456',
            'phone' => '081234567890',
            'email' => 'andi@example.com',
            'address' => 'Sumbawa Besar',
            'distance_category' => '5K',
            'jersey_size' => 'L',
            'emergency_contact_name' => 'Ayah Andi',
            'emergency_contact_phone' => '081111111111',
            'emergency_contact_relationship' => Participant::EMERGENCY_RELATIONSHIP_FATHER,
            'transfer_proof' => 'participants/payments/andi.jpg',
            'status' => Participant::STATUS_PENDING,
            'workflow_status' => Participant::WORKFLOW_PAYMENT_SUBMITTED,
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.participants.payment.approve', $participant));

        $response->assertRedirect();
        $participant->refresh();

        $this->assertSame(Participant::STATUS_VERIFIED, $participant->status);
        $this->assertSame(Participant::WORKFLOW_COMPLETED, $participant->workflow_status);
        $this->assertNotNull($participant->bib_number);

        Notification::assertSentTo([$participant], ParticipantVerifiedNotification::class);
    }
}
