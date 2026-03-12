@extends('layouts.public')

@section('content')
    <section class="px-6 py-16 lg:px-40 bg-background-dark">
        <div class="mx-auto max-w-2xl">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white font-display uppercase italic">Upload Pembayaran</h1>
                <p class="mt-2 text-gray-400">{{ $participant->event?->name }} &bull; {{ $participant->distance_category }}</p>
            </div>

            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-red-500/30 bg-red-500/10 px-5 py-4 text-sm text-red-400">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-6 rounded-2xl border border-primary/20 bg-primary/5 p-5 text-sm text-gray-300">
                <p class="font-bold text-white">Instruksi Pembayaran</p>
                <div class="mt-4 rounded-2xl border border-primary/20 bg-background-dark/60 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-primary">Nominal Pembayaran</p>
                    <p class="mt-2 text-3xl font-black text-white">{{ $participant->formatted_payment_amount }}</p>
                    <p class="mt-2 text-xs leading-relaxed text-gray-400">Harap transfer sesuai nominal kategori <span class="font-semibold text-white">{{ $participant->distance_category }}</span> agar proses review pembayaran lebih cepat.</p>
                </div>
                <p class="mt-2">Transfer ke rekening: <span class="font-semibold text-primary">{{ $participant->event?->bank_account ?? '-' }}</span></p>
                @if ($participant->event?->contact)
                    <p class="mt-2">Kontak panitia: <span class="font-semibold text-white">{{ $participant->event->contact }}</span></p>
                @endif
            </div>

            <form method="POST" enctype="multipart/form-data" class="rounded-2xl border border-slate-200 bg-white/5 p-6 shadow-sm">
                @csrf
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-300">Upload Bukti Transfer <span class="text-red-400">*</span></label>
                    <input name="transfer_proof" type="file" class="w-full rounded-xl border border-white/10 bg-background-dark px-4 py-3 text-sm text-gray-400 file:mr-4 file:rounded-lg file:border-0 file:bg-primary/10 file:px-4 file:py-2 file:text-sm file:font-bold file:text-primary focus:outline-none" accept=".jpg,.jpeg,.png,.pdf" required>
                    <p class="mt-1 text-xs text-gray-500">Format: JPG, PNG, atau PDF (maks 2MB)</p>
                </div>

                <div class="mt-6">
                    <button type="submit" class="rounded-xl bg-primary px-6 py-3 text-sm font-bold text-background-dark shadow-sm transition-all hover:bg-primary-hover active:scale-95">
                        Kirim Bukti Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </section>
@endsection
