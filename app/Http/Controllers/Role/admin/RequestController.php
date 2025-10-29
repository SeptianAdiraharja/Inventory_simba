<?php

namespace App\Http\Controllers\Role\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Cart, CartItem, Notification};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Log};
use Exception;

class RequestController extends Controller
{
    /* =======================================================
       🧭 INDEX – Daftar Request dengan Filter & Auto Cleanup
    ======================================================== */
    public function index(Request $request)
    {
        // =========================================================
        // 🧹 Hapus otomatis cart_items dari cart yang sudah final > 3 hari
        // =========================================================
        DB::table('cart_items')
            ->whereIn('cart_id', function ($q) {
                $q->select('id')
                    ->from('carts')
                    ->whereIn('status', ['approved', 'rejected'])
                    ->where('updated_at', '<', now()->subDays(3));
            })
            ->delete();

        // =========================================================
        // 🔍 Filter cart
        // =========================================================
        $status = $request->get('status');
        $recent = now()->subDays(3);

        $requests = DB::table('carts')
            ->join('users', 'carts.user_id', '=', 'users.id')
            ->leftJoin('cart_items', 'carts.id', '=', 'cart_items.cart_id')
            ->select(
                'carts.id as cart_id',
                'users.name',
                'users.email',
                'users.role',
                'carts.status',
                'carts.created_at',
                'carts.updated_at',
                DB::raw('COALESCE(SUM(cart_items.quantity), 0) as total_quantity')
            )
            // =========================================================
            // 🚫 Hanya tampilkan cart yang masih aktif atau final < 3 hari
            // =========================================================
            ->where(function ($query) use ($recent) {
                $query->where('carts.status', 'pending')
                    ->orWhere('carts.status', 'approved_partially')
                    ->orWhere(function ($q) use ($recent) {
                        $q->whereIn('carts.status', ['approved', 'rejected'])
                        ->where('carts.updated_at', '>=', $recent);
                    });
            })
            // =========================================================
            // 🎯 Filter tambahan berdasarkan parameter "status" dari request
            // =========================================================
            ->when($status, function ($query) use ($status, $recent) {
                return match ($status) {
                    'pending' => $query->where('carts.status', 'pending'),
                    'approved' => $query->where('carts.status', 'approved')->where('carts.updated_at', '>=', $recent),
                    'approved_partially' => $query->where('carts.status', 'approved_partially'),
                    'rejected' => $query->where('carts.status', 'rejected')->where('carts.updated_at', '>=', $recent),
                    default => $query
                };
            })
            ->groupBy(
                'carts.id', 'users.name', 'users.email', 'users.role',
                'carts.status', 'carts.created_at', 'carts.updated_at'
            )
            ->orderByDesc('carts.created_at')
            ->paginate(10);

        return view('role.admin.request', compact('requests', 'status'));
    }

    /* =======================================================
       📦 SHOW – Detail Cart via AJAX
    ======================================================== */
    public function show(string $id)
    {
        $cart = DB::table('carts')
            ->join('users', 'carts.user_id', '=', 'users.id')
            ->select('carts.*', 'users.name as user_name')
            ->where('carts.id', $id)
            ->first();

        if (!$cart) {
            return response()->json(['error' => 'Permintaan tidak ditemukan'], 404);
        }

        $cartItems = DB::table('cart_items')
            ->join('items', 'cart_items.item_id', '=', 'items.id')
            ->select(
                'cart_items.id', 'cart_items.quantity', 'cart_items.status',
                'cart_items.rejection_reason', 'items.name as item_name', 'items.code as item_code'
            )
            ->where('cart_items.cart_id', $id)
            ->get();

        $total = $cartItems->count();
        $processed = $cartItems->whereIn('status', ['approved', 'rejected'])->count();

        $scan_status = $processed === $total
            ? 'Selesai'
            : ($processed > 0 ? 'Sebagian' : 'Belum diproses');

        return view('role.admin.partials.cart-detail', compact('cart', 'cartItems', 'scan_status'));
    }

    /* =======================================================
       ✅ UPDATE – Approve/Reject Semua Item
    ======================================================== */
    public function update(Request $request, string $id)
    {
        Log::info('DEBUG UPDATE STATUS - REQUEST DITERIMA', [
            'input' => $request->all(),
            'status' => $request->input('status'),
            'cart_id' => $id,
        ]);

        $newStatus = $request->input('status');

        if (!in_array($newStatus, ['approved', 'rejected'])) {
            Log::warning('DEBUG UPDATE STATUS - STATUS TIDAK VALID', [
                'status' => $newStatus,
                'cart_id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Status tidak valid.'
            ], 400);
        }

        try {
            DB::transaction(function () use ($id, $newStatus) {

                // 🟢 Update semua item di cart_items
                DB::table('cart_items')
                    ->where('cart_id', $id)
                    ->update([
                        'status' => $newStatus,
                        'rejection_reason' => $newStatus === 'rejected'
                            ? 'Ditolak secara keseluruhan.'
                            : null,
                        'updated_at' => now(),
                    ]);

                // 🟢 Update status di tabel carts
                DB::table('carts')
                    ->where('id', $id)
                    ->update([
                        'status' => $newStatus,
                        'updated_at' => now(),
                    ]);

                // 🔹 Jika semua ditolak → kembalikan stok semua item
                if ($newStatus === 'rejected') {
                    $cartItems = DB::table('cart_items')->where('cart_id', $id)->get();
                    foreach ($cartItems as $item) {
                        DB::table('items')
                            ->where('id', $item->item_id)
                            ->increment('stock', $item->quantity);
                    }
                }
            });

            // 🟢 Kirim notifikasi ke user
            $cart = DB::table('carts')->find($id);
            if ($cart) {
                Notification::create([
                    'user_id' => $cart->user_id,
                    'title' => 'Request ' . ucfirst($newStatus),
                    'message' => 'Permintaan barang kamu telah ' . $newStatus . ' secara keseluruhan.',
                    'status' => 'unread',
                ]);
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'status' => $newStatus,
                    'message' => "Semua item berhasil {$newStatus}.",
                ]);
            }

