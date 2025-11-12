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
            ->withSum('cartItems as total_dibeli', 'quantity')
            ->orderByDesc('total_dibeli')
            ->orderByDesc('created_at') // misal kalau sama ambil yang baru dibuat
            ->where('stock', '>', 0)
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
                $userId = Auth::id();

                $cart = Cart::firstOrCreate([
                    'user_id' => $userId,
                    'status' => 'active',
                ]);

                foreach ($request->items as $itemData) {
                    $item = Item::lockForUpdate()->findOrFail($itemData['item_id']);

                    if ($item->stock <= 0) {
                        throw new \Exception("Stok {$item->name} sudah habis.");
                    }

                    if ($itemData['quantity'] > $item->stock) {
                        throw new \Exception("Jumlah melebihi stok {$item->name} (tersisa {$item->stock}).");
                    }

                    $item->decrement('stock', $itemData['quantity']);

                    $cartItem = CartItem::firstOrNew([
                        'cart_id' => $cart->id,
                        'item_id' => $item->id,
                    ]);

                    $cartItem->quantity = ($cartItem->quantity ?? 0) + $itemData['quantity'];
                    $cartItem->save();
                }
            });

            // 🔹 Gunakan flash message biasa (bukan SweetAlert)
            return redirect()->route('pegawai.produk')->with('success', 'Barang berhasil dimasukkan ke keranjang!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
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
     * 🔸 Hanya di sini SweetAlert muncul
     */
    public function submitPermintaan($id)
    {
        $cart = Cart::where('id', $id)
            ->where('user_id', Auth::id())
            ->where('status', 'active')
            ->with('cartItems.item')
            ->firstOrFail();

        if ($cart->cartItems->isEmpty()) {
            return redirect()->back()->with('error', 'Keranjang masih kosong.');
        }

        DB::transaction(function () use ($cart) {
            $cart->update(['status' => 'pending']);
        });

        return redirect()->back()->with([
            'swal' => [
                'icon' => 'success',
                'title' => 'Sukses!',
                'text' => 'Permintaan berhasil diajukan, menunggu persetujuan admin.'
            ]
        ]);
    }

    public function historyPermintaan()
    {
        $carts = Cart::withCount('cartItems')
            ->where('user_id', Auth::id())
            ->where('status', '!=', 'active')
            ->when(request('status') && request('status') != 'all', function ($query) {
                $query->where('status', request('status'));
            })
            ->latest()
            ->paginate(10);

        $statusCounts = [
            'all' => Cart::where('user_id', Auth::id())->where('status', '!=', 'active')->count(),
            'pending' => Cart::where('user_id', Auth::id())->where('status', 'pending')->count(),
            'approved' => Cart::where('user_id', Auth::id())->where('status', 'approved')->count(),
            'rejected' => Cart::where('user_id', Auth::id())->where('status', 'rejected')->count(),
        ];

        return view('role.pegawai.history', compact('carts', 'statusCounts'));
    }

    public function pendingPermintaan()
    {
        $carts = Cart::withCount('cartItems')
            ->where('user_id', Auth::id())
            ->where('status', 'pending')
            ->paginate(10);

        return view('role.pegawai.pending', compact('carts'));
    }

    public function detailPermintaan(string $id)
    {
        $cart = Cart::with(['cartItems.item.category'])->findOrFail($id);
        return view('role.pegawai.permintaan_detail', compact('cart'));
    }

    public function updateQuantity(Request $request, string $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () use ($validated, $id) {
                $cartItem = CartItem::findOrFail($id);
                $cart = $cartItem->cart;

                if ($cart->user_id !== Auth::id()) {
                    throw new \Exception('Akses tidak sah untuk keranjang ini.');
                }

                if ($cart->status !== 'active') {
                    throw new \Exception('Hanya bisa mengubah keranjang yang masih aktif.');
                }

                $item = $cartItem->item;
                $diff = $validated['quantity'] - $cartItem->quantity;

                if ($diff > 0 && $item->stock < $diff) {
                    throw new \Exception("Stok tidak mencukupi! Sisa stok: {$item->stock}");
                }

                $item->decrement('stock', max($diff, 0));
                $item->increment('stock', max(-$diff, 0));

                $cartItem->update(['quantity' => $validated['quantity']]);
            });

            return redirect()->back()->with('success', 'Jumlah barang berhasil diubah.');
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
                $cart = $cartItem->cart;

                if ($cart->user_id !== Auth::id()) {
                    throw new \Exception('Akses tidak sah untuk keranjang ini.');
                }

                if ($cart->status !== 'active') {
                    throw new \Exception('Hanya bisa ubah keranjang aktif.');
                }

                $cartItem->item->increment('stock', $cartItem->quantity);
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

    public function refundItem(string $id)
    {
        try {
            DB::transaction(function () use ($id) {
                $cart = Cart::with('cartItems.item')->findOrFail($id);

                if ($cart->user_id !== Auth::id()) {
                    throw new \Exception('Akses tidak sah untuk keranjang ini.');
                }

                foreach ($cart->cartItems as $cartItem) {
                    $cartItem->item->increment('stock', $cartItem->quantity);
                }

                $cart->update(['status' => 'rejected']);
            });

            return redirect()->back()->with('success', 'Permintaan berhasil di-refund dan stok dikembalikan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function cancelItem(string $id)
    {
        try {
            DB::transaction(function () use ($id) {
                $cart = Cart::with('cartItems.item')->findOrFail($id);

                // Pastikan user hanya bisa cancel miliknya sendiri
                if ($cart->user_id !== Auth::id()) {
                    throw new \Exception('Akses tidak sah untuk keranjang ini.');
                }

                // Loop setiap item dalam cart
                foreach ($cart->cartItems as $cartItem) {
                    // Kembalikan stok item
                    $cartItem->item->increment('stock', $cartItem->quantity);

                    // Update status cart_item jadi rejected
                    $cartItem->update([
                        'status' => 'rejected',
                        'rejection_reason' => 'Dibatalkan oleh pengguna pada ' . now()->format('d-m-Y H:i'),
                        'updated_at' => now(),
                    ]);
                }

                // Update status cart utama
                $cart->update([
                    'status' => 'rejected',
                    'updated_at' => now(),
                ]);
            });

            return redirect()->back()->with('success', 'Permintaan berhasil di-cancel dan stok dikembalikan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
