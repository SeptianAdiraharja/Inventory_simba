<?php

namespace App\Http\Controllers\Role\admin;

use App\Http\Controllers\Controller;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Guest;
use App\Models\CartItem;
use App\Models\Item;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransaksiItemOutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $items = Item::all();

        // 🔹 Ambil cart pegawai yang semua item-nya sudah discan
        $finishedCarts = Cart::with(['cartItems.item', 'user'])
            ->whereIn('status', ['approved', 'approved_partially'])
            ->get()
            ->filter(function ($cart) {
                return $cart->cartItems->every(fn($i) => $i->scanned_at);
            });

        // 🔹 Paginasi manual untuk Collection
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $pagedCarts = $finishedCarts->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $finishedCartsPaginated = new LengthAwarePaginator(
            $pagedCarts,
            $finishedCarts->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // 🔹 Data tamu
        $guestItemOuts = Guest::with(['guestCart.guestCartItems.item'])
            ->whereHas('guestCart.guestCartItems')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('role.admin.data_transaksi', [
            'finishedCarts' => $finishedCartsPaginated,
            'guestItemOuts' => $guestItemOuts,
            'items' => $items
        ]);
    }

    /**
     * 🔹 Proses Refund Barang
     */
    public function refundBarang(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'qty' => 'required|integer|min:1',
            'barcode' => 'required|string'
        ]);

        try {
            DB::transaction(function () use ($request) {
                $item = Item::findOrFail($request->item_id);

                // Kembalikan stok barang
                $item->increment('stock', $request->qty);

                // Catat ke log atau tabel refund jika kamu punya
                DB::table('refund_logs')->insert([
                    'item_id' => $item->id,
                    'barcode' => $request->barcode,
                    'qty' => $request->qty,
                    'processed_by' => auth::id(),
                    'created_at' => now(),
                ]);
            });

            return back()->with('success', 'Barang berhasil direfund.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Refund gagal: ' . $e->getMessage());
        }
    }

    /**
     * 🔹 Proses Edit Barang dan Qty
     */
    public function updateItem(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|exists:cart_items,id',
            'item_id' => 'required|exists:items,id',
            'qty' => 'required|integer|min:1'
        ]);

        try {
            DB::transaction(function () use ($request) {
                $cartItem = CartItem::findOrFail($request->cart_item_id);

                // Jika barang diganti, stok lama dikembalikan dulu
                if ($cartItem->item_id != $request->item_id) {
                    $oldItem = Item::find($cartItem->item_id);
                    $oldItem->increment('stock', $cartItem->quantity);

                    // Kurangi stok barang baru
                    $newItem = Item::find($request->item_id);
                    $newItem->decrement('stock', $request->qty);

                    $cartItem->item_id = $request->item_id;
                } else {
                    // Jika barang sama, update stok berdasarkan selisih
                    $diff = $request->qty - $cartItem->quantity;
                    $cartItem->item->decrement('stock', $diff);
                }

                $cartItem->quantity = $request->qty;
                $cartItem->save();
            });

            return back()->with('success', 'Data barang berhasil diperbarui.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memperbarui: ' . $e->getMessage());
        }
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
