<?php

namespace App\Http\Controllers;

use App\Models\Item_in;
use App\Models\Item_out;
use App\Models\ExportLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\BarangKeluarExport;
use App\Exports\BarangMasukExport;

class ExportController extends Controller
{
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

    public function index(Request $request)
    {
        $items = [];
        $query = ExportLog::orderBy('created_at', 'desc');
        $logs  = ExportLog::latest()->take(10)->get();

        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');
        $period    = $request->query('period'); // optional
        $format    = $request->query('format', 'excel'); // ⬅️ default excel

        if ($request->filled(['start_date','end_date'])) {
            $type = $request->query('type', 'masuk');

            $query = $type === 'masuk' ? Item_in::with('item') : Item_out::with('item');
            $items = $this->filterByDateRange($query, $startDate, $endDate)->get();

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
            'file_path' => 'exports/' . $fileName,
        ]);

        return Excel::download(new \App\Exports\BarangMasukExport($items), $fileName);
    }

    /** Export Barang Masuk PDF */
    public function exportBarangMasukPdf(Request $request)
    {
        $period   = $request->query('period', 'weekly');
        $fileName = "barang_masuk_{$period}_" . now()->format('Ymd_His') . '.pdf';

        $query = Item_in::with('item');
        $items = $this->filterByPeriod($query, $period)->get();

        $items->map(fn($row) => $row->total_price = $row->item->price * $row->quantity);

        $pdf = Pdf::loadView('exports.barang_masuk_pdf', compact('items', 'period'))
                  ->setPaper('a4', 'landscape');

        ExportLog::create([
            'super_admin_id' => Auth::id(),
            'type'      => $period,
            'data_type' => 'masuk',
            'format'    => 'pdf',
            'file_path' => 'exports/' . $fileName,
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
            'file_path' => 'exports/' . $fileName,
        ]);

        return Excel::download(new \App\Exports\BarangKeluarExport($items), $fileName);
    }

    /** Export Barang Keluar PDF */
    public function exportBarangKeluarPdf(Request $request)
    {
        $period   = $request->query('period', 'weekly');
        $fileName = "barang_keluar_{$period}_" . now()->format('Ymd_His') . '.pdf';

        $query = Item_out::with('item');
        $items = $this->filterByPeriod($query, $period)->get();

        $items->map(fn($row) => $row->total_price = $row->item->price * $row->quantity);

        $pdf = Pdf::loadView('exports.barang_keluar_pdf', compact('items', 'period'))
                  ->setPaper('a4', 'landscape');

        ExportLog::create([
            'super_admin_id' => Auth::id(),
            'type'      => $period,
            'data_type' => 'keluar',
            'format'    => 'pdf',
            'file_path' => 'exports/' . $fileName,
        ]);

        return $pdf->download($fileName);
    }

    /** Custom Download (pilih tanggal & jenis data) */
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
            'data_type' => $type,   // masuk / keluar
            'format'    => $format,
            'file_path' => "exports/{$fileName}.{$format}",
            'period' => $period,
        ]);

        if ($format === 'excel') {
            return $type === 'masuk'
                ? Excel::download(new BarangMasukExport($items), $fileName.'.xlsx')
                : Excel::download(new BarangKeluarExport($items), $fileName.'.xlsx');
        } else {
            $pdf = $type === 'masuk'
                ? Pdf::loadView('exports.barang_masuk_pdf', compact('items','startDate','endDate', 'period'))
                : Pdf::loadView('exports.barang_keluar_pdf', compact('items','startDate','endDate', 'period'));

            return $pdf->setPaper('a4', 'landscape')->download($fileName.'.pdf');
        }
    }
}
