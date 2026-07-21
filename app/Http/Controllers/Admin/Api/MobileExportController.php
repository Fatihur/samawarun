<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Participant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileExportController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_id' => ['required', 'exists:events,id'],
        ]);

        $event = Event::query()
            ->with('distanceCategories')
            ->findOrFail($validated['event_id']);

        $participants = Participant::query()
            ->where('event_id', $event->id)
            ->where('status', Participant::STATUS_VERIFIED)
            ->whereNotNull('bib_number')
            ->get()
            ->map(fn (Participant $p) => [
                'id' => $p->id,
                'bib_number' => $p->bib_number,
                'name' => $p->name,
                'gender' => $p->gender,
                'distance_category' => $p->distance_category,
                'jersey_size' => $p->jersey_size,
            ]);

        $categories = $event->distanceCategories
            ->map(fn ($cat) => [
                'name' => $cat->name,
                'price' => $cat->pivot->price ?? $cat->price,
                'quota' => $cat->pivot->quota ?? null,
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'event' => [
                    'id' => $event->id,
                    'event_code' => $event->event_code,
                    'name' => $event->name,
                    'date' => $event->date?->format('Y-m-d'),
                    'start_time' => $event->start_time?->format('H:i:s'),
                    'location' => $event->location,
                ],
                'categories' => $categories,
                'participants' => $participants,
            ],
        ]);
    }
}
