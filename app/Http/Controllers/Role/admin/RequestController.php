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
        // 🧹 Hapus otomatis cart yang sudah final lebih dari 3 hari
        DB::table('cart_items')
            ->whereIn('cart_id', function ($q) {
                $q->select('id')
                    ->from('carts')
                    ->whereIn('status', ['approved', 'rejected'])
                    ->where('updated_at', '<', now()->subDays(3));
            })
            ->delete();

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
            ->when($status, function ($query) use ($status, $recent) {
                return match ($status) {
                    'pending' => $query->where('carts.status', 'pending'),
                    'rejected', 'approved', 'approved_partially' =>
                        $query->where('carts.status', $status)->where('carts.updated_at', '>=', $recent),
                    default => $query->where(function ($sub) use ($recent) {
                        $sub->where('carts.status', 'pending')
                            ->orWhere(function ($q) use ($recent) {
                                $q->whereIn('carts.status', ['rejected', 'approved', 'approved_partially'])
                                    ->where('carts.updated_at', '>=', $recent);
                            });
                    }),
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
        $newStatus = $request->status;

        if (!in_array($newStatus, ['approved', 'rejected'])) {
            return back()->withErrors('Status tidak valid.');
        }

        DB::transaction(function () use ($id, $newStatus) {
            DB::table('cart_items')
                ->where('cart_id', $id)
                ->update([
                    'status' => $newStatus,
                    'rejection_reason' => $newStatus === 'rejected' ? 'Ditolak secara keseluruhan.' : null,
                    'updated_at' => now(),
                ]);

            DB::table('carts')
                ->where('id', $id)
                ->update([
                    'status' => $newStatus,
                    'updated_at' => now(),
                ]);
        });

        $cart = DB::table('carts')->find($id);

        if ($cart) {
            Notification::create([
                'user_id' => $cart->user_id,
                'title' => 'Request ' . ucfirst($newStatus),
                'message' => 'Permintaan barang kamu telah ' . $newStatus . ' secara keseluruhan.',
                'status' => 'unread',
            ]);
        }

        return redirect()
            ->route('admin.request', ['status' => 'pending'])
            ->with('success', 'Status berhasil diperbarui.');
    }

    /* =======================================================
       🟢 APPROVE ITEM – Per Item
    ======================================================== */
    public function approveItem(string $cartItemId)
    {
        try {
            return $this->updateItemStatus($cartItemId, 'approved');
        } catch (Exception $e) {
            Log::error('Gagal approve item', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan server.'], 500);
        }
    }

    /* =======================================================
       🔴 REJECT ITEM – Per Item
    ======================================================== */
    public function rejectItem(Request $request, string $cartItemId)
    {
        $request->validate(['reason' => 'required|string|max:255']);

        $reason = $request->reason;
        $cartItem = DB::table('cart_items')->find($cartItemId);

        if (!$cartItem) {
            return response()->json(['success' => false, 'message' => 'Item tidak ditemukan.'], 404);
        }

        DB::table('cart_items')
            ->where('id', $cartItemId)
            ->update([
                'status' => 'rejected',
                'rejection_reason' => $reason,
                'updated_at' => now(),
            ]);

        return $this->updateItemStatus($cartItemId, 'rejected', $reason);
    }

    /* =======================================================
       ⚙️ HELPER – Update Status Item & Cart Otomatis
    ======================================================== */
    protected function updateItemStatus(string $cartItemId, string $status, ?string $reason = null)
    {
        Log::info('UpdateItemStatus', ['id' => $cartItemId, 'status' => $status]);

        $item = DB::table('cart_items')->find($cartItemId);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Item tidak ditemukan.'], 404);
        }

        // Update status item
        DB::table('cart_items')->where('id', $cartItemId)->update([
            'status' => $status,
            'rejection_reason' => $status === 'rejected' ? $reason : null,
            'updated_at' => now(),
        ]);

        // Ambil ulang data setelah update (pastikan status terbaru terambil)
        $cartItems = DB::table('cart_items')
            ->where('cart_id', $item->cart_id)
            ->get();

        $total = $cartItems->count();
        $approved = $cartItems->where('status', 'approved')->count();
        $rejected = $cartItems->where('status', 'rejected')->count();
        $pending = $cartItems->where('status', 'pending')->count();

        // 🔧 Logika baru (lebih jelas dan konsisten)
        if ($approved === $total) {
            $newCartStatus = 'approved';
        } elseif ($rejected === $total) {
            $newCartStatus = 'rejected';
        } elseif (($approved > 0 && $rejected > 0) || ($approved > 0 && $pending > 0) || ($rejected > 0 && $pending > 0)) {
            $newCartStatus = 'approved_partially';
        } else {
            $newCartStatus = 'pending';
        }

        DB::table('carts')->where('id', $item->cart_id)->update([
            'status' => $newCartStatus,
            'updated_at' => now(),
        ]);

        $cart = DB::table('carts')->find($item->cart_id);

        // Kirim notifikasi
        if ($cart) {
            Notification::create([
                'user_id' => $cart->user_id,
                'title' => 'Status Permintaan Diperbarui',
                'message' => match ($newCartStatus) {
                    'approved_partially' => 'Beberapa barang kamu disetujui sebagian.',
                    'approved' => 'Semua permintaan kamu telah disetujui.',
                    'rejected' => 'Semua permintaan kamu ditolak.',
                    default => 'Status permintaan kamu diperbarui.',
                },
                'status' => 'unread',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status item berhasil diperbarui.',
            'cart_status_final' => true,
            'cart_status' => $newCartStatus,
        ]);
    }

}
