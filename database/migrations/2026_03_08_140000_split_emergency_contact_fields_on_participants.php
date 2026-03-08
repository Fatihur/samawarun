<?php

use App\Models\Participant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table): void {
            $table->string('emergency_contact_name')->nullable()->after('jersey_size');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            $table->string('emergency_contact_relationship')->nullable()->after('emergency_contact_phone');
        });

        DB::table('participants')
            ->select(['id', 'emergency_contact'])
            ->orderBy('id')
            ->chunkById(100, function ($participants): void {
                foreach ($participants as $participant) {
                    $raw = trim((string) $participant->emergency_contact);
                    [$name, $phone] = array_pad(preg_split('/\s+-\s+/', $raw, 2) ?: [], 2, null);

                    DB::table('participants')
                        ->where('id', $participant->id)
                        ->update([
                            'emergency_contact_name' => $name ?: $raw,
                            'emergency_contact_phone' => $phone ?: '-',
                            'emergency_contact_relationship' => Participant::EMERGENCY_RELATIONSHIP_OTHER_FAMILY,
                        ]);
                }
            });

        Schema::table('participants', function (Blueprint $table): void {
            $table->string('emergency_contact_name')->nullable(false)->change();
            $table->string('emergency_contact_phone')->nullable(false)->change();
            $table->string('emergency_contact_relationship')->nullable(false)->change();
            $table->dropColumn('emergency_contact');
        });
    }

    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table): void {
            $table->string('emergency_contact')->nullable()->after('jersey_size');
        });

        DB::table('participants')
            ->select([
                'id',
                'emergency_contact_name',
                'emergency_contact_phone',
                'emergency_contact_relationship',
            ])
            ->orderBy('id')
            ->chunkById(100, function ($participants): void {
                foreach ($participants as $participant) {
                    $relationship = match ($participant->emergency_contact_relationship) {
                        Participant::EMERGENCY_RELATIONSHIP_FATHER => 'Ayah',
                        Participant::EMERGENCY_RELATIONSHIP_MOTHER => 'Ibu',
                        Participant::EMERGENCY_RELATIONSHIP_HUSBAND => 'Suami',
                        Participant::EMERGENCY_RELATIONSHIP_WIFE => 'Istri',
                        Participant::EMERGENCY_RELATIONSHIP_CHILD => 'Anak',
                        default => 'Keluarga Lain',
                    };

                    DB::table('participants')
                        ->where('id', $participant->id)
                        ->update([
                            'emergency_contact' => trim($relationship.' - '.$participant->emergency_contact_name.' - '.$participant->emergency_contact_phone, ' -'),
                        ]);
                }
            });

        Schema::table('participants', function (Blueprint $table): void {
            $table->string('emergency_contact')->nullable(false)->change();
            $table->dropColumn([
                'emergency_contact_name',
                'emergency_contact_phone',
                'emergency_contact_relationship',
            ]);
        });
    }
};
