<?php

namespace App\Exports;

use App\Models\Item_out;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BarangKeluarExport implements FromCollection, WithHeadings
{
    public function collection()
    {

        return Item_out::with('item')
            ->get()
            ->map(function ($row) {
                return [
                    'ID' => $row->id,
                    'Nama Barang' => $row->item->name,
                    'Jumlah' => $row->quantity,
                    'Harga (Rp)' => $row->price,
                    'Tanggal Keluar' => $row->created_at->format('d-m-Y'),
                ];
            });
    }

    public function headings(): array
    {
        return ['ID', 'Nama Barang', 'Jumlah', 'Harga (Rp)', 'Tanggal Keluar'];
    }
}
