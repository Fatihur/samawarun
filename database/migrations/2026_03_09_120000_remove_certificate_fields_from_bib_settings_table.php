<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = [
            'certificate_template_path',
            'certificate_name_y',
            'certificate_meta_y',
            'certificate_badge_y',
            'certificate_footer_y',
        ];

        $existingColumns = array_values(array_filter($columns, fn (string $column): bool => Schema::hasColumn('bib_settings', $column)));

        if ($existingColumns !== []) {
            Schema::table('bib_settings', function (Blueprint $table) use ($existingColumns): void {
                $table->dropColumn($existingColumns);
            });
        }
    }

    public function down(): void
    {
        Schema::table('bib_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('bib_settings', 'certificate_template_path')) {
                $table->string('certificate_template_path')->nullable()->after('background_image_path');
            }

            if (! Schema::hasColumn('bib_settings', 'certificate_name_y')) {
                $table->unsignedTinyInteger('certificate_name_y')->default(38)->after('certificate_template_path');
            }

            if (! Schema::hasColumn('bib_settings', 'certificate_meta_y')) {
                $table->unsignedTinyInteger('certificate_meta_y')->default(56)->after('certificate_name_y');
            }

            if (! Schema::hasColumn('bib_settings', 'certificate_badge_y')) {
                $table->unsignedTinyInteger('certificate_badge_y')->default(70)->after('certificate_meta_y');
            }

            if (! Schema::hasColumn('bib_settings', 'certificate_footer_y')) {
                $table->unsignedTinyInteger('certificate_footer_y')->default(90)->after('certificate_badge_y');
            }
        });
    }
};
