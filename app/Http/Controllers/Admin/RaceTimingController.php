<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Participant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class RaceTimingController extends Controller
{
    public function index(Request $request): View
    {
        $selectedEventId = old('event_id', $request->query('event_id'));

        return view('admin.race-timing.index', [
            'events' => Event::query()->orderByDesc('date')->get(),
            'selectedEvent' => $selectedEventId
                ? Event::query()->find($selectedEventId)
                : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'event_id' => ['required', 'exists:events,id'],
            'bib_number' => ['required', 'string', 'max:30'],
        ], [
            'event_id.required' => 'Pilih event terlebih dahulu.',
            'bib_number.required' => 'Nomor BIB wajib diisi.',
        ]);

        $event = Event::query()->findOrFail($validated['event_id']);

        if (! $event->start_time) {
            return back()->withInput()->with('error', 'Event ini belum memiliki jam mulai. Atur jam mulai event terlebih dahulu.');
        }

        $participant = Participant::query()
            ->with('event')
            ->where('event_id', $event->id)
            ->where('bib_number', strtoupper(trim((string) $validated['bib_number'])))
            ->first();

        if (! $participant) {
            return back()->withInput()->with('error', 'Peserta dengan nomor BIB tersebut tidak ditemukan pada event yang dipilih.');
        }

        if ($participant->status !== Participant::STATUS_VERIFIED) {
            return back()->withInput()->with('error', 'Peserta belum diverifikasi sehingga waktu race belum bisa dicatat.');
        }

        $startedAt = Carbon::parse($event->date->format('Y-m-d').' '.$event->start_time->format('H:i:s'));
        $finishedAt = now();

        if ($finishedAt->lessThan($startedAt)) {
            return back()->withInput()->with('error', 'Waktu finish tidak valid karena lebih awal dari jam mulai event.');
        }

        $participant->update([
            'race_finished_at' => $finishedAt,
            'race_duration_seconds' => $startedAt->diffInSeconds($finishedAt),
        ]);

        return redirect()
            ->route('admin.race-timing.index', ['event_id' => $event->id])
            ->with('success', 'Waktu finish peserta berhasil dicatat.')
            ->with('timing_result', [
                'event_name' => $participant->event?->name,
                'bib_number' => $participant->bib_number,
                'name' => $participant->name,
                'distance_category' => $participant->distance_category,
                'finish_time' => $participant->fresh()->race_finished_at?->format('d M Y H:i:s'),
                'duration' => $participant->fresh()->formatted_race_duration,
            ]);
    }
}
