<?php
// app/Http/Controllers/ItemController.php
namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    // Daftar semua barang temuan (publik dengan filter/search)
    public function index(Request $request)
    {
        $query = Item::with('reporter')->where('status', '!=', 'pending');

        // Search (Ajax-friendly)
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('description', 'like', "%$search%")
                    ->orWhere('category', 'like', "%$search%")
                    ->orWhere('location_found', 'like', "%$search%");
            });
        }

        // Filter kategori
        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        // Filter lokasi
        if ($location = $request->get('location')) {
            $query->where('location_found', 'like', "%$location%");
        }

        // Filter tanggal
        if ($date_from = $request->get('date_from')) {
            $query->where('found_date', '>=', $date_from);
        }
        if ($date_to = $request->get('date_to')) {
            $query->where('found_date', '<=', $date_to);
        }

        $items = $query->latest()->paginate(9)->appends($request->query());

        // Jika Ajax request (untuk live search)
        if ($request->ajax()) {
            return response()->json([
                'html'  => view('items._list', compact('items'))->render(),
                'total' => $items->total(),
            ]);
        }

        return view('items.index', compact('items'));
    }

    // Detail satu barang
    public function show(Item $item)
    {
        /** @var User */
        $user = Auth::user();
        if ($item->status === 'pending' && (!$user || !$user->isAdmin())) {
            abort(404);
        }
        $item->load('reporter', 'claims.claimant');
        return view('items.show', compact('item'));
    }

    // Form lapor barang temuan
    public function create()
    {
        return view('items.create');
    }

    // Simpan laporan barang
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'required|string|min:10',
            'category'      => 'required|in:Elektronik,Aksesori,Dokumen,Pakaian,Lainnya',
            'location_found' => 'required|string|max:255',
            'found_date'    => 'required|date|before_or_equal:today',
            'contact_info'  => 'required|string|max:255',
            'photo'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'name.required'         => 'Nama barang wajib diisi.',
            'description.min'       => 'Deskripsi minimal 10 karakter.',
            'found_date.before_or_equal' => 'Tanggal tidak boleh lebih dari hari ini.',
            'photo.image'           => 'File harus berupa gambar.',
            'photo.max'             => 'Ukuran foto maksimal 2MB.',
        ]);

        // File Handling: upload foto barang
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('items', 'public');
        }

        $validated['reported_by'] = Auth::id();
        $validated['status'] = 'pending'; // Menunggu verifikasi admin

        $item = Item::create($validated);

        // PHP State: flash message
        return redirect()->route('my-reports')
            ->with('success', "Laporan berhasil dibuat! Kode laporan Anda: {$item->report_code}. Menunggu verifikasi admin.")
            ->with('report_code', $item->report_code);
    }

    // Form edit (hanya milik sendiri atau admin)
    public function edit(Item $item)
    {
        $this->authorize_item($item);
        return view('items.edit', compact('item'));
    }

    // Update barang
    public function update(Request $request, Item $item)
    {
        $this->authorize_item($item);

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'required|string|min:10',
            'category'      => 'required|in:Elektronik,Aksesori,Dokumen,Pakaian,Lainnya',
            'location_found' => 'required|string|max:255',
            'found_date'    => 'required|date|before_or_equal:today',
            'contact_info'  => 'required|string|max:255',
            'photo'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // File Handling: ganti foto jika ada upload baru
        if ($request->hasFile('photo')) {
            if ($item->photo) Storage::disk('public')->delete($item->photo);
            $validated['photo'] = $request->file('photo')->store('items', 'public');
        }

        $item->update($validated);

        return redirect()->route('my-reports')->with('success', 'Laporan berhasil diperbarui.');
    }

    // Hapus barang (hanya admin)
    public function destroy(Item $item)
    {
        /** @var User */
        $user = Auth::user();
        if (!$user->isAdmin()) abort(403, 'Hanya admin yang dapat menghapus laporan.');

        if ($item->photo) Storage::disk('public')->delete($item->photo);
        $item->delete();

        return redirect()->route('my-reports')->with('success', 'Laporan berhasil dihapus permanen.');
    }

    // Laporan milik user login
    public function myReports()
    {
        /** @var User */
        $user = Auth::user();

        if ($user->isAdmin()) {
            // Admin melihat semua
            $items = Item::with('reporter')->latest()->paginate(10);
            $stats = [
                'published'       => Item::where('status', '!=', 'pending')->count(),
                'pending'         => Item::where('status', 'pending')->count(),
                'total'           => Item::count(),
            ];
        } else {
            $items = Item::where('reported_by', $user->id)
                ->whereNull('deleted_by_user_at')
                ->latest()->paginate(10);
            $stats = [
                'published'       => Item::where('reported_by', $user->id)->whereNull('deleted_by_user_at')->where('status', '!=', 'pending')->count(),
                'pending'         => Item::where('reported_by', $user->id)->whereNull('deleted_by_user_at')->where('status', 'pending')->count(),
                'total'           => Item::where('reported_by', $user->id)->whereNull('deleted_by_user_at')->count(),
            ];
        }

        return view('items.my-reports', compact('items', 'stats'));
    }

    // Admin: verifikasi / ubah status barang
    public function updateStatus(Request $request, Item $item)
    {
        /** @var User */
        $user = Auth::user();
        if (!$user->isAdmin()) abort(403);

        $request->validate(['status' => 'required|in:pending,available,in_claim,waiting_pickup,claimed']);

        $oldStatus = $item->status;
        $item->update(['status' => $request->status]);

        // Notifikasi ke pelapor jika status berubah ke available
        if ($request->status === 'available' && $oldStatus === 'pending') {
            Notification::create_for(
                $item->reporter,
                'item_published',
                'Laporan Disetujui!',
                "Laporan barang \"{$item->name}\" (kode: {$item->report_code}) telah diverifikasi dan dipublikasikan.",
                $item
            );
        }

        return response()->json(['success' => true, 'new_status' => $item->status]);
    }

    // Helper: admin selalu bisa; mahasiswa hanya bisa jika status masih pending
    private function authorize_item(Item $item): void
    {
        /** @var User */
        $user = Auth::user();
        if ($user->isAdmin()) return;

        if ($item->reported_by !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke laporan ini.');
        }

        if ($item->status !== 'pending') {
            abort(403, 'Laporan sudah diverifikasi admin dan tidak dapat diedit lagi.');
        }
    }
}
