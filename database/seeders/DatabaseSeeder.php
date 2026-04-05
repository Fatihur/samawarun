<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminEmail = env('ADMIN_EMAIL', 'admin@samawarun.test');
        $adminName = env('ADMIN_NAME', 'Samawa Admin');
        $adminPassword = env('ADMIN_PASSWORD', 'password');

        User::query()->updateOrCreate(
            ["email" => $adminEmail],
            [
                "name" => $adminName,
                "password" => Hash::make($adminPassword),
                "is_admin" => true,
            ],
        );

        Contact::query()->firstOrCreate(
            [],
            [
                "phone" => "0812-0000-0000",
                "whatsapp" => "0812-0000-0000",
                "email" => "hello@samawarun.id",
                "instagram" => "@samawarun",
                "facebook" => "samawarun",
                "tiktok" => "@samawarun",
                "address" => "Sumbawa, Nusa Tenggara Barat",
            ],
        );

        // $this->call([
        //     DistanceCategorySeeder::class,
        //     BibSettingSeeder::class,
        //     EventSeeder::class,
        //     ParticipantSeeder::class,
        // ]);
    }
}
