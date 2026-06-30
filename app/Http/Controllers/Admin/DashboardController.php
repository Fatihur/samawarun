<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Participant;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $now = now();

        $upcomingEvents = Event::query()
            ->where('date', '>=', $now->copy()->subDay())
            ->where('is_active', true)
            ->withCount(['participants', 'participants as verified_count' => function ($q): void {
                $q->where('status', Participant::STATUS_VERIFIED);
            }])
            ->orderBy('date')
            ->limit(3)
            ->get();

        // Trend pendaftaran 7 hari terakhir
        $registrationTrend = Participant::query()
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', $now->copy()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        // Fill missing dates
        $trendData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i)->format('Y-m-d');
            $trendData[$date] = $registrationTrend[$date] ?? 0;
        }

        // Distribusi kategori jarak
        $categoryDistribution = Participant::query()
            ->select('distance_category', DB::raw('COUNT(*) as count'))
            ->groupBy('distance_category')
            ->orderByDesc('count')
            ->limit(5)
            ->pluck('count', 'distance_category')
            ->toArray();

        // Breakdown status workflow
        $workflowStatus = [
            'submitted' => Participant::where('workflow_status', Participant::WORKFLOW_SUBMITTED)->count(),
            'approved_waiting_payment' => Participant::where('workflow_status', Participant::WORKFLOW_APPROVED_WAITING_PAYMENT)->count(),
            'payment_submitted' => Participant::where('workflow_status', Participant::WORKFLOW_PAYMENT_SUBMITTED)->count(),
            'payment_rejected' => Participant::where('workflow_status', Participant::WORKFLOW_PAYMENT_REJECTED)->count(),
            'completed' => Participant::where('workflow_status', Participant::WORKFLOW_COMPLETED)->count(),
            'rejected' => Participant::where('workflow_status', Participant::WORKFLOW_REGISTRATION_REJECTED)->count(),
        ];

        // Peserta terbaru
        $recentParticipants = Participant::query()
            ->with('event')
            ->latest()
            ->limit(5)
            ->get();

        // Yang perlu review
        $needsReviewCount = Participant::whereIn('workflow_status', [
            Participant::WORKFLOW_SUBMITTED,
            Participant::WORKFLOW_PAYMENT_SUBMITTED,
        ])->count();

        return view('admin.dashboard', [
            'eventCount' => Event::count(),
            'participantCount' => Participant::count(),
            'pendingCount' => Participant::where('status', Participant::STATUS_PENDING)->count(),
            'verifiedCount' => Participant::where('status', Participant::STATUS_VERIFIED)->count(),
            'upcomingEvents' => $upcomingEvents,
            'trendData' => $trendData,
            'categoryDistribution' => $categoryDistribution,
            'workflowStatus' => $workflowStatus,
            'recentParticipants' => $recentParticipants,
            'needsReviewCount' => $needsReviewCount,
        ]);
    }
}
