@php
    $statusColors = [
        'verified' => ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'text' => 'text-emerald-800', 'badge' => 'bg-emerald-100 text-emerald-700'],
        'pending' => ['bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'text' => 'text-amber-800', 'badge' => 'bg-amber-100 text-amber-700'],
        'rejected' => ['bg' => 'bg-red-50', 'border' => 'border-red-200', 'text' => 'text-red-800', 'badge' => 'bg-red-100 text-red-700'],
    ];
    $color = $statusColors[$participant->status] ?? $statusColors['pending'];
@endphp

<div class="rounded-xl border {{ $color['border'] }} {{ $color['bg'] }} p-5 animate-fade-in">
    {{-- Header --}}
    <div class="flex items-start justify-between">
        <div>
            <span class="inline-flex items-center gap-1.5 rounded-lg {{ $color['badge'] }} px-2.5 py-1 text-xs font-bold">
                @if($participant->status === 'verified')
                    <x-heroicon-o-check-badge class="h-3.5 w-3.5" />
                @elseif($participant->status === 'rejected')
                    <x-heroicon-o-x-circle class="h-3.5 w-3.5" />
                @else
                    <x-heroicon-o-clock class="h-3.5 w-3.5" />
                @endif
                {{ ucfirst($participant->status) }}
            </span>
        </div>
        <div class="text-right">
            <p class="text-xs text-slate-500">BIB Number</p>
            <p class="font-mono text-2xl font-bold text-indigo-700">{{ $participant->bib_number ?? '-' }}</p>
        </div>
    </div>

    {{-- Name --}}
    <div class="mt-4">
        <p class="text-xs text-slate-500">Nama Peserta</p>
        <p class="text-xl font-bold {{ $color['text'] }}">{{ $participant->name }}</p>
    </div>

    {{-- Details Grid --}}
    <div class="mt-4 grid grid-cols-2 gap-3">
        <div class="rounded-lg bg-white/60 p-3">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Kategori</p>
            <p class="mt-0.5 text-sm font-semibold text-slate-800">{{ $participant->distance_category }}</p>
        </div>
        <div class="rounded-lg bg-white/60 p-3">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Jersey</p>
            <p class="mt-0.5 text-sm font-semibold text-slate-800">{{ $participant->jersey_size }}</p>
        </div>
    </div>

    {{-- Event Info --}}
    <div class="mt-3 rounded-lg bg-white/60 p-3">
        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Event</p>
        <p class="mt-0.5 text-sm font-semibold text-slate-800">{{ $participant->event?->name ?? '-' }}</p>
    </div>

    {{-- Contact Info --}}
    <div class="mt-3 space-y-2">
        <div class="flex items-center gap-2 text-sm">
            <x-heroicon-o-envelope class="h-4 w-4 text-slate-400" />
            <span class="text-slate-700">{{ $participant->email }}</span>
        </div>
        <div class="flex items-center gap-2 text-sm">
            <x-heroicon-o-phone class="h-4 w-4 text-slate-400" />
            <span class="text-slate-700">{{ $participant->phone }}</span>
        </div>
    </div>

    {{-- Emergency Contact --}}
    @if($participant->emergency_contact_display)
    <div class="mt-3 rounded-lg border border-red-100 bg-red-50/50 p-3">
        <div class="flex items-center gap-1.5">
            <x-heroicon-o-exclamation-triangle class="h-3.5 w-3.5 text-red-500" />
            <p class="text-[10px] font-bold uppercase tracking-wider text-red-600">Kontak Darurat</p>
        </div>
        <p class="mt-0.5 text-sm font-semibold text-red-800">{{ $participant->emergency_contact_display }}</p>
    </div>
    @endif

    {{-- Finish Time --}}
    <div class="mt-3 rounded-lg bg-indigo-50 p-3">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-indigo-500">Waktu Finish</p>
                <p class="mt-0.5 font-mono text-lg font-bold text-indigo-700">
                    {{ $participant->formatted_race_duration ?? '--:--:--' }}
                </p>
            </div>
            @if($participant->race_finished_at)
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                    <x-heroicon-o-check class="h-5 w-5" />
                </div>
            @else
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-200 text-slate-400">
                    <x-heroicon-o-minus class="h-5 w-5" />
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fadeIn 0.3s ease-out;
    }
</style>