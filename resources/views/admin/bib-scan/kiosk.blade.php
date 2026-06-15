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

        /* Header Logos */
        .header-logos {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 2rem;
            padding: 1.5rem 2rem;
            background: linear-gradient(to bottom, rgba(255,255,255,1) 0%, rgba(255,255,255,0.9) 70%, rgba(255,255,255,0) 100%);
            z-index: 100;
        }

        .header-logos img {
            max-height: 60px;
            max-width: 150px;
            object-fit: contain;
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
            z-index: 101;
        }

        .timer-display.active {
            color: #3b82f6;
        }

        /* Scanner Section */
        .scanner-section {
            text-align: center;
            width: 100%;
            max-width: 600px;
            margin-top: 80px;
        }

        .scanner-section.hidden {
            display: none;
        }

        .event-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 2rem;
        }

        .bib-input {
            width: 100%;
            max-width: 560px;
            margin: 0 auto;
            padding: 1.75rem 2rem;
            font-size: 3rem;
            font-weight: 800;
            text-align: center;
            border: 3px dashed #cbd5e1;
            border-radius: 1.5rem;
            background: #f8fafc;
            color: #1e293b;
            font-variant-numeric: tabular-nums;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            transition: border-color 0.2s ease, background-color 0.2s ease, box-shadow 0.2s ease;
            outline: none;
            display: block;
        }

        .bib-input:focus {
            border-color: #3b82f6;
            border-style: solid;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
        }

        .bib-input::placeholder {
            color: #94a3b8;
            font-size: 1.25rem;
            font-weight: 600;
            letter-spacing: normal;
            text-transform: none;
        }

        .scanner-status {
            margin-top: 1.5rem;
            font-size: 1.125rem;
            color: #64748b;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .scanner-status .dot {
            width: 0.625rem;
            height: 0.625rem;
            border-radius: 50%;
            background: #22c55e;
            box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.5);
            animation: pulse 1.6s ease-out infinite;
        }

        .scanner-status.error .dot {
            background: #ef4444;
            box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.5);
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.6); }
            70% { box-shadow: 0 0 0 12px rgba(34, 197, 94, 0); }
            100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
        }

        .scanner-hint {
            margin-top: 0.75rem;
            font-size: 0.875rem;
            color: #94a3b8;
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

        /* Footer Section */
        .footer-section {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            padding: 1.5rem 2rem 2rem;
            background: linear-gradient(to top, rgba(255,255,255,1) 0%, rgba(255,255,255,0.9) 70%, rgba(255,255,255,0) 100%);
            z-index: 100;
        }

        .sponsor-text {
            font-size: 0.875rem;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .footer-logos {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .footer-logos img {
            max-height: 40px;
            max-width: 120px;
            object-fit: contain;
        }

        /* Exit hint */
        .exit-hint {
            position: fixed;
            bottom: 0.5rem;
            right: 1rem;
            font-size: 0.7rem;
            color: #cbd5e1;
            opacity: 0.5;
            z-index: 101;
        }

        /* Fullscreen overlay */
        .fullscreen-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.9);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            cursor: pointer;
            text-align: center;
            padding: 2rem;
        }

        .fullscreen-overlay.hidden {
            display: none;
        }

        .fullscreen-overlay h2 {
            color: white;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .fullscreen-overlay p {
            color: #94a3b8;
            font-size: 1.125rem;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    @if($settings->kiosk_header_logos && count($settings->kiosk_header_logos) > 0)
    <div class="header-logos">
        @foreach($settings->kiosk_header_logos as $logo)
            @if($logo)
                <img src="{{ Storage::url($logo) }}" alt="Logo">
            @endif
        @endforeach
    </div>
    @endif

    <div class="timer-display" id="timer-display">30</div>

    <div class="container">
        {{-- Scanner Section --}}
        <div class="scanner-section" id="scanner-section">
            <h1 class="event-title">{{ $event->name }}</h1>

            <input
                type="text"
                id="bib-input"
                class="bib-input"
                placeholder="Arahkan scanner ke BIB..."
                autocomplete="off"
                autocapitalize="characters"
                autocorrect="off"
                spellcheck="false"
                inputmode="text"
            >

            <div class="scanner-status" id="scanner-status">
                <span class="dot"></span>
                <span id="scanner-status-text">Scanner USB siap</span>
            </div>

            <p class="scanner-hint">Scan barcode BIB atau ketik manual lalu tekan Enter</p>
        </div>

        {{-- Info Section --}}
        <div class="info-section" id="info-section">
            <div class="info-bib" id="info-bib">-</div>
            <div class="info-name" id="info-name">-</div>
            <div class="info-category" id="info-category">-</div>
        </div>

        {{-- Not Found --}}
        <div class="not-found" id="not-found">
            <h2>Peserta Tidak Ditemukan</h2>
            <p>Nomor BIB <strong id="nf-bib"></strong> tidak terdaftar</p>
        </div>
    </div>

    @if($settings->kiosk_footer_logos && count($settings->kiosk_footer_logos) > 0)
    <div class="footer-section">
        @if($settings->kiosk_sponsor_text)
            <div class="sponsor-text">{{ $settings->kiosk_sponsor_text }}</div>
        @endif
        <div class="footer-logos">
            @foreach($settings->kiosk_footer_logos as $logo)
                @if($logo)
                    <img src="{{ Storage::url($logo) }}" alt="Sponsor Logo">
                @endif
            @endforeach
        </div>
    </div>
    @endif

    <div class="exit-hint">ESC untuk keluar</div>

    {{-- Fullscreen Overlay --}}
    <div class="fullscreen-overlay" id="fullscreen-overlay">
        <h2>Mode Kiosk</h2>
        <p>Klik di mana saja untuk mulai</p>
        <p style="font-size: 0.875rem; margin-top: 1rem;">Pastikan scanner USB VSC PB-333U sudah terpasang</p>
    </div>

    <script>
        // Scanner: VSC PB-333U (USB 2D Barcode Scanner, HID Keyboard mode)
        // Konfigurasi scanner yang direkomendasikan sebelum digunakan:
        //   - Mode: HID Keyboard (default)
        //   - Suffix: CR (Enter) atau Tab - digunakan sebagai pemicu lookup
        //   - Bahasa keyboard: US/English (default)
        //   - Prefix/Code ID: Non-aktif (opsional, jika aktif akan ikut terscan)
        // Lihat barcode "Enter Configuration" di buku manual VSC PB-333U.

        const EVENT_ID = {{ $event->id }};
        const LOOKUP_URL = '{{ route("admin.bib-scan.kiosk.lookup") }}';
        const DISPLAY_DURATION = 30;

        const scannerSection = document.getElementById('scanner-section');
        const infoSection = document.getElementById('info-section');
        const notFound = document.getElementById('not-found');
        const timerDisplay = document.getElementById('timer-display');
        const bibInput = document.getElementById('bib-input');
        const scannerStatus = document.getElementById('scanner-status');
        const scannerStatusText = document.getElementById('scanner-status-text');

        let displayTimer = null;
        let countdownInterval = null;
        let scanLocked = false;

        let audioContext = null;
        function ensureAudioContext() {
            if (audioContext) return audioContext;
            const Ctor = window.AudioContext || window.webkitAudioContext;
            if (!Ctor) return null;
            audioContext = new Ctor();
            return audioContext;
        }

        function playBeep(kind) {
            const ctx = ensureAudioContext();
            if (!ctx) return;
            if (ctx.state === 'suspended') {
                ctx.resume().catch(() => {});
            }

            const now = ctx.currentTime;
            const tone = (frequency, startOffset, duration, peakGain) => {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(frequency, now + startOffset);
                gain.gain.setValueAtTime(0.0001, now + startOffset);
                gain.gain.exponentialRampToValueAtTime(peakGain, now + startOffset + 0.01);
                gain.gain.exponentialRampToValueAtTime(0.0001, now + startOffset + duration);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start(now + startOffset);
                osc.stop(now + startOffset + duration);
            };

            if (kind === 'success') {
                tone(880, 0, 0.08, 0.18);
                tone(1320, 0.09, 0.12, 0.18);
            } else if (kind === 'error') {
                tone(220, 0, 0.18, 0.2);
            } else {
                tone(660, 0, 0.08, 0.15);
            }
        }

        function focusInput() {
            if (scanLocked) return;
            if (document.activeElement !== bibInput) {
                bibInput.focus({ preventScroll: true });
            }
        }

        function setScannerStatus(text, isError = false) {
            scannerStatusText.textContent = text;
            scannerStatus.classList.toggle('error', isError);
        }

        async function lookupBib(bibNumber) {
            try {
                const url = `${LOOKUP_URL}?event_id=${EVENT_ID}&bib_number=${encodeURIComponent(bibNumber)}`;
                const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = await res.json();
                return data;
            } catch (err) {
                console.error('Lookup error', err);
                return { found: false, bib_number: bibNumber };
            }
        }

        async function handleScan(rawValue) {
            if (scanLocked) return;

            const bib = (rawValue || '').trim().toUpperCase();
            if (!bib) {
                clearInput();
                return;
            }

            scanLocked = true;
            setScannerStatus('Mencari...', false);
            const data = await lookupBib(bib);

            if (data.found) {
                showInfo(data);
                playBeep('success');
            } else {
                showNotFound(data.bib_number || bib);
                playBeep('error');
            }
        }

        function clearInput() {
            bibInput.value = '';
            setScannerStatus('Scanner USB siap', false);
            scanLocked = false;
            focusInput();
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
            scannerSection.classList.add('hidden');
            notFound.classList.remove('active');
            infoSection.classList.add('active');

            document.getElementById('info-bib').textContent = data.bib_number;
            document.getElementById('info-name').textContent = data.name;
            document.getElementById('info-category').textContent = data.distance_category;

            startCountdown();

            clearTimeout(displayTimer);
            displayTimer = setTimeout(resetToScanner, DISPLAY_DURATION * 1000);
        }

        function showNotFound(bibNumber) {
            scannerSection.classList.add('hidden');
            infoSection.classList.remove('active');
            notFound.classList.add('active');

            document.getElementById('nf-bib').textContent = bibNumber;

            startCountdown();

            clearTimeout(displayTimer);
            displayTimer = setTimeout(resetToScanner, DISPLAY_DURATION * 1000);
        }

        function resetToScanner() {
            infoSection.classList.remove('active');
            notFound.classList.remove('active');
            scannerSection.classList.remove('hidden');

            stopCountdown();
            clearInput();
        }

        bibInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === 'Tab') {
                e.preventDefault();
                handleScan(bibInput.value);
            } else if (e.key === 'Escape') {
                e.preventDefault();
                window.location.href = '{{ route("admin.bib-scan.index") }}';
            }
        });

        bibInput.addEventListener('input', () => {
            if (scanLocked) return;
            const value = bibInput.value;
            if (value.length > 30) {
                bibInput.value = value.slice(0, 30);
            }
        });

        document.addEventListener('click', (e) => {
            const target = e.target;
            if (target.closest('a, button, input, select, textarea')) return;
            focusInput();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && document.activeElement !== bibInput) {
                window.location.href = '{{ route("admin.bib-scan.index") }}';
            }
        });

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) focusInput();
        });

        window.addEventListener('blur', () => {
            setTimeout(focusInput, 100);
        });

        // Fullscreen
        const fullscreenOverlay = document.getElementById('fullscreen-overlay');

        function enterFullscreen() {
            const elem = document.documentElement;
            const req = elem.requestFullscreen
                || elem.webkitRequestFullscreen
                || elem.msRequestFullscreen;
            if (req) {
                req.call(elem).catch(() => {});
            }
        }

        fullscreenOverlay.addEventListener('click', () => {
            fullscreenOverlay.classList.add('hidden');
            ensureAudioContext();
            enterFullscreen();
            focusInput();
        });

        document.addEventListener('fullscreenchange', () => {
            if (!document.fullscreenElement) {
                // Allow ESC to exit fullscreen but keep kiosk running
                setTimeout(focusInput, 200);
            }
        });

        // Initial focus
        window.addEventListener('load', () => {
            // Wait for overlay click to actually focus (avoid browser warnings)
            setScannerStatus('Klik layar untuk mengaktifkan scanner', false);
        });
    </script>
</body>
</html>
