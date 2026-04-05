@extends('layouts.admin')

@section('content')
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <h1 class="font-display text-3xl font-bold uppercase italic text-slate-800">Kelola Event</h1>
        <a href="{{ route('admin.events.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:bg-brand-700 active:scale-95">
            <x-heroicon-o-plus class="h-4 w-4" />
            Tambah Event
        </a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table id="events-table" class="w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-5 py-4">Kode</th>
                    <th class="px-5 py-4">Nama</th>
                    <th class="px-5 py-4">Tanggal</th>
                    <th class="px-5 py-4">Status</th>
                    <th class="px-5 py-4">Aksi</th>
                </tr>
            </thead>
        </table>
    </div>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

    {{-- Quota Modal --}}
    <div id="quota-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex min-h-screen items-center justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            {{-- Background overlay --}}
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeQuotaModal()"></div>

            <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

            {{-- Modal panel --}}
            <div class="relative inline-block transform overflow-hidden rounded-2xl bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-amber-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6 text-amber-600">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.592-2.641m-1.826-3.07a6.375 6.375 0 00-4.773-4.773 6.375 6.375 0 00-4.773 4.773 6.375 6.375 0 004.773 4.773 6.375 6.375 0 004.773-4.773zm0 0V3.375" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg font-semibold leading-6 text-gray-900" id="modal-title">Edit Kuota Kategori</h3>
                            <p class="mt-1 text-sm text-gray-500" id="modal-event-name">Event Name</p>
                            
                            <div id="quota-form-container" class="mt-4 space-y-3">
                                {{-- Dynamic form fields will be inserted here --}}
                            </div>
                            
                            <div id="quota-error" class="mt-3 hidden rounded-md bg-red-50 p-3 text-sm text-red-600"></div>
                            <div id="quota-success" class="mt-3 hidden rounded-md bg-green-50 p-3 text-sm text-green-600"></div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
                    <button type="button" onclick="saveQuota()" id="save-quota-btn" class="inline-flex w-full justify-center rounded-xl bg-brand-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500 sm:w-auto transition-colors">
                        Simpan
                    </button>
                    <button type="button" onclick="closeQuotaModal()" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition-colors">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        let currentEventId = null;
        let categoryData = [];
        const quotaBaseUrl = '{{ route('admin.events.quota.get', ['event' => '__EVENT_ID__']) }}';

        function getQuotaUrl(eventId) {
            return quotaBaseUrl.replace('__EVENT_ID__', eventId);
        }

        function openQuotaModal(eventId, eventName) {
            currentEventId = eventId;
            document.getElementById('modal-event-name').textContent = eventName;
            document.getElementById('quota-modal').classList.remove('hidden');
            document.getElementById('quota-error').classList.add('hidden');
            document.getElementById('quota-success').classList.add('hidden');
            
            const quotaUrl = getQuotaUrl(eventId);
            console.log('Fetching quota from URL:', quotaUrl);
            
            // Fetch current quota data
            fetch(quotaUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('HTTP error! status: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    categoryData = data.categories;
                    renderQuotaForm();
                })
                .catch(error => {
                    console.error('Error fetching quota data:', error);
                    showError('Gagal memuat data kuota. Silakan coba lagi.');
                });
        }

        function openQuotaModal(eventId, eventName) {
            currentEventId = eventId;
            document.getElementById('modal-event-name').textContent = eventName;
            document.getElementById('quota-modal').classList.remove('hidden');
            document.getElementById('quota-error').classList.add('hidden');
            document.getElementById('quota-success').classList.add('hidden');
            
            // Fetch current quota data
            fetch(getQuotaUrl(eventId))
                .then(response => response.json())
                .then(data => {
                    categoryData = data.categories;
                    renderQuotaForm();
                })
                .catch(error => {
                    console.error('Error fetching quota data:', error);
                    showError('Gagal memuat data kuota. Silakan coba lagi.');
                });
        }

        function closeQuotaModal() {
            document.getElementById('quota-modal').classList.add('hidden');
            currentEventId = null;
            categoryData = [];
        }

        function renderQuotaForm() {
            const container = document.getElementById('quota-form-container');
            container.innerHTML = '';
            
            categoryData.forEach(category => {
                const currentQuota = category.current_quota !== null ? category.current_quota : '';
                const minQuota = category.registered_count;
                const hasLimit = category.current_quota !== null;
                const isFull = hasLimit && category.registered_count >= category.current_quota;
                
                const div = document.createElement('div');
                div.className = 'rounded-lg border border-gray-200 bg-gray-50 p-3';
                div.innerHTML = `
                    <div class="flex items-center justify-between mb-2">
                        <label class="font-medium text-gray-900">${category.name}</label>
                        <span class="text-xs text-gray-500">${category.registered_count} peserta terdaftar</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="relative flex-1">
                            <input type="number" 
                                   id="quota-${category.id}" 
                                   name="quotas[${category.id}]"
                                   value="${currentQuota}" 
                                   min="${minQuota}"
                                   placeholder="Tak terbatas"
                                   class="block w-full rounded-lg border-0 py-2 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">orang</span>
                            </div>
                        </div>
                        ${isFull ? '<span class="px-2 py-1 text-xs font-bold text-red-600 bg-red-100 rounded-full">PENUH</span>' : ''}
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Minimal: ${minQuota} (sesuai peserta terdaftar)</p>
                `;
                container.appendChild(div);
            });
        }

        function saveQuota() {
            if (!currentEventId) return;
            
            const quotas = {};
            categoryData.forEach(category => {
                const input = document.getElementById(`quota-${category.id}`);
                const value = input.value.trim();
                quotas[category.id] = value === '' ? null : parseInt(value);
            });
            
            const btn = document.getElementById('save-quota-btn');
            btn.disabled = true;
            btn.textContent = 'Menyimpan...';
            
            fetch(getQuotaUrl(currentEventId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ quotas })
            })
            .then(response => response.json())
            .then(data => {
                btn.disabled = false;
                btn.textContent = 'Simpan';
                
                if (data.success) {
                    showSuccess(data.message);
                    setTimeout(() => {
                        closeQuotaModal();
                        // Refresh DataTable
                        $('#events-table').DataTable().ajax.reload(null, false);
                    }, 1500);
                } else {
                    showError(data.message || 'Gagal menyimpan kuota.');
                }
            })
            .catch(error => {
                btn.disabled = false;
                btn.textContent = 'Simpan';
                console.error('Error saving quota:', error);
                showError('Terjadi kesalahan saat menyimpan. Silakan coba lagi.');
            });
        }

        function showError(message) {
            const errorDiv = document.getElementById('quota-error');
            errorDiv.textContent = message;
            errorDiv.classList.remove('hidden');
            document.getElementById('quota-success').classList.add('hidden');
        }

        function showSuccess(message) {
            const successDiv = document.getElementById('quota-success');
            successDiv.textContent = message;
            successDiv.classList.remove('hidden');
            document.getElementById('quota-error').classList.add('hidden');
        }

        $(document).ready(function() {
            $('#events-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route("admin.events.data") }}',
                columns: [
                    { data: 'event_code_formatted', name: 'event_code' },
                    { data: 'name_formatted', name: 'name' },
                    { data: 'date_formatted', name: 'date' },
                    { data: 'status_label', name: 'is_active', searchable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[2, 'desc']],
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
                    emptyTable: 'Tidak ada data event',
                    zeroRecords: 'Tidak ditemukan data yang sesuai'
                }
            });
        });
    </script>
@endsection
