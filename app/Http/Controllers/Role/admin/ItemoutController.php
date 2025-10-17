<?php
namespace App\Http\Controllers\Role\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Item_out;
use App\Models\Item;
use App\Models\Guest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ItemoutController extends Controller
{

    /**
     * Tampilkan daftar item keluar (pegawai & tamu)
     */
    public function index()
    {
        // 🔹 1. Barang keluar dari PEGAWAI (Cart)
        $approvedItems = Cart::with(['cartItems' => function($q) {
                $q->where('status', 'approved');
            }, 'cartItems.item', 'user'])
            ->whereIn('status', ['approved', 'approved_partially'])
            ->latest()
            ->get()
            ->filter(function ($cart) {
                // hanya tampilkan yang BELUM semua discan
                return !$cart->cartItems->every(fn($i) => $i->scanned_at);
            });

        // 🔹 2. Barang keluar dari TAMU
        $guestItemOuts = Guest::with(['guestCart.guestCartItems.item'])
            ->whereHas('guestCart.guestCartItems')
            ->orderByDesc('created_at')
            ->paginate(10);

       return view('role.admin.itemout', [
            'approvedItems' => new LengthAwarePaginator(
                $approvedItems->forPage(request('page', 1), 10), // ambil 10 data per halaman
                $approvedItems->count(),                        // total item
                10,                                             // per halaman
                request('page', 1),                             // halaman saat ini
                ['path' => request()->url()]                    // agar link pagination tetap benar
            ),
            'guestItemOuts' => $guestItemOuts,
        ]);
    }

    /**
     * Scan item berdasarkan barcode.
     */
   public function scan(Request $request, $cartId)
    {
        $request->validate([
            'barcode' => 'required|string|max:255',
        ]);

        $cart = Cart::with('cartItems.item')->findOrFail($cartId);
        $barcode = trim($request->barcode);

        // Cari item berdasarkan barcode
        $cartItem = $cart->cartItems->first(fn($ci) => optional($ci->item)->code === $barcode);

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'status'  => 'invalid',
                'message' => '❌ Kode / QR tidak sesuai dengan barang dalam permintaan ini.',
            ], 422);
        }

        if ($cartItem->scanned_at) {
            return response()->json([
                'success' => false,
                'status'  => 'duplicate',
                'message' => '⚠️ Barang ini sudah pernah dipindai.',
            ], 409);
        }

        // Tandai item sudah discan
        $cartItem->update(['scanned_at' => now()]);

        // ✅ Cek apakah SEMUA item sudah discan
        $allScanned = $cart->cartItems->every(fn($ci) => $ci->scanned_at !== null);

        // Jika semua sudah discan → langsung proses release otomatis
        if ($allScanned) {
            DB::beginTransaction();
            try {
                foreach ($cart->cartItems as $ci) {
                    // Buat entri di item_outs
                    Item_out::create([
                        'cart_id'   => $cart->id,
                        'item_id'   => $ci->item_id,
                        'quantity'  => $ci->quantity,
                        'unit_id'   => $ci->item->unit_id,
                        'released_at' => now(),
                        'approved_by' => Auth::id(),
                    ]);
                    // Kurangi stok item
                    $ci->item->decrement('stock', $ci->quantity);
                }

                $cart->update(['picked_up_at' => now()]);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Auto-release gagal: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'status'  => 'valid',
            'message' => '✅ Barang berhasil discan' . ($allScanned ? ' dan semua item sudah dikeluarkan.' : '.'),
            'all_scanned' => $allScanned,
            'item' => [
                'id'        => $cartItem->item->id,
                'name'      => $cartItem->item->name,
                'code'      => $cartItem->item->code,
                'quantity'  => $cartItem->quantity,
                'scanned_at'=> $cartItem->scanned_at,
            ],
        ]);
    }

    /**
     * Release barang keluar.
     */
    public function release(Request $request, $cartId)
    {

        Log::info('DEBUG release payload', $request->all());

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
                $item = Item::where('id', $scannedItem['id'])->lockForUpdate()->first();
                if (!$item) continue;

                $qty = (int) $scannedItem['quantity'];

                if ($item->stock < $qty) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Stok tidak cukup untuk item {$item->name} (tersisa: {$item->stock})."
                    ], 422);
                }
                $itemOut = new Item_out();
                $itemOut->cart_id = $cart->id;
                $itemOut->item_id = $item->id;
                $itemOut->quantity = $qty;
                $itemOut->unit_id = $item->unit_id;
                $itemOut->released_at = now();
                $itemOut->approved_by = Auth::id();
                $itemOut->save();

                $item->decrement('stock', $qty);

                $cartItem = CartItem::where('cart_id', $cart->id)
                    ->where('item_id', $item->id)
                    ->first();

                if ($cartItem) {
                    $cartItem->update(['scanned_at' => now()]);
                }
            }

            $cart->update(['picked_up_at' => now()]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '✅ Semua barang berhasil dikeluarkan.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Release error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses release.'
            ], 500);
        }
    }

    /**
     * Cek apakah semua item sudah discan.
     */
    public function checkAllScanned($cartId)
    {
        $cart = Cart::with('cartItems')->findOrFail($cartId);
        $allScanned = $cart->cartItems->every(fn($i) => $i->scanned_at);

        return response()->json(['all_scanned' => $allScanned]);
    }

    /**
     * Tampilkan struk langsung di browser.
     */
    public function struk($id)
    {
        $cart = Cart::with(['user', 'cartItems.item'])->findOrFail($id);
        $itemOut = Item_out::where('cart_id', $cart->id)->get();

        $pdf = Pdf::loadView('role.admin.export.struk', compact('cart', 'itemOut'));

        return $pdf->stream('struk-pemesanan-' . $cart->id . '.pdf');
    }

    /**
     * Download struk dalam bentuk PDF.
     */
    public function generateStruk($cartId)
    {
        $cart = Cart::with(['cartItems.item', 'user'])->findOrFail($cartId);
        $itemOut = Item_out::where('cart_id', $cartId)->get();

        $pdf = Pdf::loadView('role.admin.export.struk', compact('cart', 'itemOut'));

        return $pdf->download('struk_cart_' . $cart->id . '.pdf');
    }

    // Placeholder method bawaan resource controller
    public function create() {}
    public function store(Request $request) {}
    public function show(string $id) {}
    public function edit(string $id) {}
    public function update(Request $request, string $id) {}
    public function destroy(string $id) {}
}