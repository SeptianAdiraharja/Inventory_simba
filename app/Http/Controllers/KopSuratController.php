<?php

namespace App\Http\Controllers;

use App\Models\KopSurat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KopSuratController extends Controller
{
    // 🔹 Tampilkan semua kop surat
    public function index()
    {
        $kopSurats = KopSurat::latest()->get();
        return view('role.super_admin.kop_surat.index', compact('kopSurats'));
    }

    // 🔹 Form tambah kop surat
    public function create()
    {
        return view('role.super_admin.kop_surat.create');
    }

    // 🔹 Simpan data baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_instansi' => 'required|string|max:255',
            'nama_unit' => 'nullable|string|max:255',
            'alamat' => 'nullable|string|max:255',
            'telepon' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'website' => 'nullable|string|max:255',
            'kota' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('kop_logo', 'public');
        }

        KopSurat::create($validated);

        return redirect()->route('super_admin.kop_surat.index')
            ->with('success', 'Kop surat berhasil ditambahkan!');
    }

    // 🔹 Form edit
    public function edit(KopSurat $kopSurat)
    {
        return view('role.super_admin.kop_surat.edit', compact('kopSurat'));
    }

    // 🔹 Update data kop surat
    public function update(Request $request, KopSurat $kopSurat)
    {
        $validated = $request->validate([
            'nama_instansi' => 'required|string|max:255',
            'nama_unit' => 'nullable|string|max:255',
            'alamat' => 'nullable|string|max:255',
            'telepon' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'website' => 'nullable|string|max:255',
            'kota' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        // 🔸 Hapus logo lama jika upload baru
        if ($request->hasFile('logo')) {
            if ($kopSurat->logo && Storage::disk('public')->exists($kopSurat->logo)) {
                Storage::disk('public')->delete($kopSurat->logo);
            }
            $validated['logo'] = $request->file('logo')->store('kop_logo', 'public');
        }

        $kopSurat->update($validated);

        return redirect()->route('super_admin.kop_surat.index')
            ->with('success', 'Kop surat berhasil diupdate!');
    }

    // 🔹 Hapus kop surat
    public function destroy(KopSurat $kopSurat)
    {
        if ($kopSurat->logo && Storage::disk('public')->exists($kopSurat->logo)) {
            Storage::disk('public')->delete($kopSurat->logo);
        }

        $kopSurat->delete();

        return redirect()->route('super_admin.kop_surat.index')
            ->with('success', 'Kop surat dihapus!');
    }
}