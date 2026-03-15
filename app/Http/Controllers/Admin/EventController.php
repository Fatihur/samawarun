<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DistanceCategory;
use App\Models\Event;
use App\Models\EventGallery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class EventController extends Controller
{
    public function index(): View
    {
        return view("admin.events.index");
    }

    public function data(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = Event::query()->latest("date");

        // Handle DataTables global search
        $searchValue = $request->input('search.value');
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue): void {
                $q->where("name", "like", "%{$searchValue}%")
                  ->orWhere("event_code", "like", "%{$searchValue}%")
                  ->orWhere("location", "like", "%{$searchValue}%");
            });
        }

        return DataTables::of($query)
            ->addColumn("event_code_formatted", function (Event $event): string {
                return '<span class="font-mono text-xs font-bold text-slate-500">' . e($event->event_code) . "</span>";
            })
            ->addColumn("name_formatted", function (Event $event): string {
                return '<span class="font-semibold text-slate-800">' . e($event->name) . "</span>";
            })
            ->addColumn("date_formatted", function (Event $event): string {
                return $event->date ? $event->date->format("d M Y") : "-";
            })
            ->addColumn("status_label", function (Event $event): string {
                if ($event->is_active) {
                    return '<span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">Aktif</span>';
                }
                return '<span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-500">Nonaktif</span>';
            })
            ->addColumn("actions", function (Event $event): string {
                // SVG Icons
                $editSvg = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>';
                $trashSvg = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>';

                $actions = '<div class="flex items-center gap-2">';
                $actions .= '<a href="' . route("admin.events.edit", $event) . '" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600 transition-colors hover:bg-blue-100 hover:text-blue-800" title="Edit">' . $editSvg . '</a>';
                $actions .= '<form action="' . route("admin.events.destroy", $event) . '" method="POST" onsubmit="return confirm(\'Hapus event ini?\')" data-loading-title="Menghapus event" data-loading-message="Event sedang dihapus, mohon tunggu...">';
                $actions .= csrf_field() . method_field("DELETE");
                $actions .= '<button type="submit" data-loading-label="Menghapus..." class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-500 transition-colors hover:bg-red-100 hover:text-red-700" title="Hapus">' . $trashSvg . '</button></form>';
                $actions .= "</div>";
                return $actions;
            })
            ->rawColumns(["event_code_formatted", "name_formatted", "status_label", "actions"])
            ->make(true);
    }

    public function create(): View
    {
        return view("admin.events.create", [
            "event" => new Event(),
            "distanceCategories" => DistanceCategory::where("is_active", true)
                ->orderBy("name")
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateEvent($request);

        if ($request->hasFile("poster")) {
            $validated["poster"] = $request
                ->file("poster")
                ->store("event-posters", "public");
        }

        $validated["event_code"] = $this->generateEventCode($validated["date"]);

        // Remove gallery-related fields from event data
        $eventData = $validated;
        unset($eventData['gallery_images'], $eventData['delete_galleries']);

        $event = Event::create($eventData);

        $event
            ->distanceCategories()
            ->sync($this->buildDistanceCategoryPayload($request));

        // Handle gallery image uploads
        $this->syncGalleryImages($request, $event);

        return redirect()
            ->route("admin.events.index")
            ->with("success", "Event berhasil ditambahkan.");
    }

    public function edit(Event $event): View
    {
        return view("admin.events.edit", [
            "event" => $event->load(["distanceCategories", "galleries"]),
            "distanceCategories" => DistanceCategory::where("is_active", true)
                ->orderBy("name")
                ->get(),
        ]);
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $validated = $this->validateEvent($request, $event);

        if ($request->hasFile("poster")) {
            if ($event->poster) {
                Storage::disk("public")->delete($event->poster);
            }
            $validated["poster"] = $request
                ->file("poster")
                ->store("event-posters", "public");
        }

        // Remove gallery-related fields from event data
        $eventData = $validated;
        unset($eventData['gallery_images'], $eventData['delete_galleries']);

        $event->update($eventData);

        $event
            ->distanceCategories()
            ->sync($this->buildDistanceCategoryPayload($request));

        // Handle gallery image uploads and deletions
        $this->syncGalleryImages($request, $event);

        return redirect()
            ->route("admin.events.index")
            ->with("success", "Event berhasil diperbarui.");
    }

    public function destroy(Event $event): RedirectResponse
    {
        if ($event->participants()->exists()) {
            return back()->with(
                "error",
                "Event tidak bisa dihapus karena sudah memiliki peserta terdaftar.",
            );
        }

        if ($event->poster) {
            Storage::disk("public")->delete($event->poster);
        }

        $event->delete();

        return redirect()
            ->route("admin.events.index")
            ->with("success", "Event berhasil dihapus.");
    }

    private function validateEvent(
        Request $request,
        ?Event $event = null,
    ): array {
        $validated = $request->validate(
            [
                "name" => ["required", "string", "max:255"],
                "poster" => ["nullable", "image", "max:2048"],
                "description" => ["required", "string"],
                "date" => ["required", "date"],
                "start_time" => ["nullable", "date_format:H:i"],
                "registration_deadline" => ["nullable", "date"],
                "location" => ["required", "string", "max:255"],
                "price" => ["nullable", "numeric", "min:0"],
                "contact" => ["nullable", "string", "max:255"],
                "bank_account" => ["nullable", "string", "max:255"],
                "is_active" => ["nullable", "boolean"],
                "gallery_images" => ["nullable", "array"],
                "gallery_images.*" => ["image", "mimes:jpg,jpeg,png,webp", "max:2048"],
                "delete_galleries" => ["nullable", "array"],
                "delete_galleries.*" => ["exists:event_galleries,id"],
                "distance_categories" => ["required", "array", "min:1"],
                "distance_categories.*" => ["exists:distance_categories,id"],
                "category_prices" => ["required", "array"],
                "category_prices.*" => ["nullable", "numeric", "min:0"],
            ],
            [
                "name.required" => "Nama event wajib diisi.",
                "distance_categories.required" =>
                    "Pilih minimal satu kategori jarak.",
            ],
        );

        foreach ($request->input("distance_categories", []) as $categoryId) {
            $price = $request->input("category_prices." . $categoryId);

            if ($price === null || $price === "") {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "category_prices." .
                    $categoryId => "Harga wajib diisi untuk setiap kategori jarak yang dipilih.",
                ]);
            }
        }

        $validated["is_active"] = $request->boolean("is_active");
        $validated["price"] = $this->resolveBasePrice($request);

        // Remove contact from validation since it's being removed from the form
        unset($validated['contact']);

        return $validated;
    }

    private function buildDistanceCategoryPayload(Request $request): array
    {
        $payload = [];

        foreach ($request->input("distance_categories", []) as $categoryId) {
            $payload[(int) $categoryId] = [
                "price" => (float) $request->input(
                    "category_prices." . $categoryId,
                ),
            ];
        }

        return $payload;
    }

    private function resolveBasePrice(Request $request): float
    {
        $selectedPrices = collect($request->input("distance_categories", []))
            ->map(
                fn($categoryId) => $request->input(
                    "category_prices." . $categoryId,
                ),
            )
            ->filter(fn($price) => $price !== null && $price !== "")
            ->map(fn($price): float => (float) $price);

        return (float) ($selectedPrices->min() ?? 0);
    }

    private function generateEventCode(string $date): string
    {
        $prefix = "EVT" . date("ymd", strtotime($date));
        $latestCode = Event::query()
            ->where("event_code", "like", $prefix . "%")
            ->orderByDesc("event_code")
            ->value("event_code");

        $nextNumber = $latestCode ? ((int) substr($latestCode, -3)) + 1 : 1;

        return $prefix . str_pad((string) $nextNumber, 3, "0", STR_PAD_LEFT);
    }

    private function syncGalleryImages(Request $request, Event $event): void
    {
        // Handle deletions
        if ($request->has('delete_galleries')) {
            $galleriesToDelete = EventGallery::where('event_id', $event->id)
                ->whereIn('id', $request->input('delete_galleries'))
                ->get();

            foreach ($galleriesToDelete as $gallery) {
                if ($gallery->image_path) {
                    Storage::disk('public')->delete($gallery->image_path);
                }
                $gallery->delete();
            }
        }

        // Handle new uploads
        if ($request->hasFile('gallery_images')) {
            $maxOrder = EventGallery::where('event_id', $event->id)->max('sort_order') ?? 0;

            foreach ($request->file('gallery_images') as $index => $image) {
                $path = $image->store('event-galleries', 'public');
                EventGallery::create([
                    'event_id' => $event->id,
                    'image_path' => $path,
                    'sort_order' => $maxOrder + $index + 1,
                ]);
            }
        }
    }
}
