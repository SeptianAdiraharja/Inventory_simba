<?php

namespace App\Repositories\Eloquent;

use App\Models\Cart;
use App\Models\Guest;
use App\Models\Item_in;
use App\Models\Item_out;
use App\Models\User;
use App\Repositories\Contracts\AdminRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
        return Item_out::with('item')
            ->latest()
            ->paginate($limit);
    }

    public function getLatestRequest($limit = 5)
    {
        return Cart::with(['user', 'items'])
            ->latest()
            ->paginate($limit);
    }

    public function getTopRequesters($limit = 5)
    {
        $userRequests = DB::table('carts')
            ->join('cart_items', 'carts.id', '=', 'cart_items.cart_id')
            ->select('carts.user_id as requester_id', DB::raw('SUM(cart_items.quantity) as total_quantity'))
            ->groupBy('carts.user_id')
            ->get()
            ->map(function ($item) {
                $item->type = 'user';
                return $item;
            });

            $guestRequests = DB::table('guest_cart_items')
            ->join('guest_carts', 'guest_cart_items.guest_cart_id', '=', 'guest_carts.id')
            ->join('sessions', 'guest_carts.session_id', '=', 'sessions.id')
            ->leftJoin('guests', 'guests.created_by', '=', 'sessions.user_id')
            ->select(
                DB::raw('COALESCE(guests.id, sessions.id) as requester_id'),
                DB::raw('COALESCE(guests.name, sessions.ip_address) as name'),
                DB::raw('SUM(guest_cart_items.quantity) as total_quantity')
            )
            ->groupBy('guests.id', 'guests.name', 'sessions.id', 'sessions.ip_address')
            ->get()
            ->map(function ($item) {
                $item->type = 'guest';
                return $item;
            });

        $combinedRequests = $userRequests->concat($guestRequests)
            ->sortByDesc('total_quantity')
            ->take($limit);

        $topRequesters = [];
        foreach ($combinedRequests as $requester) {
            if ($requester->type === 'user') {
                $user = User::find($requester->requester_id);
                if ($user) {
                    $topRequesters[] = [
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => ucfirst($user->role),
                        'total_requests' => $requester->total_quantity,
                    ];
                }
            } else {
                $topRequesters[] = [
                    'name' => $requester->name ?? 'Guest Unknown',
                    'email' => 'N/A',
                    'role' => 'Guest',
                    'total_requests' => $requester->total_quantity,
                ];
            }
        }

        return collect($topRequesters);
    }

    public function getChartDataByRange(string $range)
    {
        if ($range === 'week') {
            // ambil 7 hari terakhir
            $start = Carbon::now()->subDays(6)->startOfDay();
            $dates = collect();
            for ($i = 0; $i < 7; $i++) {
                $dates->push(Carbon::now()->subDays(6 - $i)->format('Y-m-d'));
            }

            $barangKeluar = Item_out::select(
                    DB::raw("DATE(created_at) as tgl"),
                    DB::raw('SUM(quantity) as total')
                )
                ->where('created_at', '>=', $start)
                ->groupBy('tgl')
                ->pluck('total', 'tgl');

            $labels = $dates->map(fn($d) => Carbon::parse($d)->translatedFormat('d M')); // ex: 20 Sep
            $keluar = $dates->map(fn($d) => $barangKeluar[$d] ?? 0);

        } elseif ($range === 'month') {
            // ambil 30 hari terakhir
            $start = Carbon::now()->subDays(29)->startOfDay();
            $dates = collect();
            for ($i = 0; $i < 30; $i++) {
                $dates->push(Carbon::now()->subDays(29 - $i)->format('Y-m-d'));
            }

            $barangKeluar = Item_out::select(
                    DB::raw("DATE(created_at) as tgl"),
                    DB::raw('SUM(quantity) as total')
                )
                ->where('created_at', '>=', $start)
                ->groupBy('tgl')
                ->pluck('total', 'tgl');

            $labels = $dates->map(fn($d) => Carbon::parse($d)->translatedFormat('d M'));
            $keluar = $dates->map(fn($d) => $barangKeluar[$d] ?? 0);

        } else {
            // year → ambil 12 bulan terakhir
            $start = Carbon::now()->subMonths(11)->startOfMonth();
            $months = collect();
            for ($i = 0; $i < 12; $i++) {
                $months->push(Carbon::now()->subMonths(11 - $i)->format('Y-m'));
            }

            $barangKeluar = Item_out::select(
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m') as bulan"),
                    DB::raw('SUM(quantity) as total')
                )
                ->where('created_at', '>=', $start)
                ->groupBy('bulan')
                ->pluck('total', 'bulan');

            $labels = $months->map(fn($m) => Carbon::parse($m . '-01')->translatedFormat('M Y')); // Jan 2025
            $keluar = $months->map(fn($m) => $barangKeluar[$m] ?? 0);
        }

        return [
            'labels' => $labels,
            'keluar' => $keluar,
        ];
    }

    public function getChartDataYear()
    {
        $oneYearAgo = Carbon::now()->subYear()->startOfMonth();
        $now = Carbon::now()->endOfMonth();

        $barangMasuk = Item_in::select(
                DB::raw('MONTH(created_at) as bulan'),
                DB::raw('SUM(quantity) as total')
            )
            ->whereBetween('created_at', [$oneYearAgo, $now])
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->pluck('total', 'bulan');

        $barangKeluar = Item_out::select(
                DB::raw('MONTH(created_at) as bulan'),
                DB::raw('SUM(quantity) as total')
            )
            ->whereBetween('created_at', [$oneYearAgo, $now])
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->pluck('total', 'bulan');

        return [$barangMasuk, $barangKeluar];
    }
}
