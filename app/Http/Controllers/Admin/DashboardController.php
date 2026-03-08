<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Participant;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'eventCount' => Event::count(),
            'participantCount' => Participant::count(),
            'pendingCount' => Participant::where('status', Participant::STATUS_PENDING)->count(),
            'verifiedCount' => Participant::where('status', Participant::STATUS_VERIFIED)->count(),
        ]);
    }
}
