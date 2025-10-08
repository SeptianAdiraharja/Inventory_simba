<?php

namespace App\Http\Controllers\Role\admin;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Guest_carts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class GuestController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q');

        $guests = Guest::with('creator')
            ->when($query, function ($q) use ($query) {
                $keywords = explode(' ', $query); // pisah tiap kata, agar makin fleksibel

                $q->where(function ($sub) use ($keywords) {
                    foreach ($keywords as $word) {
                        $sub->orWhere('name', 'LIKE', "%{$word}%")
                            ->orWhere('phone', 'LIKE', "%{$word}%");
                    }
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();  // supaya query ?q= tetap terbawa saat pagination

        return view('role.admin.guest', compact('guests', 'query'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'phone'       => 'required|string|max:20',
            'description' => 'nullable|string',
        ]);

        $guest = Guest::create([
            'name'        => $request->name,
            'phone'       => $request->phone,
            'description' => $request->description,
            'created_by'  => Auth::id(), // pastikan ada kolom created_by di tabel
        ]);

        // langsung buat guest_cart kosong
        Guest_carts::create([
            'guest_id' => $guest->id,
            'session_id' => session()->getId(),
        ]);

        return redirect()->route('admin.guests.index')
            ->with('success', 'Guest berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'description' => 'nullable|string',
        ]);

        $guest = Guest::findOrFail($id);
        $guest->update($request->only('name', 'phone', 'description'));

        return redirect()->route('admin.guests.index')->with('success', 'Guest berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $guest = Guest::findOrFail($id);
        $guest->delete();

        return redirect()->route('admin.guests.index')->with('success', 'Guest berhasil dihapus.');
    }
}

