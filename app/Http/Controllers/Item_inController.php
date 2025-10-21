<?php

namespace App\Http\Controllers;

use App\Models\Item_in;
use App\Models\Item;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Item_inController extends Controller
{
    public function index(Request $request)
    {
        $query = Item_in::with(['item', 'supplier', 'creator']);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('item', fn($sub) => $sub->where('name', 'like', "%{$search}%"))
                ->orWhereHas('supplier', fn($sub) => $sub->where('name', 'like', "%{$search}%"));
            });
        }

        $perPage = $request->get('per_page', 10);
        $items_in = $query->latest()->paginate($perPage);

        return view('role.super_admin.item_ins.index', compact('items_in'));
    }

    public function create()
    {
        $items = Item::all();
        $suppliers = Supplier::all();
        return view('role.super_admin.item_ins.create', compact('items', 'suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_id'     => 'required|exists:items,id',
            'quantity'    => 'required|integer|min:1',
            'supplier_id' => 'required|exists:suppliers,id',
            'expired_at'  => 'nullable|date|after_or_equal:today',
        ]);

        $item_in = Item_in::create([
            'item_id'     => $request->item_id,
            'quantity'    => $request->quantity,
            'supplier_id' => $request->supplier_id,
            'expired_at'  => $request->expired_at ?? null,
            'created_by'  => Auth::id(),
        ]);

        $item = Item::findOrFail($request->item_id);
        $item->stock += $request->quantity;
        $item->save();

        return redirect()->route('super_admin.item_ins.index')
            ->with('success', 'Data berhasil ditambahkan & stok diperbarui');
    }

    public function edit(Item_in $item_in)
    {
        $items = Item::all();
        $suppliers = Supplier::all();
        return view('role.super_admin.item_ins.edit', compact('item_in', 'items', 'suppliers'));
    }

    public function update(Request $request, Item_in $item_in)
    {
        $request->validate([
            'item_id'     => 'required|exists:items,id',
            'quantity'    => 'required|integer|min:1',
            'supplier_id' => 'required|exists:suppliers,id',
            'expired_at'  => 'nullable|date|after_or_equal:today',
        ]);

        $oldItemId = $item_in->item_id;
        $oldQty    = $item_in->quantity;

        // Update stok
        if ($oldItemId != $request->item_id) {
            $oldItem = Item::findOrFail($oldItemId);
            $oldItem->stock -= $oldQty;
            $oldItem->save();

            $newItem = Item::findOrFail($request->item_id);
            $newItem->stock += $request->quantity;
            $newItem->save();
        } else {
            $diff = $request->quantity - $oldQty;
            $item = Item::findOrFail($request->item_id);
            $item->stock += $diff;
            $item->save();
        }

        $expiredAt = $request->has('expired_at') && $request->expired_at ? $request->expired_at : null;

        $item_in->update([
            'item_id'     => $request->item_id,
            'quantity'    => $request->quantity,
            'supplier_id' => $request->supplier_id,
            'expired_at'  => $expiredAt, 
        ]);


        return redirect()->route('super_admin.item_ins.index')
            ->with('success', 'Data berhasil diupdate & stok diperbarui');
    }


    public function destroy(Item_in $item_in)
    {
        $item = Item::findOrFail($item_in->item_id);
        $item->stock -= $item_in->quantity;
        $item->save();

        $item_in->delete();

        return redirect()->route('super_admin.item_ins.index')
            ->with('success', 'Data berhasil dihapus & stok diperbarui');
    }
}
