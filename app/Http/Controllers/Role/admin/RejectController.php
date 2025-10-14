<?php

namespace App\Http\Controllers\Role\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\Item;
use App\Models\Reject;

class RejectController extends Controller
{

    // Halaman scan
    public function scanPage()
    {
        return view('role.admin.rejects.scan');
    }
    
    public function checkBarcode($barcode)
    {
        $item = Item::where('code', $barcode)->first();

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => "Barang dengan kode <b>{$barcode}</b> tidak ditemukan."
            ]);
        }

        return response()->json([
            'success' => true,
            'item' => $item
        ]);
    }

    public function processScan(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.barcode' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.description' => 'required|string',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->items as $data) {
                $item = Item::where('code', $data['barcode'])->first();
                if ($item) {
                    $item->decrement('stock', $data['quantity']);
                    Reject::create([
                        'item_id' => $item->id,
                        'name' => $item->name,
                        'quantity' => $data['quantity'],
                        'description' => $data['description'],
                        'condition' => $data['condition'] ?? 'rusak ringan',
                    ]);
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Semua barang rusak berhasil disimpan.'
        ]);
    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
