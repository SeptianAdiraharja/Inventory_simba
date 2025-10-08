<?php

namespace App\Http\Controllers\Role\admin;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\AdminRepositoryInterface;
use App\Models\Cart;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    protected $adminRepo;

    public function __construct(AdminRepositoryInterface $adminRepo)
    {
        $this->adminRepo = $adminRepo;
    }

    /**
     * 🔹 Dashboard utama admin
     */
    public function index()
    {
        $totalBarangKeluar = $this->adminRepo->getTotalBarangKeluar();
        $totalRequest = $this->adminRepo->getTotalRequest();
        $totalGuest = $this->adminRepo->getTotalGuest();

        $latestBarangKeluar = $this->adminRepo->getLatestBarangKeluar();
        $latestRequest = $this->adminRepo->getLatestRequest();
        $topRequesters = $this->adminRepo->getTopRequesters();

        return view('role.admin.dashboard', compact(
            'totalBarangKeluar',
            'totalRequest',
            'totalGuest',
            'latestBarangKeluar',
            'latestRequest',
            'topRequesters'
        ));
    }

    /**
     * 🔹 Endpoint AJAX Chart Range
     */
    public function getChartData(Request $request)
    {
        $range = $request->query('range', 'week');
        $data = $this->adminRepo->getChartDataByRange($range);
        return response()->json($data);
    }

    /**
     * 🔹 Update status permintaan (approve/reject)
     */
    public function update(Request $request, $id)
    {
        $cart = Cart::findOrFail($id);
        $cart->status = $request->input('status');
        $cart->save();

        return redirect()->back()->with('success', "Permintaan berhasil di {$cart->status}.");
    }
}
