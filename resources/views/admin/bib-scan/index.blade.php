@extends('layouts.admin')

@section('content')
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold uppercase italic text-slate-800">Scan BIB</h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-500">Gunakan kamera atau input manual untuk membaca nomor bib dan menampilkan informasi peserta tanpa mencatat waktu race.</p>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <form method="GET" action="{{ route('admin.bib-scan.index') }}" class="space-y-6" data-skip-loading="true" id="admin-bib-scan-form">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Pilih Event</label>
                    <select name="event_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors" required>
                        <option value="">Pilih event</option>
                        @foreach ($events as $event)
                            <option value="{{ $event->id }}" @selected((string) request('event_id', old('event_id')) === (string) $event->id)>
                                {{ $event->name }} - {{ $event->date?->format('d M Y') }}
                            </option>
                        @endforeach
                    </select>
                    @error('event_id')
                        <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="rounded-2xl border border-indigo-100 bg-indigo-50 p-5">
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-indigo-600">Scanner Kamera</p>
                    <h2 class="mt-2 text-lg font-bold text-slate-900">Arahkan barcode bib ke kamera</h2>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">Setelah barcode terbaca, nomor bib akan diisikan otomatis dan sistem menampilkan detail peserta di panel kanan.</p>

                    <div class="mt-5 overflow-hidden rounded-2xl border border-dashed border-indigo-200 bg-slate-950 p-3">
                        <div id="admin-bib-info-scanner" class="min-h-[260px] rounded-xl bg-slate-900"></div>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-3">
                        <button type="button" id="start-bib-info-scanner" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-sm transition-all hover:bg-indigo-700 active:scale-95">
                            <x-heroicon-o-camera class="h-5 w-5" />
                            Mulai Scan
                        </button>
                        <button type="button" id="stop-bib-info-scanner" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition-all hover:bg-slate-100 active:scale-95">
                            <x-heroicon-o-stop-circle class="h-5 w-5" />
                            Hentikan
                        </button>
                    </div>

                    <div id="bib-info-scan-status" class="mt-4 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600">
                        Scanner belum aktif. Pilih event lalu klik <strong>Mulai Scan</strong>.
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Nomor BIB</label>
                    <input type="text" id="admin-bib-info-number" name="bib_number" value="{{ $bibNumber }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-lg font-bold uppercase tracking-wider text-slate-800 placeholder-slate-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors" placeholder="Contoh: 5001" required autocomplete="off">
                    @error('bib_number')
                        <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-5 py-3 text-sm font-bold text-white shadow-sm transition-all hover:bg-slate-700 active:scale-95">
                    <x-heroicon-o-magnifying-glass class="h-5 w-5" />
                    Tampilkan Informasi Peserta
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <h2 class="text-lg font-bold text-slate-800">Informasi Peserta</h2>

            @if ($participant)
                <div class="mt-5 space-y-3">
                    <div class="rounded-xl bg-slate-50 px-4 py-3">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Nama</p>
                        <p class="mt-1 text-base font-semibold text-slate-800">{{ $participant->name }}</p>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="rounded-xl bg-slate-50 px-4 py-3">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">BIB</p>
                            <p class="mt-1 font-mono text-lg font-bold text-indigo-700">{{ $participant->bib_number ?? '-' }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-4 py-3">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Status</p>
                            <p class="mt-1 text-sm font-semibold text-slate-800">{{ ucfirst($participant->status) }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-4 py-3">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Kategori</p>
                            <p class="mt-1 text-sm font-semibold text-slate-800">{{ $participant->distance_category }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-4 py-3">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Jersey</p>
                            <p class="mt-1 text-sm font-semibold text-slate-800">{{ $participant->jersey_size }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-4 py-3 sm:col-span-2">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Event</p>
                            <p class="mt-1 text-sm font-semibold text-slate-800">{{ $participant->event?->name ?? '-' }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-4 py-3 sm:col-span-2">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Email</p>
                            <p class="mt-1 text-sm font-semibold text-slate-800">{{ $participant->email }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-4 py-3 sm:col-span-2">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Telepon</p>
                            <p class="mt-1 text-sm font-semibold text-slate-800">{{ $participant->phone }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-4 py-3 sm:col-span-2">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Kontak Darurat</p>
                            <p class="mt-1 text-sm font-semibold text-slate-800">{{ $participant->emergency_contact_display }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-4 py-3 sm:col-span-2">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Waktu Finish</p>
                            <p class="mt-1 text-sm font-semibold text-slate-800">{{ $participant->formatted_race_duration ?? 'Belum tercatat' }}</p>
                        </div>
                    </div>
                </div>
            @elseif ($lookupAttempted)
                <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-800">
                    Nomor BIB <strong>{{ $bibNumber }}</strong> tidak ditemukan pada event yang dipilih.
                </div>
            @else
                <div class="mt-5 rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                    Belum ada data peserta ditampilkan. Pilih event dan scan BIB untuk melihat informasinya.
                </div>
            @endif
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const startButton = document.getElementById('start-bib-info-scanner');
            const stopButton = document.getElementById('stop-bib-info-scanner');
            const statusBox = document.getElementById('bib-info-scan-status');
            const bibInput = document.getElementById('admin-bib-info-number');
            const eventSelect = document.querySelector('select[name="event_id"]');
            const form = document.getElementById('admin-bib-scan-form');
            const scannerElementId = 'admin-bib-info-scanner';

            let html5QrCode = null;
            let isScanning = false;

            function setStatus(message, type) {
                const styles = {
                    neutral: 'mt-4 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600',
                    success: 'mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700',
                    error: 'mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700'
                };

                statusBox.className = styles[type] || styles.neutral;
                statusBox.innerHTML = message;
            }

            async function stopScanner() {
                if (!html5QrCode || !isScanning) {
                    return;
                }

                await html5QrCode.stop();
                await html5QrCode.clear();
                isScanning = false;
                setStatus('Scanner dihentikan. Anda bisa mulai scan lagi kapan saja.', 'neutral');
            }

            async function startScanner() {
                if (!eventSelect.value) {
                    setStatus('Pilih event terlebih dahulu sebelum memulai scan.', 'error');
                    eventSelect.focus();
                    return;
                }

                if (isScanning) {
                    return;
                }

                if (typeof Html5Qrcode === 'undefined') {
                    setStatus('Library scanner belum siap dimuat. Coba lagi beberapa detik.', 'error');
                    return;
                }

                html5QrCode = html5QrCode || new Html5Qrcode(scannerElementId);
                setStatus('Meminta akses kamera dan menyiapkan scanner...', 'neutral');

                try {
                    await html5QrCode.start(
                        { facingMode: 'environment' },
                        {
                            fps: 10,
                            aspectRatio: 1.5,
                            qrbox: { width: 280, height: 120 },
                            formatsToSupport: [
                                Html5QrcodeSupportedFormats.CODE_128,
                                Html5QrcodeSupportedFormats.CODE_39,
                                Html5QrcodeSupportedFormats.CODE_93,
                                Html5QrcodeSupportedFormats.EAN_13,
                                Html5QrcodeSupportedFormats.EAN_8,
                                Html5QrcodeSupportedFormats.ITF
                            ]
                        },
                        function (decodedText) {
                            bibInput.value = decodedText.trim().toUpperCase();
                            setStatus('Barcode berhasil dibaca. Informasi peserta sedang ditampilkan...', 'success');
                            stopScanner().finally(function () {
                                form.submit();
                            });
                        },
                        function () {}
                    );

                    isScanning = true;
                    setStatus('Scanner aktif. Arahkan barcode bib ke area kamera.', 'success');
                } catch (error) {
                    setStatus('Kamera tidak bisa dibuka. Pastikan izin kamera aktif atau masukkan BIB manual.', 'error');
                }
            }

            startButton?.addEventListener('click', function () {
                startScanner();
            });

            stopButton?.addEventListener('click', function () {
                stopScanner();
            });
        });
    </script>
@endsection
