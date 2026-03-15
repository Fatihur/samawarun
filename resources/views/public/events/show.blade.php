@extends('layouts.public')

@section('content')
    {{-- Hero Banner --}}
    <section class="relative h-[50vh] min-h-[400px] lg:h-[60vh] w-full overflow-hidden">
        @if($event->poster)
        <div class="absolute inset-0 z-0 h-full w-full">
            <img src="{{ asset('storage/'.$event->poster) }}" alt="{{ $event->name }}" class="h-full w-full object-cover">
        </div>
        @else
        <div class="absolute inset-0 z-0 h-full w-full bg-gradient-to-br from-secondary-dark to-background-dark transform scale-105">
            <div class="h-full w-full flex items-center justify-center">
                <span class="font-display text-[12rem] font-black uppercase italic text-white/[0.02] -rotate-12 whitespace-nowrap select-none">{{ $event->name }}</span>
            </div>
        </div>
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-background-dark via-background-dark/80 to-transparent"></div>
        <div class="relative z-10 flex h-full flex-col justify-end px-6 py-12 lg:px-20 lg:pb-16">
            <div class="mx-auto w-full max-w-6xl">
                <a href="{{ route('events.index') }}" wire:navigate class="mb-6 inline-flex items-center gap-2 text-sm font-bold text-gray-400 hover:text-primary transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                    Kembali ke Daftar Event
                </a>
                <div class="mb-4 inline-flex items-center gap-2 rounded-lg bg-primary px-3 py-1 text-xs font-black uppercase tracking-widest text-background-dark">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" /></svg>
                    {{ $event->registration_status_label }}
                </div>
                <h1 class="mb-6 text-4xl font-black leading-tight text-white lg:text-6xl uppercase font-display">
                    {{ $event->name }}
                </h1>
                <div class="flex flex-col gap-4 text-gray-200 sm:flex-row sm:items-center sm:gap-8">
                    {{-- Date --}}
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/10 backdrop-blur-md">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 text-primary"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-400">Tanggal</p>
                            <p class="font-bold text-white">{{ $event->date->translatedFormat('d F Y') }}</p>
                        </div>
                    </div>
                    <div class="hidden h-8 w-px bg-white/10 sm:block"></div>
                    {{-- Location --}}
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/10 backdrop-blur-md">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 text-primary"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-400">Lokasi</p>
                            <p class="font-bold text-white">{{ $event->location }}</p>
                        </div>
                    </div>
                    <div class="hidden h-8 w-px bg-white/10 sm:block"></div>
                    {{-- Category --}}
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/10 backdrop-blur-md">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 text-primary"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" /></svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-400">Kategori</p>
                            <p class="font-bold text-white">{{ $event->distanceCategories->isEmpty() ? 'Belum ditentukan' : $event->distanceCategories->pluck('name')->implode(' / ') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Content Grid --}}
    <section class="px-6 py-12 lg:px-20">
        <div class="mx-auto grid max-w-6xl grid-cols-1 gap-12 lg:grid-cols-3">
            {{-- Main Info --}}
            <div class="flex flex-col gap-10 lg:col-span-2">
                <div>
                    <h2 class="mb-6 flex items-center gap-3 text-2xl font-bold text-white font-display">
                        <span class="h-8 w-1 rounded-full bg-primary"></span>
                        Tentang Event
                    </h2>
                    <div class="text-gray-400 space-y-4 leading-relaxed text-lg">
                        @if($event->description)
                            <div class="rich-content">{!! $event->description !!}</div>
                        @else
                            <p>Detail lebih lanjut akan segera diinformasikan. Pantau terus halaman ini untuk update terbaru.</p>
                        @endif
                    </div>
                </div>

                {{-- Event Info Cards --}}
                <div>
                    <h2 class="mb-6 flex items-center gap-3 text-2xl font-bold text-white font-display">
                        <span class="h-8 w-1 rounded-full bg-primary"></span>
                        Informasi Event
                    </h2>
                    <div class="grid grid-cols-1 gap-4">
                        <div class="rounded-xl border border-white/10 bg-secondary-dark p-5">
                            <p class="text-xs font-bold uppercase tracking-widest text-gray-500 mb-2">Nomor Rekening</p>
                            <p class="text-white font-bold text-lg break-all">{{ $event->bank_account ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Gallery Dokumentasi --}}
                @if($event->galleries->isNotEmpty())
                <div>
                    <h2 class="mb-6 flex items-center gap-3 text-2xl font-bold text-white font-display">
                        <span class="h-8 w-1 rounded-full bg-primary"></span>
                        Dokumentasi Event
                    </h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        @foreach($event->galleries as $gallery)
                        <div class="group relative aspect-square overflow-hidden rounded-xl border border-white/10">
                            <img src="{{ asset('storage/'.$gallery->image_path) }}" alt="{{ $gallery->title ?? 'Dokumentasi' }}" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110">
                            @if($gallery->title)
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-3">
                                <p class="text-xs font-medium text-white truncate">{{ $gallery->title }}</p>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- Sidebar / Register --}}
            <div class="relative lg:col-span-1">
                <div class="sticky top-28 flex flex-col gap-6">
                     <div class="overflow-hidden rounded-2xl border border-white/10 bg-secondary-dark p-6 shadow-2xl">
                        <div class="mb-6 rounded-xl bg-primary/10 p-4 border border-primary/20">
                            <div class="flex items-start gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 text-primary mt-0.5 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <div>
                                    <p class="text-xs font-bold uppercase text-primary">Tanggal Event</p>
                                    <p class="text-sm font-medium text-white">{{ $event->date->translatedFormat('d F Y') }}</p>
                                </div>
                            </div>
                        </div>
                        @if ($event->registration_deadline)
                            <div class="mb-6 rounded-xl border border-white/10 bg-white/5 p-4">
                                <p class="text-xs font-bold uppercase tracking-widest text-gray-500">Deadline Pendaftaran</p>
                                <p class="mt-2 text-sm font-semibold text-white">{{ $event->registration_deadline->translatedFormat('d F Y, H:i') }}</p>
                            </div>
                        @endif
                        @if ($isRegistrationOpen)
                            <a href="{{ route('registrations.create', $event) }}" wire:navigate class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl bg-primary py-4 text-center text-lg font-bold text-background-dark shadow-[0_0_20px_rgba(48,232,122,0.2)] transition-all hover:bg-primary-hover active:scale-95">
                                Daftar Sekarang
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                            </a>
                        @else
                            <div class="rounded-xl border border-red-500/20 bg-red-500/10 px-5 py-4 text-center">
                                <p class="text-sm font-bold uppercase tracking-widest text-red-300">Pendaftaran Ditutup</p>
                                <p class="mt-2 text-sm text-red-100">Deadline pendaftaran event ini telah berakhir, sehingga peserta baru tidak bisa mendaftar.</p>
                            </div>
                        @endif
                         <p class="mt-4 text-center text-xs text-gray-500">
                             *Pastikan data diri sesuai dengan KTP/Identitas
                         </p>
                     </div>

                    <div class="rounded-2xl border border-white/10 bg-secondary-dark p-6">
                        <h3 class="mb-4 flex items-center gap-2 font-bold text-white uppercase text-sm tracking-widest">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 text-primary"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                            Lokasi Event
                        </h3>
                        <p class="mb-4 text-sm leading-relaxed text-gray-400">
                            {{ $event->location }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