            return back()->with('success', "Semua item berhasil {$newStatus}.");

        } catch (\Throwable $e) {
            Log::error('DEBUG UPDATE STATUS - ERROR', [
                'error' => $e->getMessage(),
                'cart_id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function bulkUpdate(Request $request, $cartId)
    {
        $status = $request->input('status');
        $reason = $request->input('reason');
        $changes = $request->input('changes');

        if (!$status && !$changes) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada status dikirim.'
            ]);
        }

        DB::beginTransaction();
        try {
            // CASE 1: Approve / Reject Semua
            if ($status) {
                DB::table('cart_items')
                    ->where('cart_id', $cartId)
                    ->update([
                        'status' => $status,
                        'rejection_reason' => $status === 'rejected'
                            ? ($reason ?: 'Ditolak oleh admin.')
                            : null,
                        'updated_at' => now(),
                    ]);

                // 🔹 Jika semua ditolak → kembalikan stok semua item
                if ($status === 'rejected') {
                    $cartItems = DB::table('cart_items')->where('cart_id', $cartId)->get();
                    foreach ($cartItems as $item) {
                        DB::table('items')
                            ->where('id', $item->item_id)
                            ->increment('stock', $item->quantity);
                    }
                }
            }

            // CASE 2: Perubahan per item (changes)
            if (is_array($changes) || is_object($changes)) {
                foreach ($changes as $itemId => $change) {
                    DB::table('cart_items')
                        ->where('id', $itemId)
                        ->where('cart_id', $cartId)
                        ->update([
                            'status' => $change['status'],
                            'rejection_reason' => $change['status'] === 'rejected'
                                ? ($change['reason'] ?? 'Ditolak oleh admin.')
                                : null,
                            'updated_at' => now(),
                        ]);

                    // 🔹 Jika item ditolak → kembalikan stok item tersebut
                    if ($change['status'] === 'rejected') {
                        $cartItem = DB::table('cart_items')->where('id', $itemId)->first();
                        if ($cartItem) {
                            DB::table('items')
                                ->where('id', $cartItem->item_id)
                                ->increment('stock', $cartItem->quantity);
                        }
                    }
                }
            }

            // 🧮 Hitung ulang status cart
            $cartItems = DB::table('cart_items')->where('cart_id', $cartId)->get();
            $approved = $cartItems->where('status', 'approved')->count();
            $rejected = $cartItems->where('status', 'rejected')->count();
            $total = $cartItems->count();

            $cartStatus = 'pending';
            if ($approved === $total) $cartStatus = 'approved';
            elseif ($rejected === $total) $cartStatus = 'rejected';
            elseif ($approved > 0 || $rejected > 0) $cartStatus = 'approved_partially';

            DB::table('carts')
                ->where('id', $cartId)
                ->update([
                    'status' => $cartStatus,
                    'updated_at' => now(),
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Perubahan berhasil disimpan.',
                'cart_status' => $cartStatus,
                'reason' => $reason,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =======================
    // ✅ APPROVE ITEM
    // =======================
    public function approveItem($cartItemId)
    {
        return $this->updateItemStatus($cartItemId, 'approved');
    }

    // =======================
    // ✅ REJECT ITEM
    // =======================
    public function rejectItem($cartItemId)
    {
        return $this->updateItemStatus($cartItemId, 'rejected');
    }

    // =======================
    // 🔧 UPDATE STATUS HELPER
    // =======================
    private function updateItemStatus($cartItemId, $newStatus)
    {
        $item = CartItem::with(['cart', 'item'])->findOrFail($cartItemId);
        $cart = $item->cart;
        $product = $item->item; // ambil data barang dari relasi

        DB::beginTransaction();
        try {
            // Ubah status item
            $item->status = $newStatus;
            $item->save();

            // Jika reject → kembalikan stok ke tabel items
            if ($newStatus === 'rejected') {
                DB::table('items')
                    ->where('id', $product->id)
                    ->increment('stock', $item->quantity);
            }

            // Hitung ulang status cart berdasarkan semua item
            $cartItems = $cart->cartItems;
            $approvedCount = $cartItems->where('status', 'approved')->count();
            $rejectedCount = $cartItems->where('status', 'rejected')->count();
            $totalCount = $cartItems->count();

            if ($approvedCount === $totalCount) {
                $newCartStatus = 'approved';
            } elseif ($rejectedCount === $totalCount) {
                $newCartStatus = 'rejected';
            } else {
                $newCartStatus = 'approved_partially';
            }

            $cart->status = $newCartStatus;
            $cart->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Status item berhasil diperbarui.',
                'item_id' => $item->id,
                'cart_id' => $cart->id,
                'item_status' => $item->status,
                'cart_status' => $cart->status,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memperbarui status item', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui status item.']);
        }
    }
}
