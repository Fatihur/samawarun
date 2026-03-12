<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RaceReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_race_report_page(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $event = Event::create([
            'event_code' => 'RPT001',
            'name' => 'Samawa Run Report',
            'description' => 'Race report event.',
            'date' => '2026-03-01',
            'start_time' => '06:00',
            'location' => 'Sumbawa',
            'price' => 100000,
            'contact' => '08123',
            'bank_account' => 'BCA',
            'is_active' => true,
        ]);

        Participant::create([
            'event_id' => $event->id,
            'bib_number' => '5A001',
            'name' => 'Pelari Finish',
            'birth_date' => '1998-02-10',
            'gender' => 'male',
            'nik' => '1234567890123456',
            'ktp_file' => 'participants/ktp/a.jpg',
            'phone' => '081234567890',
            'email' => 'finish@example.com',
            'address' => 'Sumbawa',
            'distance_category' => '5K',
            'jersey_size' => 'L',
            'emergency_contact_name' => 'Ayah Finish',
            'emergency_contact_phone' => '08111111111',
            'emergency_contact_relationship' => Participant::EMERGENCY_RELATIONSHIP_FATHER,
            'transfer_proof' => 'participants/payments/a.jpg',
            'status' => Participant::STATUS_VERIFIED,
            'workflow_status' => Participant::WORKFLOW_COMPLETED,
            'race_finished_at' => now(),
            'race_duration_seconds' => 3600,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.race-reports.index'));

        $response->assertOk();
        $response->assertSee('Laporan Race');
        $response->assertSee('Pelari Finish');
        $response->assertSee('Sudah Dicatat');
    }

    public function test_admin_can_export_race_report_csv(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $event = Event::create([
            'event_code' => 'RPT002',
            'name' => 'Samawa Run CSV',
            'description' => 'Race report event.',
            'date' => '2026-03-01',
            'start_time' => '06:00',
            'location' => 'Sumbawa',
            'price' => 100000,
            'contact' => '08123',
            'bank_account' => 'BCA',
            'is_active' => true,
        ]);

        Participant::create([
            'event_id' => $event->id,
            'bib_number' => '7A001',
            'name' => 'Pelari CSV',
            'birth_date' => '1998-02-10',
            'gender' => 'male',
            'nik' => '1234567890123457',
            'ktp_file' => 'participants/ktp/b.jpg',
            'phone' => '081234567891',
            'email' => 'csv@example.com',
            'address' => 'Sumbawa',
            'distance_category' => '7K',
            'jersey_size' => 'M',
            'emergency_contact_name' => 'Ibu CSV',
            'emergency_contact_phone' => '08111111112',
            'emergency_contact_relationship' => Participant::EMERGENCY_RELATIONSHIP_MOTHER,
            'transfer_proof' => 'participants/payments/b.jpg',
            'status' => Participant::STATUS_VERIFIED,
            'workflow_status' => Participant::WORKFLOW_COMPLETED,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.race-reports.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=utf-8');
        $this->assertStringContainsString('Pelari CSV', $response->streamedContent());
    }
}
