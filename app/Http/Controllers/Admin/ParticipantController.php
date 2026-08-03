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
use App\Notifications\PaymentReminderNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Yajra\DataTables\DataTables;

class ParticipantController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.participants.index', [
            'events' => Event::query()->orderBy('date')->get(),
            'distanceCategories' => Participant::query()
                ->distinct()
                ->pluck('distance_category')
                ->filter()
                ->sort()
                ->values(),
        ]);
    }

    public function create(): View
    {
        $events = Event::query()
            ->where('is_active', true)
            ->with('distanceCategories')
            ->orderBy('date', 'desc')
            ->get();

        $eventsCategories = $events->mapWithKeys(function (Event $event) {
            return [
                $event->id => $event->distanceCategories->map(function ($cat) use ($event) {
                    $name = strtoupper($cat->name);

                    return [
                        'name' => $name,
                        'quota' => $cat->pivot?->quota,
                        'remaining' => $event->getRemainingQuotaForCategory($name),
                        'is_full' => $event->isCategoryFull($name),
                    ];
                }),
            ];
        });

        return view('admin.participants.create', [
            'events' => $events,
            'eventsCategories' => $eventsCategories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'event_id' => ['required', 'exists:events,id'],
        ]);

        $event = Event::with('distanceCategories')->findOrFail($request->input('event_id'));

        $allowedDistances = $event->distanceCategories
            ->pluck('name')
            ->map(fn (string $name): string => strtoupper($name))
            ->values()
            ->toArray();

        $validated = $request->validate([
            'event_id' => ['required', 'exists:events,id'],
            'name' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date', 'before:today'],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'nik' => [
                'required', 'string', 'size:16',
                Rule::unique('participants')->where(
                    fn ($q) => $q->where('event_id', $event->id)
                        ->where('workflow_status', '!=', Participant::WORKFLOW_REGISTRATION_REJECTED)
                ),
            ],
            'phone' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('participants')->where(
                    fn ($q) => $q->where('event_id', $event->id)
                        ->where('workflow_status', '!=', Participant::WORKFLOW_REGISTRATION_REJECTED)
                ),
            ],
            'address' => ['required', 'string', 'max:1000'],
            'distance_category' => [
                'required',
                function (string $attribute, mixed $value, \Closure $fail) use ($allowedDistances, $event): void {
                    if (! in_array(strtoupper((string) $value), $allowedDistances, true)) {
                        $fail('Kategori jarak yang dipilih tidak valid untuk event ini.');
                    }
                    if ($event->isCategoryFull(strtoupper((string) $value))) {
                        $fail('Kuota untuk kategori jarak ini sudah penuh.');
                    }
                },
            ],
            'jersey_size' => ['required', Rule::in(['2XS', 'XS', 'S', 'M', 'L', 'XL', 'XXL'])],
            'emergency_contact_name' => ['required', 'string', 'max:255'],
            'emergency_contact_phone' => ['required', 'string', 'max:255'],
            'emergency_contact_relationship' => ['required', Rule::in(Participant::EMERGENCY_RELATIONSHIPS)],
        ], [
            'nik.unique' => 'NIK sudah terdaftar pada event ini.',
            'email.unique' => 'Email sudah terdaftar pada event ini.',
        ]);

        $validated['distance_category'] = strtoupper((string) $validated['distance_category']);
        $validated['transfer_proof'] = null;
        $validated['status'] = Participant::STATUS_VERIFIED;
        $validated['workflow_status'] = Participant::WORKFLOW_COMPLETED;
        $validated['payment_reviewed_at'] = now();

        $participant = DB::transaction(function () use ($validated): Participant {
            $participant = Participant::create($validated);
            $participant->bib_number = $this->buildBibNumber($participant);
            $participant->save();

            return $participant;
        });

        return redirect()
            ->route('admin.participants.index')
            ->with('success', "Peserta {$participant->name} berhasil ditambahkan dengan BIB {$participant->bib_number}.");
    }

    public function data(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = Participant::query()
            ->with('event')
            ->select('participants.*')
            ->latest()
            ->when($request->filled('event_id'), function (Builder $query) use ($request): void {
                $query->where('event_id', $request->integer('event_id'));
            })
            ->when($request->filled('distance_category'), function (Builder $query) use ($request): void {
                $query->where('distance_category', $request->string('distance_category')->value());
            })
            ->when($request->filled('status'), function (Builder $query) use ($request): void {
                $selectedStatus = $request->string('status')->value();

                if (in_array($selectedStatus, [
                    Participant::STATUS_PENDING,
                    Participant::STATUS_VERIFIED,
                    Participant::STATUS_REJECTED,
                ], true)) {
                    $query->where('status', $selectedStatus);

                    return;
                }

                $query->where('workflow_status', $selectedStatus);
            });

        // Handle DataTables global search
        $searchValue = $request->input('search.value');
        if (! empty($searchValue)) {
            $query->where(function (Builder $q) use ($searchValue): void {
                $q->where('name', 'like', "%{$searchValue}%")
                    ->orWhere('email', 'like', "%{$searchValue}%")
                    ->orWhere('bib_number', 'like', "%{$searchValue}%")
                    ->orWhere('phone', 'like', "%{$searchValue}%")
                    ->orWhereHas('event', function ($q) use ($searchValue): void {
                        $q->where('name', 'like', "%{$searchValue}%");
                    });
            });
        }

        return DataTables::of($query)
            ->addColumn('select', function (Participant $participant): string {
                if ($participant->status === Participant::STATUS_VERIFIED) {
                    return '<input type="checkbox" name="participant_ids[]" value="'.$participant->id.'" form="bulk-bib-form" class="participant-select h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">';
                }

                return '<span class="pl-6">-</span>';
            })
            ->addColumn('name_email', function (Participant $participant): string {
                $age = $participant->birth_date ? $participant->birth_date->age : '-';

                return '<p class="font-bold text-slate-800">'.e($participant->name).' <span class="ml-1 inline-flex items-center rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-bold text-slate-600">'.$age.' th</span></p>'.
                       '<p class="text-xs text-slate-500">'.e($participant->email).'</p>';
            })
            ->addColumn('event_name', function (Participant $participant): string {
                return e($participant->event?->name ?? 'N/A');
            })
            ->addColumn('distance_badge', function (Participant $participant): string {
                return '<span class="inline-flex rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700">'.e($participant->distance_category).'</span>';
            })
            ->addColumn('bib_number_display', function (Participant $participant): string {
                return e($participant->bib_number ?? '-');
            })
            ->addColumn('status_label', function (Participant $participant): string {
                if ($participant->status === Participant::STATUS_VERIFIED) {
                    return '<span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 border border-emerald-200">Verified</span>';
                } elseif ($participant->status === Participant::STATUS_REJECTED) {
                    return '<span class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-1 text-xs font-bold text-red-700 border border-red-200">Rejected</span>';
                }

                $workflowLabel = e($participant->workflow_status_label);

                // Tambahkan hari menunggu pembayaran ke dalam label
                if ($participant->workflow_status === Participant::WORKFLOW_APPROVED_WAITING_PAYMENT && $participant->registration_reviewed_at) {
                    $daysWaiting = (int) $participant->registration_reviewed_at->diffInDays(now());
                    $workflowLabel .= ' • '.$daysWaiting.' hari';
                }

                return '<span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700 border border-amber-200">'.$workflowLabel.'</span>';
            })
            ->addColumn('actions', function (Participant $participant): string {
                // SVG Icons
                $eyeSvg = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>';
                $checkSvg = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>';
                $xMarkSvg = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>';
                $banknotesSvg = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V19.5M3.375 4.5c.381 0 .668-.284.745-.646.127-.621.468-1.193.984-1.593a4.488 4.488 0 012.62-.832c1.043 0 2.032.379 2.82 1.064l.157.138c.403.358.93.558 1.476.558h.186c.545 0 1.073-.2 1.476-.558l.157-.138a4.486 4.486 0 012.82-1.064 4.488 4.488 0 012.62.832c.516.4.857.972.984 1.593.077.362.364.646.745.646M3.375 4.5h15.75M3.375 4.5c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h15.75c.621 0 1.125-.504 1.125-1.125V5.625c0-.621-.504-1.125-1.125-1.125m-15 6.75h15m-15 0c0 .966.784 1.75 1.75 1.75h11.5a1.75 1.75 0 001.75-1.75m-15 0c0-.966.784-1.75 1.75-1.75h11.5a1.75 1.75 0 011.75 1.75" /></svg>';
                $xCircleSvg = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';

                $actions = '<div class="flex items-center gap-2">';
                $actions .= '<a href="'.route('admin.participants.show', $participant).'" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600 transition-colors hover:bg-brand-100 hover:text-brand-800" title="Detail">'.$eyeSvg.'</a>';

                if ($participant->workflow_status === Participant::WORKFLOW_SUBMITTED) {
                    $actions .= '<form action="'.route('admin.participants.verify', $participant).'" method="POST" onsubmit="return confirm(\'Verifikasi peserta ini?\')" data-loading-title="Memverifikasi peserta" data-loading-message="Status peserta sedang diperbarui dan nomor dada sedang disiapkan...">';
                    $actions .= csrf_field().method_field('PATCH');
                    $actions .= '<button type="submit" data-loading-label="Memverifikasi..." class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 transition-colors hover:bg-emerald-100 hover:text-emerald-800" title="Verify">'.$checkSvg.'</button></form>';

                    $actions .= '<form action="'.route('admin.participants.reject', $participant).'" method="POST" onsubmit="return confirm(\'Tolak peserta ini?\')" data-loading-title="Menolak pendaftaran" data-loading-message="Status peserta sedang diperbarui, mohon tunggu...">';
                    $actions .= csrf_field().method_field('PATCH');
                    $actions .= '<button type="submit" data-loading-label="Menolak..." class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-600 transition-colors hover:bg-red-100 hover:text-red-800" title="Reject">'.$xMarkSvg.'</button></form>';
                } elseif ($participant->workflow_status === Participant::WORKFLOW_PAYMENT_SUBMITTED) {
                    $actions .= '<form action="'.route('admin.participants.payment.approve', $participant).'" method="POST" onsubmit="return confirm(\'Setujui pembayaran peserta ini?\')" data-loading-title="Menyetujui pembayaran" data-loading-message="Status pembayaran sedang diperbarui dan nomor dada sedang disiapkan...">';
                    $actions .= csrf_field().method_field('PATCH');
                    $actions .= '<button type="submit" data-loading-label="Menyetujui..." class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 transition-colors hover:bg-emerald-100 hover:text-emerald-800" title="Approve Payment">'.$banknotesSvg.'</button></form>';

                    $actions .= '<form action="'.route('admin.participants.payment.reject', $participant).'" method="POST" onsubmit="return confirm(\'Tolak pembayaran peserta ini?\')" data-loading-title="Menolak pembayaran" data-loading-message="Status pembayaran sedang diperbarui dan link upload ulang sedang dikirim...">';
                    $actions .= csrf_field().method_field('PATCH');
                    $actions .= '<button type="submit" data-loading-label="Menolak..." class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-600 transition-colors hover:bg-red-100 hover:text-red-800" title="Reject Payment">'.$xCircleSvg.'</button></form>';
                }

                $actions .= '</div>';

                return $actions;
            })
            ->rawColumns(['select', 'name_email', 'distance_badge', 'status_label', 'actions'])
            ->make(true);
    }

    public function show(Participant $participant): View
    {
        return view('admin.participants.show', [
            'participant' => $participant->load('event'),
        ]);
    }

    public function verify(Participant $participant): RedirectResponse
    {
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

        $participant->refresh()->load('event');
        $participant->notify(
            new ParticipantRegistrationApprovedNotification($participant),
        );

        return back()->with(
            'success',
            'Pendaftaran peserta disetujui. Link pembayaran telah dikirim ke email peserta.',
        );
    }

    public function reject(Participant $participant): RedirectResponse
    {
        $participant->update([
            'status' => Participant::STATUS_REJECTED,
            'workflow_status' => Participant::WORKFLOW_REGISTRATION_REJECTED,
            'registration_reviewed_at' => now(),
            'bib_number' => null,
        ]);

        $participant->refresh()->load('event');
        $participant->notify(new ParticipantRejectedNotification($participant));

        return back()->with('success', 'Peserta berhasil ditolak.');
    }

    public function approvePayment(Participant $participant): RedirectResponse
    {
        if ($participant->workflow_status === Participant::WORKFLOW_COMPLETED) {
            return back()->with(
                'success',
                'Pembayaran peserta sudah disetujui sebelumnya.',
            );
        }

        DB::transaction(function () use ($participant): void {
            Participant::query()
                ->where('event_id', $participant->event_id)
                ->where('distance_category', $participant->distance_category)
                ->whereNotNull('bib_number')
                ->lockForUpdate()
                ->get();

            $locked = Participant::query()
                ->lockForUpdate()
                ->findOrFail($participant->id);

            $locked->status = Participant::STATUS_VERIFIED;
            $locked->workflow_status = Participant::WORKFLOW_COMPLETED;
            $locked->payment_reviewed_at = now();
            $locked->bib_number = $this->buildBibNumber($locked);
            $locked->save();
        });

        $participant->refresh()->load('event');
        $participant->notify(new ParticipantVerifiedNotification($participant));

        return back()->with(
            'success',
            'Pembayaran disetujui. Bukti pendaftaran dan BIB telah dikirim ke email peserta.',
        );
    }

    public function rejectPayment(Participant $participant): RedirectResponse
    {
        $participant->issuePaymentToken();

        $participant->update([
            'status' => Participant::STATUS_PENDING,
            'workflow_status' => Participant::WORKFLOW_PAYMENT_REJECTED,
            'payment_reviewed_at' => now(),
        ]);

        $participant->refresh()->load('event');
        $participant->notify(
            new ParticipantPaymentRejectedNotification($participant),
        );

        return back()->with(
            'success',
            'Pembayaran ditolak. Peserta telah menerima email untuk upload ulang bukti pembayaran.',
        );
    }

    public function destroy(Participant $participant): RedirectResponse
    {
        $participant->delete();

        return redirect()
            ->route('admin.participants.index')
            ->with('success', 'Peserta berhasil dihapus.');
    }

    public function export(Request $request): StreamedResponse
    {
        $fileName = 'participants-'.now()->format('Ymd-His').'.csv';

        $participants = Participant::query()
            ->with('event')
            ->when($request->filled('event_id'), function (Builder $query) use (
                $request,
            ): void {
                $query->where('event_id', $request->integer('event_id'));
            })
            ->when($request->filled('distance_category'), function (Builder $query) use (
                $request,
            ): void {
                $query->where('distance_category', $request->string('distance_category')->value());
            })
            ->when($request->filled('status'), function (Builder $query) use (
                $request,
            ): void {
                $query->where('status', $request->string('status')->value());
            })
            ->when($request->filled('search'), function (Builder $query) use (
                $request,
            ): void {
                $search = trim($request->string('search')->value());
                $query->where(function (Builder $inner) use ($search): void {
                    $inner
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('bib_number', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        return response()->streamDownload(
            function () use ($participants): void {
                $stream = fopen('php://output', 'w');

                fputcsv($stream, [
                    'ID',
                    'Event',
                    'Bib Number',
                    'Nama',
                    'Email',
                    'Phone',
                    'Emergency Relationship',
                    'Emergency Name',
                    'Emergency Phone',
                    'Distance',
                    'Jersey',
                    'Status',
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
                'Content-Type' => 'text/csv',
            ],
        );
    }

    public function exportPdf(Request $request): Response
    {
        $fileName = 'participants-'.now()->format('Ymd-His').'.pdf';

        $participants = Participant::query()
            ->with('event')
            ->when($request->filled('event_id'), function (Builder $query) use (
                $request,
            ): void {
                $query->where('event_id', $request->integer('event_id'));
            })
            ->when($request->filled('distance_category'), function (Builder $query) use (
                $request,
            ): void {
                $query->where('distance_category', $request->string('distance_category')->value());
            })
            ->when($request->filled('status'), function (Builder $query) use (
                $request,
            ): void {
                $query->where('status', $request->string('status')->value());
            })
            ->when($request->filled('search'), function (Builder $query) use (
                $request,
            ): void {
                $search = trim($request->string('search')->value());
                $query->where(function (Builder $inner) use ($search): void {
                    $inner
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('bib_number', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        $pdf = app('dompdf.wrapper');

        return $pdf
            ->loadView('admin.participants.report-pdf', [
                'participants' => $participants,
            ])
            ->setPaper('A4', 'landscape')
            ->download($fileName);
    }

    public function exportIdCard(
        Participant $participant,
    ): Response|RedirectResponse {
        if ($participant->status !== Participant::STATUS_VERIFIED) {
            return back()->with(
                'error',
                'Nomor dada hanya bisa dibuat untuk peserta yang sudah diverifikasi.',
            );
        }

        $participant->load('event');
        $setting = BibSetting::current();

        $fileName =
            'nomor-dada-'.
            Str::slug($participant->name).
            '-'.
            $participant->id.
            '.pdf';

        $pdf = app('dompdf.wrapper');
        $this->registerBibFonts($pdf);

        return $pdf
            ->loadView('admin.participants.id-card', [
                'participants' => collect([$participant]),
                'setting' => $setting,
            ])
            ->setPaper('a5', 'landscape')
            ->download($fileName);
    }

    public function exportIdCardBulk(
        Request $request,
    ): Response|RedirectResponse {
        $ids = collect($request->input('participant_ids', []))
            ->map(fn (mixed $id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return back()->with(
                'error',
                'Pilih minimal satu peserta untuk export nomor dada.',
            );
        }

        $participants = Participant::query()
            ->with('event')
            ->whereIn('id', $ids)
            ->where('status', Participant::STATUS_VERIFIED)
            ->orderBy('bib_number')
            ->get();

        if ($participants->isEmpty()) {
            return back()->with(
                'error',
                'Peserta yang dipilih belum terverifikasi.',
            );
        }

        $setting = BibSetting::current();
        $fileName = 'id-card-bulk-'.now()->format('Ymd-His').'.pdf';

        $pdf = app('dompdf.wrapper');
        $this->registerBibFonts($pdf);

        return $pdf
            ->loadView('admin.participants.id-card', [
                'participants' => $participants,
                'setting' => $setting,
            ])
            ->setPaper('a5', 'landscape')
            ->download($fileName);
    }

    private function registerBibFonts($pdf): void
    {
        $fontDir = storage_path('fonts');
        $dompdf = $pdf->getDomPDF();
        $fontMetrics = $dompdf->getFontMetrics();

        $fonts = [
            'poppins_normal.ttf' => ['family' => 'Poppins', 'style' => 'normal', 'weight' => 'normal'],
            'poppins_bold.ttf' => ['family' => 'Poppins', 'style' => 'normal', 'weight' => 'bold'],
        ];

        foreach ($fonts as $file => $meta) {
            $path = $fontDir . DIRECTORY_SEPARATOR . $file;
            if (file_exists($path)) {
                $fontMetrics->registerFont(
                    ['family' => $meta['family'], 'style' => $meta['style'], 'weight' => $meta['weight']],
                    $path
                );
            }
        }
    }

    private function buildBibNumber(Participant $participant): string
    {
        $settings = BibSetting::current();

        $categoryId = DistanceCategory::where(
            'name',
            $participant->distance_category,
        )->value('id');

        $prefix =
            $categoryId && isset($settings->category_prefixes[$categoryId])
                ? $settings->category_prefixes[$categoryId]
                : substr((string) $participant->distance_category, 0, 1);

        $startNumber =
            $categoryId && isset($settings->category_start_numbers[$categoryId])
                ? $settings->category_start_numbers[$categoryId]
                : 1;

        $existingBibs = Participant::query()
            ->where('event_id', $participant->event_id)
            ->where('distance_category', $participant->distance_category)
            ->whereNotNull('bib_number')
            ->where('bib_number', 'like', $prefix . '%')
            ->pluck('bib_number');

        $maxSequence = $existingBibs->map(function ($bib) use ($prefix) {
            $numericPart = substr((string) $bib, strlen((string) $prefix));

            return (int) $numericPart;
        })->max();

        $sequence = $maxSequence !== null ? ($maxSequence + 1) : $startNumber;

        return $prefix.
            str_pad(
                (string) $sequence,
                $settings->number_padding,
                '0',
                STR_PAD_LEFT,
            );
    }

    public function sendPaymentReminders(): RedirectResponse
    {
        $participants = Participant::query()
            ->where('workflow_status', Participant::WORKFLOW_APPROVED_WAITING_PAYMENT)
            ->whereNotNull('payment_token')
            ->get();

        if ($participants->isEmpty()) {
            return back()->with(
                'info',
                'Tidak ada peserta yang menunggu pembayaran saat ini.',
            );
        }

        $sentCount = 0;
        foreach ($participants as $participant) {
            $participant->notify(new PaymentReminderNotification($participant));
            $sentCount++;
        }

        return back()->with(
            'success',
            "Berhasil mengirim pengingat pembayaran ke {$sentCount} peserta.",
        );
    }
}
