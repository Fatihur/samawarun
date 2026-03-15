<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(): View
    {
        return view('public.events.index', [
            'events' => Event::query()
                ->where('is_active', true)
                ->with('distanceCategories')
                ->orderBy('date')
                ->paginate(12),
        ]);
    }

    public function show(Event $event): View
    {
        abort_unless($event->is_active, 404);

        return view('public.events.show', [
            'event' => $event->load(['distanceCategories', 'galleries' => fn ($q) => $q->orderBy('sort_order')]),
            'isRegistrationOpen' => $event->isRegistrationOpen(),
        ]);
    }
}
