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

    public function index()
    {
        $items = Item::with(['category', 'unit', 'supplier'])->latest()->get();
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
            'category_id' => 'required|exists:categories,id',
            'unit_id'     => 'required|exists:units,id',
            'price'       => 'required|numeric|min:0',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // isi created_by dari user login
        $validated['created_by'] = Auth::id();

        // Simpan gambar jika ada
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('images/items', 'public');
        }

        $validated['stock'] = 0;

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

        $suppliers = Supplier::all();

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
            'category_id' => 'required|exists:categories,id',
            'unit_id'     => 'required|exists:units,id',
            'price'       => 'required|numeric|min:0',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Update gambar jika ada
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

    public function printBarcode(Item $item)
    {
        $pdf = Pdf::loadView('role.super_admin.items.barcode-pdf', compact('item'));
        return $pdf->download('barcode-' . $item->code . '.pdf');
    }
}
