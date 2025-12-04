<?php

namespace App\Imports;

use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class ItemsImport implements WithMultipleSheets
{
    public $dataBarangSheet;

    public function __construct()
    {
        $this->dataBarangSheet = new ItemsSheetImport();
    }

    public function sheets(): array
    {
        return [
            'Data_Barang' => $this->dataBarangSheet,
        ];
    }
}


/* ============================================================
 * CHILD IMPORTER (SHEET Data_Barang)
 * ============================================================ */
class ItemsSheetImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use SkipsFailures;

    // Properties untuk melacak hasil import
    private $rowCount = 0;
    private $updatedCount = 0;
    private $createdCount = 0;
    private $existingItems = [];


    // Property untuk custom failures (jika masih diperlukan)
    private $customFailures = [];

    public function __construct()
    {
        // Preload existing items untuk caching
        $this->existingItems = Item::all()->keyBy(function ($item) {
            return strtolower(trim($item->name));
        });
    }

    /**
     * Rules validasi
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:225',
            'category_id' => 'required|exists:categories,id',
            'stock' => 'nullable|numeric|min:0',
            'price' => 'nullable|numeric|min:0',
            'expired_at' => 'nullable|date',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'unit_id' => 'nullable|exists:units,id',
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages(): array
    {
        return [
            'name.required' => 'Nama barang harus diisi',
            'code.required' => 'Code Barang Harus diisi',
            'category_id.required' => 'Kategori harus diisi',
            'category_id.exists' => 'Kategori tidak ditemukan di database',
            'supplier_id.exists' => 'Supplier tidak ditemukan di database',
            'unit_id.exists' => 'Unit tidak ditemukan di database',
        ];
    }

    /**
     * Generate kode unik berdasarkan category_id
     * Format: CCC-NNN-RRR (misal: 002-001-734)
     */
    private function generateUniqueCode($categoryId)
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
     * Cek apakah item sudah ada berdasarkan nama
     */
    public function model(array $row)
    {
        $this->rowCount++;

        // Normalisasi nama untuk pencarian
        $itemName = strtolower(trim($row['name']));

        // Cek apakah item sudah ada
        if (isset($this->existingItems[$itemName])) {
            $existingItem = $this->existingItems[$itemName];

            // Update data barang yang sudah ada
            $existingItem->update([
                'category_id' => $row['category_id'] ?? $existingItem->category_id,
                'stock'       => ($row['stock'] ?? 0) + $existingItem->stock, // Tambah stok
                'price'       => $row['price'] ?? $existingItem->price,
                'expired_at'  => isset($row['expired_at']) && $row['expired_at']
                                    ? date('Y-m-d', strtotime($row['expired_at']))
                                    : $existingItem->expired_at,
                'supplier_id' => $row['supplier_id'] ?? $existingItem->supplier_id,
                'unit_id'     => $row['unit_id'] ?? $existingItem->unit_id,
            ]);

            $this->updatedCount++;

            // Return null karena sudah update, tidak perlu create baru
            return null;
        }

        // Generate kode baru jika item belum ada
        $code = isset($row['category_id']) && $row['category_id']
            ? $this->generateUniqueCode($row['category_id'])
            : null;

        // Buat item baru
        $item = new Item([
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

        // Tambahkan ke cache existing items
        $this->existingItems[$itemName] = $item;

        $this->createdCount++;

        return $item;
    }

    /**
     * Method untuk mendapatkan jumlah baris yang diproses
     */
    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    /**
     * Method untuk mendapatkan jumlah data yang dibuat baru
     */
    public function getCreatedCount(): int
    {
        return $this->createdCount;
    }

    /**
     * Method untuk mendapatkan jumlah data yang diupdate
     */
    public function getUpdatedCount(): int
    {
        return $this->updatedCount;
    }

    /**
     * Method untuk mendapatkan jumlah failures
     * Menggunakan method dari trait SkipsFailures
     */
    public function failuresCount(): int
    {
        return $this->failures()->count();
    }

    /**
     * Method untuk mendapatkan daftar failures dalam format custom
     */
    public function getCustomFailures(): array
    {
        $formattedFailures = [];

        foreach ($this->failures() as $failure) {
            $formattedFailures[] = [
                'row' => $failure->row(),
                'attribute' => $failure->attribute(),
                'errors' => $failure->errors(),
                'values' => $failure->values(),
            ];
        }

        // Gabungkan dengan custom failures jika ada
        return array_merge($formattedFailures, $this->customFailures);
    }

    /**
     * Menangani jika import tidak ada data yang baru
     */
    public function onError(\Throwable $e)
    {
        Log::error('Import Error: ' . $e->getMessage());

        // Simpan error ke custom failures
        $this->customFailures[] = [
            'row' => $this->rowCount,
            'attribute' => 'system',
            'errors' => [$e->getMessage()],
            'values' => [],
        ];

        // Biarkan exception dilempar agar import berhenti
        throw $e;
    }
}