<?php

use App\Models\Participant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->string('transfer_proof')->nullable()->change();
            $table->string('workflow_status')->default(Participant::WORKFLOW_SUBMITTED)->after('status');
            $table->timestamp('registration_reviewed_at')->nullable()->after('workflow_status');
            $table->timestamp('payment_requested_at')->nullable()->after('registration_reviewed_at');
            $table->string('payment_token', 100)->nullable()->unique()->after('payment_requested_at');
            $table->timestamp('payment_token_expires_at')->nullable()->after('payment_token');
            $table->timestamp('payment_submitted_at')->nullable()->after('payment_token_expires_at');
            $table->timestamp('payment_reviewed_at')->nullable()->after('payment_submitted_at');
        });

        DB::table('participants')
            ->where('status', Participant::STATUS_PENDING)
            ->update(['workflow_status' => Participant::WORKFLOW_SUBMITTED]);

        DB::table('participants')
            ->where('status', Participant::STATUS_VERIFIED)
            ->update(['workflow_status' => Participant::WORKFLOW_COMPLETED]);

        DB::table('participants')
            ->where('status', Participant::STATUS_REJECTED)
            ->update(['workflow_status' => Participant::WORKFLOW_REGISTRATION_REJECTED]);
    }

    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropUnique(['payment_token']);
            $table->dropColumn([
                'workflow_status',
                'registration_reviewed_at',
                'payment_requested_at',
                'payment_token',
                'payment_token_expires_at',
                'payment_submitted_at',
                'payment_reviewed_at',
            ]);
            $table->string('transfer_proof')->nullable(false)->change();
        });
    }
};
