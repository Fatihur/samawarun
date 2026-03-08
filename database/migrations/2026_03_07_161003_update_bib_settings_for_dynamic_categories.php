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
            $table->dropColumn([
                'prefix_5k',
                'prefix_7k',
                'prefix_10k',
                'start_number_5k',
                'start_number_7k',
                'start_number_10k',
            ]);

            $table->json('category_prefixes')->nullable()->after('number_padding');
            $table->json('category_start_numbers')->nullable()->after('category_prefixes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bib_settings', function (Blueprint $table) {
            $table->string('prefix_5k', 10)->default('5');
            $table->string('prefix_7k', 10)->default('7');
            $table->string('prefix_10k', 10)->default('10');
            $table->unsignedInteger('start_number_5k')->default(1);
            $table->unsignedInteger('start_number_7k')->default(1);
            $table->unsignedInteger('start_number_10k')->default(1);

            $table->dropColumn(['category_prefixes', 'category_start_numbers']);
        });
    }
};
