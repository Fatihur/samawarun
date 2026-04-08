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
        Schema::table('participants', function (Blueprint $table) {
            // Change distance_category from ENUM to VARCHAR to allow any category name
            $table->string('distance_category')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            // Revert back to ENUM (note: this might fail if there's data that doesn't match)
            $table->enum('distance_category', ['5K', '7K', '10K'])->change();
        });
    }
};
