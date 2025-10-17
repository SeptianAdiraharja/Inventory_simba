<?php

namespace App\Http\Controllers\Role\Admin;

use App\Http\Controllers\Controller;
use App\Models\{
    Cart,
    CartItem,
    Item,
    Item_out,
    User
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class AdminPegawaiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         // Ambil data user yang role-nya 'pegawai'
        $pegawai = User::where('role', 'pegawai')->get();

        return view('role.admin.pegawai', compact('pegawai'));
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
    public function showProduk($id)
    {
        // Ambil data pegawai berdasarkan ID
        $pegawai = User::findOrFail($id);

        // Ambil semua produk (atau filter jika mau khusus)
        $items = Item::with('category')->get();

        // Tampilkan ke view produk pegawai
        return view('role.admin.produk_pegawai', compact('pegawai', 'items'));
    }


    public function scan(Request $request, $pegawaiId)
    {
        try {
            $request->validate([
                'item_id'  => 'required|exists:items,id',
                'barcode'  => 'required|string',
                'quantity' => 'required|integer|min:1',
            ]);

            $pegawai = User::findOrFail($pegawaiId);

            // Ambil item
            $item = Item::findOrFail($request->item_id);

            // Normalisasi input & nilai di DB untuk perbandingan
            $inputCode = trim((string)$request->barcode);
            $dbCode1 = trim((string) ($item->code ?? ''));
            $dbCode2 = trim((string) ($item->barcode ?? '')); // jika ada kolom barcode

            // Bandingkan tolerant (case-insensitive)
            $matches = false;
            if ($dbCode1 !== '' && strcasecmp($dbCode1, $inputCode) === 0) $matches = true;
            if ($dbCode2 !== '' && strcasecmp($dbCode2, $inputCode) === 0) $matches = true;

            if (! $matches) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode barang tidak sesuai dengan data di database!',
                ], 422);
            }

            // pastikan pegawai punya cart
            $cart = Cart::firstOrCreate(['user_id' => $pegawai->id]);

            // buat item baru di cart
            $cartItem = CartItem::create([
                'cart_id'    => $cart->id,
                'item_id'    => $request->item_id,
                'quantity'   => $request->quantity,
                'scanned_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Barang berhasil ditambahkan ke keranjang!',
                'data' => [
                    'cart_item_id' => $cartItem->id,
                    'cart_id'      => $cart->id,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $ve->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function showCart($pegawaiId, Request $request)
    {
        $pegawai = User::findOrFail($pegawaiId);
        $itemId = $request->query('item_id'); // item_id opsional dari frontend

        $cart = Cart::with(['cartItems.item'])
                    ->where('user_id', $pegawai->id)
                    ->first();

        $items = [];

        if ($cart && $cart->cartItems->count() > 0) {
            $cartItems = $cart->cartItems()
                              ->when($itemId, fn($q) => $q->where('item_id', $itemId))
                              ->orderByDesc('created_at')
                              ->get();

            foreach ($cartItems as $cartItem) {
                $items[] = [
                    'cart_item_id' => $cartItem->id,
                    'item_id'      => $cartItem->item_id,
                    'quantity'     => $cartItem->quantity,
                    'status'       => $cartItem->status,
                    'scanned_at'   => $cartItem->scanned_at,
                    'item' => $cartItem->item ? [
                        'id'    => $cartItem->item->id,
                        'name'  => $cartItem->item->name,
                        'code'  => $cartItem->item->code ?? null,
                        'image' => $cartItem->item->image ?? null,
                        'unit_id' => $cartItem->item->unit_id ?? null,
                    ] : null,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'cart_id' => $cart ? $cart->id : null,
                'items'   => $items,
            ],
        ]);
    }


    /**
 * Simpan seluruh isi cart ke item_outs
 */
     public function saveCartToItemOut($pegawaiId)
    {
        DB::beginTransaction();
        try {
            $pegawai = User::findOrFail($pegawaiId);
            $cart = Cart::with('cartItems.item')->where('user_id', $pegawai->id)->first();

            if (! $cart || $cart->cartItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Keranjang masih kosong.',
                ]);
            }

            foreach ($cart->cartItems as $cartItem) {
                $item = $cartItem->item;

                // pastikan stok cukup
                if ($item->stock < $cartItem->quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => "Stok untuk {$item->name} tidak mencukupi.",
                    ]);
                }

                // kurangi stok di DB
                $item->decrement('stock', $cartItem->quantity);

                // simpan ke tabel item_outs — hanya field yang ada pada tabel
                Item_out::create([
                    'item_id'     => $item->id,
                    'cart_id'     => $cart->id,                // isi cart_id
                    'unit_id'     => $item->unit_id ?? null,   // isi unit_id dari item
                    'quantity'    => $cartItem->quantity,
                    'released_at' => now(),
                    'approved_by' => Auth::id(),               // admin yang login
                ]);
            }

            // kosongkan cart setelah disimpan
            $cart->cartItems()->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan ke Item Out!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Hapus CartItem
     */
    public function destroyCartItem($pegawaiId, $cartItemId)
    {
        $pegawai = User::findOrFail($pegawaiId);

        $cart = Cart::where('user_id', $pegawai->id)->first();
        if (! $cart) {
            return response()->json(['success' => false, 'message' => 'Cart tidak ditemukan'], 404);
        }

        $cartItem = CartItem::where('id', $cartItemId)->where('cart_id', $cart->id)->first();
        if (! $cartItem) {
            return response()->json(['success' => false, 'message' => 'Item tidak ditemukan di cart'], 404);
        }

        $cartItem->delete();

        return response()->json(['success' => true, 'message' => 'Item dihapus']);
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
