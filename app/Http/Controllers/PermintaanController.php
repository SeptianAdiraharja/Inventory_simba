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
            ->orderByRaw('CASE WHEN stock > 0 THEN 0 ELSE 1 END') // logic stock 0/ habis
            ->orderByDesc('total_dibeli')
            ->orderByDesc('created_at') // misal kalau sama ambil yang baru dibuat
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
<<<<<<< HEAD
=======

                    if ($cartItem->quantity > $item->stock) {
                        throw new \Exception("Jumlah melebihi stok {$item->name}.");
                    }

                    // Kurangi stok sementara (akan dikunci sampai permintaan diproses)
                    $item->decrement('stock', $itemData['quantity']);
>>>>>>> 010396a9d5c8baa6b6aa71e1dc1122afda1a3702
                    $cartItem->save();
                }
            });

            $itemName = Item::find($request->items[0]['item_id'])->name;
            $qty = $request->items[0]['quantity'];

            return redirect()->route('pegawai.produk')
                ->with([
                'swal' => [
                    'icon' => 'success',
                    'title' => 'Sukses!',
                    'text' => "$qty x $itemName berhasil ditambahkan ke keranjang!",
                ]
                ]);
        } catch (\Exception $e) {
            return redirect()->back()->with([
                'swal' => [
                    'icon' => 'error',
                    'title' => 'Gagal',
                    'text' => $e->getMessage(),
                ]
            ]);
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
            ->back()
            ->with([
                'swal' => [
                    'icon' => 'success',
                    'title' => 'Sukses!',
                    'text' => "Permintaan berhasil diajukan, menunggu persetujuan admin."
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

    public function detailPermintaan($id)
    {
        $cart = Cart::with(['cartItems.item'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

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

                // Cek kepemilikan keranjang
                if ($cart->user_id !== Auth::id()) {
                    throw new \Exception('Akses tidak sah untuk keranjang ini.');
                }

                // Hanya boleh ubah keranjang aktif
                if ($cart->status !== 'active') {
                    throw new \Exception('Hanya bisa mengubah keranjang yang masih aktif.');
                }

                $item = $cartItem->item;
                $oldQty = $cartItem->quantity;
                $newQty = $validated['quantity'];
                $diff = $newQty - $oldQty;

                // Kalau nambah quantity
                if ($diff > 0) {
                    if ($item->stock < $diff) {
                        throw new \Exception("Stok tidak mencukupi! Sisa stok: {$item->stock}");
                    }
                    $item->decrement('stock', $diff);
                }
                // Kalau ngurangin quantity
                elseif ($diff < 0) {
                    $item->increment('stock', abs($diff));
                }

                $cartItem->quantity = $newQty;
                $cartItem->save();
            });

            return redirect()->back()->with([
                'swal' => [
                    'icon' => 'success',
                    'title' => 'Sukses!',
                    'text' => 'Jumlah barang berhasil diubah.'
                ]
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with([
                'swal' => [
                    'icon' => 'error',
                    'title' => 'Gagal!',
                    'text' => $e->getMessage()
                ]
            ]);
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

    public function refundItem(string $id){
        {
            try {
                DB::transaction(function () use ($id) {
                    $cart = Cart::with('cartItems.item')->findOrFail($id);

                    if ($cart->user_id !== Auth::id()) {
                        throw new \Exception('Akses tidak sah untuk keranjang ini.');
                    }

                    foreach ($cart->cartItems as $cartItem) {
                        $item = $cartItem->item;
                        $item->increment('stock', $cartItem->quantity);
                    }

                    $cart->update(['status' => 'rejected']);
                });

                return redirect()->back()->with([
                    'swal' => [
                        'icon' => 'success',
                        'title' => 'Berhasil!',
                        'text' => 'Permintaan berhasil di-refund dan stok dikembalikan.'
                    ]
                ]);
            } catch (\Exception $e) {
                return redirect()->back()->with([
                    'swal' => [
                        'icon' => 'error',
                        'title' => 'Gagal!',
                        'text' => $e->getMessage()
                    ]
                ]);
            }
        }

    }
}
