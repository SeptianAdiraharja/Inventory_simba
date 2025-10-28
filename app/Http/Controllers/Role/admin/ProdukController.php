<?php

namespace App\Http\Controllers\Role\admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Guest;
use App\Models\Item_out_guest;
use App\Models\Category;
use App\Models\Guest_carts_item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q');
        $kategori = $request->input('kategori');

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
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $categories = Category::all();

        return view('role.admin.produk', compact('items', 'categories'));
    }

    /**
     * Tampilkan produk + cart guest (hanya item yang belum direlease)
     */
    public function showByGuest($id)
    {
        $guest = Guest::with('guestCart')->findOrFail($id);
        $items = Item::with('category')->get();

        $cart = $guest->guestCart;

        // ✅ Ambil item cart yang belum direlease
        $cartItems = collect();
        if ($cart) {
            $cartItems = Guest_carts_item::where('guest_cart_id', $cart->id)
                ->whereNull('released_at')
                ->with('item')
                ->get()
                ->pluck('item')
                ->map(function ($guestCartItems) {
                    $item = $guestCartItems->item;
                    if($item){
                        $item->quantity = $guestCartItems->quantity;
                    }
                    return $item;
                })
            ->filter();
        }

        return view('role.admin.produk', compact('guest', 'items', 'cartItems'));
    }

    /**
     * Ambil cart guest untuk modal (AJAX)
     */
    public function showCart($guestId)
    {
        $guest = Guest::with('guestCart')->findOrFail($guestId);

        $guestCart = $guest->guestCart;

        // ✅ Ambil hanya item yang belum direlease
        $guestCartItems = collect();
        if ($guestCart) {
            $guestCartItems = Guest_carts_item::where('guest_cart_id', $guestCart->id)
                ->whereNull('released_at') // 👈 hanya item yang belum keluar
                ->with('item')
                ->get();
        }

        $cartItems = $guestCartItems->map(function ($cartItem) {
            return [
                'id'       => $cartItem->item->id,
                'name'     => $cartItem->item->name,
                'code'     => $cartItem->item->code,
                'quantity' => $cartItem->quantity,
            ];
        });

        return response()->json(['cartItems' => $cartItems]);
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
        return $request->ajax()
            ? response()->json(['status' => 'success', 'message' => $message])
            : back()->with('success', $message);
    }

    /**
     * ✅ Checkout / Release barang guest
     * Sekarang: update kolom released_at di guest_cart_items
     */
    public function release($guestId)
    {
        $guest = Guest::with(['guestCart.items'])->findOrFail($guestId);

        // Validasi cart
        if (!$guest->guestCart || $guest->guestCart->items->isEmpty()) {
            return redirect()->back()->with('error', 'Keranjang guest kosong.');
        }

        DB::beginTransaction();
        try {
            // 🔹 Ambil hanya item yang belum direlease
            $cartItems = Guest_carts_item::where('guest_cart_id', $guest->guestCart->id)
                ->whereNull('released_at')
                ->with('item')
                ->get();

            if ($cartItems->isEmpty()) {
                return redirect()->back()->with('warning', 'Semua barang sudah dikeluarkan sebelumnya.');
            }

            // 🔹 Siapkan data untuk JSON log
            $itemsData = $cartItems->map(function ($cartItem) {
                return [
                    'item_id'  => $cartItem->item->id,
                    'name'     => $cartItem->item->name,
                    'quantity' => $cartItem->quantity,
                ];
            })->toArray();

            // 🔹 Simpan ke tabel item_out_guests
            Item_out_guest::create([
                'guest_id'   => $guest->id,
                'items'      => json_encode($itemsData),
                'printed_at' => now(),
            ]);

            // 🔹 Kurangi stok
            foreach ($cartItems as $cartItem) {
                $item = $cartItem->item;
                if ($item->stock < $cartItem->quantity) {
                    throw new \Exception("Stok untuk {$item->name} tidak mencukupi.");
                }

                $item->decrement('stock', $cartItem->quantity);
            }

            // 🔹 Update semua item di cart jadi released
            Guest_carts_item::where('guest_cart_id', $guest->guestCart->id)
                ->whereNull('released_at')
                ->update(['released_at' => now()]);

            DB::commit();

            return redirect()
                ->route('admin.produk.byGuest', $guest->id)
                ->with('success', 'Barang berhasil dikeluarkan dan ditandai sebagai released.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }


    // Resource method bawaan
    public function create() {}
    public function store(Request $request) {}
    public function show(string $id) {}
    public function edit(string $id) {}
    public function update(Request $request, string $id) {}
    public function destroy(string $id) {}
}
