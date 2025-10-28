<?php

namespace App\Http\Controllers;

use App\Models\Item_in;
use App\Models\Item_out;
use App\Models\ExportLog;
use App\Models\Guest_carts_item;
use App\Models\KopSurat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BarangMasukExport;
use App\Exports\BarangKeluarExport;
use App\Http\Controllers\Role\admin\BarangKeluarExportAdmin;

class ExportController extends Controller
{
    /** 🔹 Hitung endDate berdasarkan startDate + periode */
    private function calculateEndDate($startDate, $period)
    {
        if (!$startDate) return null;

        $start = \Carbon\Carbon::parse($startDate);

        return match ($period) {
            'weekly'  => $start->copy()->addWeek()->format('Y-m-d'),
            'monthly' => $start->copy()->addMonth()->format('Y-m-d'),
            'yearly'  => $start->copy()->addYear()->format('Y-m-d'),
            default   => $start->copy()->format('Y-m-d'),
        };
    }

    /** 🔹 Filter berdasarkan rentang tanggal */

    private function filterByDateRange($query, $startDate, $endDate)
    {
        if ($startDate && $endDate) {
            return $query->whereBetween('created_at', [
                $startDate . ' 00:00:00',
                $endDate   . ' 23:59:59'
            ]);
        }
        return $query;
    }

