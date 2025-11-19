<?php

namespace App\Http\Controllers;

use App\Models\{Item, Category, Unit, Supplier};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Storage};
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ItemTemplateExport;
use App\Imports\ItemsImport;

class ItemController extends Controller
{
    /* =====================================================
       📋 INDEX — DAFTAR SEMUA BARANG
    ===================================================== */
    public function index(Request $request)
    {
        $items = Item::with(['category', 'unit', 'supplier']);

        if ($request->filled('search')) {
            $items->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhereHas('category', fn($cat) =>
                      $cat->where('name', 'like', "%{$request->search}%")
                  );
            });
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $items->whereBetween('created_at', [
                "{$request->date_from} 00:00:00",
                "{$request->date_to} 23:59:59"
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

        $items = $items->paginate(15);


        return view('role.super_admin.items.index', compact('items'));
    }

    /* =====================================================
       🆕 CREATE — TAMBAH BARANG
    ===================================================== */
    public function create()
    {
        $categories = Category::all();
        $units = Unit::all();
        $suppliers = Supplier::all();

        return view('role.super_admin.items.create', compact('categories', 'units', 'suppliers'));
    }

    /* =====================================================
       💾 STORE — SIMPAN BARANG BARU
    ===================================================== */
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

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('images/items', 'public');
        }

        Item::create($validated);

        return redirect()->route('super_admin.items.index')
            ->with('success', 'Item berhasil ditambahkan.');
    }

    /* =====================================================
       🔍 SHOW — DETAIL BARANG + STATUS SUPPLIER
    ===================================================== */
    public function show(Request $request, Item $item)
    {
        $supplierId = $request->get('supplier_id');
        $now = Carbon::now();

        $itemInQuery = $item->itemIns()->with('supplier');

        if ($supplierId) {
            $itemInQuery->where('supplier_id', $supplierId);
        }

        $itemIns = $itemInQuery->get();

        $expiredCount = $itemIns->where('expired_at', '<', $now)->sum('quantity');
        $nonExpiredCount = $itemIns->where('expired_at', '>=', $now)->sum('quantity');

        $suppliers = $item->itemIns()
            ->with('supplier')
            ->get()
            ->pluck('supplier')
            ->filter()
            ->unique('id')
            ->values();

        $totalStock = $item->itemIns()->sum('quantity');

        return view('role.super_admin.items.show', compact(
            'item',
            'suppliers',
            'supplierId',
            'expiredCount',
            'nonExpiredCount',
            'totalStock'
        ));
    }

    /* =====================================================
       ✏️ EDIT — FORM EDIT BARANG
    ===================================================== */
    public function edit(Item $item)
    {
        $categories = Category::all();
        $units = Unit::all();
        $suppliers = Supplier::all();

        return view('role.super_admin.items.edit', compact('item', 'categories', 'units', 'suppliers'));
    }

    /* =====================================================
       🔁 UPDATE — SIMPAN PERUBAHAN BARANG
    ===================================================== */
    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'unit_id'     => 'required|exists:units,id',
            'price'       => 'required|numeric|min:0',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($item->image && Storage::disk('public')->exists($item->image)) {
                Storage::disk('public')->delete($item->image);
            }
            $validated['image'] = $request->file('image')->store('images/items', 'public');
        }

        $item->update($validated);

        return redirect()->route('super_admin.items.index')
            ->with('success', 'Item berhasil diperbarui.');
    }

    /* =====================================================
       🗑️ DESTROY — HAPUS ITEM
    ===================================================== */
    public function destroy(Item $item)
    {
        if ($item->image && Storage::disk('public')->exists($item->image)) {
            Storage::disk('public')->delete($item->image);
        }

        $item->delete();

        return redirect()->route('super_admin.items.index')
            ->with('success', 'Item berhasil dihapus.');
    }

    /* =====================================================
       🧾 PRINT BARCODE — GENERATE PDF BARCODE
    ===================================================== */
    public function printBarcode(Request $request, Item $item)
    {
        $jumlah = (int) $request->get('jumlah', 1);

        $widthPt = (30 / 25.4) * 72;
        $heightPt = (20 / 25.4) * 72;

        $pdf = Pdf::loadView('role.super_admin.items.barcode-pdf', compact('item', 'jumlah'))
            ->setPaper([0, 0, $widthPt, $heightPt], 'portrait')
            ->setOptions([
                'margin-top' => 0,
                'margin-bottom' => 0,
                'margin-left' => 0,
                'margin-right' => 0,
                'dpi' => 300,
                'enable-smart-shrinking' => false,
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);

        return $pdf->stream('barcode-' . $item->code . '.pdf');
    }

    /* =====================================================
       📤 IMPORT — UPLOAD DAN SIMPAN DATA BARANG DARI EXCEL
    ===================================================== */
     /* =====================================================
       📤 IMPORT — UPLOAD DAN SIMPAN DATA BARANG DARI EXCEL
    ===================================================== */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls'
        ]);

        Excel::import(new ItemsImport, $request->file('file'));

        return redirect()->route('super_admin.items.index')
            ->with('success', 'Data barang berhasil diimport!');
    }
    public function downloadTemplate()
    {
        return Excel::download(new ItemTemplateExport, 'template_import_items.xlsx');
    }

}
