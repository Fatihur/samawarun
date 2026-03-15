@extends('layouts.public')

@section('content')
    {{-- Hero Section --}}
    <section class="relative flex min-h-[600px] lg:min-h-[90vh] w-full flex-col items-center justify-center overflow-hidden px-4 py-20">
        <div class="absolute inset-0 z-0 h-full w-full bg-cover bg-center bg-no-repeat" style="background-image: linear-gradient(rgba(13,26,18,0.7) 0%, rgba(13,26,18,0.9) 100%), url('https://images.unsplash.com/photo-1552674605-db6ffd4facb5?w=1920&q=80')"></div>
        <div class="relative z-10 flex flex-col gap-6 text-center max-w-4xl px-4">
            <div class="inline-flex items-center justify-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-1.5 backdrop-blur-sm self-center">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-primary"></span>
                </span>
                <span class="text-xs font-medium text-white tracking-wide uppercase">Komunitas Lari Sumbawa</span>
            </div>
            <h1 class="text-white text-5xl font-black leading-tight tracking-tight lg:text-7xl uppercase font-display">
                SAMAWA<br><span class="text-primary">RUN</span>
            </h1>
            <p class="text-gray-300 text-lg font-normal leading-relaxed lg:text-xl max-w-2xl mx-auto">
                Komunitas lari yang aktif menyelenggarakan event lari di Sumbawa. Temukan event, lihat informasi, dan daftar langsung.
            </p>
            <div class="mt-4 flex flex-wrap justify-center gap-4">
                <a href="{{ route('events.index') }}" wire:navigate class="flex min-w-[160px] cursor-pointer items-center justify-center rounded-full h-12 px-8 bg-primary hover:bg-primary-hover transition-all text-background-dark text-base font-bold shadow-[0_0_20px_rgba(48,232,122,0.3)] active:scale-95">
                    Lihat Event
                </a>
                @if($upcomingEvents->first())
                <a href="{{ route('registrations.create', $upcomingEvents->first()) }}" wire:navigate class="flex min-w-[160px] cursor-pointer items-center justify-center rounded-full h-12 px-8 bg-white/10 hover:bg-white/20 border border-white/20 text-white transition-all text-base font-bold backdrop-blur-sm active:scale-95">
                    Daftar Event
                </a>
                @endif
            </div>
        </div>
    </section>

    {{-- Stats Section --}}
    <section class="bg-[#0d1a12] py-12 border-y border-white/5">
        <div class="px-6 lg:px-40 flex flex-wrap justify-center gap-12 lg:gap-32">
            <div class="flex flex-col items-center">
                <span class="text-3xl font-black text-primary">{{ $upcomingEvents->count() }}+</span>
                <span class="text-xs text-gray-500 uppercase tracking-widest font-bold mt-1">Upcoming Events</span>
            </div>
            <div class="flex flex-col items-center">
                <span class="text-3xl font-black text-primary">{{ $participantCount }}+</span>
                <span class="text-xs text-gray-500 uppercase tracking-widest font-bold mt-1">Total Peserta</span>
            </div>
            <div class="flex flex-col items-center">
                <span class="text-3xl font-black text-primary">{{ $eventCount }}+</span>
                <span class="text-xs text-gray-500 uppercase tracking-widest font-bold mt-1">Total Events</span>
            </div>
            <div class="flex flex-col items-center">
                <span class="text-3xl font-black text-primary">10k+</span>
                <span class="text-xs text-gray-500 uppercase tracking-widest font-bold mt-1">Km Conquered</span>
            </div>
        </div>
    </section>

    {{-- About Section --}}
    <section class="px-6 py-16 lg:px-40 lg:py-24 bg-background-dark" id="about">
        <div class="mx-auto max-w-6xl">
            <div class="flex flex-col gap-12 lg:flex-row lg:items-center lg:gap-20">
                <div class="relative w-full lg:w-1/2">
                    <div class="aspect-[4/3] w-full overflow-hidden rounded-2xl bg-gray-800 shadow-2xl">
                        <div class="h-full w-full bg-cover bg-center transition-transform duration-700 hover:scale-105" style="background-image: url('https://images.unsplash.com/photo-1571008887538-b36bb32f4571?w=800&q=80')"></div>
                    </div>
                    <div class="absolute -bottom-6 -right-6 h-24 w-24 rounded-full bg-primary/20 blur-2xl"></div>
                </div>
                <div class="flex flex-col gap-6 lg:w-1/2">
                    <div>
                        <h2 class="text-primary text-sm font-bold uppercase tracking-widest mb-2">Tentang Kami</h2>
                        <h3 class="text-white text-3xl font-bold leading-tight lg:text-4xl font-display">
                            Mengenal SamawaRun
                        </h3>
                    </div>
                    <p class="text-gray-400 text-lg leading-relaxed">
                        SamawaRun adalah komunitas lari yang berbasis di Sumbawa. Kami rutin menyelenggarakan event lari untuk mempromosikan gaya hidup sehat dan mempererat kebersamaan masyarakat.
                    </p>
                    <div class="flex flex-col gap-4 mt-2">
                        @php
                            $aboutItems = [
                                ['title' => 'Inklusif', 'desc' => 'Terbuka untuk semua usia dan tingkat kemampuan.'],
                                ['title' => 'Aktif', 'desc' => 'Rutin menyelenggarakan event lari di Sumbawa.'],
                            ];
                        @endphp
                        @foreach($aboutItems as $item)
                        <div class="flex items-start gap-3">
                            <x-heroicon-s-check-circle class="h-6 w-6 text-primary mt-0.5 shrink-0" />
                            <div>
                                <h4 class="text-white font-bold">{{ $item['title'] }}</h4>
                                <p class="text-gray-500 text-sm">{{ $item['desc'] }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Activities Highlight --}}
    <section class="bg-secondary-dark/30 px-6 py-20 lg:px-40">
        <div class="mx-auto max-w-6xl">
            <div class="mb-12 flex flex-col items-start gap-4 md:items-center md:text-center">
                <h2 class="text-white text-3xl font-bold leading-tight lg:text-4xl font-display">Kegiatan Kami</h2>
                <p class="text-gray-400 max-w-2xl text-base">
                    Berikut adalah beberapa kegiatan yang rutin diselenggarakan oleh SamawaRun.
                </p>
            </div>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="group flex flex-col gap-4 rounded-xl border border-white/5 bg-background-dark p-6 transition-all duration-300 hover:-translate-y-1 hover:border-primary/50 hover:bg-secondary-dark">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 text-primary group-hover:bg-primary group-hover:text-background-dark transition-colors">
                        <x-heroicon-o-bolt class="h-6 w-6" />
                    </div>
                    <div>
                        <h3 class="text-white text-lg font-bold">Weekly Run</h3>
                        <p class="mt-2 text-sm text-gray-400">Kegiatan lari santai rutin setiap Minggu pagi keliling kota.</p>
                    </div>
                </div>
                <div class="group flex flex-col gap-4 rounded-xl border border-white/5 bg-background-dark p-6 transition-all duration-300 hover:-translate-y-1 hover:border-primary/50 hover:bg-secondary-dark">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 text-primary group-hover:bg-primary group-hover:text-background-dark transition-colors">
                        <x-heroicon-o-calendar-days class="h-6 w-6" />
                    </div>
                    <div>
                        <h3 class="text-white text-lg font-bold">Training Program</h3>
                        <p class="mt-2 text-sm text-gray-400">Program latihan interval &amp; strength untuk meningkatkan performa.</p>
                    </div>
                </div>
                <div class="group flex flex-col gap-4 rounded-xl border border-white/5 bg-background-dark p-6 transition-all duration-300 hover:-translate-y-1 hover:border-primary/50 hover:bg-secondary-dark">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 text-primary group-hover:bg-primary group-hover:text-background-dark transition-colors">
                        <x-heroicon-o-user-group class="h-6 w-6" />
                    </div>
                    <div>
                        <h3 class="text-white text-lg font-bold">Networking</h3>
                        <p class="mt-2 text-sm text-gray-400">Wadah untuk saling mengenal antar pelari di Sumbawa.</p>
                    </div>
                </div>
                <div class="group flex flex-col gap-4 rounded-xl border border-white/5 bg-background-dark p-6 transition-all duration-300 hover:-translate-y-1 hover:border-primary/50 hover:bg-secondary-dark">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 text-primary group-hover:bg-primary group-hover:text-background-dark transition-colors">
                        <x-heroicon-o-trophy class="h-6 w-6" />
                    </div>
                    <div>
                        <h3 class="text-white text-lg font-bold">Event &amp; Race</h3>
                        <p class="mt-2 text-sm text-gray-400">Penyelenggaraan event lomba lari dengan berbagai kategori jarak.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Upcoming Events --}}
    <section class="px-6 py-20 lg:px-40 bg-background-dark relative overflow-hidden" id="events">
        <div class="absolute top-0 right-0 w-96 h-96 bg-primary/5 rounded-full blur-3xl pointer-events-none"></div>
        <div class="mx-auto max-w-6xl relative z-10">
            <div class="flex items-center justify-between mb-10">
                <h2 class="text-white text-3xl font-bold leading-tight font-display">Event Mendatang</h2>
                <a href="{{ route('events.index') }}" wire:navigate class="hidden sm:flex items-center gap-1 text-sm font-bold text-primary hover:text-white transition-colors">
                    Lihat Semua
                    <x-heroicon-o-arrow-right class="h-4 w-4" />
                </a>
            </div>

            @if($upcomingEvents->isEmpty())
                <div class="flex flex-col items-center justify-center rounded-2xl border border-white/10 bg-secondary-dark p-16 text-center">
                    <div class="mb-4 rounded-full bg-white/5 p-4 text-gray-500">
                        <x-heroicon-o-calendar-days class="h-8 w-8" />
                    </div>
                    <h3 class="text-lg font-bold text-white">Belum Ada Event Aktif</h3>
                    <p class="mt-1 text-gray-500">Pantau terus untuk info event lari terbaru.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach ($upcomingEvents as $event)
                    <div class="flex flex-col overflow-hidden rounded-2xl bg-secondary-dark border border-white/5 group hover:border-primary/30 transition-all duration-300">
                        <div class="relative h-48 w-full overflow-hidden">
                            <div class="absolute top-3 left-3 z-10 rounded-lg bg-background-dark/80 backdrop-blur px-3 py-1 text-center border border-white/10">
                                <p class="text-xs font-bold text-primary">{{ strtoupper($event->date->format('M')) }}</p>
                                <p class="text-xl font-black text-white">{{ $event->date->format('d') }}</p>
                            </div>
                            <div class="absolute top-3 right-3 z-10 flex gap-1">
                                @foreach($event->distanceCategories as $category)
                                <span class="rounded bg-white/10 backdrop-blur px-2 py-0.5 text-[10px] font-bold text-white border border-white/10">{{ $category->name }}</span>
                                @endforeach
                            </div>
                            @if($event->poster)
                            <img src="{{ asset('storage/'.$event->poster) }}" alt="{{ $event->name }}" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110">
                            @else
                            <div class="h-full w-full bg-gradient-to-br from-secondary-dark to-background-dark flex items-center justify-center transition-transform duration-500 group-hover:scale-110">
                                <span class="font-display text-5xl font-black uppercase italic text-white/5 -rotate-12">{{ Str::limit($event->name, 12) }}</span>
                            </div>
                            @endif
                        </div>
                        <div class="flex flex-1 flex-col p-5">
                            <h3 class="text-xl font-bold text-white mb-2 font-display">{{ $event->name }}</h3>
                            <div class="flex items-center gap-2 text-gray-400 text-sm mb-4">
                                <x-heroicon-o-map-pin class="h-4 w-4 shrink-0" />
                                <span>{{ $event->location }}</span>
                            </div>
                            <p class="text-gray-400 text-sm mb-6 line-clamp-2">
                                {{ $event->date->format('dmY') }}
                            </p>
                            <div class="mt-auto flex gap-3">
                                <a href="{{ route('events.show', $event) }}" wire:navigate class="flex-1 rounded-lg bg-primary py-2.5 text-sm font-bold text-center text-background-dark hover:bg-primary-hover transition-colors">
                                    Lihat Detail
                                </a>
                                <a href="{{ route('events.show', $event) }}" wire:navigate class="flex items-center justify-center rounded-lg border border-white/20 bg-transparent px-3 text-white hover:bg-white/5 transition-colors">
                                    <x-heroicon-o-information-circle class="h-5 w-5" />
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Gallery Section --}}
    @if($galleries->count())
    <section class="px-6 py-20 lg:px-40 bg-secondary-dark/30" id="gallery">
        <div class="mx-auto max-w-6xl">
            <div class="mb-12 flex flex-col items-start gap-4 md:items-center md:text-center">
                <h2 class="text-primary text-sm font-bold uppercase tracking-widest">Galeri</h2>
                <h3 class="text-white text-3xl font-bold leading-tight lg:text-4xl font-display">Dokumentasi Kegiatan</h3>
                <p class="text-gray-400 max-w-2xl text-base">
                    Momen-momen terbaik dari event dan kegiatan SamawaRun.
                </p>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 auto-rows-[180px] lg:auto-rows-[200px] gap-3">
                @foreach($galleries as $index => $gallery)
                @php
                    $spanClass = match($index % 6) {
                        0 => 'col-span-2 row-span-2',
                        3 => 'col-span-2',
                        default => '',
                    };
                @endphp
                <div class="{{ $spanClass }} group relative overflow-hidden rounded-2xl border border-white/5">
                    <img src="{{ asset('storage/' . $gallery->image_path) }}" alt="{{ $gallery->title ?? 'SamawaRun Gallery' }}" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy" />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        @if($gallery->title)
                        <p class="absolute bottom-4 left-4 right-4 text-white text-sm font-semibold">{{ $gallery->title }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Contact Section --}}
    <section class="px-6 py-20 lg:px-40 bg-secondary-dark/30" id="contact">
        <div class="mx-auto max-w-5xl">
            @if($contact)
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-start">
                {{-- Left: Heading & Social --}}
                <div>
                    <h2 class="text-primary text-sm font-bold uppercase tracking-widest mb-3">Hubungi Kami</h2>
                    <h3 class="text-white text-3xl font-bold leading-tight lg:text-4xl font-display mb-5">Tetap Terhubung</h3>
                    <p class="text-gray-400 leading-relaxed mb-8">
                        Untuk informasi lebih lanjut tentang event dan kegiatan SamawaRun, silakan hubungi kami.
                    </p>

                    @if($contact->address)
                    <div class="flex items-start gap-3 mb-8">
                        <x-heroicon-o-map-pin class="h-5 w-5 text-primary shrink-0 mt-0.5" />
                        <p class="text-gray-400 text-sm leading-relaxed">{{ $contact->address }}</p>
                    </div>
                    @endif

                    {{-- Social Media --}}
                    @if($contact->instagram || $contact->facebook || $contact->tiktok)
                    <p class="text-xs font-bold uppercase tracking-widest text-gray-500 mb-4">Ikuti Kami</p>
                    <div class="flex items-center gap-3">
                        @if($contact->instagram)
                        <a href="https://instagram.com/{{ $contact->instagram }}" target="_blank" class="flex h-10 w-10 items-center justify-center rounded-full bg-white/5 text-gray-400 hover:bg-pink-500 hover:text-white transition-all" title="Instagram">
                            <x-heroicon-o-camera class="h-4 w-4" />
                        </a>
                        @endif
                        @if($contact->facebook)
                        <a href="https://facebook.com/{{ $contact->facebook }}" target="_blank" class="flex h-10 w-10 items-center justify-center rounded-full bg-white/5 text-gray-400 hover:bg-blue-600 hover:text-white transition-all" title="Facebook">
                            <x-heroicon-o-user-group class="h-4 w-4" />
                        </a>
                        @endif
                        @if($contact->tiktok)
                        <a href="https://tiktok.com/@{{ $contact->tiktok }}" target="_blank" class="flex h-10 w-10 items-center justify-center rounded-full bg-white/5 text-gray-400 hover:bg-white hover:text-black transition-all" title="TikTok">
                            <x-heroicon-o-musical-note class="h-4 w-4" />
                        </a>
                        @endif
                    </div>
                    @endif
                </div>

                {{-- Right: Contact Card --}}
                <div class="rounded-2xl border border-white/5 bg-background-dark overflow-hidden p-2">
                    @if($contact->phone)
                    <div class="flex items-center gap-4 px-5 py-5 rounded-xl">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10">
                            <x-heroicon-o-phone class="h-5 w-5 text-primary" />
                        </div>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-widest text-gray-500">Nomor HP</p>
                            <p class="text-white font-semibold">{{ $contact->phone }}</p>
                        </div>
                    </div>
                    @endif
                    @if($contact->whatsapp)
                    <a href="https://wa.me/{{ $contact->whatsapp }}" target="_blank" class="flex items-center gap-4 px-5 py-5 rounded-xl hover:bg-white/[0.03] transition-colors group">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-green-500/10 group-hover:bg-green-500 transition-colors">
                            <x-heroicon-o-chat-bubble-oval-left-ellipsis class="h-5 w-5 text-green-400 group-hover:text-white transition-colors" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[11px] font-bold uppercase tracking-widest text-gray-500">WhatsApp</p>
                            <p class="text-white font-semibold truncate">{{ $contact->whatsapp }}</p>
                        </div>
                        <x-heroicon-o-arrow-top-right-on-square class="h-4 w-4 text-gray-600 shrink-0 group-hover:text-green-400 transition-colors" />
                    </a>
                    @endif
                    @if($contact->email)
                    <a href="mailto:{{ $contact->email }}" class="flex items-center gap-4 px-5 py-5 rounded-xl hover:bg-white/[0.03] transition-colors group">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-500/10 group-hover:bg-blue-500 transition-colors">
                            <x-heroicon-o-envelope class="h-5 w-5 text-blue-400 group-hover:text-white transition-colors" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[11px] font-bold uppercase tracking-widest text-gray-500">Email</p>
                            <p class="text-white font-semibold truncate">{{ $contact->email }}</p>
                        </div>
                        <x-heroicon-o-arrow-top-right-on-square class="h-4 w-4 text-gray-600 shrink-0 group-hover:text-blue-400 transition-colors" />
                    </a>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </section>

    {{-- Contact / CTA Section --}}
    <section class="py-24 px-6">
        <div class="mx-auto max-w-4xl overflow-hidden rounded-3xl bg-primary relative shadow-2xl">
            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(#000 1px, transparent 1px); background-size: 20px 20px"></div>
            <div class="relative z-10 flex flex-col items-center justify-center gap-6 px-6 py-16 text-center">
                <h2 class="text-background-dark text-4xl font-black leading-tight tracking-tight lg:text-5xl uppercase font-display">
                    Temukan Event Kami
                </h2>
                <p class="text-background-dark/80 text-lg font-medium max-w-lg">
                    Lihat jadwal event terbaru, informasi lomba, dan detail pendaftaran langsung dari SamawaRun.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 mt-4 w-full justify-center">
                    @if($contact?->whatsapp)
                    <a href="https://wa.me/{{ $contact->whatsapp }}" target="_blank" class="flex min-w-[200px] cursor-pointer items-center justify-center gap-2 rounded-full h-14 px-8 bg-background-dark text-white text-lg font-bold shadow-lg hover:bg-black transition-all active:scale-95">
                        <x-heroicon-o-chat-bubble-oval-left-ellipsis class="h-5 w-5" />
                        <span>WhatsApp</span>
                    </a>
                    @endif
                    @if($contact?->instagram)
                    <a href="https://instagram.com/{{ $contact->instagram }}" target="_blank" class="flex min-w-[200px] cursor-pointer items-center justify-center gap-2 rounded-full h-14 px-8 bg-white/20 border-2 border-background-dark text-background-dark text-lg font-bold hover:bg-white/30 transition-all active:scale-95">
                        <x-heroicon-o-camera class="h-5 w-5" />
                        <span>Instagram</span>
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
