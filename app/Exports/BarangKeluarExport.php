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
            // 🔹 Deteksi apakah data berasal dari Pegawai atau Guest
            $tipe = isset($item->approver) && $item->approver ? 'Pegawai' : 'Guest';

            // 🔹 Ambil nama penerima
            $penerima = $item->approver->name
                ?? $item->guest->name
                ?? '-';

            $rows->push([
                $i + 1,
                $item->item->name ?? '-',
                $tipe,
                $penerima,
                optional($item->created_at)->format('d-m-Y H:i') ?? '-',
                $item->quantity ?? 0,
                $item->item->unit->name ?? '-',
                $item->item->price ?? 0,
                $item->total_price ?? 0,
            ]);
        }

        // 🔹 Tambahkan total di bawah tabel
        $rows->push([
            '', '', '', 'TOTAL JUMLAH', $this->totalJumlah, '', 
            'TOTAL SEMUA HARGA (Rp)', $this->grandTotal, ''
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Barang',
            'role',
            'Penerima',
            'Tanggal Keluar',
            'Jumlah',
            'Satuan Barang',
            'Harga Satuan (Rp)',
            'Total Harga (Rp)',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $rowCount = count($this->items) + 2;

        // 🔹 Style Header
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => 'center',
                'vertical'   => 'center'
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
            ],
        ]);

        // 🔹 Border seluruh isi tabel
        $sheet->getStyle("A1:I{$rowCount}")->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
            ],
        ]);

        // 🔹 Bold untuk baris total
        $sheet->getStyle("D{$rowCount}:I{$rowCount}")->applyFromArray([
            'font' => ['bold' => true],
        ]);

        // 🔹 Auto-size setiap kolom
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }

    public function title(): string
    {
        return 'Barang Keluar';
    }
}
