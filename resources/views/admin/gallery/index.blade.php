@extends('layouts.admin')

@section('content')
    <div class="mb-8">
        <h1 class="font-display text-3xl font-bold uppercase italic text-slate-800">Kelola Galeri</h1>
        <p class="mt-1 text-sm text-slate-500">Upload dan atur foto galeri yang ditampilkan di halaman utama.</p>
    </div>

    {{-- Upload Form --}}
    <div class="mb-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500 mb-4">Tambah Foto</h2>
        <form action="{{ route('admin.gallery.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row items-start sm:items-end gap-4" data-loading-title="Mengunggah foto" data-loading-message="Foto galeri sedang diunggah...">
            @csrf
            <div class="flex-1 w-full">
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Keterangan (opsional)</label>
                <input type="text" name="title" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 focus:border-brand-500 focus:ring-brand-500 transition-colors" placeholder="Contoh: Fun Run 2025" />
            </div>
            <div class="w-full sm:w-auto">
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">File Gambar</label>
                <input type="file" name="image" accept="image/*" required class="w-full text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-600 file:px-4 file:py-2 file:text-sm file:font-bold file:text-white hover:file:bg-brand-700 file:cursor-pointer file:transition-colors" />
            </div>
            <button type="submit" data-loading-label="Mengunggah..." class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:bg-brand-700 active:scale-95 shrink-0">
                <x-heroicon-o-arrow-up-tray class="h-4 w-4" />
                Upload
            </button>
        </form>
        @error('image')
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Gallery Grid --}}
    @if($galleries->isEmpty())
    <div class="flex flex-col items-center justify-center rounded-2xl border border-slate-200 bg-white p-16 text-center">
        <x-heroicon-o-photo class="h-12 w-12 text-slate-300 mb-4" />
        <h3 class="text-lg font-bold text-slate-700">Belum Ada Foto</h3>
        <p class="mt-1 text-sm text-slate-400">Upload foto pertama untuk galeri anda.</p>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($galleries as $gallery)
        <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="aspect-[4/3] overflow-hidden">
                <img src="{{ asset('storage/' . $gallery->image_path) }}" alt="{{ $gallery->title ?? 'Gallery' }}" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105" />
            </div>

            {{-- Overlay Actions --}}
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                <div class="absolute bottom-0 left-0 right-0 p-4 flex items-end justify-between">
                    <p class="text-white text-sm font-semibold truncate flex-1 mr-2">{{ $gallery->title ?? 'Tanpa keterangan' }}</p>
                    <div class="flex gap-1.5 shrink-0">
                        {{-- Toggle Active --}}
                        <form action="{{ route('admin.gallery.toggle', $gallery) }}" method="POST" data-loading-title="Mengubah status foto" data-loading-message="Status foto galeri sedang diperbarui...">
                            @csrf @method('PATCH')
                            <button type="submit" data-loading-label="Memproses..." class="flex h-8 w-8 items-center justify-center rounded-lg {{ $gallery->is_active ? 'bg-emerald-500 text-white' : 'bg-white/20 text-white/70' }} backdrop-blur-sm transition-colors hover:bg-emerald-600 hover:text-white" title="{{ $gallery->is_active ? 'Nonaktifkan' : 'Aktifkan' }}" aria-label="{{ $gallery->is_active ? 'Nonaktifkan' : 'Aktifkan' }} foto {{ $gallery->title ?? 'galeri' }}">
                                <x-heroicon-o-eye class="h-4 w-4" />
                            </button>
                        </form>
                        {{-- Delete --}}
                        <form action="{{ route('admin.gallery.destroy', $gallery) }}" method="POST" onsubmit="return confirm('Yakin hapus foto ini?')" data-loading-title="Menghapus foto" data-loading-message="Foto galeri sedang dihapus...">
                            @csrf @method('DELETE')
                            <button type="submit" data-loading-label="Menghapus..." class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/20 backdrop-blur-sm text-white/70 transition-colors hover:bg-red-500 hover:text-white" title="Hapus" aria-label="Hapus foto {{ $gallery->title ?? 'galeri' }}">
                                <x-heroicon-o-trash class="h-4 w-4" />
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Status Badge --}}
            @unless($gallery->is_active)
            <div class="absolute top-3 left-3">
                <span class="rounded-lg bg-slate-800/80 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-white backdrop-blur-sm">Nonaktif</span>
            </div>
            @endunless

            {{-- Edit Title Form --}}
            <div class="px-4 py-3 border-t border-slate-100">
                <form action="{{ route('admin.gallery.update', $gallery) }}" method="POST" class="flex gap-2" data-loading-title="Menyimpan keterangan foto" data-loading-message="Perubahan keterangan galeri sedang disimpan...">
                    @csrf @method('PUT')
                    <input type="text" name="title" value="{{ $gallery->title }}" class="flex-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-700 focus:border-brand-500 focus:ring-brand-500" placeholder="Keterangan..." />
                    <button type="submit" data-loading-label="Menyimpan..." class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-600 transition-colors hover:bg-slate-200" title="Simpan" aria-label="Simpan keterangan foto {{ $gallery->title ?? 'galeri' }}">
                        <x-heroicon-o-check class="h-4 w-4" />
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @endif
@endsection
