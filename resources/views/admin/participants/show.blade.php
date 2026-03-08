@extends('layouts.admin')

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <a href="{{ route('admin.participants.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-brand-600 transition-colors">
            <x-heroicon-o-arrow-left class="h-4 w-4" />
            Kembali ke daftar peserta
        </a>

        {{-- Action Buttons --}}
        @if($participant->status === 'pending')
        <div class="flex items-center gap-2">
            <form action="{{ route('admin.participants.verify', $participant) }}" method="POST" class="inline">
                @csrf @method('PATCH')
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition-all hover:bg-emerald-700 active:scale-95" onclick="return confirm('Verifikasi peserta ini?')">
                    <x-heroicon-o-check-circle class="h-4 w-4" />
                    Verify
                </button>
            </form>
            <form action="{{ route('admin.participants.reject', $participant) }}" method="POST" class="inline">
                @csrf @method('PATCH')
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition-all hover:bg-red-700 active:scale-95" onclick="return confirm('Tolak pendaftaran peserta ini?')">
                    <x-heroicon-o-x-circle class="h-4 w-4" />
                    Reject
                </button>
            </form>
        </div>
        @elseif ($participant->status === 'verified')
        <a href="{{ route('admin.participants.id-card', $participant) }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 transition-all active:scale-95">
            <x-heroicon-o-arrow-down-tray class="h-4 w-4" />
            Download Nomor Dada
        </a>
        @endif
    </div>

    <div x-data="{ lightboxOpen: false, lightboxImage: '' }" class="grid gap-6 lg:grid-cols-2 items-start">
        {{-- Left Column: Data --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <h1 class="font-display text-2xl font-bold uppercase italic text-slate-800">Data Peserta</h1>
                @if($participant->status === 'verified')
                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 border border-emerald-200">Verified</span>
                @elseif($participant->status === 'rejected')
                    <span class="inline-flex items-center rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-red-700 border border-red-200">Rejected</span>
                @else
                    <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700 border border-amber-200">Pending</span>
                @endif
            </div>

            <div class="grid gap-4 text-sm md:grid-cols-2">
                @php
                    $fields = [
                        ['label' => 'Nama', 'value' => $participant->name, 'full' => true],
                        ['label' => 'Event', 'value' => $participant->event?->name, 'full' => true],
                        ['label' => 'Tanggal Lahir', 'value' => $participant->birth_date->format('d M Y')],
                        ['label' => 'Jenis Kelamin', 'value' => $participant->gender === 'male' ? 'Laki-laki' : 'Perempuan'],
                        ['label' => 'NIK', 'value' => $participant->nik, 'full' => true],
                        ['label' => 'HP', 'value' => $participant->phone],
                        ['label' => 'Email', 'value' => $participant->email],
                        ['label' => 'Kontak Darurat', 'value' => $participant->emergency_contact, 'full' => true],
                        ['label' => 'Kategori', 'value' => $participant->distance_category],
                        ['label' => 'Jersey', 'value' => $participant->jersey_size],
                        ['label' => 'BIB Number', 'value' => $participant->bib_number ?? '-'],
                    ];
                @endphp
                @foreach($fields as $field)
                <div class="rounded-xl bg-slate-50 px-4 py-3 {{ isset($field['full']) && $field['full'] ? 'col-span-1 md:col-span-2' : '' }}">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">{{ $field['label'] }}</p>
                    <p class="font-semibold text-slate-800">{{ $field['value'] }}</p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Right Column: Attachments --}}
        <div class="flex flex-col gap-6">
            {{-- KTP --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm flex flex-col items-center">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500 mb-4 self-start">Kartu Identitas (KTP)</h2>
                @if($participant->ktp_file)
                <div class="relative group cursor-pointer w-full rounded-xl overflow-hidden border border-slate-100 bg-slate-50 hover:border-brand-200 transition-colors" @click="lightboxOpen = true; lightboxImage = '{{ asset('storage/'.$participant->ktp_file) }}'">
                    <img src="{{ asset('storage/'.$participant->ktp_file) }}" alt="KTP" class="w-full object-contain max-h-[300px]">
                    <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center backdrop-blur-[2px]">
                        <div class="bg-white/90 text-slate-800 px-4 py-2 rounded-lg font-bold text-sm shadow-lg flex items-center gap-2">
                            <x-heroicon-o-arrows-pointing-out class="w-4 h-4" />
                            Perbesar
                        </div>
                    </div>
                </div>
                @else
                <div class="w-full rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 px-4 py-12 text-center text-slate-500">
                    Tidak ada file KTP
                </div>
                @endif
            </div>

            {{-- Bukti Transfer --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm flex flex-col items-center">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500 mb-4 self-start">Bukti Transfer</h2>
                @if($participant->transfer_proof)
                <div class="relative group cursor-pointer w-full rounded-xl overflow-hidden border border-slate-100 bg-slate-50 hover:border-brand-200 transition-colors" @click="lightboxOpen = true; lightboxImage = '{{ asset('storage/'.$participant->transfer_proof) }}'">
                    <img src="{{ asset('storage/'.$participant->transfer_proof) }}" alt="Bukti Transfer" class="w-full object-contain max-h-[400px]">
                    <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center backdrop-blur-[2px]">
                        <div class="bg-white/90 text-slate-800 px-4 py-2 rounded-lg font-bold text-sm shadow-lg flex items-center gap-2">
                            <x-heroicon-o-arrows-pointing-out class="w-4 h-4" />
                            Perbesar
                        </div>
                    </div>
                </div>
                @else
                <div class="w-full rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 px-4 py-12 text-center text-slate-500">
                    Tidak ada bukti transfer
                </div>
                @endif
            </div>
        </div>

        {{-- Lightbox --}}
        <div x-cloak x-show="lightboxOpen" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/90 backdrop-blur-sm p-4" x-transition.opacity>
            <button @click="lightboxOpen = false" class="absolute top-4 right-4 text-white/70 hover:text-white bg-black/50 hover:bg-black/70 rounded-full p-2 transition-colors">
                <x-heroicon-o-x-mark class="w-6 h-6" />
            </button>
            <div class="relative w-full max-w-5xl max-h-screen p-4 flex items-center justify-center" @click.away="lightboxOpen = false">
                <img :src="lightboxImage" class="max-w-full max-h-[85vh] object-contain rounded-lg shadow-2xl" alt="Preview">
            </div>
        </div>
    </div>
@endsection
