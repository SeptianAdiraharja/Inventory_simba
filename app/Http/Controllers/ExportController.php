<?php

namespace App\Http\Controllers;

use App\Models\Item_in;
use App\Models\Item_out;
use App\Models\ExportLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportController extends Controller
{
    /**
     * Filter data berdasarkan period (weekly, monthly, yearly)
     */
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

        return $query; // default semua data
    }

    /**
     * Export Barang Masuk Excel
     */
    public function exportBarangMasukExcel(Request $request)
    {
        $period = $request->query('period', 'weekly');
        $fileName = "barang_masuk_{$period}_" . now()->format('Ymd_His') . '.xlsx';

        $query = Item_in::with('item');
        $items = $this->filterByPeriod($query, $period)->get();

        // Hitung total_price
        $items->map(function ($itemIn) {
            $itemIn->total_price = $itemIn->item->price * $itemIn->quantity;
            return $itemIn;
        });

        ExportLog::create([
            'super_admin_id' => Auth::id(),
            'type'   => $period,
            'format' => 'excel',
            'file_path' => 'exports/' . $fileName,
        ]);

        return Excel::download(new \App\Exports\BarangMasukExport($items), $fileName);
    }

    /**
     * Export Barang Masuk PDF
     */
    public function exportBarangMasukPdf(Request $request)
    {
        $period = $request->query('period', 'weekly');
        $fileName = "barang_masuk_{$period}_" . now()->format('Ymd_His') . '.pdf';

        $query = Item_in::with('item');
        $items = $this->filterByPeriod($query, $period)->get();

        // Hitung total_price
        $items->map(function ($itemIn) {
            $itemIn->total_price = $itemIn->item->price * $itemIn->quantity;
            return $itemIn;
        });

        $pdf = Pdf::loadView('exports.barang_masuk_pdf', compact('items', 'period'))
                  ->setPaper('a4', 'landscape');

        ExportLog::create([
            'super_admin_id' => Auth::id(),
            'type'   => $period,
            'format' => 'pdf',
            'file_path' => 'exports/' . $fileName,
        ]);

        return $pdf->download($fileName);
    }

    /**
     * Export Barang Keluar Excel
     */
    public function exportBarangKeluarExcel(Request $request)
    {
        $period = $request->query('period', 'weekly');
        $fileName = "barang_keluar_{$period}_" . now()->format('Ymd_His') . '.xlsx';

        $query = Item_out::with('item');
        $items = $this->filterByPeriod($query, $period)->get();

        // Hitung total_price
        $items->map(function ($itemOut) {
            $itemOut->total_price = $itemOut->item->price * $itemOut->quantity;
            return $itemOut;
        });

        ExportLog::create([
            'super_admin_id' => Auth::id(),
            'type'   => $period,
            'format' => 'excel',
            'file_path' => 'exports/' . $fileName,
        ]);

        return Excel::download(new \App\Exports\BarangKeluarExport($items), $fileName);
    }

    /**
     * Export Barang Keluar PDF
     */
    public function exportBarangKeluarPdf(Request $request)
    {
        $period = $request->query('period', 'weekly');
        $fileName = "barang_keluar_{$period}_" . now()->format('Ymd_His') . '.pdf';

        $query = Item_out::with('item');
        $items = $this->filterByPeriod($query, $period)->get();

        // Hitung total_price
        $items->map(function ($itemOut) {
            $itemOut->total_price = $itemOut->item->price * $itemOut->quantity;
            return $itemOut;
        });

        $pdf = Pdf::loadView('exports.barang_keluar_pdf', compact('items', 'period'))
                  ->setPaper('a4', 'landscape');

        ExportLog::create([
            'super_admin_id' => Auth::id(),
            'type'   => $period,
            'format' => 'pdf',
            'file_path' => 'exports/' . $fileName,
        ]);

        return $pdf->download($fileName);
    }
}
