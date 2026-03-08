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
        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('bib_number')->nullable();
            $table->string('name');
            $table->date('birth_date');
            $table->enum('gender', ['male', 'female']);
            $table->string('nik', 32);
            $table->string('ktp_file');
            $table->string('phone');
            $table->string('email');
            $table->text('address');
            $table->enum('distance_category', ['5K', '7K', '10K']);
            $table->enum('jersey_size', ['S', 'M', 'L', 'XL', 'XXL']);
            $table->string('emergency_contact');
            $table->string('transfer_proof');
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending')->index();
            $table->timestamps();

            $table->index(['event_id', 'distance_category']);
            $table->unique(['event_id', 'bib_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
