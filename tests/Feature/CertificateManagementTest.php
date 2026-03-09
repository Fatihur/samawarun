<?php

namespace Tests\Feature;

use App\Models\CertificateTemplate;
use App\Models\Event;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CertificateManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_certificate_page(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $event = Event::create([
            'event_code' => 'CRT001',
            'name' => 'Samawa Run Finisher',
            'description' => 'Certificate event.',
            'date' => '2026-03-10',
            'start_time' => '06:00',
            'location' => 'Sumbawa',
            'price' => 100000,
            'contact' => '08123',
            'bank_account' => 'BCA',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.certificates.index', ['event_id' => $event->id]));

        $response->assertOk();
        $response->assertSee('Sertifikat Finisher');
        $response->assertSee('Visual Editor');
        $response->assertSee('Generate PDF');
    }

    public function test_admin_can_upload_background_image(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['is_admin' => true]);

        $event = Event::create([
            'event_code' => 'CRT002',
            'name' => 'Samawa Run Upload',
            'description' => 'Certificate event.',
            'date' => '2026-03-10',
            'start_time' => '06:00',
            'location' => 'Sumbawa',
            'price' => 100000,
            'contact' => '08123',
            'bank_account' => 'BCA',
            'is_active' => true,
        ]);

        $image = UploadedFile::fake()->image('certificate-bg.png', 1920, 1080);

        $response = $this->actingAs($admin)->post(route('admin.certificates.background.update'), [
            'event_id' => $event->id,
            'name' => 'Template Finisher',
            'background_image' => $image,
            'orientation' => 'landscape',
        ]);

        $response->assertRedirect(route('admin.certificates.index', ['tab' => 'template', 'event_id' => $event->id]));

        $template = CertificateTemplate::query()->where('event_id', $event->id)->first();

        $this->assertNotNull($template);
        $this->assertTrue(Storage::disk('public')->exists($template->background_image_path));
        $this->assertSame('landscape', $template->orientation);
        $this->assertIsArray($template->text_elements);
    }

    public function test_admin_can_save_text_element_positions(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $event = Event::create([
            'event_code' => 'CRT003',
            'name' => 'Samawa Run Elements',
            'description' => 'Certificate event.',
            'date' => '2026-03-10',
            'start_time' => '06:00',
            'location' => 'Sumbawa',
            'price' => 100000,
            'contact' => '08123',
            'bank_account' => 'BCA',
            'is_active' => true,
        ]);

        CertificateTemplate::create([
            'event_id' => $event->id,
            'name' => 'Test Template',
            'background_image_path' => 'certificates/backgrounds/test.png',
            'orientation' => 'landscape',
            'text_elements' => [],
        ]);

        $elements = [
            [
                'placeholder' => 'participant_name',
                'label' => 'Nama Peserta',
                'x' => 50,
                'y' => 45,
                'fontSize' => 28,
                'fontWeight' => 'bold',
                'color' => '#000000',
                'textAlign' => 'center',
                'width' => 80,
            ],
        ];

        $response = $this->actingAs($admin)->postJson(route('admin.certificates.elements.save'), [
            'event_id' => $event->id,
            'text_elements' => $elements,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $template = CertificateTemplate::query()->where('event_id', $event->id)->first();
        $this->assertCount(1, $template->text_elements);
        $this->assertSame('participant_name', $template->text_elements[0]['placeholder']);
    }

    public function test_admin_can_download_single_certificate_pdf(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['is_admin' => true]);

        $event = Event::create([
            'event_code' => 'CRT004',
            'name' => 'Samawa Run PDF',
            'description' => 'Certificate event.',
            'date' => '2026-03-10',
            'start_time' => '06:00',
            'location' => 'Sumbawa',
            'price' => 100000,
            'contact' => '08123',
            'bank_account' => 'BCA',
            'is_active' => true,
        ]);

        $bgImage = UploadedFile::fake()->image('bg.png', 1920, 1080);
        $bgPath = $bgImage->storeAs('certificates/backgrounds', 'test-bg.png', 'public');

        CertificateTemplate::create([
            'event_id' => $event->id,
            'name' => 'Template PDF',
            'background_image_path' => $bgPath,
            'orientation' => 'landscape',
            'text_elements' => [
                [
                    'placeholder' => 'participant_name',
                    'label' => 'Nama Peserta',
                    'x' => 50,
                    'y' => 45,
                    'fontSize' => 28,
                    'fontWeight' => 'bold',
                    'color' => '#000000',
                    'textAlign' => 'center',
                    'width' => 80,
                ],
            ],
        ]);

        $participant = Participant::create([
            'event_id' => $event->id,
            'bib_number' => '10K001',
            'name' => 'Finisher PDF',
            'birth_date' => '1998-02-10',
            'gender' => 'male',
            'nik' => '1234567890123458',
            'ktp_file' => 'participants/ktp/c.jpg',
            'phone' => '081234567892',
            'email' => 'pdf@example.com',
            'address' => 'Sumbawa',
            'distance_category' => '10K',
            'jersey_size' => 'L',
            'emergency_contact_name' => 'Ibu PDF',
            'emergency_contact_phone' => '08111111113',
            'emergency_contact_relationship' => Participant::EMERGENCY_RELATIONSHIP_MOTHER,
            'transfer_proof' => 'participants/payments/c.jpg',
            'status' => Participant::STATUS_VERIFIED,
            'race_finished_at' => now(),
            'race_duration_seconds' => 4200,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.participants.certificate', $participant));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }
}
