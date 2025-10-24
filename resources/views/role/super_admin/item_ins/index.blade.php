@extends('layouts.index')
@section('content')
<div class="card shadow-sm border-0">

    {{--  Judul Halaman --}}
    <div class="card-header bg-white border-0 pb-0">
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
            <h4 class="fw-bold text-primary mb-0">
                <i class="ri-archive-2-line me-2"></i> Daftar Barang Masuk
            </h4>
            <a href="{{ route('super_admin.item_ins.create') }}" class="btn btn-sm btn-primary">
                <i class="ri-add-line me-1"></i> Tambah Barang
            </a>
        </div>

        {{--  Filter & Pencarian --}}
        <form method="GET" action="{{ route('super_admin.item_ins.index') }}" class="row g-2 align-items-center" id="filterForm">

            {{--  Range tanggal --}}
            <div class="col-md-3 col-sm-6">
                <input type="date" name="start_date" id="startDate" class="form-control form-control-sm"
                    value="{{ request('start_date') }}">
            </div>

            <div class="col-md-3 col-sm-6">
                <input type="date" name="end_date" id="endDate" class="form-control form-control-sm"
                    value="{{ request('end_date') }}">
            </div>

            {{-- Urutkan Stok --}}
            <div class="col-md-3 col-sm-6">
                <select name="sort_stock" id="sortStock" class="form-select form-select-sm">
                    <option value="">Urutkan Stok</option>
                    <option value="desc" {{ request('sort_stock') == 'desc' ? 'selected' : '' }}>Paling Banyak</option>
                    <option value="asc" {{ request('sort_stock') == 'asc' ? 'selected' : '' }}>Paling Sedikit</option>
                </select>
            </div>

            {{--  Search --}}
            <div class="col-md-2 col-sm-8">
                <input type="text" name="search" id="autoSearchInput" class="form-control form-control-sm"
                    placeholder="Cari nama barang / supplier..."
                    value="{{ request('search') }}">
            </div>

            {{--  Tombol Reset di kanan --}}
            <div class="col-md-1 col-sm-4 text-end">
                @if(request('start_date') || request('end_date') || request('sort_stock') || request('search'))
                    <a href="{{ route('super_admin.item_ins.index') }}" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="ri-refresh-line me-1"></i> Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{--  Tabel Data --}}
    <div class="card-body pt-2">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light text-center">
                    <tr>
                        <th>Barang</th>
                        <th>Jumlah</th>
                        <th>Supplier</th>
                        <th>Expired</th>
                        <th>Status</th>
                        <th>Dibuat Oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items_in as $row)
                        @php
                            $isExpired = $row->expired_at ? $row->expired_at->isPast() : null;
                            $statusText = $isExpired === null ? 'Tidak Berlaku' : ($isExpired ? 'Expired' : 'Belum Expired');
                            $statusClass = $isExpired === null ? 'bg-secondary'
                                          : ($isExpired ? 'bg-danger' : 'bg-success');
                        @endphp
                        <tr class="text-center">
                            <td class="text-start fw-semibold">{{ $row->item->name ?? '-' }}</td>
                            <td>{{ $row->quantity }}</td>
                            <td>{{ $row->supplier->name ?? '-' }}</td>
                            <td>{{ $row->expired_at ? $row->expired_at->format('d M Y') : '-' }}</td>
                            <td><span class="badge {{ $statusClass }}">{{ $statusText }}</span></td>
                            <td>{{ $row->creator->name ?? '-' }}</td>
                            <td>
                                <div class="dropdown">
                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow shadow-none" data-bs-toggle="dropdown">
                                        <i class="ri-more-2-fill"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('super_admin.item_ins.edit', $row->id) }}">
                                                <i class="ri-pencil-line me-2"></i> Edit
                                            </a>
                                        </li>
                                        <li>
                                            <form action="{{ route('super_admin.item_ins.destroy', $row->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Yakin hapus data ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="ri-delete-bin-6-line me-2"></i> Hapus
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="ri-information-line me-1"></i> Belum ada data barang masuk.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('filterForm');
    const searchInput = document.getElementById('autoSearchInput');
    const startDate = document.getElementById('startDate');
    const endDate = document.getElementById('endDate');
    const sortStock = document.getElementById('sortStock');
    let timer = null;

    function autoSubmit() {
        // cuma jalan kalau dua tanggal keisi lengkap
        const start = startDate.value;
        const end = endDate.value;
        if ((start && end) || (!start && !end)) {
            clearTimeout(timer);
            timer = setTimeout(() => form.submit(), 500);
        }
    }

    // Event listener
    searchInput.addEventListener('input', autoSubmit);
    startDate.addEventListener('change', autoSubmit);
    endDate.addEventListener('change', autoSubmit);
    sortStock.addEventListener('change', autoSubmit);
});
</script>
@endsection