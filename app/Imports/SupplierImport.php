<?php

namespace App\Imports;

use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SupplierImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Normalisasi nama kolom supaya fleksibel
        $name = $row['name'] ?? $row['nama_supplier'] ?? $row['nama'] ?? null;
        $contact = $row['contact'] ?? $row['kontak'] ?? $row['no_hp'] ?? null;

        if (!$name) return null; // skip baris tanpa nama

        return new Supplier([
            'name'    => $name,
            'contact' => $contact,
        ]);
    }
}
