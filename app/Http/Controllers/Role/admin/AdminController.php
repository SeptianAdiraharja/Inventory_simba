<?php

namespace App\Http\Controllers\Role\admin;
use App\Http\Controllers\NotificationController;

use App\Http\Controllers\Controller; // Base controller Laravel
use App\Repositories\Contracts\AdminRepositoryInterface; // Interface untuk repository Admin
use Illuminate\Http\Request;
use App\Models\Cart;

class AdminController extends Controller
{
    /**
     * Repository yang menangani query terkait data admin.
     *
     * @var AdminRepositoryInterface
     */
    protected $adminRepo;

    /**
     * Konstruktor untuk dependency injection.
     *
     * AdminRepositoryInterface akan otomatis di-bind
     * ke implementasi (AdminRepository) melalui Service Provider.
     */
    public function __construct(AdminRepositoryInterface $adminRepo)
    {
        $this->adminRepo = $adminRepo;
    }

    /**
     * Tampilkan halaman dashboard admin.
     *
     * Method ini mengambil data dari AdminRepository,
     * lalu mengoper ke view "role.admin.dashboard".
     */
    public function index()
    {
        // --- Statistik Utama ---
        $totalBarangKeluar = $this->adminRepo->getTotalBarangKeluar(); // jumlah semua barang keluar
        $totalRequest      = $this->adminRepo->getTotalRequest();      // jumlah semua request barang
        $totalGuest        = $this->adminRepo->getTotalGuest();        // jumlah guest yang terdaftar

        // --- Data Chart Tahunan ---
        // Mengembalikan collection "barangMasuk" dan "barangKeluar"
        [$barangMasuk, $barangKeluar] = $this->adminRepo->getChartDataYear();

        // --- Data Terbaru ---
        $latestBarangKeluar = $this->adminRepo->getLatestBarangKeluar(); // 5 barang keluar terakhir
        $latestRequest      = $this->adminRepo->getLatestRequest();      // 5 request terakhir

        // --- Top 5 Requesters ---
        $topRequesters = $this->adminRepo->getTopRequesters();

        // --- Label bulan untuk chart ---
        $labels = [
            'Januari','Februari','Maret','April','Mei','Juni',
            'Juli','Agustus','September','Oktober','November','Desember'
        ];

        // --- Susun data chart bulanan (isi default 0 kalau kosong) ---
        $dataMasuk  = []; // array jumlah barang masuk per bulan
        $dataKeluar = []; // array jumlah barang keluar per bulan
        for ($i = 1; $i <= 12; $i++) {
            $dataMasuk[]  = $barangMasuk[$i] ?? 0;
            $dataKeluar[] = $barangKeluar[$i] ?? 0;
        }

          $carts = Cart::with(['user', 'cartItems.item'])->get();

        return view('role.admin.dashboard', compact(
            'totalBarangKeluar',
            'totalRequest',
            'totalGuest',
            'labels',
            'dataKeluar',
            'latestBarangKeluar',
            'latestRequest',
            'topRequesters',
            'carts'
        ));
    }

    public function loadModalData($type)
{
    if ($type === 'barang_keluar') {
        $items = \App\Models\Item_out::with('item')->latest()->paginate(5);
    } elseif ($type === 'request') {
        $items = \App\Models\Cart::with(['items', 'user'])->latest()->paginate(5);
    } else {
        return response()->json(['error' => 'Invalid type'], 400);
    }

    $html = view('partials.modal-content', compact('items', 'type'))->render();
    return response()->json(['html' => $html]);
}

public function barangKeluarModal()
{
    // Ambil data barang keluar (atau sesuaikan nama model-nya)
    $barangKeluar = \App\Models\Item_out::latest()->take(10)->get();

    return response()->json([
        'status' => 'success',
        'data' => $barangKeluar
    ]);
}


    /**
     * Endpoint Ajax Chart
     *
     * Mengembalikan data chart JSON berdasarkan filter range
     * (mingguan, bulanan, 3 bulan, 6 bulan, tahunan).
     *
     * Route ini biasanya dipanggil via AJAX di frontend
     * untuk update chart tanpa reload halaman.
     */
    public function getChartData(Request $request)
    {
        // Ambil query string "range", default = "week"
        $range = $request->query('range', 'week');

        // Ambil data chart berdasarkan range dari repository
        $data = $this->adminRepo->getChartDataByRange($range);

        // Kembalikan dalam bentuk JSON
        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $cart = Cart::findOrFail($id);
        $status = $request->input('status');

        $cart->status = $status;
        $cart->save();

        // Buat notifikasi via NotificationController

        return redirect()->back()->with('success', "Permintaan berhasil di {$status}.");
    }

}
