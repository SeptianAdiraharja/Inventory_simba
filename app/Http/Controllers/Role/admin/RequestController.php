<?php

namespace App\Http\Controllers\Role\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Cart, CartItem, Notification, Item};
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
        // 🧹 Auto Cleanup – Hapus cart_items dari cart yang final > 3 hari
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
        // 🔍 Filter dan Pencarian
        // =========================================================
        $status = $request->get('status');
        $search = $request->get('q');
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
            // 🚫 Hanya tampilkan cart aktif atau final < 3 hari
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
            // 🔍 Pencarian (dari search bar)
            // =========================================================
            ->when($search, function ($query) use ($search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%");
                });
            })
            // =========================================================
            // 🎯 Filter tambahan berdasarkan status (dropdown/tab)
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
            ->paginate(10)
            ->appends(['q' => $search, 'status' => $status]);

        // =========================================================
        // 📄 Kirim ke view
        // =========================================================
        return view('role.admin.request', compact('requests', 'status', 'search'));
    }

    /* =======================================================
       📦 SHOW – Detail Cart via AJAX
    ======================================================== */
    public function show(string $id)
    {
        try {
            Log::info('DEBUG SHOW METHOD - Memuat detail cart', ['cart_id' => $id]);

            $cart = DB::table('carts')
                ->join('users', 'carts.user_id', '=', 'users.id')
                ->select('carts.*', 'users.name as user_name')
                ->where('carts.id', $id)
                ->first();

            if (!$cart) {
                Log::warning('DEBUG SHOW METHOD - Cart tidak ditemukan', ['cart_id' => $id]);
                return response()->json(['error' => 'Permintaan tidak ditemukan'], 404);
            }

            Log::info('DEBUG SHOW METHOD - Cart ditemukan', ['cart_id' => $id, 'user_name' => $cart->user_name]);

            $cartItems = DB::table('cart_items')
                ->join('items', 'cart_items.item_id', '=', 'items.id')
                ->select(
                    'cart_items.id',
                    'cart_items.quantity',
                    'cart_items.status',
                    'cart_items.rejection_reason',
                    'items.name as item_name',
                    'items.code as item_code',
                    'items.id as item_id' // Pastikan item_id ada
                )
                ->where('cart_items.cart_id', $id)
                ->get();

            Log::info('DEBUG SHOW METHOD - Cart items loaded', [
                'cart_id' => $id,
                'total_items' => $cartItems->count()
            ]);

            $total = $cartItems->count();
            $processed = $cartItems->whereIn('status', ['approved', 'rejected'])->count();

            $scan_status = $processed === $total
                ? 'Selesai'
                : ($processed > 0 ? 'Sebagian' : 'Belum diproses');

            Log::info('DEBUG SHOW METHOD - Menampilkan view', ['cart_id' => $id]);

            return view('role.admin.partials.cart-detail', compact('cart', 'cartItems', 'scan_status'));

        } catch (\Exception $e) {
            Log::error('DEBUG SHOW METHOD - ERROR', [
                'cart_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ], 500);
        }
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
            DB::beginTransaction();

            // Ambil semua item di cart sebelum update
            $cartItems = DB::table('cart_items')->where('cart_id', $id)->get();

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

            // 🔹 Update stok berdasarkan status
            foreach ($cartItems as $item) {
                $itemModel = Item::find($item->item_id);
                if ($itemModel) {
                    if ($newStatus === 'approved') {
                        // Kurangi stok saat approve
                        if ($itemModel->stock >= $item->quantity) {
                            $itemModel->decrement('stock', $item->quantity);
                        } else {
                            throw new Exception("Stok {$itemModel->name} tidak mencukupi.");
                        }
                    } elseif ($newStatus === 'rejected') {
                        // Kembalikan stok saat reject - HANYA jika sebelumnya approved
                        if ($item->status === 'approved') {
                            $itemModel->increment('stock', $item->quantity);
                        }
                        // Jika sebelumnya pending, tidak perlu kembalikan stok
                    }
                }
            }

            DB::commit();

            // 🟢 Kirim notifikasi ke user sesuai hasil akhir
            $cart = DB::table('carts')->find($id);
            if ($cart) {
                $notifTitle = '';
                $notifMessage = '';

                switch ($newStatus) {
                    case 'approved':
                        $notifTitle = 'Permintaan Disetujui';
                        $notifMessage = 'Permintaan barang kamu telah disetujui sepenuhnya.';
                        break;
                    case 'rejected':
                        $notifTitle = 'Permintaan Ditolak';
                        $notifMessage = 'Permintaan barang kamu telah ditolak.';
                        break;
                }

                Notification::create([
                    'user_id' => $cart->user_id,
                    'title' => $notifTitle,
                    'message' => $notifMessage,
                    'status' => 'unread',
                ]);
            }

            // 🔹 Kembalikan response AJAX
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'status' => $newStatus,
                    'message' => "Semua item berhasil {$newStatus}.",
                ]);
            }

            return back()->with('success', "Semua item berhasil {$newStatus}.");

        } catch (\Throwable $e) {
            DB::rollBack();
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
                $cartItems = DB::table('cart_items')->where('cart_id', $cartId)->get();

                // Update status cart_items
                DB::table('cart_items')
                    ->where('cart_id', $cartId)
                    ->update([
                        'status' => $status,
                        'rejection_reason' => $status === 'rejected'
                            ? ($reason ?: 'Ditolak oleh admin.')
                            : null,
                        'updated_at' => now(),
                    ]);

                // 🔹 Update stok untuk semua item
                foreach ($cartItems as $item) {
                    $itemModel = Item::find($item->item_id);
                    if ($itemModel) {
                        if ($status === 'approved') {
                            // Kurangi stok saat approve
                            if ($itemModel->stock >= $item->quantity) {
                                $itemModel->decrement('stock', $item->quantity);
                            } else {
                                throw new Exception("Stok {$itemModel->name} tidak mencukupi.");
                            }
                        } elseif ($status === 'rejected') {
                            // Kembalikan stok saat reject - PERBAIKAN LOGIC
                            // Jika sebelumnya approved, kembalikan stok
                            if ($item->status === 'approved') {
                                $itemModel->increment('stock', $item->quantity);
                                Log::info('Stok dikembalikan untuk item yang direject', [
                                    'item_id' => $item->item_id,
                                    'quantity' => $item->quantity,
                                    'previous_status' => $item->status
                                ]);
                            }
                            // Jika sebelumnya pending, tidak perlu ubah stok karena belum dikurangi
                        }
                    }
                }
            }

            // CASE 2: Perubahan per item (changes)
            if (is_array($changes) || is_object($changes)) {
                foreach ($changes as $itemId => $change) {
                    $cartItem = DB::table('cart_items')->where('id', $itemId)->first();

                    if ($cartItem) {
                        $itemModel = Item::find($cartItem->item_id);

                        // 🔹 Logika update stok berdasarkan perubahan status
                        if ($cartItem->status === 'pending') {
                            if ($change['status'] === 'approved' && $itemModel) {
                                // Kurangi stok saat approve dari pending
                                if ($itemModel->stock >= $cartItem->quantity) {
                                    $itemModel->decrement('stock', $cartItem->quantity);
                                    Log::info('Stok dikurangi untuk item yang diapprove dari pending', [
                                        'item_id' => $cartItem->item_id,
                                        'quantity' => $cartItem->quantity
                                    ]);
                                } else {
                                    throw new Exception("Stok {$itemModel->name} tidak mencukupi.");
                                }
                            }
                            // Jika dari pending ke rejected, tidak perlu ubah stok karena belum dikurangi
                        }
                        // 🔹 Jika status berubah dari approved ke rejected
                        elseif ($cartItem->status === 'approved' && $change['status'] === 'rejected' && $itemModel) {
                            // Kembalikan stok yang sebelumnya dikurangi
                            $itemModel->increment('stock', $cartItem->quantity);
                            Log::info('Stok dikembalikan untuk item yang direject dari approved', [
                                'item_id' => $cartItem->item_id,
                                'quantity' => $cartItem->quantity
                            ]);
                        }
                        // 🔹 Jika status berubah dari rejected ke approved
                        elseif ($cartItem->status === 'rejected' && $change['status'] === 'approved' && $itemModel) {
                            // Kurangi stok untuk approve
                            if ($itemModel->stock >= $cartItem->quantity) {
                                $itemModel->decrement('stock', $cartItem->quantity);
                                Log::info('Stok dikurangi untuk item yang diapprove dari rejected', [
                                    'item_id' => $cartItem->item_id,
                                    'quantity' => $cartItem->quantity
                                ]);
                            } else {
                                throw new Exception("Stok {$itemModel->name} tidak mencukupi.");
                            }
                        }

                        // Update status cart_item
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

            // Notifikasi
            $cart = DB::table('carts')->find($cartId);
            if ($cart) {
                $notifTitle = '';
                $notifMessage = '';

                switch ($cartStatus) {
                    case 'approved':
                        $notifTitle = 'Permintaan Disetujui';
                        $notifMessage = 'Permintaan barang kamu telah disetujui sepenuhnya.';
                        break;
                    case 'rejected':
                        $notifTitle = 'Permintaan Ditolak';
                        $notifMessage = 'Semua permintaan barang kamu telah ditolak.';
                        break;
                    case 'approved_partially':
                        $notifTitle = 'Sebagian Permintaan Disetujui';
                        $notifMessage = 'Sebagian permintaan barang kamu disetujui, sebagian lainnya ditolak.';
                        break;
                    default:
                        $notifTitle = 'Status Permintaan Diperbarui';
                        $notifMessage = 'Status permintaan barang kamu telah diperbarui.';
                        break;
                }

                Notification::create([
                    'user_id' => $cart->user_id,
                    'title' => $notifTitle,
                    'message' => $notifMessage,
                    'status' => 'unread',
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Perubahan berhasil disimpan.',
                'cart_status' => $cartStatus,
                'reason' => $reason,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error dalam bulkUpdate', [
                'cart_id' => $cartId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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
        $product = $item->item;

        DB::beginTransaction();
        try {
            // 🔹 Update stok berdasarkan perubahan status
            if ($item->status === 'pending' && $newStatus === 'approved') {
                // Kurangi stok saat approve dari pending
                if ($product->stock >= $item->quantity) {
                    $product->decrement('stock', $item->quantity);
                    Log::info('Stok dikurangi untuk item yang diapprove dari pending', [
                        'item_id' => $product->id,
                        'quantity' => $item->quantity,
                        'new_stock' => $product->stock - $item->quantity
                    ]);
                } else {
                    throw new Exception("Stok {$product->name} tidak mencukupi.");
                }
            } elseif ($item->status === 'approved' && $newStatus === 'rejected') {
                // Kembalikan stok saat reject dari approved
                $product->increment('stock', $item->quantity);
                Log::info('Stok dikembalikan untuk item yang direject dari approved', [
                    'item_id' => $product->id,
                    'quantity' => $item->quantity,
                    'new_stock' => $product->stock + $item->quantity
                ]);
            } elseif ($item->status === 'rejected' && $newStatus === 'approved') {
                // Kurangi stok saat approve dari rejected
                if ($product->stock >= $item->quantity) {
                    $product->decrement('stock', $item->quantity);
                    Log::info('Stok dikurangi untuk item yang diapprove dari rejected', [
                        'item_id' => $product->id,
                        'quantity' => $item->quantity,
                        'new_stock' => $product->stock - $item->quantity
                    ]);
                } else {
                    throw new Exception("Stok {$product->name} tidak mencukupi.");
                }
            }
            // Jika dari pending ke rejected, tidak perlu ubah stok karena belum dikurangi

            // Simpan status sebelumnya untuk logging
            $oldStatus = $item->status;

            // Ubah status item
            $item->status = $newStatus;
            $item->save();

            Log::info('Status item berubah', [
                'cart_item_id' => $item->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'item_id' => $product->id,
                'quantity' => $item->quantity
            ]);

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
            Log::error('Gagal memperbarui status item', [
                'cart_item_id' => $cartItemId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status item: ' . $e->getMessage()
            ]);
        }
    }
}
