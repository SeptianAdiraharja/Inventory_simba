<?php

namespace App\Http\Controllers\Role\Admin;

use App\Http\Controllers\Controller;
use App\Models\{
    Cart,
    CartItem,
    Item,
    Item_out,
    User,
    Category
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class AdminPegawaiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = $request->input('q');
        // Ambil data user yang role-nya 'pegawai'
        $pegawai = User::where('role', 'pegawai')
            ->when($query, function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('role.admin.pegawai', compact('pegawai'));
    }

    /* =======================================================
       🔍 SEARCH – Method untuk pencarian pegawai
    ======================================================== *

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
    public function showProduk(Request $request, $id)
    {
        // Ambil data pegawai berdasarkan ID
        $pegawai = User::findOrFail($id);

        // 🔥 AMBIL KATEGORI YANG DI-ASSIGN KE PEGAWAI
        $assignedCategories = $pegawai->categories;

        // Jika pegawai memiliki kategori yang di-assign, gunakan hanya kategori tersebut
        // Jika tidak, tampilkan semua kategori
        $categories = $assignedCategories->isNotEmpty() ? $assignedCategories : Category::all();

        // Query untuk produk
        $itemsQuery = Item::with('category');

        // 🔥 FILTER BERDASARKAN KATEGORI YANG DI-ASSIGN KE PEGAWAI
        if ($assignedCategories->isNotEmpty()) {
            $itemsQuery->whereIn('category_id', $assignedCategories->pluck('id'));
        }

        // 🔥 PERBAIKAN: Filter berdasarkan pencarian (jika ada)
        if ($request->has('q') && !empty($request->q)) {
            $searchTerm = $request->q;
            $itemsQuery->where(function($query) use ($searchTerm) {
                $query->where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('code', 'LIKE', "%{$searchTerm}%")
                    ->orWhereHas('category', function($categoryQuery) use ($searchTerm) {
                        $categoryQuery->where('name', 'LIKE', "%{$searchTerm}%");
                    });
            });
        }

        // 🔥 PERBAIKAN: Filter berdasarkan kategori (jika ada)
        if ($request->has('kategori') && $request->kategori != 'none') {
            $itemsQuery->whereHas('category', function($query) use ($request) {
                $query->where('name', $request->kategori);
            });
        }

        // 🔥 PERBAIKAN: LOGIKA SORTING YANG LEBIH ROBUST
        $sort = $request->get('sort', 'stok_terbanyak');

        switch ($sort) {
            case 'stok_menipis':
                $itemsQuery->where('stock', '>', 0)
                    ->orderBy('stock', 'asc')
                    ->orderBy('name', 'asc');
                break;

            case 'paling_laris':
                $itemsQuery->withCount(['itemOuts as total_keluar' => function($query) {
                    $query->select(DB::raw('COALESCE(SUM(quantity), 0)'));
                }])->orderBy('total_keluar', 'desc');
                break;

            case 'terbaru':
                $itemsQuery->orderBy('created_at', 'desc');
                break;

            case 'terlama':
                $itemsQuery->orderBy('created_at', 'asc');
                break;

            case 'a_z':
                $itemsQuery->orderBy('name', 'asc');
                break;

            case 'z_a':
                $itemsQuery->orderBy('name', 'desc');
                break;

            case 'stok_terbanyak':
            default:
                $itemsQuery->orderBy('stock', 'desc');
                break;
        }

        // Tambahkan order by created_at sebagai secondary sort untuk konsistensi
        if (!in_array($sort, ['terbaru', 'terlama', 'stok_menipis'])) {
            $itemsQuery->orderBy('created_at', 'desc');
        }

        // Pagination dengan menyimpan parameter query
        $items = $itemsQuery->paginate(12)->appends($request->except('page'));

        // 🔥 PERBAIKAN: Pindahkan pengecekan setelah $items didefinisikan
        if ($request->has('q') && $items->isEmpty()) {
            session()->flash('search_warning',
                $request->has('kategori') && $request->kategori != 'none'
                ? "Barang dengan nama '{$request->q}' dan kategori '{$request->kategori}' tidak ditemukan."
                : "Barang dengan nama '{$request->q}' tidak ditemukan."
            );
        } elseif ($request->has('kategori') && $request->kategori != 'none' && $items->isEmpty()) {
            session()->flash('search_warning', "Tidak ada barang dalam kategori '{$request->kategori}'.");
        }

        if ($items->isEmpty() && $request->has('sort') && $request->sort == 'stok_menipis') {
            session()->flash('info', 'Tidak ada barang dengan stok menipis saat ini.');
        }

        // Tampilkan ke view produk pegawai
        return view('role.admin.produk_pegawai', compact(
            'pegawai',
            'items',
            'categories',
            'assignedCategories'
        ));
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

            // Cari cart dengan status active terlebih dahulu
            $cart = Cart::where('user_id', $pegawai->id)
                ->where('status', 'active')
                ->first();

            // Jika tidak ada cart active, buat baru
            if (! $cart) {
                $cart = Cart::create([
                    'user_id' => $pegawai->id,
                    'status'  => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

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

        // Cari cart dengan status active atau yang masih memiliki item scanned
        $cart = Cart::with(['cartItems' => function ($q) use ($itemId) {
                    $q->where('status', 'scanned') // Tampilkan item dengan status scanned
                    ->when($itemId, fn($query) => $query->where('item_id', $itemId))
                    ->orderByDesc('created_at')
                    ->with('item');
                }])
                ->where('user_id', $pegawai->id)
                ->where('status', 'active') // Hanya cart dengan status active
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

        // HITUNG PERMINTAAN MINGGU INI HANYA DARI CART
        $weeklyCartCount = Cart::where('user_id', $pegawaiId)
            ->whereIn('status', ['active', 'pending', 'approved'])
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        // Hitung juga item_outs yang berasal dari cart (untuk permintaan yang sudah disetujui)
        $weeklyItemOutCount = Item_out::whereHas('cart', function ($query) use ($pegawaiId) {
                $query->where('user_id', $pegawaiId);
            })
            ->whereBetween('released_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        // Gabungkan kedua sumber (cart aktif + item_outs yang sudah approved)
        $totalWeekly = $weeklyCartCount + $weeklyItemOutCount;

        $hasReachedLimit = $totalWeekly >= 5;

        return response()->json([
            'success' => true,
            'data' => [
                'cart_id' => $cart ? $cart->id : null,
                'items'   => $items,
                'weekly_request_count' => $totalWeekly,
                'has_reached_limit' => $hasReachedLimit,
                'limit_message' => $hasReachedLimit
                    ? 'Pegawai ini sudah mencapai batas peminjaman mingguan (5 permintaan).'
                    : null,
                'debug' => [
                    'cart_count' => $weeklyCartCount,
                    'item_out_count' => $weeklyItemOutCount,
                    'cart_found' => $cart ? true : false,
                    'cart_id' => $cart ? $cart->id : null,
                    'items_count' => count($items)
                ]
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

            // Cari cart dengan status active terlebih dahulu
            $cart = Cart::where('user_id', $pegawaiId)
                ->where('status', 'active')
                ->first();

            // Jika tidak ada cart active, buat baru
            if (! $cart) {
                $cart = Cart::create([
                    'user_id' => $pegawaiId,
                    'status'  => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Tambahkan / update item dalam cart_items
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

            // Cari cart dengan status active terlebih dahulu
            $cart = Cart::where('user_id', $pegawai->id)
                    ->where('status', 'active')
                    ->first();

            if (! $cart) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada keranjang aktif yang ditemukan.',
                ]);
            }

            // Ambil semua cart items yang dimiliki pegawai
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

                // Kurangi stok barang
                $item->decrement('stock', $cartItem->quantity);

                // Buat record di item_out
                Item_out::create([
                    'item_id'     => $item->id,
                    'cart_id'     => $cart->id,
                    'unit_id'     => $item->unit_id ?? null,
                    'quantity'    => $cartItem->quantity,
                    'released_at' => now(),
                    'approved_by' => Auth::id(),
                ]);

                // Update status cart_item langsung approved
                $cartItem->update([
                    'status' => 'approved',
                    'rejection_reason' => null,
                ]);
            }

            // Update status cart menjadi approved setelah semua item diproses
            $cart->update(['status' => 'approved']);

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

        // Cari cart dengan status active
        $cart = Cart::where('user_id', $pegawai->id)
            ->where('status', 'active')
            ->first();

        if (! $cart) {
            return response()->json(['success' => false, 'message' => 'Cart aktif tidak ditemukan'], 404);
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
