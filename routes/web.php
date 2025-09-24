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
use App\Http\Controllers\Role\admin\GuestCartController;



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

        // Export Barang Masuk
        Route::get('/export/barang-masuk/excel', [ExportController::class, 'exportBarangMasukExcel'])
            ->name('export.barang_masuk.excel');
        Route::get('/export/barang-masuk/pdf', [ExportController::class, 'exportBarangMasukPdf'])
            ->name('export.barang_masuk.pdf');

        // Export Barang Keluar
        Route::get('/export/barang-keluar/excel', [ExportController::class, 'exportBarangKeluarExcel'])
            ->name('export.barang_keluar.excel');
        Route::get('/export/barang-keluar/pdf', [ExportController::class, 'exportBarangKeluarPdf'])
            ->name('export.barang_keluar.pdf');
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
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/data', [AdminController::class, 'getChartData']);

        // Item Out
        Route::resource('itemout', ItemoutController::class);
        // Struk PDF
        Route::get('/itemout/{cart}/struk', [ItemoutController::class, 'struk'])
            ->name('itemout.struk');

        // Scan barang
        Route::post('/itemout/{cartItem}/scan', [ItemoutController::class, 'scan'])
        ->name('itemout.scan');

        // Request
        Route::get('/request', [RequestController::class, 'index'])->name('request');
        Route::resource('carts', RequestController::class);

        // Guests
        Route::resource('guests', GuestController::class);

        // Produk guest
        Route::get('/produk/guest/{id}', [ProdukController::class, 'showByGuest'])
            ->name('produk.byGuest');

        // Guest Cart

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
        Route::get('/permintaan', [PermintaanController::class, 'permintaan'])->name('permintaan.index');
        Route::get('/permintaan/pending', [PermintaanController::class, 'pendingPermintaan'])->name('permintaan.pending');
        Route::get('/permintaan/{id}', [PermintaanController::class, 'detailPermintaan'])->name('permintaan.detail');

        Route::post('/permintaan/create', [PermintaanController::class, 'createPermintaan'])->name('permintaan.create');
        Route::post('/permintaan/{id}/submit', [PermintaanController::class, 'submitPermintaan'])->name('permintaan.submit');
});

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';
