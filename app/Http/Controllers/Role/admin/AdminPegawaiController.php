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
                'status'     => 'scanned',
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
        $itemId = $request->query('item_id');

        $cart = Cart::with(['cartItems' => function ($q) use ($itemId) {
                    $q->where('status', 'scanned') // hanya tampilkan item yang sudah discan
                    ->when($itemId, fn($query) => $query->where('item_id', $itemId))
                    ->orderByDesc('created_at')
                    ->with('item');
                }])
                ->where('user_id', $pegawai->id)
                ->whereIn('status', ['active', 'pending', 'approved'])
                ->latest()
                ->first();

        $items = [];

        if ($cart && $cart->cartItems->count() > 0) {
            foreach ($cart->cartItems as $cartItem) {
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
     * Tambahkan item ke cart pegawai (otomatis buat cart kalau belum ada)
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'pegawai_id' => 'required|exists:users,id',
            'item_id'    => 'required|exists:items,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $pegawaiId = $request->pegawai_id;
            $itemId = $request->item_id;
            $quantity = $request->quantity;

            // 🔹 1. Buat cart aktif jika belum ada
            $cart = Cart::firstOrCreate(
                ['user_id' => $pegawaiId, 'status' => 'active'],
                ['created_at' => now(), 'updated_at' => now()]
            );

            // 🔹 2. Tambahkan / update item dalam cart_items
            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('item_id', $itemId)
                ->first();

            if ($cartItem) {
                $cartItem->increment('quantity', $quantity);
            } else {
                CartItem::create([
                    'cart_id'  => $cart->id,
                    'item_id'  => $itemId,
                    'quantity' => $quantity,
                    'status'   => 'pending',
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Item berhasil ditambahkan ke keranjang.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan item: ' . $e->getMessage(),
            ], 500);
        }
    }



    /**
 * Simpan seluruh isi cart ke item_outs
 */
    public function saveCartToItemOut($pegawaiId)
    {
        DB::beginTransaction();
        try {
            $pegawai = User::findOrFail($pegawaiId);

            // 🔹 Cek apakah ada cart active
            $cart = Cart::where('user_id', $pegawai->id)
                    ->whereIn('status', ['active', 'approved'])
                    ->first();

            // 🔹 Jika belum ada, buat cart langsung dengan status approved
            if (! $cart) {
                $cart = Cart::create([
                    'user_id' => $pegawai->id,
                    'status'  => 'approved',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                // Jika sudah ada, ubah status ke approved
                $cart->update(['status' => 'approved']);
            }

            // 🔹 Ambil semua cart items yang dimiliki pegawai (bisa pending/baru)
            $cartItems = CartItem::where('cart_id', $cart->id)->get();

            if ($cartItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Keranjang masih kosong.',
                ]);
            }

            foreach ($cartItems as $cartItem) {
                $item = $cartItem->item;

                if ($item->stock < $cartItem->quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => "Stok untuk {$item->name} tidak mencukupi.",
                    ]);
                }

                // 🔹 Kurangi stok barang
                $item->decrement('stock', $cartItem->quantity);

                // 🔹 Buat record di item_out
                Item_out::create([
                    'item_id'     => $item->id,
                    'cart_id'     => $cart->id,
                    'unit_id'     => $item->unit_id ?? null,
                    'quantity'    => $cartItem->quantity,
                    'released_at' => now(),
                    'approved_by' => Auth::id(),
                ]);

                // 🔹 Update status cart_item langsung approved
                $cartItem->update([
                    'status' => 'approved',
                    'rejection_reason' => null,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Barang berhasil disimpan ke Item Out (langsung approved)!',
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
