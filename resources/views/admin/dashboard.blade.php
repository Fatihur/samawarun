@extends('layouts.admin')

@section('content')
    <div class="mb-8 flex items-center justify-between">
        <h1 class="font-display text-3xl font-bold uppercase italic text-slate-800">Dashboard</h1>
        <p class="text-sm font-medium text-slate-500">{{ now()->translatedFormat('l, d F Y') }}</p>
    </div>

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
        {{-- Total Event Card --}}
        <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:-translate-y-1 hover:shadow-lg">
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
        </div>
        
        {{-- Total Peserta Card --}}
        <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:-translate-y-1 hover:shadow-lg">
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
        </div>

        {{-- Pending Card --}}
        <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:-translate-y-1 hover:shadow-lg hover:border-amber-200">
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
        </div>

        {{-- Verified Card --}}
        <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:-translate-y-1 hover:shadow-lg hover:border-emerald-200">
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
        </div>
    </div>
@endsection
