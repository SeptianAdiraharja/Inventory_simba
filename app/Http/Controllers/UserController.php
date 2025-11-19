<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Category; // ⬅️ Tambahan
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Admin tetap normal dan paginate
        $admins = User::whereIn('role', ['admin'])
                    ->orderBy('name')
                    ->paginate(10)
                    ->withQueryString();

        // DEFAULT: Pegawai aktif (tanpa withTrashed)
        $query = User::where('role', 'pegawai')->whereNull('deleted_at');

        // Filter
        if ($request->status === 'banned') {
            $query->where('is_banned', true)
                ->whereNull('deleted_at');
        } elseif ($request->status === 'active') {
            $query->where('is_banned', false)
                ->whereNull('deleted_at');
        } elseif ($request->status === 'deleted') {
            // Hanya yang soft deleted
            $query = User::onlyTrashed()->where('role', 'pegawai');
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Paginate pegawai
        $pegawai = $query->orderBy('name')
                        ->paginate(10)
                        ->withQueryString();

        return view('role.super_admin.users.index', compact('admins', 'pegawai'));
    }

    public function create()
    {
        $categories = Category::all();
        $user = null;

        return view('role.super_admin.users.create', compact('categories', 'user'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role'     => 'required|in:super_admin,admin,pegawai',
            'categories' => 'array', // ⬅️ Tambahan
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);

        // ⬅️ Tambahan many-to-many
        if ($request->has('categories')) {
            $user->categories()->sync($request->categories);
        }

        return redirect()->route('super_admin.users.index')
                         ->with('success', 'Akun berhasil dibuat.');
    }

    public function edit(User $user)
    {
        $categories = Category::all();
        return view('role.super_admin.users.edit', compact('user', 'categories'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'role'     => 'required|in:super_admin,admin,pegawai',
            'categories' => 'array',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        // Sync kategori yang dipilih oleh super admin
        $user->categories()->sync($request->categories ?? []);

        return redirect()->route('super_admin.users.index')
                        ->with('success', 'Akun berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        // 🔥 SOFT DELETE KHUSUS PEGAWAI
        if ($user->role === 'pegawai') {
            $user->delete(); // Soft delete
            return back()->with('success', 'Pegawai berhasil dihapus (soft delete).');
        }

        // 🔥 ADMIN & SUPER ADMIN = HARD DELETE
        $user->forceDelete();

        return back()->with('success', 'Akun admin berhasil dihapus permanen.');
    }

    // 🚫 BAN PEGAWAI
    public function ban($id)
    {
        $user = User::withTrashed()->findOrFail($id);

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
        $user = User::withTrashed()->findOrFail($id);

        if ($user->role !== 'pegawai') {
            return back()->with('error', 'Hanya pegawai yang bisa di-unban!');
        }

        $user->update([
            'is_banned' => false,
            'banned_at' => null,
        ]);

        return back()->with('success', "Pegawai {$user->name} berhasil di-unban.");
    }

    // 🔁 RESTORE PEGAWAI
    public function restore($id)
    {
        $user = User::withTrashed()->findOrFail($id);

        if ($user->role !== 'pegawai') {
            return back()->with('error', 'Hanya pegawai yang bisa dipulihkan!');
        }

        $user->restore();

        return back()->with('success', "Pegawai {$user->name} berhasil dipulihkan.");
    }
}