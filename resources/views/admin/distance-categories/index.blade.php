@extends('layouts.admin')

@section('content')
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold uppercase italic text-slate-800">Kategori Jarak</h1>
            <p class="mt-1 text-sm text-slate-500">Kelola master data Kategori Jarak yang dapat dipilih pada saat membuat Event.</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-8 items-start">
        {{-- Form Create --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500 mb-4">Tambah Kategori</h2>
            <form action="{{ route('admin.distance-categories.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Kategori</label>
                    <input type="text" name="name" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 focus:border-brand-500 focus:ring-brand-500 transition-colors" placeholder="Contoh: 5K, 10K, Half Marathon" required />
                    @error('name')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="w-full inline-flex justify-center items-center gap-2 rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:bg-brand-700 active:scale-95">
                    <x-heroicon-o-plus class="h-4 w-4" />
                    Tambah
                </button>
            </form>
        </div>

        {{-- List --}}
        <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white shadow-sm">
            @if($categories->isEmpty())
                <div class="flex flex-col items-center justify-center p-12 text-center">
                    <x-heroicon-o-queue-list class="h-12 w-12 text-slate-300 mb-4" />
                    <h3 class="text-lg font-bold text-slate-700">Belum Ada Kategori</h3>
                    <p class="mt-1 text-sm text-slate-400">Silakan tambahkan kategori jarak pertama Anda.</p>
                </div>
            @else
                <table class="datatable w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-6 py-4 font-bold">Nama Kategori</th>
                            <th class="px-6 py-4 font-bold">Status</th>
                            <th class="px-6 py-4 font-bold text-right" data-orderable="false">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($categories as $category)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 font-semibold text-slate-800">
                                <form action="{{ route('admin.distance-categories.update', $category) }}" method="POST" class="flex items-center gap-2">
                                    @csrf @method('PUT')
                                    <input type="text" name="name" value="{{ $category->name }}" class="rounded-lg border border-transparent hover:border-slate-200 focus:border-brand-500 focus:ring-brand-500 bg-transparent hover:bg-white px-2 py-1 text-sm font-semibold transition-all w-32" required>
                                    <button type="submit" class="invisible group-hover:visible text-xs font-bold text-brand-600 hover:text-brand-700">Simpan</button>
                                </form>
                            </td>
                            <td class="px-6 py-4">
                                <form action="{{ route('admin.distance-categories.toggle', $category) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $category->is_active ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }} transition-colors">
                                        {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <form action="{{ route('admin.distance-categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Yakin hapus kategori ini? Semua event yang menggunakan kategori ini akan terdampak.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-red-50 p-2 text-red-600 transition-colors hover:bg-red-100 hover:text-red-700" title="Hapus">
                                        <x-heroicon-o-trash class="h-4 w-4" />
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
