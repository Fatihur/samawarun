@extends('layouts.admin')

@section('content')
    <div class="mb-8 flex items-center justify-between">
        <h1 class="font-display text-3xl font-bold uppercase italic text-slate-800">Dashboard</h1>
        <p class="text-sm font-medium text-slate-500">{{ now()->translatedFormat('l, d F Y') }}</p>
    </div>

    {{-- Stats Cards --}}
    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4 mb-8">
        {{-- Total Event Card --}}
        <a href="{{ route('admin.events.index') }}" class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:-translate-y-1 hover:shadow-lg">
            <div class="absolute right-0 top-0 h-24 w-24 -translate-y-8 translate-x-8 rounded-full bg-brand-50 transition-transform group-hover:scale-150"></div>
            <div class="relative flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wider text-slate-500">Total Event</p>
                    <p class="mt-2 text-4xl font-bold text-slate-800">{{ $eventCount }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-100 text-brand-600 shadow-inner">
                    <x-heroicon-o-calendar-days class="h-6 w-6" />
                </div>
            </div>
        </a>

        {{-- Total Peserta Card --}}
        <a href="{{ route('admin.participants.index') }}" class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:-translate-y-1 hover:shadow-lg">
            <div class="absolute right-0 top-0 h-24 w-24 -translate-y-8 translate-x-8 rounded-full bg-blue-50 transition-transform group-hover:scale-150"></div>
            <div class="relative flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wider text-slate-500">Total Peserta</p>
                    <p class="mt-2 text-4xl font-bold text-slate-800">{{ $participantCount }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-blue-600 shadow-inner">
                    <x-heroicon-o-user-group class="h-6 w-6" />
                </div>
            </div>
        </a>

        {{-- Pending Card --}}
        <a href="{{ route('admin.participants.index', ['status' => 'pending']) }}" class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:-translate-y-1 hover:shadow-lg hover:border-amber-200">
            <div class="absolute right-0 top-0 h-24 w-24 -translate-y-8 translate-x-8 rounded-full bg-amber-50 transition-transform group-hover:scale-150"></div>
            <div class="relative flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wider text-slate-500">Pending</p>
                    <p class="mt-2 text-4xl font-bold text-amber-600">{{ $pendingCount }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-amber-600 shadow-inner">
                    <x-heroicon-o-clock class="h-6 w-6" />
                </div>
            </div>
        </a>

        {{-- Verified Card --}}
        <a href="{{ route('admin.participants.index', ['status' => 'verified']) }}" class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:-translate-y-1 hover:shadow-lg hover:border-emerald-200">
            <div class="absolute right-0 top-0 h-24 w-24 -translate-y-8 translate-x-8 rounded-full bg-emerald-50 transition-transform group-hover:scale-150"></div>
            <div class="relative flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wider text-slate-500">Verified</p>
                    <p class="mt-2 text-4xl font-bold text-emerald-600">{{ $verifiedCount }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 shadow-inner">
                    <x-heroicon-o-check-circle class="h-6 w-6" />
                </div>
            </div>
        </a>
    </div>

    {{-- Quick Alert --}}
    @if($needsReviewCount > 0)
        <div class="mb-8 rounded-2xl border border-indigo-200 bg-indigo-50 p-5 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600">
                    <x-heroicon-o-bell-alert class="h-6 w-6" />
                </div>
                <div class="flex-1">
                    <p class="font-bold text-indigo-900">Perlu Review</p>
                    <p class="text-sm text-indigo-700">Ada <strong>{{ $needsReviewCount }}</strong> pendaftaran yang menunggu verifikasi atau approval pembayaran.</p>
                </div>
                <a href="{{ route('admin.participants.index', ['status' => 'submitted']) }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:bg-indigo-700">
                    Lihat
                    <x-heroicon-o-arrow-right class="h-4 w-4" />
                </a>
            </div>
        </div>
    @endif

    {{-- Charts Row --}}
    <div class="grid gap-6 lg:grid-cols-3 mb-8">
        {{-- Trend Pendaftaran --}}
        <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-800">Trend Pendaftaran (7 Hari)</h2>
                <span class="rounded-lg bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">{{ array_sum($trendData) }} pendaftar</span>
            </div>
            <div class="h-64">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        {{-- Distribusi Kategori --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-bold text-slate-800">Distribusi Kategori</h2>
            @if(count($categoryDistribution) > 0)
                <div class="space-y-3">
                    @foreach($categoryDistribution as $category => $count)
                        @php
                            $percentage = $participantCount > 0 ? round(($count / $participantCount) * 100, 1) : 0;
                            $colors = ['bg-brand-500', 'bg-blue-500', 'bg-emerald-500', 'bg-amber-500', 'bg-purple-500'];
                            $color = $colors[$loop->index % count($colors)];
                        @endphp
                        <div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium text-slate-700">{{ $category }}</span>
                                <span class="text-slate-500">{{ $count }} ({{ $percentage }}%)</span>
                            </div>
                            <div class="mt-1 h-2 w-full rounded-full bg-slate-100">
                                <div class="h-2 rounded-full {{ $color }} transition-all" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex h-48 items-center justify-center text-sm text-slate-400">
                    Belum ada data
                </div>
            @endif
        </div>
    </div>

    {{-- Bottom Row --}}
    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Event Terdekat --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-800">Event Terdekat</h2>
                <a href="{{ route('admin.events.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">Lihat Semua</a>
            </div>

            @if($upcomingEvents->count() > 0)
                <div class="space-y-4">
                    @foreach($upcomingEvents as $event)
                        <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 transition-colors hover:bg-slate-100">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="font-bold text-slate-800">{{ $event->name }}</p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        <x-heroicon-o-calendar class="inline h-3 w-3" />
                                        {{ $event->date?->translatedFormat('d M Y') ?? 'Tanggal belum diatur' }}
                                    </p>
                                </div>
                                @php
                                    $daysUntil = $event->date ? now()->diffInDays($event->date, false) : null;
                                @endphp
                                @if($daysUntil !== null && $daysUntil >= 0)
                                    <span class="rounded-lg bg-brand-100 px-2 py-1 text-xs font-bold text-brand-700">
                                        {{ $daysUntil }} hari
                                    </span>
                                @elseif($daysUntil !== null && $daysUntil < 0)
                                    <span class="rounded-lg bg-slate-200 px-2 py-1 text-xs font-bold text-slate-600">
                                        Selesai
                                    </span>
                                @endif
                            </div>
                            <div class="mt-3 flex items-center gap-4 text-xs">
                                <span class="text-slate-500">
                                    <x-heroicon-o-user-group class="inline h-3 w-3" />
                                    {{ $event->participants_count }} pendaftar
                                </span>
                                <span class="text-emerald-600">
                                    <x-heroicon-o-check-badge class="inline h-3 w-3" />
                                    {{ $event->verified_count }} verified
                                </span>
                            </div>
                            <div class="mt-2 h-1.5 w-full rounded-full bg-slate-200">
                                @php
                                    $progress = $event->participants_count > 0
                                        ? ($event->verified_count / $event->participants_count) * 100
                                        : 0;
                                @endphp
                                <div class="h-1.5 rounded-full bg-emerald-500 transition-all" style="width: {{ $progress }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex h-48 flex-col items-center justify-center text-center">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                        <x-heroicon-o-x-circle class="h-6 w-6" />
                    </div>
                    <p class="mt-3 text-sm text-slate-500">Tidak ada event aktif</p>
                    <a href="{{ route('admin.events.create') }}" class="mt-2 text-sm font-medium text-brand-600 hover:text-brand-700">Buat Event Baru</a>
                </div>
            @endif
        </div>

        {{-- Breakdown Status --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-bold text-slate-800">Status Pendaftaran</h2>
            <div class="space-y-2">
                @php
                    $statusLabels = [
                        'submitted' => ['label' => 'Menunggu Review', 'color' => 'bg-slate-500', 'text' => 'text-slate-700'],
                        'approved_waiting_payment' => ['label' => 'Menunggu Pembayaran', 'color' => 'bg-blue-500', 'text' => 'text-blue-700'],
                        'payment_submitted' => ['label' => 'Pembayaran Direview', 'color' => 'bg-indigo-500', 'text' => 'text-indigo-700'],
                        'payment_rejected' => ['label' => 'Pembayaran Ditolak', 'color' => 'bg-rose-500', 'text' => 'text-rose-700'],
                        'completed' => ['label' => 'Selesai', 'color' => 'bg-emerald-500', 'text' => 'text-emerald-700'],
                        'rejected' => ['label' => 'Ditolak', 'color' => 'bg-red-500', 'text' => 'text-red-700'],
                    ];
                @endphp

                @foreach($workflowStatus as $key => $count)
                    @if($count > 0)
                        @php
                            $statusInfo = $statusLabels[$key] ?? ['label' => $key, 'color' => 'bg-slate-500', 'text' => 'text-slate-700'];
                            $statusMap = [
                                'submitted' => 'submitted',
                                'approved_waiting_payment' => 'approved_waiting_payment',
                                'payment_submitted' => 'payment_submitted',
                                'payment_rejected' => 'payment_rejected',
                                'completed' => 'completed',
                                'rejected' => 'rejected',
                            ];
                        @endphp
                        <a href="{{ route('admin.participants.index', ['status' => $statusMap[$key] ?? '']) }}" class="flex items-center justify-between rounded-lg p-2 transition-colors hover:bg-slate-50">
                            <div class="flex items-center gap-3">
                                <span class="h-2.5 w-2.5 rounded-full {{ $statusInfo['color'] }}"></span>
                                <span class="text-sm font-medium text-slate-700">{{ $statusInfo['label'] }}</span>
                            </div>
                            <span class="text-sm font-bold {{ $statusInfo['text'] }}">{{ $count }}</span>
                        </a>
                    @endif
                @endforeach
            </div>

            <div class="mt-4 rounded-xl border border-amber-100 bg-amber-50 p-3">
                <div class="flex items-center gap-2 text-amber-700">
                    <x-heroicon-o-light-bulb class="h-4 w-4" />
                    <span class="text-xs font-medium">Klik status untuk melihat detail peserta</span>
                </div>
            </div>
        </div>

        {{-- Peserta Terbaru --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-800">Peserta Terbaru</h2>
                <a href="{{ route('admin.participants.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">Lihat Semua</a>
            </div>

            @if($recentParticipants->count() > 0)
                <div class="space-y-3">
                    @foreach($recentParticipants as $participant)
                        <a href="{{ route('admin.participants.show', $participant) }}" class="flex items-center gap-3 rounded-lg p-2 transition-colors hover:bg-slate-50">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                                <x-heroicon-o-user class="h-5 w-5" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-800">{{ $participant->name }}</p>
                                <p class="truncate text-xs text-slate-500">{{ $participant->event?->name ?? 'Event tidak ditemukan' }}</p>
                            </div>
                            <span class="shrink-0 rounded-md px-2 py-0.5 text-xs font-medium
                                @if($participant->status === 'verified') bg-emerald-100 text-emerald-700
                                @elseif($participant->status === 'pending') bg-amber-100 text-amber-700
                                @else bg-red-100 text-red-700
                                @endif">
                                {{ ucfirst($participant->status) }}
                            </span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="flex h-48 items-center justify-center text-sm text-slate-400">
                    Belum ada peserta
                </div>
            @endif
        </div>
    </div>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Trend Chart
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        const trendData = @json(array_values($trendData));
        const trendLabels = @json(array_keys($trendData)).map(date => {
            const d = new Date(date);
            return d.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric' });
        });

        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Pendaftar',
                    data: trendData,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            color: '#64748b'
                        },
                        grid: {
                            color: '#f1f5f9'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#64748b'
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
@endsection
