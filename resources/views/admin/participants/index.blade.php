@extends('layouts.admin')

@section('content')
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <h1 class="font-display text-3xl font-bold uppercase italic text-slate-800">Kelola Peserta</h1>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.participants.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:bg-brand-700 active:scale-95">
                <x-heroicon-o-plus class="h-5 w-5" />
                Tambah Peserta
            </a>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <a href="{{ route('admin.participants.export_pdf', request()->query()) }}" class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:bg-red-700 active:scale-95">
                    <x-heroicon-o-document-arrow-down class="h-5 w-5" />
                    Export PDF
                </a>
                <a href="{{ route('admin.participants.export', request()->query()) }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:bg-emerald-700 active:scale-95">
                    <x-heroicon-o-document-arrow-down class="h-5 w-5" />
                    Export CSV
                </a>
                <form action="{{ route('admin.participants.send-payment-reminders') }}" method="POST" class="inline" onsubmit="return confirm('Kirim pengingat pembayaran ke semua peserta yang menunggu pembayaran?')" data-loading-title="Mengirim pengingat" data-loading-message="Email pengingat sedang dikirim ke semua peserta yang menunggu pembayaran...">
                    @csrf
                    <button type="submit" data-loading-label="Mengirim..." class="inline-flex items-center gap-2 rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:bg-amber-600 active:scale-95">
                        <x-heroicon-o-bell-alert class="h-4 w-4" />
                        Kirim Pengingat
                    </button>
                </form>
            </div>
            <button form="bulk-bib-form" type="submit" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:bg-indigo-700 active:scale-95">
                <x-heroicon-o-arrow-down-tray class="h-4 w-4" />
                Download Nomor Dada
            </button>
        </div>
    </div>

    {{-- Filter --}}
    <form id="filter-form" method="GET" class="mb-6 grid gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:grid-cols-2 lg:grid-cols-5">
        <select name="event_id" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors">
            <option value="">Semua Event</option>
            @foreach ($events as $event)
                <option value="{{ $event->id }}" @selected((string) request('event_id') === (string) $event->id)>{{ $event->name }}</option>
            @endforeach
        </select>
        <select name="distance_category" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors">
            <option value="">Semua Kategori</option>
            @foreach ($distanceCategories as $category)
                <option value="{{ $category }}" @selected(request('distance_category') === $category)>{{ $category }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors">
            <option value="">Semua Status</option>
            @foreach ([
                'pending' => 'Pending',
                'verified' => 'Verified',
                'rejected' => 'Rejected',
                'submitted' => 'Menunggu Review Pendaftaran',
                'approved_waiting_payment' => 'Menunggu Pembayaran',
                'payment_submitted' => 'Pembayaran Direview',
                'payment_rejected' => 'Pembayaran Ditolak',
                'completed' => 'Selesai',
            ] as $status => $label)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <button type="submit" class="flex-1 rounded-xl bg-slate-800 px-5 py-3 text-sm font-bold text-white hover:bg-slate-700 transition-colors active:scale-95">Filter</button>
            @if(request()->hasAny(['event_id', 'status', 'distance_category']))
                <a href="{{ route('admin.participants.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-500 hover:bg-slate-50 transition-colors" title="Reset filter">
                    <x-heroicon-o-x-mark class="h-4 w-4" />
                </a>
            @endif
        </div>
    </form>

    <form id="bulk-bib-form" action="{{ route('admin.participants.id-card.bulk') }}" method="POST" class="hidden">
        @csrf
    </form>

    <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div class="rounded-xl border border-indigo-100 bg-indigo-50 px-4 py-3 text-sm text-indigo-700">
            Pilih peserta yang sudah <strong>verified</strong> lalu klik <strong>Download Nomor Dada</strong>. Centang hanya berlaku untuk halaman ini.
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <table id="participants-table" class="w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-5 py-4">
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
                    <th class="px-5 py-4">Aksi</th>
                </tr>
            </thead>
        </table>
    </div>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            const table = $('#participants-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("admin.participants.data") }}',
                    data: function(d) {
                        d.event_id = $('select[name="event_id"]').val();
                        d.distance_category = $('select[name="distance_category"]').val();
                        d.status = $('select[name="status"]').val();
                    }
                },
                columns: [
                    { data: 'select', name: 'select', orderable: false, searchable: false },
                    { data: 'name_email', name: 'name' },
                    { data: 'event_name', name: 'event.name' },
                    { data: 'distance_badge', name: 'distance_category', searchable: false },
                    { data: 'bib_number_display', name: 'bib_number' },
                    { data: 'status_label', name: 'status', searchable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[1, 'desc']],
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
                },
                drawCallback: function() {
                    initCheckboxHandlers();
                }
            });

            // Reload table when filter form submits
            $('#filter-form').on('submit', function(e) {
                e.preventDefault();
                table.ajax.reload();
            });

            function initCheckboxHandlers() {
                const selectAll = document.getElementById('select-all-verified');
                const participantChecks = Array.from(document.querySelectorAll('.participant-select'));

                if (selectAll) {
                    // Remove existing listener to avoid duplicates
                    selectAll.replaceWith(selectAll.cloneNode(true));
                    const newSelectAll = document.getElementById('select-all-verified');

                    newSelectAll.addEventListener('change', function() {
                        document.querySelectorAll('.participant-select').forEach(function(checkbox) {
                            checkbox.checked = newSelectAll.checked;
                        });
                    });

                    document.querySelectorAll('.participant-select').forEach(function(checkbox) {
                        checkbox.addEventListener('change', function() {
                            const allChecks = Array.from(document.querySelectorAll('.participant-select'));
                            const allChecked = allChecks.length > 0 && allChecks.every(function(item) {
                                return item.checked;
                            });
                            newSelectAll.checked = allChecked;
                        });
                    });
                }
            }

            initCheckboxHandlers();
        });
    </script>
@endsection
