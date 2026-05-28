{{-- resources/views/items/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Lapor Barang Temuan')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="card">
        {{-- Header --}}
        <div class="bg-navy px-4 py-4">
            <h2 class="font-manrope font-bold text-white text-lg">Form Laporan Barang Temuan</h2>
            <p class="text-blue-200 text-xs mt-0.5">Isi informasi barang yang Anda temukan</p>
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

            <form action="{{ route('items.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                {{-- Nama Barang --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Nama Barang <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        placeholder="Contoh: Dompet Kulit"
                        class="form-input form-input-orange @error('name') error @enderror">
                    @error('name') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Deskripsi <span class="text-red-500">*</span></label>
                    <textarea name="description" rows="3"
                        placeholder="Jelaskan barang yang ditemukan dengan detail...."
                        class="form-input form-input-orange resize-none @error('description') error @enderror">{{ old('description') }}</textarea>
                    @error('description') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                {{-- Kategori --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                    <select name="category" class="form-input form-input-orange @error('category') error @enderror">
                        <option value="">Pilih kategori barang...</option>
                        @foreach(['Elektronik','Aksesori','Dokumen','Pakaian','Lainnya'] as $cat)
                        <option value="{{ $cat }}" {{ old('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                    @error('category') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                {{-- Lokasi --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Lokasi Ditemukan <span class="text-red-500">*</span></label>
                    <input type="text" name="location_found" value="{{ old('location_found') }}"
                        placeholder="Contoh: Gedung F 3.12"
                        class="form-input form-input-orange @error('location_found') error @enderror">
                    @error('location_found') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                {{-- Tanggal --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal Ditemukan <span class="text-red-500">*</span></label>
                    <input type="date" name="found_date" value="{{ old('found_date', date('Y-m-d')) }}"
                        max="{{ date('Y-m-d') }}"
                        class="form-input form-input-orange @error('found_date') error @enderror">
                    @error('found_date') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                {{-- Kontak --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Informasi Kontak <span class="text-red-500">*</span></label>
                    <input type="text" name="contact_info" value="{{ old('contact_info', Auth::user()->email) }}"
                        placeholder="Email atau nomor telepon anda"
                        class="form-input form-input-orange @error('contact_info') error @enderror">
                    @error('contact_info') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                {{-- Foto Barang (File Handling) --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Foto Barang</label>
                    <p class="text-xs text-gray-400 mb-2">Foto barang yang ditemukan agar memudahkan untuk menemukan pemiliknya</p>
                    <div id="upload-zone"
                        class="border-2 border-dashed border-gray-200 rounded-xl py-8 flex flex-col items-center gap-3 cursor-pointer hover:border-orange-300 hover:bg-orange-50 transition"
                        onclick="document.getElementById('photo-input').click()">
                        <div id="upload-preview" class="hidden">
                            <img id="preview-img" class="h-32 object-cover rounded-lg" alt="preview">
                        </div>
                        <div id="upload-placeholder" class="flex flex-col items-center gap-2">
                            <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <p class="text-xs text-gray-500">Klik untuk mengunggah file atau drag & drop</p>
                            <p class="text-xs text-gray-400">JPG, PNG, WEBP maksimal 2MB</p>
                        </div>
                        <input type="file" id="photo-input" name="photo" accept="image/*" class="hidden">
                    </div>
                    @error('photo') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                {{-- Buttons --}}
                <div class="flex gap-3 pt-2">
                    <button type="submit"
                        class="btn-primary w-2/3 py-2.5 font-medium flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        Submit
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

@push('scripts')
<script>
    // Preview foto sebelum upload (JavaScript)
    document.getElementById('photo-input').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        if (file.size > 2 * 1024 * 1024) {
            showToast('danger', 'Ukuran file melebihi 2MB!');
            this.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = (ev) => {
            document.getElementById('preview-img').src = ev.target.result;
            document.getElementById('upload-preview').classList.remove('hidden');
            document.getElementById('upload-placeholder').classList.add('hidden');
        };
        reader.readAsDataURL(file);
    });

    // Drag and drop
    const uploadZone = document.getElementById('upload-zone');
    uploadZone.addEventListener('dragover', e => {
        e.preventDefault();
        uploadZone.classList.add('border-orange-400');
    });
    uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('border-orange-400'));
    uploadZone.addEventListener('drop', e => {
        e.preventDefault();
        uploadZone.classList.remove('border-orange-400');
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('image/')) {
            const input = document.getElementById('photo-input');
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            input.files = dataTransfer.files;
            input.dispatchEvent(new Event('change'));
        }
    });
</script>
@endpush