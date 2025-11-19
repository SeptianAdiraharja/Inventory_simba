<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;
use App\Models\Item_in;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Supplier;
use App\Models\User;

class ItemSeeder extends Seeder
{
    public function run()
    {
        // --- Buat kategori yang diperlukan ---
        $categories = [
            ['name' => 'Alat Dapur'],
            ['name' => 'Alat Kebersihan'],
            ['name' => 'Perabotan'],
            ['name' => 'Elektronik'],
            ['name' => 'Alat Tulis'],
            ['name' => 'Bahan Bangunan'],
        ];

        foreach ($categories as $categoryData) {
            Category::firstOrCreate($categoryData);
        }

        // --- Buat unit yang diperlukan ---
        $units = [
            ['name' => 'pcs'],
            ['name' => 'unit'],
            ['name' => 'set'],
            ['name' => 'lusin'],
            ['name' => 'pak'],
        ];

        foreach ($units as $unitData) {
            Unit::firstOrCreate($unitData);
        }

        // --- Buat supplier yang diperlukan ---
        $suppliers = [
            ['name' => 'PT Sumber Makmur'],
            ['name' => 'CV Jaya Abadi'],
            ['name' => 'UD Sentosa'],
            ['name' => 'Toko Serba Ada'],
        ];

        foreach ($suppliers as $supplierData) {
            Supplier::firstOrCreate($supplierData);
        }

        $user = User::first() ?? User::factory()->create();

        // --- Data item dengan kategori yang bervariasi ---
        $itemsData = [
            // Alat Dapur
            ['name' => 'Panci Stainless', 'category' => 'Alat Dapur', 'unit' => 'pcs', 'supplier' => 'PT Sumber Makmur', 'stock' => 15, 'price' => 75000],
            ['name' => 'Wajan Anti Lengket', 'category' => 'Alat Dapur', 'unit' => 'pcs', 'supplier' => 'CV Jaya Abadi', 'stock' => 20, 'price' => 45000],
            ['name' => 'Pisau Dapur Set', 'category' => 'Alat Dapur', 'unit' => 'set', 'supplier' => 'UD Sentosa', 'stock' => 8, 'price' => 120000],
            ['name' => 'Spatula Kayu', 'category' => 'Alat Dapur', 'unit' => 'pcs', 'supplier' => 'Toko Serba Ada', 'stock' => 30, 'price' => 15000],
            ['name' => 'Blender Listrik', 'category' => 'Alat Dapur', 'unit' => 'unit', 'supplier' => 'PT Sumber Makmur', 'stock' => 12, 'price' => 250000],

            // Alat Kebersihan
            ['name' => 'Sapu Lantai', 'category' => 'Alat Kebersihan', 'unit' => 'pcs', 'supplier' => 'CV Jaya Abadi', 'stock' => 25, 'price' => 35000],
            ['name' => 'Kemoceng Bulu', 'category' => 'Alat Kebersihan', 'unit' => 'pcs', 'supplier' => 'UD Sentosa', 'stock' => 40, 'price' => 12000],
            ['name' => 'Ember Plastik', 'category' => 'Alat Kebersihan', 'unit' => 'pcs', 'supplier' => 'Toko Serba Ada', 'stock' => 18, 'price' => 25000],
            ['name' => 'Pel Lantai', 'category' => 'Alat Kebersihan', 'unit' => 'pcs', 'supplier' => 'PT Sumber Makmur', 'stock' => 22, 'price' => 18000],
            ['name' => 'Kain Lap', 'category' => 'Alat Kebersihan', 'unit' => 'lusin', 'supplier' => 'CV Jaya Abadi', 'stock' => 10, 'price' => 30000],

            // Perabotan
            ['name' => 'Meja Kayu', 'category' => 'Perabotan', 'unit' => 'unit', 'supplier' => 'UD Sentosa', 'stock' => 5, 'price' => 450000],
            ['name' => 'Kursi Plastik', 'category' => 'Perabotan', 'unit' => 'pcs', 'supplier' => 'Toko Serba Ada', 'stock' => 35, 'price' => 65000],
            ['name' => 'Lemari Baju', 'category' => 'Perabotan', 'unit' => 'unit', 'supplier' => 'PT Sumber Makmur', 'stock' => 3, 'price' => 850000],
            ['name' => 'Rak Buku', 'category' => 'Perabotan', 'unit' => 'unit', 'supplier' => 'CV Jaya Abadi', 'stock' => 7, 'price' => 320000],
            ['name' => 'Meja Kerja', 'category' => 'Perabotan', 'unit' => 'unit', 'supplier' => 'UD Sentosa', 'stock' => 4, 'price' => 550000],

            // Elektronik
            ['name' => 'Lampu LED', 'category' => 'Elektronik', 'unit' => 'pcs', 'supplier' => 'PT Sumber Makmur', 'stock' => 50, 'price' => 25000],
            ['name' => 'Kabel Extension', 'category' => 'Elektronik', 'unit' => 'pcs', 'supplier' => 'CV Jaya Abadi', 'stock' => 28, 'price' => 45000],
            ['name' => 'Stop Kontak', 'category' => 'Elektronik', 'unit' => 'pcs', 'supplier' => 'UD Sentosa', 'stock' => 35, 'price' => 15000],
            ['name' => 'Baterai AA', 'category' => 'Elektronik', 'unit' => 'pak', 'supplier' => 'Toko Serba Ada', 'stock' => 20, 'price' => 20000],
            ['name' => 'Adaptor USB', 'category' => 'Elektronik', 'unit' => 'pcs', 'supplier' => 'PT Sumber Makmur', 'stock' => 15, 'price' => 35000],
        ];

        // --- Buat item berdasarkan data di atas ---
        foreach ($itemsData as $itemData) {
            $category = Category::where('name', $itemData['category'])->first();
            $unit = Unit::where('name', $itemData['unit'])->first();
            $supplier = Supplier::where('name', $itemData['supplier'])->first();

            $item = Item::create([
                'name'        => $itemData['name'],
                'category_id' => $category->id,
                'stock'       => $itemData['stock'],
                'price'       => $itemData['price'],
                'unit_id'     => $unit->id,
                'supplier_id' => $supplier->id,
                'created_by'  => $user->id,
                'image'       => 'default.png',
                'expired_at'  => now()->addDays(rand(30, 365)),
            ]);

            // --- Tambah data item_in ---
            Item_in::create([
                'item_id'     => $item->id,
                'quantity'    => $itemData['stock'],
                'supplier_id' => $supplier->id,
                'expired_at'  => now()->addDays(rand(30, 365)),
                'created_by'  => $user->id,
            ]);
        }

        $this->command->info('Items seeded successfully with various categories!');
    }
}