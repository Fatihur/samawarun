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
            // Add XS and 2XS to jersey_size enum
            $table->enum('jersey_size', ['2XS', 'XS', 'S', 'M', 'L', 'XL', 'XXL'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->enum('jersey_size', ['S', 'M', 'L', 'XL', 'XXL'])->change();
        });
    }
};
