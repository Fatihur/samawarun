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
        Schema::create('bib_settings', function (Blueprint $table) {
            $table->id();
            $table->string('prefix_5k', 10)->default('5');
            $table->string('prefix_7k', 10)->default('7');
            $table->string('prefix_10k', 10)->default('10');
            $table->unsignedTinyInteger('number_padding')->default(3);
            $table->unsignedInteger('start_number_5k')->default(1);
            $table->unsignedInteger('start_number_7k')->default(1);
            $table->unsignedInteger('start_number_10k')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bib_settings');
    }
};
