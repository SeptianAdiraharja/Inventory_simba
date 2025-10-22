@extends('layouts.index')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-0 pb-0">
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
            <div>
                <h4 class="fw-bold text-primary mb-0">
                    <i class="ri-archive-2-line me-2"></i> Dashboard Barang: {{ $item->name }}
                </h4>
            </div>
            <a href="{{ route('super_admin.items.index') }}" class="btn btn-secondary btn-sm">
                <i class="ri-arrow-left-line me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="card-body">
        <form method="GET" class="mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="supplier_id" class="form-label fw-semibold">Pilih Supplier</label>
                    <select name="supplier_id" id="supplier_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Supplier</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ $supplierId == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>

        <div class="alert alert-light border-start border-4 border-primary py-2">
            <i class="ri-information-line me-1"></i>
            Menampilkan stok dari:
            <strong>{{ $suppliers->firstWhere('id', $supplierId)?->name ?? 'Semua Supplier' }}</strong>
        </div>

        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Status Expired</th>
                        <th>Jumlah Barang</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-success fw-semibold">
                            <i class="bi bi-check-circle-fill me-2"></i> Belum Expired
                        </td>
                        <td><strong>{{ $nonExpiredCount }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-danger fw-semibold">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> Sudah Expired
                        </td>
                        <td><strong>{{ $expiredCount }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
