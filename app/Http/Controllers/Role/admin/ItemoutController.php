<?php

namespace App\Http\Controllers\Role\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Guest_carts;
use App\Models\CartItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Models\Item_out;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;

class ItemoutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // cart milik user (ambil SEMUA item, tidak filter scanned_at)
        $approvedItems = Cart::with(['cartItems.item', 'user'])
            ->where('status', 'approved')
            ->latest()
            ->paginate(10);

        // cart milik guest
        $guestRequests = Guest_carts::with(['guestCartItems.item', 'guest'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('role.admin.itemout', compact('approvedItems', 'guestRequests'));
    }


    // scan
   public function scan(Request $request, $cartItemId)
    {
        $request->validate([
            'barcode' => 'required|string|max:255',
        ]);

        $cartItem = CartItem::with(['item', 'cart'])->findOrFail($cartItemId);
        $barcode = $request->input('barcode');

        if ($cartItem->item->code === $barcode) {
            // kalau sudah pernah discan → jangan ulang
            if ($cartItem->scanned_at) {
                return back()->with('info', 'ℹ️ Item ini sudah discan sebelumnya.');
            }

            // tandai sudah discan
            $cartItem->update([
                'scanned_at' => now(),
            ]);

            // update parent cart picked_up_at kalau belum ada
            if (!$cartItem->cart->picked_up_at) {
                $cartItem->cart->update([
                    'picked_up_at' => now(),
                ]);
            }

            // catat ke tabel item_out (jika belum ada)
            $exists = Item_out::where('cart_id', $cartItem->cart->id)
                ->where('item_id', $cartItem->item->id)
                ->exists();

            if (!$exists) {
                Item_out::create([
                    'cart_id'     => $cartItem->cart->id,
                    'item_id'     => $cartItem->item->id,
                    'quantity'    => $cartItem->quantity,
                    'released_at' => now(),
                    'approved_by' => Auth::id(),
                ]);

                // kurangi stok item
                $cartItem->item->decrement('stock', $cartItem->quantity);
            }

            return back()->with('success', '✅ Barang berhasil discan dan masuk ke item_out.');
        }

        return back()->with('error', '❌ Barcode tidak sesuai dengan item.');
    }

    // struk
    public function struk($id)
    {
        // Ambil data cart berdasarkan ID
        $cart = Cart::with(['user', 'cartItems.item'])->findOrFail($id);

        // Ambil data item_out yang terkait dengan cart ini
        $itemOut = Item_out::where('cart_id', $cart->id)->get();

        // Load view untuk struk PDF
        $pdf = Pdf::loadView('role.admin.export.struk', [
            'cart'    => $cart,
            'itemOut' => $itemOut
        ]);

        // Output langsung sebagai file PDF
        return $pdf->stream('struk-pemesanan-' . $cart->id . '.pdf');
    }

    // generate Struk
    public function generateStruk($cartId)
    {
        $cart = Cart::with(['cartItems.item', 'user', 'guest'])->findOrFail($cartId);

        // Ambil data item_out sesuai cart
        $itemOut = Item_out::where('cart_id', $cartId)->get();

        $pdf = Pdf::loadView('role.admin.export.struk', [
            'cart' => $cart,
            'itemOut' => $itemOut
        ]);

        return $pdf->download('struk_cart_'.$cart->id.'.pdf');
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
        $request->validate([
            'item_id'  => 'required|exists:items,id',
            'guest_id' => 'required|exists:guests,id',
            'barcode'  => 'required|string',
        ]);

        $item = Item::findOrFail($request->item_id);

        if ($item->stock < 1) {
            return back()->with('error', 'Stok tidak mencukupi.');
        }

        Item_out::create([
            'item_id'    => $item->id,
            'guest_id'   => $request->guest_id,
            'quantity'   => 1,
            'released_at'=> now(),
            'approved_by'=> Auth::id(),
        ]);

        $item->decrement('stock', 1);

        return back()->with('success', 'Barang berhasil dikeluarkan.');
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
