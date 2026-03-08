@extends('layouts.public')

@section('content')
    <section class="px-6 py-16 lg:px-40 bg-background-dark">
        <div class="mx-auto max-w-6xl">
            <div class="mb-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-white text-3xl font-bold leading-tight lg:text-4xl font-display uppercase">Semua Event</h1>
                    <p class="mt-2 text-gray-400 text-sm">Temukan dan daftarkan diri di event lari favorit Anda.</p>
                </div>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm font-bold text-primary hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                    Kembali ke Home
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse ($events as $event)
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
                            @if (! $event->isRegistrationOpen())
                                <div class="absolute bottom-3 left-3 z-10 rounded-lg bg-red-500/85 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-white">
                                    Pendaftaran Ditutup
                                </div>
                            @endif
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
                            <div class="flex items-center gap-2 text-gray-400 text-sm mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                                <span>{{ $event->location }}</span>
                            </div>
                            <p class="text-gray-400 text-sm mb-6">
                                Rp {{ number_format($event->price, 0, ',', '.') }}
                            </p>
                            <div class="mt-auto flex gap-3">
                                <a href="{{ route('events.show', $event) }}" class="flex-1 rounded-lg bg-primary py-2.5 text-sm font-bold text-center text-background-dark hover:bg-primary-hover transition-colors">
                                    {{ $event->isRegistrationOpen() ? 'Daftar' : 'Lihat Detail' }}
                                </a>
                                <a href="{{ route('events.show', $event) }}" class="flex items-center justify-center rounded-lg border border-white/20 bg-transparent px-3 text-white hover:bg-white/5 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full flex flex-col items-center justify-center rounded-2xl border border-white/10 bg-secondary-dark p-16 text-center">
                        <div class="mb-4 rounded-full bg-white/5 p-4 text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-8 w-8"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
                        </div>
                        <h3 class="text-lg font-bold text-white">Belum Ada Event</h3>
                        <p class="mt-1 text-gray-500">Silakan cek kembali nanti.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-10">
                {{ $events->links() }}
            </div>
        </div>
    </section>
@endsection
