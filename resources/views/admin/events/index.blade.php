@extends('layouts.admin')

@section('content')
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <h1 class="font-display text-3xl font-bold uppercase italic text-slate-800">Kelola Event</h1>
        <a href="{{ route('admin.events.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:bg-brand-700 active:scale-95">
            <x-heroicon-o-plus class="h-4 w-4" />
            Tambah Event
        </a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="datatable w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-5 py-4">Kode</th>
                    <th class="px-5 py-4">Nama</th>
                    <th class="px-5 py-4">Tanggal</th>
                    <th class="px-5 py-4">Status</th>
                    <th class="px-5 py-4" data-orderable="false">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($events as $event)
                    <tr class="transition-colors hover:bg-slate-50/50">
                        <td class="px-5 py-4 font-mono text-xs font-bold text-slate-500">{{ $event->event_code }}</td>
                        <td class="px-5 py-4 font-semibold text-slate-800">{{ $event->name }}</td>
                        <td class="px-5 py-4 text-slate-600">{{ $event->date->format('d M Y') }}</td>
                        <td class="px-5 py-4">
                            @if($event->is_active)
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">Aktif</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-500">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.events.edit', $event) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600 transition-colors hover:bg-blue-100 hover:text-blue-800" title="Edit" aria-label="Edit {{ $event->name }}">
                                    <x-heroicon-o-pencil-square class="h-4 w-4" />
                                </a>
                                <form action="{{ route('admin.events.destroy', $event) }}" method="POST" onsubmit="return confirm('Hapus event ini?')" data-loading-title="Menghapus event" data-loading-message="Event sedang dihapus, mohon tunggu...">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" data-loading-label="Menghapus..." class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-500 transition-colors hover:bg-red-100 hover:text-red-700" title="Hapus" aria-label="Hapus {{ $event->name }}">
                                        <x-heroicon-o-trash class="h-4 w-4" />
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{-- Pagination handled by DataTables --}}
    </div>
@endsection
