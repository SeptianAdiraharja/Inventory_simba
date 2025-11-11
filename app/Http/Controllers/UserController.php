<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // 🔹 Ambil semua admin & super_admin (tetap tampil di atas)
        $admins = User::whereIn('role', ['admin'])
                      ->orderBy('name')
                      ->get();

        // 🔹 Query khusus untuk pegawai saja
        $query = User::where('role', 'pegawai');

        // 🔍 Filter status pegawai
        if ($request->status === 'banned') {
            $query->where('is_banned', true);
        } elseif ($request->status === 'active') {
            $query->where('is_banned', false);
        }

        // 🔎 Pencarian berdasarkan nama / email (khusus pegawai)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // 🔹 Ambil hasil pegawai
        $pegawai = $query->orderBy('name')->get();

        // 🔹 Kirim dua dataset terpisah ke view
        return view('role.super_admin.users.index', compact('admins', 'pegawai'));
    }

    public function create()
    {
        return view('role.super_admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role'     => 'required|in:super_admin,admin,pegawai',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        User::create($validated);

        return redirect()->route('super_admin.users.index')
                         ->with('success', 'Akun berhasil dibuat.');
    }

    public function edit(User $user)
    {
        return view('role.super_admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'role'     => 'required|in:super_admin,admin,pegawai',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('super_admin.users.index')
                         ->with('success', 'Akun berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('super_admin.users.index')
                         ->with('success', 'Akun berhasil dihapus.');
    }

    // 🚫 BAN PEGAWAI
    public function ban($id)
    {
        $user = User::findOrFail($id);

        if ($user->role !== 'pegawai') {
            return back()->with('error', 'Hanya pegawai yang bisa diban!');
        }

        $user->update([
            'is_banned' => true,
            'banned_at' => now(),
        ]);

        return back()->with('success', "Pegawai {$user->name} berhasil diban.");
    }

    // 🔓 UNBAN PEGAWAI
    public function unban($id)
    {
        $user = User::findOrFail($id);

        if ($user->role !== 'pegawai') {
            return back()->with('error', 'Hanya pegawai yang bisa di-unban!');
        }

        $user->update([
            'is_banned' => false,
            'banned_at' => null,
        ]);

        return back()->with('success', "Pegawai {$user->name} berhasil di-unban.");
    }
}
