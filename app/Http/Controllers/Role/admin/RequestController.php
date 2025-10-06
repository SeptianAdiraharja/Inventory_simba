<?php

namespace App\Http\Controllers\Role\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Cart;
use App\Models\Notification;

class RequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // 🔹 1. Hapus otomatis request yang sudah approved lebih dari 3 hari
        DB::table('carts')
            ->where('status', 'approved')
            ->where('updated_at', '<', now()->subDays(3))
            ->delete();

        // 🔹 Ambil filter dari query string (?status=pending / ?status=rejected / ?status=all)
        $statusFilter = $request->get('status');

        // 🔹 2. Ambil data sesuai filter
        $requests = DB::table('carts')
            ->join('users', 'carts.user_id', '=', 'users.id')
            ->leftJoin('cart_items', 'carts.id', '=', 'cart_items.cart_id')
            ->select(
                'carts.id as cart_id',
                'users.name',
                'users.email',
                'users.role',
                'carts.status',
                'carts.created_at',
                'carts.updated_at',
                DB::raw('COALESCE(SUM(cart_items.quantity), 0) as total_quantity')
            )
            ->where(function($query) use ($statusFilter) {
                // 🔸 Filter sesuai dropdown
                if ($statusFilter === 'pending') {
                    // Semua pending tanpa batas waktu
                    $query->where('carts.status', 'pending');
                } elseif ($statusFilter === 'rejected') {
                    // Rejected hanya yang 3 hari terakhir
                    $query->where('carts.status', 'rejected')
                          ->where('carts.updated_at', '>=', now()->subDays(3));
                } else {
                    // Default "all" → pending + rejected (<= 3 hari)
                    $query->where('carts.status', 'pending')
                          ->orWhere(function($sub) {
                              $sub->where('carts.status', 'rejected')
                                  ->where('carts.updated_at', '>=', now()->subDays(3));
                          });
                }
            })
            ->groupBy(
                'carts.id',
                'users.name',
                'users.email',
                'users.role',
                'carts.status',
                'carts.created_at',
                'carts.updated_at'
            )
            ->orderBy('carts.created_at', 'desc')
            ->paginate(10);

        return view('role.admin.request', compact('requests', 'statusFilter'));
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
        DB::table('carts')
            ->where('id', $id)
            ->update([
                'status' => $request->status,
                'updated_at' => now()
            ]);

        $cart = DB::table('carts')->where('id', $id)->first();

        if ($cart) {
            Notification::create([
                'user_id' => $cart->user_id,
                'title'   => 'Request ' . ucfirst($request->status),
                'message' => 'Permintaan barang kamu telah ' . $request->status,
                'status'  => 'unread',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->back()->with('success', 'Status berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
