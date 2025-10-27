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
use App\Http\Controllers\SearchController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

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

        // CRUD Master Data
        Route::resources([
            'categories' => CategoryController::class,
            'items'      => ItemController::class,
            'item_ins'   => Item_inController::class,
            'units'      => UnitController::class,
            'suppliers'  => SupplierController::class,
            'users'      => UserController::class,
        ]);

        // Barcode
        Route::get('items/{item}/barcode-pdf', [ItemController::class, 'printBarcode'])
            ->name('items.barcode.pdf');

        Route::get('/export', [ExportController::class, 'index'])
            ->name('export.index');

        // Export Barang Masuk
        Route::get('/export/barang-masuk/excel', [ExportController::class, 'exportBarangMasukExcel'])
            ->name('exports.barang_masuk.excel');
        Route::get('/export/barang-masuk/pdf', [ExportController::class, 'exportBarangMasukPdf'])
            ->name('exports.barang_masuk.pdf');

        // Export Barang Keluar
        Route::get('/export/barang-keluar/excel', [ExportController::class, 'exportBarangKeluarExcel'])
            ->name('exports.barang_keluar.excel');
        Route::get('/export/barang-keluar/pdf', [ExportController::class, 'exportBarangKeluarPdf'])
            ->name('exports.barang_keluar.pdf');
        Route::get('/export/download', [ExportController::class, 'download'])
            ->name('export.download');

        // Export Barang Reject
        Route::get('/export/barang-reject-improved', [ExportController::class, 'exportBarangRejectExcelImproved'])
            ->name('export.barangRejectImproved');
        Route::get('/export/barang-reject/pdf', [ExportController::class, 'exportBarangRejectPdf'])
            ->name('exports.barang_reject.pdf');

        Route::delete('/export/clear', [ExportController::class, 'clearLogs'])->name('export.clear');
});


