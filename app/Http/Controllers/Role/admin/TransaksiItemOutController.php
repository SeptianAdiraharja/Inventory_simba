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
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $items = Item::all();

        // 🔹 Ambil cart pegawai yang semua item-nya sudah discan
        $finishedCarts = Cart::with([
            'cartItems' => function ($q) {
                $q->where('status', 'approved'); // hanya ambil item yang approved
            },
            'cartItems.item',
            'user'
        ])
        ->whereIn('status', ['approved', 'approved_partially'])
        ->get()
        ->filter(function ($cart) {
            // hanya ambil cart yang punya minimal 1 item approved dan sudah discan
            return $cart->cartItems->isNotEmpty() &&
                $cart->cartItems->every(fn($i) => $i->scanned_at);
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
            'cart_item_id' => 'required|exists:cart_items,id',
            'item_id' => 'required|exists:items,id',
            'code' => 'required|string',
            'qty' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            // Ambil cart item yang benar
            $cartItem = CartItem::findOrFail($request->cart_item_id);

            // Cek bahwa cartItem terkait dengan item_id yang dikirim
            if ($cartItem->item_id != $request->item_id) {
                return back()->with('error', 'Data cart item tidak cocok dengan barang yang dipilih.');
            }

            // Cari Item berdasarkan code (hasil scan)
            $scannedItem = Item::where('code', $request->code)->first();
            if (!$scannedItem) {
                return back()->with('error', 'Code tidak ditemukan di database.');
            }

            // Pastikan scanned item sama dengan item yang ada di cart item
            if ($scannedItem->id != $cartItem->item_id) {
                return back()->with('error', 'Code tidak cocok dengan barang pada cart.');
            }

            $cartId = $cartItem->cart_id;
            $itemId = $cartItem->item_id;
            $refundQty = (int) $request->qty;

            // Cari Item_out terkait berdasarkan cart_id + item_id (lebih spesifik)
            $itemOut = Item_out::where('cart_id', $cartId)
                ->where('item_id', $itemId)
                ->orderByDesc('created_at')
                ->first();

            if (!$itemOut) {
                return back()->with('error', 'Data transaksi barang keluar tidak ditemukan untuk cart ini.');
            }

            // Validasi jumlah refund terhadap item_out dan cart_item
            if ($refundQty > $itemOut->quantity) {
                return back()->with('error', 'Jumlah refund melebihi jumlah barang yang tercatat di transaksi keluar.');
            }

            if ($refundQty > $cartItem->quantity) {
                return back()->with('error', 'Jumlah refund melebihi jumlah pada cart.');
            }

            // Kurangi quantity di item_out
            $itemOut->quantity -= $refundQty;
            $itemOut->save();

            // Kurangi quantity di cart_item
            $cartItem->quantity -= $refundQty;
            if ($cartItem->quantity <= 0) {
                $cartItem->quantity = 0;
                $cartItem->status = 'refunded';
            }
            $cartItem->save();

            // Tambahkan kembali stok item
            $scannedItem->increment('stock', $refundQty);

            // Jika semua item di cart sudah refunded, ubah status cart
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
            Log::error('Refund error: ' . $e->getMessage() . ' -- trace: ' . $e->getTraceAsString());
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
                    throw new \Exception("Code tidak ditemukan di database.");
                }

                // ✅ Pastikan item yang dipilih sama dengan hasil scan
                if ($scannedItem->id != $request->item_id) {
                    throw new \Exception("Code tidak cocok dengan barang yang dipilih.");
                }

                // ✅ Jika barang diganti dengan barang lain
                if ($cartItem->item_id != $request->item_id) {
                    $oldItem = Item::find($cartItem->item_id);
                    $oldItem->increment('stock', $cartItem->quantity); // Kembalikan stok lama

                    $newItem = Item::find($request->item_id);
                    if ($newItem->stock < $request->qty) {
                        throw new \Exception("Stok barang baru tidak mencukupi.");
                    }

                    $newItem->decrement('stock', $request->qty);
                    $cartItem->item_id = $request->item_id;
                    $cartItem->quantity = $request->qty;
                } else {
                    // ✅ Barang sama → cek selisih
                    $diff = $request->qty - $cartItem->quantity;

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

                    $cartItem->quantity = $request->qty;
                }

                $cartItem->save();
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
            'guest_cart_item_id' => 'required|exists:guest_cart_items,id', // sesuai tabel relasi
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
