<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DistanceCategory;
use App\Models\Event;
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

    public function data(): \Illuminate\Http\JsonResponse
    {
        $events = Event::query()->latest("date");

        return DataTables::of($events)
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
                $actions = '<div class="flex items-center gap-2">';
                $actions .= '<a href="' . route("admin.events.edit", $event) . '" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600 transition-colors hover:bg-blue-100 hover:text-blue-800" title="Edit"><x-heroicon-o-pencil-square class="h-4 w-4" /></a>';
                $actions .= '<form action="' . route("admin.events.destroy", $event) . '" method="POST" onsubmit="return confirm(\'Hapus event ini?\')" data-loading-title="Menghapus event" data-loading-message="Event sedang dihapus, mohon tunggu...">';
                $actions .= csrf_field() . method_field("DELETE");
                $actions .= '<button type="submit" data-loading-label="Menghapus..." class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-500 transition-colors hover:bg-red-100 hover:text-red-700" title="Hapus"><x-heroicon-o-trash class="h-4 w-4" /></button></form>';
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
        $event = Event::create($validated);

        $event
            ->distanceCategories()
            ->sync($this->buildDistanceCategoryPayload($request));

        return redirect()
            ->route("admin.events.index")
            ->with("success", "Event berhasil ditambahkan.");
    }

    public function edit(Event $event): View
    {
        return view("admin.events.edit", [
            "event" => $event->load("distanceCategories"),
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

        $event->update($validated);

        $event
            ->distanceCategories()
            ->sync($this->buildDistanceCategoryPayload($request));

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
}
