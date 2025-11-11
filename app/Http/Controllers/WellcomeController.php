<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class WellcomeController extends Controller
{
    public function index()
    {
        // Hitung total pengunjung (selain admin & super_admin)
        $totalPengunjung = User::whereNotIn('role', ['admin', 'super_admin'])->count();

        // Hitung jumlah pegawai aktif (yang tidak dibanned)
        $pegawaiAktif = User::where('role', 'pegawai')
            ->where('is_banned', false)
            ->count();

        // Kirim data ke view utama
        return view('welcome', compact('totalPengunjung', 'pegawaiAktif'));
    }
}
