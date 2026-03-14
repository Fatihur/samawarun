@extends('layouts.admin')

@section('content')
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold uppercase italic text-slate-800">Scan BIB</h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-500">Pilih event terlebih dahulu, kemudian masuk ke mode kiosk untuk memindai BIB peserta.</p>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Panel Kiri: Pilih Event --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="mb-6 flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-100 text-brand-600">
                    <x-heroicon-o-calendar-days class="h-6 w-6" />
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-800">Pilih Event</h2>
                    <p class="text-sm text-slate-500">Pilih event yang sedang berlangsung</p>
                </div>
            </div>

            <form id="event-form" class="space-y-6">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Event</label>
                    <select name="event_id" id="event-select" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-4 text-lg text-slate-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors" required>
                        <option value="">-- Pilih Event --</option>
                        @foreach ($events as $event)
                            <option value="{{ $event->id }}" @selected((string) request('event_id', old('event_id')) === (string) $event->id)>
                                {{ $event->name }} - {{ $event->date?->format('d M Y') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if (request('event_id'))
                    <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                                <x-heroicon-o-check class="h-5 w-5" />
                            </div>
                            <div>
                                <p class="font-semibold text-emerald-800">Event Terpilih</p>
                                <p class="text-sm text-emerald-600">{{ $selectedEvent?->name ?? 'Event tidak ditemukan' }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="rounded-xl bg-amber-50 border border-amber-200 p-4">
                    <div class="flex items-start gap-3">
                        <x-heroicon-o-information-circle class="h-5 w-5 text-amber-600 shrink-0 mt-0.5" />
                        <div class="text-sm text-amber-800">
                            <p class="font-semibold mb-1">Mode Kiosk</p>
                            <p>Scanner BIB hanya tersedia di mode kiosk. Pastikan perangkat memiliki kamera yang berfungsi dengan baik.</p>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- Panel Kanan: Aksi --}}
        <div class="space-y-6">
            {{-- Tombol Masuk Kiosk --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                <div class="text-center">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-orange-100 text-orange-600 mb-4">
                        <x-heroicon-o-computer-desktop class="h-10 w-10" />
                    </div>
                    <h2 class="text-xl font-bold text-slate-800 mb-2">Mode Kiosk</h2>
                    <p class="text-sm text-slate-500 mb-6">Masuk ke layar penuh dengan scanner kamera untuk memindai BIB peserta</p>

                    <a href="#" id="kiosk-link" class="inline-flex items-center gap-3 rounded-xl bg-orange-600 px-8 py-4 text-lg font-bold text-white shadow-lg shadow-orange-200 transition-all hover:bg-orange-700 hover:shadow-xl active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:shadow-none">
                        <x-heroicon-o-play-circle class="h-6 w-6" />
                        Mulai Scan BIB
                    </a>

                    <p id="select-event-warning" class="mt-4 text-sm text-amber-600 hidden">
                        <x-heroicon-o-exclamation-triangle class="inline h-4 w-4" />
                        Silakan pilih event terlebih dahulu
                    </p>
                </div>
            </div>

            {{-- Panduan --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <x-heroicon-o-question-mark-circle class="h-5 w-5 text-slate-400" />
                    Panduan Penggunaan
                </h3>
                <div class="space-y-3 text-sm text-slate-600">
                    <div class="flex items-start gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-100 text-brand-600 font-bold text-xs">1</span>
                        <p>Pilih event dari daftar di sebelah kiri</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-100 text-brand-600 font-bold text-xs">2</span>
                        <p>Klik tombol "Mulai Scan BIB" untuk masuk mode kiosk</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-100 text-brand-600 font-bold text-xs">3</span>
                        <p>Arahkan barcode BIB ke kamera hingga terbaca</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-100 text-brand-600 font-bold text-xs">4</span>
                        <p>Informasi peserta akan muncul otomatis</p>
                    </div>
                </div>
            </div>

            {{-- Tips --}}
            <div class="rounded-xl border border-indigo-100 bg-indigo-50 p-4">
                <div class="flex items-start gap-3">
                    <x-heroicon-o-light-bulb class="h-5 w-5 text-indigo-600 shrink-0" />
                    <div class="text-sm text-indigo-800">
                        <p class="font-semibold mb-1">Tips</p>
                        <ul class="list-disc list-inside space-y-1 text-indigo-700">
                            <li>Pastikan pencahayaan cukup terang</li>
                            <li>Posisikan barcode dalam jarak 10-20 cm dari kamera</li>
                            <li>Gunakan tombol ESC untuk keluar dari mode kiosk</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Riwayat Scan (jika ada event terpilih) --}}
    @if (request('event_id'))
    <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <x-heroicon-o-clock class="h-5 w-5 text-slate-400" />
                Riwayat Scan Hari Ini
            </h2>
            <button type="button" onclick="clearHistory()" class="text-sm text-slate-500 hover:text-red-600">
                Hapus Riwayat
            </button>
        </div>
        <div id="scan-history" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <p class="col-span-full text-center py-8 text-slate-400">Belum ada scan hari ini</p>
        </div>
    </div>
    @endif

    <script>
        const eventSelect = document.getElementById('event-select');
        const kioskLink = document.getElementById('kiosk-link');
        const warningEl = document.getElementById('select-event-warning');

        // Update kiosk link saat event dipilih
        eventSelect.addEventListener('change', function() {
            const eventId = this.value;

            if (eventId) {
                kioskLink.href = '{{ route("admin.bib-scan.kiosk") }}?event_id=' + eventId;
                warningEl.classList.add('hidden');

                // Update URL tanpa reload
                const url = new URL(window.location);
                url.searchParams.set('event_id', eventId);
                window.history.pushState({}, '', url);
            } else {
                kioskLink.href = '#';
            }
        });

        // Cek saat link diklik
        kioskLink.addEventListener('click', function(e) {
            if (!eventSelect.value) {
                e.preventDefault();
                warningEl.classList.remove('hidden');
                eventSelect.focus();
                eventSelect.classList.add('ring-2', 'ring-amber-500', 'border-amber-500');
                setTimeout(() => {
                    eventSelect.classList.remove('ring-2', 'ring-amber-500', 'border-amber-500');
                }, 2000);
            }
        });

        // Set initial link jika sudah ada event terpilih
        if (eventSelect.value) {
            kioskLink.href = '{{ route("admin.bib-scan.kiosk") }}?event_id=' + eventSelect.value;
        }

        // Riwayat Scan dari localStorage
        function renderHistory() {
            const eventId = eventSelect.value;
            if (!eventId) return;

            const historyKey = 'kioskScanHistory_' + eventId;
            const scanHistory = JSON.parse(localStorage.getItem(historyKey) || '[]');
            const today = new Date().toDateString();
            const todayScans = scanHistory.filter(s => new Date(s.time).toDateString() === today);

            const container = document.getElementById('scan-history');
            if (!todayScans.length) {
                container.innerHTML = '<p class="col-span-full text-center py-8 text-slate-400">Belum ada scan hari ini</p>';
                return;
            }

            container.innerHTML = todayScans.slice(0, 8).map(item => {
                const time = new Date(item.time).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                const statusClass = item.found ? 'bg-emerald-50 border-emerald-200' : 'bg-red-50 border-red-200';
                const statusIcon = item.found ? '✓' : '✕';
                const statusColor = item.found ? 'text-emerald-600' : 'text-red-600';

                return `
                    <div class="flex items-center justify-between rounded-lg border ${statusClass} p-3">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-slate-700">${item.bib}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-slate-400">${time}</span>
                            <span class="text-sm font-bold ${statusColor}">${statusIcon}</span>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function clearHistory() {
            if (!confirm('Hapus riwayat scan untuk event ini?')) return;

            const eventId = eventSelect.value;
            if (eventId) {
                localStorage.removeItem('kioskScanHistory_' + eventId);
                renderHistory();
            }
        }

        // Render history on load
        renderHistory();
    </script>
@endsection
