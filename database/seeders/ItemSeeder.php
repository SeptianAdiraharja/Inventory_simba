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
        // --- Pastikan ada minimal data Category, Unit, Supplier, User ---
        $category = Category::firstOrCreate(['name' => 'Elektronik']);
        $unit     = Unit::firstOrCreate(['name' => 'pcs']);
        $supplier = Supplier::firstOrCreate(['name' => 'PT Sumber Makmur']);
        $user     = User::first() ?? User::factory()->create();

        // --- Buat 20 item ---
        for ($i = 1; $i <= 20; $i++) {
            $item = Item::create([
                'name'        => "Barang {$i}",
                'category_id' => $category->id,
                'stock'       => rand(10, 100),
                'price'       => rand(5000, 50000),
                'unit_id'     => $unit->id,
                'supplier_id' => $supplier->id,
                'created_by'  => $user->id,
                'image'       => 'default.png',
                // expired_at bisa null supaya ikut status auto
                'expired_at'  => now()->addDays(rand(30, 365)),
            ]);

            // --- Tambah data item_in ---
            Item_in::create([
                'item_id'    => $item->id,
                'quantity'   => $item->stock,
                'supplier_id'=> $supplier->id,
                'expired_at' => now()->addDays(rand(30, 365)),
                'created_by' => $user->id,
            ]);
        }
    }
}
