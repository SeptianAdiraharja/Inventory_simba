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

class ItemoutController extends Controller
{

    /**
     * Tampilkan daftar item keluar (pegawai & tamu)
     */
    public function index(Request $request)
    {
        $search = $request->get('q');

        // 🔹 1. Barang keluar dari PEGAWAI (Cart)
        $approvedItems = Cart::with(['cartItems' => function ($q) {
                $q->whereNull('scanned_at')
                ->where(function ($query) {
                    $query->where('status', 'approved')
                            ->orWhereNull('status');
                })
                ->with('item');
            }, 'user'])
            ->whereIn('status', ['approved', 'approved_partially'])
            ->whereHas('cartItems', function ($q) {
                $q->whereNull('scanned_at')
                ->where(function ($query) {
                    $query->where('status', 'approved')
                            ->orWhereNull('status');
                });
            })
            // 🔍 Filter pencarian
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    // Cari berdasarkan nama atau email user
                    $q->whereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                    })
                    // Atau berdasarkan nama/kode item di cartItems
                    ->orWhereHas('cartItems.item', function ($itemQuery) use ($search) {
                        $itemQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                    });
                });
            })
            ->latest()
            ->get();

        // 🔹 2. Barang keluar dari TAMU
        $guestItemOuts = Guest::with(['guestCart.guestCartItems.item'])
            ->whereHas('guestCart.guestCartItems')
            ->orderByDesc('created_at')
            ->paginate(10);

        // 🔹 3. Pagination manual untuk hasil pegawai
        $approvedItemsPaginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $approvedItems->forPage($request->get('page', 1), 10),
            $approvedItems->count(),
            10,
            $request->get('page', 1),
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('role.admin.itemout', [
            'approvedItems' => $approvedItemsPaginated,
            'guestItemOuts' => $guestItemOuts,
            'search' => $search,
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
            ], 200);
        }

        // Kalau item ini sudah discan di database (hasil release sebelumnya)
        if ($cartItem->scanned_at) {
            return response()->json([
                'success' => false,
                'status'  => 'duplicate',
                'message' => '⚠️ Barang ini sudah pernah dipindai sebelumnya.',
            ], 200);
        }

        // 🚫 Jangan update DB di sini — hanya kirim respon validasi
        return response()->json([
            'success' => true,
            'status'  => 'valid',
            'message' => "✅ Barang {$cartItem->item->name} cocok dengan daftar.",
            'item' => [
                'id'        => $cartItem->item->id,
                'name'      => $cartItem->item->name,
                'code'      => $cartItem->item->code,
                'quantity'  => $cartItem->quantity,
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
