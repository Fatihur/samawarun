<?php

namespace Database\Seeders;

use App\Models\BibSetting;
use App\Models\DistanceCategory;
use App\Models\Event;
use App\Models\Participant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ParticipantSeeder extends Seeder
{
    public function run(): void
    {
        $settings = BibSetting::current();
        $padding = max(1, (int) $settings->number_padding);

        $events = Event::query()->with('distanceCategories')->get();

        foreach ($events as $event) {
            $distances = $event->distanceCategories
                ->pluck('name')
                ->map(fn (string $name): string => strtoupper($name))
                ->intersect(Event::DISTANCES)
                ->values()
                ->all();

            if ($distances === []) {
                $distances = Event::DISTANCES;
            }

            $targetTotal = match ($event->event_code) {
                'SRN260301' => 180,
                'SRN001' => 120,
                default => 80,
            };

            $currentCount = Participant::query()
                ->where('event_id', $event->id)
                ->count();

            $needToCreate = max(0, $targetTotal - $currentCount);

            $sequenceByDistance = [];

            foreach ($distances as $distance) {
                $categoryId = DistanceCategory::query()
                    ->whereRaw('upper(name) = ?', [$distance])
                    ->value('id');

                $prefix = $categoryId && isset($settings->category_prefixes[$categoryId])
                    ? (string) $settings->category_prefixes[$categoryId]
                    : substr($distance, 0, 1);

                $startNumber = $categoryId && isset($settings->category_start_numbers[$categoryId])
                    ? (int) $settings->category_start_numbers[$categoryId]
                    : 1;

                $existingBibCount = Participant::query()
                    ->where('event_id', $event->id)
                    ->where('distance_category', $distance)
                    ->whereNotNull('bib_number')
                    ->count();

                $sequenceByDistance[$distance] = [
                    'prefix' => $prefix,
                    'next' => $startNumber + $existingBibCount,
                ];
            }

            for ($i = 0; $i < $needToCreate; $i++) {
                $distance = fake()->randomElement($distances);
                $prefix = $sequenceByDistance[$distance]['prefix'];
                $number = $sequenceByDistance[$distance]['next'];

                $bibNumber = $prefix.str_pad((string) $number, $padding, '0', STR_PAD_LEFT);

                while (Participant::query()->where('event_id', $event->id)->where('bib_number', $bibNumber)->exists()) {
                    $number++;
                    $bibNumber = $prefix.str_pad((string) $number, $padding, '0', STR_PAD_LEFT);
                }

                $sequenceByDistance[$distance]['next'] = $number + 1;

                $startedAt = $event->date && $event->start_time
                    ? Carbon::parse($event->date->format('Y-m-d').' '.$event->start_time->format('H:i:s'))
                    : null;

                $shouldMarkAsFinished = $startedAt !== null
                    && $event->date?->isPast()
                    && fake()->boolean(65);

                $raceDurationSeconds = null;
                $raceFinishedAt = null;

                if ($shouldMarkAsFinished) {
                    $raceDurationSeconds = fake()->numberBetween(1500, 10800);
                    $raceFinishedAt = $startedAt->copy()->addSeconds($raceDurationSeconds);
                }

                Participant::query()->create([
                    'event_id' => $event->id,
                    'bib_number' => $bibNumber,
                    'name' => fake()->name(),
                    'birth_date' => fake()->dateTimeBetween('-55 years', '-17 years')->format('Y-m-d'),
                    'gender' => fake()->randomElement(['male', 'female']),
                    'nik' => fake()->numerify('################'),
                    'ktp_file' => 'participants/ktp/sample-'.$event->id.'-'.($i + 1).'.jpg',
                    'phone' => fake()->phoneNumber(),
                    'email' => fake()->unique()->safeEmail(),
                    'address' => fake()->address(),
                    'distance_category' => $distance,
                    'jersey_size' => fake()->randomElement(['S', 'M', 'L', 'XL', 'XXL']),
                    'emergency_contact_name' => fake()->name(),
                    'emergency_contact_phone' => fake()->phoneNumber(),
                    'emergency_contact_relationship' => fake()->randomElement(Participant::EMERGENCY_RELATIONSHIPS),
                    'transfer_proof' => 'participants/payments/sample-'.$event->id.'-'.($i + 1).'.jpg',
                    'status' => Participant::STATUS_VERIFIED,
                    'race_finished_at' => $raceFinishedAt,
                    'race_duration_seconds' => $raceDurationSeconds,
                ]);
            }

        }
    }
}
