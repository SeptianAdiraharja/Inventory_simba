<?php

namespace App\Http\Controllers;

use App\Models\Item_in;
use App\Models\Item_out;
use App\Models\ExportLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\BarangMasukExport;
use App\Exports\BarangKeluarExport;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ExportController extends Controller
{
    /** 🔹 Filter berdasarkan rentang tanggal */
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

    /** 🔹 Filter berdasarkan periode (weekly, monthly, yearly) */
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

    /** 🔹 Halaman utama export log */
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
            $queryItem = $type === 'masuk'
                ? Item_in::with('item.unit','supplier')
                : Item_out::with('item.unit','supplier');

            $items = $this->filterByDateRange($queryItem, $startDate, $endDate)->get();
            $items->map(fn($row) => $row->total_price = $row->item->price * $row->quantity);
        }

        if ($request->has('filter_format') && in_array($request->filter_format, ['excel','pdf'])) {
            $query->where('format', $request->filter_format);
        }

        $logs = $query->get();

        return view('role.super_admin.exports.index', compact('items','logs','period','startDate','endDate','format'));
    }

    /** 🔹 Barang Masuk PDF */
    public function exportBarangMasukPdf(Request $request)
    {
        $period   = $request->query('period', 'weekly');
        $fileName = "barang_masuk_{$period}_" . now()->format('Ymd_His') . '.pdf';

        $query = Item_in::with('item.unit','supplier');
        $items = $this->filterByPeriod($query, $period)->get();
        $items->map(fn($r) => $r->total_price = $r->item->price * $r->quantity);

        $totalJumlah = $items->sum('quantity');
        $grandTotal  = $items->sum('total_price');

        $pdf = Pdf::loadView('role.super_admin.exports.barang_masuk_pdf', compact('items','period','totalJumlah','grandTotal'))
                  ->setPaper('a4', 'landscape');

        ExportLog::create([
            'super_admin_id' => Auth::id(),
            'type' => $period,
            'data_type' => 'masuk',
            'format' => 'pdf',
            'file_path' => "role/super_admin/exports/{$fileName}",
            'period' => $period,
        ]);

        return $pdf->download($fileName);
    }

    /** 🔹 Barang Keluar PDF */
    public function exportBarangKeluarPdf(Request $request)
    {
        $period   = $request->query('period', 'weekly');
        $fileName = "barang_keluar_{$period}_" . now()->format('Ymd_His') . '.pdf';

        $query = Item_out::with('item.unit','supplier');
        $items = $this->filterByPeriod($query, $period)->get();
        $items->map(fn($r) => $r->total_price = $r->item->price * $r->quantity);

        $totalJumlah = $items->sum('quantity');
        $grandTotal  = $items->sum('total_price');

        $pdf = Pdf::loadView('role.super_admin.exports.barang_keluar_pdf', compact('items','period','totalJumlah','grandTotal'))
                  ->setPaper('a4', 'landscape');

        ExportLog::create([
            'super_admin_id' => Auth::id(),
            'type' => $period,
            'data_type' => 'keluar',
            'format' => 'pdf',
            'file_path' => "role/super_admin/exports/{$fileName}",
            'period' => $period,
        ]);

        return $pdf->download($fileName);
    }

    /** 🔹 Custom Download (range tanggal & format dinamis) */
    public function download(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');
        $type      = $request->query('type', 'masuk');
        $format    = $request->query('format', 'excel');
        $period    = $startDate . " s/d " . $endDate;

        $query = $type === 'masuk'
            ? Item_in::with('item.unit','supplier')
            : Item_out::with('item.unit','supplier');

        $items = $this->filterByDateRange($query, $startDate, $endDate)->get();
        $items->map(fn($r) => $r->total_price = $r->item->price * $r->quantity);

        $totalJumlah = $items->sum('quantity');
        $grandTotal  = $items->sum('total_price');

        $fileName = "barang_{$type}_{$startDate}_to_{$endDate}_" . now()->format('Ymd_His');

        ExportLog::create([
            'super_admin_id' => Auth::id(),
            'type' => 'custom',
            'data_type' => $type,
            'format' => $format,
            'file_path' => "role/super_admin/exports/{$fileName}.{$format}",
            'period' => $period,
        ]);

        if ($format === 'excel') {
            return $type === 'masuk'
                ? Excel::download(new BarangMasukExport($items, $totalJumlah, $grandTotal), $fileName.'.xlsx')
                : Excel::download(new BarangKeluarExport($items, $totalJumlah, $grandTotal), $fileName.'.xlsx');
        }

        $pdf = $type === 'masuk'
            ? Pdf::loadView('role.super_admin.exports.barang_masuk_pdf', compact('items','startDate','endDate','period','totalJumlah','grandTotal'))
            : Pdf::loadView('role.super_admin.exports.barang_keluar_pdf', compact('items','startDate','endDate','period','totalJumlah','grandTotal'));

        return $pdf->setPaper('a4', 'landscape')->download($fileName.'.pdf');
    }

    /** 🔹 Hapus semua riwayat export */
    public function clearLogs()
    {
        try {
            ExportLog::query()->delete();
            return redirect()->route('super_admin.export.index')
                ->with('success', 'Riwayat export berhasil dibersihkan.');
        } catch (\Exception $e) {
            return redirect()->route('super_admin.export.index')
                ->with('error', 'Gagal menghapus riwayat export: ' . $e->getMessage());
        }
    }

    /** 🔹 Barang Masuk Excel (Improved mirip Blade tanpa kop & footer) */
    public function exportBarangMasukExcelImproved(Request $request)
    {
        $period = $request->query('period', 'weekly');
        $fileName = "barang_masuk_tanpa_kop_{$period}_" . now()->format('Ymd_His') . '.xlsx';

        $query = Item_in::with('item.unit','supplier');
        $items = $this->filterByPeriod($query, $period)->get();
        $items->map(fn($r) => $r->total_price = $r->item->price * $r->quantity);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Data Barang Masuk');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $headers = ['ID', 'Nama Barang', 'Satuan', 'Supplier', 'Jumlah', 'Total Harga (Rp)'];
        $sheet->fromArray($headers, null, 'A3');
        $sheet->getStyle('A3:F3')->getFont()->setBold(true);
        $sheet->getStyle('A3:F3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 4;
        foreach ($items as $item) {
            $sheet->setCellValue('A'.$row, $item->id);
            $sheet->setCellValue('B'.$row, $item->item->name);
            $sheet->setCellValue('C'.$row, optional($item->item->unit)->name);
            $sheet->setCellValue('D'.$row, optional($item->supplier)->name);
            $sheet->setCellValue('E'.$row, $item->quantity);
            $sheet->setCellValue('F'.$row, $item->total_price);
            $row++;
        }

        $sheet->setCellValue('E'.$row, 'Total');
        $sheet->setCellValue('F'.$row, $items->sum('total_price'));
        $sheet->getStyle('E'.$row.':F'.$row)->getFont()->setBold(true);

        foreach (range('A','F') as $col)
            $sheet->getColumnDimension($col)->setAutoSize(true);

        $sheet->getStyle('A3:F'.$row)->applyFromArray([
            'borders'=>['allBorders'=>['borderStyle'=>Border::BORDER_THIN]]
        ]);

        $temp = storage_path("app/public/{$fileName}");
        (new Xlsx($spreadsheet))->save($temp);
        return response()->download($temp)->deleteFileAfterSend(true);
    }

    /** 🔹 Barang Keluar Excel (Improved mirip Blade tanpa kop & footer) */
    public function exportBarangKeluarExcelImproved(Request $request)
    {
        $period = $request->query('period', 'weekly');
        $fileName = "barang_keluar_tanpa_kop_{$period}_" . now()->format('Ymd_His') . '.xlsx';

        $query = Item_out::with('item.unit','supplier');
        $items = $this->filterByPeriod($query, $period)->get();
        $items->map(fn($r) => $r->total_price = $r->item->price * $r->quantity);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Data Barang Keluar');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $headers = ['ID', 'Nama Barang', 'Satuan', 'Supplier', 'Jumlah', 'Total Harga (Rp)'];
        $sheet->fromArray($headers, null, 'A3');
        $sheet->getStyle('A3:F3')->getFont()->setBold(true);
        $sheet->getStyle('A3:F3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 4;
        foreach ($items as $item) {
            $sheet->setCellValue('A'.$row, $item->id);
            $sheet->setCellValue('B'.$row, $item->item->name);
            $sheet->setCellValue('C'.$row, optional($item->item->unit)->name);
            $sheet->setCellValue('D'.$row, optional($item->supplier)->name);
            $sheet->setCellValue('E'.$row, $item->quantity);
            $sheet->setCellValue('F'.$row, $item->total_price);
            $row++;
        }

        $sheet->setCellValue('E'.$row, 'Total');
        $sheet->setCellValue('F'.$row, $items->sum('total_price'));
        $sheet->getStyle('E'.$row.':F'.$row)->getFont()->setBold(true);

        foreach (range('A','F') as $col)
            $sheet->getColumnDimension($col)->setAutoSize(true);

        $sheet->getStyle('A3:F'.$row)->applyFromArray([
            'borders'=>['allBorders'=>['borderStyle'=>Border::BORDER_THIN]]
        ]);

        $temp = storage_path("app/public/{$fileName}");
        (new Xlsx($spreadsheet))->save($temp);
        return response()->download($temp)->deleteFileAfterSend(true);
    }
}
