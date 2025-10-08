<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\PegawaiRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

class PegawaiController extends Controller
{
    protected $pegawaiRepository;

    public function __construct(PegawaiRepository $pegawaiRepository)
    {
        $this->pegawaiRepository = $pegawaiRepository;
    }

    public function index(Request $request)
    {
        $range = $request->get('range', 'week');
        $history = $this->pegawaiRepository->getUserRequestHistory($range);

        // Pastikan user sudah login
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = Auth::user();

        // 🔹 Ambil notif yang belum dibaca
        $notifications = Notification::where('user_id', $user->id)
            ->where('status', 'unread')
            ->latest()
            ->get();

        // 🔹 Jumlah notifikasi untuk icon bell
        $notifCount = $notifications->count();

        // 🔹 Ambil list request user yang login
        $users = DB::table('users')
            ->where('users.id', $user->id)
            ->leftJoin('carts', 'users.id', '=', 'carts.user_id')
            ->leftJoin('cart_items', 'carts.id', '=', 'cart_items.cart_id')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.role',
                DB::raw('COALESCE(SUM(cart_items.quantity), 0) as total_request')
            )
            ->groupBy('users.id', 'users.name', 'users.email', 'users.role')
            ->get();

        if (!is_array($history) || !isset($history['labels']) || !isset($history['data'])) {
            $history = ['labels' => [], 'data' => []];
        }

        return view('role.pegawai.dashboard', compact(
            'history',
            'range',
            'users',
            'notifications',
            'notifCount'
        ));
    }

    public function readNotifications()
    {
        // Gunakan facade Auth, bukan helper langsung
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'User not authenticated']);
        }

        Notification::where('user_id', Auth::id())
            ->where('status', 'unread')
            ->update(['status' => 'read']);

        return response()->json(['success' => true]);
    }

    public function produk()
    {
        return view('role.pegawai.produk');
    }

    // metode CRUD default
    public function create() {}
    public function store(Request $request) {}
    public function show(string $id) {}
    public function edit(string $id) {}
    public function update(Request $request, string $id) {}
    public function destroy(string $id) {}
}
