<?php

namespace Database\Seeders;

use App\Models\DistanceCategory;
use App\Models\Event;
use Illuminate\Database\Seeder;

class DistanceCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (Event::DISTANCES as $distance) {
            DistanceCategory::query()->updateOrCreate(
                ['name' => $distance],
                ['is_active' => true],
            );
        }
    }
}
