<?php

namespace App\Http\Controllers\Role\admin;

use App\Http\Controllers\Controller;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Guest;
use App\Models\Guest_carts_item;
use App\Models\Guest_carts;
use App\Models\Item_out_guest;
use App\Models\CartItem;
use App\Models\Item;
use App\Models\Item_out;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class TransaksiItemOutController extends Controller
{
    public function index(Request $request)
    {
        $items = Item::all();

        // 🔹 QUERY PEGAWAI YANG DIPERBAIKI - Tampilkan SEMUA transaksi selesai
        $finishedCarts = Cart::with([
            'cartItems.item', // Selalu load item, regardless of status
            'user'
        ])
        ->whereIn('status', ['approved', 'approved_partially', 'completed'])
        ->whereHas('cartItems') // Pastikan punya item
        ->orderBy('created_at', 'desc')
        ->get();

        // 🔹 Paginasi manual
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

        // 🔹 QUERY GUEST YANG DIPERBAIKI
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
    /**
 * 🔹 Proses Refund Barang - VERSI FIXED
 */
    public function refundBarang(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|exists:cart_items,id',
            'item_id' => 'required|exists:items,id',
            'qty' => 'required|integer|min:1',
            'code' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            // Ambil cart item
            $cartItem = CartItem::with(['item', 'cart'])->findOrFail($request->cart_item_id);

            // Validasi item_id match
            if ($cartItem->item_id != $request->item_id) {
                return back()->with('error', 'Data cart item tidak cocok dengan barang yang dipilih.');
            }

            // Validasi code
            $scannedItem = Item::where('code', $request->code)->first();
            if (!$scannedItem) {
                return back()->with('error', 'Kode barang tidak ditemukan.');
            }
            if ($scannedItem->id != $request->item_id) {
                return back()->with('error', 'Kode barang tidak cocok dengan item yang dipilih.');
            }

            $refundQty = (int) $request->qty;

            // Validasi quantity
            if ($refundQty > $cartItem->quantity) {
                return back()->with('error', 'Jumlah refund melebihi jumlah barang pada cart.');
            }

            // Update cart item quantity
            $cartItem->quantity -= $refundQty;

            if ($cartItem->quantity <= 0) {
                $cartItem->quantity = 0;
                $cartItem->status = 'refunded';
            }
            $cartItem->save();

            // Update item stock
            $scannedItem->increment('stock', $refundQty);

            // Update item_out record
            $itemOut = Item_out::where('cart_id', $cartItem->cart_id)
                ->where('item_id', $cartItem->item_id)
                ->first();

            if ($itemOut) {
                $itemOut->quantity -= $refundQty;
                if ($itemOut->quantity <= 0) {
                    $itemOut->delete();
                } else {
                    $itemOut->save();
                }
            }

            // Check if all items in cart are refunded
            $remainingItems = CartItem::where('cart_id', $cartItem->cart_id)
                ->where('quantity', '>', 0)
                ->count();

            if ($remainingItems == 0) {
                Cart::where('id', $cartItem->cart_id)->update(['status' => 'refunded']);
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
    /**
     * 🔹 Proses Edit Barang dan Qty Pegawai
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
                $cartItem = CartItem::with('item')->findOrFail($request->cart_item_id);

                // ✅ Cari barang berdasarkan code
                $scannedItem = Item::where('code', $request->code)->first();

                if (!$scannedItem) {
                    throw new \Exception("Code tidak ditemukan di database.");
                }

                // ✅ Pastikan item yang dipilih sama dengan hasil scan
                if ($scannedItem->id != $request->item_id) {
                    throw new \Exception("Code tidak cocok dengan barang yang dipilih.");
                }

                $oldQty = $cartItem->quantity;
                $newQty = $request->qty;

                // ✅ Jika barang diganti dengan barang lain
                if ($cartItem->item_id != $request->item_id) {
                    $oldItem = Item::find($cartItem->item_id);
                    $oldItem->increment('stock', $oldQty); // Kembalikan stok lama

                    $newItem = Item::find($request->item_id);
                    if ($newItem->stock < $newQty) {
                        throw new \Exception("Stok barang baru tidak mencukupi.");
                    }

                    $newItem->decrement('stock', $newQty);
                    $cartItem->item_id = $request->item_id;
                    $cartItem->quantity = $newQty;
                } else {
                    // ✅ Barang sama → cek selisih
                    $diff = $newQty - $oldQty;

                    if ($diff > 0) {
                        // Tambah qty → kurangi stok
                        if ($cartItem->item->stock < $diff) {
                            throw new \Exception("Stok tidak mencukupi untuk menambah jumlah barang.");
                        }
                        $cartItem->item->decrement('stock', $diff);
                    } elseif ($diff < 0) {
                        // Kurangi qty → tambah stok
                        $cartItem->item->increment('stock', abs($diff));
                    }

                    $cartItem->quantity = $newQty;
                }

                $cartItem->save();

                // Update juga di item_out
                $itemOut = Item_out::where('cart_id', $cartItem->cart_id)
                    ->where('item_id', $cartItem->item_id)
                    ->orderByDesc('created_at')
                    ->first();

                if ($itemOut) {
                    $itemOut->quantity = $newQty;
                    $itemOut->save();
                }
            });

            return back()->with('success', 'Barang berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Update Item Error: ' . $e->getMessage());
            return back()->with('error', 'Gagal memperbarui barang: ' . $e->getMessage());
        }
    }

    /**
     * 🔹 Refund Barang untuk Guest
     */
    public function refundBarangGuest(Request $request)
    {
        $request->validate([
            'guest_cart_item_id' => 'required|exists:guest_cart_items,id',
            'item_id' => 'required|exists:items,id',
            'qty' => 'required|integer|min:1',
            'code' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            // 🔹 Ambil data item cart
            $guestCartItem = Guest_carts_item::with(['item', 'guestCart'])
                ->findOrFail($request->guest_cart_item_id);

            // 🔹 Validasi scan code
            $item = Item::where('id', $request->item_id)
                ->where('code', $request->code)
                ->first();

            if (!$item) {
                throw new \Exception('Kode tidak cocok dengan barang yang dipilih.');
            }

            // 🔹 Validasi qty refund
            if ($request->qty > $guestCartItem->quantity) {
                throw new \Exception('Jumlah refund melebihi jumlah barang di cart.');
            }

            // 🔹 Kurangi jumlah barang di cart tamu
            $guestCartItem->decrement('quantity', $request->qty);

            // 🔹 Tambahkan stok barang
            $item->increment('stock', $request->qty);

            // 🔹 Catat transaksi refund ke log (opsional)
            Item_out_guest::create([
                'guest_id' => $guestCartItem->guestCart->guest_id,
                'items' => json_encode([
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'code' => $item->code,
                    'refund_qty' => $request->qty,
                    'action' => 'refund',
                    'timestamp' => now()->toDateTimeString(),
                ]),
            ]);

            DB::commit();

            return back()->with('success', 'Refund barang tamu berhasil. Stok dikembalikan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Refund Guest Error: ' . $e->getMessage());
            return back()->with('error', 'Refund gagal: ' . $e->getMessage());
        }
    }

    /**
     * 🔹 Update Barang & Qty untuk Guest
     */
    public function updateItemGuest(Request $request)
    {
        $request->validate([
            'guest_cart_item_id' => 'required|exists:guest_cart_items,id',
            'item_id'            => 'required|exists:items,id',
            'qty'                => 'required|integer|min:1',
            'code'               => 'required|string',
        ]);

        try {
            DB::transaction(function () use ($request) {

                // Ambil record cart item guest
                $guestCartItem = Guest_carts_item::findOrFail($request->guest_cart_item_id);
                $item = Item::findOrFail($guestCartItem->item_id);

                // Validasi code hasil scan
                $scannedItem = Item::where('code', $request->code)->first();
                if (!$scannedItem || $scannedItem->id != $request->item_id) {
                    throw new \Exception('Kode scan tidak cocok dengan barang yang dipilih.');
                }

                // Jika user memilih item baru, kembalikan stok lama
                if ($guestCartItem->item_id != $request->item_id) {
                    // kembalikan stok lama
                    $item->increment('stock', $guestCartItem->quantity);

                    // kurangi stok baru
                    $newItem = Item::findOrFail($request->item_id);
                    if ($newItem->stock < $request->qty) {
                        throw new \Exception('Stok tidak mencukupi untuk barang yang dipilih.');
                    }
                    $newItem->decrement('stock', $request->qty);

                    // update item id dan qty
                    $guestCartItem->update([
                        'item_id'  => $request->item_id,
                        'quantity' => $request->qty,
                    ]);
                } else {
                    // Barang sama → cek selisih qty
                    $diff = $request->qty - $guestCartItem->quantity;

                    if ($diff > 0) {
                        // nambah barang → kurangi stok
                        if ($item->stock < $diff) {
                            throw new \Exception('Stok tidak mencukupi untuk menambah jumlah.');
                        }
                        $item->decrement('stock', $diff);
                    } elseif ($diff < 0) {
                        // mengurangi barang → kembalikan stok
                        $item->increment('stock', abs($diff));
                    }

                    $guestCartItem->update(['quantity' => $request->qty]);
                }
            });

            return back()->with('success', 'Barang tamu berhasil diperbarui.');
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
