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

class RaceReportController extends Controller
{
    public function index(Request $request): View
    {
        $participants = $this->buildQuery($request)
            ->latest()
            ->get();

        $baseQuery = $this->buildQuery($request);

        return view('admin.race-reports.index', [
            'participants' => $participants,
            'events' => Event::query()->orderByDesc('date')->get(),
            'recordedCount' => (clone $baseQuery)->whereNotNull('race_finished_at')->count(),
            'unrecordedCount' => (clone $baseQuery)->whereNull('race_finished_at')->count(),
        ]);
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