    /** 🔹 Halaman utama export */
    public function index(Request $request)
    {
        $items = collect();
        $logs  = ExportLog::orderBy('created_at', 'desc')->get();

        $startDate = $request->query('start_date');
        $period    = $request->query('period', 'weekly');
        $type      = $request->query('type', 'masuk');
        $format    = $request->query('format', 'excel');

        $endDate = $this->calculateEndDate($startDate, $period);

        if ($startDate && $endDate) {
            // 🔹 Barang Masuk
            if ($type === 'masuk') {
                $items = Item_in::with('item.unit', 'supplier')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->get()
                    ->map(function ($row) {
                        $row->total_price = ($row->item->price ?? 0) * ($row->quantity ?? 0);
                        return $row;
                    });
            }

            // 🔹 Barang Keluar
            elseif ($type === 'keluar') {
            $pegawaiItems = Item_out::with(['item.unit', 'cart.user'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get()
                ->map(function ($row) {
                    $row->role        = 'Pegawai';
                    $row->dikeluarkan = 'Petugas Gudang';
                    $row->penerima    = $row->cart->user->name ?? '-';
                    $row->total_price = ($row->item->price ?? 0) * ($row->quantity ?? 0);
                    return $row;
                });

            $guestItems = Guest_carts_item::with(['item.unit', 'guestCart.guest'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get()
                ->map(function ($row) {
                    $row->role        = 'Guest';
                    $row->dikeluarkan = 'Petugas Gudang';
                    $row->penerima    = $row->guestCart->guest->name ?? '-';
                    $row->total_price = ($row->item->price ?? 0) * ($row->quantity ?? 0);
                    return $row;
                });

            $items = $pegawaiItems->concat($guestItems)->sortBy('created_at')->values();
        }


            // 🔹 Semua Data
            elseif ($type === 'all') {
                $barangMasuk = Item_in::with('item.unit', 'supplier')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->get()
                    ->map(function ($row) {
                        $row->role        = 'Supplier';
                        $row->dikeluarkan = $row->supplier->name ?? '-';
                        $row->penerima    = '-';
                        $row->total_price = ($row->item->price ?? 0) * ($row->quantity ?? 0);
                        return $row;
                    });

                $pegawaiItems = Item_out::with(['item.unit', 'approver'])
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->get()
                    ->map(function ($row) {
                        $row->role        = 'Pegawai';
                        $row->dikeluarkan = $row->approver->name ?? 'Petugas Gudang';
                        $row->penerima    = $row->approver->name ?? '-';
                        $row->total_price = ($row->item->price ?? 0) * ($row->quantity ?? 0);
                        return $row;
                    });

                $guestItems = Guest_carts_item::with(['item.unit', 'guestCart.guest'])
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->get()
                    ->map(function ($row) {
                        $row->role        = 'Guest';
                        $row->dikeluarkan = 'Petugas Gudang';
                        $row->penerima    = $row->guestCart->guest->name ?? '-';
                        $row->total_price = ($row->item->price ?? 0) * ($row->quantity ?? 0);
                        return $row;
                    });

                $items = $barangMasuk->concat($pegawaiItems)->concat($guestItems)->sortBy('created_at')->values();
            }
        }
            $kopSurat = KopSurat::all();
        return view('role.super_admin.exports.index', compact(
            'items', 'logs', 'period', 'startDate', 'endDate', 'format', 'type', 'kopSurat'
        ));
    }


    /** 🔹 Download data */
    public function download(Request $request)
    {
        // 🔹 Cek pilihan kop surat
        if (!$request->has('kop_surat')) {
            return back()->with('warning', 'Silakan pilih kop surat terlebih dahulu sebelum mengunduh.');
        }

        $kopSuratId = $request->input('kop_surat');
        $kopSurat   = KopSurat::find($kopSuratId);

        if (!$kopSurat) {
            return back()->with('warning', 'Kop surat yang dipilih tidak ditemukan.');
        }

        $startDate = $request->query('start_date');
        $period    = $request->query('period', 'weekly');
        $type      = $request->query('type', 'masuk');
        $format    = $request->query('format', 'excel');
        $endDate   = $this->calculateEndDate($startDate, $period);
        $periodeText = "{$startDate} s/d {$endDate}";

        $controllerData = $this->index($request)->getData();
        $items = $controllerData['items'] ?? collect();

        $totalJumlah = $items->sum('quantity');
        $grandTotal  = $items->sum('total_price');
        $fileName    = "barang_{$type}{$startDate}_to{$endDate}_" . now()->format('Ymd_His');

        ExportLog::create([
            'super_admin_id' => Auth::id(),
            'type'           => $period,
            'data_type'      => $type,
            'format'         => $format,
            'file_path'      => "role/super_admin/exports/{$fileName}.{$format}",
            'period'         => $periodeText,
        ]);

        if ($format === 'excel') {
            return $type === 'masuk'
                ? Excel::download(new BarangMasukExport($items, $totalJumlah, $grandTotal), "{$fileName}.xlsx")
                : Excel::download(new BarangKeluarExport($items, $totalJumlah, $grandTotal), "{$fileName}.xlsx");
        }

        $options = [
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => true,
            'isRemoteEnabled' => true
        ];

        $pdf = ($type === 'masuk')
            ? Pdf::loadView('role.super_admin.exports.barang_masuk_pdf', compact(
                'items','startDate','endDate','periodeText','totalJumlah','grandTotal','kopSurat'
            ))->setOptions($options)
            : Pdf::loadView('role.super_admin.exports.barang_keluar_pdf', compact(
                'items','startDate','endDate','periodeText','totalJumlah','grandTotal','kopSurat'
            ))->setOptions($options);

        return $pdf->setPaper('a4', 'landscape')->download("{$fileName}.pdf");

    }

    /** 🔹 Bersihkan log */
    public function clearLogs()
    {
        ExportLog::truncate();
        return redirect()->route('super_admin.export.index')
            ->with('success', 'Riwayat export berhasil dibersihkan.');
    }

    // ===============================================================
    // 🔹 BAGIAN ADMIN (Tetap dipertahankan)
    // ===============================================================

    public function exportOut(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $format    = $request->input('format', 'pdf');

        $exports = DB::table('export_logs')
            ->where('data_type', 'keluar')
            ->orderBy('created_at', 'desc')
            ->get();

        $items = collect();

        if ($startDate && $endDate) {
            $items = Item_out::with('item', 'approver')
                ->whereBetween('created_at', [
                    $startDate . ' 00:00:00',
                    $endDate . ' 23:59:59'
                ])
                ->get()
                ->map(function ($row) {
                    $row->total_price = ($row->item->price ?? 0) * ($row->quantity ?? 0);
                    return $row;
                });
        }

        return view('role.admin.barangkeluar', [
            'exports'   => $exports,
            'items'     => $items,
            'startDate' => $startDate,
            'endDate'   => $endDate,
            'format'    => $format,
        ]);
    }

    public function clearOutHistory()
    {
        DB::table('export_logs')->where('data_type', 'keluar')->delete();
        return redirect()->back()->with('success', 'Riwayat export barang keluar berhasil dibersihkan.');
    }

    public function exportBarangKeluarExcelAdmin(Request $request)
    {
        $start = $request->query('start_date');
        $end   = $request->query('end_date');

        $data = Item_out::with('item', 'cart.user', 'approver')
            ->whereBetween('released_at', [$start, $end])
            ->get();

        return Excel::download(new BarangKeluarExportAdmin($data), "barang_keluar_{$start}_{$end}.xlsx");
    }

    public function exportBarangKeluarPdfAdmin(Request $request)
    {
        $start = date('Y-m-d', strtotime($request->query('start_date')));
        $end   = date('Y-m-d', strtotime($request->query('end_date')));

        $period = "Periode: " . date('d/m/Y', strtotime($start)) . " - " . date('d/m/Y', strtotime($end));

        $data = Item_out::with(['item', 'cart.user', 'approver'])
            ->whereBetween(DB::raw('DATE(released_at)'), [$start, $end])
            ->orWhereBetween(DB::raw('DATE(created_at)'), [$start, $end])
            ->get();

        if ($data->isEmpty()) {
            return back()->with('warning', 'Tidak ada data barang keluar pada periode ini.');
        }

        $data->map(function ($row) {
            $row->released_date = \Carbon\Carbon::parse($row->released_at ?? $row->created_at)->format('d-m-Y');
            return $row;
        });

        $pdf = Pdf::loadView('role.admin.export.barang_keluar_pdf', [
            'items' => $data,
            'period' => $period,
        ]);

        $fileName = "barang_keluar_{$start}_{$end}.pdf";

        ExportLog::create([
            'super_admin_id' => Auth::id(),
            'data_type'      => 'keluar',
            'format'         => 'pdf',
            'file_path'      => "exports/{$fileName}",
            'period'         => $period,
        ]);

        return $pdf->download($fileName);
    }
}