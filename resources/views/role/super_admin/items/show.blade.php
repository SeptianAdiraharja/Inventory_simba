@extends('layouts.index')

@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- ======================== --}}
  {{-- 🧭 BREADCRUMB ORANGE --}}
  {{-- ======================== --}}
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap justify-content-between align-items-center smooth-fade">
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <i class="ri-archive-2-line fs-5" style="color:#FF9800;"></i>
      <a href="{{ route('dashboard') }}" class="breadcrumb-link fw-semibold text-decoration-none" style="color:#FF9800;">
        Dashboard
      </a>
      <span class="text-muted">/</span>
      <a href="{{ route('super_admin.items.index') }}" class="fw-semibold text-decoration-none" style="color:#FFB300;">
        Daftar Barang
      </a>
      <span class="text-muted">/</span>
      <span class="fw-semibold text-dark">{{ $item->name }}</span>
    </div>

    <a href="{{ route('super_admin.items.index') }}"
       class="btn rounded-pill btn-sm d-flex align-items-center gap-2 shadow-sm hover-glow"
       style="background-color:#FF9800;color:#fff;">
      <i class="ri-arrow-left-line"></i> Kembali
    </a>
  </div>

  {{-- ======================== --}}
  {{-- 📦 DASHBOARD BARANG --}}
  {{-- ======================== --}}
  <div class="card border-0 shadow-sm rounded-4 smooth-fade">
    <div class="card-header bg-white border-0 d-flex justify-content-between flex-wrap align-items-center">
      <h4 class="fw-bold mb-0" style="color:#FF9800;">
        <i class="ri-cube-line me-2"></i> Dashboard Barang: {{ $item->name }}
      </h4>
      <small class="text-warning fw-semibold">Pantau stok & kedaluwarsa barang</small>
    </div>

    <div class="card-body bg-white p-4 rounded-bottom-4">

      {{-- 🧭 FILTER SUPPLIER --}}
      <form method="GET" class="mb-4">
        <div class="row g-3 align-items-end">
          <div class="col-md-4">
            <label for="supplier_id" class="form-label fw-semibold text-dark">Pilih Supplier</label>
            <select name="supplier_id" id="supplier_id" class="form-select border-0 shadow-sm"
                    onchange="this.form.submit()"
                    style="border-left:4px solid #FF9800 !important;">
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

      {{-- 🌟 INFORMASI --}}
      <div class="alert d-flex align-items-center shadow-sm border-0 rounded-3 py-2 px-3"
           style="background:#FFF9E6;border-left:5px solid #FF9800;">
        <i class="ri-information-line fs-5 me-2" style="color:#FF9800;"></i>
        <div class="fw-semibold text-dark">
          Menampilkan stok dari:
          <span style="color:#FF9800;">
            {{ $suppliers->firstWhere('id', $supplierId)?->name ?? 'Semua Supplier' }}
          </span>
        </div>
      </div>

      {{-- 📊 TABEL DATA --}}
      <div class="table-responsive mt-3">
        <table class="table table-hover align-middle mb-0">
          <thead style="background:#FFF3CD;">
            <tr class="text-center fw-semibold text-dark">
              <th>Status Expired</th>
              <th>Jumlah Barang</th>
            </tr>
          </thead>
          <tbody>
            <tr class="text-center">
              <td class="fw-semibold text-success">
                <i class="bi bi-check-circle-fill me-2"></i> Belum Expired
              </td>
              <td class="fw-bold text-dark">{{ $nonExpiredCount }}</td>
            </tr>
            <tr class="text-center">
              <td class="fw-semibold text-danger">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> Sudah Expired
              </td>
              <td class="fw-bold text-dark">{{ $expiredCount }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

{{-- ======================== --}}
{{-- 🎨 STYLE TAMBAHAN (TEMA ORANGE CERAH) --}}
{{-- ======================== --}}
<style>
.smooth-fade {
  animation: fadeIn 0.6s ease-in-out;
}
@keyframes fadeIn {
  from {opacity: 0; transform: translateY(10px);}
  to {opacity: 1; transform: translateY(0);}
}

/* Select Input */
.form-select:focus {
  border-color: #FF9800 !important;
  box-shadow: 0 0 0 3px rgba(255,152,0,0.25) !important;
}
option:checked {
  background-color: #FFE082 !important;
}

/* Alert */
.alert {
  background-color: #FFF9E6 !important;
  color: #333 !important;
  transition: all 0.3s ease;
}
.alert:hover {
  background-color: #FFF3CD !important;
}

/* Table */
.table-hover tbody tr:hover {
  background-color: #FFF9E6 !important;
  transition: all 0.3s ease;
}
thead {
  border-bottom: 2px solid #FFD54F !important;
}

/* Breadcrumb Animation */
.breadcrumb-link {
  position: relative;
  transition: all 0.25s ease;
}
.breadcrumb-link::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  width: 0;
  height: 2px;
  background: #FF9800;
  transition: width 0.25s ease;
}
.breadcrumb-link:hover::after {
  width: 100%;
}

/* Hover Glow */
.hover-glow {
  transition: all 0.25s ease;
}
.hover-glow:hover {
  background-color: #FFC107 !important;
  color: #fff !important;
  box-shadow: 0 0 12px rgba(255,152,0,0.4);
}

/* Responsiveness */
@media (max-width:768px) {
  .card-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
  .btn {
    font-size: 0.9rem;
  }
  .form-select {
    font-size: 0.9rem;
  }
}
</style>
@endsection
