<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Event;
use App\Models\Gallery;
use App\Models\Participant;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('public.home', [
            'contact' => Contact::first(),
            'upcomingEvents' => Event::query()
                ->where('is_active', true)
                ->whereDate('date', '>=', now()->toDateString())
                ->orderBy('date')
                ->limit(6)
                ->get(),
            'participantCount' => Participant::count(),
            'eventCount' => Event::count(),
            'galleries' => Gallery::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }
}

