<?php

namespace App\Http\Controllers;

use App\Models\Item_in;
use App\Models\Item_out;
use App\Models\ExportLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\BarangKeluarExport;
use App\Exports\BarangMasukExport;
use App\Http\Controllers\Role\admin\BarangKeluarExportAdmin;



class ExportController extends Controller
{
    /** Filter by range tanggal */
    private function filterByDateRange($query, $startDate, $endDate)
    {
        if ($startDate && $endDate) {
            return $query->whereBetween('created_at', [
                $startDate . ' 00:00:00',
                $endDate . ' 23:59:59'
            ]);
        }
        return $query;
    }

    /** Filter by period (weekly, monthly, yearly) */
    private function filterByPeriod($query, $period)
    {
        if ($period === 'weekly') {
            return $query->whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ]);
        } elseif ($period === 'monthly') {
            return $query->whereYear('created_at', now()->year)
                         ->whereMonth('created_at', now()->month);
        } elseif ($period === 'yearly') {
            return $query->whereYear('created_at', now()->year);
        }
        return $query;
    }

    /** Index export log */
    public function index(Request $request)
    {
        $items = [];
        $query = ExportLog::orderBy('created_at', 'desc');

        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');
        $period    = $request->query('period');
        $format    = $request->query('format', 'excel');

        if ($request->filled(['start_date','end_date'])) {
            $type = $request->query('type', 'masuk');
            $queryItem = $type === 'masuk' ? Item_in::with('item') : Item_out::with('item');
            $items = $this->filterByDateRange($queryItem, $startDate, $endDate)->get();

            $items->map(function ($row) {
                $row->total_price = $row->item->price * $row->quantity;
                return $row;
            });
        }

        if ($request->has('filter_format') && in_array($request->filter_format, ['excel','pdf'])) {
            $query->where('format', $request->filter_format);
        }

        $logs = $query->get();

        return view('role.super_admin.exports.index', compact('items','logs','period','startDate','endDate','format'));
    }

    /** Export Barang Masuk Excel */
    public function exportBarangMasukExcel(Request $request)
    {
        $period   = $request->query('period', 'weekly');
        $fileName = "barang_masuk_{$period}_" . now()->format('Ymd_His') . '.xlsx';

        $query = Item_in::with('item');
        $items = $this->filterByPeriod($query, $period)->get();
        $items->map(fn($row) => $row->total_price = $row->item->price * $row->quantity);

        ExportLog::create([
            'super_admin_id' => Auth::id(),
            'type'      => $period,
            'data_type' => 'masuk',
            'format'    => 'excel',
            'file_path' => "role/super_admin/exports/{$fileName}",
            'period'    => $period,
        ]);

        return Excel::download(new BarangMasukExport($items), $fileName);
    }

    /** Export Barang Masuk PDF */
    public function exportBarangMasukPdf(Request $request)
    {
        $period   = $request->query('period', 'weekly');
        $fileName = "barang_masuk_{$period}_" . now()->format('Ymd_His') . '.pdf';

        $query = Item_in::with('item');
        $items = $this->filterByPeriod($query, $period)->get();
        $items->map(fn($row) => $row->total_price = $row->item->price * $row->quantity);

        $pdf = Pdf::loadView('role.super_admin.exports.barang_masuk_pdf', compact('items', 'period'))
                  ->setPaper('a4', 'landscape');

        ExportLog::create([
            'super_admin_id' => Auth::id(),
            'type'      => $period,
            'data_type' => 'masuk',
            'format'    => 'pdf',
            'file_path' => "role/super_admin/exports/{$fileName}",
            'period'    => $period,
        ]);

        return $pdf->download($fileName);
    }

    /** Export Barang Keluar Excel */
    public function exportBarangKeluarExcel(Request $request)
    {
        $period   = $request->query('period', 'weekly');
        $fileName = "barang_keluar_{$period}_" . now()->format('Ymd_His') . '.xlsx';

        $query = Item_out::with('item');
        $items = $this->filterByPeriod($query, $period)->get();
        $items->map(fn($row) => $row->total_price = $row->item->price * $row->quantity);

        ExportLog::create([
            'super_admin_id' => Auth::id(),
            'type'      => $period,
            'data_type' => 'keluar',
            'format'    => 'excel',
            'file_path' => "role/super_admin/exports/{$fileName}",
            'period'    => $period,
        ]);

        return Excel::download(new BarangKeluarExport($items), $fileName);
    }

    /** Export Barang Keluar PDF */
    public function exportBarangKeluarPdf(Request $request)
    {
        $period   = $request->query('period', 'weekly');
        $fileName = "barang_keluar_{$period}_" . now()->format('Ymd_His') . '.pdf';

        $query = Item_out::with('item');
        $items = $this->filterByPeriod($query, $period)->get();
        $items->map(fn($row) => $row->total_price = $row->item->price * $row->quantity);

        $pdf = Pdf::loadView('role.super_admin.exports.barang_keluar_pdf', compact('items', 'period'))
                  ->setPaper('a4', 'landscape');

        ExportLog::create([
            'super_admin_id' => Auth::id(),
            'type'      => $period,
            'data_type' => 'keluar',
            'format'    => 'pdf',
            'file_path' => "role/super_admin/exports/{$fileName}",
            'period'    => $period,
        ]);

        return $pdf->download($fileName);
    }

    /** Custom Download by Date Range */
    public function download(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');
        $type      = $request->query('type', 'masuk');
        $format    = $request->query('format', 'excel');

        $period = $startDate . " s/d " . $endDate;

        $query = $type === 'masuk' ? Item_in::with('item') : Item_out::with('item');
        $items = $this->filterByDateRange($query, $startDate, $endDate)->get();
        $items->map(fn($row) => $row->total_price = $row->item->price * $row->quantity);

        $fileName = "barang_{$type}_{$startDate}_to_{$endDate}_" . now()->format('Ymd_His');

        ExportLog::create([
            'super_admin_id' => Auth::id(),
            'type'      => 'custom',
            'data_type' => $type,
            'format'    => $format,
            'file_path' => "role/super_admin/exports/{$fileName}.{$format}",
            'period'    => $period,
        ]);

        if ($format === 'excel') {
            return $type === 'masuk'
                ? Excel::download(new BarangMasukExport($items), $fileName.'.xlsx')
                : Excel::download(new BarangKeluarExport($items), $fileName.'.xlsx');
        } else {
            $pdf = $type === 'masuk'
                ? Pdf::loadView('role.super_admin.exports.barang_masuk_pdf', compact('items','startDate','endDate','period'))
                : Pdf::loadView('role.super_admin.exports.barang_keluar_pdf', compact('items','startDate','endDate','period'));

            return $pdf->setPaper('a4', 'landscape')->download($fileName.'.pdf');
        }
    }

    public function clearLogs()
    {
        try {
            // hapus semua data log export
            ExportLog::query()->delete();

            return redirect()->route('super_admin.export.index')
                ->with('success', 'Riwayat export berhasil dibersihkan.');
        } catch (\Exception $e) {
            return redirect()->route('super_admin.export.index')
                ->with('error', 'Gagal menghapus riwayat export: ' . $e->getMessage());
        }
    }

    public function exportOut(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $format    = $request->input('format', 'pdf');

        // Ambil log export barang keluar
        $exports = DB::table('export_logs')
            ->where('data_type', 'keluar')
            ->orderBy('created_at', 'desc')
            ->get();

        // Default: kosong
        $items = collect();

        // Jika user memilih tanggal, ambil data item keluar
        if ($startDate && $endDate) {
            $items = \App\Models\Item_out::with('item')
                ->whereBetween('created_at', [
                    $startDate . ' 00:00:00',
                    $endDate . ' 23:59:59'
                ])
                ->get()
                ->map(function ($row) {
                    $row->total_price = $row->item->price * $row->quantity;
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

        $data = Item_out::with('item', 'cart.user')
            ->whereBetween('released_at', [$start, $end])
            ->get();

        // Gunakan package seperti Maatwebsite\Excel untuk export
        return Excel::download(new BarangKeluarExportAdmin($data), "barang_keluar_{$start}_{$end}.xlsx");
    }

    public function exportBarangKeluarPdfAdmin(Request $request)
    {
        $start = date('Y-m-d', strtotime($request->query('start_date')));
        $end   = date('Y-m-d', strtotime($request->query('end_date')));

        $period = "Periode: " . date('d/m/Y', strtotime($start)) . " - " . date('d/m/Y', strtotime($end));

        $data = Item_out::with(['item', 'cart.user'])
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

        $pdf = PDF::loadView('role.admin.export.barang_keluar_pdf', [
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
