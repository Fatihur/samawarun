<!DOCTYPE html>
<html lang="id" class="h-full m-0 p-0">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Layar Informasi — {{ $event->name }}</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { 
            height: 100%; 
            overflow: hidden; 
            background-color: #55c4f5; /* Light blue matching the photo */
            color: #1e293b; 
            font-family: 'Inter', system-ui, sans-serif; 
        }

        .kiosk-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            width: 100vw;
            position: relative;
        }

        /* Top Header */
        .kiosk-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 2rem 4rem;
        }
        .kiosk-header .brand-placeholder {
            /* Placeholder for left logo */
            width: 80px;
        }
        .kiosk-header .event-title {
            text-align: center;
            flex: 1;
        }
        .kiosk-header .event-title h1 {
            font-size: 2.5rem;
            font-weight: 900;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .kiosk-header .exit-btn {
            padding: 0.5rem 1rem;
            background: rgba(0,0,0,0.1);
            border-radius: 0.5rem;
            color: #1e293b;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s;
        }
        .kiosk-header .exit-btn:hover { background: rgba(0,0,0,0.2); }

        /* Main Content Center */
        .kiosk-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem;
        }

        /* Default / Idle State */
        .idle-state {
            animation: pulse-glow 3s ease-in-out infinite;
        }
        .idle-state h2 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1rem;
            color: #0f172a;
        }
        .idle-state p {
            font-size: 1.5rem;
            color: #334155;
        }

        /* Participant Info (Match Photo) */
        .participant-info {
            display: none;
            flex-direction: column;
            align-items: center;
            gap: 1.5rem;
            animation: zoomIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .participant-info.active { display: flex; }

        .info-bib {
            font-size: 4rem;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: 0.05em;
            line-height: 1;
        }
        
        .info-name {
            font-size: 6rem;
            font-weight: 800;
            color: #ef4444; /* Red color from photo */
            line-height: 1.1;
            max-width: 90vw;
            word-wrap: break-word;
            text-transform: capitalize;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }

        .info-category {
            font-size: 2rem;
            font-weight: 600;
            color: #334155;
            margin-top: 1rem;
        }

        .info-extra {
            margin-top: 2rem;
            display: flex;
            gap: 2rem;
            background: rgba(255,255,255,0.4);
            padding: 1rem 2rem;
            border-radius: 1rem;
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
        }

        /* Not found */
        .not-found {
            display: none;
            text-align: center;
        }
        .not-found.active { display: block; }
        .not-found h2 {
            font-size: 3rem;
            font-weight: 800;
            color: #ef4444;
            margin-bottom: 1rem;
        }
        .not-found p { font-size: 1.5rem; color: #1e293b; }

        /* Scanner Picture-in-Picture (Bottom Right) */
        .scanner-pip {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 300px;
            background: #000;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            border: 4px solid #fff;
            aspect-ratio: 4/3;
            z-index: 50;
        }
        .scanner-pip::before {
            content: "SCANNER AKTIF";
            position: absolute;
            top: 0.5rem;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0,0,0,0.6);
            color: #fff;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.7rem;
            font-weight: bold;
            z-index: 60;
            pointer-events: none;
        }
        #kiosk-scanner { width: 100%; height: 100%; }

        /* Countdown logic */
        .countdown-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            height: 6px;
            background: #ef4444;
            transition: width 0.1s linear;
        }

        @keyframes zoomIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
        @keyframes pulse-glow {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
    </style>
</head>
<body>
    <div class="kiosk-container">
        {{-- Header --}}
        <div class="kiosk-header">
            <div class="brand-placeholder"></div>
            <div class="event-title">
                <h1>{{ $event->name }}</h1>
            </div>
            <a href="{{ route('admin.bib-scan.index', ['event_id' => $event->id]) }}" class="exit-btn">Keluar</a>
        </div>

        {{-- Main --}}
        <div class="kiosk-main">
            {{-- Idle --}}
            <div class="idle-state" id="idle-state">
                <h2>Siap Melayani</h2>
                <p>Silakan scan barcode pada BIB Anda pada kamera di sudut layar.</p>
            </div>

            {{-- Info Panel (Like Photo) --}}
            <div class="participant-info" id="participant-info">
                <div class="info-bib" id="info-bib">00000</div>
                <div class="info-name" id="info-name">NAMA LENGKAP</div>
                <div class="info-category" id="info-category">Category -</div>
                
                <div class="info-extra">
                    <div>Jersey: <span id="info-jersey">-</span></div>
                    <div>&bull;</div>
                    <div>Status: <span id="info-status">-</span></div>
                </div>
            </div>

            {{-- Not found --}}
            <div class="not-found" id="not-found">
                <h2>TIDAK DITEMUKAN</h2>
                <p>Nomor BIB <strong id="nf-bib"></strong> tidak terdaftar di event ini.</p>
            </div>
        </div>
    </div>

    {{-- Camera Picture-in-Picture --}}
    <div class="scanner-pip">
        <div id="kiosk-scanner"></div>
    </div>

    {{-- Countdown --}}
    <div class="countdown-bar" id="countdown-bar" style="width: 0%;"></div>

    <script src="https://unpkg.com/html5-qrcode" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const EVENT_ID = {{ $event->id }};
            const LOOKUP_URL = '{{ route("admin.bib-scan.kiosk.lookup") }}';
            const DISPLAY_DURATION = 15; // seconds (longer so they can take photos)

            let html5QrCode = null;
            let isScanning = false;
            let displayTimer = null;
            let countdownInterval = null;

            const idleState = document.getElementById('idle-state');
            const participantInfo = document.getElementById('participant-info');
            const notFound = document.getElementById('not-found');
            const countdownBar = document.getElementById('countdown-bar');

            function showState(state) {
                idleState.style.display = state === 'idle' ? 'block' : 'none';
                participantInfo.className = 'participant-info' + (state === 'found' ? ' active' : '');
                notFound.className = 'not-found' + (state === 'not-found' ? ' active' : '');
                countdownBar.style.width = '0%';
            }

            function displayParticipant(data) {
                document.getElementById('info-bib').textContent = data.bib_number;
                document.getElementById('info-name').textContent = data.name;
                document.getElementById('info-category').textContent = 'Category ' + data.distance_category;
                document.getElementById('info-jersey').textContent = data.jersey_size;
                document.getElementById('info-status').textContent = data.status;

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
                const step = 50; // ms
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
                    setTimeout(startScanner, 1000);
                    return;
                }

                html5QrCode = html5QrCode || new Html5Qrcode('kiosk-scanner');

                try {
                    await html5QrCode.start(
                        { facingMode: 'environment' },
                        {
                            fps: 10,
                            aspectRatio: 1.333334,
                            qrbox: { width: 200, height: 200 }, // Square box for easier aiming
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
                    console.error("Camera error", error);
                }
            }

            // ESC key to exit
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    window.location.href = '{{ route("admin.bib-scan.index", ["event_id" => $event->id]) }}';
                }
                
                // Optional: Allow keyboard input for barcode scanner guns
                // This resets timer if typing happens
                clearTimeout(window.barcodeScannerTimer);
                window.barcodeScannerTimer = setTimeout(() => {
                    window.barcodeScannerBuffer = '';
                }, 100);
                
                if (e.key === 'Enter' && window.barcodeScannerBuffer) {
                    stopScanner().then(() => lookupBib(window.barcodeScannerBuffer));
                    window.barcodeScannerBuffer = '';
                } else if (e.key.length === 1) { // Normal character
                    window.barcodeScannerBuffer = (window.barcodeScannerBuffer || '') + e.key;
                }
            });

            // Auto-start scanner
            showState('idle');
            startScanner();
        });
    </script>
</body>
</html>
