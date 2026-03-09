<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificate_templates', function (Blueprint $table): void {
            $table->string('background_image_path')->nullable()->after('name');
            $table->json('text_elements')->nullable()->after('background_image_path');
            $table->string('orientation', 20)->default('landscape')->after('text_elements');
        });

        Schema::table('certificate_templates', function (Blueprint $table): void {
            $table->dropColumn(['template_path', 'original_filename']);
        });
    }

    public function down(): void
    {
        Schema::table('certificate_templates', function (Blueprint $table): void {
            $table->string('template_path')->after('name');
            $table->string('original_filename')->after('template_path');
        });

        Schema::table('certificate_templates', function (Blueprint $table): void {
            $table->dropColumn(['background_image_path', 'text_elements', 'orientation']);
        });
    }
};
