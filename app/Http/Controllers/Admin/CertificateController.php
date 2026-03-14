<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CertificateTemplate;
use App\Models\Event;
use App\Models\Participant;
use App\Services\CertificateTemplateService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class CertificateController extends Controller
{
    public function __construct(
        private readonly CertificateTemplateService $certificateTemplateService,
    ) {
    }

    public function index(Request $request): View
    {
        $activeTab = $request->string('tab')->value() ?: 'template';
        $events = Event::query()->with('certificateTemplate')->orderByDesc('date')->get();
        $selectedEventId = $request->integer('event_id') ?: $events->first()?->id;
        $selectedEvent = $selectedEventId
            ? $events->firstWhere('id', $selectedEventId) ?? Event::query()->with('certificateTemplate')->find($selectedEventId)
            : null;

        $template = $selectedEvent?->certificateTemplate;

        return view('admin.certificates.index', [
            'activeTab' => in_array($activeTab, ['template', 'generate'], true) ? $activeTab : 'template',
            'events' => $events,
            'selectedEvent' => $selectedEvent,
            'template' => $template,
            'supportedPlaceholders' => $this->certificateTemplateService->supportedPlaceholders(),
        ]);
    }

    public function data(Request $request): \Illuminate\Http\JsonResponse
    {
        $eventId = $request->integer('event_id');

        $query = Participant::query()
            ->with('event')
            ->when($eventId > 0, function (Builder $query) use ($eventId): void {
                $query->where('event_id', $eventId);
            })
            ->where('status', Participant::STATUS_VERIFIED)
            ->whereNotNull('race_finished_at')
            ->select('participants.*');

        return DataTables::of($query)
            ->addColumn('select', function (Participant $participant): string {
                return '<input type="checkbox" name="participant_ids[]" value="' . $participant->id . '" form="bulk-certificate-form" class="certificate-select h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">';
            })
            ->addColumn('name_email', function (Participant $participant): string {
                return '<p class="font-bold text-slate-800">' . e($participant->name) . '</p>' .
                       '<p class="text-xs text-slate-500">' . e($participant->email) . '</p>';
            })
            ->addColumn('bib_number_display', function (Participant $participant): string {
                return '<span class="font-mono font-bold text-slate-700">' . e($participant->bib_number ?? '-') . '</span>';
            })
            ->addColumn('distance_badge', function (Participant $participant): string {
                return '<span class="inline-flex rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700">' . e($participant->distance_category) . '</span>';
            })
            ->addColumn('finish_time_formatted', function (Participant $participant): string {
                return $participant->race_finished_at ? $participant->race_finished_at->format('d M Y H:i:s') : '-';
            })
            ->addColumn('duration_display', function (Participant $participant): string {
                return '<span class="font-mono font-bold text-slate-700">' . e($participant->formatted_race_duration ?? '-') . '</span>';
            })
            ->addColumn('actions', function (Participant $participant): string {
                return '<a href="' . route('admin.participants.certificate', $participant) . '" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-white transition-colors hover:bg-slate-700" title="Download PDF">
                            <x-heroicon-o-document-arrow-down class="h-4 w-4" />
                        </a>';
            })
            ->rawColumns(['select', 'name_email', 'bib_number_display', 'distance_badge', 'duration_display', 'actions'])
            ->make(true);
    }

    public function updateBackground(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'event_id' => ['required', 'exists:events,id'],
            'name' => ['required', 'string', 'max:150'],
            'background_image' => ['required', 'image', 'mimes:png,jpg,jpeg', 'max:10240'],
            'orientation' => ['required', 'in:landscape,portrait'],
        ], [
            'background_image.required' => 'Upload gambar background sertifikat.',
            'background_image.image' => 'File harus berupa gambar (PNG/JPG).',
            'background_image.mimes' => 'Hanya format PNG dan JPG yang didukung.',
        ]);

        $template = CertificateTemplate::query()->firstOrCreate(
            ['event_id' => $validated['event_id']],
            ['name' => $validated['name']]
        );

        $bgPath = $this->certificateTemplateService->storeBackgroundImage(
            $template,
            $request->file('background_image'),
        );

        $template->fill([
            'name' => $validated['name'],
            'background_image_path' => $bgPath,
            'orientation' => $validated['orientation'],
            'text_elements' => $template->text_elements ?? $template->getDefaultTextElements(),
        ])->save();

        return redirect()
            ->route('admin.certificates.index', ['tab' => 'template', 'event_id' => $validated['event_id']])
            ->with('success', 'Background sertifikat berhasil diupload. Atur posisi text di editor.');
    }

    public function saveElements(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_id' => ['required', 'exists:events,id'],
            'text_elements' => ['required', 'array'],
            'text_elements.*.placeholder' => ['required', 'string'],
            'text_elements.*.label' => ['required', 'string'],
            'text_elements.*.x' => ['required', 'numeric', 'min:0', 'max:100'],
            'text_elements.*.y' => ['required', 'numeric', 'min:0', 'max:100'],
            'text_elements.*.fontSize' => ['required', 'integer', 'min:8', 'max:120'],
            'text_elements.*.fontFamily' => ['sometimes', 'string'],
            'text_elements.*.fontWeight' => ['required', 'in:normal,bold'],
            'text_elements.*.fontStyle' => ['sometimes', 'in:normal,italic'],
            'text_elements.*.textDecoration' => ['sometimes', 'in:none,underline'],
            'text_elements.*.textTransform' => ['sometimes', 'in:none,uppercase,lowercase,capitalize'],
            'text_elements.*.color' => ['required', 'string'],
            'text_elements.*.textAlign' => ['required', 'in:left,center,right'],
            'text_elements.*.width' => ['required', 'numeric', 'min:5', 'max:100'],
        ]);

        $template = CertificateTemplate::query()->where('event_id', $validated['event_id'])->firstOrFail();
        $template->update(['text_elements' => $validated['text_elements']]);

        return response()->json(['success' => true, 'message' => 'Posisi text berhasil disimpan.']);
    }

    public function previewPdf(Request $request): Response
    {
        $eventId = $request->integer('event_id');
        $template = CertificateTemplate::query()->where('event_id', $eventId)->firstOrFail();

        if (! $template->background_image_path) {
            abort(404, 'Background image belum diupload.');
        }

        $sampleParticipant = Participant::query()
            ->with('event')
            ->where('event_id', $eventId)
            ->where('status', Participant::STATUS_VERIFIED)
            ->whereNotNull('race_finished_at')
            ->first();

        if (! $sampleParticipant) {
            $sampleParticipant = new Participant([
                'name' => 'Nama Peserta Contoh',
                'bib_number' => '10K-001',
                'email' => 'peserta@contoh.com',
                'phone' => '081234567890',
                'distance_category' => '10K',
                'race_finished_at' => now(),
                'race_duration_seconds' => 3661,
            ]);
            $sampleParticipant->setRelation('event', Event::find($eventId));
        }

        $pdf = $this->certificateTemplateService->generatePdf($template, $sampleParticipant);

        return $pdf->stream('preview-sertifikat.pdf');
    }

    public function downloadParticipant(Participant $participant): Response|RedirectResponse
    {
        $participant->load('event.certificateTemplate');

        $guard = $this->ensureParticipantCanReceiveCertificate($participant);

        if ($guard instanceof RedirectResponse) {
            return $guard;
        }

        $template = $participant->event->certificateTemplate;
        $pdf = $this->certificateTemplateService->generatePdf($template, $participant);
        $fileName = 'sertifikat-'.Str::slug($participant->name).'-'.$participant->id.'.pdf';

        return $pdf->download($fileName);
    }

    public function downloadBulk(Request $request): Response|RedirectResponse
    {
        $validated = $request->validate([
            'participant_ids' => ['required', 'array', 'min:1'],
            'participant_ids.*' => ['integer'],
        ], [
            'participant_ids.required' => 'Pilih minimal satu peserta untuk generate sertifikat.',
        ]);

        $participants = Participant::query()
            ->with('event.certificateTemplate')
            ->whereIn('id', $validated['participant_ids'])
            ->where('status', Participant::STATUS_VERIFIED)
            ->whereNotNull('race_finished_at')
            ->orderBy('name')
            ->get();

        if ($participants->isEmpty()) {
            return back()->with('error', 'Tidak ada peserta valid untuk bulk generate sertifikat.');
        }

        foreach ($participants as $participant) {
            $guard = $this->ensureParticipantCanReceiveCertificate($participant);

            if ($guard instanceof RedirectResponse) {
                return $guard;
            }
        }

        set_time_limit(300);

        $template = $participants->first()->event->certificateTemplate;
        $pdf = $this->certificateTemplateService->generateBulkPdf($template, $participants);
        $fileName = 'sertifikat-bulk-'.now()->format('Ymd-His').'.pdf';

        return $pdf->download($fileName);
    }

    private function ensureParticipantCanReceiveCertificate(Participant $participant): RedirectResponse|null
    {
        if ($participant->status !== Participant::STATUS_VERIFIED || ! $participant->race_finished_at) {
            return back()->with('error', 'Sertifikat hanya tersedia untuk peserta verified yang sudah menyelesaikan race.');
        }

        if (! $participant->event?->certificateTemplate?->background_image_path) {
            return back()->with('error', 'Template sertifikat untuk event peserta ini belum tersedia. Upload gambar background terlebih dahulu.');
        }

        if (! Storage::disk('public')->exists($participant->event->certificateTemplate->background_image_path)) {
            return back()->with('error', 'File background sertifikat tidak ditemukan di server. Upload ulang background untuk event ini.');
        }

        return null;
    }
}
