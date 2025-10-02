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
use Illuminate\Support\Facades\Log;


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

        // cari cartItem dengan kode barcode (lebih aman menggunakan callback)
        $cartItem = $cart->cartItems->first(function ($ci) use ($barcode) {
            return optional($ci->item)->code === $barcode;
        });

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => '❌ Barcode tidak ditemukan pada cart ini.',
            ], 404);
        }

        // update scanned_at
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
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::with('cartItems')->findOrFail($cartId);
        $items = $request->input('items', []);

        DB::beginTransaction();
        try {
            foreach ($items as $scannedItem) {
                // lock record agar stok konsisten
                $item = Item::where('id', $scannedItem['id'])->lockForUpdate()->first();
                if (!$item) continue;

                $qty = (int) $scannedItem['quantity'];

                // cek stok
                if ($item->stock < $qty) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Stok tidak cukup untuk item {$item->name} (tersisa: {$item->stock})."
                    ], 422);
                }

                // buat record keluaran barang (hindari mass assignment issue dengan assign lalu save)
                $itemOut = new Item_out();
                $itemOut->cart_id = $cart->id;
                $itemOut->item_id = $item->id;
                $itemOut->quantity = $qty;
                $itemOut->released_at = now();
                $itemOut->approved_by = Auth::id();
                $itemOut->save();

                // kurangi stok
                $item->decrement('stock', $qty);

                // set scanned_at pada cart_items
                $cartItem = CartItem::where('cart_id', $cart->id)
                    ->where('item_id', $item->id)
                    ->first();

                if ($cartItem) {
                    $cartItem->update(['scanned_at' => now()]);
                }
            }

            // set picked_up_at
            $cart->update(['picked_up_at' => now()]);

            DB::commit();

            return response()->json(['success' => true, 'message' => '✅ Semua barang berhasil dikeluarkan.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Release error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat memproses release.'], 500);
        }
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
