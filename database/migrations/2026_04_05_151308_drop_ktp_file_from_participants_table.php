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
        if (Schema::hasColumn('participants', 'ktp_file')) {
            Schema::table('participants', function (Blueprint $table) {
                $table->dropColumn('ktp_file');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('participants', 'ktp_file')) {
            Schema::table('participants', function (Blueprint $table) {
                $table->string('ktp_file')->nullable();
            });
        }
    }
};
