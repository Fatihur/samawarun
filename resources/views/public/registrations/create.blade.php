@extends('layouts.public')

@section('content')
    <section class="px-6 py-16 lg:px-40 bg-background-dark" x-data="registrationForm()">
        <div class="mx-auto max-w-2xl">
            {{-- Back Link --}}
            <a href="{{ route('events.show', $event) }}" class="inline-flex items-center gap-2 text-sm font-bold text-gray-400 hover:text-primary transition-colors mb-8">
                <x-heroicon-o-arrow-left class="h-4 w-4" />
                Kembali ke detail event
            </a>

            {{-- Header --}}
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white font-display uppercase italic">Pendaftaran</h1>
                <p class="mt-2 text-gray-400">{{ $event->name }} &bull; Rp {{ number_format($event->price, 0, ',', '.') }}</p>
                @if($event->registration_deadline)
                    <p class="mt-2 text-sm text-amber-300">Deadline pendaftaran: {{ $event->registration_deadline->translatedFormat('d F Y, H:i') }}</p>
                @endif
            </div>

            {{-- Step Indicator --}}
            <div class="mb-10 flex items-center justify-between">
                <template x-for="(label, index) in steps" :key="index">
                    <div class="flex items-center" :class="index < steps.length - 1 ? 'flex-1' : ''">
                        <div class="flex flex-col items-center">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold transition-all duration-300"
                                 :class="currentStep > index ? 'bg-primary text-background-dark' : (currentStep === index ? 'bg-primary text-background-dark shadow-[0_0_15px_rgba(48,232,122,0.4)]' : 'bg-white/10 text-gray-500')">
                                <template x-if="currentStep > index">
                                    <x-heroicon-o-check class="h-5 w-5" />
                                </template>
                                <template x-if="currentStep <= index">
                                    <span x-text="index + 1"></span>
                                </template>
                            </div>
                            <span class="mt-2 text-[10px] font-bold uppercase tracking-wider hidden sm:block"
                                  :class="currentStep >= index ? 'text-primary' : 'text-gray-600'"
                                  x-text="label"></span>
                        </div>
                        <template x-if="index < steps.length - 1">
                            <div class="mx-3 h-px flex-1 transition-colors duration-300"
                                 :class="currentStep > index ? 'bg-primary' : 'bg-white/10'"></div>
                        </template>
                    </div>
                </template>
            </div>

            {{-- Error Messages --}}
            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-red-500/30 bg-red-500/10 px-5 py-4 text-sm text-red-400">
                    <p class="font-bold mb-2">Terdapat kesalahan:</p>
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Form --}}
            <form action="{{ route('registrations.store', $event) }}" method="POST" enctype="multipart/form-data" data-loading-title="Mengirim pendaftaran" data-loading-message="Data peserta dan berkas sedang dikirim, mohon tunggu...">
                @csrf

                {{-- Step 1: Data Diri --}}
                <div x-show="currentStep === 0" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="rounded-2xl border border-white/10 bg-secondary-dark p-6 sm:p-8">
                        <h2 class="text-xl font-bold text-white mb-1 font-display">Data Diri</h2>
                        <p class="text-sm text-gray-500 mb-6">Lengkapi informasi data diri Anda</p>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="mb-1.5 block text-sm font-medium text-gray-300">Nama Lengkap <span class="text-red-400">*</span></label>
                                <input name="name" value="{{ old('name') }}" class="w-full rounded-xl border border-white/10 bg-background-dark px-4 py-3 text-sm text-white placeholder-gray-500 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary transition-colors" placeholder="Nama lengkap sesuai KTP" required>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-300">Tanggal Lahir <span class="text-red-400">*</span></label>
                                <input name="birth_date" value="{{ old('birth_date') }}" type="date" class="w-full rounded-xl border border-white/10 bg-background-dark px-4 py-3 text-sm text-white focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary transition-colors" required>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-300">Jenis Kelamin <span class="text-red-400">*</span></label>
                                <select name="gender" class="w-full rounded-xl border border-white/10 bg-background-dark px-4 py-3 text-sm text-white focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary transition-colors" required>
                                    <option value="">Pilih jenis kelamin</option>
                                    <option value="male" @selected(old('gender') === 'male')>Laki-laki</option>
                                    <option value="female" @selected(old('gender') === 'female')>Perempuan</option>
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-1.5 block text-sm font-medium text-gray-300">NIK <span class="text-red-400">*</span></label>
                                <input name="nik" value="{{ old('nik') }}" class="w-full rounded-xl border border-white/10 bg-background-dark px-4 py-3 text-sm text-white placeholder-gray-500 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary transition-colors" placeholder="Nomor Induk Kependudukan (16 digit)" required>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Step 2: Kontak --}}
                <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="rounded-2xl border border-white/10 bg-secondary-dark p-6 sm:p-8">
                        <h2 class="text-xl font-bold text-white mb-1 font-display">Informasi Kontak</h2>
                        <p class="text-sm text-gray-500 mb-6">Nomor HP & alamat yang bisa dihubungi</p>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-300">Nomor HP <span class="text-red-400">*</span></label>
                                <input name="phone" value="{{ old('phone') }}" class="w-full rounded-xl border border-white/10 bg-background-dark px-4 py-3 text-sm text-white placeholder-gray-500 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary transition-colors" placeholder="08xxxxxxxxxx" required>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-300">Email <span class="text-red-400">*</span></label>
                                <input name="email" value="{{ old('email') }}" type="email" class="w-full rounded-xl border border-white/10 bg-background-dark px-4 py-3 text-sm text-white placeholder-gray-500 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary transition-colors" placeholder="email@contoh.com" required>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-1.5 block text-sm font-medium text-gray-300">Alamat <span class="text-red-400">*</span></label>
                                <textarea name="address" rows="3" class="w-full rounded-xl border border-white/10 bg-background-dark px-4 py-3 text-sm text-white placeholder-gray-500 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary transition-colors" placeholder="Alamat lengkap" required>{{ old('address') }}</textarea>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-300">Status Kontak Darurat <span class="text-red-400">*</span></label>
                                <select name="emergency_contact_relationship" class="w-full rounded-xl border border-white/10 bg-background-dark px-4 py-3 text-sm text-white focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary transition-colors" required>
                                    <option value="">Pilih hubungan keluarga</option>
                                    <option value="father" @selected(old('emergency_contact_relationship') === 'father')>Ayah</option>
                                    <option value="mother" @selected(old('emergency_contact_relationship') === 'mother')>Ibu</option>
                                    <option value="husband" @selected(old('emergency_contact_relationship') === 'husband')>Suami</option>
                                    <option value="wife" @selected(old('emergency_contact_relationship') === 'wife')>Istri</option>
                                    <option value="child" @selected(old('emergency_contact_relationship') === 'child')>Anak</option>
                                    <option value="other_family" @selected(old('emergency_contact_relationship') === 'other_family')>Keluarga Lain</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-300">Nama Kontak Darurat <span class="text-red-400">*</span></label>
                                <input name="emergency_contact_name" value="{{ old('emergency_contact_name') }}" class="w-full rounded-xl border border-white/10 bg-background-dark px-4 py-3 text-sm text-white placeholder-gray-500 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary transition-colors" placeholder="Nama lengkap kontak darurat" required>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-1.5 block text-sm font-medium text-gray-300">Nomor Kontak Darurat <span class="text-red-400">*</span></label>
                                <input name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}" class="w-full rounded-xl border border-white/10 bg-background-dark px-4 py-3 text-sm text-white placeholder-gray-500 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary transition-colors" placeholder="08xxxxxxxxxx" required>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Step 3: Detail Event --}}
                <div x-show="currentStep === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="rounded-2xl border border-white/10 bg-secondary-dark p-6 sm:p-8">
                        <h2 class="text-xl font-bold text-white mb-1 font-display">Detail Perlombaan</h2>
                        <p class="text-sm text-gray-500 mb-6">Pilih kategori jarak dan ukuran jersey</p>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-300">Kategori Jarak <span class="text-red-400">*</span></label>
                                <div class="grid grid-cols-3 gap-3">
                                    @foreach ($event->distanceCategories as $category)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="distance_category" value="{{ strtoupper($category->name) }}" class="peer hidden" @checked(strtoupper((string) old('distance_category')) === strtoupper($category->name)) required>
                                        <div class="flex items-center justify-center rounded-xl border border-white/10 bg-background-dark py-4 text-lg font-bold text-gray-400 transition-all peer-checked:border-primary peer-checked:bg-primary/10 peer-checked:text-primary peer-checked:shadow-[0_0_15px_rgba(48,232,122,0.15)] hover:bg-white/5">
                                            {{ strtoupper($category->name) }}
                                        </div>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-300">Ukuran Jersey <span class="text-red-400">*</span></label>
                                <div class="grid grid-cols-5 gap-2">
                                    @foreach (['S', 'M', 'L', 'XL', 'XXL'] as $size)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="jersey_size" value="{{ $size }}" class="peer hidden" @checked(old('jersey_size') === $size) required>
                                        <div class="flex items-center justify-center rounded-xl border border-white/10 bg-background-dark py-3 text-sm font-bold text-gray-400 transition-all peer-checked:border-primary peer-checked:bg-primary/10 peer-checked:text-primary hover:bg-white/5">
                                            {{ $size }}
                                        </div>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Step 4: Pembayaran & Upload --}}
                <div x-show="currentStep === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="rounded-2xl border border-white/10 bg-secondary-dark p-6 sm:p-8">
                        <h2 class="text-xl font-bold text-white mb-1 font-display">Pembayaran & Upload</h2>
                        <p class="text-sm text-gray-500 mb-6">Transfer biaya pendaftaran lalu upload bukti</p>

                        {{-- Bank Account Info --}}
                        <div class="mb-6 rounded-xl border border-primary/20 bg-primary/5 p-5">
                            <div class="flex items-start gap-4">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary/10">
                                    <x-heroicon-o-credit-card class="h-5 w-5 text-primary" />
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs font-bold uppercase tracking-widest text-primary mb-2">Transfer Pembayaran</p>
                                    <div class="rounded-lg bg-background-dark px-4 py-3 mb-2">
                                        <p class="text-xs text-gray-500 mb-1">Nomor Rekening</p>
                                        <p class="text-xl font-black text-white font-mono tracking-wider">{{ $event->bank_account ?? '-' }}</p>
                                    </div>
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-sm text-gray-400">Jumlah:</span>
                                        <span class="text-lg font-bold text-white">Rp {{ number_format($event->price, 0, ',', '.') }}</span>
                                    </div>
                                    @if($event->contact)
                                    <p class="mt-2 text-xs text-gray-500">Konfirmasi ke panitia: <span class="text-gray-300 font-semibold">{{ $event->contact }}</span></p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-5">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-300">Upload KTP <span class="text-red-400">*</span></label>
                                <div class="relative">
                                    <input name="ktp_file" type="file" class="w-full rounded-xl border border-white/10 bg-background-dark px-4 py-3 text-sm text-gray-400 file:mr-4 file:rounded-lg file:border-0 file:bg-primary/10 file:px-4 file:py-2 file:text-sm file:font-bold file:text-primary focus:outline-none" accept=".jpg,.jpeg,.png,.pdf" required>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Format: JPG, PNG, atau PDF (maks 2MB)</p>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-300">Upload Bukti Transfer <span class="text-red-400">*</span></label>
                                <div class="relative">
                                    <input name="transfer_proof" type="file" class="w-full rounded-xl border border-white/10 bg-background-dark px-4 py-3 text-sm text-gray-400 file:mr-4 file:rounded-lg file:border-0 file:bg-primary/10 file:px-4 file:py-2 file:text-sm file:font-bold file:text-primary focus:outline-none" accept=".jpg,.jpeg,.png,.pdf" required>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Screenshot/foto bukti transfer pembayaran</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Navigation Buttons --}}
                <div class="mt-8 flex items-center justify-between">
                    <button type="button" x-show="currentStep > 0" @click="prevStep()" class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-transparent px-6 py-3 text-sm font-bold text-white hover:bg-white/5 transition-all active:scale-95">
                        <x-heroicon-o-arrow-left class="h-4 w-4" />
                        Sebelumnya
                    </button>
                    <div x-show="currentStep === 0"></div>

                    <button type="button" x-show="currentStep < 3" @click="nextStep()" class="inline-flex items-center gap-2 rounded-xl bg-primary px-6 py-3 text-sm font-bold text-background-dark shadow-[0_0_20px_rgba(48,232,122,0.2)] transition-all hover:bg-primary-hover active:scale-95">
                        Selanjutnya
                        <x-heroicon-o-arrow-right class="h-4 w-4" />
                    </button>

                    <button type="submit" x-show="currentStep === 3" data-loading-label="Mengirim..." class="inline-flex items-center gap-2 rounded-xl bg-primary px-8 py-3 text-sm font-bold text-background-dark shadow-[0_0_20px_rgba(48,232,122,0.3)] transition-all hover:bg-primary-hover active:scale-95">
                        <x-heroicon-o-check-circle class="h-4 w-4" />
                        Kirim Pendaftaran
                    </button>
                </div>
            </form>
        </div>
    </section>

    <script>
        function registrationForm() {
            return {
                currentStep: 0,
                steps: ['Data Diri', 'Kontak', 'Lomba', 'Bayar'],
                nextStep() {
                    if (this.currentStep < 3) {
                        this.currentStep++;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },
                prevStep() {
                    if (this.currentStep > 0) {
                        this.currentStep--;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                }
            }
        }
    </script>
@endsection
