<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Participant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use App\Notifications\NewParticipantNotification;
use App\Notifications\ParticipantRegistrationThankYouNotification;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function create(Event $event): View
    {
        abort_unless($event->is_active, 404);
        abort_unless($event->isRegistrationOpen(), 404);

        // Ensure slug exists for route generation
        if (empty($event->slug)) {
            $event->slug = $event->generateSlug();
            $event->saveQuietly();
        }

        return view("public.registrations.create", [
            "event" => $event->load("distanceCategories"),
            "categoryInfo" => $event->distanceCategories->mapWithKeys(function ($category) use ($event) {
                $categoryName = strtoupper($category->name);
                $remaining = $event->getRemainingQuotaForCategory($categoryName);
                
                return [
                    $categoryName => [
                        'price' => (int) round((float) ($category->pivot?->price ?? $event->price ?? 0)),
                        'quota' => $category->pivot?->quota,
                        'remaining' => $remaining,
                        'is_full' => $remaining !== null && $remaining <= 0,
                        'registered_count' => $event->getRegisteredCountForCategory($categoryName),
                    ]
                ];
            })->toArray(),
        ]);
    }

    public function store(Request $request, Event $event): RedirectResponse
    {
        abort_unless($event->is_active, 404);

        // Ensure slug exists for route generation
        if (empty($event->slug)) {
            $event->slug = $event->generateSlug();
            $event->saveQuietly();
        }

        if (!$event->isRegistrationOpen()) {
            return redirect()
                ->route("events.show", $event)
                ->with("error", "Pendaftaran untuk event ini sudah ditutup.");
        }

        $allowedDistances = $event->distanceCategories
            ->pluck("name")
            ->map(fn(string $name): string => strtoupper($name))
            ->values()
            ->toArray();

        $validated = $request->validate(
            [
                "name" => ["required", "string", "max:255"],
                "birth_date" => ["required", "date", "before:today"],
                "gender" => ["required", Rule::in(["male", "female"])],
                "nik" => [
                    "required",
                    "string",
                    "size:16",
                    Rule::unique("participants")
                        ->where(
                            fn($q) => $q
                                ->where("event_id", $event->id)
                                ->where(
                                    "workflow_status",
                                    "!=",
                                    \App\Models\Participant::WORKFLOW_REGISTRATION_REJECTED,
                                ),
                        ),
                ],
                "phone" => ["required", "string", "max:255"],
                "email" => [
                    "required",
                    "email",
                    "max:255",
                    Rule::unique("participants")->where(
                        fn($q) => $q
                            ->where("event_id", $event->id)
                            ->where(
                                "workflow_status",
                                "!=",
                                \App\Models\Participant::WORKFLOW_REGISTRATION_REJECTED,
                            ),
                    ),
                ],
                "address" => ["required", "string", "max:1000"],
                "distance_category" => [
                    "required",
                    function (
                        string $attribute,
                        mixed $value,
                        \Closure $fail,
                    ) use ($allowedDistances, $event): void {
                        if (
                            !in_array(
                                strtoupper((string) $value),
                                $allowedDistances,
                                true,
                            )
                        ) {
                            $fail("Kategori jarak yang dipilih tidak valid.");
                        }

                        if ($event->isCategoryFull(strtoupper((string) $value))) {
                            $fail("Kuota untuk kategori jarak ini sudah penuh. Silakan pilih kategori lain.");
                        }
                    },
                ],
                "jersey_size" => [
                    "required",
                    Rule::in(["2XS", "XS", "S", "M", "L", "XL", "XXL"]),
                ],
                "emergency_contact_name" => ["required", "string", "max:255"],
                "emergency_contact_phone" => ["required", "string", "max:255"],
                "emergency_contact_relationship" => [
                    "required",
                    Rule::in(Participant::EMERGENCY_RELATIONSHIPS),
                ],
            ],
            [
                "nik.unique" => "NIK Anda sudah terdaftar pada event ini.",
                "email.unique" => "Email Anda sudah terdaftar pada event ini.",
            ],
        );

        $validated["event_id"] = $event->id;
        $validated["distance_category"] = strtoupper(
            (string) $validated["distance_category"],
        );
        $validated["transfer_proof"] = null;
        $validated["status"] = Participant::STATUS_PENDING;
        $validated["workflow_status"] = Participant::WORKFLOW_SUBMITTED;

        $participant = Participant::create($validated);

        $participant->notify(
            new ParticipantRegistrationThankYouNotification($participant),
        );

        // Notify all admins
        $admins = User::where("is_admin", true)->get();
        Notification::send(
            $admins,
            new NewParticipantNotification($participant),
        );

        return redirect()
            ->route("events.show", $event)
            ->with(
                "success",
                "Pendaftaran berhasil dikirim. Status saat ini: Pending Verifikasi.",
            );
    }
}
