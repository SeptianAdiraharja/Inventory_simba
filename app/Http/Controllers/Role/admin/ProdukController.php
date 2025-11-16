<?php

namespace App\Http\Controllers\Role\admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Guest;
use App\Models\Item_out_guest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProdukController extends Controller
{
    /**
     * Tampilkan semua produk
     */
    /**
     * Tampilkan semua produk
     */
    public function index(Request $request)
    {
        $query = $request->input('q');
        $kategori = $request->input('kategori');

        // Query utama - urutkan berdasarkan stok (terbanyak ke terkecil)
        $items = Item::with('category')
            ->when($query, function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {
                    $sub->where('name', 'LIKE', "%{$query}%")
                        ->orWhereHas('category', function ($cat) use ($query) {
                            $cat->where('name', 'LIKE', "%{$query}%");
                        });
                });
            })
            ->when($kategori && $kategori !== 'none', function ($q) use ($kategori) {
                $q->whereHas('category', function ($cat) use ($kategori) {
                    $cat->where('name', $kategori);
                });
            })
            ->when(!$query, function ($q) {
                // Hanya tampilkan barang dengan stok > 0 jika tidak sedang search
                $q->where('stock', '>', 0);
            })
            ->orderBy('stock', 'desc') // Urutkan dari stok terbanyak ke terkecil
            ->latest()
            ->paginate(12)
            ->withQueryString(); // 🔥 menjaga query tetap ada saat pagination

        // Ambil semua kategori untuk dropdown filter
        $categories = Category::all();

        return view('role.admin.produk', compact('items', 'categories'));
    }

    /**
     * Tampilkan produk + cart guest
     */
    /**
     * Tampilkan produk + cart guest
     */
    public function showByGuest($id)
    {
       $guest = Guest::with(['guestCart.items' => function ($q) {
            $q->wherePivot('released_at', null);
        }])->findOrFail($id);

        $items = Item::with('category')
            ->where('stock', '>', 0) // Hanya tampilkan barang dengan stok > 0
            ->orderBy('stock', 'desc') // Urutkan dari stok terbanyak ke terkecil
            ->paginate(12);

        $cart = $guest->guestCart;
        $cartItems = $cart?->items ?? collect();

        return view('role.admin.produk', compact('guest', 'items', 'cartItems'));
    }



    /**
     * Scan item ke cart guest
     */
    public function scan(Request $request, $guestId)
    {
        $request->validate([
            'item_id'  => 'required|exists:items,id',
            'barcode'  => 'required|string',
            'quantity' => 'required|integer|min:1'
        ]);

        $guest = Guest::findOrFail($guestId);
        $item = Item::findOrFail($request->item_id);

        // 🧩 Validasi kode barang
        if (trim($item->code) !== trim($request->barcode)) {
            $message = "❌ Kode <b>{$request->barcode}</b> tidak cocok dengan <b>{$item->name}</b> ({$item->code}).";
            return $request->ajax()
                ? response()->json(['status' => 'error', 'message' => $message], 422)
                : back()->with('error', $message);
        }

        // 🧩 Cek stok
        if ($request->quantity > $item->stock) {
            $message = "⚠️ Stok untuk <b>{$item->name}</b> hanya tersedia <b>{$item->stock}</b>.";
            return $request->ajax()
                ? response()->json(['status' => 'error', 'message' => $message], 422)
                : back()->with('error', $message);
        }

        // 🛒 Buat cart jika belum ada
        $cart = $guest->guestCart()->firstOrCreate(
            ['guest_id' => $guest->id],
            ['session_id' => session()->getId()]
        );

        $existing = $cart->items()->where('items.id', $item->id)->first();

        if ($existing) {
            $newQty = $existing->pivot->quantity + $request->quantity;

            if ($newQty > $item->stock) {
                $message = "❗ Jumlah total untuk <b>{$item->name}</b> melebihi stok tersedia (<b>{$item->stock}</b>).";
                return $request->ajax()
                    ? response()->json(['status' => 'error', 'message' => $message], 422)
                    : back()->with('error', $message);
            }

            $cart->items()->updateExistingPivot($item->id, [
                'quantity' => $newQty,
                'updated_at' => now(),
            ]);

            $message = "🔁 Jumlah <b>{$item->name}</b> diperbarui jadi <b>{$newQty}</b>.";
        } else {
            $cart->items()->attach($item->id, [
                'quantity' => $request->quantity,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $message = "✅ Barang <b>{$item->name}</b> sebanyak <b>{$request->quantity}</b> ditambahkan ke keranjang.";
        }

        // 🔄 Jika AJAX, kirim JSON agar tidak reload halaman
       if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => $message,
                'item_name' => $item->name,
                'quantity' => $request->quantity,
                'item_id' => $item->id,
            ]);
        }

        // 🔙 Kalau bukan AJAX, redirect biasa
        return back()->with('success', $message);
    }


    /**
     * Ambil cart guest untuk modal (AJAX)
     */
   public function showCart($guestId)
    {
        $guest = Guest::with(['guestCart.items' => function ($q) {
            // hanya ambil item yang belum direlease
            $q->wherePivot('released_at', null);
        }])->findOrFail($guestId);

        $cartItems = $guest->guestCart?->items->map(function ($item) {
            return [
                'id'       => $item->id,
                'name'     => $item->name,
                'code'     => $item->code,
                'quantity' => $item->pivot->quantity,
            ];
        }) ?? collect();

        return response()->json(['cartItems' => $cartItems]);
    }


    /**
     * ✅ Checkout / Release barang guest
     * Tidak menghapus cart dan pivot, hanya menandai is_released = true
     */
    public function release($guestId)
    {
        $guest = Guest::with('guestCart.items')->findOrFail($guestId);

        if (!$guest->guestCart || $guest->guestCart->items->isEmpty()) {
            return redirect()->back()->with('error', 'Keranjang guest kosong.');
        }

        if ($guest->guestCart->is_released ?? false) {
            return redirect()->back()->with('warning', 'Barang untuk guest ini sudah pernah dikeluarkan.');
        }

        DB::beginTransaction();
        try {
            $itemsData = $guest->guestCart->items->map(function ($item) {
                return [
                    'item_id'  => $item->id,
                    'name'     => $item->name,
                    'quantity' => $item->pivot->quantity,
                ];
            })->toArray();

            Item_out_guest::create([
                'guest_id'   => $guest->id,
                'items'      => json_encode($itemsData),
                'printed_at' => now(),
            ]);

            foreach ($guest->guestCart->items as $item) {
                if ($item->stock < $item->pivot->quantity) {
                    throw new \Exception("Stok untuk {$item->name} tidak mencukupi.");
                }

                $item->decrement('stock', $item->pivot->quantity);

                // 🔹 Tambahkan baris ini agar released_at terisi
                DB::table('guest_cart_items')
                    ->where('guest_cart_id', $guest->guestCart->id)
                    ->where('item_id', $item->id)
                    ->update(['released_at' => now()]);
            }

            // Tandai cart sudah direlease
            $guest->guestCart->update(['is_released' => true]);

            DB::commit();

            return redirect()
                ->route('admin.produk.byGuest', $guest->id)
                ->with('success', 'Barang berhasil dikeluarkan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function updateCart(Request $request, $guestId)
    {
        try {
            $guest = \App\Models\Guest::with('guestCart.items')->findOrFail($guestId);
            $cart = $guest->guestCart;

            if (!$cart) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cart tidak ditemukan.'
                ], 404);
            }

            $itemId = $request->input('item_id');
            $quantity = (int) $request->input('quantity');

            // Validasi kuantitas
            if ($quantity < 1) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Jumlah harus minimal 1.'
                ], 422);
            }

            $item = $cart->items()->where('items.id', $itemId)->first();
            if (!$item) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Item tidak ditemukan di cart.'
                ], 404);
            }

            // Cek stok sebelum update
            if ($quantity > $item->stock) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Stok tersedia untuk {$item->name} hanya {$item->stock}."
                ], 422);
            }

            // Update pivot table
            $cart->items()->updateExistingPivot($itemId, [
                'quantity' => $quantity,
                'updated_at' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Jumlah barang berhasil diperbarui.'
            ]);
        } catch (\Throwable $e) {
            Log::error('Cart update error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat update cart: ' . $e->getMessage()
            ], 500);
        }
    }

    // Resource method bawaan
    public function create() {}
    public function store(Request $request) {}
    public function show(string $id) {}
    public function edit(string $id) {}
    public function update(Request $request, string $id) {}
    public function destroy($guestId, $itemId) {
        try {
            $guest = Guest::with('guestCart.items')->findOrFail($guestId);
            $cart = $guest->guestCart;

            if (!$cart) {
                return response()->json(['status' => 'error', 'message' => 'Cart tidak ditemukan.'], 404);
            }

            $item = $cart->items()->where('items.id', $itemId)->first();
            if (!$item) {
                return response()->json(['status' => 'error', 'message' => 'Item tidak ditemukan di cart.'], 404);
            }

            // Hapus relasi item dari pivot
            $cart->items()->detach($itemId);

            return response()->json(['status' => 'success', 'message' => 'Item berhasil dihapus dari cart.']);
        } catch (\Throwable $e) {
            Log::error('Cart remove error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
}