Route::middleware(['auth', 'role:admin'])
->prefix('admin')
->as('admin.')
->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */
    Route::controller(AdminController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard');
        Route::get('/dashboard/data', 'getChartData');
        Route::get('/dashboard/modal/{type}', 'loadModalData')->name('dashboard.modal.data');
        Route::get('/dashboard/modal/barang_keluar', 'barangKeluarModal')->name('dashboard.modal.barang_keluar');
    });


    /*
    |--------------------------------------------------------------------------
    | Item Out (Barang Keluar)
    |--------------------------------------------------------------------------
    */
    Route::controller(ItemoutController::class)->group(function () {
        Route::resource('itemout', ItemoutController::class);
        Route::get('/itemout/{cart}/struk', 'struk')->name('itemout.struk');
        Route::post('/itemout/scan/{cart}', 'scan')->name('itemout.scan');
        Route::get('/itemout/check-all-scanned/{cart}', 'checkAllScanned')->name('itemout.checkAllScanned');
        Route::post('/itemout/release/{cart}', 'release')->name('itemout.release');
    });


    /*
    |--------------------------------------------------------------------------
    | Requests & Carts
    |--------------------------------------------------------------------------
    | Mengelola permintaan pegawai (approval / reject).
    */
    Route::controller(RequestController::class)->group(function () {
        Route::get('/request', 'index')->name('request');
        Route::get('/carts/{id}', 'show')->name('carts.show');
        Route::patch('/carts/{id}', 'update')->name('carts.update'); // ✅ penting
        Route::patch('/carts/item/{id}/approve', 'approveItem')->name('carts.item.approve');
        Route::patch('/carts/item/{id}/reject', 'rejectItem')->name('carts.item.reject');
        Route::post('/carts/{id}/bulk-update', 'bulkUpdate')->name('carts.bulkUpdate');
    });


    /*
    |--------------------------------------------------------------------------
    | Guest Management
    |--------------------------------------------------------------------------
    */
    Route::controller(SearchController::class)->group(function () {
        Route::get('/guests/search', 'searchGuests')->name('guests.search');
    });

    Route::resource('guests', GuestController::class)->except('show');


        /*
    |--------------------------------------------------------------------------
    | Pegawai Management
    |--------------------------------------------------------------------------
    */
    Route::controller(SearchController::class)->group(function () {
        Route::get('/guests/search', 'searchGuests')->name('guests.search');
    });

    Route::controller(AdminPegawaiController::class)->group(function () {
        Route::resource('pegawai', AdminPegawaiController::class);
        Route::get('/pegawai/{id}/produk', 'showProduk')->name('pegawai.produk');
        Route::post('/pegawai/{id}/scan', [AdminPegawaiController::class, 'scan'])->name('pegawai.scan');
        Route::get('/pegawai/{id}/cart', [AdminPegawaiController::class, 'showCart'])->name('pegawai.cart');
        Route::delete('/pegawai/{pegawai}/cart/item/{id}', [AdminPegawaiController::class, 'destroyCartItem'])->name('admin.pegawai.cart.item.destroy');
        Route::post('/pegawai/{id}/cart/save', [AdminPegawaiController::class, 'saveCartToItemOut'])->name('pegawai.cart.save');


    });



    /*
    |--------------------------------------------------------------------------
    | Produk Guest
    |--------------------------------------------------------------------------
    */
    Route::controller(ProdukController::class)->group(function () {
        Route::get('/produk', 'index')->name('produk.index');
        Route::get('/produk/guest/{id}', 'showByGuest')->name('produk.byGuest');
        Route::post('/produk/guest/{id}/scan', 'scan')->name('produk.scan');
        Route::get('/produk/guest/{id}/cart', 'showCart')->name('produk.cart');
        Route::post('/produk/guest/{id}/release', 'release')->name('produk.release');
    });


    /*
    |--------------------------------------------------------------------------
    | Export Barang Keluar
    |--------------------------------------------------------------------------
    */
    Route::controller(ExportController::class)->group(function () {
        Route::get('/out', 'exportOut')->name('export.out');
        Route::post('/out/clear', 'clearOutHistory')->name('export.out.clear');
        Route::get('/export/barang-keluar/excel', 'exportBarangKeluarExcelAdmin')->name('barang_keluar.excel');
        Route::get('/export/barang-keluar/pdf', 'exportBarangKeluarPdfAdmin')->name('barang_keluar.pdf');
    });

    /*
    |--------------------------------------------------------------------------
    | Data Transaksi & refund
    |--------------------------------------------------------------------------
    */
    Route::controller(TransaksiItemOutController::class)->group(function () {
        Route::get('/transaksi', 'index')->name('transaksi.out');

        Route::post('/refund', 'refundBarang')->name('pegawai.refund');
        Route::post('/edit-item', 'updateItem')->name('pegawai.updateItem');

        Route::post('/guest/refund', 'refundBarangGuest')->name('guest.refund');
        Route::post('/guest/edit-item', 'updateItemGuest')->name('guest.updateItem');

    });



    /*
    |--------------------------------------------------------------------------
    | Reject Barang
    |--------------------------------------------------------------------------
    */
    Route::controller(RejectController::class)->group(function () {
        Route::get('/rejects', 'index')->name('rejects.index');
        Route::get('/rejects/scan', 'scanPage')->name('rejects.scan');
        Route::post('/rejects/process', 'processScan')->name('rejects.process');
        Route::get('/rejects/check/{barcode}', 'checkBarcode')->name('rejects.check');
    });



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

        // Produk & Permintaan
        Route::get('/produk', [PermintaanController::class, 'index'])->name('produk');

        Route::get('/produk/search', [SearchController::class, 'index'])
            ->name('produk.search');

        Route::get('/permintaan', [PermintaanController::class, 'permintaan'])->name('permintaan.index');
        Route::get('/permintaan/pending', [PermintaanController::class, 'pendingPermintaan'])->name('permintaan.pending');
        Route::get('/permintaan/history', [PermintaanController::class, 'historyPermintaan'])->name('permintaan.history');
        Route::put('/permintaan/update/{id}/quantity', [PermintaanController::class, 'updateQuantity'])->name('permintaan.update');
        Route::get('/permintaan/{id}', [PermintaanController::class, 'detailPermintaan'])->name('permintaan.detail');
        Route::post('/permintaan/refund/{id}', [PermintaanController::class, 'refundItem'])->name('permintaan.refund');

        Route::post('/permintaan/create', [PermintaanController::class, 'createPermintaan'])->name('permintaan.create');
        Route::post('/permintaan/{id}/submit', [PermintaanController::class, 'submitPermintaan'])->name('permintaan.submit');

        Route::get('/notifications/read', [PegawaiController::class, 'readNotifications'])
        ->name('notifications.read');

});

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';
