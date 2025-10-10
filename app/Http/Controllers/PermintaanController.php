<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Item;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PermintaanController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        $items = Item::with('category')
            ->latest()
            ->paginate(12);

        return view('role.pegawai.produk', compact('categories', 'items'));
    }

    public function createPermintaan(Request $request)
    {
        $request->validate([
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $cart = Cart::firstOrCreate(
                    ['user_id' => Auth::id(), 'status' => 'active'],
                    ['user_id' => Auth::id(), 'status' => 'active']
                );

                foreach ($request->items as $itemData) {
                    $item = Item::lockForUpdate()->findOrFail($itemData['item_id']); // 🔒 kunci baris supaya stok aman di transaksi paralel

                    if ($item->stock <= 0) {
                        throw new \Exception("Stok {$item->name} sudah habis.");
                    }

                    if ($itemData['quantity'] > $item->stock) {
                        throw new \Exception("Jumlah melebihi stok {$item->name} (tersisa {$item->stock}).");
                    }

                    // Kurangi stok dulu baru lanjut
                    $item->decrement('stock', $itemData['quantity']);

                    // Tambahkan item ke keranjang
                    $cartItem = CartItem::firstOrNew([
                        'cart_id' => $cart->id,
                        'item_id' => $item->id,
                    ]);

                    $cartItem->quantity = ($cartItem->quantity ?? 0) + $itemData['quantity'];
                    $cartItem->save();
                }
            });

            $itemName = Item::find($request->items[0]['item_id'])->name;
            $qty = $request->items[0]['quantity'];

            return redirect()->route('pegawai.produk')
                ->with('success', "$qty x $itemName berhasil ditambahkan ke keranjang!");
        } catch (\Exception $e) {
            return redirect()->route('pegawai.produk')
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }


    public function getActiveCart()
    {
        return Cart::where('user_id', Auth::id())
            ->where('status', 'active')
            ->with('cartItems.item')
            ->first();
    }

    public function permintaan()
    {
        $carts = Cart::with(['cartItems.item'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('permintaan.index', compact('carts'));
    }

    /**
     * Saat tombol "Ajukan Permintaan" ditekan.
     * Mengubah status keranjang dari active → pending.
     */
    public function submitPermintaan($id)
    {
        $cart = Cart::where('id', $id)
            ->where('user_id', Auth::id())
            ->where('status', 'active')
            ->with('cartItems.item')
            ->firstOrFail();

        DB::transaction(function () use ($cart) {
            // Ubah status menjadi pending
            $cart->status = 'pending';
            $cart->save();
        });

        return redirect()
            ->route('pegawai.permintaan.detail', $cart->id)
            ->with('success', 'Permintaan berhasil diajukan, menunggu persetujuan admin.');
    }

    public function pendingPermintaan()
    {
        $carts = Cart::withCount('cartItems')
            ->where('user_id', Auth::id())
            ->where('status', 'pending')
            ->paginate(10);

        return view('role.pegawai.pending', compact('carts'));
    }

    public function detailPermintaan($id)
    {
        $cart = Cart::with(['cartItems.item'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('role.pegawai.permintaan_detail', compact('cart'));
    }

    public function updateQuantity(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|exists:cart_items,id',
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $cartItem = CartItem::findOrFail($request->cart_item_id);
                $cart = Cart::findOrFail($cartItem->cart_id);

                if ($cart->user_id !== Auth::id()) {
                    throw new \Exception('Akses tidak sah untuk keranjang ini.');
                }

                if ($cart->status !== 'active') {
                    throw new \Exception('Hanya bisa ubah keranjang aktif.');
                }

                $item = Item::findOrFail($cartItem->item_id);

                if ($request->quantity > $item->stock + $cartItem->quantity) {
                    throw new \Exception("Maaf, stok tidak cukup untuk {$item->name}.");
                }

                $item->increment('stock', $cartItem->quantity);
                $cartItem->quantity = $request->quantity;
                $cartItem->save();
                $item->decrement('stock', $request->quantity);
            });

            return redirect()->back()->with('success', 'Jumlah produk berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function removeItem(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|exists:cart_items,id',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $cartItem = CartItem::findOrFail($request->cart_item_id);
                $cart = Cart::findOrFail($cartItem->cart_id);

                if ($cart->user_id !== Auth::id()) {
                    throw new \Exception('Akses tidak sah untuk keranjang ini.');
                }

                if ($cart->status !== 'active') {
                    throw new \Exception('Hanya bisa ubah keranjang aktif.');
                }

                $item = Item::findOrFail($cartItem->item_id);
                $item->increment('stock', $cartItem->quantity);

                $cartItem->delete();

                if ($cart->cartItems()->count() === 0) {
                    $cart->delete();
                }
            });

            return redirect()->back()->with('success', 'Produk berhasil dihapus dari keranjang.');
        } catch (\Exception $e) {
            return redirect()->route('pegawai.produk')->with('error', $e->getMessage());
        }
    }
}
