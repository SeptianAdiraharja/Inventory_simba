<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SupplierTemplateExport;
use App\Imports\SupplierImport;

class SupplierController extends Controller
{
    /**
     * Display list of suppliers
     */
    public function index()
    {
        $suppliers = Supplier::paginate(10);
        $totalSuppliers = Supplier::count();
        return view('role.super_admin.suppliers.index', compact('suppliers', 'totalSuppliers'));
    }

    /**
     * Show form to create supplier
     */
    public function create()
    {
        return view('role.super_admin.suppliers.create');
    }

    public function show(Supplier $supplier)
    {
        return abort(404);
    }

    /**
     * Store new supplier
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255|unique:suppliers,name',
            'contact' => 'nullable|string|max:255',
        ]);

        Supplier::create($validated);

        return redirect()
            ->route('super_admin.suppliers.index')
            ->with('success', 'Supplier berhasil ditambahkan.');
    }

    /**
     * Edit supplier
     */
    public function edit(Supplier $supplier)
    {
        return view('role.super_admin.suppliers.edit', compact('supplier'));
    }

    /**
     * Update supplier
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255|unique:suppliers,name,' . $supplier->id,
            'contact' => 'nullable|string|max:255',
        ]);

        $supplier->update($validated);

        return redirect()
            ->route('super_admin.suppliers.index')
            ->with('success', 'Supplier berhasil diperbarui.');
    }

    /**
     * Delete supplier
     */
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()
            ->route('super_admin.suppliers.index')
            ->with('success', 'Supplier berhasil dihapus.');
    }

    /**
     * Import suppliers from Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx',
        ], [
            'file.required' => 'Silakan pilih file Excel untuk diunggah.',
            'file.mimes'    => 'Format file harus .xls atau .xlsx.',
        ]);

        try {
            Excel::import(new SupplierImport, $request->file('file'));

            return redirect()
                ->route('super_admin.suppliers.index')
                ->with('success', 'Data supplier berhasil diimport dari Excel!');

        } catch (\Exception $e) {

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }

    /**
     * Download Excel Template for Supplier Import
     */
    public function downloadTemplate()
    {
        return Excel::download(
            new SupplierTemplateExport,
            'template_import_supplier.xlsx'
        );
    }
}
