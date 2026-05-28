<?php
// app/Http/Controllers/ClaimController.php
namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\Item;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClaimController extends Controller
{
    // Form klaim barang
    public function create(Item $item)
    {
        if (!$item->isClaimable()) {
            return redirect()->route('items.show', $item)
                ->with('error', 'Barang ini tidak dapat diklaim saat ini.');
        }

        // Cek apakah sudah pernah klaim barang ini
        $existing = Claim::where('item_id', $item->id)
            ->where('claimant_id', Auth::id())
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existing) {
            return redirect()->route('items.show', $item)
                ->with('error', 'Anda sudah memiliki klaim aktif untuk barang ini.');
        }

        return view('claims.create', compact('item'));
    }

    // Simpan klaim
    public function store(Request $request, Item $item)
    {
        if (!$item->isClaimable()) {
            return back()->with('error', 'Barang tidak dapat diklaim.');
        }

        $validated = $request->validate([
            'item_name'              => 'required|string|max:255',
            'proof_of_ownership'     => 'required|string|min:20',
            'special_characteristics'=> 'required|string|min:10',
            'phone'                  => 'required|string|max:20',
            'email'                  => 'required|email',
        ], [
            'proof_of_ownership.min'     => 'Bukti kepemilikan minimal 20 karakter.',
            'special_characteristics.min'=> 'Ciri-ciri khusus minimal 10 karakter.',
        ]);

        $validated['item_id']     = $item->id;
        $validated['claimant_id'] = Auth::id();
        $validated['status']      = 'pending';

        $claim = Claim::create($validated);

        // Update status barang menjadi in_claim
        $item->update(['status' => 'in_claim']);

        // Notifikasi ke admin (semua admin)
        $admins = \App\Models\User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            Notification::create_for(
                $admin,
                'new_claim',
                'Klaim Baru Masuk',
                "Ada klaim baru untuk barang \"{$item->name}\" dari " . Auth::user()->name . ".",
                $claim
            );
        }

        // PHP State: simpan di session untuk konfirmasi
        session(['last_claim_id' => $claim->id]);

        return redirect()->route('claims.my-claims')
            ->with('success', 'Klaim berhasil diajukan! Tunggu verifikasi dari petugas.');
    }

    // Klaim milik user login
    public function myClaims()
    {
        $claims = Claim::with(['item', 'item.reporter'])
            ->where('claimant_id', Auth::id())
            ->latest()
            ->paginate(10);

        $stats = [
            'my_claims'  => Claim::where('claimant_id', Auth::id())->count(),
            'pending'    => Claim::where('claimant_id', Auth::id())->where('status', 'pending')->count(),
            'total_items'=> Item::where('status', '!=', 'pending')->count(),
        ];

        return view('claims.my-claims', compact('claims', 'stats'));
    }

    // ============================================================
    // FITUR UNGGULAN: Real-time Notification via Ajax Polling
    // ============================================================

    /**
     * Endpoint Ajax: cek notifikasi baru untuk user yang login.
     * Di-poll setiap 15 detik oleh frontend JavaScript.
     * Mengembalikan jumlah notif belum baca + list terbaru.
     */
    public function pollNotifications()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user) return response()->json(['count' => 0, 'notifications' => []]);

        $notifications = $user->appNotifications()
            ->where('is_read', false)
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($n) => [
                'id'         => $n->id,
                'title'      => $n->title,
                'message'    => $n->message,
                'type'       => $n->type,
                'created_at' => $n->created_at->diffForHumans(),
                'icon'       => $this->notifIcon($n->type),
                'color'      => $this->notifColor($n->type),
            ]);

        return response()->json([
            'count'         => $notifications->count(),
            'notifications' => $notifications,
        ]);
    }

    /**
     * Endpoint Ajax: tandai semua notifikasi sebagai sudah dibaca.
     */
    public function markNotificationsRead()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->appNotifications()->update(['is_read' => true]);
        return response()->json(['success' => true]);
    }

    /**
     * Endpoint Ajax: tandai 1 notifikasi sebagai dibaca.
     */
    public function markOneRead(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) abort(403);
        $notification->update(['is_read' => true]);
        return response()->json(['success' => true]);
    }

    // Admin: lihat semua klaim
    public function adminIndex()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->isAdmin()) abort(403);

        $claims = Claim::with(['item', 'claimant'])
            ->latest()
            ->paginate(15);

        return view('claims.admin-index', compact('claims'));
    }

    // Admin: approve atau reject klaim
    public function adminDecide(Request $request, Claim $claim)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->isAdmin()) abort(403);

        $request->validate([
            'decision'   => 'required|in:approved,rejected',
            'admin_note' => 'nullable|string|max:500',
        ]);

        $claim->update([
            'status'     => $request->decision,
            'admin_note' => $request->admin_note,
        ]);

        // Update status barang berdasarkan keputusan
        if ($request->decision === 'approved') {
            $claim->item->update(['status' => 'waiting_pickup']);

            // FITUR UNGGULAN: Buat notifikasi untuk user pelapor klaim
            Notification::create_for(
                $claim->claimant,
                'claim_approved',
                '✅ Klaim Disetujui!',
                "Klaim Anda untuk barang \"{$claim->item->name}\" telah DISETUJUI. Segera ambil barang di admin FILKOM.",
                $claim
            );
        } else {
            $claim->item->update(['status' => 'available']); // Kembalikan ke available

            // Notifikasi penolakan
            Notification::create_for(
                $claim->claimant,
                'claim_rejected',
                '❌ Klaim Ditolak',
                "Klaim Anda untuk barang \"{$claim->item->name}\" ditolak. Alasan: " . ($request->admin_note ?? 'Bukti kepemilikan tidak mencukupi.'),
                $claim
            );
        }

        // Jika Ajax
        if ($request->ajax()) {
            return response()->json([
                'success'    => true,
                'decision'   => $request->decision,
                'claim_id'   => $claim->id,
            ]);
        }

        $msg = $request->decision === 'approved' ? 'Klaim disetujui.' : 'Klaim ditolak.';
        return back()->with('success', $msg);
    }

    // Helpers
    private function notifIcon(string $type): string
    {
        return match($type) {
            'claim_approved'  => 'check-circle',
            'claim_rejected'  => 'x-circle',
            'claim_picked_up' => 'check-circle',
            'new_claim'       => 'file-text',
            'item_published'  => 'package',
            default           => 'bell',
        };
    }

    private function notifColor(string $type): string
    {
        return match($type) {
            'claim_approved'  => 'success',
            'claim_rejected'  => 'danger',
            'claim_picked_up' => 'success',
            'new_claim'       => 'warning',
            'item_published'  => 'info',
            default           => 'gray',
        };
    }
}