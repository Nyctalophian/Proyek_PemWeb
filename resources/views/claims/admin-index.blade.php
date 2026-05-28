@extends('layouts.app')

@section('title', 'Admin - Kelola Klaim')

@section('content')
<div class="mb-4 flex items-center justify-between">
    <h2 class="font-manrope font-bold text-xl text-gray-800">Panel Admin — Kelola Klaim & Barang</h2>
    <a href="{{ route('my-reports') }}"
        class="btn btn-navy btn-sm">
        Daftar Laporan
    </a>
</div>

@if($claims->isEmpty())
<div class="bg-white rounded-2xl p-12 text-center shadow-sm">
    <p class="text-gray-400">Belum ada klaim masuk</p>
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
                {{-- Form keputusan admin --}}
                <div class="mt-3 p-3 bg-gray-50 rounded-xl" id="form-{{ $claim->id }}">
                    <textarea id="note-{{ $claim->id }}" rows="2" placeholder="Catatan untuk pelapor (opsional)..."
                        class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2 resize-none outline-none focus:ring-2 focus:ring-blue-200 mb-2"></textarea>
                    <div class="flex gap-2">
                        <button onclick="decide('{{ $claim->id }}', 'approved')"
                            class="btn btn-sm btn-success flex-1">
                            ✅ Setujui Klaim
                        </button>
                        <button onclick="decide('{{ $claim->id }}', 'rejected')"
                            class="btn btn-sm btn-danger flex-1">
                            ❌ Tolak Klaim
                        </button>
                    </div>
                </div>
                @elseif($claim->status === 'approved')
                <div class="mt-2 text-xs text-green-600 font-medium">✅ Klaim disetujui — menunggu pengambilan</div>
                @else
                <div class="mt-2 text-xs text-red-500 font-medium">❌ Klaim ditolak{{ $claim->admin_note ? ': ' . $claim->admin_note : '' }}</div>
                @endif
            </div>
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
                body: JSON.stringify({
                    decision,
                    admin_note: note
                })
            });

            const data = await res.json();

            if (data.success) {
                showToast('success', decision === 'approved' ? 'Klaim berhasil disetujui! Notifikasi terkirim ke user.' : 'Klaim berhasil ditolak.');
                setTimeout(() => location.reload(), 1500);
            }
        } catch (e) {
            showToast('danger', 'Terjadi kesalahan.');
        }
    }
</script>
@endpush