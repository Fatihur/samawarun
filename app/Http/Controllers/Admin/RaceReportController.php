<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Participant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Yajra\DataTables\DataTables;

class RaceReportController extends Controller
{
    public function index(Request $request): View
    {
        $baseQuery = $this->buildQuery($request);

        return view('admin.race-reports.index', [
            'events' => Event::query()->orderByDesc('date')->get(),
            'recordedCount' => (clone $baseQuery)->whereNotNull('race_finished_at')->count(),
            'unrecordedCount' => (clone $baseQuery)->whereNull('race_finished_at')->count(),
        ]);
    }

    public function data(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = $this->buildQuery($request);

        // Handle DataTables global search
        $searchValue = $request->input('search.value');
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue): void {
                $q->where('name', 'like', '%' . $searchValue . '%')
                  ->orWhere('email', 'like', '%' . $searchValue . '%')
                  ->orWhere('bib_number', 'like', '%' . $searchValue . '%')
                  ->orWhere('distance_category', 'like', '%' . $searchValue . '%')
                  ->orWhereHas('event', function ($q) use ($searchValue): void {
                      $q->where('name', 'like', '%' . $searchValue . '%');
                  });
            });
        }

        return DataTables::of($query)
            ->addColumn('name_email', function (Participant $participant): string {
                return '<p class="font-bold text-slate-800">' . e($participant->name) . '</p>' .
                       '<p class="text-xs text-slate-500">' . e($participant->email) . '</p>';
            })
            ->addColumn('event_name', function (Participant $participant): string {
                return e($participant->event?->name ?? 'N/A');
            })
            ->addColumn('bib_number_display', function (Participant $participant): string {
                return '<span class="font-mono font-bold text-slate-700">' . e($participant->bib_number ?? '-') . '</span>';
            })
            ->addColumn('distance_badge', function (Participant $participant): string {
                return '<span class="inline-flex rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700">' . e($participant->distance_category) . '</span>';
            })
            ->addColumn('status_label', function (Participant $participant): string {
                return match ($participant->status) {
                    Participant::STATUS_VERIFIED => '<span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">Verified</span>',
                    Participant::STATUS_REJECTED => '<span class="inline-flex items-center rounded-full border border-red-200 bg-red-50 px-2.5 py-1 text-xs font-bold text-red-700">Rejected</span>',
                    default => '<span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700">Pending</span>',
                };
            })
            ->addColumn('race_status_label', function (Participant $participant): string {
                if ($participant->race_finished_at) {
                    return '<span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">Sudah Dicatat</span>';
                }
                return '<span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700">Belum Dicatat</span>';
            })
            ->addColumn('finish_time_formatted', function (Participant $participant): string {
                return $participant->race_finished_at ? $participant->race_finished_at->format('d M Y H:i:s') : '-';
            })
            ->addColumn('duration_display', function (Participant $participant): string {
                return '<span class="font-mono font-bold text-slate-700">' . e($participant->formatted_race_duration ?? '-') . '</span>';
            })
            ->rawColumns(['name_email', 'bib_number_display', 'distance_badge', 'status_label', 'race_status_label', 'duration_display'])
            ->make(true);
    }

    public function export(Request $request): StreamedResponse
    {
        $fileName = 'race-report-'.now()->format('Ymd-His').'.csv';
        $participants = $this->buildQuery($request)
            ->latest()
            ->get();

        return response()->streamDownload(function () use ($participants): void {
            $stream = fopen('php://output', 'w');

            fputcsv($stream, [
                'ID',
                'Event',
                'Bib Number',
                'Nama',
                'Kategori',
                'Status Peserta',
                'Status Race',
                'Waktu Finish',
                'Durasi',
            ]);

            foreach ($participants as $participant) {
                fputcsv($stream, [
                    $participant->id,
                    $participant->event?->name,
                    $participant->bib_number,
                    $participant->name,
                    $participant->distance_category,
                    $participant->status,
                    $participant->race_finished_at ? 'Sudah Dicatat' : 'Belum Dicatat',
                    $participant->race_finished_at?->format('Y-m-d H:i:s'),
                    $participant->formatted_race_duration,
                ]);
            }

            fclose($stream);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportPdf(Request $request): Response
    {
        $participants = $this->buildQuery($request)
            ->latest()
            ->get();

        $pdf = app('dompdf.wrapper');

        return $pdf->loadView('admin.race-reports.report-pdf', [
            'participants' => $participants,
            'selectedEvent' => $request->filled('event_id')
                ? Event::query()->find($request->integer('event_id'))
                : null,
            'timingStatus' => $request->string('timing_status')->value(),
        ])
            ->setPaper('A4', 'landscape')
            ->download('race-report-'.now()->format('Ymd-His').'.pdf');
    }

    private function buildQuery(Request $request): Builder
    {
        return Participant::query()
            ->with('event')
            ->when($request->filled('event_id'), function (Builder $query) use ($request): void {
                $query->where('event_id', $request->integer('event_id'));
            })
            ->when($request->filled('timing_status'), function (Builder $query) use ($request): void {
                match ($request->string('timing_status')->value()) {
                    'recorded' => $query->whereNotNull('race_finished_at'),
                    'unrecorded' => $query->whereNull('race_finished_at'),
                    default => null,
                };
            });
    }
}
