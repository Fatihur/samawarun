@extends('layouts.admin')

@section('content')
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold uppercase italic text-slate-800">Laporan Race</h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-500">Pantau peserta yang sudah atau belum dicatat waktu race-nya, lalu export laporan ke Excel atau PDF sesuai filter yang dipilih.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.race-reports.export-pdf', request()->query()) }}" class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:bg-red-700 active:scale-95">
                <x-heroicon-o-document-text class="h-5 w-5" />
                Export PDF
            </a>
            <a href="{{ route('admin.race-reports.export', request()->query()) }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:bg-emerald-700 active:scale-95">
                <x-heroicon-o-table-cells class="h-5 w-5" />
                Export Excel
            </a>
        </div>
    </div>

    <form method="GET" class="mb-6 grid gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:grid-cols-4">
        <select name="event_id" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors">
            <option value="">Semua Event</option>
            @foreach ($events as $event)
                <option value="{{ $event->id }}" @selected((string) request('event_id') === (string) $event->id)>
                    {{ $event->name }} - {{ $event->date?->format('d M Y') }}
                </option>
            @endforeach
        </select>

        <select name="timing_status" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors">
            <option value="">Semua Status Race</option>
            <option value="recorded" @selected(request('timing_status') === 'recorded')>Sudah Dicatat</option>
            <option value="unrecorded" @selected(request('timing_status') === 'unrecorded')>Belum Dicatat</option>
        </select>

        <button type="submit" class="rounded-xl bg-slate-800 px-5 py-3 text-sm font-bold text-white hover:bg-slate-700 transition-colors active:scale-95">Filter</button>
        <a href="{{ route('admin.race-reports.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-600 transition-colors hover:bg-slate-50">Reset</a>
    </form>

    <div class="mb-6 grid gap-4 md:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Data</p>
            <p class="mt-2 text-3xl font-black text-slate-800">{{ $participants->count() }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-emerald-500">Sudah Dicatat</p>
            <p class="mt-2 text-3xl font-black text-emerald-700">{{ $recordedCount }}</p>
        </div>
        <div class="rounded-2xl border border-amber-100 bg-amber-50 p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-amber-500">Belum Dicatat</p>
            <p class="mt-2 text-3xl font-black text-amber-700">{{ $unrecordedCount }}</p>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <table class="datatable w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-5 py-4">Peserta</th>
                    <th class="px-5 py-4">Event</th>
                    <th class="px-5 py-4">BIB</th>
                    <th class="px-5 py-4">Kategori</th>
                    <th class="px-5 py-4">Status Peserta</th>
                    <th class="px-5 py-4">Status Race</th>
                    <th class="px-5 py-4">Waktu Finish</th>
                    <th class="px-5 py-4">Durasi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($participants as $participant)
                    <tr class="transition-colors hover:bg-slate-50/50">
                        <td class="px-5 py-4">
                            <p class="font-bold text-slate-800">{{ $participant->name }}</p>
                            <p class="text-xs text-slate-500">{{ $participant->email }}</p>
                        </td>
                        <td class="px-5 py-4 font-semibold text-slate-600">{{ $participant->event?->name ?? 'N/A' }}</td>
                        <td class="px-5 py-4 font-mono font-bold text-slate-700">{{ $participant->bib_number ?? '-' }}</td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700">{{ $participant->distance_category }}</span>
                        </td>
                        <td class="px-5 py-4">
                            @if ($participant->status === \App\Models\Participant::STATUS_PENDING)
                                <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700">Pending</span>
                            @elseif ($participant->status === \App\Models\Participant::STATUS_VERIFIED)
                                <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">Verified</span>
                            @else
                                <span class="inline-flex items-center rounded-full border border-red-200 bg-red-50 px-2.5 py-1 text-xs font-bold text-red-700">Rejected</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if ($participant->race_finished_at)
                                <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">Sudah Dicatat</span>
                            @else
                                <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700">Belum Dicatat</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-sm text-slate-600">{{ $participant->race_finished_at?->format('d M Y H:i:s') ?? '-' }}</td>
                        <td class="px-5 py-4 font-mono font-bold text-slate-700">{{ $participant->formatted_race_duration ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if ($participants->isEmpty())
            <div class="border-t border-slate-100 px-5 py-10 text-center text-sm text-slate-500">
                Belum ada data peserta yang sesuai filter laporan race.
            </div>
        @endif
    </div>
@endsection
