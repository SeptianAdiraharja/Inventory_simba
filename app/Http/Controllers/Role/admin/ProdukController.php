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
use Illuminate\Support\Carbon;

class ProdukController extends Controller
{
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
            ->orderBy('stock', 'desc') // 🔥 Urutkan dari stok terbanyak ke terkecil
            ->latest() // Tetap pertahankan latest untuk barang baru
            ->paginate(12)
            ->withQueryString();

        // Ambil semua kategori untuk dropdown filter
        $categories = Category::all();

        return view('role.admin.produk', compact('items', 'categories'));
    }

    /**
     * Tampilkan produk + cart guest
     */
    public function showByGuest($id, Request $request)
    {
        $guest = Guest::with(['guestCart.items' => function($query) {
            $query->wherePivot('released_at', null);
        }])->findOrFail($id);

        // Query dengan pencarian
        $query = Item::with('category');

        // Filter berdasarkan pencarian
        if ($request->has('q') && !empty($request->q)) {
            $search = $request->q;
            $query->where('name', 'like', "%{$search}%");
        }

        // Filter berdasarkan kategori
        if ($request->has('kategori') && $request->kategori != 'none') {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('name', $request->kategori);
            });
        }

        // 🔥 URUTKAN: Stok terbanyak ke terkecil, kemudian barang terbaru
        $items = $query->orderBy('stock', 'desc')
                    ->latest()
                    ->paginate(12);

        // Ambil cart items untuk guest melalui guestCart
        $cartItems = $guest->guestCart ? $guest->guestCart->items : collect();

        // Hitung jumlah pengeluaran minggu ini
        $releaseCountThisWeek = $this->getReleaseCountThisWeek($guest->id);
        $maxReleasePerWeek = 3;
        $isLimitReached = $releaseCountThisWeek >= $maxReleasePerWeek;

        return view('role.admin.produk', compact(
            'guest',
            'items',
            'cartItems',
            'releaseCountThisWeek',
            'maxReleasePerWeek',
            'isLimitReached'
        ));
    }

    /**
     * Hitung jumlah pengeluaran barang guest dalam 1 minggu terakhir
     */
    private function getReleaseCountThisWeek($guestId)
    {
        $startOfWeek = Carbon::now()->startOfWeek(); // Senin minggu ini
        $endOfWeek = Carbon::now()->endOfWeek();     // Minggu minggu ini

        return Item_out_guest::where('guest_id', $guestId)
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->count();
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

        // Hitung jumlah pengeluaran minggu ini
        $releaseCountThisWeek = $this->getReleaseCountThisWeek($guestId);
        $maxReleasePerWeek = 3;
        $isLimitReached = $releaseCountThisWeek >= $maxReleasePerWeek;

        return response()->json([
            'cartItems' => $cartItems,
            'releaseCountThisWeek' => $releaseCountThisWeek,
            'maxReleasePerWeek' => $maxReleasePerWeek,
            'isLimitReached' => $isLimitReached
        ]);
    }


     /**
     * ✅ Checkout / Release barang guest dengan validasi batas mingguan
     */
    public function release($guestId)
    {
        $guest = Guest::with('guestCart.items')->findOrFail($guestId);

        // Cek batas pengeluaran mingguan
        $releaseCountThisWeek = $this->getReleaseCountThisWeek($guestId);
        $maxReleasePerWeek = 3;

        if ($releaseCountThisWeek >= $maxReleasePerWeek) {
            return redirect()->back()->with('error',
                "Guest telah mencapai batas maksimal pengeluaran barang ({$maxReleasePerWeek} kali) dalam seminggu."
            );
        }

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

            $cart->items()->detach($itemId);

            return response()->json(['status' => 'success', 'message' => 'Item berhasil dihapus dari cart.']);
        } catch (\Throwable $e) {
            Log::error('Cart remove error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
}
