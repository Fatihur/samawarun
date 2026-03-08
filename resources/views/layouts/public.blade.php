<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'Samawa Run' }}</title>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
        {{-- AlpineJS for basic interactivity like mobile menu --}}
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>
    <body class="min-h-screen bg-background-dark font-sans text-white selection:bg-primary selection:text-background-dark">
        @include('layouts.public.header')

        @if (session('success'))
            <div
                x-data="{ show: true }"
                x-init="setTimeout(() => show = false, 4000)"
                x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-2"
                class="fixed right-4 top-20 z-[60] w-[calc(100%-2rem)] max-w-md rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 shadow-lg"
                role="status"
            >
                <div class="flex items-start gap-3">
                    <x-heroicon-s-check-circle class="mt-0.5 h-5 w-5 shrink-0 text-emerald-500" />
                    <div class="text-sm font-medium leading-relaxed">{{ session('success') }}</div>
                    <button type="button" @click="show = false" class="ml-auto text-emerald-600 hover:text-emerald-800" aria-label="Tutup notifikasi">
                        <x-heroicon-o-x-mark class="h-4 w-4" />
                    </button>
                </div>
            </div>
        @endif

        <main class="w-full">
            @yield('content')
        </main>

        @include('layouts.public.footer')
    </body>
</html>
