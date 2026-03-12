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

        $event->distanceCategories()->sync($this->buildDistanceCategoryPayload($request));

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

        $event->distanceCategories()->sync($this->buildDistanceCategoryPayload($request));

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
            'price' => ['nullable', 'numeric', 'min:0'],
            'contact' => ['nullable', 'string', 'max:255'],
            'bank_account' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'distance_categories' => ['required', 'array', 'min:1'],
            'distance_categories.*' => ['exists:distance_categories,id'],
            'category_prices' => ['required', 'array'],
            'category_prices.*' => ['nullable', 'numeric', 'min:0'],
        ], [
            'name.required' => 'Nama event wajib diisi.',
            'distance_categories.required' => 'Pilih minimal satu kategori jarak.',
        ]);

        foreach ($request->input('distance_categories', []) as $categoryId) {
            $price = $request->input('category_prices.'.$categoryId);

            if ($price === null || $price === '') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'category_prices.'.$categoryId => 'Harga wajib diisi untuk setiap kategori jarak yang dipilih.',
                ]);
            }
        }

        $validated['is_active'] = $request->boolean('is_active');
        $validated['price'] = $this->resolveBasePrice($request);

        return $validated;
    }

    private function buildDistanceCategoryPayload(Request $request): array
    {
        $payload = [];

        foreach ($request->input('distance_categories', []) as $categoryId) {
            $payload[(int) $categoryId] = [
                'price' => (float) $request->input('category_prices.'.$categoryId),
            ];
        }

        return $payload;
    }

    private function resolveBasePrice(Request $request): float
    {
        $selectedPrices = collect($request->input('distance_categories', []))
            ->map(fn ($categoryId) => $request->input('category_prices.'.$categoryId))
            ->filter(fn ($price) => $price !== null && $price !== '')
            ->map(fn ($price): float => (float) $price);

        return (float) ($selectedPrices->min() ?? 0);
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
