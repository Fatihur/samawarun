<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BibScanController extends Controller
{
    public function index(Request $request): View
    {
        $selectedEventId = old('event_id', $request->query('event_id'));
        $bibNumber = '';
        $participant = null;

        if ($selectedEventId && $request->filled('bib_number')) {
            $validated = $request->validate([
                'event_id' => ['required', 'exists:events,id'],
                'bib_number' => ['required', 'string', 'max:30', 'regex:/^[A-Za-z0-9-]+$/'],
            ], [
                'event_id.required' => 'Pilih event terlebih dahulu.',
                'bib_number.required' => 'Nomor BIB wajib diisi.',
                'bib_number.regex' => 'Nomor BIB hanya boleh berisi huruf, angka, dan tanda hubung.',
            ]);

            $selectedEventId = $validated['event_id'];
            $bibNumber = strtoupper(trim((string) $validated['bib_number']));

            $participant = Participant::query()
                ->with('event')
                ->where('event_id', (int) $selectedEventId)
                ->where('bib_number', $bibNumber)
                ->first();
        }

        return view('admin.bib-scan.index', [
            'events' => Event::query()->orderByDesc('date')->get(),
            'selectedEvent' => $selectedEventId
                ? Event::query()->find($selectedEventId)
                : null,
            'bibNumber' => $bibNumber,
            'participant' => $participant,
            'lookupAttempted' => $selectedEventId && $request->filled('bib_number'),
        ]);
    }

    public function kiosk(Request $request): View
    {
        $request->validate([
            'event_id' => ['required', 'exists:events,id'],
        ]);

        $event = Event::findOrFail($request->query('event_id'));

        return view('admin.bib-scan.kiosk', [
            'event' => $event,
        ]);
    }

    public function kioskLookup(Request $request)
    {
        $validated = $request->validate([
            'event_id' => ['required', 'exists:events,id'],
            'bib_number' => ['required', 'string', 'max:30', 'regex:/^[A-Za-z0-9-]+$/'],
        ]);

        $bibNumber = strtoupper(trim((string) $validated['bib_number']));

        $participant = Participant::query()
            ->with('event')
            ->where('event_id', (int) $validated['event_id'])
            ->where('bib_number', $bibNumber)
            ->first();

        if (!$participant) {
            return response()->json(['found' => false, 'bib_number' => $bibNumber]);
        }

        return response()->json([
            'found' => true,
            'name' => $participant->name,
            'bib_number' => $participant->bib_number,
            'status' => $participant->status === Participant::STATUS_VERIFIED
                ? 'Verified'
                : $participant->workflow_status_label,
            'distance_category' => $participant->distance_category,
            'jersey_size' => $participant->jersey_size,
            'email' => $participant->email,
            'phone' => $participant->phone,
            'emergency_contact' => $participant->emergency_contact_display,
            'event_name' => $participant->event?->name ?? '-',
            'finish_time' => $participant->formatted_race_duration ?? 'Belum tercatat',
        ]);
    }
}
