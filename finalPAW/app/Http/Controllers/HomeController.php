<?php
// app/Http/Controllers/HomeController.php
namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Claim;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Stats untuk dashboard beranda
        $stats = [
            'available_items' => Item::where('status', 'available')->count(),
            'my_claims'       => $user ? Claim::where('claimant_id', $user->id)->count() : 0,
            'total_items'     => Item::where('status', '!=', 'pending')->count(),
        ];

        // Barang terbaru yang available (preview di beranda)
        $recentItems = Item::where('status', 'available')
            ->latest()
            ->take(3)
            ->get();

        return view('home.index', compact('stats', 'recentItems'));
    }
}