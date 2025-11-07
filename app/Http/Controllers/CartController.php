<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class CartController extends Controller
{

    /**
     * Hitung total pengajuan minggu ini (status pending/approved).
     */
    private function getWeeklyRequestCount(): int
    {
        $now = Carbon::now('Asia/Jakarta');

        // awal minggu = Senin
        $daysToSubtract = ($now->dayOfWeek === Carbon::SUNDAY) ? 6 : $now->dayOfWeek - 1;
        $startOfWeek = $now->copy()->subDays($daysToSubtract)->startOfDay();
        $endOfWeek   = $startOfWeek->copy()->addDays(6)->endOfDay();

        return Cart::where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'approved'])
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->count();
    }

    /**
     * Tampilkan keranjang aktif user.
     */
    public function index()
    {
        $cart = Cart::where('user_id', Auth::id())
                    ->where('status', 'active')
                    ->with('cartItems.item')
                    ->first();

        $countThisWeek = $this->getWeeklyRequestCount();

        return view('role.pegawai.cart', compact('cart', 'countThisWeek'));
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
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cartItem = CartItem::findOrFail($id);
        // logic misal cart yang dituju beda sm akun yg di login
        if ($cartItem->cart->user_id !== auth()->id()) {
            abort(403, 'No Permission');
        }
        $newQty = $validated['quantity'];
        $oldQty = $cartItem->quantity;
        $item = $cartItem->item;

        // Hitung selisih
        $diff = $newQty - $oldQty;

        if ($diff > 0) {
            // User nambah jumlah barang → kurangi stok
            if ($item->stock < $diff) {
                return back()->with('error', "Stok tidak mencukupi! Sisa stok: {$item->stock}");
            }
            $item->stock -= $diff;
        } elseif ($diff < 0) {
            // User ngurangin jumlah barang → tambahin stok
            $item->stock += abs($diff);
        }
        $cartItem->quantity = $validated['quantity'];
        $cartItem->save();
        $item->save();
        return redirect()->back()->with('success', 'Jumlah barang berhasil diubah!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cartItem = CartItem::findOrFail($id);
        $cart = $cartItem->cart;

        if ($cart->user_id !== Auth::id() || $cart->status !== 'active') {
            return redirect()->back()->with('error', 'Akses tidak sah.');
        }

        $cartItem->item->increment('stock', $cartItem->quantity);

        $cartItem->delete();

        if ($cart->cartItems()->count() === 0) {
            $cart->delete();
        }

        return redirect()->back()->with('success', 'Produk dihapus dari keranjang.');
    }
}
