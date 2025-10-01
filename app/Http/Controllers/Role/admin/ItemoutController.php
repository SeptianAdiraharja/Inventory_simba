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
        // Ambil cart milik user dengan pagination
        $approvedItems = Cart::with(['cartItems.item', 'user'])
            ->where('status', 'approved')
            ->latest()
            ->paginate(10) // 🔑 tampil 10 data per halaman
            ->through(function ($cart) {
                $cart->all_scanned = $cart->cartItems->every(fn($i) => $i->scanned_at);
                return $cart;
            });

        // Guest requests (kalau juga mau dipaginasi, sama caranya)
        $guestRequests = Item_out::select('item_outs.*', 'guests.name as guest_name')
            ->leftJoin('guests', 'item_outs.guest_id', '=', 'guests.id')
            ->with('item')
            ->whereNull('item_outs.cart_id')
            ->latest()
            ->paginate(10);

        return view('role.admin.itemout', compact('approvedItems', 'guestRequests'));
    }



   // Scan item berdasarkan barcode
    public function scan(Request $request, $cartId)
    {
        $request->validate([
            'barcode' => 'required|string|max:255',
        ]);

        $cart = Cart::with('cartItems.item')->findOrFail($cartId);
        $barcode = $request->barcode;

        $cartItem = $cart->cartItems->firstWhere('item.code', $barcode);

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => '❌ Barcode tidak ditemukan pada cart ini.',
            ]);
        }

        // update status scanned_at
        $cartItem->update(['scanned_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => '✅ Barang berhasil discan.',
            'item' => [
                'id' => $cartItem->item->id,
                'name' => $cartItem->item->name,
                'code' => $cartItem->item->code,
                'quantity' => $cartItem->quantity,
                'scanned_at' => $cartItem->scanned_at,
            ],
        ]);
    }


    // Release barang keluar
    public function release(Request $request, $cartId)
    {
        $cart = Cart::with('cartItems.item')->findOrFail($cartId);
        $items = $request->input('items', []);

        if (empty($items)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada item yang discan.']);
        }

        foreach ($items as $scannedItem) {
            $item = Item::find($scannedItem['id']);
            if (!$item) continue;

            // insert ke item_outs
            Item_out::create([
                'cart_id'     => $cart->id,
                'item_id'     => $item->id,
                'quantity'    => $scannedItem['quantity'],
                'released_at' => now(),
                'approved_by' => Auth::id(),
            ]);

            // update stok
            $item->decrement('stock', $scannedItem['quantity']);

            // 🔑 update scanned_at di cart_items
            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('item_id', $item->id)
                ->first();

            if ($cartItem) {
                $cartItem->update(['scanned_at' => now()]);
            }
        }

        $cart->update(['picked_up_at' => now()]);

        return response()->json(['success' => true, 'message' => '✅ Semua barang berhasil dikeluarkan.']);
    }

    public function checkAllScanned($cartId)
    {
        $cart = Cart::with('cartItems')->findOrFail($cartId);
        $allScanned = $cart->cartItems->every(fn($i) => $i->scanned_at);
        return response()->json(['all_scanned' => $allScanned]);
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

        $guestCart = Guest_carts::firstOrCreate(
            [
                'session_id' => session()->getId(),
                'guest_id'   => $request->guest_id,
            ]
        );


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
