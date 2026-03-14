<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BibSetting;
use App\Models\DistanceCategory;
use App\Models\Event;
use App\Models\Participant;
use App\Notifications\ParticipantPaymentRejectedNotification;
use App\Notifications\ParticipantRegistrationApprovedNotification;
use App\Notifications\ParticipantRejectedNotification;
use App\Notifications\ParticipantVerifiedNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class ParticipantController extends Controller
{
    public function index(Request $request): View
    {
        $participants = Participant::query()
            ->with("event")
            ->when($request->filled("event_id"), function (Builder $query) use (
                $request,
            ): void {
                $query->where("event_id", $request->integer("event_id"));
            })
            ->when($request->filled("status"), function (Builder $query) use (
                $request,
            ): void {
                $selectedStatus = $request->string("status")->value();

                if (
                    in_array(
                        $selectedStatus,
                        [
                            Participant::STATUS_PENDING,
                            Participant::STATUS_VERIFIED,
                            Participant::STATUS_REJECTED,
                        ],
                        true,
                    )
                ) {
                    $query->where("status", $selectedStatus);

                    return;
                }

                $query->where("workflow_status", $selectedStatus);
            })
            ->when($request->filled("search"), function (Builder $query) use (
                $request,
            ): void {
                $search = trim($request->string("search")->value());
                $query->where(function (Builder $inner) use ($search): void {
                    $inner
                        ->where("name", "like", "%{$search}%")
                        ->orWhere("email", "like", "%{$search}%")
                        ->orWhere("bib_number", "like", "%{$search}%")
                        ->orWhere("phone", "like", "%{$search}%");
                });
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view("admin.participants.index", [
            "participants" => $participants,
            "events" => Event::query()->orderBy("date")->get(),
        ]);
    }

    public function show(Participant $participant): View
    {
        return view("admin.participants.show", [
            "participant" => $participant->load("event"),
        ]);
    }

    public function verify(Participant $participant): RedirectResponse
    {
        if (
            $participant->workflow_status ===
            Participant::WORKFLOW_APPROVED_WAITING_PAYMENT
        ) {
            return back()->with(
                "success",
                "Pendaftaran peserta sudah disetujui dan menunggu pembayaran.",
            );
        }

        DB::transaction(function () use ($participant): void {
            $locked = Participant::query()
                ->lockForUpdate()
                ->findOrFail($participant->id);

            if (
                $locked->workflow_status ===
                Participant::WORKFLOW_APPROVED_WAITING_PAYMENT
            ) {
                return;
            }

            $locked->status = Participant::STATUS_PENDING;
            $locked->workflow_status =
                Participant::WORKFLOW_APPROVED_WAITING_PAYMENT;
            $locked->registration_reviewed_at = now();
            $locked->issuePaymentToken();
        });

        $participant->refresh()->load("event");
        $participant->notify(
            new ParticipantRegistrationApprovedNotification($participant),
        );

        return back()->with(
            "success",
            "Pendaftaran peserta disetujui. Link pembayaran telah dikirim ke email peserta.",
        );
    }

    public function reject(Participant $participant): RedirectResponse
    {
        $participant->update([
            "status" => Participant::STATUS_REJECTED,
            "workflow_status" => Participant::WORKFLOW_REGISTRATION_REJECTED,
            "registration_reviewed_at" => now(),
            "bib_number" => null,
        ]);

        $participant->refresh()->load("event");
        $participant->notify(new ParticipantRejectedNotification($participant));

        return back()->with("success", "Peserta berhasil ditolak.");
    }

    public function approvePayment(Participant $participant): RedirectResponse
    {
        if ($participant->workflow_status === Participant::WORKFLOW_COMPLETED) {
            return back()->with(
                "success",
                "Pembayaran peserta sudah disetujui sebelumnya.",
            );
        }

        DB::transaction(function () use ($participant): void {
            $locked = Participant::query()
                ->lockForUpdate()
                ->findOrFail($participant->id);

            $locked->status = Participant::STATUS_VERIFIED;
            $locked->workflow_status = Participant::WORKFLOW_COMPLETED;
            $locked->payment_reviewed_at = now();
            $locked->bib_number = $this->buildBibNumber($locked);
            $locked->save();
        });

        $participant->refresh()->load("event");
        $participant->notify(new ParticipantVerifiedNotification($participant));

        return back()->with(
            "success",
            "Pembayaran disetujui. Bukti pendaftaran dan BIB telah dikirim ke email peserta.",
        );
    }

    public function rejectPayment(Participant $participant): RedirectResponse
    {
        $participant->issuePaymentToken();

        $participant->update([
            "status" => Participant::STATUS_PENDING,
            "workflow_status" => Participant::WORKFLOW_PAYMENT_REJECTED,
            "payment_reviewed_at" => now(),
        ]);

        $participant->refresh()->load("event");
        $participant->notify(
            new ParticipantPaymentRejectedNotification($participant),
        );

        return back()->with(
            "success",
            "Pembayaran ditolak. Peserta telah menerima email untuk upload ulang bukti pembayaran.",
        );
    }

    public function export(Request $request): StreamedResponse
    {
        $fileName = "participants-" . now()->format("Ymd-His") . ".csv";

        $participants = Participant::query()
            ->with("event")
            ->when($request->filled("event_id"), function (Builder $query) use (
                $request,
            ): void {
                $query->where("event_id", $request->integer("event_id"));
            })
            ->when($request->filled("status"), function (Builder $query) use (
                $request,
            ): void {
                $query->where("status", $request->string("status")->value());
            })
            ->when($request->filled("search"), function (Builder $query) use (
                $request,
            ): void {
                $search = trim($request->string("search")->value());
                $query->where(function (Builder $inner) use ($search): void {
                    $inner
                        ->where("name", "like", "%{$search}%")
                        ->orWhere("email", "like", "%{$search}%")
                        ->orWhere("bib_number", "like", "%{$search}%")
                        ->orWhere("phone", "like", "%{$search}%");
                });
            })
            ->latest()
            ->get();

        return response()->streamDownload(
            function () use ($participants): void {
                $stream = fopen("php://output", "w");

                fputcsv($stream, [
                    "ID",
                    "Event",
                    "Bib Number",
                    "Nama",
                    "Email",
                    "Phone",
                    "Emergency Relationship",
                    "Emergency Name",
                    "Emergency Phone",
                    "Distance",
                    "Jersey",
                    "Status",
                ]);

                foreach ($participants as $participant) {
                    fputcsv($stream, [
                        $participant->id,
                        $participant->event?->name,
                        $participant->bib_number,
                        $participant->name,
                        $participant->email,
                        $participant->phone,
                        $participant->emergency_contact_relationship_label,
                        $participant->emergency_contact_name,
                        $participant->emergency_contact_phone,
                        $participant->distance_category,
                        $participant->jersey_size,
                        $participant->status,
                    ]);
                }

                fclose($stream);
            },
            $fileName,
            [
                "Content-Type" => "text/csv",
            ],
        );
    }

    public function exportPdf(Request $request): Response
    {
        $fileName = "participants-" . now()->format("Ymd-His") . ".pdf";

        $participants = Participant::query()
            ->with("event")
            ->when($request->filled("event_id"), function (Builder $query) use (
                $request,
            ): void {
                $query->where("event_id", $request->integer("event_id"));
            })
            ->when($request->filled("status"), function (Builder $query) use (
                $request,
            ): void {
                $query->where("status", $request->string("status")->value());
            })
            ->when($request->filled("search"), function (Builder $query) use (
                $request,
            ): void {
                $search = trim($request->string("search")->value());
                $query->where(function (Builder $inner) use ($search): void {
                    $inner
                        ->where("name", "like", "%{$search}%")
                        ->orWhere("email", "like", "%{$search}%")
                        ->orWhere("bib_number", "like", "%{$search}%")
                        ->orWhere("phone", "like", "%{$search}%");
                });
            })
            ->latest()
            ->get();

        $pdf = app("dompdf.wrapper");

        return $pdf
            ->loadView("admin.participants.report-pdf", [
                "participants" => $participants,
            ])
            ->setPaper("A4", "landscape")
            ->download($fileName);
    }

    public function exportIdCard(
        Participant $participant,
    ): Response|RedirectResponse {
        if ($participant->status !== Participant::STATUS_VERIFIED) {
            return back()->with(
                "error",
                "Nomor dada hanya bisa dibuat untuk peserta yang sudah diverifikasi.",
            );
        }

        $participant->load("event");
        $setting = BibSetting::current();

        $fileName =
            "nomor-dada-" .
            Str::slug($participant->name) .
            "-" .
            $participant->id .
            ".pdf";

        $pdf = app("dompdf.wrapper");

        return $pdf
            ->loadView("admin.participants.id-card", [
                "participants" => collect([$participant]),
                "setting" => $setting,
            ])
            ->setPaper("a5", "landscape")
            ->download($fileName);
    }

    public function exportIdCardBulk(
        Request $request,
    ): Response|RedirectResponse {
        $ids = collect($request->input("participant_ids", []))
            ->map(fn(mixed $id): int => (int) $id)
            ->filter(fn(int $id): bool => $id > 0)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return back()->with(
                "error",
                "Pilih minimal satu peserta untuk export nomor dada.",
            );
        }

        $participants = Participant::query()
            ->with("event")
            ->whereIn("id", $ids)
            ->where("status", Participant::STATUS_VERIFIED)
            ->orderBy("bib_number")
            ->get();

        if ($participants->isEmpty()) {
            return back()->with(
                "error",
                "Peserta yang dipilih belum terverifikasi.",
            );
        }

        $pdf = app("dompdf.wrapper");
        $fileName = "nomor-dada-bulk-" . now()->format("Ymd-His") . ".pdf";
        $setting = BibSetting::current();

        return $pdf
            ->loadView("admin.participants.id-card", [
                "participants" => $participants,
                "setting" => $setting,
            ])
            ->setPaper("a5", "landscape")
            ->download($fileName);
    }

    private function buildBibNumber(Participant $participant): string
    {
        $settings = BibSetting::current();

        $categoryId = DistanceCategory::where(
            "name",
            $participant->distance_category,
        )->value("id");

        $prefix =
            $categoryId && isset($settings->category_prefixes[$categoryId])
                ? $settings->category_prefixes[$categoryId]
                : substr((string) $participant->distance_category, 0, 1);

        $startNumber =
            $categoryId && isset($settings->category_start_numbers[$categoryId])
                ? $settings->category_start_numbers[$categoryId]
                : 1;

        $count = Participant::query()
            ->where("event_id", $participant->event_id)
            ->where("distance_category", $participant->distance_category)
            ->whereNotNull("bib_number")
            ->count();

        $sequence = $startNumber + $count;

        return $prefix .
            str_pad(
                (string) $sequence,
                $settings->number_padding,
                "0",
                STR_PAD_LEFT,
            );
    }
}
