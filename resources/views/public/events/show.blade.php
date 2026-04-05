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
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4" id="gallery-grid">
                        @foreach($event->galleries as $index => $gallery)
                        <div class="group relative aspect-square overflow-hidden rounded-xl border border-white/10 cursor-pointer" onclick="openLightbox({{ $index }})">
                            <img src="{{ asset('storage/'.$gallery->image_path) }}" alt="{{ $gallery->caption ?? 'Dokumentasi' }}" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" data-gallery-img="{{ $index }}">
                            @if($gallery->caption)
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-3">
                                <p class="text-xs font-medium text-white truncate">{{ $gallery->caption }}</p>
                            </div>
                            @endif
                            <div class="absolute inset-0 flex items-center justify-center bg-black/0 group-hover:bg-black/30 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-8 w-8 text-white opacity-0 group-hover:opacity-100 transition-opacity">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM10 7.5v5m-2.5-2.5h5" />
                                </svg>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Lightbox --}}
                <div id="lightbox" class="fixed inset-0 z-50 hidden bg-black/95 backdrop-blur-sm" onclick="closeLightboxOnBackground(event)">
                    {{-- Close Button --}}
                    <button onclick="closeLightbox()" class="absolute top-4 right-4 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-red-600 text-white shadow-lg hover:bg-red-700 transition-all hover:scale-105 border-2 border-white/30">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="h-7 w-7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    {{-- Prev Button --}}
                    <button onclick="changeImage(-1); event.stopPropagation();" class="absolute left-4 sm:left-8 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-white/20 text-white shadow-lg hover:bg-white/30 transition-all hover:scale-105 border border-white/30" style="top: 50%; transform: translateY(-50%);">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="h-7 w-7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                        </svg>
                    </button>

                    {{-- Next Button --}}
                    <button onclick="changeImage(1); event.stopPropagation();" class="absolute right-4 sm:right-8 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-white/20 text-white shadow-lg hover:bg-white/30 transition-all hover:scale-105 border border-white/30" style="top: 50%; transform: translateY(-50%);">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="h-7 w-7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </button>

                    {{-- Image Counter --}}
                    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-50 rounded-full bg-black/50 px-4 py-2 text-sm text-white">
                        <span id="lightbox-counter">1</span> / {{ $event->galleries->count() }}
                    </div>

                    {{-- Main Image --}}
                    <div class="flex h-full w-full items-center justify-center p-4 sm:p-16">
                        <img id="lightbox-img" src="" alt="" class="max-h-full max-w-full object-contain">
                    </div>

                    {{-- Caption --}}
                    <div id="lightbox-caption" class="absolute bottom-16 left-0 right-0 text-center text-white text-sm px-4"></div>
                </div>

                <script>
                    const galleryImages = [
                        @foreach($event->galleries as $gallery)
                        { src: '{{ asset('storage/'.$gallery->image_path) }}', caption: '{{ $gallery->caption ?? '' }}' },
                        @endforeach
                    ];
                    let currentIndex = 0;

                    function openLightbox(index) {
                        currentIndex = index;
                        updateLightboxImage();
                        document.getElementById('lightbox').classList.remove('hidden');
                        document.body.style.overflow = 'hidden';
                    }

                    function closeLightbox() {
                        document.getElementById('lightbox').classList.add('hidden');
                        document.body.style.overflow = '';
                    }

                    function closeLightboxOnBackground(event) {
                        if (event.target.id === 'lightbox') {
                            closeLightbox();
                        }
                    }

                    function changeImage(direction) {
                        currentIndex += direction;
                        if (currentIndex < 0) {
                            currentIndex = galleryImages.length - 1;
                        } else if (currentIndex >= galleryImages.length) {
                            currentIndex = 0;
                        }
                        updateLightboxImage();
                    }

                    function updateLightboxImage() {
                        const img = document.getElementById('lightbox-img');
                        const caption = document.getElementById('lightbox-caption');
                        const counter = document.getElementById('lightbox-counter');

                        img.src = galleryImages[currentIndex].src;
                        caption.textContent = galleryImages[currentIndex].caption || '';
                        counter.textContent = currentIndex + 1;
                    }

                    // Keyboard navigation
                    document.addEventListener('keydown', function(e) {
                        if (document.getElementById('lightbox').classList.contains('hidden')) return;

                        if (e.key === 'Escape') closeLightbox();
                        if (e.key === 'ArrowLeft') changeImage(-1);
                        if (e.key === 'ArrowRight') changeImage(1);
                    });
                </script>
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

                    {{-- Kuota Per Kategori --}}
                    @if($event->distanceCategories->isNotEmpty())
                    <div class="rounded-2xl border border-white/10 bg-secondary-dark p-6">
                        <h3 class="mb-4 flex items-center gap-2 font-bold text-white uppercase text-sm tracking-widest">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 text-primary"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" /></svg>
                            Kuota Kategori
                        </h3>
                        <div class="space-y-3">
                            @foreach($event->distanceCategories as $category)
                            @php
                                $categoryName = strtoupper($category->name);
                                $registeredCount = $event->getRegisteredCountForCategory($categoryName);
                                $remainingQuota = $event->getRemainingQuotaForCategory($categoryName);
                                $isFull = $remainingQuota !== null && $remainingQuota <= 0;
                                $hasQuota = $category->pivot?->quota !== null;
                            @endphp
                            <div class="flex items-center justify-between rounded-lg border {{ $isFull ? 'border-red-500/30 bg-red-500/10' : 'border-white/10 bg-white/5' }} px-4 py-3">
                                <div>
                                    <p class="font-bold text-white">{{ $categoryName }}</p>
                                    @if($hasQuota)
                                        <p class="text-xs text-gray-400">{{ $registeredCount }}/{{ $category->pivot->quota }} peserta</p>
                                    @else
                                        <p class="text-xs text-gray-400">{{ $registeredCount }} peserta terdaftar</p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    @if($isFull)
                                        <span class="rounded-full bg-red-500/20 px-2 py-1 text-xs font-bold text-red-400">PENUH</span>
                                    @elseif($remainingQuota !== null && $remainingQuota <= 10)
                                        <span class="rounded-full bg-amber-500/20 px-2 py-1 text-xs font-bold text-amber-400">SISA {{ $remainingQuota }}</span>
                                    @else
                                        <span class="text-xs text-primary font-bold">TERSEDIA</span>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Share Event --}}
                    <div class="rounded-2xl border border-white/10 bg-secondary-dark p-6">
                        <h3 class="mb-4 flex items-center gap-2 font-bold text-white uppercase text-sm tracking-widest">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 text-primary"><path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.287.696.287 1.093m0-1.093c-.18.324-.287.696-.287 1.093m9.5-3.093a2.25 2.25 0 100 2.186m0-2.186c.18.324.287.696.287 1.093m0-1.093c-.18.324-.287.696-.287 1.093M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            Bagikan Event
                        </h3>
                        <div class="flex flex-wrap gap-3">
                            {{-- WhatsApp --}}
                            <a href="https://wa.me/?text={{ urlencode($event->name . ' - ' . route('events.show', $event)) }}" target="_blank" class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-600 text-white hover:bg-green-700 transition-colors" title="Bagikan ke WhatsApp">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            </a>
                            {{-- Facebook --}}
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('events.show', $event)) }}" target="_blank" class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors" title="Bagikan ke Facebook">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </a>
                            {{-- Twitter/X --}}
                            <a href="https://twitter.com/intent/tweet?text={{ urlencode($event->name) }}&url={{ urlencode(route('events.show', $event)) }}" target="_blank" class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-800 text-white hover:bg-slate-900 transition-colors" title="Bagikan ke X/Twitter">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                            </a>
                            {{-- Copy Link --}}
                            <button onclick="copyEventLink()" class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-600 text-white hover:bg-gray-700 transition-colors" title="Salin Link">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" /></svg>
                            </button>
                            {{-- Native Share (Mobile) --}}
                            <button onclick="nativeShare()" class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary text-background-dark hover:bg-primary-hover transition-colors" title="Bagikan Lainnya">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>
                            </button>
                        </div>
                        <p id="copy-message" class="mt-3 text-xs text-green-400 hidden">✓ Link berhasil disalin!</p>
                    </div>

                    <script>
                        function copyEventLink() {
                            const url = '{{ route('events.show', $event) }}';
                            navigator.clipboard.writeText(url).then(() => {
                                const msg = document.getElementById('copy-message');
                                msg.classList.remove('hidden');
                                setTimeout(() => msg.classList.add('hidden'), 2000);
                            });
                        }

                        function nativeShare() {
                            if (navigator.share) {
                                navigator.share({
                                    title: '{{ $event->name }}',
                                    text: 'Ikuti event {{ $event->name }} di Samawa Run!',
                                    url: '{{ route('events.show', $event) }}'
                                });
                            } else {
                                copyEventLink();
                            }
                        }
                    </script>
                </div>
            </div>
        </div>
    </section>
@endsection
