@extends('layouts.app')

@section('title', 'Edit Laporan')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="card">
        <div class="bg-navy px-4 py-4">
            <h2 class="font-manrope font-bold text-white text-lg">Edit Laporan Barang</h2>
            <p class="text-blue-200 text-xs mt-0.5">Kode: {{ $item->report_code }}</p>
        </div>

        <div class="p-6">
            @if($errors->any())
            <div class="alert-error">
                @foreach($errors->all() as $error)
                <p>• {{ $error }}</p>
                @endforeach
            </div>
            @endif

            <form action="{{ route('items.update', $item) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Nama Barang <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $item->name) }}"
                        class="form-input form-input-orange">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Deskripsi <span class="text-red-500">*</span></label>
                    <textarea name="description" rows="3"
                        class="form-input form-input-orange resize-none">{{ old('description', $item->description) }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                    <select name="category" class="form-input form-input-orange">
                        @foreach(['Elektronik','Aksesori','Dokumen','Pakaian','Lainnya'] as $cat)
                        <option value="{{ $cat }}" {{ old('category', $item->category) == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Lokasi Ditemukan <span class="text-red-500">*</span></label>
                    <input type="text" name="location_found" value="{{ old('location_found', $item->location_found) }}"
                        class="form-input form-input-orange">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal Ditemukan <span class="text-red-500">*</span></label>
                    <input type="date" name="found_date" value="{{ old('found_date', $item->found_date->format('Y-m-d')) }}"
                        max="{{ date('Y-m-d') }}"
                        class="form-input form-input-orange">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Informasi Kontak <span class="text-red-500">*</span></label>
                    <input type="text" name="contact_info" value="{{ old('contact_info', $item->contact_info) }}"
                        class="form-input form-input-orange">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Ganti Foto</label>
                    @if($item->photo)
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $item->photo) }}" class="h-24 rounded-lg object-cover" alt="foto saat ini">
                        <p class="text-xs text-gray-400 mt-1">Foto saat ini. Upload baru untuk mengganti.</p>
                    </div>
                    @endif
                    <input type="file" name="photo" accept="image/*"
                        class="w-full text-xs text-gray-500 file:mr-2 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:bg-orange-50 file:text-orange-600 hover:file:bg-orange-100 border border-gray-200 rounded-lg p-2">
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit"
                        class="btn-primary w-2/3 py-2.5 font-medium">
                        Simpan Perubahan
                    </button>
                    <a href="{{ route('my-reports') }}"
                        class="btn btn-outline flex-1 text-center py-2.5">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection