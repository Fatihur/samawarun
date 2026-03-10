<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBibScanTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_bib_scan_page(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('admin.bib-scan.index'));

        $response->assertOk();
        $response->assertSee('Scan BIB');
        $response->assertSee('Tampilkan Informasi Peserta');
    }

    public function test_admin_can_find_participant_information_by_bib(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $event = Event::create([
            'event_code' => 'BSCAN001',
            'name' => 'Samawa Run Lookup',
            'description' => 'Event scan admin.',
            'date' => '2026-03-10',
            'start_time' => '06:00',
            'location' => 'Sumbawa',
            'price' => 100000,
            'contact' => '08123',
            'bank_account' => 'BCA',
            'is_active' => true,
        ]);

        Participant::create([
            'event_id' => $event->id,
            'bib_number' => '10K007',
            'name' => 'Pelari Admin',
            'birth_date' => '1998-02-10',
            'gender' => 'male',
            'nik' => '1234567890123456',
            'ktp_file' => 'participants/ktp/a.jpg',
            'phone' => '081234567890',
            'email' => 'adminscan@example.com',
            'address' => 'Sumbawa',
            'distance_category' => '10K',
            'jersey_size' => 'L',
            'emergency_contact_name' => 'Kontak Admin',
            'emergency_contact_phone' => '08111111111',
            'emergency_contact_relationship' => Participant::EMERGENCY_RELATIONSHIP_FATHER,
            'transfer_proof' => 'participants/payments/a.jpg',
            'status' => Participant::STATUS_VERIFIED,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.bib-scan.index', [
            'event_id' => $event->id,
            'bib_number' => '10k007',
        ]));

        $response->assertOk();
        $response->assertSee('Pelari Admin');
        $response->assertSee('10K007');
        $response->assertSee('adminscan@example.com');
    }

    public function test_admin_sees_not_found_message_for_missing_bib(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $event = Event::create([
            'event_code' => 'BSCAN002',
            'name' => 'Samawa Run Missing',
            'description' => 'Event scan admin.',
            'date' => '2026-03-10',
            'start_time' => '06:00',
            'location' => 'Sumbawa',
            'price' => 100000,
            'contact' => '08123',
            'bank_account' => 'BCA',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.bib-scan.index', [
            'event_id' => $event->id,
            'bib_number' => 'NOTFOUND',
        ]));

        $response->assertOk();
        $response->assertSee('tidak ditemukan pada event yang dipilih');
        $response->assertSee('NOTFOUND');
    }

    public function test_admin_bib_scan_validates_bib_format(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $event = Event::create([
            'event_code' => 'BSCAN003',
            'name' => 'Samawa Run Invalid',
            'description' => 'Event scan admin.',
            'date' => '2026-03-10',
            'start_time' => '06:00',
            'location' => 'Sumbawa',
            'price' => 100000,
            'contact' => '08123',
            'bank_account' => 'BCA',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.bib-scan.index'))
            ->get(route('admin.bib-scan.index', [
                'event_id' => $event->id,
                'bib_number' => 'INV@LID',
            ]));

        $response->assertRedirect(route('admin.bib-scan.index'));
        $response->assertSessionHasErrors('bib_number');
    }
}
