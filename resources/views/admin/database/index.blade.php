@extends('layouts.admin')

@section('content')
    <div class="mb-8 flex items-center justify-between">
        <h1 class="font-display text-3xl font-bold uppercase italic text-slate-800">Database</h1>
        <p class="text-sm font-medium text-slate-500">Manajemen Backup & Restore</p>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4">
            <div class="flex items-center gap-3">
                <x-heroicon-o-check-circle class="h-5 w-5 text-emerald-600" />
                <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4">
            <div class="flex items-center gap-3">
                <x-heroicon-o-x-circle class="h-5 w-5 text-red-600" />
                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Backup Section --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-6 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-100 text-brand-600">
                    <x-heroicon-o-cloud-arrow-up class="h-5 w-5" />
                </div>
                <div>
                    <h2 class="font-display text-lg font-bold text-slate-800">Backup Database</h2>
                    <p class="text-sm text-slate-500">Buat backup database saat ini</p>
                </div>
            </div>

            <form action="{{ route('admin.database.backup') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Tipe Backup</label>
                    <select name="type" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors">
                        <option value="full">Full Database</option>
                        <option value="data">Data Only (tanpa struktur)</option>
                    </select>
                </div>

                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-6 py-3 text-sm font-bold text-white shadow-sm transition-all hover:bg-brand-700 active:scale-95">
                    <x-heroicon-o-circle-stack class="h-4 w-4" />
                    Buat Backup
                </button>
            </form>
        </div>

        {{-- Restore Section --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-6 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                    <x-heroicon-o-cloud-arrow-down class="h-5 w-5" />
                </div>
                <div>
                    <h2 class="font-display text-lg font-bold text-slate-800">Restore Database</h2>
                    <p class="text-sm text-slate-500">Restore dari file backup yang tersedia</p>
                </div>
            </div>

            @if(count($backups) > 0)
                <form action="{{ route('admin.database.restore') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Pilih Backup</label>
                        <select name="backup_file" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors">
                            @foreach($backups as $backup)
                                <option value="{{ $backup['filename'] }}">
                                    {{ $backup['filename'] }} ({{ $backup['size'] }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-3">
                        <div class="flex items-start gap-2">
                            <x-heroicon-o-exclamation-triangle class="h-4 w-4 text-amber-600 mt-0.5" />
                            <p class="text-xs text-amber-800">
                                <strong>Perhatian:</strong> Restore akan mengganti semua data saat ini. Pastikan Anda sudah membuat backup terlebih dahulu.
                            </p>
                        </div>
                    </div>

                    <button type="submit" onclick="return confirm('Yakin ingin restore database? Data saat ini akan diganti.')" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-3 text-sm font-bold text-white shadow-sm transition-all hover:bg-blue-700 active:scale-95">
                        <x-heroicon-o-arrow-path class="h-4 w-4" />
                        Restore Database
                    </button>
                </form>
            @else
                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center">
                    <x-heroicon-o-inbox class="mx-auto h-10 w-10 text-slate-400" />
                    <p class="mt-2 text-sm text-slate-500">Belum ada backup tersedia</p>
                    <p class="text-xs text-slate-400">Buat backup terlebih dahulu</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Data Management Section --}}
    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-6 flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-100 text-red-600">
                <x-heroicon-o-trash class="h-5 w-5" />
            </div>
            <div>
                <h2 class="font-display text-lg font-bold text-slate-800">Hapus Data</h2>
                <p class="text-sm text-slate-500">Hapus data dari tabel tertentu</p>
            </div>
        </div>

        <form action="{{ route('admin.database.delete') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($tables as $key => $table)
                    <label class="relative flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 transition-all hover:border-red-200 hover:bg-red-50/50 has-[:checked]:border-red-300 has-[:checked]:bg-red-50">
                        <input type="checkbox" name="tables[]" value="{{ $key }}" class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500">
                        <div class="flex-1">
                            <p class="font-semibold text-slate-800">{{ $table['name'] }}</p>
                            <p class="text-xs text-slate-500">{{ $table['description'] }}</p>
                            <p class="mt-1 text-lg font-bold text-slate-700">{{ $table['count'] }} <span class="text-xs font-normal text-slate-500">records</span></p>
                        </div>
                    </label>
                @endforeach
            </div>

            <div class="rounded-lg border border-red-200 bg-red-50 p-3">
                <div class="flex items-start gap-2">
                    <x-heroicon-o-exclamation-triangle class="h-4 w-4 text-red-600 mt-0.5" />
                    <p class="text-xs text-red-800">
                        <strong>Perhatian:</strong> Data yang dihapus tidak dapat dikembalikan. Pastikan Anda sudah membuat backup terlebih dahulu.
                    </p>
                </div>
            </div>

            <button type="submit" onclick="return confirm('Yakin ingin menghapus data yang dipilih? Data yang dihapus tidak dapat dikembalikan.')" class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-6 py-3 text-sm font-bold text-white shadow-sm transition-all hover:bg-red-700 active:scale-95">
                <x-heroicon-o-trash class="h-4 w-4" />
                Hapus Data Terpilih
            </button>
        </form>
    </div>

    {{-- Backup List Section --}}
    @if(count($backups) > 0)
        <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-6 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600">
                    <x-heroicon-o-archive-box class="h-5 w-5" />
                </div>
                <div>
                    <h2 class="font-display text-lg font-bold text-slate-800">Daftar Backup</h2>
                    <p class="text-sm text-slate-500">{{ count($backups) }} file backup tersedia</p>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Nama File</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Ukuran</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Dibuat</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-700">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($backups as $backup)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <x-heroicon-o-document-text class="h-4 w-4 text-slate-400" />
                                        <span class="font-medium text-slate-800">{{ $backup['filename'] }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ $backup['size'] }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $backup['created_at'] }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.database.download', $backup['filename']) }}" class="inline-flex items-center gap-1 rounded-lg bg-brand-100 px-3 py-1.5 text-xs font-semibold text-brand-700 transition-colors hover:bg-brand-200">
                                            <x-heroicon-o-arrow-down class="h-3 w-3" />
                                            Download
                                        </a>
                                        <form action="{{ route('admin.database.backup.destroy', $backup['filename']) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus backup ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-red-100 px-3 py-1.5 text-xs font-semibold text-red-700 transition-colors hover:bg-red-200">
                                                <x-heroicon-o-trash class="h-3 w-3" />
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
