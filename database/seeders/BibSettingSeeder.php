<?php

namespace Database\Seeders;

use App\Models\BibSetting;
use App\Models\DistanceCategory;
use Illuminate\Database\Seeder;

class BibSettingSeeder extends Seeder
{
    public function run(): void
    {
        $categories = DistanceCategory::query()->orderBy('name')->get();

        $prefixes = [];
        $startNumbers = [];

        foreach ($categories as $category) {
            $name = strtoupper((string) $category->name);

            $prefixes[$category->id] = match ($name) {
                '5K' => '5A',
                '7K' => '7A',
                '10K' => '10A',
                default => substr($name, 0, 2),
            };

            $startNumbers[$category->id] = 1;
        }

        BibSetting::query()->updateOrCreate(
            ['id' => BibSetting::query()->value('id') ?? 1],
            array_merge(BibSetting::defaults(), [
                'category_prefixes' => $prefixes,
                'category_start_numbers' => $startNumbers,
                'number_padding' => 3,
                'template_title' => 'Nomor Dada Samawa Run',
                'footer_text' => 'Nomor dada resmi peserta Samawa Run. Harap dibawa saat race day.',
            ]),
        );
    }
}
