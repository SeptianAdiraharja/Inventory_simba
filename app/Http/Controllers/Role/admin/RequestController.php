<?php

namespace App\Http\Controllers\Role\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Notification;
use Carbon\Carbon;

class RequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // 🔹 1. Hapus otomatis request yang sudah approved lebih dari 3 hari
        // HANYA hapus cart yang statusnya 'approved' atau 'rejected', jangan hapus 'approved_partially'
        DB::table('carts')
            ->whereIn('status', ['approved', 'rejected'])
            ->where('updated_at', '<', now()->subDays(3))
            ->delete();

        // 🔹 Ambil filter dari query string
        $statusFilter = $request->get('status');

        // 🔹 2. Ambil data sesuai filter
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
            ->where(function($query) use ($statusFilter) {
                // 🔸 Filter sesuai dropdown
                if ($statusFilter === 'pending') {
                    $query->where('carts.status', 'pending');
                } elseif ($statusFilter === 'rejected') {
                    $query->where('carts.status', 'rejected')
                          ->where('carts.updated_at', '>=', now()->subDays(3));
                } elseif ($statusFilter === 'approved') {
                    $query->where('carts.status', 'approved')
                          ->where('carts.updated_at', '>=', now()->subDays(3));
                } elseif ($statusFilter === 'approved_partially') {
                    $query->where('carts.status', 'approved_partially')
                          ->where('carts.updated_at', '>=', now()->subDays(3));
                } else {
                    // Default "all" → pending + rejected/approved/approved_partially (<= 3 hari)
                    $query->where('carts.status', 'pending')
                          ->orWhere(function($sub) {
                              $sub->whereIn('carts.status', ['rejected', 'approved', 'approved_partially'])
                                  ->where('carts.updated_at', '>=', now()->subDays(3));
                          });
                }
            })
            ->groupBy(
                'carts.id',
                'users.name',
                'users.email',
                'users.role',
                'carts.status',
                'carts.created_at',
                'carts.updated_at'
            )
            ->orderBy('carts.created_at', 'desc')
            ->paginate(10);

        return view('role.admin.request', compact('requests', 'statusFilter'));
    }

    /**
     * Display the specified resource (Untuk Detail AJAX).
     */
    public function show(string $id)
    {
        // Ambil cart utama
        $cart = DB::table('carts')
            ->join('users', 'carts.user_id', '=', 'users.id')
            ->select('carts.*', 'users.name as user_name')
            ->where('carts.id', $id)
            ->first();

        if (!$cart) {
            return response()->json(['error' => 'Permintaan tidak ditemukan'], 404);
        }

        // Tampilkan SEMUA item tanpa filter berdasarkan status cart
        $cartItems = DB::table('cart_items')
            ->join('items', 'cart_items.item_id', '=', 'items.id')
            ->select(
                'cart_items.id',
                'cart_items.quantity',
                'cart_items.status',
                'cart_items.rejection_reason',
                'items.name as item_name',
                'items.code as item_code'
            )
            ->where('cart_items.cart_id', $id)
            ->get();

        // Cek status pemrosesan item
        $totalItems = DB::table('cart_items')->where('cart_id', $id)->count();
        $processedItems = DB::table('cart_items')
            ->where('cart_id', $id)
            ->whereIn('status', ['approved', 'rejected'])
            ->count();

        if ($processedItems === $totalItems) {
            $scan_status = 'Selesai';
        } elseif ($processedItems > 0) {
            $scan_status = 'Sebagian';
        } else {
            $scan_status = 'Belum diproses';
        }

        // Load partial view (detail item)
        return view('role.admin.partials.cart-detail', compact('cart', 'cartItems', 'scan_status'));
    }

    /**
     * Update status untuk SEMUA item dalam satu cart (Approve All/Reject All).
     */
    public function update(Request $request, string $id)
    {
        $newStatus = $request->status;
        $validStatuses = ['approved', 'rejected'];

        if (!in_array($newStatus, $validStatuses)) {
            return redirect()->back()->withErrors('Invalid status value.');
        }

        // 1. Update status SEMUA cart_items
        DB::table('cart_items')
            ->where('cart_id', $id)
            ->update([
                'status' => $newStatus,
                'rejection_reason' => ($newStatus === 'rejected') ? 'Ditolak secara keseluruhan.' : null,
                'updated_at' => now()
            ]);

        // 2. Update status cart utama
        DB::table('carts')
            ->where('id', $id)
            ->update([
                'status' => $newStatus,
                'updated_at' => now()
            ]);

        $cart = DB::table('carts')->where('id', $id)->first();

        if ($cart) {
            Notification::create([
                'user_id' => $cart->user_id,
                'title'   => 'Request ' . ucfirst($newStatus),
                'message' => 'Permintaan barang kamu telah ' . $newStatus . ' secara keseluruhan.',
                'status'  => 'unread',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if ($request->status === 'approved') {
            return redirect()
                ->route('admin.request', ['status' => 'pending'])
                ->with('showCartProcessedModal', true);
        }

        return redirect()->back()->with('success', 'Status berhasil diperbarui!');
    }

    /**
     * Approve satu cart item.
     */
    public function approveItem(string $cartItemId)
    {
        return $this->updateItemStatus($cartItemId, 'approved');
    }

    /**
     * Reject satu cart item.
     */
    public function rejectItem(Request $request, string $cartItemId)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $reason = $request->input('reason');
        return $this->updateItemStatus($cartItemId, 'rejected', $reason);
    }

    /**
     * Helper untuk update status item dan mengecek status cart utama.
     */
    protected function updateItemStatus(string $cartItemId, string $status, $reason = null)
    {
        $item = DB::table('cart_items')->where('id', $cartItemId)->first();

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Item tidak ditemukan'], 404);
        }

        // 1. Siapkan data update item
        $updateData = [
            'status' => $status,
            'updated_at' => now()
        ];

        // Hanya simpan alasan jika statusnya 'rejected'
        if ($status === 'rejected') {
            $updateData['rejection_reason'] = $reason;
        } else {
            $updateData['rejection_reason'] = null;
        }

        // 2. Update status item
        DB::table('cart_items')
            ->where('id', $cartItemId)
            ->update($updateData);

        // 3. Cek apakah semua item dalam cart sudah diproses
        $cartItems = DB::table('cart_items')->where('cart_id', $item->cart_id)->get();
        $totalItems = $cartItems->count();
        $processedItems = $cartItems->whereIn('status', ['approved', 'rejected'])->count();

        $response = ['success' => true, 'message' => 'Status item berhasil diperbarui.'];
        $newCartStatus = null;

        if ($processedItems === $totalItems) {
            // Semua item sudah diproses, tentukan status cart utama
            $approvedItems = $cartItems->where('status', 'approved')->count();
            $rejectedItems = $cartItems->where('status', 'rejected')->count();

            // Tentukan status cart berdasarkan kondisi item
            if ($approvedItems === $totalItems) {
                $newCartStatus = 'approved';
            } elseif ($rejectedItems === $totalItems) {
                $newCartStatus = 'rejected';
            } else {
                // Jika ada yang approved dan rejected → approved_partially
                $newCartStatus = 'approved_partially';
            }

            // Update status cart
            DB::table('carts')
                ->where('id', $item->cart_id)
                ->update([
                    'status' => $newCartStatus,
                    'updated_at' => now()
                ]);

            // Buat notifikasi berdasarkan status
            $cart = DB::table('carts')->where('id', $item->cart_id)->first();

            if ($newCartStatus === 'approved_partially') {
                $message = 'Permintaan barang kamu telah disetujui sebagian. Beberapa item ditolak.';
            } else {
                $message = 'Permintaan barang kamu telah ' . $newCartStatus . ' secara final.';
            }

            Notification::create([
                'user_id' => $cart->user_id,
                'title' => 'Request ' . ucfirst(str_replace('_', ' ', $newCartStatus)),
                'message' => $message,
                'status' => 'unread',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Tambahkan status cart ke response
            $response['cart_status_final'] = true;
            $response['cart_status'] = $newCartStatus;
        }

        return response()->json($response);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
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