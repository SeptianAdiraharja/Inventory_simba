<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmployeeRequestSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil satu user pegawai (kalau belum ada, buat dummy)
        $userId = DB::table('users')->where('role', 'pegawai')->value('id');

        if (!$userId) {
            $userId = DB::table('users')->insertGetId([
                'name' => 'Pegawai Contoh',
                'email' => 'pegawai@example.com',
                'password' => bcrypt('password'),
                'role' => 'pegawai',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        // Buat cart baru untuk request pegawai
        $cartId = DB::table('carts')->insertGetId([
            'user_id' => $userId,
            'status' => 'pending',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Tambahkan beberapa barang (pastikan tabel items tidak kosong)
        $item1 = DB::table('items')->inRandomOrder()->first();
        $item2 = DB::table('items')->inRandomOrder()->skip(1)->first();

        if ($item1 && $item2) {
            DB::table('cart_items')->insert([
                [
                    'cart_id' => $cartId,
                    'item_id' => $item1->id,
                    'quantity' => rand(1, 5),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'cart_id' => $cartId,
                    'item_id' => $item2->id,
                    'quantity' => rand(1, 3),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
            ]);
        }

        // Tambahkan notifikasi untuk admin bahwa ada permintaan baru
        $admin = DB::table('users')->where('role', 'admin')->first();

        if ($admin) {
            DB::table('notifications')->insert([
                'user_id' => $admin->id,
                'title' => 'Permintaan Baru',
                'message' => "Pegawai ID: {$userId} mengajukan permintaan barang.",
                'status' => 'unread',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
