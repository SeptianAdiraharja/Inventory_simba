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

        // --- DATA TABEL ---
        $itemIns = Item_in::with('item')->latest()->take(5)->get();
        $itemOuts = Item_out::with('item')->latest()->take(5)->get();

        // --- BARANG HAMPIR KEDALUARSA ---
        $expiredSoon = Item_in::whereNotNull('expired_at')
            ->whereBetween('expired_at', [Carbon::now(), Carbon::now()->addDays(30)])
            ->with('item')
            ->get();

        // === GRAFIK ===

        // ---  Harian (Senin–Minggu) ---
        $dailyLabels = [];
        $dailyMasuk = [];
        $dailyKeluar = [];
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        for ($date = $startOfWeek->copy(); $date->lte($endOfWeek); $date->addDay()) {
            $dailyLabels[] = $date->format('D');
            $dailyMasuk[] = Item_in::whereDate('created_at', $date)->sum('quantity');
            $dailyKeluar[] = Item_out::whereDate('created_at', $date)->sum('quantity');
        }

        // --- Mingguan (4 minggu di bulan ini) ---
        $weeklyLabels = [];
        $weeklyMasuk = [];
        $weeklyKeluar = [];

        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $totalDays = $endOfMonth->day;

        $daysPerWeek = ceil($totalDays / 4);

        for ($i = 0; $i < 4; $i++) {
            $weekStart = $startOfMonth->copy()->addDays($i * $daysPerWeek);
            $weekEnd = $weekStart->copy()->addDays($daysPerWeek - 1);

            // Biar gak lewat dari akhir bulan
            if ($weekEnd->gt($endOfMonth)) {
                $weekEnd = $endOfMonth;
            }

            $weeklyLabels[] = 'Minggu ke-' . ($i + 1);
            $weeklyMasuk[] = Item_in::whereBetween('created_at', [$weekStart, $weekEnd])->sum('quantity');
            $weeklyKeluar[] = Item_out::whereBetween('created_at', [$weekStart, $weekEnd])->sum('quantity');
        }

        // ---  Bulanan (12 bulan dalam 1 tahun) ---
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

        // ---  Tahunan (5 tahun terakhir) ---
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

        // ---  Triwulan (setiap 3 bulan) ---
        $triwulanLabels = ['Triwulan 1', 'Triwulan 2', 'Triwulan 3', 'Triwulan 4'];
        $triwulanMasuk = [];
        $triwulanKeluar = [];

        for ($i = 0; $i < 4; $i++) {
            $start = Carbon::create(Carbon::now()->year, ($i * 3) + 1, 1)->startOfMonth();
            $end = $start->copy()->addMonths(2)->endOfMonth();

            $triwulanMasuk[] = Item_in::whereBetween('created_at', [$start, $end])->sum('quantity');
            $triwulanKeluar[] = Item_out::whereBetween('created_at', [$start, $end])->sum('quantity');
        }

        // ---  Semester (setiap 6 bulan) ---
        $semesterLabels = ['Semester 1', 'Semester 2'];
        $semesterMasuk = [];
        $semesterKeluar = [];

        for ($i = 0; $i < 2; $i++) {
            $start = Carbon::create(Carbon::now()->year, ($i * 6) + 1, 1)->startOfMonth();
            $end = $start->copy()->addMonths(5)->endOfMonth();

            $semesterMasuk[] = Item_in::whereBetween('created_at', [$start, $end])->sum('quantity');
            $semesterKeluar[] = Item_out::whereBetween('created_at', [$start, $end])->sum('quantity');
        }

        // === GROWTH ===
        $thisMonth = $monthlyMasuk[Carbon::now()->month - 1] ?? 0;
        $lastMonth = $monthlyMasuk[Carbon::now()->month - 2] ?? 0;
        $growth = ($lastMonth > 0)
            ? (($thisMonth - $lastMonth) / $lastMonth) * 100
            : 0;

        $lowStockItems = Item::where('stock', '<', 11)->orderBy('stock', 'asc')->get();

        $lastUpdateItemIn = Item_in::latest('updated_at')->value('updated_at');
        $lastUpdateExpired = Item_in::whereNotNull('expired_at')
            ->whereBetween('expired_at', [Carbon::now(), Carbon::now()->addDays(30)])
            ->latest('updated_at')
            ->value('updated_at');

        // Hitung total semua data
        $itemNow = Item::count();
        $supplierNow = Supplier::count();
        $userNow = User::count();

        // Hitung data yang ditambah hari ini
        $itemToday = Item::whereDate('created_at', today())->count();
        $supplierToday = Supplier::whereDate('created_at', today())->count();
        $userToday = User::whereDate('created_at', today())->count();

        // Hitung data yang ditambah kemarin
        $itemYesterday = Item::whereDate('created_at', today()->subDay())->count();
        $supplierYesterday = Supplier::whereDate('created_at', today()->subDay())->count();
        $userYesterday = User::whereDate('created_at', today()->subDay())->count();

        // Selisih antara hari ini dan kemarin
        $itemDiff = $itemToday - $itemYesterday;
        $supplierDiff = $supplierToday - $supplierYesterday;
        $userDiff = $userToday - $userYesterday;

        // Persentase perubahan
        $itemPercent = $itemYesterday > 0 ? round(($itemDiff / $itemYesterday) * 100, 1) : 0;
        $supplierPercent = $supplierYesterday > 0 ? round(($supplierDiff / $supplierYesterday) * 100, 1) : 0;
        $userPercent = $userYesterday > 0 ? round(($userDiff / $userYesterday) * 100, 1) : 0;

        return view('role.super_admin.dashboard', [
            'categories' => Category::count(),
            'item' => Item::count(),
            'suppliers' => Supplier::count(),
            'users' => User::count(),
            'item' => $itemNow,
            'suppliers' => $supplierNow,
            'users' => $userNow,

            'itemDiff' => $itemDiff,
            'itemPercent' => $itemPercent,
            'supplierDiff' => $supplierDiff,
            'supplierPercent' => $supplierPercent,
            'userDiff' => $userDiff,
            'userPercent' => $userPercent,

            'itemIns' => $itemIns,
            'itemOuts' => $itemOuts,
            'expiredSoon' => $expiredSoon,
            'topUsers' => $topUsers,

            // Chart data
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
            'triwulanLabels' => $triwulanLabels,
            'triwulanMasuk' => $triwulanMasuk,
            'triwulanKeluar' => $triwulanKeluar,
            'semesterLabels' => $semesterLabels,
            'semesterMasuk' => $semesterMasuk,
            'semesterKeluar' => $semesterKeluar,

            // Growth data
            'growth' => $growth,

            // Info tambahan
            'lastUpdateItemIn' => $lastUpdateItemIn,
            'lastUpdateExpired' => $lastUpdateExpired,
            'lowStockItems' => $lowStockItems,
        ]);
    }
}
