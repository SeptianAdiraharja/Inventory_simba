@extends('layouts.index')
@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- ======================== --}}
  {{-- 🧭 MODERN BREADCRUMB --}}
  {{-- ======================== --}}
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap align-items-center justify-content-between smooth-fade">
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <i class="bi bi-box-arrow-in-down fs-5 text-primary"></i>
      <a href="{{ route('dashboard') }}" class="breadcrumb-link fw-semibold text-primary text-decoration-none">
        Dashboard
      </a>
      <span class="text-muted">/</span>
      <span class="text-muted">Daftar Barang Masuk</span>
    </div>
    <a href="{{ route('super_admin.item_ins.create') }}" class="btn btn-sm btn-primary rounded-pill d-flex align-items-center gap-2 shadow-sm hover-glow">
      <i class="ri ri-add-line fs-5"></i> Tambah Barang
    </a>
  </div>

  {{-- ======================== --}}
  {{-- 📦 DAFTAR BARANG MASUK --}}
  {{-- ======================== --}}
  <div class="card shadow-sm border-0 rounded-4">
    <div class="card-header bg-white border-0 pb-0">
      <h4 class="fw-bold text-primary mb-3"><i class="ri-archive-2-line me-2"></i> Daftar Barang Masuk</h4>

      {{-- 🔍 Filter & Search --}}
      <form method="GET" action="{{ route('super_admin.item_ins.index') }}" class="row g-2 align-items-center" id="filterForm">
        <div class="col-md-3 col-sm-6">
          <input type="date" name="start_date" id="startDate" class="form-control form-control-sm" value="{{ request('start_date') }}">
        </div>
        <div class="col-md-3 col-sm-6">
          <input type="date" name="end_date" id="endDate" class="form-control form-control-sm" value="{{ request('end_date') }}">
        </div>
        <div class="col-md-3 col-sm-6">
          <select name="sort_stock" id="sortStock" class="form-select form-select-sm">
            <option value="">Urutkan Stok</option>
            <option value="desc" {{ request('sort_stock') == 'desc' ? 'selected' : '' }}>Paling Banyak</option>
            <option value="asc" {{ request('sort_stock') == 'asc' ? 'selected' : '' }}>Paling Sedikit</option>
          </select>
        </div>
        <div class="col-md-2 col-sm-8">
          <input type="text" name="search" id="autoSearchInput" class="form-control form-control-sm"
                 placeholder="Cari barang / supplier..." value="{{ request('search') }}">
        </div>
        <div class="col-md-1 col-sm-4 text-end">
          @if(request('start_date') || request('end_date') || request('sort_stock') || request('search'))
          <a href="{{ route('super_admin.item_ins.index') }}" class="btn btn-sm btn-outline-secondary w-100">
            <i class="ri-refresh-line me-1"></i> Reset
          </a>
          @endif
        </div>
      </form>
    </div>

    {{-- 📋 TABEL DATA --}}
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
                $statusText = $isExpired === null ? 'Tidak Berlaku' : ($isExpired ? 'Expired' : 'Aktif');
                $statusClass = $isExpired === null ? 'bg-secondary-subtle text-secondary' :
                                ($isExpired ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success');
              @endphp
              <tr class="text-center table-row-hover">
                <td class="text-start fw-semibold">{{ $row->item->name ?? '-' }}</td>
                <td>{{ $row->quantity }}</td>
                <td>{{ $row->supplier->name ?? '-' }}</td>
                <td>{{ $row->expired_at ? $row->expired_at->format('d M Y') : '-' }}</td>
                <td><span class="badge px-3 py-2 rounded-pill {{ $statusClass }}">{{ $statusText }}</span></td>
                <td>{{ $row->creator->name ?? '-' }}</td>
                <td>
                  <div class="dropdown">
                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow shadow-none" data-bs-toggle="dropdown">
                      <i class="ri-more-2-fill text-muted"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                      <li>
                        <a class="dropdown-item d-flex align-items-center" href="{{ route('super_admin.item_ins.edit', $row->id) }}">
                          <i class="ri-pencil-line me-2 text-primary"></i> Edit
                        </a>
                      </li>
                      <li>
                        <form action="{{ route('super_admin.item_ins.destroy', $row->id) }}" method="POST"
                              onsubmit="return confirm('Yakin hapus data ini?')">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="dropdown-item text-danger d-flex align-items-center">
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

</div>

{{-- 🎨 STYLE TAMBAHAN --}}
<style>
.smooth-fade { animation: fadeIn 0.6s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px);} to { opacity: 1; transform: translateY(0);} }
.table-row-hover { transition: background-color 0.2s ease, transform 0.15s ease; }
.table-row-hover:hover { background-color: #f8f9fc !important; transform: translateX(3px); }
.hover-glow { transition: all 0.25s ease; }
.hover-glow:hover { background-color: #7d0dfd !important; color: #fff !important; box-shadow: 0 0 12px rgba(125,13,253,0.4); }
.breadcrumb-link { position: relative; transition: all 0.25s ease; }
.breadcrumb-link::after { content: ''; position: absolute; bottom: -2px; left: 0; width: 0; height: 2px; background: #7d0dfd; transition: width 0.25s ease; }
.breadcrumb-link:hover::after { width: 100%; }
</style>

{{-- ⚡ SCRIPT AUTO FILTER --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('filterForm');
  const searchInput = document.getElementById('autoSearchInput');
  const startDate = document.getElementById('startDate');
  const endDate = document.getElementById('endDate');
  const sortStock = document.getElementById('sortStock');
  let timer = null;

  function autoSubmit() {
    const start = startDate.value;
    const end = endDate.value;
    if ((start && end) || (!start && !end)) {
      clearTimeout(timer);
      timer = setTimeout(() => form.submit(), 500);
    }
  }

  searchInput.addEventListener('input', autoSubmit);
  startDate.addEventListener('change', autoSubmit);
  endDate.addEventListener('change', autoSubmit);
  sortStock.addEventListener('change', autoSubmit);
});
</script>
@endsection
