<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SupplierTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'name',
            'contact',
        ];
    }

    public function array(): array
    {
        return [
            ['Nama Supplier Contoh', '089xxxxxxxx'],
        ];
    }
}
