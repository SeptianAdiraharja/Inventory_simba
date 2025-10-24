<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->status === 'banned') {
            $query->where('is_banned', true);
        } elseif ($request->status === 'active') {
            $query->where('is_banned', false);
        }

        $users = $query->latest()->get();

        return view('role.super_admin.users.index', compact('users'));
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

        return redirect()->route('super_admin.users.index')->with('success', 'Akun berhasil dibuat.');
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

        return redirect()->route('super_admin.users.index')->with('success', 'Akun berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('super_admin.users.index')->with('success', 'Akun berhasil dihapus.');
    }

    public function ban($id)
    {
        $user = User::findOrFail($id);

        // Cek kalau bukan pegawai, tolak
        if ($user->role !== 'pegawai') {
            return redirect()->back()->with('error', 'Hanya pegawai yang bisa diban!');
        }

        $user->update([
            'is_banned' => true,
            'banned_at' => now(),
        ]);

        return redirect()->back()->with('success', "Pegawai {$user->name} berhasil diban.");
    }

    public function unban($id)
    {
        $user = User::findOrFail($id);

        if ($user->role !== 'pegawai') {
            return redirect()->back()->with('error', 'Hanya pegawai yang bisa di-unban!');
        }

        $user->update([
            'is_banned' => false,
            'banned_at' => null,
        ]);

        return redirect()->back()->with('success', "Pegawai {$user->name} berhasil di-unban.");
    }
}
