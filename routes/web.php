<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\Item_inController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\PermintaanController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\KopSuratController;
use App\Models\Visitor;
use App\Models\User;
use App\Http\Controllers\ManualBookController;
use Illuminate\Support\Facades\Log;

// Role Controllers
use App\Http\Controllers\Role\SuperAdminController;
use App\Http\Controllers\Role\PegawaiController;
use App\Http\Controllers\Role\admin\AdminController;
use App\Http\Controllers\Role\admin\ItemoutController;
use App\Http\Controllers\Role\admin\RequestController;
use App\Http\Controllers\Role\admin\GuestController;
use App\Http\Controllers\Role\admin\ProdukController;
use App\Http\Controllers\Role\admin\RejectController;
use App\Http\Controllers\Role\admin\TransaksiItemOutController;
use App\Http\Controllers\Role\admin\AdminPegawaiController;
use App\Http\Controllers\Role\Admin\ManualBookAdminController;
use App\Http\Controllers\SearchController;

/*
|--------------------------------------------------------------------------
| 🌟 Default Route (Welcome Page)
|--------------------------------------------------------------------------
| Saat pertama kali aplikasi diakses, user diarahkan ke "welcome.blade.php".
| Kalau sudah login, langsung ke dashboard sesuai role.
*/
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    try {
        $ip = request()->ip();
        $userAgent = request()->userAgent();
        $today = now()->toDateString();

        // Cek apakah IP ini sudah tercatat hari ini
        $exists = Visitor::where('ip_address', $ip)
            ->whereDate('created_at', $today)
            ->exists();

        if (!$exists) {
            Visitor::create([
                'ip_address' => $ip,
                'user_agent' => $userAgent,
            ]);
        }

    } catch (\Throwable $th) {
        // kalau error, log aja (tidak akan ganggu tampilan)
        Log::error('Gagal mencatat pengunjung: ' . $th->getMessage());
    }

    $totalPengunjung = Visitor::count();
    $pegawaiAktif = User::where('role', 'pegawai')
        ->where('status', 'active')
        ->count();

    return view('welcome', compact('totalPengunjung', 'pegawaiAktif'));
})->name('welcome');
/*
|--------------------------------------------------------------------------
| Banned User Management
|--------------------------------------------------------------------------
*/
Route::put('/users/{id}/ban', [UserController::class, 'ban'])->name('users.ban');
Route::put('/users/{id}/unban', [UserController::class, 'unban'])->name('users.unban');
Route::put('/users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');

