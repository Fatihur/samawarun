<?php

namespace Tests\Feature;

use App\Models\DistanceCategory;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventCategoryPricingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_store_event_with_price_per_distance_category(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $fiveK = DistanceCategory::create(['name' => '5K', 'is_active' => true]);
        $tenK = DistanceCategory::create(['name' => '10K', 'is_active' => true]);

        $response = $this->actingAs($admin)->post(route('admin.events.store'), [
            'name' => 'Samawa Run Multi Price',
            'description' => 'Event dengan harga per kategori.',
            'date' => '2026-04-01',
            'start_time' => '06:00',
            'location' => 'Sumbawa',
            'contact' => '08123',
            'bank_account' => 'BCA',
            'is_active' => '1',
            'distance_categories' => [$fiveK->id, $tenK->id],
            'category_prices' => [
                $fiveK->id => 100000,
                $tenK->id => 150000,
            ],
        ]);

        $response->assertRedirect(route('admin.events.index'));

        $event = Event::query()
            ->with('distanceCategories')
            ->where('name', 'Samawa Run Multi Price')
            ->firstOrFail();

        $this->assertSame(100000.0, (float) $event->price);
        $this->assertSame(100000.0, (float) $event->distanceCategories->firstWhere('id', $fiveK->id)?->pivot?->price);
        $this->assertSame(150000.0, (float) $event->distanceCategories->firstWhere('id', $tenK->id)?->pivot?->price);
    }

    public function test_public_event_page_shows_category_based_pricing(): void
    {
        $event = Event::create([
            'event_code' => 'EVT260401001',
            'name' => 'Samawa Run Price Display',
            'description' => 'Event dengan harga per kategori.',
            'date' => '2026-04-01',
            'start_time' => '06:00',
            'location' => 'Sumbawa',
            'price' => 100000,
            'contact' => '08123',
            'bank_account' => 'BCA',
            'is_active' => true,
        ]);

        $fiveK = DistanceCategory::create(['name' => '5K', 'is_active' => true]);
        $tenK = DistanceCategory::create(['name' => '10K', 'is_active' => true]);

        $event->distanceCategories()->attach([
            $fiveK->id => ['price' => 100000],
            $tenK->id => ['price' => 150000],
        ]);

        $response = $this->get(route('events.show', $event));

        $response->assertOk();
        $response->assertSee('Mulai dari Rp 100.000');
        $response->assertSee('5K');
        $response->assertSee('10K');
        $response->assertSee('Rp 150.000');
    }
}
