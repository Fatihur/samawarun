@extends('layouts.admin')

@section('content')
    <h1 class="mb-8 font-display text-3xl font-bold uppercase italic text-slate-800">Edit Event</h1>

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.events.update', $event) }}" method="POST" enctype="multipart/form-data" class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm" data-loading-title="Memperbarui event" data-loading-message="Perubahan event sedang disimpan...">
        @csrf
        @method('PUT')
        @include('admin.events._form', ['event' => $event])
        <div class="mt-6 flex items-center gap-4">
            <button type="submit" data-loading-label="Menyimpan..." class="rounded-xl bg-brand-600 px-6 py-3 text-sm font-bold text-white shadow-sm transition-all hover:bg-brand-700 active:scale-95">Update Event</button>
            <a href="{{ route('admin.events.index') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-700 transition-colors">Batal</a>
        </div>
    </form>
@endsection
