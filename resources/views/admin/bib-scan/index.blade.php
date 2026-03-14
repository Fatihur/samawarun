@extends('layouts.admin')

@section('content')
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold uppercase italic text-slate-800">Scan BIB</h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-500">Gunakan kamera atau input manual untuk membaca nomor BIB. Data akan tampil di panel kanan dan tersimpan di riwayat scan.</p>
        </div>

        @if ($selectedEvent = request('event_id', old('event_id')))
        <div class="flex items-center gap-3">
            <button type="button" onclick="toggleFullscreen()" class="inline-flex items-center gap-2 rounded-xl bg-slate-700 px-5 py-3 text-sm font-bold text-white shadow-sm transition-all hover:bg-slate-800 active:scale-95">
                <x-heroicon-o-arrows-pointing-out class="h-5 w-5" />
                Fullscreen
            </button>
            <a href="{{ route('admin.bib-scan.kiosk', ['event_id' => $selectedEvent]) }}" class="inline-flex items-center gap-2 rounded-xl bg-orange-600 px-5 py-3 text-sm font-bold text-white shadow-sm transition-all hover:bg-orange-700 active:scale-95">
                <x-heroicon-o-computer-desktop class="h-5 w-5" />
                Mode Kiosk
            </a>
        </div>
        @endif
    </div>

    <div class="grid gap-6 lg:grid-cols-[1fr_0.9fr_0.5fr]" id="scan-container">
        {{-- Panel Kiri: Scanner --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <form method="GET" action="{{ route('admin.bib-scan.index') }}" class="space-y-6" data-skip-loading="true" id="admin-bib-scan-form">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Pilih Event</label>
                    <select name="event_id" id="event-select" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors" required>
                        <option value="">Pilih event</option>
                        @foreach ($events as $event)
                            <option value="{{ $event->id }}" @selected((string) request('event_id', old('event_id')) === (string) $event->id)>
                                {{ $event->name }} - {{ $event->date?->format('d M Y') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Scanner Area --}}
                <div class="rounded-2xl border border-indigo-100 bg-gradient-to-br from-indigo-50 to-blue-50 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.22em] text-indigo-600">Scanner Kamera</p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">Arahkan Barcode ke Kamera</h2>
                        </div>
                        <div id="scanner-status-indicator" class="flex h-3 w-3 rounded-full bg-slate-300">
                            <span class="sr-only">Scanner status</span>
                        </div>
                    </div>

                    <div class="mt-4 overflow-hidden rounded-2xl border-2 border-dashed border-indigo-200 bg-slate-950 p-2">
                        <div id="admin-bib-info-scanner" class="min-h-[240px] rounded-xl bg-slate-900 flex items-center justify-center">
                            <div class="text-center text-slate-500">
                                <x-heroicon-o-qr-code class="mx-auto h-12 w-12 mb-2" />
                                <p class="text-sm">Kamera akan aktif di sini</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-3">
                        <button type="button" id="start-bib-info-scanner" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-sm transition-all hover:bg-indigo-700 active:scale-95">
                            <x-heroicon-o-camera class="h-5 w-5" />
                            Mulai Scan
                        </button>
                        <button type="button" id="stop-bib-info-scanner" class="hidden inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition-all hover:bg-slate-100 active:scale-95">
                            <x-heroicon-o-stop-circle class="h-5 w-5" />
                            Hentikan
                        </button>
                        <button type="button" id="reset-scan-btn" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-5 py-3 text-sm font-bold text-slate-600 transition-all hover:bg-slate-100">
                            <x-heroicon-o-arrow-path class="h-5 w-5" />
                            Scan Lagi
                        </button>
                    </div>

                    {{-- Status Box --}}
                    <div id="bib-info-scan-status" class="mt-4 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600 flex items-center gap-3">
                        <x-heroicon-o-information-circle class="h-5 w-5 text-slate-400 shrink-0" />
                        <span>Pilih event lalu klik <strong>Mulai Scan</strong> untuk memindai BIB.</span>
                    </div>
                </div>

                {{-- Manual Input --}}
                <div class="relative">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Nomor BIB (Manual)</label>
                    <div class="flex gap-2">
                        <input type="text" id="admin-bib-info-number" name="bib_number" value="{{ $bibNumber }}" class="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-lg font-bold uppercase tracking-wider text-slate-800 placeholder-slate-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors" placeholder="Contoh: 10K-001" autocomplete="off">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-5 py-3 text-sm font-bold text-white shadow-sm transition-all hover:bg-slate-700 active:scale-95">
                            <x-heroicon-o-magnifying-glass class="h-5 w-5" />
                        </button>
                    </div>
                    <p class="mt-2 text-xs text-slate-500">Tekan <kbd class="rounded bg-slate-200 px-1.5 py-0.5 font-mono">Enter</kbd> untuk mencari</p>
                </div>
            </form>
        </div>

        {{-- Panel Tengah: Hasil Scan --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-slate-800">Informasi Peserta</h2>
                <span id="scan-timestamp" class="text-xs text-slate-400"></span>
            </div>

            <div id="participant-result">
                @if ($participant)
                    @include('admin.bib-scan._participant-card', ['participant' => $participant])
                @elseif ($lookupAttempted)
                    <div class="rounded-xl border border-red-200 bg-red-50 p-6 text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-100 text-red-600 mb-3">
                            <x-heroicon-o-x-circle class="h-8 w-8" />
                        </div>
                        <h3 class="font-bold text-red-800">Peserta Tidak Ditemukan</h3>
                        <p class="mt-1 text-sm text-red-600">Nomor BIB <strong>{{ $bibNumber }}</strong> tidak terdaftar di event ini.</p>
                        <button type="button" onclick="resetScan()" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-bold text-white hover:bg-red-700">
                            <x-heroicon-o-arrow-path class="h-4 w-4" />
                            Coba Lagi
                        </button>
                    </div>
                @else
                    <div class="flex h-96 flex-col items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center">
                        <div class="flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                            <x-heroicon-o-qr-code class="h-10 w-10" />
                        </div>
                        <p class="mt-4 text-sm text-slate-500">Belum ada data peserta.<br>Scan BIB atau masukkan nomor manual.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Panel Kanan: Riwayat Scan --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-800">Riwayat Scan</h2>
                <button type="button" onclick="clearHistory()" class="text-xs font-medium text-slate-500 hover:text-red-600">
                    Hapus
                </button>
            </div>

            <div id="scan-history" class="space-y-2 max-h-[500px] overflow-y-auto">
                <div class="rounded-lg bg-slate-50 p-4 text-center text-sm text-slate-400">
                    Belum ada riwayat scan
                </div>
            </div>

            <div class="mt-4 rounded-xl bg-slate-50 p-3">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500">Total Scan Hari Ini:</span>
                    <span id="today-scan-count" class="font-bold text-slate-800">0</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Audio untuk feedback --}}
    <audio id="scan-success-sound" preload="auto">
        <source src="{{ asset('sounds/success-beep.mp3') }}" type="audio/mpeg">
    </audio>
    <audio id="scan-error-sound" preload="auto">
        <source src="{{ asset('sounds/error-beep.mp3') }}" type="audio/mpeg">
    </audio>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        // Global state
        let html5QrCode = null;
        let isScanning = false;
        let scanHistory = JSON.parse(localStorage.getItem('bibScanHistory') || '[]');
        const today = new Date().toDateString();

        // DOM Elements
        const startButton = document.getElementById('start-bib-info-scanner');
        const stopButton = document.getElementById('stop-bib-info-scanner');
        const resetButton = document.getElementById('reset-scan-btn');
        const statusBox = document.getElementById('bib-info-scan-status');
        const statusIndicator = document.getElementById('scanner-status-indicator');
        const bibInput = document.getElementById('admin-bib-info-number');
        const eventSelect = document.getElementById('event-select');
        const form = document.getElementById('admin-bib-scan-form');
        const scannerElementId = 'admin-bib-info-scanner';
        const successSound = document.getElementById('scan-success-sound');
        const errorSound = document.getElementById('scan-error-sound');

        // Initialize
        document.addEventListener('DOMContentLoaded', function () {
            renderHistory();
            updateTodayCount();

            // Event listeners
            startButton?.addEventListener('click', function(e) {
                e.preventDefault();
                startScanner();
            });

            stopButton?.addEventListener('click', function(e) {
                e.preventDefault();
                stopScanner();
            });

            resetButton?.addEventListener('click', function(e) {
                e.preventDefault();
                resetScan();
            });

            // Auto-focus input on load
            bibInput?.focus();

            // Enter key on input triggers search
            bibInput?.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    form.submit();
                }
            });

            // Event change resets scanner
            eventSelect?.addEventListener('change', function() {
                stopScanner();
                if (this.value) {
                    setStatus('Event dipilih. Klik Mulai Scan untuk memindai.', 'neutral');
                }
            });
        });

        function setStatus(message, type) {
            const styles = {
                neutral: 'mt-4 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600 flex items-center gap-3',
                success: 'mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 flex items-center gap-3',
                error: 'mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 flex items-center gap-3',
                warning: 'mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700 flex items-center gap-3'
            };

            const icons = {
                neutral: '<svg class="h-5 w-5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
                success: '<svg class="h-5 w-5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
                error: '<svg class="h-5 w-5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
                warning: '<svg class="h-5 w-5 text-amber-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>'
            };

            statusBox.className = styles[type] || styles.neutral;
            statusBox.innerHTML = (icons[type] || icons.neutral) + '<span>' + message + '</span>';
        }

        function updateScannerStatus(status) {
            const colors = {
                idle: 'bg-slate-300',
                active: 'bg-emerald-500 animate-pulse',
                error: 'bg-red-500',
                success: 'bg-emerald-600'
            };
            statusIndicator.className = 'flex h-3 w-3 rounded-full ' + (colors[status] || colors.idle);
        }

        async function stopScanner() {
            if (!html5QrCode || !isScanning) return;

            try {
                await html5QrCode.stop();
                await html5QrCode.clear();
            } catch (e) {
                // Ignore
            }
            isScanning = false;
            html5QrCode = null;

            startButton.classList.remove('hidden');
            stopButton.classList.add('hidden');
            updateScannerStatus('idle');
            setStatus('Scanner dihentikan.', 'neutral');
        }

        async function startScanner() {
            if (!eventSelect.value) {
                setStatus('Pilih event terlebih dahulu!', 'error');
                eventSelect.focus();
                return;
            }

            if (isScanning) return;

            // Wait for library
            if (typeof Html5Qrcode === 'undefined') {
                setStatus('Memuat library scanner...', 'warning');
                let retries = 0;
                const checkLibrary = setInterval(function() {
                    retries++;
                    if (typeof Html5Qrcode !== 'undefined') {
                        clearInterval(checkLibrary);
                        startScanner();
                    } else if (retries >= 10) {
                        clearInterval(checkLibrary);
                        setStatus('Library gagal dimuat. Refresh halaman.', 'error');
                    }
                }, 500);
                return;
            }

            html5QrCode = new Html5Qrcode(scannerElementId);
            setStatus('Meminta akses kamera...', 'neutral');
            updateScannerStatus('active');

            try {
                await html5QrCode.start(
                    { facingMode: 'environment' },
                    {
                        fps: 10,
                        aspectRatio: 1.5,
                        qrbox: { width: 250, height: 100 }
                    },
                    function (decodedText) {
                        const bibNumber = decodedText.trim().toUpperCase();
                        bibInput.value = bibNumber;
                        playSuccessSound();
                        addToHistory(bibNumber, 'success');
                        setStatus('BIB terbaca: ' + bibNumber, 'success');
                        stopScanner().finally(function () {
                            form.submit();
                        });
                    },
                    function () {}
                );

                isScanning = true;
                startButton.classList.add('hidden');
                stopButton.classList.remove('hidden');
                setStatus('Scanner aktif! Arahkan barcode ke kamera.', 'success');
            } catch (error) {
                console.error('Scanner error:', error);
                updateScannerStatus('error');
                setStatus('Kamera tidak bisa dibuka. Pastikan izin kamera aktif.', 'error');
                html5QrCode = null;
            }
        }

        function resetScan() {
            bibInput.value = '';
            bibInput.focus();
            setStatus('Siap untuk scan berikutnya.', 'neutral');
            updateScannerStatus('idle');
        }

        function addToHistory(bibNumber, status) {
            const entry = {
                bib: bibNumber,
                status: status,
                timestamp: new Date().toISOString()
            };
            scanHistory.unshift(entry);
            if (scanHistory.length > 20) scanHistory.pop();
            localStorage.setItem('bibScanHistory', JSON.stringify(scanHistory));
            renderHistory();
            updateTodayCount();
        }

        function renderHistory() {
            const container = document.getElementById('scan-history');
            if (!scanHistory.length) {
                container.innerHTML = '<div class="rounded-lg bg-slate-50 p-4 text-center text-sm text-slate-400">Belum ada riwayat scan</div>';
                return;
            }

            container.innerHTML = scanHistory.map(item => {
                const time = new Date(item.timestamp).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                const statusColor = item.status === 'success' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700';
                return `
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 p-3 text-sm">
                        <div class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full ${statusColor.replace('bg-', 'bg-').replace('text-', '')}"></span>
                            <span class="font-mono font-bold">${item.bib}</span>
                        </div>
                        <span class="text-xs text-slate-400">${time}</span>
                    </div>
                `;
            }).join('');
        }

        function updateTodayCount() {
            const todayScans = scanHistory.filter(item =>
                new Date(item.timestamp).toDateString() === today
            ).length;
            document.getElementById('today-scan-count').textContent = todayScans;
        }

        function clearHistory() {
            if (confirm('Hapus semua riwayat scan?')) {
                scanHistory = [];
                localStorage.removeItem('bibScanHistory');
                renderHistory();
                updateTodayCount();
            }
        }

        function playSuccessSound() {
            try {
                successSound.currentTime = 0;
                successSound.play().catch(() => {});
            } catch (e) {}
        }

        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(() => {});
            } else {
                document.exitFullscreen();
            }
        }
    </script>
@endsection