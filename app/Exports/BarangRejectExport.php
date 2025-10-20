<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BarangRejectExport implements FromCollection, WithHeadings
{
    protected Collection $items;
    protected int $totalJumlah;
    protected float $grandTotal;

    public function __construct(Collection $items, int $totalJumlah = 0, float $grandTotal = 0)
    {
        $this->items = $items;
        $this->totalJumlah = $totalJumlah;
        $this->grandTotal = $grandTotal;
    }

    public function collection()
    {
        return $this->items->map(function($row){
            return [
                'Nama Item'     => $row->item->name ?? '-',
                'Nama Reject'   => $row->name,
                'Quantity'      => $row->quantity,
                'Description'   => $row->description,
                'Condition'     => $row->condition,
                'Total Price'   => $row->total_price ?? 0,
                'Tanggal'       => $row->created_at->format('d-m-Y H:i'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Nama Item',
            'Nama Reject',
            'Quantity',
            'Description',
            'Condition',
            'Total Price',
            'Tanggal',
        ];
    }
}
