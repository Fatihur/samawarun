@extends('layouts.admin')

@section('content')
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <h1 class="font-display text-3xl font-bold uppercase italic text-slate-800">Kelola Peserta</h1>
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <a href="{{ route('admin.participants.export_pdf', request()->query()) }}" class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:bg-red-700 active:scale-95">
                    <x-heroicon-o-document-arrow-down class="h-5 w-5" />
                    Export PDF
                </a>
                <a href="{{ route('admin.participants.export', request()->query()) }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:bg-emerald-700 active:scale-95">
                    <x-heroicon-o-document-arrow-down class="h-5 w-5" />
                    Export CSV
                </a>
            </div>
            <button form="bulk-bib-form" type="submit" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:bg-indigo-700 active:scale-95">
                <x-heroicon-o-arrow-down-tray class="h-4 w-4" />
                Download Nomor Dada
            </button>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" class="mb-6 grid gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:grid-cols-4">
        <select name="event_id" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors">
            <option value="">Semua Event</option>
            @foreach ($events as $event)
                <option value="{{ $event->id }}" @selected((string) request('event_id') === (string) $event->id)>{{ $event->name }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors">
            <option value="">Semua Status</option>
            @foreach (['pending', 'verified', 'rejected'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-xl bg-slate-800 px-5 py-3 text-sm font-bold text-white hover:bg-slate-700 transition-colors active:scale-95">Filter</button>
    </form>

    <form id="bulk-bib-form" action="{{ route('admin.participants.id-card.bulk') }}" method="POST" class="hidden">
        @csrf
    </form>

    <div class="mb-4 rounded-xl border border-indigo-100 bg-indigo-50 px-4 py-3 text-sm text-indigo-700">
        Pilih peserta yang sudah <strong>verified</strong> lalu klik <strong>Download Nomor Dada</strong>.
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <table class="datatable w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-5 py-4" data-orderable="false">
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" id="select-all-verified" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span>Pilih</span>
                        </label>
                    </th>
                    <th class="px-5 py-4">Nama</th>
                    <th class="px-5 py-4">Event</th>
                    <th class="px-5 py-4">Jarak</th>
                    <th class="px-5 py-4">BIB</th>
                    <th class="px-5 py-4">Status</th>
                    <th class="px-5 py-4" data-orderable="false">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($participants as $participant)
                    <tr class="transition-colors hover:bg-slate-50/50">
                        <td class="px-5 py-4">
                            @if($participant->status === \App\Models\Participant::STATUS_VERIFIED)
                                <input type="checkbox" name="participant_ids[]" value="{{ $participant->id }}" form="bulk-bib-form" class="participant-select h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            @else
                                <span class="pl-6">-</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <p class="font-bold text-slate-800">{{ $participant->name }}</p>
                            <p class="text-xs text-slate-500">{{ $participant->email }}</p>
                        </td>
                        <td class="px-5 py-4 font-semibold text-slate-600">
                            {{ $participant->event?->name ?? 'N/A' }}
                        </td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700">{{ $participant->distance_category }}</span>
                        </td>
                        <td class="px-5 py-4 font-mono font-bold text-slate-600">
                            {{ $participant->bib_number ?? '-' }}
                        </td>
                        <td class="px-5 py-4">
                            @if($participant->status === \App\Models\Participant::STATUS_PENDING)
                                <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700 border border-amber-200">Pending</span>
                            @elseif($participant->status === \App\Models\Participant::STATUS_VERIFIED)
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 border border-emerald-200">Verified</span>
                            @elseif($participant->status === \App\Models\Participant::STATUS_REJECTED)
                                <span class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-1 text-xs font-bold text-red-700 border border-red-200">Rejected</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.participants.show', $participant) }}" class="text-xs font-bold text-brand-600 hover:text-brand-800 transition-colors">Detail</a>
                                
                                @if($participant->status === \App\Models\Participant::STATUS_PENDING)
                                    <form action="{{ route('admin.participants.verify', $participant) }}" method="POST" onsubmit="return confirm('Verifikasi peserta ini?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-xs font-bold text-emerald-600 hover:text-emerald-800 transition-colors">Verify</button>
                                    </form>
                                    <form action="{{ route('admin.participants.reject', $participant) }}" method="POST" onsubmit="return confirm('Tolak peserta ini?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-xs font-bold text-red-600 hover:text-red-800 transition-colors">Reject</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="mt-6">
        {{-- Pagination handled by DataTables --}}
    </div>

    <script>
        const selectAll = document.getElementById('select-all-verified');
        const participantChecks = Array.from(document.querySelectorAll('.participant-select'));

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                participantChecks.forEach(function (checkbox) {
                    checkbox.checked = selectAll.checked;
                });
            });

            participantChecks.forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    const allChecked = participantChecks.length > 0 && participantChecks.every(function (item) {
                        return item.checked;
                    });
                    selectAll.checked = allChecked;
                });
            });
        }
    </script>
@endsection
