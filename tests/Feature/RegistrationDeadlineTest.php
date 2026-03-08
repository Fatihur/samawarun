<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationDeadlineTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_form_is_not_accessible_after_deadline(): void
    {
        $event = Event::create([
            'event_code' => 'DEAD001',
            'name' => 'Deadline Race',
            'description' => 'Test event.',
            'date' => now()->addWeek()->toDateString(),
            'registration_deadline' => now()->subMinute(),
            'location' => 'Sumbawa',
            'price' => 100000,
            'contact' => '08123',
            'bank_account' => 'BCA',
            'is_active' => true,
        ]);

        $this->get(route('registrations.create', $event))->assertNotFound();
    }

    public function test_registration_store_redirects_when_deadline_has_passed(): void
    {
        $event = Event::create([
            'event_code' => 'DEAD002',
            'name' => 'Deadline Race 2',
            'description' => 'Test event.',
            'date' => now()->addWeek()->toDateString(),
            'registration_deadline' => now()->subMinute(),
            'location' => 'Sumbawa',
            'price' => 100000,
            'contact' => '08123',
            'bank_account' => 'BCA',
            'is_active' => true,
        ]);

        $response = $this->post(route('registrations.store', $event), []);

        $response->assertRedirect(route('events.show', $event));
        $response->assertSessionHas('error');
    }
}
