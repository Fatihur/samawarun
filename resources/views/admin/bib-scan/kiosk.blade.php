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
            background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 50%, #3a7ca5 100%);
            color: #ffffff;
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
            padding: 1.5rem 3rem;
            background: rgba(0,0,0,0.2);
            backdrop-filter: blur(10px);
        }
        .kiosk-header .brand-placeholder {
            width: 60px;
            height: 60px;
            background: rgba(255,255,255,0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .kiosk-header .event-title {
            text-align: center;
            flex: 1;
        }
        .kiosk-header .event-title h1 {
            font-size: 2rem;
            font-weight: 800;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        .kiosk-header .exit-btn {
            padding: 0.75rem 1.5rem;
            background: rgba(255,255,255,0.15);
            border-radius: 0.75rem;
            color: #ffffff;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .kiosk-header .exit-btn:hover {
            background: rgba(255,255,255,0.25);
        }

        /* Main Content Center */
        .kiosk-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem;
            position: relative;
        }

        /* Default / Idle State */
        .idle-state {
            animation: pulse-glow 3s ease-in-out infinite;
        }
        .idle-state h2 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            color: #ffffff;
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        .idle-state p {
            font-size: 1.5rem;
            color: rgba(255,255,255,0.8);
        }
        .idle-state .scan-icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 2rem;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid rgba(255,255,255,0.3);
        }
        .idle-state .scan-icon svg {
            width: 60px;
            height: 60px;
            color: rgba(255,255,255,0.9);
        }

        /* Participant Info */
        .participant-info {
            display: none;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            animation: zoomIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            background: rgba(255,255,255,0.95);
            border-radius: 2rem;
            padding: 3rem 5rem;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
            max-width: 90vw;
        }
        .participant-info.active { display: flex; }
        .participant-info .info-bib {
            font-size: 3rem;
            font-weight: 700;
            color: #3b82f6;
            letter-spacing: 0.1em;
            background: #dbeafe;
            padding: 0.5rem 2rem;
            border-radius: 1rem;
        }
        .participant-info .info-name {
            font-size: 5rem;
            font-weight: 900;
            color: #1e293b;
            line-height: 1.1;
            max-width: 80vw;
            word-wrap: break-word;
        }
        .participant-info .info-category {
            font-size: 2rem;
            font-weight: 600;
            color: #64748b;
        }
        .participant-info .info-extra {
            margin-top: 1rem;
            display: flex;
            gap: 2rem;
            background: #f1f5f9;
            padding: 1rem 2rem;
            border-radius: 1rem;
            font-size: 1.25rem;
            font-weight: 600;
            color: #475569;
        }
        .participant-info .info-status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 1rem;
            font-weight: 700;
        }
        .info-status.verified { background: #dcfce7; color: #166534; }
        .info-status.pending { background: #fef3c7; color: #92400e; }
        .info-status.rejected { background: #fee2e2; color: #991b1b; }

        /* Not found */
        .not-found {
            display: none;
            text-align: center;
            background: rgba(255,255,255,0.95);
            border-radius: 2rem;
            padding: 3rem 5rem;
            animation: shake 0.5s ease-in-out;
        }
        .not-found.active { display: block; }
        .not-found h2 {
            font-size: 3rem;
            font-weight: 800;
            color: #dc2626;
            margin-bottom: 1rem;
        }
        .not-found p { font-size: 1.5rem; color: #475569; }

        /* Scanner Picture-in-Picture */
        .scanner-pip {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 350px;
            background: #000;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            border: 3px solid rgba(255,255,255,0.3);
            aspect-ratio: 4/3;
            z-index: 50;
        }
        .scanner-pip::before {
            content: "SCANNER AKTIF";
            position: absolute;
            top: 0.75rem;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(16, 185, 129, 0.9);
            color: #fff;
            padding: 0.35rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.75rem;
            font-weight: bold;
            z-index: 60;
            pointer-events: none;
        }
        #kiosk-scanner { width: 100%; height: 100%; }

        /* Manual Input Modal */
        .manual-input-btn {
            position: fixed;
            bottom: 2rem;
            left: 2rem;
            padding: 1rem 1.5rem;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 1rem;
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            backdrop-filter: blur(10px);
        }
        .manual-input-btn:hover {
            background: rgba(255,255,255,0.25);
        }

        .manual-input-modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.8);
            z-index: 100;
            align-items: center;
            justify-content: center;
        }
        .manual-input-modal.active { display: flex; }
        .manual-input-modal .modal-content {
            background: #fff;
            border-radius: 1.5rem;
            padding: 2rem;
            width: 90%;
            max-width: 500px;
        }
        .manual-input-modal h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1rem;
        }
        .manual-input-modal input {
            width: 100%;
            padding: 1rem 1.5rem;
            font-size: 1.5rem;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
            border: 2px solid #e2e8f0;
            border-radius: 1rem;
            margin-bottom: 1rem;
        }
        .manual-input-modal input:focus {
            outline: none;
            border-color: #3b82f6;
        }
        .manual-input-modal .btn-group {
            display: flex;
            gap: 1rem;
        }
        .manual-input-modal button {
            flex: 1;
            padding: 1rem;
            border-radius: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
        }
        .manual-input-modal .btn-primary {
            background: #3b82f6;
            color: #fff;
        }
        .manual-input-modal .btn-secondary {
            background: #e2e8f0;
            color: #475569;
        }

        /* Countdown bar */
        .countdown-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            height: 8px;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6);
            transition: width 0.1s linear;
        }

        /* Stats overlay */
        .stats-overlay {
            position: fixed;
            top: 2rem;
            right: 2rem;
            background: rgba(0,0,0,0.3);
            backdrop-filter: blur(10px);
            padding: 1rem 1.5rem;
            border-radius: 1rem;
            font-size: 0.875rem;
        }
        .stats-overlay .stat-item {
            display: flex;
            justify-content: space-between;
            gap: 2rem;
            margin-bottom: 0.5rem;
        }
        .stats-overlay .stat-item:last-child { margin-bottom: 0; }
        .stats-overlay .stat-value {
            font-weight: 700;
            color: #34d399;
        }

        @keyframes zoomIn {
            from { opacity: 0; transform: scale(0.8) translateY(20px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
        @keyframes pulse-glow {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-10px); }
            40%, 80% { transform: translateX(10px); }
        }
    </style>
</head>
<body>
    <div class="kiosk-container">
        {{-- Header --}}
        <div class="kiosk-header">
            <div class="brand-placeholder">
                <svg class="w-10 h-10 text-white/50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="event-title">
                <h1>{{ $event->name }}</h1>
            </div>
            <a href="{{ route('admin.bib-scan.index', ['event_id' => $event->id]) }}" class="exit-btn">
                ✕ Keluar
            </a>
        </div>

        {{-- Main --}}
        <div class="kiosk-main">
            {{-- Idle --}}
            <div class="idle-state" id="idle-state">
                <div class="scan-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5zM13.5 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5z" />
                    </svg>
                </div>
                <h2>Siap Melayani</h2>
                <p>Arahkan barcode BIB ke kamera di sudut kanan bawah</p>
            </div>

            {{-- Info Panel --}}
            <div class="participant-info" id="participant-info">
                <div class="info-bib" id="info-bib">00000</div>
                <div class="info-name" id="info-name">NAMA LENGKAP</div>
                <div class="info-category" id="info-category">Category -</div>
                <span class="info-status" id="info-status">-</span>
                <div class="info-extra">
                    <div>Jersey: <span id="info-jersey">-</span></div>
                    <div>|</div>
                    <div>Finish: <span id="info-finish">-</span></div>
                </div>
            </div>

            {{-- Not found --}}
            <div class="not-found" id="not-found">
                <h2>✕ TIDAK DITEMUKAN</h2>
                <p>Nomor BIB <strong id="nf-bib"></strong> tidak terdaftar</p>
            </div>
        </div>
    </div>

    {{-- Stats Overlay --}}
    <div class="stats-overlay">
        <div class="stat-item">
            <span>Scan Hari Ini:</span>
            <span class="stat-value" id="today-count">0</span>
        </div>
        <div class="stat-item">
            <span>Total Valid:</span>
            <span class="stat-value" id="valid-count">0</span>
        </div>
    </div>

    {{-- Camera Picture-in-Picture --}}
    <div class="scanner-pip">
        <div id="kiosk-scanner"></div>
    </div>

    {{-- Manual Input Button --}}
    <button class="manual-input-btn" onclick="showManualInput()">
        ⌨ Input Manual
    </button>

    {{-- Manual Input Modal --}}
    <div class="manual-input-modal" id="manual-modal">
        <div class="modal-content">
            <h3>Input Nomor BIB</h3>
            <input type="text" id="manual-bib-input" placeholder="CONTOH: 10K-001" autofocus>
            <div class="btn-group">
                <button class="btn-secondary" onclick="hideManualInput()">Batal</button>
                <button class="btn-primary" onclick="submitManualBib()">Cari</button>
            </div>
        </div>
    </div>

    {{-- Countdown --}}
    <div class="countdown-bar" id="countdown-bar" style="width: 0%;"></div>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        const EVENT_ID = {{ $event->id }};
        const LOOKUP_URL = '{{ route("admin.bib-scan.kiosk.lookup") }}';
        const DISPLAY_DURATION = 10; // seconds

        let html5QrCode = null;
        let isScanning = false;
        let displayTimer = null;
        let countdownInterval = null;
        let scanHistory = JSON.parse(localStorage.getItem('kioskScanHistory_' + EVENT_ID) || '[]');

        const idleState = document.getElementById('idle-state');
        const participantInfo = document.getElementById('participant-info');
        const notFound = document.getElementById('not-found');
        const countdownBar = document.getElementById('countdown-bar');

        // Update stats
        updateStats();

        function updateStats() {
            const today = new Date().toDateString();
            const todayScans = scanHistory.filter(s => new Date(s.time).toDateString() === today);
            document.getElementById('today-count').textContent = todayScans.length;
            document.getElementById('valid-count').textContent = todayScans.filter(s => s.found).length;
        }

        function addToHistory(bib, found) {
            scanHistory.unshift({ bib, found, time: new Date().toISOString() });
            if (scanHistory.length > 100) scanHistory.pop();
            localStorage.setItem('kioskScanHistory_' + EVENT_ID, JSON.stringify(scanHistory));
            updateStats();
        }

        function showState(state) {
            idleState.style.display = state === 'idle' ? 'block' : 'none';
            participantInfo.classList.toggle('active', state === 'found');
            notFound.classList.toggle('active', state === 'not-found');
            countdownBar.style.width = '0%';
        }

        function displayParticipant(data) {
            document.getElementById('info-bib').textContent = data.bib_number;
            document.getElementById('info-name').textContent = data.name;
            document.getElementById('info-category').textContent = 'Category ' + data.distance_category;
            document.getElementById('info-jersey').textContent = data.jersey_size;
            document.getElementById('info-finish').textContent = data.finish_time;

            const statusEl = document.getElementById('info-status');
            statusEl.textContent = data.status;
            statusEl.className = 'info-status ' + (data.status === 'Verified' ? 'verified' : 'pending');

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
            const step = 50;
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

                addToHistory(bibNumber, data.found);

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
                setTimeout(startScanner, 500);
                return;
            }

            html5QrCode = new Html5Qrcode('kiosk-scanner');

            try {
                await html5QrCode.start(
                    { facingMode: 'environment' },
                    { fps: 10, aspectRatio: 1.333, qrbox: { width: 250, height: 180 } },
                    (decodedText) => {
                        const bib = decodedText.trim().toUpperCase();
                        stopScanner().then(() => lookupBib(bib));
                    },
                    () => {}
                );
                isScanning = true;
            } catch (error) {
                console.error("Camera error", error);
            }
        }

        // Manual Input
        function showManualInput() {
            stopScanner();
            document.getElementById('manual-modal').classList.add('active');
            document.getElementById('manual-bib-input').value = '';
            document.getElementById('manual-bib-input').focus();
        }

        function hideManualInput() {
            document.getElementById('manual-modal').classList.remove('active');
            startScanner();
        }

        function submitManualBib() {
            const bib = document.getElementById('manual-bib-input').value.trim().toUpperCase();
            if (bib) {
                hideManualInput();
                lookupBib(bib);
            }
        }

        document.getElementById('manual-bib-input').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') submitManualBib();
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (document.getElementById('manual-modal').classList.contains('active')) {
                    hideManualInput();
                } else {
                    window.location.href = '{{ route("admin.bib-scan.index", ["event_id" => $event->id]) }}';
                }
            }
            if (e.key === 'Enter' && !document.getElementById('manual-modal').classList.contains('active')) {
                showManualInput();
            }
        });

        // Auto-start
        showState('idle');
        startScanner();
    </script>
</body>
</html>
