<?php

namespace App\Http\Controllers\Role\admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Guest;
use App\Models\Item_out_guest;
use App\Models\Guest_carts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProdukController extends Controller
{
    /**
     * Tampilkan semua produk
     */
    public function index()
    {
        $items = Item::with('category')->get();
        return view('role.admin.produk', compact('items'));
    }

    /**
     * Tampilkan produk + cart guest
     */
    public function showByGuest($id)
    {
        $guest = Guest::with('guestCart.items')->findOrFail($id);
        $items = Item::with('category')->get();
        $cartItems = $guest->guestCart?->items ?? collect();

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
            'quantity' => 'nullable|integer|min:1'
        ]);

        $guest = Guest::findOrFail($guestId);

        // Buat cart jika belum ada
        $cart = $guest->guestCart()->firstOrCreate(
            ['guest_id' => $guest->id],
            ['session_id' => session()->getId()]
        );

        // Tambah / update item ke pivot guest_cart_items
        if ($cart->items()->where('items.id', $request->item_id)->exists()) {
            $cart->items()->updateExistingPivot($request->item_id, [
                'quantity' => DB::raw("quantity + " . ($request->quantity ?? 1))
            ]);
        } else {
            $cart->items()->attach($request->item_id, [
                'quantity' => $request->quantity ?? 1
            ]);
        }

        return redirect()->back()->with('success', 'Barang berhasil discan ke keranjang guest.');
    }

    /**
     * Ambil cart guest untuk modal (AJAX)
     */
    public function showCart($guestId)
    {
        $guest = Guest::with('guestCart.items')->findOrFail($guestId);

        $cartItems = $guest->guestCart?->items->map(function($item) {
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

        // Validasi cart
        if (!$guest->guestCart || $guest->guestCart->items->isEmpty()) {
            return redirect()->back()->with('error', 'Keranjang guest kosong.');
        }

        // Jika sudah direlease, tolak duplikasi
        if ($guest->guestCart->is_released ?? false) {
            return redirect()->back()->with('warning', 'Barang untuk guest ini sudah pernah dikeluarkan.');
        }

        DB::beginTransaction();
        try {
            // Data item untuk JSON
            $itemsData = $guest->guestCart->items->map(function ($item) {
                return [
                    'item_id'  => $item->id,
                    'name'     => $item->name,
                    'quantity' => $item->pivot->quantity,
                ];
            })->toArray();

            // Simpan pengeluaran
            Item_out_guest::create([
                'guest_id'   => $guest->id,
                'items'      => json_encode($itemsData),
                'printed_at' => now(),
            ]);

            // Kurangi stok setiap item
            foreach ($guest->guestCart->items as $item) {
                if ($item->stock < $item->pivot->quantity) {
                    throw new \Exception("Stok untuk {$item->name} tidak mencukupi.");
                }

                $item->decrement('stock', $item->pivot->quantity);
            }

            // ✅ Tandai cart sudah direlease tanpa menghapusnya
            $guest->guestCart->update(['is_released' => true]);

            DB::commit();

            return redirect()
                ->route('admin.produk.byGuest', $guest->id)
                ->with('success', 'Barang berhasil dikeluarkan. Data cart disimpan untuk laporan.');
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
