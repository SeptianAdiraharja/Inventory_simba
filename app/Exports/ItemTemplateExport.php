<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ItemTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new TemplateSheet(),
            new SopSheet(),
            new CategorySheet(),
            new UnitSheet(),
            new SupplierSheet(),
        ];
    }
}

/* ============================================================
 * SHEET 1 — DATA BARANG
 * ============================================================ */
class TemplateSheet implements FromArray, WithHeadings, WithTitle
{
    public function title(): string
    {
        return 'Data_Barang';
    }

    public function headings(): array
    {
        return [
            'name',
            'category_id',
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
                10000,
                '',
                1,
                1,
                1,
                '',
            ],
        ];
    }
}

/* ============================================================
 * SHEET 2 — SOP PENGISIAN DATA
 * ============================================================ */
class SopSheet implements FromArray, WithHeadings, WithTitle
{
    public function title(): string
    {
        return 'SOP_Pengisian_Data';
    }

    public function headings(): array
    {
        return ["Bagian / Langkah", "Penjelasan Detail"];
    }

    public function array(): array
    {
        return [
            ["=== I. TUJUAN ===", ""],
            ["Deskripsi", "SOP ini disusun untuk membantu pengguna melakukan pengisian data barang..."],

            ["=== II. RUANG LINGKUP ===", ""],
            ["Cakupan", "Berlaku untuk seluruh staf yang mengelola data barang..."],

            ["=== III. DEFINISI ===", ""],
            ["Import Barang", "Proses memasukkan banyak data barang..."],
            ["Referensi Data", "Data acuan kategori, satuan, supplier..."],
            ["ID", "Angka unik..."],

            ["=== IV. STRUKTUR FILE ===", ""],
            ["Data_Barang", "Tempat pengisian data barang."],
            ["SOP_Pengisian_Data", "Petunjuk lengkap."],
            ["Referensi_Kategori", "Daftar kategori."],
            ["Referensi_Satuan", "Daftar satuan."],
            ["Referensi_Supplier", "Daftar supplier."],

            ["=== V. SOP PENGISIAN DATA (SUPER DETAIL) ===", ""],

            ["1. Persiapan",
            "Pastikan menggunakan file terbaru dan sheet referensi sudah benar."],

            ["2. Isi 'name'",
            "Nama barang wajib diisi. Contoh: Printer Epson L3110."],

            ["3. Isi 'category_id'",
            "Lihat sheet Referensi_Kategori dan isi ID yang sesuai."],

            ["4. Isi 'price'",
            "Harga harus angka tanpa simbol."],

            ["5. Isi 'expired_at'",
            "Tanggal format YYYY-MM-DD. Bisa kosong."],

            ["6. Isi 'supplier_id'",
            "Pilih ID supplier dari sheet Referensi_Supplier."],

            ["7. Isi 'unit_id'",
            "Pilih ID satuan dari sheet Referensi_Satuan."],

            ["8. Isi 'created_by'",
            "ID user admin = 1."],

            ["9. Isi 'image'",
            "Nama file di folder images/items/."],

            ["10. Review Data",
            "Pastikan semua kolom valid."],

            ["11. Import File",
            "Simpan .xlsx lalu upload ke sistem."],
        ];
    }
}

/* ============================================================
 * SHEET 3 — KATEGORI
 * ============================================================ */
class CategorySheet implements FromCollection, WithHeadings, WithTitle
{
    public function title(): string
    {
        return 'Referensi_Kategori';
    }

    public function headings(): array
    {
        return ["ID", "Nama Kategori"];
    }

    public function collection()
    {
        return DB::table('categories')
            ->select('id', 'name')
            ->orderBy('id')
            ->get();
    }
}

/* ============================================================
 * SHEET 4 — SATUAN
 * ============================================================ */
class UnitSheet implements FromCollection, WithHeadings, WithTitle
{
    public function title(): string
    {
        return 'Referensi_Satuan';
    }

    public function headings(): array
    {
        return ["ID", "Nama Satuan"];
    }

    public function collection()
    {
        return DB::table('units')
            ->select('id', 'name')
            ->orderBy('id')
            ->get();
    }
}

/* ============================================================
 * SHEET 5 — SUPPLIER
 * ============================================================ */
class SupplierSheet implements FromCollection, WithHeadings, WithTitle
{
    public function title(): string
    {
        return 'Referensi_Supplier';
    }

    public function headings(): array
    {
        return ["ID", "Nama Supplier", "Kontak"];
    }

    public function collection()
    {
        return DB::table('suppliers')
            ->select('id', 'name', 'contact')
            ->orderBy('id')
            ->get();
    }
}
