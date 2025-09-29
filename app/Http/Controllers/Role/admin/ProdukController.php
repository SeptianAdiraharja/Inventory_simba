<?php
namespace App\Http\Controllers\Role\admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Guest;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
     public function index()
    {
        // Ambil semua item + kategori, tanpa supplier & harga
        $items = Item::with('category')->get();

        return view('role.admin.produk', compact('items'));
    }

   public function showByGuest($id)
    {
        $guest = Guest::with('guestCarts.items')->findOrFail($id);

        // Ambil semua barang (meskipun guest_cart_items kosong)
        $items = Item::with('category')->get();

        // Ambil cart items milik guest (untuk dicek di view)
        $cartItems = $guest->carts->flatMap->pivot ?? collect();

        return view('role.admin.produk', compact('guest', 'items', 'cartItems'));
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {

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
