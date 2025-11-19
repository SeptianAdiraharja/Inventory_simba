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

        // 🔹 QUERY PEGAWAI - Hanya tampilkan yang memiliki scanned_at di cart_items
        $finishedCarts = Cart::with([
            'cartItems.item',
            'user'
        ])
        ->whereIn('status', ['approved', 'approved_partially', 'completed'])
        ->whereHas('cartItems', function($query) {
            $query->whereNotNull('scanned_at');
        })
        ->orderBy('created_at', 'desc')
        ->get();

        // 🔹 Filter cart items yang memiliki scanned_at
        $finishedCarts->each(function($cart) {
            $cart->cartItems = $cart->cartItems->filter(function($item) {
                return !is_null($item->scanned_at);
            });
        });

        // 🔹 Paginasi manual untuk pegawai
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

        // 🔹 PERBAIKAN UTAMA: QUERY GUEST - Ambil data guest yang sudah memiliki item di guest_cart_items
        $guestItemOuts = Guest::with([
                'guestCart.guestCartItems.item'
            ])
            ->whereHas('guestCart.guestCartItems') // Pastikan ada item di cart
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
            'cart_item_id' => 'required|exists:cart_items,id',
            'item_id' => 'required|exists:items,id',
            'qty' => 'required|integer|min:1',
            'code' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            // Ambil cart item dengan relasi
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
                return back()->with('error', 'Jumlah refund melebihi jumlah barang pada cart. Maksimal: ' . $cartItem->quantity);
            }

            // 🔥 PERBAIKAN UTAMA: Ambil item yang akan di-refund
            $itemToRefund = Item::findOrFail($request->item_id);

            // 🔥 LOG SEBELUM REFUND
            $stockBefore = $itemToRefund->stock;
            Log::info("🔍 BEFORE REFUND - Item: {$itemToRefund->name}, Stock Before: {$stockBefore}, Refund Qty: {$refundQty}");

            // 🔥 PERBAIKAN: Update stok barang - gunakan item yang sudah diambil
            $itemToRefund->increment('stock', $refundQty);

            // 🔥 LOG SETELAH REFUND
            $stockAfter = $itemToRefund->stock;
            Log::info("✅ AFTER REFUND - Item: {$itemToRefund->name}, Stock After: {$stockAfter}, Success: " . ($stockAfter - $stockBefore == $refundQty ? 'YES' : 'NO'));

            // Update cart item quantity
            $cartItem->quantity -= $refundQty;

            // Jika refund jumlah penuh, HAPUS data cart item
            if ($cartItem->quantity <= 0) {
                // Hapus cart item
                $cartItem->delete();

                // Hapus juga dari item_out
                Item_out::where('cart_id', $cartItem->cart_id)
                    ->where('item_id', $cartItem->item_id)
                    ->delete();
            } else {
                $cartItem->save();

                // Update item_out record jika masih ada sisa
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
            }

            // Check if all items in cart are refunded/removed
            $remainingItems = CartItem::where('cart_id', $cartItem->cart_id)->count();

            if ($remainingItems == 0) {
                // Jika semua item di cart sudah dihapus, hapus cart juga
                Cart::where('id', $cartItem->cart_id)->delete();
            }

            DB::commit();

            return back()->with('success', "Refund berhasil. {$refundQty} stok {$itemToRefund->name} dikembalikan. Stok sebelumnya: {$stockBefore}, Stok setelah: {$stockAfter}");
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
                throw new \Exception('Jumlah refund melebihi jumlah barang di cart. Maksimal: ' . $guestCartItem->quantity);
            }

            $refundQty = (int) $request->qty;

            // 🔥 PERBAIKAN UTAMA: Log sebelum refund
            $stockBefore = $item->stock;
            Log::info("🔍 BEFORE GUEST REFUND - Item: {$item->name}, Stock Before: {$stockBefore}, Refund Qty: {$refundQty}");

            // 🔥 PERBAIKAN: Update stok barang
            $item->increment('stock', $refundQty);

            // 🔥 LOG SETELAH REFUND
            $stockAfter = $item->stock;
            Log::info("✅ AFTER GUEST REFUND - Item: {$item->name}, Stock After: {$stockAfter}, Success: " . ($stockAfter - $stockBefore == $refundQty ? 'YES' : 'NO'));

            // Jika refund jumlah penuh, HAPUS data guest cart item
            if ($refundQty >= $guestCartItem->quantity) {
                // Hapus guest cart item
                $guestCartItem->delete();

                // Check jika semua item di guest cart sudah dihapus
                $remainingGuestItems = Guest_carts_item::where('guest_cart_id', $guestCartItem->guest_cart_id)->count();

                if ($remainingGuestItems == 0) {
                    // Hapus guest cart dan guest jika tidak ada item lagi
                    $guestCart = Guest_carts::find($guestCartItem->guest_cart_id);
                    if ($guestCart) {
                        Guest::where('id', $guestCart->guest_id)->delete();
                        $guestCart->delete();
                    }
                }
            } else {
                // Kurangi jumlah barang di cart tamu
                $guestCartItem->decrement('quantity', $refundQty);
            }

            // 🔹 Catat transaksi refund ke log
            Item_out_guest::create([
                'guest_id' => $guestCartItem->guestCart->guest_id,
                'items' => json_encode([
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'code' => $item->code,
                    'refund_qty' => $refundQty,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'action' => 'refund',
                    'timestamp' => now()->toDateTimeString(),
                ]),
            ]);

            DB::commit();

            return back()->with('success', "Refund barang tamu berhasil. {$refundQty} stok {$item->name} dikembalikan. Stok sebelumnya: {$stockBefore}, Stok setelah: {$stockAfter}");
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
