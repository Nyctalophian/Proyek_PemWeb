{{-- resources/views/items/show.blade.php --}}
@extends('layouts.app')

@section('title', $item->name)

@section('content')
@php $badge = $item->statusBadge(); @endphp

<div class="max-w-4xl mx-auto">
    <a href="{{ route('items.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-navy mb-4 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali
    </a>

    <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
        {{-- Photo --}}
        <div class="md:col-span-2">
            <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100">
                <div class="relative h-64 md:h-80 bg-gray-100">
                    <img src="{{ $item->photoUrl() }}" alt="{{ $item->name }}"
                        class="w-full h-full object-cover">
                    <span class="badge {{ $badge['class'] }} absolute top-4 right-4 shadow-sm">
                        {{ $badge['label'] }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Detail --}}
        <div class="md:col-span-3 space-y-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h1 class="font-manrope font-bold text-2xl text-gray-800 mb-2">{{ $item->name }}</h1>
                <span class="badge badge-gray text-xs">{{ $item->category }}</span>

                <hr class="my-4 border-gray-100">

                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                        </svg>
                        <div>
                            <p class="text-xs text-gray-400">Deskripsi</p>
                            <p class="text-sm text-gray-700 leading-relaxed">{{ $item->description }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        </svg>
                        <div>
                            <p class="text-xs text-gray-400">Lokasi Ditemukan</p>
                            <p class="text-sm text-gray-700">{{ $item->location_found }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <div>
                            <p class="text-xs text-gray-400">Tanggal Ditemukan</p>
                            <p class="text-sm text-gray-700">{{ $item->found_date->format('d F Y') }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <div>
                            <p class="text-xs text-gray-400">Informasi Kontak</p>
                            <p class="text-sm text-gray-700">{{ $item->contact_info }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <div>
                            <p class="text-xs text-gray-400">Dilaporkan oleh</p>
                            <p class="text-sm text-gray-700">{{ $item->reporter->name }} ({{ $item->reporter->nim }})</p>
                        </div>
                    </div>
                </div>

                @if($item->isClaimable())
                <div class="mt-6">
                    @auth
                    <a href="{{ route('claims.create', $item) }}"
                        class="btn-primary w-full inline-flex items-center justify-center gap-2 py-3">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Klaim Barang Ini
                    </a>
                    @else
                    <a href="{{ route('login') }}"
                        class="btn-primary w-full inline-flex items-center justify-center gap-2 py-3">
                        Masuk untuk Klaim
                    </a>
                    @endauth
                </div>
                @elseif($item->status !== 'pending')
                <div class="mt-6 warning-box">
                    <p class="text-sm text-gray-600">Barang ini sudah tidak tersedia untuk diklaim.</p>
                </div>
                @endif
            </div>

            {{-- Daftar Klaim (hanya untuk admin) --}}
            @auth
            @if(Auth::user()->isAdmin() && $item->claims->isNotEmpty())
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-manrope font-bold text-gray-800 mb-4">Riwayat Klaim</h3>
                <div class="space-y-3">
                    @foreach($item->claims as $claim)
                    @php $cBadge = $claim->statusBadge(); @endphp
                    <div class="flex items-start gap-3 p-3 rounded-xl bg-gray-50">
                        <div class="w-8 h-8 rounded-full bg-navy flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                            {{ strtoupper(substr($claim->claimant->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-800">{{ $claim->claimant->name }}</p>
                            <p class="text-xs text-gray-500">{{ $claim->claimant->nim ?? $claim->claimant->email }}</p>
                            <span class="badge {{ $cBadge['class'] }} mt-1 inline-block">{{ $cBadge['label'] }}</span>
                            @if($claim->admin_note)
                            <p class="text-xs text-gray-400 mt-1">Catatan admin: {{ $claim->admin_note }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            @endauth
        </div>
    </div>
</div>
@endsection