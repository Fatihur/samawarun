<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $event->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #ffffff;
            overflow: hidden;
        }

        .container {
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        /* Timer */
        .timer-display {
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: #94a3b8;
            font-variant-numeric: tabular-nums;
        }

        .timer-display.active {
            color: #3b82f6;
        }

        /* Scanner Section */
        .scanner-section {
            text-align: center;
            width: 100%;
            max-width: 500px;
        }

        .scanner-section.hidden {
            display: none;
        }

        .event-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 3rem;
        }

        .scanner-box {
            width: 100%;
            aspect-ratio: 1;
            max-width: 400px;
            margin: 0 auto;
            border: 3px dashed #cbd5e1;
            border-radius: 1.5rem;
            overflow: hidden;
            position: relative;
            background: #f8fafc;
        }

        #kiosk-scanner {
            width: 100%;
            height: 100%;
        }

        .scanner-label {
            margin-top: 2rem;
            font-size: 1.125rem;
            color: #64748b;
        }

        /* Info Section */
        .info-section {
            display: none;
            text-align: center;
            animation: fadeIn 0.5s ease;
        }

        .info-section.active {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2rem;
        }

        .info-bib {
            font-size: 4rem;
            font-weight: 800;
            color: #3b82f6;
            line-height: 1;
        }

        .info-name {
            font-size: 6rem;
            font-weight: 900;
            color: #1e293b;
            line-height: 1.1;
            text-transform: uppercase;
            max-width: 90vw;
            word-wrap: break-word;
        }

        .info-category {
            font-size: 3rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
        }

        /* Not Found */
        .not-found {
            display: none;
            text-align: center;
            animation: shake 0.5s ease;
        }

        .not-found.active {
            display: block;
        }

        .not-found h2 {
            font-size: 4rem;
            font-weight: 800;
            color: #dc2626;
            margin-bottom: 1rem;
        }

        .not-found p {
            font-size: 2rem;
            color: #64748b;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        /* Corner hint */
        .corner-hint {
            position: fixed;
            bottom: 1rem;
            left: 1rem;
            font-size: 0.875rem;
            color: #cbd5e1;
        }
    </style>
</head>
<body>
    <div class="timer-display" id="timer-display">30</div>

    <div class="container">
        <!-- Scanner Section -->
        <div class="scanner-section" id="scanner-section">
            <h1 class="event-title">{{ $event->name }}</h1>
            <div class="scanner-box">
                <div id="kiosk-scanner"></div>
            </div>
            <p class="scanner-label">Arahkan barcode BIB ke area di atas</p>
        </div>

        <!-- Info Section -->
        <div class="info-section" id="info-section">
            <div class="info-bib" id="info-bib">-</div>
            <div class="info-name" id="info-name">-</div>
            <div class="info-category" id="info-category">-</div>
        </div>

        <!-- Not Found -->
        <div class="not-found" id="not-found">
            <h2>Peserta Tidak Ditemukan</h2>
            <p>Nomor BIB <strong id="nf-bib"></strong> tidak terdaftar</p>
        </div>
    </div>

    <div class="corner-hint">{{ $event->name }}</div>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        const EVENT_ID = {{ $event->id }};
        const LOOKUP_URL = '{{ route("admin.bib-scan.kiosk.lookup") }}';
        const DISPLAY_DURATION = 30; // seconds

        const scannerSection = document.getElementById('scanner-section');
        const infoSection = document.getElementById('info-section');
        const notFound = document.getElementById('not-found');
        const timerDisplay = document.getElementById('timer-display');

        let html5QrCode = null;
        let displayTimer = null;
        let countdownInterval = null;

        async function startScanner() {
            if (typeof Html5Qrcode === 'undefined') {
                setTimeout(startScanner, 500);
                return;
            }

            html5QrCode = new Html5Qrcode('kiosk-scanner');

            try {
                await html5QrCode.start(
                    { facingMode: 'environment' },
                    {
                        fps: 10,
                        qrbox: { width: 300, height: 300 }
                    },
                    (decodedText) => {
                        const bib = decodedText.trim().toUpperCase();
                        lookupBib(bib);
                    },
                    () => {}
                );
            } catch (error) {
                console.error('Camera error:', error);
            }
        }

        async function lookupBib(bibNumber) {
            try {
                const url = `${LOOKUP_URL}?event_id=${EVENT_ID}&bib_number=${encodeURIComponent(bibNumber)}`;
                const res = await fetch(url);
                const data = await res.json();

                if (data.found) {
                    showInfo(data);
                } else {
                    showNotFound(data.bib_number);
                }
            } catch (err) {
                showNotFound(bibNumber);
            }
        }

        function startCountdown() {
            let remaining = DISPLAY_DURATION;
            timerDisplay.textContent = remaining;
            timerDisplay.classList.add('active');

            clearInterval(countdownInterval);
            countdownInterval = setInterval(() => {
                remaining--;
                timerDisplay.textContent = remaining;
                if (remaining <= 0) {
                    clearInterval(countdownInterval);
                }
            }, 1000);
        }

        function stopCountdown() {
            clearInterval(countdownInterval);
            timerDisplay.classList.remove('active');
            timerDisplay.textContent = DISPLAY_DURATION;
        }

        function showInfo(data) {
            // Stop scanner
            if (html5QrCode) {
                html5QrCode.stop().then(() => {
                    html5QrCode.clear();
                }).catch(() => {});
            }

            // Hide scanner, show info
            scannerSection.classList.add('hidden');
            notFound.classList.remove('active');
            infoSection.classList.add('active');

            // Fill data (only BIB, name, category)
            document.getElementById('info-bib').textContent = data.bib_number;
            document.getElementById('info-name').textContent = data.name;
            document.getElementById('info-category').textContent = data.distance_category;

            // Start countdown
            startCountdown();

            // Auto reset after delay
            clearTimeout(displayTimer);
            displayTimer = setTimeout(() => {
                resetToScanner();
            }, DISPLAY_DURATION * 1000);
        }

        function showNotFound(bibNumber) {
            // Stop scanner
            if (html5QrCode) {
                html5QrCode.stop().then(() => {
                    html5QrCode.clear();
                }).catch(() => {});
            }

            // Hide scanner, show not found
            scannerSection.classList.add('hidden');
            infoSection.classList.remove('active');
            notFound.classList.add('active');

            document.getElementById('nf-bib').textContent = bibNumber;

            // Start countdown
            startCountdown();

            // Auto reset after delay
            clearTimeout(displayTimer);
            displayTimer = setTimeout(() => {
                resetToScanner();
            }, DISPLAY_DURATION * 1000);
        }

        function resetToScanner() {
            infoSection.classList.remove('active');
            notFound.classList.remove('active');
            scannerSection.classList.remove('hidden');

            stopCountdown();

            // Restart scanner
            setTimeout(() => {
                startScanner();
            }, 500);
        }

        // Auto-start
        startScanner();
    </script>
</body>
</html>
