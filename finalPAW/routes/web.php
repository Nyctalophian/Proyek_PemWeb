<?php
// routes/web.php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// ─── Auth ───────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ─── Public ─────────────────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::prefix('barang')->name('items.')->group(function () {
    Route::get('/',           [ItemController::class, 'index'])->name('index');
    Route::get('/{item}',     [ItemController::class, 'show'])->name('show');
});

// ─── Authenticated ──────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Laporan barang
    Route::get('/lapor',               [ItemController::class, 'create'])->name('items.create');
    Route::post('/lapor',              [ItemController::class, 'store'])->name('items.store');
    Route::get('/laporan-saya',        [ItemController::class, 'myReports'])->name('my-reports');
    Route::get('/barang/{item}/edit',  [ItemController::class, 'edit'])->name('items.edit');
    Route::put('/barang/{item}',       [ItemController::class, 'update'])->name('items.update');
    Route::delete('/barang/{item}',    [ItemController::class, 'destroy'])->name('items.destroy');

    // Klaim
    Route::get('/barang/{item}/klaim',  [ClaimController::class, 'create'])->name('claims.create');
    Route::post('/barang/{item}/klaim', [ClaimController::class, 'store'])->name('claims.store');
    Route::get('/klaim-saya',           [ClaimController::class, 'myClaims'])->name('claims.my-claims');
    Route::delete('/klaim/{claim}',     [ClaimController::class, 'destroy'])->name('claims.destroy');

    // Profil
    Route::get('/profil',              [ProfileController::class, 'show'])->name('profile');
    Route::put('/profil',              [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profil/password',     [ProfileController::class, 'changePassword'])->name('profile.password');

    // ─── FITUR UNGGULAN: Ajax Notification Endpoints ───────────────────────
    Route::get('/api/notifications/poll',          [ClaimController::class, 'pollNotifications'])->name('notifications.poll');
    Route::post('/api/notifications/mark-all-read',[ClaimController::class, 'markNotificationsRead'])->name('notifications.mark-read');
    Route::post('/api/notifications/{notification}/read', [ClaimController::class, 'markOneRead'])->name('notifications.mark-one');

    // ─── Admin routes ────────────────────────────────────────────────────────
    Route::middleware('can:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/klaim',                        [ClaimController::class, 'adminIndex'])->name('claims.index');
        Route::post('/klaim/{claim}/decide',        [ClaimController::class, 'adminDecide'])->name('claims.decide');
        Route::patch('/barang/{item}/status',       [ItemController::class, 'updateStatus'])->name('items.status');
    });
});