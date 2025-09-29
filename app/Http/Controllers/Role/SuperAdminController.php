<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Item_in;
use App\Models\Item_out;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SuperAdminController extends Controller
{
    public function index()
    {
        $topUsers = Item_out::select('cart_id', DB::raw('COUNT(*) as total_out'))
            ->whereNotNull('cart_id')
            ->groupBy('cart_id')
            ->orderByDesc('total_out')
            ->take(5)
            ->with(['cart.user'])
            ->get();

        $itemIns = Item_in::with('item')->latest()->take(5)->get();
        $itemOuts = Item_out::with('item')->latest()->take(5)->get();

        $expiredSoon = Item_in::whereNotNull('expired_at')
            ->whereBetween('expired_at', [Carbon::now(), Carbon::now()->addDays(30)])
            ->with('item')
            ->get();

        // --- Daily (7 hari: Senin - Minggu) ---
        $dailyLabels = [];
        $dailyMasuk = [];
        $dailyKeluar = [];
        $startOfWeek = Carbon::now()->startOfWeek(); // Senin
        $endOfWeek = Carbon::now()->endOfWeek(); // Minggu

        for ($date = $startOfWeek->copy(); $date->lte($endOfWeek); $date->addDay()) {
            $dailyLabels[] = $date->format('D'); // Sen, Sel, Rab, dst
            $dailyMasuk[] = Item_in::whereDate('created_at', $date->toDateString())->sum('quantity');
            $dailyKeluar[] = Item_out::whereDate('created_at', $date->toDateString())->sum('quantity');
        }

        // --- Weekly (4 minggu terakhir) ---
        $weeklyLabels = [];
        $weeklyMasuk = [];
        $weeklyKeluar = [];
        for ($i = 3; $i >= 0; $i--) {
            $startOfWeek = Carbon::now()->subWeeks($i)->startOfWeek();
            $endOfWeek = Carbon::now()->subWeeks($i)->endOfWeek();

            $weeklyLabels[] = "Minggu ke-" . (4 - $i);
            $weeklyMasuk[] = Item_in::whereBetween('created_at', [$startOfWeek, $endOfWeek])->sum('quantity');
            $weeklyKeluar[] = Item_out::whereBetween('created_at', [$startOfWeek, $endOfWeek])->sum('quantity');
        }


        // --- Monthly (1–12) ---
        $monthlyLabels = [];
        $monthlyMasuk = [];
        $monthlyKeluar = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlyLabels[] = Carbon::create()->month($m)->format('M');
            $monthlyMasuk[] = Item_in::whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', $m)->sum('quantity');
            $monthlyKeluar[] = Item_out::whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', $m)->sum('quantity');
        }

        // --- Yearly (ambil 5 tahun terakhir) ---
        $yearlyLabels = [];
        $yearlyMasuk = [];
        $yearlyKeluar = [];
        $startYear = Carbon::now()->year - 4;
        $endYear = Carbon::now()->year;
        for ($y = $startYear; $y <= $endYear; $y++) {
            $yearlyLabels[] = $y;
            $yearlyMasuk[] = Item_in::whereYear('created_at', $y)->sum('quantity');
            $yearlyKeluar[] = Item_out::whereYear('created_at', $y)->sum('quantity');
        }

        // --- Hitung Growth bulan ini vs bulan lalu ---
        $thisMonth = $monthlyMasuk[Carbon::now()->month - 1] ?? 0;
        $lastMonth = $monthlyMasuk[Carbon::now()->month - 2] ?? 0;
        $growth = 0;
        if ($lastMonth > 0) {
            $growth = (($thisMonth - $lastMonth) / $lastMonth) * 100;
        }

        return view('role.super_admin.dashboard', [
            'categories' => Category::count(),
            'item' => Item::count(),
            'suppliers' => Supplier::count(),
            'users' => User::count(),
            'itemIns' => $itemIns,
            'itemOuts' => $itemOuts,
            'expiredSoon' => $expiredSoon,
            'topUsers' => $topUsers,

            // chart data
            'dailyLabels' => $dailyLabels,
            'dailyMasuk' => $dailyMasuk,
            'dailyKeluar' => $dailyKeluar,
            'weeklyLabels' => $weeklyLabels,
            'weeklyMasuk' => $weeklyMasuk,
            'weeklyKeluar' => $weeklyKeluar,
            'monthlyLabels' => $monthlyLabels,
            'monthlyMasuk' => $monthlyMasuk,
            'monthlyKeluar' => $monthlyKeluar,
            'yearlyLabels' => $yearlyLabels,
            'yearlyMasuk' => $yearlyMasuk,
            'yearlyKeluar' => $yearlyKeluar,

            // growth data
            'growth' => $growth,
        ]);
    }
}