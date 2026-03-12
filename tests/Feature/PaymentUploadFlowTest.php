<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentUploadFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_can_upload_payment_after_registration_is_approved(): void
    {
        Storage::fake('public');

        $event = Event::create([
            'event_code' => 'PAY001',
            'name' => 'Samawa Run Payment Flow',
            'description' => 'Event pembayaran.',
            'date' => now()->addWeek()->toDateString(),
            'location' => 'Sumbawa',
            'price' => 100000,
            'contact' => '08123',
            'bank_account' => 'BCA',
            'is_active' => true,
        ]);

        $participant = Participant::create([
            'event_id' => $event->id,
            'name' => 'Peserta Bayar',
            'birth_date' => '1998-01-20',
            'gender' => 'male',
            'nik' => '1234567890123456',
            'ktp_file' => 'participants/ktp/test.jpg',
            'phone' => '081234567890',
            'email' => 'bayar@example.com',
            'address' => 'Sumbawa',
            'distance_category' => '5K',
            'jersey_size' => 'L',
            'emergency_contact_name' => 'Ayah',
            'emergency_contact_phone' => '081111111111',
            'emergency_contact_relationship' => Participant::EMERGENCY_RELATIONSHIP_FATHER,
            'transfer_proof' => null,
            'status' => Participant::STATUS_PENDING,
            'workflow_status' => Participant::WORKFLOW_APPROVED_WAITING_PAYMENT,
            'payment_token' => 'test-payment-token',
            'payment_token_expires_at' => now()->addDay(),
        ]);

        $url = URL::temporarySignedRoute(
            'registrations.payment.store',
            now()->addDay(),
            ['participant' => $participant->id, 'token' => $participant->payment_token]
        );

        $response = $this->post($url, [
            'transfer_proof' => UploadedFile::fake()->image('payment.jpg'),
        ]);

        $response->assertRedirect(route('home'));

        $participant->refresh();

        $this->assertSame(Participant::WORKFLOW_PAYMENT_SUBMITTED, $participant->workflow_status);
        $this->assertNotNull($participant->transfer_proof);
    }
}
