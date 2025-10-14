<?php

namespace App\Http\Controllers\Role\Admin;

use App\Models\Item_out;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BarangKeluarExportAdmin implements FromCollection, WithHeadings
{
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
