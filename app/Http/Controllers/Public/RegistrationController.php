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
            ->intersect(Event::DISTANCES)
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
                    "max:32",
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
                "ktp_file" => [
                    "required",
                    "file",
                    "mimes:jpg,jpeg,png,pdf",
                    "max:2048",
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
                    ) use ($allowedDistances): void {
                        if (
                            !in_array(
                                strtoupper((string) $value),
                                $allowedDistances,
                                true,
                            )
                        ) {
                            $fail("Kategori jarak yang dipilih tidak valid.");
                        }
                    },
                ],
                "jersey_size" => [
                    "required",
                    Rule::in(["S", "M", "L", "XL", "XXL"]),
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
        $validated["ktp_file"] = $request
            ->file("ktp_file")
            ->store("participants/ktp", "public");
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
