@extends('layouts.index')
@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Dashboard Barang</h5>
    </div>

    <div class="card-body">

        <!-- Dropdown Filter -->
        <form method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-4">
                    <select name="supplier_id" class="form-select" onchange="this.form.submit()">
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

        <!-- Filter Info -->
        <div class="mb-4 text-muted">
            <strong>Menampilkan stok dari:</strong>
            {{ $suppliers->firstWhere('id', $supplierId)?->name ?? 'Semua Supplier' }}
        </div>

        <!-- Tabel Statistik Barang -->
        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Status Expired</th>
                        <th>Jumlah Barang</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    <tr>
                        <td class="text-success">
                            <i class="bi bi-check-circle-fill me-1"></i> Belum Expired
                        </td>
                        <td><strong>{{ $nonExpiredCount }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-danger">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Sudah Expired
                        </td>
                        <td><strong>{{ $expiredCount }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <a href="{{ route('super_admin.items.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
    </div>
</div>

@endsection
