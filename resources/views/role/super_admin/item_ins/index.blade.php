@extends('layouts.index')
@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- 🧭 BREADCRUMB --}}
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3 smooth-fade">
    <div class="d-flex align-items-center flex-wrap gap-2">
      <i class="bi bi-box-arrow-in-down fs-5" style="color:#FF9800;"></i>
      <a href="{{ route('dashboard') }}" class="breadcrumb-link fw-semibold text-decoration-none" style="color:#FF9800;">Dashboard</a>
      <span class="text-muted">/</span>
      <span class="fw-semibold text-dark">Daftar Barang Masuk</span>
    </div>

    <a href="{{ route('super_admin.item_ins.create') }}"
       class="btn btn-sm rounded-pill shadow-sm hover-glow d-flex align-items-center gap-2"
       style="background-color:#FF9800;color:#fff;">
      <i class="ri-add-line fs-5"></i> Tambah Barang
    </a>
  </div>

  {{-- 📋 FILTER DAN PENCARIAN --}}
  <div class="card border-0 shadow-sm rounded-4 smooth-fade mb-4">
    <div class="card-body">
      <form method="GET" action="{{ route('super_admin.item_ins.index') }}" id="filterForm">
        <div class="row g-3 align-items-center">
          <div class="col-md-3 col-sm-6">
            <label class="form-label small text-muted mb-1">Dari Tanggal</label>
            <input type="date" name="start_date" id="startDate"
              class="form-control form-control-sm border-0 shadow-sm"
              value="{{ request('start_date') }}"
              style="border-left:4px solid #FF9800 !important;">
          </div>
          <div class="col-md-3 col-sm-6">
            <label class="form-label small text-muted mb-1">Sampai Tanggal</label>
            <input type="date" name="end_date" id="endDate"
              class="form-control form-control-sm border-0 shadow-sm"
              value="{{ request('end_date') }}"
              style="border-left:4px solid #FF9800 !important;">
          </div>
          <div class="col-md-3 col-sm-6">
            <label class="form-label small text-muted mb-1">Urutkan Stok</label>
            <select name="sort_stock" id="sortStock"
              class="form-select form-select-sm border-0 shadow-sm"
              style="border-left:4px solid #FF9800 !important;">
              <option value="">Semua</option>
              <option value="desc" {{ request('sort_stock') == 'desc' ? 'selected' : '' }}>Paling Banyak</option>
              <option value="asc" {{ request('sort_stock') == 'asc' ? 'selected' : '' }}>Paling Sedikit</option>
            </select>
          </div>
          <div class="col-md-2 col-sm-8">
            <label class="form-label small text-muted mb-1">Cari Barang/Supplier</label>
            <input type="text" name="search" id="autoSearchInput"
              class="form-control form-control-sm border-0 shadow-sm"
              placeholder="Cari..."
              value="{{ request('search') }}"
              style="border-left:4px solid #FF9800 !important;">
          </div>
          <div class="col-md-1 col-sm-4 d-flex align-items-end">
            @if(request('start_date') || request('end_date') || request('sort_stock') || request('search'))
            <a href="{{ route('super_admin.item_ins.index') }}"
               class="btn btn-sm rounded-pill w-100 shadow-sm"
               style="border:1px solid #FFB74D;color:#FF9800;background:#FFF3E0;">
              <i class="ri-refresh-line me-1"></i> Reset
            </a>
            @endif
          </div>
        </div>
      </form>
    </div>
  </div>

  {{-- 📦 TABEL DATA --}}
  <div class="card border-0 shadow-sm rounded-4 smooth-fade">
    <div class="card-body p-3">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="text-center" style="background:#FFF3E0;">
            <tr>
              <th>Barang</th>
              <th>Jumlah</th>
              <th>Supplier</th>
              <th>Tanggal Kadaluarsa</th>
              <th>Status</th>
              <th>Dibuat Oleh</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($items_in as $row)
              @php
                  $now = \Carbon\Carbon::now();
                  $daysLeft = $row->expired_at ? $now->diffInDays($row->expired_at, false) : null;

                  if (!$row->expired_at) {
                      $statusText = 'Tidak Berlaku';
                      $statusClass = 'bg-secondary-subtle text-secondary';
                  } elseif ($daysLeft < 0) {
                      $statusText = 'Expired';
                      $statusClass = 'bg-danger-subtle text-danger';
                  } elseif ($daysLeft <= 10) {
                      $statusText = 'Hampir Expired';
                      $statusClass = 'bg-warning-subtle text-warning';
                  } else {
                      $statusText = 'Belum Expired';
                      $statusClass = 'bg-success-subtle text-success';
                  }

                  $itemStock = $row->item->stock ?? 0;
                  $stockBadge = $itemStock <= 10
                      ? 'bg-danger-subtle text-danger'
                      : ($itemStock <= 30
                          ? 'bg-warning-subtle text-warning'
                          : 'bg-success-subtle text-success');
              @endphp

              <tr class="text-center table-row-hover">
                <td class="text-start fw-semibold text-dark">{{ $row->item->name ?? '-' }}</td>
                <td>{{ $row->quantity }}</td>
                <td class="text-start">{{ $row->supplier->name ?? '-' }}</td>
                <td>{{ $row->expired_at ? $row->expired_at->format('d M Y') : '-' }}</td>
                <td><span class="badge px-3 py-2 rounded-pill {{ $statusClass }}">{{ $statusText }}</span></td>
                <td>{{ $row->creator->name ?? '-' }}</td>
                <td>
                  <div class="dropdown">
                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow shadow-none" data-bs-toggle="dropdown">
                      <i class="ri-more-2-fill text-muted"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 fade show-on-hover">
                      <li>
                        <a class="dropdown-item d-flex align-items-center"
                           data-bs-toggle="modal" data-bs-target="#detailModal{{ $row->id }}">
                          <i class="ri-eye-line me-2 text-primary"></i> Detail
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item d-flex align-items-center"
                           href="{{ route('super_admin.item_ins.edit', $row->id) }}">
                          <i class="ri-pencil-line me-2 text-warning"></i> Edit
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

              {{-- 🪄 MODAL DETAIL --}}
              <div class="modal fade" id="detailModal{{ $row->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                  <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="modal-header text-white py-2" style="background:linear-gradient(90deg,#FF9800,#FFB300);">
                      <h5 class="modal-title fw-semibold">
                        <i class="ri-archive-line me-2"></i> Detail Barang Masuk — {{ $row->item->name ?? '-' }}
                      </h5>
                      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body bg-light">
                      <div class="row mb-3">
                        <div class="col-md-6">
                          <p><strong>Nama Barang:</strong> {{ $row->item->name ?? '-' }}</p>
                          <p><strong>Jumlah Masuk:</strong> {{ $row->quantity }}</p>
                          <p><strong>Supplier:</strong> {{ $row->supplier->name ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                          <p><strong>Tanggal Masuk:</strong> {{ $row->created_at->format('d M Y') }}</p>
                          <p><strong>Tanggal Kadaluarsa:</strong> {{ $row->expired_at ? $row->expired_at->format('d M Y') : '-' }}</p>
                          <p><strong>Status:</strong> <span class="badge rounded-pill px-3 py-2 {{ $statusClass }}">{{ $statusText }}</span></p>
                        </div>
                      </div>

                      <hr>
                      <div class="d-flex flex-wrap justify-content-between align-items-center">
                        <p class="mb-1 fw-semibold text-dark">Sisa Barang di Stok Saat Ini:</p>
                        <span class="badge rounded-pill px-3 py-2 fs-6 {{ $stockBadge }}">
                          {{ $itemStock }} unit
                        </span>
                      </div>

                      @if($itemStock <= 10)
                        <div class="alert alert-danger mt-3 mb-0 py-2">
                          <i class="ri-error-warning-line me-1"></i> Stok sangat sedikit! Segera lakukan restock barang ini.
                        </div>
                      @elseif($itemStock <= 30)
                        <div class="alert alert-warning mt-3 mb-0 py-2">
                          <i class="ri-alert-line me-1"></i> Stok mulai menipis, disarankan untuk restock.
                        </div>
                      @endif

                      <div class="alert alert-light border-start border-4 shadow-sm mt-3" style="border-color:#FF9800;">
                        <i class="ri-information-line me-1 text-warning"></i>
                        Barang ini dimasukkan oleh supplier
                        <span class="fw-bold" style="color:#FF9800;">{{ $row->supplier->name ?? 'Tidak Diketahui' }}</span>.
                      </div>
                    </div>

                    <div class="modal-footer bg-white border-0 pt-3 d-flex justify-content-end">
                      <button type="button" class="btn btn-outline-warning btn-sm rounded-pill px-3" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i> Tutup
                      </button>
                    </div>
                  </div>
                </div>
              </div>
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

@if(request('search'))
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const searchTerm = @json(request('search'));
    const searchInput = document.getElementById('autoSearchInput');
    const table = document.querySelector('.table');
    if (searchInput && searchTerm) {
      searchInput.focus();
      searchInput.classList.add('glow-focus');
      table.scrollIntoView({ behavior: 'smooth', block: 'center' });
      setTimeout(() => searchInput.classList.remove('glow-focus'), 1800);
    }
  });
</script>
@endif

<style>
.glow-focus {
  box-shadow: 0 0 12px rgba(255,152,0,0.6);
  transition: box-shadow 0.4s ease-in-out;
}
</style>

{{-- ⚡ AUTO FILTER --}}
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
      timer = setTimeout(() => form.submit(), 600);
    }
  }

  searchInput.addEventListener('input', autoSubmit);
  startDate.addEventListener('change', autoSubmit);
  endDate.addEventListener('change', autoSubmit);
  sortStock.addEventListener('change', autoSubmit);
});
</script>
@endsection
