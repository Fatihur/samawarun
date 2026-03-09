<?php

namespace Tests\Feature;

use App\Models\DistanceCategory;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEventCodeGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_event_store_auto_generates_event_code(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = DistanceCategory::create(['name' => '5K', 'is_active' => true]);

        $response = $this->actingAs($admin)->post(route('admin.events.store'), [
            'name' => 'Samawa Run Auto Code',
            'description' => 'Event dengan kode otomatis.',
            'date' => '2026-03-01',
            'start_time' => '06:00',
            'registration_deadline' => '2026-02-25 23:59',
            'location' => 'Sumbawa',
            'price' => 100000,
            'contact' => '08123',
            'bank_account' => 'BCA',
            'is_active' => '1',
            'distance_categories' => [$category->id],
        ]);

        $response->assertRedirect(route('admin.events.index'));

        $event = Event::query()->where('name', 'Samawa Run Auto Code')->first();

        $this->assertNotNull($event);
        $this->assertSame('EVT260301001', $event->event_code);
    }

    public function test_admin_event_store_increments_event_code_for_same_date(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = DistanceCategory::create(['name' => '5K', 'is_active' => true]);

        Event::create([
            'event_code' => 'EVT260301001',
            'name' => 'Existing Event',
            'description' => 'Event lama.',
            'date' => '2026-03-01',
            'start_time' => '06:00',
            'location' => 'Sumbawa',
            'price' => 100000,
            'contact' => '08123',
            'bank_account' => 'BCA',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.events.store'), [
            'name' => 'Second Event',
            'description' => 'Event kedua.',
            'date' => '2026-03-01',
            'start_time' => '07:00',
            'registration_deadline' => '2026-02-26 23:59',
            'location' => 'Sumbawa',
            'price' => 120000,
            'contact' => '08124',
            'bank_account' => 'BNI',
            'is_active' => '1',
            'distance_categories' => [$category->id],
        ]);

        $response->assertRedirect(route('admin.events.index'));

        $event = Event::query()->where('name', 'Second Event')->first();

        $this->assertNotNull($event);
        $this->assertSame('EVT260301002', $event->event_code);
    }
}
