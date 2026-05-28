{{-- resources/views/home/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
{{-- Hero banner --}}
<div class="rounded-xl overflow-hidden mb-6 hero-gradient">
    <div class="px-6 py-8 text-center">
        <h1 class="font-manrope text-2xl font-semibold text-white mb-2">SELAMAT DATANG</h1>
        <p class="text-white/90 text-sm leading-relaxed max-w-lg mx-auto">
            Lost &amp; Found merupakan suatu perangkat lunak yang dirancang untuk memfasilitasi
            dan mengelola proses pelaporan barang hilang serta penemuan barang di lingkungan
            Fakultas Ilmu Komputer Universitas Brawijaya.
        </p>
    </div>
</div>

{{-- Stats cards --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="stat-card p-6 flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500">Barang Tersedia</p>
            <p class="text-4xl font-manrope font-bold text-gray-800">{{ $stats['available_items'] }}</p>
        </div>
        <div class="p-3 bg-green-100 rounded-xl">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
    </div>

    <div class="stat-card p-6 flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500">Klaim Saya</p>
            <p class="text-4xl font-manrope font-bold text-gray-800">{{ $stats['my_claims'] }}</p>
        </div>
        <div class="p-3 bg-blue-100 rounded-xl">
            <svg class="w-6 h-6 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
        </div>
    </div>

    <div class="stat-card p-6 flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500">Total Barang Temuan</p>
            <p class="text-4xl font-manrope font-bold text-gray-800">{{ $stats['total_items'] }}</p>
        </div>
        <div class="p-3 bg-yellow-100 rounded-xl">
            <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10" />
            </svg>
        </div>
    </div>
</div>

{{-- Tips + Panduan --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    {{-- Tips Pencarian --}}
    <div class="card">
        <div class="bg-yellow-50 px-4 py-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="font-manrope font-bold text-orange-500 text-sm">Tips Pencarian Barang:</p>
        </div>
        <div class="px-4 py-3 space-y-2">
            <div class="flex gap-2 items-start">
                <span class="w-1.5 h-1.5 rounded-full bg-gray-700 mt-2 flex-shrink-0"></span>
                <p class="text-xs text-gray-700">Gunakan kata kunci spesifik (merek, warna, model, dll)</p>
            </div>
            <div class="flex gap-2 items-start">
                <span class="w-1.5 h-1.5 rounded-full bg-gray-700 mt-2 flex-shrink-0"></span>
                <p class="text-xs text-gray-700">Cek kategori yang sesuai dengan barang anda</p>
            </div>
            <div class="flex gap-2 items-start">
                <span class="w-1.5 h-1.5 rounded-full bg-gray-700 mt-2 flex-shrink-0"></span>
                <p class="text-xs text-gray-700">Perhatikan lokasi dan tanggal ditemukan</p>
            </div>
        </div>
    </div>

    {{-- Panduan Klaim --}}
    <div class="card">
        <div class="bg-blue-50 px-4 py-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-navy" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <p class="font-manrope font-bold text-navy text-sm">Panduan Mengklaim Barang</p>
        </div>
        <div class="px-4 py-3 space-y-2">
            <div class="flex gap-2 items-start">
                <span class="w-1.5 h-1.5 rounded-full bg-gray-700 mt-2 flex-shrink-0"></span>
                <p class="text-xs text-gray-700">Gunakan fitur <span class="text-navy">pencarian</span> untuk menemukan barang Anda</p>
            </div>
            <div class="flex gap-2 items-start">
                <span class="w-1.5 h-1.5 rounded-full bg-gray-700 mt-2 flex-shrink-0"></span>
                <p class="text-xs text-gray-700">Klik tombol <span class="text-navy">"Klaim Barang"</span> jika menemukan barang Anda</p>
            </div>
            <div class="flex gap-2 items-start">
                <span class="w-1.5 h-1.5 rounded-full bg-gray-700 mt-2 flex-shrink-0"></span>
                <p class="text-xs text-gray-700">Isi form dengan <span class="text-navy">bukti kepemilikan</span> dan ciri-ciri khusus</p>
            </div>
            <div class="flex gap-2 items-start">
                <span class="w-1.5 h-1.5 rounded-full bg-gray-700 mt-2 flex-shrink-0"></span>
                <p class="text-xs text-gray-700">Tunggu <span class="text-navy">verifikasi dari petugas</span>, jika disetujui Anda bisa mengambil barang</p>
            </div>
        </div>
    </div>
</div>

{{-- CTA Buttons --}}
<div class="flex flex-col sm:flex-row gap-3 justify-center">
    <a href="{{ route('items.index') }}"
        class="btn btn-navy btn-lg text-center font-manrope">
        Lihat Barang Temuan
    </a>
    @auth
    @unless(Auth::user()->isAdmin())
    <a href="{{ route('items.create') }}"
        class="btn btn-primary btn-lg text-center font-manrope">
        Laporkan Barang Temuan
    </a>
    @endunless
    @else
    <a href="{{ route('login') }}"
        class="btn btn-primary btn-lg text-center font-manrope">
        Masuk untuk Melapor
    </a>
    @endauth
</div>
@endsection