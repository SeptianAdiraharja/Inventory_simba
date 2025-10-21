<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Guest;
use App\Models\Item_in;
use App\Models\Item_out;
use App\Models\Item_out_guest;
use App\Models\Guest_carts_item;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SuperAdminController extends Controller
{
    public function index()
    {
        /**
         * ============================
         * 🔹 DATA USER DAN GUEST TERATAS
         * ============================
         */
        $topUsers = Item_out::select(
                'users.id',
                'users.name',
                'users.email',
                'users.role',
                DB::raw('COUNT(item_outs.id) as total_out')
            )
            ->join('carts', 'item_outs.cart_id', '=', 'carts.id')
            ->join('users', 'carts.user_id', '=', 'users.id')
            ->groupBy('users.id', 'users.name', 'users.email', 'users.role')
            ->orderByDesc('total_out')
            ->take(5)
            ->get();

        $topGuests = Item_out_guest::select(
                'guests.id',
                'guests.name',
                DB::raw('NULL as email'),
                DB::raw('"Guest" as role'),
                DB::raw('COUNT(item_out_guests.id) as total_out')
            )
            ->join('guests', 'item_out_guests.guest_id', '=', 'guests.id')
            ->groupBy('guests.id', 'guests.name')
            ->orderByDesc('total_out')
            ->take(5)
            ->get();

        $topUsers = $topUsers->concat($topGuests)->sortByDesc('total_out')->take(5)->values();

        /**
         * ============================
         * 🔹 DATA TABEL TERBARU
         * ============================
         */
        $itemIns = Item_in::with('item')->latest()->take(5)->get();

        $itemOutsUser = Item_out::with(['item', 'cart.user'])
            ->latest()->take(5)->get()
            ->map(function ($out) {
                $out->source = 'user';
                return $out;
            });

        $itemOutsGuest = Item_out_guest::with('guest')
            ->latest()->take(5)->get()
            ->map(function ($out) {
                $out->source = 'guest';
                return $out;
            });

        $itemOuts = $itemOutsUser->concat($itemOutsGuest)
            ->sortByDesc('created_at')
            ->take(5)
            ->values();

        /**
         * ============================
         * 🔹 BARANG HAMPIR KEDALUARSA
         * ============================
         */
        
        $expiredSoon = Item_in::whereNotNull('expired_at')
        ->whereBetween('expired_at', [Carbon::now(), Carbon::now()->addDays(30)])
        ->with('item')
        ->get()
        ->map(function ($itemIn) {
            $itemIn->quantity = $itemIn->item->stock ?? 0;
            return $itemIn;
        });

        /**
         * ============================
         * 🔹 PERHITUNGAN JUMLAH KELUAR (DARI GUEST CART)
         * ============================
         */
        $sumGuestQuantity = function ($query) {
            return Guest_carts_item::whereIn('id', $query->pluck('guest_cart_item_id'))
                ->sum('quantity');
        };

        /**
         * ============================
         * 🔹 GRAFIK MASUK & KELUAR
         * ============================
         */

        // --- Harian
        $dailyLabels = [];
        $dailyMasuk = [];
        $dailyKeluar = [];
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        for ($date = $startOfWeek->copy(); $date->lte($endOfWeek); $date->addDay()) {
            $dailyLabels[] = $date->format('D');
            $dailyMasuk[] = Item_in::whereDate('created_at', $date)->sum('quantity') ?? 0;

            // Pegawai
            $pegawaiOut = Item_out::whereDate('created_at', $date)->sum('quantity') ?? 0;
            // Guest
            $guestOut = Guest_carts_item::whereDate('created_at', $date)->sum('quantity') ?? 0;

            $dailyKeluar[] = $pegawaiOut + $guestOut;
        }

        // --- Mingguan (berdasarkan minggu kalender asli)
        $weeklyLabels = [];
        $weeklyMasuk = [];
        $weeklyKeluar = [];

        $startOfMonth = Carbon::now()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $endOfMonth = Carbon::now()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        for ($weekStart = $startOfMonth->copy(); $weekStart->lte($endOfMonth); $weekStart->addWeek()) {
            $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

            $weeklyLabels[] = 'Minggu ' . $weekStart->format('W'); // W = nomor minggu dalam tahun
            $weeklyMasuk[] = Item_in::whereBetween('created_at', [$weekStart, $weekEnd])->sum('quantity') ?? 0;

            $pegawaiOut = Item_out::whereBetween('created_at', [$weekStart, $weekEnd])->sum('quantity') ?? 0;
            $guestOut = Guest_carts_item::whereBetween('created_at', [$weekStart, $weekEnd])->sum('quantity') ?? 0;

            $weeklyKeluar[] = $pegawaiOut + $guestOut;
        }

        // --- Bulanan
        $monthlyLabels = [];
        $monthlyMasuk = [];
        $monthlyKeluar = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlyLabels[] = Carbon::create()->month($m)->format('M');
            $monthlyMasuk[] = Item_in::whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', $m)->sum('quantity') ?? 0;

            $pegawaiOut = Item_out::whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', $m)->sum('quantity') ?? 0;
            $guestOut = Guest_carts_item::whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', $m)->sum('quantity') ?? 0;

            $monthlyKeluar[] = $pegawaiOut + $guestOut;
        }

        // --- Tahunan
        $yearlyLabels = [];
        $yearlyMasuk = [];
        $yearlyKeluar = [];
        $startYear = Carbon::now()->year - 4;
        $endYear = Carbon::now()->year;
        for ($y = $startYear; $y <= $endYear; $y++) {
            $yearlyLabels[] = $y;
            $yearlyMasuk[] = Item_in::whereYear('created_at', $y)->sum('quantity') ?? 0;

            $pegawaiOut = Item_out::whereYear('created_at', $y)->sum('quantity') ?? 0;
            $guestOut = Guest_carts_item::whereYear('created_at', $y)->sum('quantity') ?? 0;

            $yearlyKeluar[] = $pegawaiOut + $guestOut;
        }

        // --- Triwulan
        $triwulanLabels = ['Triwulan 1', 'Triwulan 2', 'Triwulan 3', 'Triwulan 4'];
        $triwulanMasuk = [];
        $triwulanKeluar = [];
        for ($i = 0; $i < 4; $i++) {
            $start = Carbon::create(Carbon::now()->year, ($i * 3) + 1, 1)->startOfMonth();
            $end = $start->copy()->addMonths(2)->endOfMonth();

            $pegawaiOut = Item_out::whereBetween('created_at', [$start, $end])->sum('quantity') ?? 0;
            $guestOut = Guest_carts_item::whereBetween('created_at', [$start, $end])->sum('quantity') ?? 0;

            $triwulanMasuk[] = Item_in::whereBetween('created_at', [$start, $end])->sum('quantity') ?? 0;
            $triwulanKeluar[] = $pegawaiOut + $guestOut;
        }

        // --- Semester
        $semesterLabels = ['Semester 1', 'Semester 2'];
        $semesterMasuk = [];
        $semesterKeluar = [];
        for ($i = 0; $i < 2; $i++) {
            $start = Carbon::create(Carbon::now()->year, ($i * 6) + 1, 1)->startOfMonth();
            $end = $start->copy()->addMonths(5)->endOfMonth();

            $pegawaiOut = Item_out::whereBetween('created_at', [$start, $end])->sum('quantity') ?? 0;
            $guestOut = Guest_carts_item::whereBetween('created_at', [$start, $end])->sum('quantity') ?? 0;

            $semesterMasuk[] = Item_in::whereBetween('created_at', [$start, $end])->sum('quantity') ?? 0;
            $semesterKeluar[] = $pegawaiOut + $guestOut;
        }

        /**
         * ============================
         * 🔹 GROWTH
         * ============================
         */
        $thisMonth = $monthlyMasuk[Carbon::now()->month - 1] ?? 0;
        $lastMonth = $monthlyMasuk[Carbon::now()->month - 2] ?? 0;
        $growth = ($lastMonth > 0)
            ? (($thisMonth - $lastMonth) / $lastMonth) * 100
            : 0;

        /**
         * ============================
         * 🔹 INFORMASI TAMBAHAN
         * ============================
         */
        $lowStockItems = Item::where('stock', '<', 11)->orderBy('stock', 'asc')->get();

        $lastUpdateItemIn = Item_in::latest('updated_at')->value('updated_at');
        $lastUpdateExpired = Item_in::whereNotNull('expired_at')
            ->whereBetween('expired_at', [Carbon::now(), Carbon::now()->addDays(30)])
            ->latest('updated_at')
            ->value('updated_at');

        /**
         * ============================
         * 🔹 JUMLAH DATA
         * ============================
         */
        $itemNow = Item::count();
        $supplierNow = Supplier::count();
        $userNow = User::count();
        $guestNow = Guest::count();

        // Hari ini
        $itemToday = Item::whereDate('created_at', today())->count();
        $supplierToday = Supplier::whereDate('created_at', today())->count();
        $userToday = User::whereDate('created_at', today())->count();
        $guestToday = Guest::whereDate('created_at', today())->count();

        // Kemarin
        $itemYesterday = Item::whereDate('created_at', today()->subDay())->count();
        $supplierYesterday = Supplier::whereDate('created_at', today()->subDay())->count();
        $userYesterday = User::whereDate('created_at', today()->subDay())->count();
        $guestYesterday = Guest::whereDate('created_at', today()->subDay())->count();

        // Selisih & Persentase
        $itemDiff = $itemToday - $itemYesterday;
        $supplierDiff = $supplierToday - $supplierYesterday;
        $userDiff = $userToday - $userYesterday;
        $guestDiff = $guestToday - $guestYesterday;

        $itemPercent = $itemYesterday > 0 ? round(($itemDiff / $itemYesterday) * 100, 1) : 0;
        $supplierPercent = $supplierYesterday > 0 ? round(($supplierDiff / $supplierYesterday) * 100, 1) : 0;
        $userPercent = $userYesterday > 0 ? round(($userDiff / $userYesterday) * 100, 1) : 0;
        $guestPercent = $guestYesterday > 0 ? round(($guestDiff / $guestYesterday) * 100, 1) : 0;

        /**
         * ============================
         * 🔹 RETURN KE VIEW
         * ============================
         */
        return view('role.super_admin.dashboard', [
            'categories' => Category::count(),
            'item' => $itemNow,
            'suppliers' => $supplierNow,
            'users' => $userNow,
            'guests' => $guestNow,

            'itemDiff' => $itemDiff,
            'itemPercent' => $itemPercent,
            'supplierDiff' => $supplierDiff,
            'supplierPercent' => $supplierPercent,
            'userDiff' => $userDiff,
            'userPercent' => $userPercent,
            'guestDiff' => $guestDiff,
            'guestPercent' => $guestPercent,

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
            'growth' => $growth,
            'lastUpdateItemIn' => $lastUpdateItemIn,
            'lastUpdateExpired' => $lastUpdateExpired,
            'lowStockItems' => $lowStockItems,
        ]);
    }
}
