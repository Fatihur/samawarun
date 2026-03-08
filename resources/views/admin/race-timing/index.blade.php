@extends('layouts.admin')

@section('content')
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold uppercase italic text-slate-800">Catat Waktu Race</h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-500">Masukkan event dan nomor BIB peserta. Sistem akan otomatis mengambil data peserta, mencatat waktu finish saat ini, dan menghitung durasi lari dari jam mulai event.</p>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <form action="{{ route('admin.race-timing.store') }}" method="POST" class="space-y-6" data-loading-title="Mencatat waktu finish" data-loading-message="Data peserta sedang dicocokkan dan waktu finish sedang disimpan...">
                @csrf
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Event</label>
                    <select name="event_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors" required>
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

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Nomor BIB</label>
                    <input type="text" name="bib_number" value="{{ old('bib_number') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-lg font-bold uppercase tracking-wider text-slate-800 placeholder-slate-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors" placeholder="Contoh: 5001" required autofocus>
                    @error('bib_number')
                        <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" data-loading-label="Mencatat..." class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white shadow-sm transition-all hover:bg-emerald-700 active:scale-95">
                    <x-heroicon-o-check-badge class="h-5 w-5" />
                    Catat Finish Sekarang
                </button>
            </form>
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
