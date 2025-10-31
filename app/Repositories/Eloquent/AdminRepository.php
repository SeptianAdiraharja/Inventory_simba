<?php

namespace App\Repositories\Eloquent;

use App\Models\Cart;
use App\Models\Guest;
use App\Models\Item_out;
use App\Models\User;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Repositories\Contracts\AdminRepositoryInterface;

class AdminRepository implements AdminRepositoryInterface
{
    public function getTotalBarangKeluar()
    {
        return Item_out::sum('quantity');
    }

    public function getTotalRequest()
    {
        return Cart::count();
    }

    public function getTotalGuest()
    {
        return Guest::count();
    }

    public function getLatestBarangKeluar($limit = 5)
    {
        return Item_out::with('item')->latest()->take($limit)->get();
    }

    public function getLatestRequest($limit = 5)
    {
        return Cart::with(['user', 'items'])->latest()->take($limit)->get();
    }

    /**
     * 🔹 Mengambil 5 user/guest dengan total permintaan terbanyak
     */
    public function getTopRequesters($limit = 5)
    {
        // --- Pegawai ---
        $userRequests = DB::table('carts')
            ->join('cart_items', 'carts.id', '=', 'cart_items.cart_id')
            ->select(
                'carts.user_id as requester_id',
                DB::raw('SUM(cart_items.quantity) as total_quantity')
            )
            ->groupBy('carts.user_id')
            ->get()
            ->map(function ($item) {
                $item->type = 'user';
                return $item;
            });

        // --- Guest ---
        $guestRequests = DB::table('guest_cart_items')
            ->join('guest_carts', 'guest_cart_items.guest_cart_id', '=', 'guest_carts.id')
            ->join('sessions', 'guest_carts.session_id', '=', 'sessions.id')
            ->leftJoin('guests', 'guests.created_by', '=', 'sessions.user_id')
            ->select(
                DB::raw('COALESCE(guests.id, sessions.id) as requester_id'),
                DB::raw('COALESCE(guests.name, "Guest") as name'),
                DB::raw('SUM(guest_cart_items.quantity) as total_quantity')
            )
            ->groupBy('guests.id', 'guests.name', 'sessions.id', 'sessions.ip_address')
            ->get()
            ->map(function ($item) {
                $item->type = 'guest';
                return $item;
        });


        // --- Gabungkan dan urutkan ---
        $combined = $userRequests->concat($guestRequests)
            ->sortByDesc('total_quantity')
            ->take($limit);

        // --- Format output ---
        return $combined->map(function ($r) {
            if ($r->type === 'user') {
                $user = User::find($r->requester_id);
                return [
                    'name' => $user->name ?? 'Unknown User',
                    'email' => $user->email ?? '-',
                    'role' => ucfirst($user->role ?? 'Pegawai'),
                    'total_requests' => $r->total_quantity,
                ];
            }
            return [
                'name' => $r->name ?? 'Guest',
                'email' => 'N/A',
                'role' => 'Guest',
                'total_requests' => $r->total_quantity,
            ];
        });
    }


    /**
     * 🔹 Ambil data chart mingguan/bulanan/tahunan
     */
    public function getChartDataByRange(string $range)
    {
        $now = Carbon::now();

        if ($range === 'week') {
            $start = $now->copy()->subDays(6);
            $period = collect(range(0, 6))->map(fn($i) => $now->copy()->subDays(6 - $i)->format('Y-m-d'));
        } elseif ($range === 'month') {
            $start = $now->copy()->subDays(29);
            $period = collect(range(0, 29))->map(fn($i) => $now->copy()->subDays(29 - $i)->format('Y-m-d'));
        } else {
            $start = $now->copy()->subMonths(11);
            $period = collect(range(0, 11))->map(fn($i) => $now->copy()->subMonths(11 - $i)->format('Y-m'));
        }

        $barangKeluar = Item_out::select(
                DB::raw($range === 'year'
                    ? "DATE_FORMAT(created_at, '%Y-%m') as periode"
                    : "DATE(created_at) as periode"
                ),
                DB::raw('SUM(quantity) as total')
            )
            ->where('created_at', '>=', $start)
            ->groupBy('periode')
            ->pluck('total', 'periode');

        return [
            'labels' => $period->map(fn($p) =>
                $range === 'year'
                    ? Carbon::parse($p . '-01')->translatedFormat('M Y')
                    : Carbon::parse($p)->translatedFormat('d M')
            ),
            'keluar' => $period->map(fn($p) => $barangKeluar[$p] ?? 0),
        ];
    }
}
