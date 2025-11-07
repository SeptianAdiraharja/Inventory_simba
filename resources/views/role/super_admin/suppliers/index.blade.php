@extends('layouts.index')
@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- ======================== --}}
  {{-- 🧭 BREADCRUMB ORANGE --}}
  {{-- ======================== --}}
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap align-items-center justify-content-between smooth-fade">
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <i class="bi bi-people-fill fs-5" style="color:#FF9800;"></i>
      <a href="{{ route('dashboard') }}" class="breadcrumb-link fw-semibold text-decoration-none" style="color:#FF9800;">
        Dashboard
      </a>
      <span class="text-muted">/</span>
      <span class="text-dark fw-semibold">Daftar Supplier</span>
    </div>

    <a href="{{ route('super_admin.suppliers.create') }}" class="btn btn-sm rounded-pill d-flex align-items-center gap-2 shadow-sm hover-glow"
       style="background-color:#FF9800;color:#fff;">
      <i class="ri ri-add-line fs-5"></i> Tambah Supplier
    </a>
  </div>

  {{-- ======================== --}}
  {{-- 🏪 DAFTAR SUPPLIER --}}
  {{-- ======================== --}}
  <div class="card shadow-sm border-0 rounded-4 overflow-visible smooth-fade">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 px-4 border-bottom">
      <h5 class="fw-bold mb-0 d-flex align-items-center gap-2" style="color:#FF9800;">
        <i class="ri ri-store-2-line"></i> Daftar Supplier
      </h5>
      <span class="badge rounded-pill px-3 py-2 fw-semibold" style="background:#FFF9E6;color:#FF9800;border:1px solid #FFE082;">
        Total: {{ $suppliers->count() }}
      </span>
    </div>

    <div class="table-responsive text-nowrap" style="overflow: visible !important;">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light text-center">
          <tr>
            <th class="text-start ps-4">Nama Supplier</th>
            <th>Kontak</th>
            <th width="120px">Aksi</th>
          </tr>
        </thead>

        <tbody>
          @forelse($suppliers as $supplier)
          <tr class="table-row-hover">
            <td class="ps-4 fw-semibold text-dark">
              <i class="ri ri-store-2-line me-2 fs-5" style="color:#FFB300;"></i> {{ $supplier->name }}
            </td>
            <td class="text-center">{{ $supplier->contact ?? '-' }}</td>
            <td class="text-center position-relative">
              <div class="dropdown">
                <button type="button" class="btn p-0 dropdown-toggle hide-arrow shadow-none" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="ri ri-more-2-line fs-5 text-muted"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                  <li>
                    <a class="dropdown-item d-flex align-items-center gap-2"
                       href="{{ route('super_admin.suppliers.edit', $supplier->id) }}">
                      <i class="ri ri-pencil-line text-warning"></i> Edit
                    </a>
                  </li>
                  <li>
                    <form action="{{ route('super_admin.suppliers.destroy', $supplier->id) }}" method="POST"
                          onsubmit="return confirm('Yakin ingin menghapus supplier ini?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2">
                        <i class="ri ri-delete-bin-6-line"></i> Hapus
                      </button>
                    </form>
                  </li>
                </ul>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="3" class="text-center text-muted py-4">
              <i class="ri-information-line me-1"></i> Belum ada data supplier
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- ======================== --}}
{{-- 🎨 STYLE TAMBAHAN (TEMA ORANGE) --}}
{{-- ======================== --}}
<style>
/* Smooth page fade animation */
.smooth-fade { animation: fadeIn 0.6s ease-in-out; }
@keyframes fadeIn { from {opacity:0;transform:translateY(10px);} to {opacity:1;transform:translateY(0);} }

/* Hover glow button */
.hover-glow {
  transition: all 0.25s ease;
}
.hover-glow:hover {
  background-color: #FFC107 !important;
  color: #fff !important;
  box-shadow: 0 0 10px rgba(255,152,0,0.4);
}

/* Breadcrumb underline */
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
.breadcrumb-link:hover::after { width: 100%; }

/* Table hover and layout */
.table-row-hover {
  transition: background-color 0.2s ease, transform 0.15s ease;
}
.table-row-hover:hover {
  background-color: #FFF9E6 !important;
  transform: translateX(3px);
}
.table thead th {
  font-weight: 600;
  color: #444;
  background-color: #fff8e1 !important;
}

/* Dropdown animation and fix overlap */
.dropdown-menu {
  z-index: 1050 !important;
  margin-top: 8px !important;
  border-radius: 10px !important;
  animation: fadeDropdown 0.15s ease-in-out;
}
@keyframes fadeDropdown {
  from { opacity: 0; transform: translateY(-5px); }
  to { opacity: 1; transform: translateY(0); }
}
.dropdown-item {
  font-weight: 500;
  transition: all 0.2s ease;
}
.dropdown-item:hover {
  background-color: #FFF3E0;
  color: #FF9800 !important;
}

/* Responsive */
@media (max-width:768px){
  .card-header{flex-direction:column;align-items:flex-start;gap:.25rem;}
  .btn{font-size:.9rem;}
  .breadcrumb-link{font-size:.9rem;}
  .table thead th{font-size:.85rem;}
}
</style>
@endsection
