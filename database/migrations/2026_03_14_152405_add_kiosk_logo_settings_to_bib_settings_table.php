<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bib_settings', function (Blueprint $table) {
            $table->json('kiosk_header_logos')->nullable()->after('background_image_path');
            $table->json('kiosk_footer_logos')->nullable()->after('kiosk_header_logos');
            $table->string('kiosk_sponsor_text', 100)->nullable()->after('kiosk_footer_logos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bib_settings', function (Blueprint $table) {
            $table->dropColumn(['kiosk_header_logos', 'kiosk_footer_logos', 'kiosk_sponsor_text']);
        });
    }
};
