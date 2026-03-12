<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('distance_category_event', function (Blueprint $table) {
            $table->decimal('price', 12, 2)->nullable()->after('distance_category_id');
        });

        DB::table('distance_category_event')
            ->join('events', 'events.id', '=', 'distance_category_event.event_id')
            ->update([
                'distance_category_event.price' => DB::raw('events.price'),
            ]);
    }

    public function down(): void
    {
        Schema::table('distance_category_event', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};
