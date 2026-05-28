{{-- resources/views/claims/my-claims.blade.php --}}
@extends('layouts.app')

@section('title', 'Klaim Saya')

@section('content')
{{-- Stats --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="stat-card flex items-center justify-between">
        <div>
            <p class="text-xs text-gray-500">Klaim Saya</p>
            <p class="text-3xl font-manrope font-bold text-gray-800">{{ $stats['my_claims'] }}</p>
        </div>
        <div class="p-2 bg-green-100 rounded-xl">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
    </div>
    <div class="stat-card flex items-center justify-between">
        <div>
            <p class="text-xs text-gray-500">Menunggu</p>
            <p class="text-3xl font-manrope font-bold text-gray-800">{{ $stats['pending'] }}</p>
        </div>
        <div class="p-2 bg-yellow-100 rounded-xl">
            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
    </div>
    <div class="stat-card flex items-center justify-between">
        <div>
            <p class="text-xs text-gray-500">Total Barang</p>
            <p class="text-3xl font-manrope font-bold text-gray-800">{{ $stats['total_items'] }}</p>
        </div>
        <div class="p-2 bg-blue-100 rounded-xl">
            <svg class="w-5 h-5 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
            </svg>
        </div>
    </div>
</div>

{{-- Header + CTA --}}
<div class="bg-navy rounded-t-2xl px-5 py-4 flex items-center justify-between">
    <h2 class="font-manrope text-white font-semibold">Klaim Saya</h2>
    <a href="{{ route('items.index') }}"
        class="btn btn-sm btn-primary">
        Klaim Barang
    </a>
</div>

{{-- Claims list --}}
@if($claims->isEmpty())
    <div class="bg-white rounded-b-2xl p-12 text-center shadow-sm">
        <svg class="w-14 h-14 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-gray-400 font-manrope">Belum ada klaim</p>
        <a href="{{ route('items.index') }}" class="mt-3 inline-block text-orange-500 text-sm hover:underline">Cari barang →</a>
    </div>
@else
    <div class="space-y-3">
        @foreach($claims as $claim)
            @php $badge = $claim->statusBadge(); @endphp
            <div class="bg-white rounded-xl overflow-hidden shadow-sm border border-gray-50 flex">
                <img src="{{ $claim->item->photoUrl() }}" alt="{{ $claim->item->name }}"
                    class="w-32 h-32 thumbnail">
                <div class="flex-1 p-4 flex flex-col justify-between">
                    <div>
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="font-manrope font-bold text-gray-800 text-sm">{{ $claim->item->name }}</h3>
                            <span class="badge {{ $badge['class'] }} flex-shrink-0">{{ $badge['label'] }}</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">{{ $claim->item->location_found }}</p>
                        @if($claim->admin_note)
                            <p class="text-xs text-gray-600 mt-1 italic">Catatan: {{ $claim->admin_note }}</p>
                        @endif
                    </div>
                    <div class="flex items-center justify-between mt-2">
                        <span class="text-xs text-gray-400">{{ $claim->created_at->format('d M Y') }}</span>
                        @if($claim->item->status === 'waiting_pickup')
                            <span class="text-xs text-blue-600 font-medium bg-blue-50 px-3 py-1 rounded-full">
                                Segera ambil di admin FILKOM
                            </span>
                        @elseif($claim->item->status === 'claimed')
                            <span class="text-xs text-green-600 font-medium bg-green-50 px-3 py-1 rounded-full">
                                ✓ Barang sudah diambil
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-4">{{ $claims->links() }}</div>
@endif
@endsection