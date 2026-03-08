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
            $table->string('background_image_path')->nullable()->after('show_event_location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bib_settings', function (Blueprint $table) {
            $table->dropColumn('background_image_path');
        });
    }
};
