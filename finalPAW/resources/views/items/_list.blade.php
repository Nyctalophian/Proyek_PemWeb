{{-- resources/views/items/_list.blade.php --}}
@if($items->isEmpty())
    <div class="text-center py-16">
        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
        </svg>
        <p class="text-gray-400 font-manrope text-lg">Tidak ada barang ditemukan</p>
        <p class="text-gray-300 text-sm mt-1">Coba kata kunci atau filter yang berbeda</p>
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($items as $item)
            @php $badge = $item->statusBadge(); @endphp
            <div class="item-card bg-white rounded-xl overflow-hidden shadow-sm border border-gray-100 flex flex-col">
                {{-- Photo --}}
                <div class="relative h-40 bg-gray-100">
                    <img src="{{ $item->photoUrl() }}" alt="{{ $item->name }}"
                        class="w-full h-full object-cover">
                    {{-- Status badge --}}
                    <span class="badge {{ $badge['class'] }} absolute top-3 right-3 shadow-sm">
                        {{ $badge['label'] }}
                    </span>
                </div>

                {{-- Content --}}
                <div class="p-4 flex flex-col flex-1">
                    <h3 class="font-manrope font-bold text-lg text-gray-800 mb-1 truncate">{{ $item->name }}</h3>
                    <p class="text-xs text-gray-500 mb-3 line-clamp-2 leading-relaxed">{{ $item->description }}</p>

                    <div class="space-y-1.5 mb-4">
                        <div class="flex items-center gap-2 text-xs text-gray-600">
                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            </svg>
                            <span class="truncate">{{ $item->location_found }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-gray-600">
                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Ditemukan pada {{ $item->found_date->format('d/m/Y') }}
                        </div>
                    </div>

                    <div class="mt-auto flex items-center justify-between">
                        <span class="badge badge-gray text-xs">{{ $item->category }}</span>

                        @if($item->isClaimable())
                            @auth
                                <a href="{{ route('claims.create', $item) }}"
                                    class="btn btn-primary btn-sm">
                                    Klaim
                                </a>
                            @else
                                <a href="{{ route('login') }}"
                                    class="btn btn-primary btn-sm">
                                    Klaim
                                </a>
                            @endauth
                        @else
                            <button disabled class="btn btn-sm bg-gray-200 text-gray-400 cursor-not-allowed">
                                Klaim
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Pagination (only in non-Ajax) --}}
    @if(!request()->ajax())
        <div class="mt-6">{{ $items->links() }}</div>
    @endif
@endif