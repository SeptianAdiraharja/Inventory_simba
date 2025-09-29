<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\PegawaiRepository;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PegawaiController extends Controller
{
    protected $pegawaiRepository;


    /**
     * Display a listing of the resource.
     */
     public function __construct(PegawaiRepository $pegawaiRepository)
    {
        $this->pegawaiRepository = $pegawaiRepository;
    }

    public function index(Request $request)
    {
        $range = $request->get('range', 'week');
        $history = $this->pegawaiRepository->getUserRequestHistory($range);

        $userId = Auth::id();

            // Ambil list request user yg login saja
            $users = DB::table('users')
            ->where('users.id', $userId)
            ->leftJoin('carts', 'users.id', '=', 'carts.user_id')
            ->leftJoin('cart_items', 'carts.id', '=', 'cart_items.cart_id')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.role',
                DB::raw('COALESCE(SUM(cart_items.quantity), 0) as total_request')
            )
            ->groupBy('users.id', 'users.name', 'users.email', 'users.role')
            ->get();

        // Pastikan $history memiliki format array yang kita pakai di blade
        if (!is_array($history) || !isset($history['labels']) || !isset($history['data'])) {
            $history = ['labels' => [], 'data' => []];
        }

        return view('role.pegawai.dashboard', compact('history', 'range', 'users'));
    }

    public function produk()
    {
        return view('role.pegawai.produk');
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}