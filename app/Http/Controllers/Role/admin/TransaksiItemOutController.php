<?php

namespace App\Http\Controllers\Role\admin;

use App\Http\Controllers\Controller;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Guest;
use App\Models\CartItem;
use App\Models\Item;
use App\Models\Item_out;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            'code' => 'required|string',
            'qty' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            // ✅ Pastikan code cocok dengan item yang dimaksud
            $scannedItem = Item::where('id', $request->item_id)
                ->where('code', $request->code)
                ->first();

            if (!$scannedItem) {
                return back()->with('error', 'Code tidak cocok dengan barang yang dipilih.');
            }

            // ✅ Ambil semua item_out yang berhubungan dengan item_id
            $itemOut = Item_out::where('item_id', $request->item_id)
                ->orderByDesc('created_at')
                ->first();

            if (!$itemOut) {
                return back()->with('error', 'Data transaksi barang keluar tidak ditemukan.');
            }

            $cartId = $itemOut->cart_id;

            if ($request->qty > $itemOut->quantity) {
                return back()->with('error', 'Jumlah refund melebihi jumlah barang keluar.');
            }

            // ✅ Kurangi quantity di item_out
            $itemOut->quantity -= $request->qty;
            $itemOut->save();

            // ✅ Update cart_item
            $cartItem = CartItem::where('item_id', $request->item_id)
                ->where('cart_id', $cartId)
                ->first();

            if (!$cartItem) {
                return back()->with('error', 'Data cart item tidak ditemukan.');
            }

            if ($request->qty > $cartItem->quantity) {
                return back()->with('error', 'Jumlah refund melebihi jumlah di cart.');
            }

            $cartItem->quantity -= $request->qty;

            // Jika sudah 0, ubah status menjadi refunded
            if ($cartItem->quantity <= 0) {
                $cartItem->quantity = 0;
                $cartItem->status = 'refunded';
            }

            $cartItem->save();

            // ✅ Tambahkan kembali stok item
            $scannedItem->increment('stock', $request->qty);

            // ✅ Jika semua item di cart sudah refunded, ubah status cart
            $remainingItems = CartItem::where('cart_id', $cartId)
                ->where(function ($q) {
                    $q->whereNull('status')->orWhere('status', '!=', 'refunded');
                })
                ->count();

            if ($remainingItems == 0) {
                Cart::where('id', $cartId)->update(['status' => 'refunded']);
            }

            DB::commit();
            return back()->with('success', 'Refund berhasil. Stok barang dikembalikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Refund error: ' . $e->getMessage());
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
            'qty' => 'required|integer|min:1',
            'code' => 'required|string'
        ]);

        try {
            DB::transaction(function () use ($request) {
                $cartItem = CartItem::findOrFail($request->cart_item_id);

                // ✅ Cari barang berdasarkan code
                $scannedItem = Item::where('code', $request->code)->first();

                if (!$scannedItem) {
                    throw new \Exception("code tidak ditemukan di database.");
                }

                // ✅ Pastikan item yang dipilih sama dengan hasil scan
                if ($scannedItem->id != $request->item_id) {
                    throw new \Exception("code tidak cocok dengan barang yang dipilih.");
                }

                // ✅ Jika barang diganti
                if ($cartItem->item_id != $request->item_id) {
                    $oldItem = Item::find($cartItem->item_id);
                    $oldItem->increment('stock', $cartItem->quantity);

                    $newItem = Item::find($request->item_id);
                    if ($newItem->stock < $request->qty) {
                        throw new \Exception("Stok barang baru tidak mencukupi.");
                    }

                    $newItem->decrement('stock', $request->qty);
                    $cartItem->item_id = $request->item_id;
                } else {
                    // ✅ Barang sama → hitung selisih
                    $diff = $request->qty - $cartItem->quantity;
                    if ($diff > 0) {
                        if ($cartItem->item->stock < $diff) {
                            throw new \Exception("Stok tidak mencukupi untuk menambah jumlah.");
                        }
                        $cartItem->item->decrement('stock', $diff);
                    } elseif ($diff < 0) {
                        $cartItem->item->increment('stock', abs($diff));
                    }
                }

                // ✅ Simpan perubahan
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
