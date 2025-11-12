<?php

namespace App\Imports;

use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ItemsImport implements ToModel, WithHeadingRow
{
    private static function generateUniqueCode($categoryId)
    {
        // Format category_id menjadi 3 digit (contoh: 2 -> 002)
        $categoryCode = str_pad($categoryId, 3, '0', STR_PAD_LEFT);

        // Ambil item terakhir di kategori ini
        $lastItem = Item::where('category_id', $categoryId)
            ->orderBy('id', 'desc')
            ->first();

        // Tentukan nomor urut berikutnya
        $nextNumber = 1;
        if ($lastItem && preg_match('/-(\d+)-/', $lastItem->code, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        // Format nomor urut dan angka acak
        $formattedNumber = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        $randomNumber = mt_rand(100, 999);

        // Gabungkan hasil akhir → 002-001-734
        return "{$categoryCode}-{$formattedNumber}-{$randomNumber}";
    }

    public function model(array $row)
    {
        // Jika category_id ada, buat kode otomatis
        $code = $row['category_id']
            ? self::generateUniqueCode($row['category_id'])
            : null;

        return new Item([
            'name'        => $row['name'],
            'code'        => $code,
            'category_id' => $row['category_id'] ?? null,
            'stock'       => $row['stock'] ?? 0,
            'price'       => $row['price'] ?? 0,
            'expired_at'  => $row['expired_at'] ? date('Y-m-d', strtotime($row['expired_at'])) : null,
            'supplier_id' => $row['supplier_id'] ?? null,
            'unit_id'     => $row['unit_id'] ?? null,
            'created_by'  => $row['created_by'] ?? Auth::id(),
            'image'       => $row['image'] ?? null,
        ]);
    }
}
