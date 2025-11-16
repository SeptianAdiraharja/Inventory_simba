<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Cart;
use App\Models\Item;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Unit;
use Illuminate\Support\Str;

class RequestSeeder extends Seeder
{
    public function run(): void
    {
        // ===== Buat data referensi kategori & unit =====
        $category = Category::firstOrCreate(['name' => 'Elektronik']);
        $unit = Unit::firstOrCreate(['name' => 'Pcs']);

        // ===== Buat user dummy jika belum ada =====
        $user = User::firstOrCreate(
            ['email' => 'pegawai@example.com'],
            [
                'name' => 'Pegawai Contoh',
                'password' => bcrypt('password'),
                'role' => 'pegawai',
            ]
        );

        // ===== Buat beberapa item =====
        $items = collect([
            ['name' => 'Printer Canon', 'price' => 2500000],
            ['name' => 'Monitor LG', 'price' => 1800000],
            ['name' => 'Keyboard Logitech', 'price' => 250000],
            ['name' => 'Mouse Wireless', 'price' => 150000],
        ])->map(function ($data) use ($category, $unit, $user) {
            return Item::create([
                'name' => $data['name'],
                'category_id' => $category->id,
                'unit_id' => $unit->id,
                'supplier_id' => null,
                'stock' => rand(10, 50),
                'price' => $data['price'],
                'created_by' => $user->id,
            ]);
        });

        // ===== Buat 3 permintaan (cart) =====
        for ($i = 1; $i <= 50; $i++) {
            $cart = Cart::create([
                'user_id' => $user->id,
                'status' => 'approved',
            ]);

            // Ambil 2 item acak untuk tiap permintaan
            $selectedItems = $items->random(2);

            foreach ($selectedItems as $item) {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'item_id' => $item->id,
                    'quantity' => rand(1, 3),
                    'status' => 'approved',
                ]);
            }
        }

        $this->command->info('✅ RequestSeeder berhasil dijalankan: carts & cart_items terisi.');
    }
}
