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

    <form id="filter-form" method="GET" class="mb-6 grid gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:grid-cols-4">
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
            <p id="total-count" class="mt-2 text-3xl font-black text-slate-800">-</p>
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
        <table id="race-reports-table" class="w-full text-left text-sm">
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
        </table>
    </div>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            const table = $('#race-reports-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("admin.race-reports.data") }}',
                    data: function(d) {
                        d.event_id = $('select[name="event_id"]').val();
                        d.timing_status = $('select[name="timing_status"]').val();
                    },
                    dataSrc: function(json) {
                        // Update total count from recordsTotal
                        $('#total-count').text(json.recordsTotal.toLocaleString('id-ID'));
                        return json.data;
                    }
                },
                columns: [
                    { data: 'name_email', name: 'name' },
                    { data: 'event_name', name: 'event.name' },
                    { data: 'bib_number_display', name: 'bib_number' },
                    { data: 'distance_badge', name: 'distance_category', searchable: false },
                    { data: 'status_label', name: 'status', searchable: false },
                    { data: 'race_status_label', name: 'race_finished_at', searchable: false },
                    { data: 'finish_time_formatted', name: 'race_finished_at' },
                    { data: 'duration_display', name: 'race_duration_seconds', searchable: false }
                ],
                order: [[0, 'asc']],
                pageLength: 25,
                language: {
                    processing: 'Memuat data...',
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                    infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
                    infoFiltered: '(disaring dari _MAX_ total data)',
                    paginate: {
                        first: 'Pertama',
                        last: 'Terakhir',
                        next: 'Selanjutnya',
                        previous: 'Sebelumnya'
                    },
                    emptyTable: 'Tidak ada data peserta',
                    zeroRecords: 'Tidak ditemukan data yang sesuai'
                }
            });

            // Reload table when filter form submits
            $('#filter-form').on('submit', function(e) {
                e.preventDefault();
                table.ajax.reload();
            });
        });
    </script>
@endsection
