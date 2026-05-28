{{-- resources/views/items/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Barang Temuan')

@section('content')
{{-- Hero section with search --}}
<div class="rounded-[40px] overflow-visible mb-0 relative hero-gradient">
    <div class="px-6 pt-8 pb-32 text-center">
        <h2 class="font-manrope text-2xl font-bold text-white mb-4">Barang Temuan</h2>

        {{-- Search bar --}}
        <div class="max-w-lg mx-auto">
            <div class="relative flex items-center bg-white rounded-full px-4 py-2.5 shadow-lg">
                <svg class="w-5 h-5 text-gray-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" id="search-input" placeholder="Cari Barang (nama, kategori, deskripsi)....."
                    class="flex-1 outline-none text-sm text-gray-700 bg-transparent"
                    value="{{ request('search') }}">
                <span id="search-loading" class="hidden">
                    <svg class="animate-spin w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </span>
            </div>
        </div>
    </div>
</div>

{{-- Filter panel (overlapping) --}}
<div class="bg-white rounded-2xl shadow-lg p-4 mx-4 -mt-24 relative z-10 mb-6">
    <form id="filter-form" class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Kategori</label>
            <select name="category" id="filter-category"
                class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-200 focus:border-orange-400 outline-none">
                <option value="">Semua Kategori</option>
                @foreach(['Elektronik','Aksesori','Dokumen','Pakaian','Lainnya'] as $cat)
                    <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Lokasi</label>
            <div class="relative">
                <input type="text" name="location" id="filter-location" placeholder="Cari lokasi..."
                    value="{{ request('location') }}"
                    class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2 pr-8 focus:ring-2 focus:ring-orange-200 focus:border-orange-400 outline-none">
                <svg class="absolute right-2 top-2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                </svg>
            </div>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Dari Tanggal</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-200 outline-none">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Sampai Tanggal</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-200 outline-none">
        </div>
    </form>
</div>

{{-- Results count --}}
<p class="text-sm text-gray-500 mb-4" id="results-count">
    Menampilkan {{ $items->total() }} barang
</p>

{{-- Items grid --}}
<div id="items-grid">
    @include('items._list', ['items' => $items])
</div>

{{-- Pagination --}}
<div id="pagination" class="mt-6">
    {{ $items->links() }}
</div>

@endsection

@push('scripts')
<script>
// ── Live Search & Filter via Ajax ────────────────────────────────────────────
let searchTimer = null;

function getFilters() {
    return {
        search:    document.getElementById('search-input').value,
        category:  document.getElementById('filter-category').value,
        location:  document.getElementById('filter-location').value,
        date_from: document.querySelector('[name="date_from"]').value,
        date_to:   document.querySelector('[name="date_to"]').value,
    };
}

async function fetchItems(params = {}) {
    document.getElementById('search-loading').classList.remove('hidden');

    const qs = new URLSearchParams(params).toString();

    try {
        const res  = await fetch(`{{ route('items.index') }}?${qs}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();

        document.getElementById('items-grid').innerHTML    = data.html;
        document.getElementById('results-count').textContent = `Menampilkan ${data.total} barang`;
    } catch(e) {
        console.error(e);
    } finally {
        document.getElementById('search-loading').classList.add('hidden');
    }
}

// Debounce search input
document.getElementById('search-input').addEventListener('input', () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => fetchItems(getFilters()), 400);
});

// Instant filter on change
['filter-category', 'filter-location'].forEach(id => {
    document.getElementById(id)?.addEventListener('change', () => fetchItems(getFilters()));
});
document.querySelectorAll('[name="date_from"], [name="date_to"]').forEach(el => {
    el.addEventListener('change', () => fetchItems(getFilters()));
});
</script>
@endpush