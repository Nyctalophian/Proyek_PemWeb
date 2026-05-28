{{-- resources/views/auth/login.blade.php --}}
@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="max-w-md mx-auto">
    <div class="card">
        <div class="card-header">
            <h2 class="font-manrope font-bold text-white text-xl">Masuk ke Lost & Found</h2>
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

            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        placeholder="nama@ub.ac.id"
                        class="form-input @error('email') error @enderror">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Password</label>
                    <input type="password" name="password"
                        placeholder="Minimal 6 karakter"
                        class="form-input @error('password') error @enderror">
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-xs text-gray-600 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-gray-300 text-orange-500">
                        Ingat saya
                    </label>
                </div>

                <button type="submit" class="btn-primary w-full">
                    Masuk
                </button>

                <p class="text-center text-xs text-gray-500">
                    Belum punya akun?
                    <a href="{{ route('register') }}" class="link-primary">Daftar di sini</a>
                </p>
            </form>
        </div>
    </div>
</div>
@endsection