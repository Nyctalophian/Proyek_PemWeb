{{-- resources/views/items/my-reports.blade.php --}}
@extends('layouts.app')

@section('title', Auth::user()->isAdmin() ? 'Daftar Laporan' : 'Laporan Saya')

@section('content')
{{-- Stats --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl p-4 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs text-gray-500">Dipublikasikan</p>
            <p class="text-3xl font-manrope font-bold text-gray-800">{{ $stats['published'] }}</p>
        </div>
        <div class="p-2 bg-green-100 rounded-xl">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
    </div>
    <div class="bg-white rounded-2xl p-4 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs text-gray-500">Menunggu Verifikasi</p>
            <p class="text-3xl font-manrope font-bold text-gray-800">{{ $stats['pending'] }}</p>
        </div>
        <div class="p-2 bg-yellow-100 rounded-xl">
            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
    </div>
    <div class="bg-white rounded-2xl p-4 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs text-gray-500">Total Barang</p>
            <p class="text-3xl font-manrope font-bold text-gray-800">{{ $stats['total'] }}</p>
        </div>
        <div class="p-2 bg-blue-100 rounded-xl">
            <svg class="w-5 h-5 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10" />
            </svg>
        </div>
    </div>
</div>

{{-- Header --}}
<div class="bg-navy rounded-t-2xl px-5 py-4 flex items-center justify-between">
    <h2 class="font-manrope text-white font-semibold">
        {{ Auth::user()->isAdmin() ? 'Daftar Laporan' : 'Laporan Saya' }}
    </h2>
    @unless(Auth::user()->isAdmin())
    <a href="{{ route('items.create') }}"
        class="bg-orange hover:bg-orange-hover text-white text-xs px-4 py-2 rounded-lg transition font-medium">
        Laporkan Barang
    </a>
    @endunless
</div>

{{-- Items list --}}
@if($items->isEmpty())
<div class="bg-white rounded-b-2xl p-12 text-center shadow-sm">
    <svg class="w-14 h-14 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
    </svg>
    <p class="text-gray-400 font-manrope">Belum ada laporan</p>
    @unless(Auth::user()->isAdmin())
    <a href="{{ route('items.create') }}" class="mt-3 inline-block text-orange-500 text-sm hover:underline">Buat laporan pertama →</a>
    @endunless
</div>
@else
<div class="bg-white rounded-b-2xl shadow-sm divide-y divide-gray-50">
    @foreach($items as $item)
    @php $badge = $item->statusBadge(); @endphp
    <div class="flex gap-4 p-4 hover:bg-gray-50 transition">
        <img src="{{ $item->photoUrl() }}" alt="{{ $item->name }}"
            class="w-20 h-20 object-cover rounded-xl flex-shrink-0">
        <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <h3 class="font-manrope font-bold text-gray-800 text-sm truncate">{{ $item->name }}</h3>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $item->location_found }}</p>
                    <p class="text-xs text-gray-400">{{ $item->found_date->format('d/m/Y') }}</p>
                    <p class="text-xs text-gray-400 font-mono mt-0.5">{{ $item->report_code }}</p>
                </div>
                <span class="badge {{ $badge['class'] }} flex-shrink-0 text-xs">{{ $badge['label'] }}</span>
            </div>

            {{-- Admin: status changer via Ajax --}}
            @if(Auth::user()->isAdmin())
            <div class="flex items-center gap-2 mt-2">
                <select class="status-select text-xs border border-gray-200 rounded-lg px-2 py-1 outline-none"
                    data-id="{{ $item->id }}">
                    @foreach(['pending','available','in_claim','waiting_pickup','claimed'] as $s)
                    <option value="{{ $s }}" {{ $item->status === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            @endif
        </div>

        {{-- Actions --}}
        <div class="flex flex-col gap-2 flex-shrink-0">
            <a href="{{ route('items.edit', $item) }}"
                class="text-xs bg-blue-50 text-blue-600 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition text-center">
                Edit
            </a>
            <form action="{{ route('items.destroy', $item) }}" method="POST"
                onsubmit="return confirm('Yakin hapus laporan ini?')">
                @csrf @method('DELETE')
                <button type="submit"
                    class="w-full text-xs bg-red-50 text-red-500 hover:bg-red-100 px-3 py-1.5 rounded-lg transition">
                    Hapus
                </button>
            </form>
        </div>
    </div>
    @endforeach
</div>

<div class="mt-4">{{ $items->links() }}</div>
@endif
@endsection

@push('scripts')
<script>
    // Admin: ubah status via Ajax
    document.querySelectorAll('.status-select').forEach(select => {
        select.addEventListener('change', async function() {
            const id = this.dataset.id;
            const status = this.value;
            try {
                const res = await fetch(`/admin/barang/${id}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        status
                    })
                });
                const data = await res.json();
                if (data.success) showToast('success', 'Status berhasil diubah.');
                else showToast('danger', 'Gagal mengubah status.');
            } catch (e) {
                showToast('danger', 'Terjadi kesalahan.');
            }
        });
    });
</script>
@endpush