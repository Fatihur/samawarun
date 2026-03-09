<?php

namespace Database\Seeders;

use App\Models\DistanceCategory;
use App\Models\Event;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $categories = DistanceCategory::query()->whereIn('name', Event::DISTANCES)->get()->keyBy('name');

        $events = [
            [
                'event_code' => 'SRN260301',
                'name' => 'Samawa Run Maret 2026',
                'description' => 'Event Samawa Run pada 1 Maret 2026 untuk kategori 5K, 7K, dan 10K.',
                'date' => '2026-03-01',
                'start_time' => '06:00',
                'registration_deadline' => '2026-02-25 23:59:00',
                'location' => 'Lapangan Pahlawan Sumbawa',
                'price' => 150000,
                'contact' => 'Panitia Samawa Run',
                'bank_account' => 'BCA 1234567890 a.n. Samawa Run',
                'is_active' => true,
                'distances' => [Event::DISTANCE_5K, Event::DISTANCE_7K, Event::DISTANCE_10K],
            ],
            [
                'event_code' => 'SRN001',
                'name' => 'Samawa Run Opening Race',
                'description' => 'Event perdana komunitas Samawa Run untuk kategori 5K, 7K, dan 10K.',
                'date' => now()->addWeeks(4)->toDateString(),
                'start_time' => '06:00',
                'registration_deadline' => now()->addWeeks(3)->setTime(23, 59, 0),
                'location' => 'Lapangan Pahlawan Sumbawa',
                'price' => 125000,
                'contact' => 'Panitia Samawa Run',
                'bank_account' => 'BCA 1234567890 a.n. Samawa Run',
                'is_active' => true,
                'distances' => [Event::DISTANCE_5K, Event::DISTANCE_7K, Event::DISTANCE_10K],
            ],
            [
                'event_code' => 'SRN002',
                'name' => 'Samawa Run City Night Run',
                'description' => 'Race malam spesial Samawa Run dengan atmosfer kota dan kategori favorit pelari.',
                'date' => now()->addWeeks(8)->toDateString(),
                'start_time' => '19:00',
                'registration_deadline' => now()->addWeeks(7)->setTime(23, 59, 0),
                'location' => 'Taman Kota Sumbawa',
                'price' => 150000,
                'contact' => 'Panitia Samawa Run',
                'bank_account' => 'BCA 1234567890 a.n. Samawa Run',
                'is_active' => true,
                'distances' => [Event::DISTANCE_5K, Event::DISTANCE_10K],
            ],
        ];

        foreach ($events as $payload) {
            $distanceNames = $payload['distances'];
            unset($payload['distances']);

            $event = Event::query()->updateOrCreate(
                ['event_code' => $payload['event_code']],
                $payload,
            );

            $event->distanceCategories()->sync(
                collect($distanceNames)
                    ->map(fn (string $distance): ?int => $categories->get($distance)?->id)
                    ->filter()
                    ->values()
                    ->all(),
            );
        }
    }
}
