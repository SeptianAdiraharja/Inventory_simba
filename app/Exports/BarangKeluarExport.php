<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithTitle;

class BarangKeluarExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $items;
    protected $totalJumlah;
    protected $grandTotal;

    public function __construct($items, $totalJumlah, $grandTotal)
    {
        $this->items = $items;
        $this->totalJumlah = $totalJumlah;
        $this->grandTotal = $grandTotal;
    }

    public function collection()
    {
        $rows = collect();

        foreach ($this->items as $i => $item) {
            $rows->push([
                $i + 1,
                $item->item->name,
                $item->user->name ?? '-',
                $item->created_at->format('d-m-Y H:i'),
                $item->quantity,
                $item->item->unit->name ?? '-',
                $item->item->price,
                $item->total_price,
            ]);
        }

        // Tambahkan total di akhir tabel
        $rows->push(['', '', '', 'TOTAL JUMLAH', $this->totalJumlah, '', 'TOTAL SEMUA HARGA', $this->grandTotal]);

        return $rows;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Barang',
            'Dikeluarkan Oleh',
            'Tanggal Keluar',
            'Jumlah',
            'Satuan Barang',
            'Harga Satuan (Rp)',
            'Total Harga (Rp)'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $rowCount = count($this->items) + 2;

        // Header style
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ]);

        // Semua border tabel
        $sheet->getStyle("A1:H{$rowCount}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ]);

        // Bold total
        $sheet->getStyle("D{$rowCount}:H{$rowCount}")->applyFromArray([
            'font' => ['bold' => true]
        ]);

        // Auto-size
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }

    public function title(): string
    {
        return 'Barang Keluar';
    }
}
