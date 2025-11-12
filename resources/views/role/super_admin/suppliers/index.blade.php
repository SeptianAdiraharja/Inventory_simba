@extends('layouts.index')
@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- ======================== --}}
  {{-- 🧭 BREADCRUMB + ACTION BUTTONS --}}
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

    <div class="d-flex align-items-center gap-2 flex-wrap">
      {{-- 🔘 Tombol Import Data --}}
      <button type="button" class="btn btn-sm rounded-pill d-flex align-items-center gap-2 shadow-sm hover-glow"
              style="background-color:#FFB300;color:#fff;" data-bs-toggle="modal" data-bs-target="#importModal">
        <i class="bi bi-upload fs-6"></i> Import Data
      </button>

      {{-- 🔘 Tombol Tambah Supplier --}}
      <a href="{{ route('super_admin.suppliers.create') }}" class="btn btn-sm rounded-pill d-flex align-items-center gap-2 shadow-sm hover-glow"
         style="background-color:#FF9800;color:#fff;">
        <i class="ri ri-add-line fs-5"></i> Tambah Supplier
      </a>
    </div>
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
{{-- 📦 MODAL IMPORT SUPPLIER
{{-- ======================== --}}
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header text-white" style="background:linear-gradient(90deg,#FF9800,#FF9300);">
        <h5 class="modal-title" id="importModalLabel">
          <i class="bi bi-file-earmark-excel me-2"></i>Import Data Supplier
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <form action="{{ route('super_admin.suppliers.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label for="file" class="form-label fw-semibold">Pilih File Excel</label>
            <input type="file" name="file" id="file" class="form-control" accept=".xlsx,.xls,.csv" required>
            <small class="text-muted">Format file: .xlsx / .xls / .csv</small>
          </div>

          <div class="alert alert-info small mt-3">
            <i class="bi bi-info-circle me-1"></i>
            Pastikan file berisi kolom:
            <br><code>name, contact </code>
          </div>
        </div>

        <div class="modal-footer border-0">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-warning text-white">
            <i class="bi bi-check-circle me-1"></i>Import Sekarang
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ======================== --}}
{{-- 🎨 STYLE TAMBAHAN --}}
{{-- ======================== --}}
<style>
.smooth-fade {
  animation: fadeIn .6s ease-in-out;
}
@keyframes fadeIn {
  from {opacity: 0; transform: translateY(10px);}
  to {opacity: 1; transform: translateY(0);}
}
.hover-glow {transition: all .25s ease;}
.hover-glow:hover {
  background-color: #FFC107 !important;
  color: #fff !important;
  box-shadow: 0 0 10px rgba(255,152,0,0.4);
}
.breadcrumb-link {
  position: relative; transition: all .25s ease;
}
.breadcrumb-link::after {
  content: ''; position: absolute; bottom: -2px; left: 0;
  width: 0; height: 2px; background: #FF9800;
  transition: width .25s ease;
}
.breadcrumb-link:hover::after { width: 100%; }
.table-row-hover {transition: background-color .2s ease, transform .15s ease;}
.table-row-hover:hover {
  background-color: #FFF9E6 !important;
  transform: translateX(3px);
}
</style>
@endsection
