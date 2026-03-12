<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class RaceTimingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_record_race_finish_time_by_bib(): void
    {
        Carbon::setTestNow('2026-03-08 07:45:30');

        $admin = User::factory()->create(['is_admin' => true]);

        $event = Event::create([
            'event_code' => 'RACE001',
            'name' => 'Samawa Run 10K',
            'description' => 'Race day.',
            'date' => '2026-03-08',
            'start_time' => '06:30',
            'location' => 'Sumbawa',
            'price' => 100000,
            'contact' => '08123',
            'bank_account' => 'BCA',
            'is_active' => true,
        ]);

        $participant = Participant::create([
            'event_id' => $event->id,
            'bib_number' => '5A001',
            'name' => 'Pelari Cepat',
            'birth_date' => '1998-02-10',
            'gender' => 'male',
            'nik' => '1234567890123456',
            'ktp_file' => 'participants/ktp/a.jpg',
            'phone' => '081234567890',
            'email' => 'runner@example.com',
            'address' => 'Sumbawa',
            'distance_category' => '5K',
            'jersey_size' => 'L',
            'emergency_contact_name' => 'Ayah Pelari',
            'emergency_contact_phone' => '08111111111',
            'emergency_contact_relationship' => Participant::EMERGENCY_RELATIONSHIP_FATHER,
            'transfer_proof' => 'participants/payments/a.jpg',
            'status' => Participant::STATUS_VERIFIED,
            'workflow_status' => Participant::WORKFLOW_COMPLETED,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.race-timing.store'), [
            'event_id' => $event->id,
            'bib_number' => '5A001',
        ]);

        $response->assertRedirect(route('admin.race-timing.index', ['event_id' => $event->id]));

        $participant->refresh();

        $this->assertSame('2026-03-08 07:45:30', $participant->race_finished_at?->format('Y-m-d H:i:s'));
        $this->assertSame(4530, $participant->race_duration_seconds);
        $this->assertSame('01:15:30', $participant->formatted_race_duration);

        Carbon::setTestNow();
    }

    public function test_race_time_cannot_be_recorded_for_non_verified_participant(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $event = Event::create([
            'event_code' => 'RACE002',
            'name' => 'Samawa Run 7K',
            'description' => 'Race day.',
            'date' => '2026-03-08',
            'start_time' => '06:30',
            'location' => 'Sumbawa',
            'price' => 100000,
            'contact' => '08123',
            'bank_account' => 'BCA',
            'is_active' => true,
        ]);

        $participant = Participant::create([
            'event_id' => $event->id,
            'bib_number' => '7A001',
            'name' => 'Pelari Pending',
            'birth_date' => '1998-02-10',
            'gender' => 'male',
            'nik' => '1234567890123456',
            'ktp_file' => 'participants/ktp/a.jpg',
            'phone' => '081234567890',
            'email' => 'pending@example.com',
            'address' => 'Sumbawa',
            'distance_category' => '7K',
            'jersey_size' => 'L',
            'emergency_contact_name' => 'Ibu Pending',
            'emergency_contact_phone' => '08111111111',
            'emergency_contact_relationship' => Participant::EMERGENCY_RELATIONSHIP_MOTHER,
            'transfer_proof' => 'participants/payments/a.jpg',
            'status' => Participant::STATUS_PENDING,
            'workflow_status' => Participant::WORKFLOW_SUBMITTED,
        ]);

        $response = $this->actingAs($admin)->from(route('admin.race-timing.index', ['event_id' => $event->id]))->post(route('admin.race-timing.store'), [
            'event_id' => $event->id,
            'bib_number' => '7A001',
        ]);

        $response->assertRedirect(route('admin.race-timing.index', ['event_id' => $event->id]));
        $response->assertSessionHas('error');
        $this->assertNull($participant->fresh()->race_finished_at);
    }
}
