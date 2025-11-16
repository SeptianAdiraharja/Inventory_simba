<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ItemTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'name',
            'category_id',
            'stock',
            'price',
            'expired_at',
            'supplier_id',
            'unit_id',
            'created_by',
            'image',
        ];
    }

    public function array(): array
    {
        return [
            [
                'Contoh Barang',
                1,
                0,
                10000,
                '',          // expired_at boleh kosong
                1,
                1,
                1,
                '',          // image kosong
            ],
        ];
    }
}
