<!DOCTYPE html>
<html lang="id" class="h-full m-0 p-0">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Scan BIB — {{ $event->name }}</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; overflow: hidden; background: #0f172a; color: #f8fafc; font-family: 'Inter', system-ui, sans-serif; }

        .kiosk-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            width: 100vw;
        }

        /* Top bar — event name & branding */
        .kiosk-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-bottom: 1px solid rgba(255,255,255,0.08);
            flex-shrink: 0;
        }
        .kiosk-header .event-name {
            font-size: 1.25rem;
            font-weight: 700;
            color: #f8fafc;
            letter-spacing: 0.02em;
        }
        .kiosk-header .brand {
            font-size: 0.875rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.15em;
        }
        .kiosk-header .brand span { color: #f97316; }
        .kiosk-header .exit-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 0.5rem;
            color: #94a3b8;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }
        .kiosk-header .exit-btn:hover { background: rgba(255,255,255,0.1); color: #f8fafc; }

        /* Main content */
        .kiosk-main {
            flex: 1;
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 3rem;
            padding: 3rem;
            max-width: 1600px;
            margin: 0 auto;
            width: 100%;
            align-items: center;
        }

        /* Scanner Panel */
        .scanner-panel {
            width: 100%;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1.5rem;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .scanner-panel h3 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #f8fafc;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .scanner-panel h3 svg { width: 24px; height: 24px; color: #f97316; }
        .scanner-container {
            width: 100%;
            border-radius: 1rem;
            overflow: hidden;
            background: #000;
            aspect-ratio: 4/3;
            border: 2px dashed rgba(249, 115, 22, 0.3);
            position: relative;
        }
        #kiosk-scanner { width: 100%; height: 100%; }

        /* Info panel */
        .info-panel {
            width: 100%;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1.5rem;
            padding: 2.5rem;
            animation: fadeInUp 0.4s ease-out;
            min-height: 500px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* Idle state — waiting for scan */
        .idle-state {
            text-align: center;
            animation: pulse-glow 3s ease-in-out infinite;
        }
        .idle-state .icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 2rem;
            background: rgba(249, 115, 22, 0.1);
            border: 2px dashed rgba(249, 115, 22, 0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .idle-state .icon svg {
            width: 56px;
            height: 56px;
            color: #f97316;
        }
        .idle-state h2 {
            font-size: 2rem;
            font-weight: 800;
            color: #f8fafc;
            margin-bottom: 0.75rem;
        }
        .idle-state p {
            font-size: 1.125rem;
            color: #94a3b8;
            line-height: 1.6;
            max-width: 400px;
            margin: 0 auto;
        }

        /* Participant info */
        .participant-info { display: none; }
        .participant-info.active { display: block; }

        .participant-name {
            font-size: 2.5rem;
            font-weight: 800;
            color: #f8fafc;
            margin-bottom: 0.25rem;
            line-height: 1.2;
        }
        .participant-bib {
            font-size: 1.5rem;
            font-weight: 700;
            color: #f97316;
            font-family: ui-monospace, monospace;
            margin-bottom: 2rem;
            letter-spacing: 0.1em;
        }
        .participant-bib .label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            font-family: 'Inter', system-ui, sans-serif;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            display: block;
            margin-bottom: 0.25rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem 2rem;
        }
        .info-grid .info-item.full { grid-column: 1 / -1; }

        .info-item .label {
            font-size: 0.7rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            margin-bottom: 0.25rem;
        }
        .info-item .value {
            font-size: 1.125rem;
            font-weight: 500;
            color: #e2e8f0;
            line-height: 1.5;
        }

        .divider {
            height: 1px;
            background: rgba(255,255,255,0.06);
            margin: 1.5rem 0;
        }

        /* Status badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.375rem 1rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 700;
        }
        .status-badge.verified { background: rgba(16, 185, 129, 0.15); color: #34d399; }
        .status-badge.pending { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }
        .status-badge.rejected { background: rgba(239, 68, 68, 0.15); color: #f87171; }
        .status-badge .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: currentColor;
        }

        /* Not found state */
        .not-found {
            text-align: center;
            display: none;
        }
        .not-found.active { display: block; }
        .not-found h2 {
            font-size: 1.75rem;
            font-weight: 800;
            color: #fbbf24;
            margin-bottom: 0.5rem;
        }
        .not-found p {
            font-size: 1.125rem;
            color: #94a3b8;
        }
        .not-found .bib-code {
            font-family: ui-monospace, monospace;
            font-weight: 700;
            color: #f8fafc;
            background: rgba(255,255,255,0.08);
            padding: 0.125rem 0.5rem;
            border-radius: 0.25rem;
        }

        /* Countdown bar */
        .countdown-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            height: 4px;
            background: #f97316;
            transition: width 0.1s linear;
            z-index: 100;
        }

        /* Scanner status indicator */
        .scanner-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
            color: #64748b;
        }
        .scanner-status .dot-live {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #22c55e;
            animation: blink 1.5s ease-in-out infinite;
        }
        .scanner-status.error .dot-live { background: #ef4444; animation: none; }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes pulse-glow {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.85; }
        }
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
    </style>
</head>
<body>
    <div class="kiosk-container">
        {{-- Header --}}
        <div class="kiosk-header">
            <div>
                <div class="brand">Samawa<span>Run</span></div>
                <div class="event-name">{{ $event->name }}</div>
            </div>
            <div style="display: flex; align-items: center; gap: 1.5rem;">
                <div class="scanner-status" id="scanner-status">
                    <div class="dot-live"></div>
                    <span>Scanner aktif</span>
                </div>
                <a href="{{ route('admin.bib-scan.index', ['event_id' => $event->id]) }}" class="exit-btn">
                    ESC &middot; Keluar
                </a>
            </div>
        </div>

        {{-- Main content --}}
        <div class="kiosk-main">
            {{-- Kamera Scanner --}}
            <div class="scanner-panel">
                <h3>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                    </svg>
                    Arahkan Barcode Kemari
                </h3>
                <div class="scanner-container">
                    <div id="kiosk-scanner"></div>
                </div>
            </div>

            {{-- Info Panel --}}
            <div class="info-panel">
                {{-- Idle state --}}
                <div class="idle-state" id="idle-state">
                    <div class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5Z" />
                        </svg>
                    </div>
                    <h2>Scan BIB Anda</h2>
                    <p>Arahkan barcode pada BIB Anda ke kamera untuk melihat informasi peserta</p>
                </div>

                {{-- Participant info --}}
                <div class="participant-info" id="participant-info">
                    <div class="participant-bib">
                        <span class="label">Nomor BIB</span>
                        <span id="info-bib">—</span>
                    </div>
                    <div class="participant-name" id="info-name">—</div>
                    <div style="margin-top: 0.75rem;" id="info-status-wrap"></div>

                    <div class="divider"></div>

                    <div class="info-grid">
                        <div class="info-item">
                            <div class="label">Kategori</div>
                            <div class="value" id="info-category">—</div>
                        </div>
                        <div class="info-item">
                            <div class="label">Jersey</div>
                            <div class="value" id="info-jersey">—</div>
                        </div>
                        <div class="info-item full">
                            <div class="label">Event</div>
                            <div class="value" id="info-event">—</div>
                        </div>
                        <div class="info-item full">
                            <div class="label">Email</div>
                            <div class="value" id="info-email">—</div>
                        </div>
                        <div class="info-item full">
                            <div class="label">Telepon</div>
                            <div class="value" id="info-phone">—</div>
                        </div>
                        <div class="info-item full">
                            <div class="label">Kontak Darurat</div>
                            <div class="value" id="info-emergency">—</div>
                        </div>
                        <div class="info-item full">
                            <div class="label">Waktu Finish</div>
                            <div class="value" id="info-finish">—</div>
                        </div>
                    </div>
                </div>

                {{-- Not found state --}}
                <div class="not-found" id="not-found">
                    <h2>BIB Tidak Ditemukan</h2>
                    <p>Nomor BIB <span class="bib-code" id="nf-bib">—</span> tidak ditemukan pada event ini.</p>
                </div>
            </div>
        </div>

        {{-- Countdown bar --}}
        <div class="countdown-bar" id="countdown-bar" style="width: 0%;"></div>
    </div>

    <script src="https://unpkg.com/html5-qrcode" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const EVENT_ID = {{ $event->id }};
            const LOOKUP_URL = '{{ route("admin.bib-scan.kiosk.lookup") }}';
            const DISPLAY_DURATION = 10; // seconds

            let html5QrCode = null;
            let isScanning = false;
            let displayTimer = null;
            let countdownInterval = null;

            const idleState = document.getElementById('idle-state');
            const participantInfo = document.getElementById('participant-info');
            const notFound = document.getElementById('not-found');
            const countdownBar = document.getElementById('countdown-bar');
            const scannerStatus = document.getElementById('scanner-status');

            function showState(state) {
                idleState.style.display = state === 'idle' ? 'block' : 'none';
                participantInfo.className = 'participant-info' + (state === 'found' ? ' active' : '');
                notFound.className = 'not-found' + (state === 'not-found' ? ' active' : '');
                countdownBar.style.width = '0%';
            }

            function statusBadge(status) {
                const s = status.toLowerCase();
                const cls = s === 'verified' ? 'verified' : s === 'rejected' ? 'rejected' : 'pending';
                return `<span class="status-badge ${cls}"><span class="dot"></span>${status}</span>`;
            }

            function displayParticipant(data) {
                document.getElementById('info-name').textContent = data.name;
                document.getElementById('info-bib').textContent = data.bib_number;
                document.getElementById('info-status-wrap').innerHTML = statusBadge(data.status);
                document.getElementById('info-category').textContent = data.distance_category;
                document.getElementById('info-jersey').textContent = data.jersey_size;
                document.getElementById('info-event').textContent = data.event_name;
                document.getElementById('info-email').textContent = data.email;
                document.getElementById('info-phone').textContent = data.phone;
                document.getElementById('info-emergency').textContent = data.emergency_contact;
                document.getElementById('info-finish').textContent = data.finish_time;

                showState('found');
                startCountdown();
            }

            function displayNotFound(bibNumber) {
                document.getElementById('nf-bib').textContent = bibNumber;
                showState('not-found');
                startCountdown();
            }

            function startCountdown() {
                clearTimeout(displayTimer);
                clearInterval(countdownInterval);

                let elapsed = 0;
                const step = 100; // ms
                const total = DISPLAY_DURATION * 1000;

                countdownInterval = setInterval(() => {
                    elapsed += step;
                    const pct = Math.min((elapsed / total) * 100, 100);
                    countdownBar.style.width = pct + '%';
                }, step);

                displayTimer = setTimeout(() => {
                    clearInterval(countdownInterval);
                    countdownBar.style.width = '0%';
                    showState('idle');
                    startScanner();
                }, total);
            }

            async function lookupBib(bibNumber) {
                try {
                    const url = `${LOOKUP_URL}?event_id=${EVENT_ID}&bib_number=${encodeURIComponent(bibNumber)}`;
                    const res = await fetch(url);
                    const data = await res.json();

                    if (data.found) {
                        displayParticipant(data);
                    } else {
                        displayNotFound(data.bib_number);
                    }
                } catch (err) {
                    console.error('Lookup error:', err);
                    displayNotFound(bibNumber);
                }
            }

            async function stopScanner() {
                if (!html5QrCode || !isScanning) return;
                try {
                    await html5QrCode.stop();
                    await html5QrCode.clear();
                } catch (e) {}
                isScanning = false;
            }

            async function startScanner() {
                if (isScanning) return;
                if (typeof Html5Qrcode === 'undefined') {
                    scannerStatus.innerHTML = '<div class="dot-live" style="background:#ef4444;animation:none"></div><span>Library belum siap</span>';
                    setTimeout(startScanner, 1000);
                    return;
                }

                html5QrCode = html5QrCode || new Html5Qrcode('kiosk-scanner');
                scannerStatus.innerHTML = '<div class="dot-live"></div><span>Scanner aktif</span>';

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
                            const bib = decodedText.trim().toUpperCase();
                            stopScanner().then(() => lookupBib(bib));
                        },
                        function () {}
                    );
                    isScanning = true;
                } catch (error) {
                    scannerStatus.className = 'scanner-status error';
                    scannerStatus.innerHTML = '<div class="dot-live"></div><span>Kamera tidak tersedia</span>';
                }
            }

            // ESC key to exit
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    window.location.href = '{{ route("admin.bib-scan.index", ["event_id" => $event->id]) }}';
                }
            });

            // Auto-start scanner
            showState('idle');
            startScanner();
        });
    </script>
</body>
</html>
