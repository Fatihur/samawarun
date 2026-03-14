@extends('layouts.admin')

@section('content')
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold uppercase italic text-slate-800">Catat Waktu Race</h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-500">Pilih event sekali, lalu masukkan nomor BIB peserta satu per satu. Sistem akan otomatis mencatat waktu finish saat ini dan menghitung durasi lari dari jam mulai event.</p>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            @if ($selectedEvent)
                <div class="mb-6 rounded-2xl border border-emerald-100 bg-emerald-50 p-5">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-600">Event Aktif</p>
                            <h2 class="mt-1 text-lg font-bold text-emerald-900">{{ $selectedEvent->name }}</h2>
                            <p class="mt-1 text-sm text-emerald-700">
                                {{ $selectedEvent->date?->format('d M Y') }}{{ $selectedEvent->start_time ? ' / '.$selectedEvent->start_time->format('H:i') : ' / jam mulai belum diatur' }}
                            </p>
                        </div>
                        <a href="{{ route('admin.race-timing.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-white px-4 py-2.5 text-sm font-bold text-emerald-700 transition-colors hover:bg-emerald-100">
                            <x-heroicon-o-arrow-path class="h-4 w-4" />
                            Ganti Event
                        </a>
                    </div>
                </div>

                @if (session('warning'))
                    <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800">
                        <p>{{ session('warning') }}</p>
                        @if(session('overwrite_candidate'))
                            @php($candidate = session('overwrite_candidate'))
                            <form action="{{ route('admin.race-timing.store') }}" method="POST" class="mt-3">
                                @csrf
                                <input type="hidden" name="event_id" value="{{ $candidate['event_id'] }}">
                                <input type="hidden" name="bib_number" value="{{ $candidate['bib_number'] }}">
                                <input type="hidden" name="overwrite" value="1">
                                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-bold text-white hover:bg-amber-700 active:scale-95 transition-all">
                                    <x-heroicon-o-arrow-path class="h-4 w-4" />
                                    Ya, Timpa Waktu Sebelumnya
                                </button>
                            </form>
                        @endif
                    </div>
                @endif

                <form action="{{ route('admin.race-timing.store') }}" method="POST" class="space-y-6" data-skip-loading="true">
                    @csrf
                    <input type="hidden" name="event_id" value="{{ $selectedEvent->id }}">

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Nomor BIB</label>
                        <input type="text" name="bib_number" value="{{ old('bib_number') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-lg font-bold uppercase tracking-wider text-slate-800 placeholder-slate-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors" placeholder="Contoh: 5001" required autofocus>
                        @error('bib_number')
                            <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white shadow-sm transition-all hover:bg-emerald-700 active:scale-95">
                        <x-heroicon-o-check-badge class="h-5 w-5" />
                        Catat Finish Sekarang
                    </button>
                </form>
            @else
                <form action="{{ route('admin.race-timing.index') }}" method="GET" class="space-y-6" data-skip-loading="true">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Pilih Event</label>
                        <select name="event_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors" required autofocus>
                            <option value="">Pilih event</option>
                            @foreach ($events as $event)
                                <option value="{{ $event->id }}" @selected((string) old('event_id') === (string) $event->id)>
                                    {{ $event->name }} - {{ $event->date?->format('d M Y') }}{{ $event->start_time ? ' / '.$event->start_time->format('H:i') : ' / jam mulai belum diatur' }}
                                </option>
                            @endforeach
                        </select>
                        @error('event_id')
                            <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-5 py-3 text-sm font-bold text-white shadow-sm transition-all hover:bg-slate-700 active:scale-95">
                        <x-heroicon-o-arrow-right class="h-5 w-5" />
                        Lanjut ke Input BIB
                    </button>
                </form>
            @endif
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <h2 class="text-lg font-bold text-slate-800">Hasil Pencatatan Terakhir</h2>

            @if (session('timing_result'))
                @php($result = session('timing_result'))
                <div class="mt-5 space-y-3">
                    <div class="rounded-xl bg-slate-50 px-4 py-3">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Peserta</p>
                        <p class="mt-1 text-base font-semibold text-slate-800">{{ $result['name'] }}</p>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="rounded-xl bg-slate-50 px-4 py-3">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">BIB</p>
                            <p class="mt-1 font-mono text-lg font-bold text-emerald-700">{{ $result['bib_number'] }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-4 py-3">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Kategori</p>
                            <p class="mt-1 text-sm font-semibold text-slate-800">{{ $result['distance_category'] }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-4 py-3 sm:col-span-2">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Event</p>
                            <p class="mt-1 text-sm font-semibold text-slate-800">{{ $result['event_name'] }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-4 py-3">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Waktu Finish</p>
                            <p class="mt-1 text-sm font-semibold text-slate-800">{{ $result['finish_time'] }}</p>
                        </div>
                        <div class="rounded-xl bg-emerald-50 px-4 py-3 border border-emerald-100">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-500">Durasi Lari</p>
                            <p class="mt-1 text-xl font-bold text-emerald-700">{{ $result['duration'] }}</p>
                        </div>
                    </div>
                </div>
            @else
                <div class="mt-5 rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                    Belum ada pencatatan waktu pada sesi ini.
                </div>
            @endif
        </div>
    </div>

@endsection
