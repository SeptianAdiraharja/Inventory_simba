@extends('layouts.index')
@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- ======================== --}}
  {{-- 🧭 BREADCRUMB MODERN ORANGE --}}
  {{-- ======================== --}}
  <div class="breadcrumb-modern bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap justify-content-between align-items-center smooth-fade">
    <div class="d-flex align-items-center gap-3 flex-wrap">
      {{-- Icon Gradient --}}
      <div class="icon-wrapper d-flex align-items-center justify-content-center rounded-circle shadow-sm">
        <i class="ri-price-tag-3-line fs-5 text-white"></i>
      </div>

      {{-- Breadcrumb Link --}}
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <a href="{{ route('super_admin.dashboard') }}" class="breadcrumb-link fw-semibold text-decoration-none">
          Dashboard
        </a>
        <span class="text-muted">/</span>
        <span class="fw-semibold text-dark">Daftar Kategori</span>
      </div>
    </div>

    {{-- Tanggal Otomatis --}}
    <div class="text-muted small d-flex align-items-center gap-2">
      <i class="ri-calendar-line"></i>
      {{ \Carbon\Carbon::now()->format('d M Y, H:i') }}
    </div>
  </div>

  {{-- ======================== --}}
  {{-- 📋 DAFTAR KATEGORI --}}
  {{-- ======================== --}}
  <div class="card shadow-sm border-0 rounded-4 smooth-fade">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3 px-4 flex-wrap">
      <h5 class="fw-bold text-warning mb-0 d-flex align-items-center gap-2">
        <i class="ri ri-price-tag-3-line"></i> Daftar Kategori
      </h5>
      <div class="d-flex gap-2 align-items-center flex-wrap">
        <button id="refreshBtn" class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-medium shadow-sm hover-glow d-flex align-items-center gap-2">
          <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
        <a href="{{ route('super_admin.categories.create') }}" class="btn btn-sm btn-warning text-white rounded-pill shadow-sm d-flex align-items-center gap-2 hover-glow">
          <i class="ri ri-add-line"></i> Tambah
        </a>
      </div>
    </div>

    {{-- Tabel Daftar Kategori --}}
    <div class="table-responsive text-nowrap">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light text-center">
          <tr>
            <th class="text-start ps-4">Nama Kategori</th>
            <th width="120">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($categories as $category)
          <tr class="align-middle text-start table-row-hover">
            <td class="ps-4">
              <i class="ri-price-tag-3-line text-warning me-2 fs-5"></i>
              <span class="fw-semibold">{{ $category->name }}</span>
            </td>
            <td class="text-center">
              <div class="dropdown">
                <button type="button" class="btn p-0 dropdown-toggle hide-arrow shadow-none" data-bs-toggle="dropdown">
                  <i class="ri-more-2-line fs-5 text-muted"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow-sm rounded-3">
                  <a href="{{ route('super_admin.categories.edit', $category->id) }}" class="dropdown-item d-flex align-items-center gap-2">
                    <i class="ri-pencil-line text-warning"></i> Edit
                  </a>
                  <form action="{{ route('super_admin.categories.destroy', $category->id) }}" method="POST" class="m-0 p-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2"
                            onclick="return confirm('Yakin hapus kategori ini?')">
                      <i class="ri-delete-bin-6-line"></i> Hapus
                    </button>
                  </form>
                </div>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="2" class="text-center text-muted py-4">
              <i class="ri-information-line me-1"></i> Belum Ada Data Kategori
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- ⭐ PAGINATION (TAMBAHAN / TIDAK UBAH STRUKTUR) --}}
    <div class="p-3">
      {{ $categories->withQueryString()->links('pagination::bootstrap-5') }}
    </div>

  </div>
</div>

{{-- ======================== --}}
{{-- 💅 STYLE --}}
{{-- ======================== --}}
<style>

/* ===== PAGINATION ORANGE (TAMBAHAN) ===== */
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
  color: #fff !important;
}
.pagination .page-item.disabled .page-link {
  color: #FFCC80;
}

/* ===== Smooth Fade ===== */
.smooth-fade { animation: fadeIn 0.6s ease-in-out; }
@keyframes fadeIn { from {opacity: 0; transform: translateY(10px);} to {opacity: 1; transform: translateY(0);} }

/* ===== Modern Breadcrumb ===== */
.breadcrumb-modern {
  transition: all 0.3s ease-in-out;
  background-color: #fff;
  border: none;
}
.icon-wrapper {
  width: 42px;
  height: 42px;
  background: linear-gradient(135deg, #FF9800, #FFC300);
}
.breadcrumb-link {
  position: relative;
  color: #FF9800;
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
.breadcrumb-link:hover {
  color: #e67e22 !important;
}

/* ===== Hover & Button ===== */
.hover-glow { transition: all 0.25s ease; }
.hover-glow:hover {
  background-color: #FF9800 !important;
  color: #fff !important;
  box-shadow: 0 0 12px rgba(255, 152, 0, 0.4);
}

/* ===== Table Style ===== */
.table-hover tbody tr:hover {
  background-color: #FFF9E6 !important;
  transition: 0.25s ease;
}
.table thead th {
  font-weight: 600;
  font-size: 0.9rem;
  color: #555;
}
.table-row-hover {
  transition: background-color 0.25s ease, transform 0.15s ease;
}
.table-row-hover:hover {
  background-color: #FFFBEA !important;
  transform: translateX(3px);
}

/* ===== Responsive ===== */
@media (max-width: 768px) {
  .breadcrumb-modern {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
  .icon-wrapper {
    width: 36px;
    height: 36px;
  }
  .card-header h5 {
    font-size: 1rem;
  }
  .btn {
    font-size: 0.85rem;
  }
}
</style>

{{-- ======================== --}}
{{-- ⚙️ SCRIPT REFRESH BUTTON --}}
{{-- ======================== --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const refreshBtn = document.getElementById('refreshBtn');
  refreshBtn.addEventListener('click', () => {
    refreshBtn.disabled = true;
    refreshBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Memuat...`;
    setTimeout(() => window.location.reload(), 1000);
  });
});
</script>
@endpush
@endsection
