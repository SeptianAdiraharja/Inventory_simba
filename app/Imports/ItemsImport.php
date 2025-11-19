<?php

namespace App\Imports;

use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ItemsImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Data_Barang' => new ItemsSheetImport(),
        ];
    }
}

/* ============================================================
 * CHILD IMPORTER (SHEET Data_Barang)
 * ============================================================ */
class ItemsSheetImport implements ToModel, WithHeadingRow
{
    /**
     * Generate kode unik berdasarkan category_id
     * Format: CCC-NNN-RRR (misal: 002-001-734)
     */
    private static function generateUniqueCode($categoryId)
    {
        $categoryCode = str_pad($categoryId, 3, '0', STR_PAD_LEFT);

        $lastItem = Item::where('category_id', $categoryId)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastItem && preg_match('/-(\d+)-/', $lastItem->code, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        $formattedNumber = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        $randomNumber = mt_rand(100, 999);

        return "{$categoryCode}-{$formattedNumber}-{$randomNumber}";
    }

    /**
     * Convert each row to model
     */
    public function model(array $row)
    {
        $code = isset($row['category_id']) && $row['category_id']
            ? self::generateUniqueCode($row['category_id'])
            : null;

        return new Item([
            'name'        => $row['name'],
            'code'        => $code,
            'category_id' => $row['category_id'] ?? null,
            'stock'       => $row['stock'] ?? 0,
            'price'       => $row['price'] ?? 0,
            'expired_at'  => isset($row['expired_at']) && $row['expired_at']
                                ? date('Y-m-d', strtotime($row['expired_at']))
                                : null,
            'supplier_id' => $row['supplier_id'] ?? null,
            'unit_id'     => $row['unit_id'] ?? null,
            'created_by'  => $row['created_by'] ?? Auth::id(),
            'image'       => $row['image'] ?? null,
        ]);
    }
}
