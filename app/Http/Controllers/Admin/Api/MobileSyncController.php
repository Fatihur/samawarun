<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Participant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileSyncController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_code' => ['required', 'string'],
            'results' => ['required', 'array'],
            'results.*.bib_number' => ['required', 'string'],
            'results.*.race_finished_at' => ['required', 'string'],
            'results.*.race_duration_seconds' => ['required', 'integer'],
        ]);

        $event = Event::query()
            ->where('event_code', $validated['event_code'])
            ->first();

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event tidak ditemukan',
            ], 404);
        }

        $syncedCount = 0;
        $skippedCount = 0;

        foreach ($validated['results'] as $result) {
            $participant = Participant::query()
                ->where('event_id', $event->id)
                ->where('bib_number', $result['bib_number'])
                ->first();

            if (!$participant) {
                $skippedCount++;
                continue;
            }

            $participant->update([
                'race_finished_at' => $result['race_finished_at'],
                'race_duration_seconds' => $result['race_duration_seconds'],
            ]);

            $syncedCount++;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'synced_count' => $syncedCount,
                'skipped_count' => $skippedCount,
            ],
        ]);
    }
}
