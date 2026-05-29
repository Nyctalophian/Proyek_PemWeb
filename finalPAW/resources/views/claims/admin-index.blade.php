@extends('layouts.app')

@section('title', 'Admin - Kelola Klaim')

@section('content')
<div class="mb-6 space-y-4">
    {{-- Stats --}}
    <div class="grid grid-cols-4 gap-3">
        <div class="bg-white rounded-xl p-4 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500">Total Klaim</p>
                <p class="text-2xl font-manrope font-bold text-gray-800">{{ $stats['total'] }}</p>
            </div>
            <div class="p-2 bg-blue-100 rounded-lg"><svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500">Menunggu</p>
                <p class="text-2xl font-manrope font-bold text-gray-800">{{ $stats['pending'] }}</p>
            </div>
            <div class="p-2 bg-yellow-100 rounded-lg"><svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500">Disetujui</p>
                <p class="text-2xl font-manrope font-bold text-gray-800">{{ $stats['approved'] }}</p>
            </div>
            <div class="p-2 bg-green-100 rounded-lg"><svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500">Ditolak</p>
                <p class="text-2xl font-manrope font-bold text-gray-800">{{ $stats['rejected'] }}</p>
            </div>
            <div class="p-2 bg-red-100 rounded-lg"><svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></div>
        </div>
    </div>

    {{-- Filter & Search --}}
    <form method="GET" action="{{ route('admin.claims.index') }}" class="flex gap-3">
        <select name="status" onchange="this.form.submit()"
            class="text-xs border border-gray-200 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-orange-200">
            <option value="">Semua Status</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu</option>
            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
        </select>
        <div class="flex-1 relative">
            <input type="text" name="search" placeholder="Cari barang, pemohon, atau NIM..."
                value="{{ request('search') }}"
                class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2 pr-8 outline-none focus:ring-2 focus:ring-orange-200">
            <button type="submit" class="absolute right-2 top-2 text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </button>
        </div>
        @if(request('search') || request('status'))
        <a href="{{ route('admin.claims.index') }}" class="text-xs text-gray-500 hover:text-gray-700 flex items-center px-2">Reset</a>
        @endif
    </form>
</div>

@if($claims->isEmpty())
<div class="bg-white rounded-2xl p-12 text-center shadow-sm">
    <p class="text-gray-400">Tidak ada klaim ditemukan</p>
    @if(request('search') || request('status'))
    <a href="{{ route('admin.claims.index') }}" class="mt-2 inline-block text-sm text-orange-500 hover:underline">Reset filter</a>
    @endif
</div>
@else
<div class="space-y-3">
    @foreach($claims as $claim)
    @php $badge = $claim->statusBadge(); @endphp
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex gap-4">
            <img src="{{ $claim->item->photoUrl() }}" class="w-20 h-20 thumbnail">
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <h3 class="font-manrope font-bold text-gray-800">{{ $claim->item->name }}</h3>
                        <p class="text-xs text-gray-500">Diklaim oleh: <span class="font-semibold">{{ $claim->claimant->name }}</span> ({{ $claim->claimant->nim }})</p>
                        <p class="text-xs text-gray-400">{{ $claim->created_at->format('d M Y H:i') }}</p>
                    </div>
                    <span class="badge {{ $badge['class'] }} flex-shrink-0">{{ $badge['label'] }}</span>
                </div>

                <div class="mt-2 space-y-1">
                    <p class="text-xs"><span class="font-semibold text-gray-700">Bukti kepemilikan:</span> <span class="text-gray-600">{{ $claim->proof_of_ownership }}</span></p>
                    <p class="text-xs"><span class="font-semibold text-gray-700">Ciri-ciri khusus:</span> <span class="text-gray-600">{{ $claim->special_characteristics }}</span></p>
                    <p class="text-xs"><span class="font-semibold text-gray-700">Kontak:</span> {{ $claim->phone }} / {{ $claim->email }}</p>
                </div>

                @if($claim->status === 'pending')
                <div class="mt-3 p-3 bg-gray-50 rounded-xl" id="form-{{ $claim->id }}">
                    <textarea id="note-{{ $claim->id }}" rows="2" placeholder="Catatan untuk pelapor (opsional)..."
                        class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2 resize-none outline-none focus:ring-2 focus:ring-blue-200 mb-2"></textarea>
                    <div class="flex gap-2">
                        <button onclick="decide('{{ $claim->id }}', 'approved')"
                            class="btn btn-sm btn-success flex-1">Setujui Klaim</button>
                        <button onclick="decide('{{ $claim->id }}', 'rejected')"
                            class="btn btn-sm btn-danger flex-1">Tolak Klaim</button>
                    </div>
                </div>
                @elseif($claim->status === 'approved')
                <div class="mt-2 text-xs text-green-600 font-medium">Klaim disetujui — menunggu pengambilan</div>
                @else
                <div class="mt-2 text-xs text-red-500 font-medium">Klaim ditolak{{ $claim->admin_note ? ': ' . $claim->admin_note : '' }}</div>
                @endif
            </div>

            <form action="{{ route('claims.destroy', $claim) }}" method="POST"
                onsubmit="return confirm('Yakin hapus klaim ini secara permanent? Data akan hilang untuk semua user.')"
                class="flex-shrink-0 self-start">
                @csrf @method('DELETE')
                <button type="submit" class="text-xs text-red-400 hover:text-red-600 transition p-1" title="Hapus permanent">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </button>
            </form>
        </div>
    </div>
    @endforeach
</div>

<div class="mt-4">{{ $claims->links() }}</div>
@endif
@endsection

@push('scripts')
<script>
    async function decide(claimId, decision) {
        const note = document.getElementById(`note-${claimId}`).value;
        const label = decision === 'approved' ? 'menyetujui' : 'menolak';

        if (!confirm(`Yakin ingin ${label} klaim ini?`)) return;

        try {
            const res = await fetch(`/admin/klaim/${claimId}/decide`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ decision, admin_note: note })
            });

            const data = await res.json();

            if (data.success) {
                if (decision === 'approved') {
                    showToast('success', 'Klaim berhasil disetujui! Notifikasi terkirim ke user.');
                } else {
                    showToast('danger', 'Klaim ditolak.');
                }
                setTimeout(() => location.reload(), 1500);
            }
        } catch (e) {
            showToast('danger', 'Terjadi kesalahan.');
        }
    }
</script>
@endpush
