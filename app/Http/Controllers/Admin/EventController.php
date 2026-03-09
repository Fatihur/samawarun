<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DistanceCategory;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(): View
    {
        return view('admin.events.index', [
            'events' => Event::query()->latest('date')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.events.create', [
            'event' => new Event(),
            'distanceCategories' => DistanceCategory::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateEvent($request);

        if ($request->hasFile('poster')) {
            $validated['poster'] = $request->file('poster')->store('event-posters', 'public');
        }

        $validated['event_code'] = $this->generateEventCode($validated['date']);
        $event = Event::create($validated);

        if ($request->has('distance_categories')) {
            $event->distanceCategories()->attach($request->distance_categories);
        }

        return redirect()->route('admin.events.index')->with('success', 'Event berhasil ditambahkan.');
    }

    public function edit(Event $event): View
    {
        return view('admin.events.edit', [
            'event' => $event->load('distanceCategories'),
            'distanceCategories' => DistanceCategory::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $validated = $this->validateEvent($request, $event);

        if ($request->hasFile('poster')) {
            $validated['poster'] = $request->file('poster')->store('event-posters', 'public');
        }

        $event->update($validated);
        
        $event->distanceCategories()->sync($request->distance_categories ?? []);

        return redirect()->route('admin.events.index')->with('success', 'Event berhasil diperbarui.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $event->delete();

        return redirect()->route('admin.events.index')->with('success', 'Event berhasil dihapus.');
    }

    private function validateEvent(Request $request, ?Event $event = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'poster' => ['nullable', 'image', 'max:2048'],
            'description' => ['required', 'string'],
            'date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'registration_deadline' => ['nullable', 'date'],
            'location' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'contact' => ['nullable', 'string', 'max:255'],
            'bank_account' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'distance_categories' => ['nullable', 'array'],
            'distance_categories.*' => ['exists:distance_categories,id'],
        ], [
            'name.required' => 'Nama event wajib diisi.',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }

    private function generateEventCode(string $date): string
    {
        $prefix = 'EVT'.date('ymd', strtotime($date));
        $latestCode = Event::query()
            ->where('event_code', 'like', $prefix.'%')
            ->orderByDesc('event_code')
            ->value('event_code');

        $nextNumber = $latestCode
            ? ((int) substr($latestCode, -3)) + 1
            : 1;

        return $prefix.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
