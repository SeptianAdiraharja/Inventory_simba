<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $items = Item::with(['category', 'unit', 'supplier']);

        if ($request->filled('search')) {
            $items->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhereHas('category', function ($cat) use ($request) {
                      $cat->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $items->whereBetween('created_at', [
                $request->date_from . ' 00:00:00',
                $request->date_to . ' 23:59:59'
            ]);
        } elseif ($request->filled('date_from')) {
            $items->whereDate('created_at', '>=', $request->date_from);
        } elseif ($request->filled('date_to')) {
            $items->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('sort_stock')) {
            $items->orderBy('stock', $request->sort_stock);
        } else {
            $items->latest();
        }

        $items = $items->paginate(10);

        return view('role.super_admin.items.index', compact('items'));
    }

    public function create()
    {
        $categories = Category::all();
        $units = Unit::all();
        $suppliers = Supplier::all();
        return view('role.super_admin.items.create', compact('categories', 'units', 'suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'nullable|string|max:255|unique:items,code',
            'category_id' => 'required|exists:categories,id',
            'unit_id'     => 'required|exists:units,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'price'       => 'required|numeric|min:0',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['stock'] = 0;

        // simpan gambar jika ada
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('images/items', 'public');
        }

        // biarkan code dikosongkan supaya model generate otomatis
        Item::create($validated);

        return redirect()->route('super_admin.items.index')->with('success', 'Item berhasil ditambahkan.');
    }

    public function show(Request $request, Item $item)
    {
        $supplierId = $request->get('supplier_id');
        $itemInQuery = $item->itemIns();

        if ($supplierId) {
            $itemInQuery->where('supplier_id', $supplierId);
        }

        $itemIns = $itemInQuery->get();

        $expiredCount = $itemIns->where('expired_at', '<', now())->sum('quantity');
        $nonExpiredCount = $itemIns->where('expired_at', '>=', now())->sum('quantity');

        // 🔹 ambil supplier yang pernah masukin item ini
        $suppliers = $item->itemIns()
            ->with('supplier') // pastikan di model Item_in ada relasi ke Supplier
            ->get()
            ->pluck('supplier')
            ->unique('id')
            ->values();

        return view('role.super_admin.items.show', compact(
            'item',
            'suppliers',
            'supplierId',
            'expiredCount',
            'nonExpiredCount'
        ));
    }

    public function edit(Item $item)
    {
        $categories = Category::all();
        $units = Unit::all();
        $suppliers = Supplier::all();
        return view('role.super_admin.items.edit', compact('item', 'categories', 'units', 'suppliers'));
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:255|unique:items,code,' . $item->id,
            'category_id' => 'required|exists:categories,id',
            'unit_id'     => 'required|exists:units,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'price'       => 'required|numeric|min:0',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:1024',
        ]);

        if ($request->hasFile('image')) {
            if ($item->image && Storage::disk('public')->exists($item->image)) {
                Storage::disk('public')->delete($item->image);
            }
            $validated['image'] = $request->file('image')->store('images/items', 'public');
        }

        $item->update($validated);

        return redirect()->route('super_admin.items.index')->with('success', 'Item berhasil diperbarui.');
    }

    public function destroy(Item $item)
    {
        if ($item->image && Storage::disk('public')->exists($item->image)) {
            Storage::disk('public')->delete($item->image);
        }

        $item->delete();
        return redirect()->route('super_admin.items.index')->with('success', 'Item berhasil dihapus.');
    }

    public function printBarcode(Request $request, Item $item)
    {
        $jumlah = $request->get('jumlah', 1);
        $ukuran = $request->get('ukuran', 'A4');

        $pdf = Pdf::loadView('role.super_admin.items.barcode-pdf', compact('item', 'jumlah', 'ukuran'))
            ->setPaper('A4', 'portrait');

        return $pdf->download('barcode-' . $item->code . '.pdf');
    }
}
