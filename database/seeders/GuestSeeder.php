<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GuestSeeder extends Seeder
{
    public function run(): void
    {
        // Buat data tamu baru
        $guestId = DB::table('guests')->insertGetId([
            'name' => 'Tamu Undangan A',
            'phone' => '081234567890',
            'description' => 'Tamu dari instansi luar melakukan permintaan peminjaman barang.',
            'created_by' => 1, // misal admin ID 1
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Buat data tamu baru
        $guestId = DB::table('guests')->insertGetId([
            'name' => 'Tamu Undangan B',
            'phone' => '081234567890',
            'description' => 'Tamu dari instansi luar melakukan permintaan peminjaman barang.',
            'created_by' => 1, // misal admin ID 1
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Buat guest_cart (keranjang)
        $guestCartId = DB::table('guest_carts')->insertGetId([
            'session_id' => 'session_' . uniqid(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Ambil beberapa item random dari tabel items
        $items = DB::table('items')->inRandomOrder()->limit(2)->get();

        foreach ($items as $item) {
            DB::table('guest_cart_items')->insert([
                'guest_cart_id' => $guestCartId,
                'item_id' => $item->id,
                'quantity' => rand(1, 5),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        // Simulasikan bahwa tamu sudah mengajukan permintaan barang keluar
        DB::table('item_out_guests')->insert([
            'guest_id' => $guestId,
            'items' => json_encode(
                $items->map(fn($item) => [
                    'item_id' => $item->id,
                    'quantity' => rand(1, 5),
                ])
            ),
            'printed_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
