{{-- resources/views/profile/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="max-w-xl mx-auto space-y-4">

    {{-- Header info --}}
    <div class="bg-navy rounded-2xl px-5 py-4">
        <p class="text-xs text-white/60">MAHASISWA UB</p>
        <p class="text-white font-mono text-sm">{{ $user->nim ?? '-' }}</p>
        <p class="text-orange text-sm">{{ $user->email }}</p>
    </div>

    {{-- Update Profile Form --}}
    <div class="bg-white rounded-2xl overflow-hidden shadow-sm">
        <div class="bg-navy px-5 py-3">
            <h2 class="font-manrope font-bold text-white">Profil Saya</h2>
        </div>

        <div class="p-5">
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 rounded-xl p-3 mb-4 text-xs text-green-700">
                    {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-xl p-3 mb-4">
                    @foreach($errors->all() as $error)
                        <p class="text-xs text-red-600">• {{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                @csrf
                @method('PUT')

                {{-- Avatar --}}
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-16 h-16 rounded-full overflow-hidden bg-orange flex items-center justify-center flex-shrink-0">
                        @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" class="w-full h-full object-cover" alt="avatar">
                        @else
                            <span class="text-white text-2xl font-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                        @endif
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Foto Profil</label>
                        <input type="file" name="avatar" accept="image/*"
                            class="text-xs text-gray-500 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:bg-orange-50 file:text-orange-600 hover:file:bg-orange-100">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-200 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Angkatan</label>
                    <input type="text" value="{{ $user->angkatan }}" disabled
                        class="w-full border border-gray-100 bg-gray-50 rounded-lg px-4 py-2.5 text-sm text-gray-400 cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Status Akademik</label>
                    <input type="text" value="Aktif" disabled
                        class="w-full border border-gray-100 bg-gray-50 rounded-lg px-4 py-2.5 text-sm text-gray-400 cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Fakultas</label>
                    <input type="text" value="{{ $user->fakultas ?? 'Ilmu Komputer' }}" disabled
                        class="w-full border border-gray-100 bg-gray-50 rounded-lg px-4 py-2.5 text-sm text-gray-400 cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Jurusan</label>
                    <input type="text" value="{{ $user->jurusan }}" disabled
                        class="w-full border border-gray-100 bg-gray-50 rounded-lg px-4 py-2.5 text-sm text-gray-400 cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Email</label>
                    <input type="text" value="{{ $user->email }}" disabled
                        class="w-full border border-gray-100 bg-gray-50 rounded-lg px-4 py-2.5 text-sm text-gray-400 cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Nomor Telepon</label>
                    <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}"
                        placeholder="08xxxxxxxxxx"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-200 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Alamat</label>
                    <input type="text" name="address" value="{{ old('address', $user->address) }}"
                        placeholder="Alamat tempat tinggal"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-200 outline-none">
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit"
                        class="flex-1 bg-orange hover:bg-orange-hover text-white py-2.5 rounded-lg text-sm font-semibold transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Ganti Password --}}
    <div class="bg-white rounded-2xl overflow-hidden shadow-sm">
        <div class="bg-gray-700 px-5 py-3">
            <h2 class="font-manrope font-bold text-white text-sm">Ganti Password</h2>
        </div>
        <div class="p-5">
            <form action="{{ route('profile.password') }}" method="POST" class="space-y-3">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Password Lama</label>
                    <input type="password" name="current_password"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-200 outline-none @error('current_password') border-red-400 @enderror">
                    @error('current_password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Password Baru</label>
                    <input type="password" name="password"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-200 outline-none @error('password') border-red-400 @enderror">
                    @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-200 outline-none">
                </div>

                <button type="submit"
                    class="w-full bg-red-500 hover:bg-red-600 text-white py-2.5 rounded-lg text-sm font-semibold transition">
                    Ganti Password
                </button>
            </form>
        </div>
    </div>

</div>
@endsection