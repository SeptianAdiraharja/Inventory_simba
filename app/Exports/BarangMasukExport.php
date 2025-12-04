<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class BarangMasukExport implements FromCollection, WithHeadings, WithStyles, WithEvents
{
    protected $items;
    protected $totalJumlah;
    protected $grandTotal;
    protected $rowNumber = 1;

    public function __construct($items, $totalJumlah, $grandTotal)
    {
        $this->items = $items;
        $this->totalJumlah = $totalJumlah;
        $this->grandTotal = $grandTotal;
    }

    public function collection()
    {
        $this->rowNumber = 1;

        return $this->items->map(function ($row) {

            return [
                $this->rowNumber++,
                $row->item->name ?? '-',
                $row->supplier->name ?? '-',
                $row->created_at ? $row->created_at->format('d-m-Y H:i') : '-',
                $row->quantity ?? 0,
                $row->item->unit->name ?? '-',
                'Rp ' . number_format($row->item->price ?? 0, 0, ',', '.'),
                'Rp ' . number_format(($row->item->price ?? 0) * ($row->quantity ?? 0), 0, ',', '.'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Barang',
            'Supplier',
            'Tanggal Masuk',
            'Jumlah',
            'Satuan',
            'Harga Satuan (Rp)',
            'Total Harga (Rp)',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // HEADER STYLE
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FF9800']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ],

            // BORDER DATA
            'A2:H' . (count($this->items) + 1) => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'DDDDDD']
                    ]
                ]
            ],

            // TOTAL JML
            (count($this->items) + 2) => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFE0B2']
                ]
            ],

            // GRAND TOTAL
            (count($this->items) + 3) => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFCC80']
                ]
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $rowCount = count($this->items);
                $startRow = $rowCount + 2; // baris setelah data

                // ============================
                // TOTAL JUMLAH
                // ============================
                $event->sheet->mergeCells("A{$startRow}:D{$startRow}");
                $event->sheet->setCellValue("A{$startRow}", "Total Jumlah Barang");
                $event->sheet->setCellValue("E{$startRow}", $this->totalJumlah);

                // merge kolom F - H
                $event->sheet->mergeCells("F{$startRow}:H{$startRow}");

                // ============================
                // GRAND TOTAL
                // ============================
                $grandTotalRow = $startRow + 1;
                $event->sheet->mergeCells("A{$grandTotalRow}:D{$grandTotalRow}");
                $event->sheet->setCellValue("A{$grandTotalRow}", "Grand Total Harga");

                $event->sheet->mergeCells("E{$grandTotalRow}:H{$grandTotalRow}");
                $event->sheet->setCellValue("E{$grandTotalRow}", 'Rp ' . number_format($this->grandTotal, 0, ',', '.'));

                // ============================
                // BORDER TABEL
                // ============================
                $range = "A1:H" . ($rowCount + 1);
                $event->sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['rgb' => '000000']
                        ],
                        'inside' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'DDDDDD']
                        ]
                    ]
                ]);

                // ============================
                // STYLE TOTAL BLOCK
                // ============================
                $totalRange = "A{$startRow}:H{$grandTotalRow}";
                $event->sheet->getStyle($totalRange)->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ]
                ]);

                // Auto width
                foreach (range('A', 'H') as $col) {
                    $event->sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // Row height header
                $event->sheet->getRowDimension(1)->setRowHeight(25);

                // Align angka
                $event->sheet->getStyle('E2:E' . ($rowCount + 1))
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $event->sheet->getStyle('G2:H' . ($rowCount + 1))
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            },
        ];
    }
}
