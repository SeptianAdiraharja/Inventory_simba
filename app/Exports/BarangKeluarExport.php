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
use PhpOffice\PhpSpreadsheet\Style\Color;

class BarangKeluarExport implements FromCollection, WithHeadings, WithStyles, WithEvents
{
    protected $items;
    protected $totalJumlah;
    protected $grandTotal;
    protected $rowNumber = 1; // 👈 Tambah counter untuk nomor urut

    public function __construct($items, $totalJumlah, $grandTotal)
    {
        $this->items = $items;
        $this->totalJumlah = $totalJumlah;
        $this->grandTotal = $grandTotal;
    }

    public function collection()
    {
        $this->rowNumber = 1; // Reset counter

        return $this->items->map(function ($row) {
            // Ambil nama satuan dari object/array
            $unitName = '-';
            if ($row->item->unit) {
                if (is_object($row->item->unit)) {
                    $unitName = $row->item->unit->name ?? '-';
                } elseif (is_array($row->item->unit)) {
                    $unitName = $row->item->unit['name'] ?? '-';
                } else {
                    $unitName = $row->item->unit;
                }
            }

            // 👈 PERBAIKI DISINI: Gunakan field yang benar
            // Cek field yang ada di database/eloquent
            $issuedBy = $row->issued_by ?? $row->dikeluarkan ?? '-';
            $recipient = $row->recipient ?? $row->penerima ?? '-';

            return [
                $this->rowNumber++, // 👈 Nomor urut yang benar (1,2,3,...)
                $row->item->name ?? '-',
                $row->role ?? '-',
                $issuedBy, // 👈 Gunakan field yang benar
                $recipient, // 👈 Gunakan field yang benar
                $row->created_at ? $row->created_at->format('d-m-Y') : '-',
                $row->quantity ?? 0,
                $unitName,
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
            'Role',
            'Dikeluarkan Oleh',
            'Penerima',
            'Tanggal Keluar',
            'Jumlah',
            'Satuan',
            'Harga Satuan (Rp)',
            'Total Harga (Rp)',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FF9800']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ],
            'A2:J' . (count($this->items) + 1) => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'DDDDDD']
                    ]
                ]
            ],
            (count($this->items) + 2) => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFE0B2']
                ]
            ],
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
                $startRow = $rowCount + 2; // Baris setelah data

                // 👇 Baris untuk "Total Jumlah Barang"
                $event->sheet->mergeCells("A{$startRow}:F{$startRow}");
                $event->sheet->setCellValue("A{$startRow}", "Total Jumlah Barang");
                $event->sheet->setCellValue("G{$startRow}", $this->totalJumlah);

                // Gabungkan kolom H, I, J untuk Total Jumlah Barang
                $event->sheet->mergeCells("H{$startRow}:J{$startRow}");
                $event->sheet->setCellValue("H{$startRow}", "");

                // 👇 Baris untuk "Grand Total Harga"
                $grandTotalRow = $startRow + 1;
                $event->sheet->mergeCells("A{$grandTotalRow}:F{$grandTotalRow}");
                $event->sheet->setCellValue("A{$grandTotalRow}", "Grand Total Harga");

                // Gabungkan kolom G, H, I, J untuk Grand Total Harga
                $event->sheet->mergeCells("G{$grandTotalRow}:J{$grandTotalRow}");
                $event->sheet->setCellValue("G{$grandTotalRow}", 'Rp ' . number_format($this->grandTotal, 0, ',', '.'));

                // Style untuk border tabel data
                $dataRange = "A1:J" . ($rowCount + 1);
                $event->sheet->getStyle($dataRange)->applyFromArray([
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

                // Style khusus untuk baris total
                $totalRange = "A{$startRow}:J{$grandTotalRow}";
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

                // Border atas untuk baris total pertama
                $event->sheet->getStyle("A{$startRow}:J{$startRow}")->applyFromArray([
                    'borders' => [
                        'top' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['rgb' => 'FF9800']
                        ]
                    ]
                ]);

                // Auto width untuk semua kolom dengan padding
                foreach (range('A', 'J') as $col) {
                    $event->sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // Set tinggi baris header
                $event->sheet->getRowDimension(1)->setRowHeight(25);

                // Center alignment untuk kolom angka
                $event->sheet->getStyle('G2:G' . ($rowCount + 1))
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $event->sheet->getStyle('I2:J' . ($rowCount + 1))
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            },
        ];
    }
}