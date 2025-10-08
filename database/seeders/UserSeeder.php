<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
        ]);

        // Admin
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Pegawai
        User::create([
            'name' => 'Pegawai',
            'email' => 'pegawai@example.com',
            'password' => Hash::make('password'),
            'role' => 'pegawai',
        ]);

        // Pegawai
        User::create([
            'name' => 'Edi',
            'email' => 'edi@example.com',
            'password' => Hash::make('password'),
            'role' => 'pegawai',
        ]);

        // Pegawai
        User::create([
            'name' => 'Udin',
            'email' => 'udin@example.com',
            'password' => Hash::make('password'),
            'role' => 'pegawai',
        ]);

        // Pegawai
        User::create([
            'name' => 'Pasep',
            'email' => 'pasep@example.com',
            'password' => Hash::make('password'),
            'role' => 'pegawai',
        ]);

        // Pegawai
        User::create([
            'name' => 'Atang',
            'email' => 'atang@example.com',
            'password' => Hash::make('password'),
            'role' => 'pegawai',
        ]);
    }
}
