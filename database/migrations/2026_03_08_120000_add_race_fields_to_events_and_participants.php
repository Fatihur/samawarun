<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->time('start_time')->nullable()->after('date');
        });

        Schema::table('participants', function (Blueprint $table) {
            $table->timestamp('race_finished_at')->nullable()->after('status');
            $table->unsignedInteger('race_duration_seconds')->nullable()->after('race_finished_at');
            $table->index(['event_id', 'race_finished_at']);
        });
    }

    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropIndex(['event_id', 'race_finished_at']);
            $table->dropColumn(['race_finished_at', 'race_duration_seconds']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('start_time');
        });
    }
};
