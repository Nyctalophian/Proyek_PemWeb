{{-- resources/views/claims/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Klaim Barang')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="card">
        {{-- Header --}}
        <div class="bg-navy px-4 py-4">
            <h2 class="font-manrope font-bold text-white text-lg">Form Klaim Barang Temuan</h2>
            <p class="text-blue-200 text-xs mt-0.5">Isi informasi untuk membuktikan kepemilikan barang</p>
        </div>

        {{-- Item info card --}}
        <div class="mx-4 mt-4 p-3 bg-blue-50 rounded-xl border border-blue-100 flex gap-3 items-start">
            <img src="{{ $item->photoUrl() }}" alt="{{ $item->name }}"
                class="w-16 h-16 thumbnail rounded-lg">
            <div>
                <p class="font-semibold text-sm text-gray-800">{{ $item->name }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ $item->location_found }}</p>
                <p class="text-xs text-gray-400">Ditemukan {{ $item->found_date->format('d/m/Y') }}</p>
            </div>
        </div>

        {{-- Form --}}
        <div class="p-6">
            @if($errors->any())
                <div class="alert-error">
                    <ul class="space-y-1">
                        @foreach($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('claims.store', $item) }}" method="POST" class="space-y-4">
                @csrf

                {{-- Nama Barang --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Nama Barang <span class="text-red-500">*</span></label>
                    <input type="text" name="item_name" value="{{ old('item_name', $item->name) }}"
                        placeholder="Contoh: Dompet Kulit"
                        class="form-input">
                    @error('item_name') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                {{-- Bukti Kepemilikan --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Bukti Kepemilikan <span class="text-red-500">*</span></label>
                    <textarea name="proof_of_ownership" rows="3"
                        placeholder="Jelaskan bagaimana anda bisa membuktikan kepemilikan. Contoh: ada stiker nama di dalamnya, ada foto di galeri, nomor seri, dll...."
                        class="form-input resize-none @error('proof_of_ownership') error @enderror">{{ old('proof_of_ownership') }}</textarea>
                    @error('proof_of_ownership') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                {{-- Ciri-ciri Khusus --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Ciri-ciri Khusus <span class="text-red-500">*</span></label>
                    <textarea name="special_characteristics" rows="2"
                        placeholder="Sebutkan ciri-ciri dari barang yang hanya pemilik asli yang tahu"
                        class="form-input resize-none @error('special_characteristics') error @enderror">{{ old('special_characteristics') }}</textarea>
                    @error('special_characteristics') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                {{-- Nomor Telepon --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Nomor Telepon <span class="text-red-500">*</span></label>
                    <input type="tel" name="phone" value="{{ old('phone', Auth::user()->phone) }}"
                        placeholder="Contoh: 08265217621"
                        class="form-input @error('phone') error @enderror">
                    @error('phone') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', Auth::user()->email) }}"
                        placeholder="Contoh: nama@gmail.com"
                        class="form-input @error('email') error @enderror">
                    @error('email') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                {{-- Disclaimer --}}
                <div class="warning-box">
                    <p class="text-xs text-yellow-700">
                        <strong>⚠️ Perhatian:</strong> Pengajuan klaim palsu dapat dikenakan sanksi akademik.
                        Pastikan informasi yang Anda berikan akurat dan dapat diverifikasi.
                    </p>
                </div>

                {{-- Buttons --}}
                <div class="flex gap-3 pt-2">
                    <button type="submit"
                        class="btn-primary w-2/3 py-2.5 font-medium flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Ajukan Klaim
                    </button>
                    <a href="{{ route('items.index') }}"
                        class="btn btn-outline flex-1 text-center py-2.5">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection