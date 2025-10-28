<?php

namespace App\Http\Controllers\Role\Admin;

use App\Models\Item_out;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BarangKeluarExportAdmin implements FromCollection, WithHeadings
{



    public function cetakLaporanBarangKeluar(Request $request)
    {
        $items = Item_out::with(['item', 'cart.user', 'guestCart.guest'])->get();

        $pdf = Pdf::loadView('role.admin.export.barang_keluar_pdf', [
            'items' => $items,
            'period' => $request->period,
            'startDate' => $request->startDate,
            'endDate' => $request->endDate,
        ])->setPaper('a4', 'landscape');

        // =========================================
        // 🧩 Tambahkan footer (nomor halaman & tanggal)
        // =========================================
        $pdf->output();
        $dompdf = $pdf->getDomPDF();
        $canvas = $dompdf->getCanvas();
        $font = $dompdf->getFontMetrics()->get_font("DejaVu Sans", "normal");

        // 📅 Kiri bawah → tanggal cetak
        $canvas->page_text(40, 560, "Dicetak pada: " . Carbon::now()->format('d-m-Y H:i'), $font, 10, [0, 0, 0]);

        // 📄 Kanan bawah → nomor halaman
        $canvas->page_text(720, 560, "Halaman {PAGE_NUM} dari {PAGE_COUNT}", $font, 10, [0, 0, 0]);

        return $pdf->stream('Laporan_Barang_Keluar.pdf');
    }

    /**
     * Mengambil data barang keluar untuk diexport ke Excel.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Item_out::with('item')
            ->get()
            ->map(function ($row) {
                return [
                    'ID'             => $row->id,
                    'Nama Barang'    => $row->item->name ?? '-',
                    'Jumlah'         => $row->quantity,
                    'Tanggal Keluar' => $row->created_at ? $row->created_at->format('d-m-Y') : '-',
                ];
            });
    }

    /**
     * Menentukan header kolom di file Excel.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Nama Barang',
            'Jumlah',
            'Tanggal Keluar',
        ];
    }
}