/*
|--------------------------------------------------------------------------
| Profile (Authenticated Users)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Notifkasi (Authenticated Users)
|--------------------------------------------------------------------------
*/
Route::get('/notifications/count', function () {
    return response()->json([
        'pending' => \App\Models\Cart::where('status', 'pending')->count(),
        'approved' => \App\Models\Cart::whereIn('status', ['approved', 'approved_partially'])
            ->whereHas('user', fn($u) => $u->where('role', 'pegawai'))
            ->whereHas('cartItems', fn($q) => $q->whereNull('scanned_at'))
            ->whereDoesntHave('cartItems', fn($q) =>
                $q->whereNotNull('scanned_at')
            , '=', 0)
            ->count(),
    ]);
})->name('notifications.count');
/*
|--------------------------------------------------------------------------
| Super Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:super_admin'])
    ->prefix('super-admin')
    ->as('super_admin.')
    ->group(function () {
        Route::get('/dashboard', [SuperAdminController::class, 'index'])->name('dashboard');
        Route::get('/admin/dashboard/modal/{type}', [AdminController::class, 'loadModalData']);
        Route::get('/dashboard/modal/barang_keluar', [AdminController::class, 'barangKeluarModal'])
            ->name('dashboard.modal.barang_keluar');

        Route::get('/items/download-template', [ItemController::class, 'downloadTemplate'])
        ->name('items.template');
        Route::get('/suppliers/download-template', [SupplierController::class, 'downloadTemplate'])
        ->name('suppliers.template');

        // CRUD Master Data
        Route::resources([
            'categories' => CategoryController::class,
            'items'      => ItemController::class,
            'item_ins'   => Item_inController::class,
            'units'      => UnitController::class,
            'suppliers'  => SupplierController::class,
            'users'      => UserController::class,
        ]);

        // Import Barang & Supplier
        Route::post('/items/import', [ItemController::class, 'import'])->name('items.import');
        Route::post('/suppliers/import', [SupplierController::class, 'import'])->name('suppliers.import');

        // Barcode
        Route::get('items/{item}/barcode-pdf', [ItemController::class, 'printBarcode'])->name('items.barcode.pdf');

        // Export Barang Masuk / Keluar / Reject
        Route::get('/export', [ExportController::class, 'index'])->name('export.index');
        Route::get('/export/barang-masuk/excel', [ExportController::class, 'exportBarangMasukExcel'])->name('exports.barang_masuk.excel');
        Route::get('/export/barang-masuk/pdf', [ExportController::class, 'exportBarangMasukPdf'])->name('exports.barang_masuk.pdf');
        Route::get('/export/barang-keluar/excel', [ExportController::class, 'exportBarangKeluarExcel'])->name('exports.barang_keluar.excel');
        Route::get('/export/barang-keluar/pdf', [ExportController::class, 'exportBarangKeluarPdf'])->name('exports.barang_keluar.pdf');
        Route::get('/export/barang-reject-improved', [ExportController::class, 'exportBarangRejectExcelImproved'])->name('export.barangRejectImproved');
        Route::get('/export/barang-reject/pdf', [ExportController::class, 'exportBarangRejectPdf'])->name('exports.barang_reject.pdf');
        Route::get('/export/download', [ExportController::class, 'download'])->name('export.download');
        Route::delete('/export/clear', [ExportController::class, 'clearLogs'])->name('export.clear');

        // Kop Surat
        Route::resource('kop_surat', KopSuratController::class);

        //Manualbook
        Route::get('/manual-book', [ManualBookController::class, 'index'])
        ->name('manual_book.index');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {

    // Dashboard Admin
    Route::controller(AdminController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard');
        Route::get('/dashboard/data', 'getChartData');
        Route::get('/dashboard/modal/{type}', 'loadModalData')->name('dashboard.modal.data');
        Route::get('/dashboard/modal/barang_keluar', 'barangKeluarModal')->name('dashboard.modal.barang_keluar');
    });

    // Barang Keluar
    Route::controller(ItemoutController::class)->group(function () {
        Route::get('/itemout/search', 'search')->name('itemout.search');
        Route::resource('itemout', ItemoutController::class);
        Route::get('/itemout/{cart}/struk', 'struk')->name('itemout.struk');
        Route::post('/itemout/scan/{cart}', 'scan')->name('itemout.scan');
        Route::get('/itemout/check-all-scanned/{cart}', 'checkAllScanned')->name('itemout.checkAllScanned');
        Route::post('/itemout/release/{cart}', 'release')->name('itemout.release');
    });

    // Request & Cart
    Route::controller(RequestController::class)->group(function () {
        Route::get('/request', 'index')->name('request');
        Route::get('/request/search', 'search')->name('request.search');
        Route::get('/carts/{id}', 'show')->name('carts.show');
        Route::patch('/carts/{id}', 'update')->name('carts.update');
        Route::patch('/carts/item/{id}/approve', 'approveItem')->name('carts.item.approve');
        Route::patch('/carts/item/{id}/reject', 'rejectItem')->name('carts.item.reject');
        Route::post('/carts/{id}/bulk-update', 'bulkUpdate')->name('carts.bulkUpdate');
    });

    // Guest Management
    Route::controller(SearchController::class)->group(function () {
        Route::get('/guests/search', 'searchGuests')->name('guests.search');
    });
    Route::resource('guests', GuestController::class)->except('show');

    // Pegawai Management
    Route::controller(AdminPegawaiController::class)->group(function () {
        Route::resource('pegawai', AdminPegawaiController::class);
        Route::get('/pegawai/{id}/produk', 'showProduk')->name('pegawai.produk'); // Perbaiki nama route
        Route::post('/pegawai/{id}/scan', 'scan')->name('pegawai.scan'); // Perbaiki nama route
        Route::get('/pegawai/{id}/cart', 'showCart')->name('pegawai.cart'); // Perbaiki nama route
        Route::delete('/pegawai/{pegawai}/cart/item/{id}', 'destroyCartItem')->name('pegawai.cart.item.destroy');
        Route::post('/pegawai/{id}/cart/save', 'saveCartToItemOut')->name('pegawai.cart.save'); // Perbaiki nama route
    });

    // Produk Guest
    Route::controller(ProdukController::class)->group(function () {
        Route::get('/produk', 'index')->name('produk.index');
        Route::get('/produk/guest/{id}', 'showByGuest')->name('produk.byGuest');
        Route::post('/produk/guest/{id}/scan', 'scan')->name('produk.scan');
        Route::get('/produk/guest/{id}/cart', 'showCart')->name('produk.cart');
        Route::post('/produk/guest/{id}/release', 'release')->name('produk.release');
        Route::post('/produk/guest/{id}/cart/update', 'updateCart')->name('produk.cart.update');
        Route::delete('/produk/guest/{id}/cart/item/{itemId}', 'destroy')->name('produk.cart.remove');
    });

    // Export Barang Keluar
    Route::controller(ExportController::class)->group(function () {
        Route::get('/out', 'exportOut')->name('export.out');
        Route::post('/out/clear', 'clearOutHistory')->name('export.out.clear');
        Route::get('/export/barang-keluar/excel', 'exportBarangKeluarExcelAdmin')->name('barang_keluar.excel');
        Route::get('/export/barang-keluar/pdf', 'exportBarangKeluarPdfAdmin')->name('barang_keluar.pdf');
    });

    // Transaksi & Refund
    Route::controller(TransaksiItemOutController::class)->group(function () {
        Route::get('/transaksi', 'index')->name('transaksi.out');
        Route::get('/transaksi/search', 'index')->name('transaksi.search');
        Route::post('/refund', 'refundBarang')->name('pegawai.refund');
        Route::post('/edit-item', 'updateItem')->name('pegawai.updateItem');
        Route::post('/guest/refund', 'refundBarangGuest')->name('guest.refund');
        Route::post('/guest/edit-item', 'updateItemGuest')->name('guest.updateItem');
    });

    // Reject Barang
    Route::controller(RejectController::class)->group(function () {
        Route::get('/rejects/search', 'search')->name('rejects.search');
        Route::get('/rejects', 'index')->name('rejects.index');
        Route::get('/rejects/scan', 'scanPage')->name('rejects.scan');
        Route::post('/rejects/process', 'processScan')->name('rejects.process');
        Route::get('/rejects/check/{barcode}', 'checkBarcode')->name('rejects.check');
    });

     Route::get('/manual-book', [ManualBookAdminController::class, 'index'])->name('manual_book.index');
});

/*
|--------------------------------------------------------------------------
| Pegawai Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:pegawai'])
    ->prefix('pegawai')
    ->as('pegawai.')
    ->group(function () {
        Route::get('/dashboard', [PegawaiController::class, 'index'])->name('dashboard');
        Route::resource('cart', CartController::class);

        Route::get('/produk', [PermintaanController::class, 'index'])->name('produk');
        Route::get('/produk/search', [SearchController::class, 'index'])->name('produk.search');

        Route::get('/permintaan', [PermintaanController::class, 'permintaan'])->name('permintaan.index');
        Route::get('/permintaan/pending', [PermintaanController::class, 'pendingPermintaan'])->name('permintaan.pending');
        Route::get('/permintaan/history', [PermintaanController::class, 'historyPermintaan'])->name('permintaan.history');
        Route::put('/permintaan/update/{id}/quantity', [PermintaanController::class, 'updateQuantity'])->name('permintaan.update');
        Route::post('/permintaan/refund/{id}', [PermintaanController::class, 'refundItem'])->name('permintaan.refund');
        Route::post('/permintaan/cancel/{id}', [PermintaanController::class, 'cancelItem'])->name('permintaan.cancel');
        Route::get('/permintaan/{id}/detail', [PermintaanController::class, 'detailPermintaan'])->name('permintaan.detail');
        Route::post('/permintaan/create', [PermintaanController::class, 'createPermintaan'])->name('permintaan.create');
        Route::post('/permintaan/{id}/submit', [PermintaanController::class, 'submitPermintaan'])->name('permintaan.submit');

        Route::get('/notifications/read', [PegawaiController::class, 'readNotifications'])->name('notifications.read');
});

/*
|--------------------------------------------------------------------------
| Dashboard Redirect (Role Based)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->get('/dashboard', function () {
    $user = auth()->user();
    if ($user->role === 'super_admin') {
        return redirect()->route('super_admin.dashboard');
    } elseif ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    } elseif ($user->role === 'pegawai') {
        return redirect()->route('pegawai.dashboard');
    }
    abort(403, 'Unauthorized');
})->name('dashboard');

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';
