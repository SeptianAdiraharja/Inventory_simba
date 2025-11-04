@extends('layouts.index')
@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- ======================== --}}
  {{-- 🧭 BREADCRUMB MODERN --}}
  {{-- ======================== --}}
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap align-items-center justify-content-between smooth-fade">
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <i class="bi bi-box-seam fs-5 text-primary"></i>
      <a href="{{ route('dashboard') }}" class="breadcrumb-link fw-semibold text-primary text-decoration-none">Dashboard</a>
      <span class="text-muted">/</span>
      <span class="text-muted">Daftar Satuan Barang</span>
    </div>
    <a href="{{ route('super_admin.units.create') }}" class="btn btn-sm btn-primary rounded-pill d-flex align-items-center gap-2 shadow-sm hover-glow">
      <i class="ri ri-add-line fs-5"></i> Tambah Satuan
    </a>
  </div>

  {{-- ======================== --}}
  {{-- 📦 TABEL SATUAN BARANG --}}
  {{-- ======================== --}}
  <div class="card shadow-sm border-0 rounded-4 overflow-hidden smooth-fade">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 px-4 border-bottom">
      <h5 class="fw-bold text-primary mb-0"><i class="ri ri-ruler-line me-2 text-primary"></i> Daftar Satuan Barang</h5>
      <span class="badge bg-light text-muted px-3 py-2 rounded-pill">Total: {{ $units->count() }}</span>
    </div>

    <div class="table-responsive text-nowrap">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light text-center">
          <tr>
            <th class="text-start ps-4">Nama Satuan Barang</th>
            <th width="120px">Aksi</th>
          </tr>
        </thead>

        <tbody>
          @forelse($units as $unit)
          <tr class="table-row-hover">
            <td class="ps-4">
              <i class="ri ri-stack-line text-info me-2 fs-5"></i>
              <span class="fw-semibold text-dark">{{ $unit->name }}</span>
            </td>
            <td class="text-center">
              <div class="dropdown">
                <button type="button" class="btn p-0 dropdown-toggle hide-arrow shadow-none" data-bs-toggle="dropdown">
                  <i class="ri ri-more-2-line fs-5 text-muted"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow-sm">
                  <a class="dropdown-item d-flex align-items-center" href="{{ route('super_admin.units.edit', $unit->id) }}">
                    <i class="ri ri-pencil-line me-2 text-primary"></i> Edit
                  </a>
                  <form action="{{ route('super_admin.units.destroy', $unit->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="dropdown-item text-danger d-flex align-items-center"
                      onclick="return confirm('Yakin ingin menghapus satuan ini?')">
                      <i class="ri ri-delete-bin-6-line me-2"></i> Hapus
                    </button>
                  </form>
                </div>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="2" class="text-center text-muted py-4">
              <i class="ri-information-line me-1"></i> Belum ada data satuan barang
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>

{{-- ======================== --}}
{{-- 🎨 STYLE TAMBAHAN --}}
{{-- ======================== --}}
<style>
.smooth-fade {
  animation: fadeIn 0.6s ease-in-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.table-row-hover {
  transition: background-color 0.2s ease, transform 0.15s ease;
}
.table-row-hover:hover {
  background-color: #f8f9fc !important;
  transform: translateX(3px);
}

.hover-glow {
  transition: all 0.25s ease;
}
.hover-glow:hover {
  background-color: #7d0dfd !important;
  color: #fff !important;
  box-shadow: 0 0 12px rgba(125, 13, 253, 0.4);
}

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
  background: #7d0dfd;
  transition: width 0.25s ease;
}
.breadcrumb-link:hover::after {
  width: 100%;
}
</style>
@endsection
