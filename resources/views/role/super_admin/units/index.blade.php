@extends('layouts.index')
@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- BREADCRUMB --}}
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap align-items-center justify-content-between smooth-fade">
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <i class="bi bi-box-seam fs-5" style="color:#FF9800;"></i>
      <a href="{{ route('dashboard') }}" class="breadcrumb-link fw-semibold text-decoration-none" style="color:#FF9800;">
        Dashboard
      </a>
      <span class="text-muted">/</span>
      <span class="text-dark fw-semibold">Daftar Satuan Barang</span>
    </div>

    <a href="{{ route('super_admin.units.create') }}"
       class="btn btn-sm rounded-pill d-flex align-items-center gap-2 shadow-sm hover-glow"
       style="background-color:#FF9800;color:#fff;">
      <i class="ri ri-add-line fs-5"></i> Tambah Satuan
    </a>
  </div>

  {{-- TABLE --}}
  <div class="card shadow-sm border-0 rounded-4 overflow-hidden smooth-fade">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 px-4 border-bottom">
      <h5 class="fw-bold mb-0 d-flex align-items-center gap-2" style="color:#FF9800;">
        <i class="ri ri-ruler-line me-1" style="color:#FF9800;"></i> Daftar Satuan Barang
      </h5>
      <span class="badge rounded-pill px-3 py-2 fw-semibold" style="background:#FFF9E6;color:#FF9800;border:1px solid #FFE082;">
        Total: {{ $totalUnits }}
      </span>
    </div>

    <div class="table-responsive text-nowrap position-relative">
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
              <i class="ri ri-stack-line me-2 fs-5" style="color:#FFB300;"></i>
              <span class="fw-semibold text-dark">{{ $unit->name }}</span>
            </td>
            <td class="text-center">
              <div class="dropdown">
                <button class="btn p-0 border-0 shadow-none" type="button" id="dropdownMenuBtn{{ $unit->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="ri ri-more-2-line fs-5 text-muted"></i>
                </button>

                <ul class="dropdown-menu dropdown-menu-end shadow-sm rounded-3" aria-labelledby="dropdownMenuBtn{{ $unit->id }}">
                  <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('super_admin.units.edit', $unit->id) }}">
                      <i class="ri ri-pencil-line text-warning"></i> Edit
                    </a>
                  </li>
                  <li>
                    <form action="{{ route('super_admin.units.destroy', $unit->id) }}" method="POST" class="m-0 p-0">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2"
                        onclick="return confirm('Yakin ingin menghapus satuan ini?')">
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
            <td colspan="2" class="text-center text-muted py-4">
              <i class="ri-information-line me-1"></i> Belum ada data satuan barang
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- ⭐ PAGINATION (TAMBAHAN) --}}
    <div class="p-3">
      {{ $units->links('pagination::bootstrap-5') }}
    </div>

  </div>
</div>

<style>
/* ===== Pagination Orange ===== */
.pagination .page-link {
  color: #FF9800;
  border: 1px solid #FFCC80;
}
.pagination .page-link:hover {
  background-color: #FFE0B2;
  color: #E67E22;
}
.pagination .active .page-link {
  background-color: #FF9800;
  border-color: #FF9800;
  color: white !important;
}
.pagination .page-item.disabled .page-link {
  color: #FFCC80;
}

/* keep theme styles */
.smooth-fade { animation: fadeIn 0.6s ease-in-out; }
@keyframes fadeIn { from {opacity:0;transform:translateY(10px);} to {opacity:1;transform:translateY(0);} }

.table-row-hover { transition: background-color 0.2s ease, transform 0.15s ease; }
.table-row-hover:hover { background-color: #FFF9E6 !important; transform: translateX(3px); }

.hover-glow { transition: all 0.25s ease; }
.hover-glow:hover { background-color: #FFC300 !important; color:#fff !important; box-shadow: 0 0 12px rgba(255,152,0,0.4); }

.breadcrumb-link { position: relative; transition: all 0.25s ease; }
.breadcrumb-link::after { content:''; position:absolute; bottom:-2px; left:0; width:0; height:2px; background:#FF9800; transition:width 0.25s ease; }
.breadcrumb-link:hover::after { width:100%; }

.table-responsive { overflow: visible !important; }
.dropdown-menu { position: absolute !important; z-index:3000 !important; min-width:160px; box-shadow: 0 6px 18px rgba(0,0,0,0.12); }
.dropdown-item:hover { background-color:#FFF3CD !important; }
.dropdown-item:active { background-color:#FFD54F !important; }

.table thead th { font-weight:600; color:#444; background-color:#fff8e1 !important; }
</style>
@endsection
