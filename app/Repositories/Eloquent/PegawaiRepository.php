<?php

namespace App\Repositories\Eloquent;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PegawaiRepository
{
    public function getUserRequestHistory(string $range = 'week')
    {
        $userId = Auth::id();

        $labels = [];
        $data = [];

        if ($range === 'week') {
            // 7 hari terakhir
            $start = Carbon::now()->subDays(6)->startOfDay();
            $end   = Carbon::now()->endOfDay();

            $requests = DB::table('carts')
                ->join('cart_items', 'carts.id', '=', 'cart_items.cart_id')
                ->where('carts.user_id', $userId)
                ->whereBetween('carts.created_at', [$start, $end])
                ->select(
                    DB::raw('DATE(carts.created_at) as tanggal'),
                    DB::raw('SUM(cart_items.quantity) as total_quantity')
                )
                ->groupBy('tanggal')
                ->pluck('total_quantity', 'tanggal');

            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $label = $date->format('d M');
                $labels[] = $label;
                $data[] = $requests[$date->toDateString()] ?? 0;
            }
        }

        elseif ($range === 'month') {
            // 30 hari terakhir
            $start = Carbon::now()->subDays(29)->startOfDay();
            $end   = Carbon::now()->endOfDay();

            $requests = DB::table('carts')
                ->join('cart_items', 'carts.id', '=', 'cart_items.cart_id')
                ->where('carts.user_id', $userId)
                ->whereBetween('carts.created_at', [$start, $end])
                ->select(
                    DB::raw('DATE(carts.created_at) as tanggal'),
                    DB::raw('SUM(cart_items.quantity) as total_quantity')
                )
                ->groupBy('tanggal')
                ->pluck('total_quantity', 'tanggal');

            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $label = $date->format('d M');
                $labels[] = $label;
                $data[] = $requests[$date->toDateString()] ?? 0;
            }
        }

        elseif ($range === 'year') {
            // 12 bulan terakhir
            $start = Carbon::now()->subMonths(11)->startOfMonth();
            $end   = Carbon::now()->endOfMonth();

            $requests = DB::table('carts')
                ->join('cart_items', 'carts.id', '=', 'cart_items.cart_id')
                ->where('carts.user_id', $userId)
                ->whereBetween('carts.created_at', [$start, $end])
                ->select(
                    DB::raw('DATE_FORMAT(carts.created_at, "%Y-%m") as bulan'),
                    DB::raw('SUM(cart_items.quantity) as total_quantity')
                )
                ->groupBy('bulan')
                ->pluck('total_quantity', 'bulan');

            for ($date = $start->copy(); $date->lte($end); $date->addMonth()) {
                $key = $date->format('Y-m');
                $label = $date->format('M Y'); // contoh: Sep 2025
                $labels[] = $label;
                $data[] = $requests[$key] ?? 0;
            }
        }

        return [
            'labels' => $labels,
            'data'   => $data,
        ];
    }
}
