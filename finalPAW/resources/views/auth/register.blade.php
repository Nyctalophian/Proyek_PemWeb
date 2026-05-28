{{-- resources/views/auth/register.blade.php --}}
@extends('layouts.app')

@section('title', 'Daftar')

@section('content')
<div class="max-w-md mx-auto">
    <div class="card">
        <div class="card-header">
            <h2 class="font-manrope font-bold text-white text-xl">Daftar Akun Baru</h2>
            <p class="text-blue-200 text-xs mt-1">FILKOM Universitas Brawijaya</p>
        </div>

        <div class="p-6">
            @if($errors->any())
                <div class="alert-error">
                    @foreach($errors->all() as $error)
                        <p>• {{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        placeholder="Nama sesuai KTM"
                        class="form-input @error('name') error @enderror">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">NIM <span class="text-red-500">*</span></label>
                        <input type="text" name="nim" value="{{ old('nim') }}"
                            placeholder="245150207111..."
                            class="form-input @error('nim') error @enderror">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Angkatan <span class="text-red-500">*</span></label>
                        <input type="text" name="angkatan" value="{{ old('angkatan') }}"
                            placeholder="2024" maxlength="4"
                            class="form-input @error('angkatan') error @enderror">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Jurusan <span class="text-red-500">*</span></label>
                    <select name="jurusan"
                        class="form-input @error('jurusan') error @enderror">
                        <option value="">Pilih jurusan...</option>
                        <option value="Teknik Informatika" {{ old('jurusan') == 'Teknik Informatika' ? 'selected' : '' }}>Teknik Informatika</option>
                        <option value="Sistem Informasi" {{ old('jurusan') == 'Sistem Informasi' ? 'selected' : '' }}>Sistem Informasi</option>
                        <option value="Pendidikan Teknologi Informasi" {{ old('jurusan') == 'Pendidikan Teknologi Informasi' ? 'selected' : '' }}>Pendidikan Teknologi Informasi</option>
                        <option value="Teknik Komputer" {{ old('jurusan') == 'Teknik Komputer' ? 'selected' : '' }}>Teknik Komputer</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        placeholder="nama@ub.ac.id"
                        class="form-input @error('email') error @enderror">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">No. Telepon <span class="text-red-500">*</span></label>
                    <input type="tel" name="phone" value="{{ old('phone') }}"
                        placeholder="08xxxxxxxxxx"
                        class="form-input @error('phone') error @enderror">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password"
                        placeholder="Minimal 6 karakter"
                        class="form-input @error('password') error @enderror">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Konfirmasi Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password_confirmation"
                        placeholder="Ulangi password"
                        class="form-input">
                </div>

                <button type="submit" class="btn-primary w-full">
                    Daftar Sekarang
                </button>

                <p class="text-center text-xs text-gray-500">
                    Sudah punya akun?
                    <a href="{{ route('login') }}" class="link-primary">Masuk di sini</a>
                </p>
            </form>
        </div>
    </div>
</div>
@endsection