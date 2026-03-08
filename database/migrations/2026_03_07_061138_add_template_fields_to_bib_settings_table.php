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
            $table->string('template_title', 60)->default('Nomor Dada');
            $table->string('footer_text', 255)->default('Nomor dada resmi peserta. Dokumen ini bukan nota/struk pembayaran.');
            $table->string('primary_color', 7)->default('#0f172a');
            $table->string('accent_color', 7)->default('#cbd5e1');
            $table->string('text_color', 7)->default('#0f172a');
            $table->string('meta_text_color', 7)->default('#334155');
            $table->unsignedSmallInteger('bib_font_size')->default(108);
            $table->unsignedSmallInteger('name_font_size')->default(22);
            $table->boolean('show_event_date')->default(true);
            $table->boolean('show_event_location')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bib_settings', function (Blueprint $table) {
            $table->dropColumn([
                'template_title',
                'footer_text',
                'primary_color',
                'accent_color',
                'text_color',
                'meta_text_color',
                'bib_font_size',
                'name_font_size',
                'show_event_date',
                'show_event_location',
            ]);
        });
    }
};
