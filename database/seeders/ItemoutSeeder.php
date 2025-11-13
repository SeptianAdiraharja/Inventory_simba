<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item_out;
use App\Models\Item;
use App\Models\Cart;
use App\Models\User;
use Carbon\Carbon;

class ItemOutSeeder extends Seeder
{
    /**
     * Jalankan seeder untuk tabel item_outs.
     */
    public function run(): void
    {
        // Pastikan ada data relasi yang dibutuhkan
        $items = Item::all();
        $carts = Cart::all();
        $users = User::all();

        if ($items->isEmpty() || $carts->isEmpty() || $users->isEmpty()) {
            $this->command->warn('⚠️ Tidak ada data item/cart/user. Jalankan seeder lain dulu.');
            return;
        }

        // Buat contoh data barang keluar
        for ($i = 0; $i < 30; $i++) {
            $item = $items->random();
            $cart = $carts->random();
            $user = $users->random();

            Item_out::create([
                'cart_id' => $cart->id,
                'item_id' => $item->id,
                'quantity' => rand(1, 10),
                'approved_by' => $user->id,
                'released_at' => Carbon::now()->subDays(rand(0, 10)),
                'created_at' => now()->subDays(rand(5, 15)),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✅ Seeder ItemOutSeeder berhasil dijalankan.');
    }
}
