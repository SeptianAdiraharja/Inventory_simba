@extends('layouts.index')
@section('title', 'Daftar Produk untuk Guest')
@section('content')

<style>
  body {
    background-color: #f4f6f9;
  }

  /* === Breadcrumb Modern === */
  .breadcrumb-icon {
    width: 38px;
    height: 38px;
    background: #FFF3E0;
    color: #FF9800;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: 0.3s ease;
  }

  .breadcrumb-icon:hover {
    transform: scale(1.1);
    background-color: #ffecb3;
  }

  .breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: #ffb74d;
    margin: 0 6px;
  }

  /* === Filter Section Styles === */
  .filter-section {
    background: white;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    margin-bottom: 24px;
  }

  .filter-label {
    font-size: 14px;
    font-weight: 600;
    color: #666;
    margin-bottom: 8px;
    display: block;
  }

  .filter-dropdown {
    background: white;
    border: 2px solid #E5E7EB;
    border-radius: 12px;
    color: #374151;
    font-weight: 500;
    padding: 10px 16px;
    width: 200px;
    text-align: left;
    transition: all 0.3s ease;
    position: relative;
  }

  .filter-dropdown:hover {
    border-color: #FF9800;
    background-color: #FFFBF5;
  }

  .filter-dropdown:focus {
    border-color: #FF9800;
    box-shadow: 0 0 0 3px rgba(255, 152, 0, 0.1);
  }

  .filter-dropdown::after {
    content: "";
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    width: 0;
    height: 0;
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-top: 5px solid #6B7280;
  }

  /* === Card Produk === */
  .card {
    border-radius: 1.25rem;
    border: none;
    background: #ffffff;
    transition: all 0.3s ease;
  }

  .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(255, 152, 0, 0.2);
  }

  .card-body h5 {
    font-size: 1.05rem;
    color: #5d4037;
  }

  .card-body p {
    color: #6b7280;
  }

  /* === Tombol === */
  .btn {
    border-radius: 50px !important;
    transition: all 0.25s ease;
    font-weight: 500;
  }

  .btn-primary {
    background: linear-gradient(90deg, #FF9800, #FFB74D);
    border: none;
  }

  .btn-primary:hover {
    background: linear-gradient(90deg, #FB8C00, #FFA726);
    box-shadow: 0 4px 12px rgba(255, 152, 0, 0.3);
  }

  .btn-success {
    background: linear-gradient(90deg, #43A047, #66BB6A);
    border: none;
  }

  .btn-outline-secondary {
    border: 2px solid #FF9800;
    color: #FF9800;
    font-weight: 600;
  }

  .btn-outline-secondary:hover {
    background-color: #FFF3E0;
    color: #FF9800;
  }

  /* === Floating Cart === */
  #openCartModal {
    background: linear-gradient(90deg, #FF9800, #FFB74D);
    border: none;
    box-shadow: 0 10px 20px rgba(255, 152, 0, 0.4);
    transition: all 0.3s ease;
    position: relative;
    overflow: visible;
  }

  #openCartModal:hover {
    transform: scale(1.1);
    box-shadow: 0 12px 25px rgba(255, 152, 0, 0.5);
  }

  #openCartModal .badge {
    position: absolute;
    top: -10px;
    right: -10px;
    background-color: #e53935;
    color: #fff;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 5px 7px;
    border-radius: 10px;
    box-shadow: 0 0 0 2px #fff;
    z-index: 10;
  }

  /* === Modal === */
  .modal-content {
    border-radius: 1.25rem;
    border: none;
    overflow: hidden;
  }

  .modal-header {
    background: linear-gradient(90deg, #FF9800, #FFB74D);
    color: white;
    border-bottom: none;
  }

  .modal-footer {
    border-top: 1px solid #ffe0b2;
    background-color: #fff8e1;
  }

  /* === Tabel Cart === */
  .table-hover tbody tr:hover {
    background-color: #FFF8E1 !important;
    transition: 0.25s;
  }

  .table thead {
    background-color: #FFF3E0;
    color: #5d4037;
  }

  /* === Animasi === */
  .smooth-fade {
    animation: smoothFade 0.8s ease;
  }

  @keyframes smoothFade {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
  }

  @media (max-width: 768px) {
    .breadcrumb-extra { display: none; }
    .card-body h5 { font-size: 1rem; }
    #openCartModal { width: 60px; height: 60px; font-size: 1.2rem; }
    table { font-size: 0.9rem; }
    .filter-dropdown { width: 100%; margin-bottom: 10px; }
    .filter-section .d-flex { flex-direction: column; }
  }
</style>

<!-- 🧭 BREADCRUMB -->
<div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3 smooth-fade">
  <div class="d-flex align-items-center gap-2">
    <div class="breadcrumb-icon">
      <i class="bi bi-house-door-fill fs-5"></i>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0 align-items-center">
        <li class="breadcrumb-item">
          <a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold" style="color:#FF9800;">
            Dashboard
          </a>
        </li>
        <li class="breadcrumb-item">
          <a href="{{ route('admin.guests.index') }}" class="text-decoration-none fw-semibold" style="color:#FF9800;">
            Daftar Guest
          </a>
        </li>
        <li class="breadcrumb-item active fw-semibold text-dark" aria-current="page">
          Produk untuk Guest: {{ $guest->name ?? 'Unknown' }}
        </li>
      </ol>
    </nav>
  </div>
  <div class="breadcrumb-extra text-end">
    <small class="text-muted">
      <i class="bi bi-calendar-check me-1"></i>{{ now()->format('d M Y, H:i') }}
    </small>
  </div>
</div>

<!-- 🔍 FILTER SECTION -->
<div class="filter-section smooth-fade">
  <form action="{{ route('admin.produk.byGuest', $guest->id ?? 0) }}" method="GET" id="filterForm">
    <div class="row align-items-center">

      <!-- BAGIAN KIRI (Dropdown Sorting) -->
      <div class="col-md-9">
        <div class="d-flex flex-wrap align-items-center gap-4">

          <!-- Sort Dropdown -->
          <div>
            <span class="filter-label">Urutkan:</span>
            <select name="sort" class="form-select filter-dropdown"
                    onchange="document.getElementById('filterForm').submit()">
              <option value="stok_terbanyak" {{ request('sort', 'stok_terbanyak') == 'stok_terbanyak' ? 'selected' : '' }}>
                📦 Stok Terbanyak
              </option>
              <option value="stok_menipis" {{ request('sort') == 'stok_menipis' ? 'selected' : '' }}>
                ⚠️ Stok Menipis
              </option>
              <option value="paling_laris" {{ request('sort') == 'paling_laris' ? 'selected' : '' }}>
                🔥 Paling Laris
              </option>
              <option value="terbaru" {{ request('sort') == 'terbaru' ? 'selected' : '' }}>
                🆕 Terbaru
              </option>
              <option value="terlama" {{ request('sort') == 'terlama' ? 'selected' : '' }}>
                📅 Terlama
              </option>
              <option value="a_z" {{ request('sort') == 'a_z' ? 'selected' : '' }}>
                🔤 A → Z
              </option>
              <option value="z_a" {{ request('sort') == 'z_a' ? 'selected' : '' }}>
                🔤 Z → A
              </option>
            </select>
          </div>

        </div>
      </div>

      <!-- BAGIAN KANAN (Button Refresh) -->
      <div class="col-md-3 d-flex justify-content-end">
        <button type="button" class="btn btn-outline-warning refresh-btn"
                onclick="resetFilters()"
                style="border-radius: 12px; padding: 10px 16px; border: 2px solid #FF9800; color: #FF9800; font-weight: 500; transition: all 0.3s ease;">
          <i class="ri-refresh-line me-1"></i> Refresh
        </button>
      </div>

    </div>
  </form>
</div>

<script>
function resetFilters() {
  const baseUrl = "{{ route('admin.produk.byGuest', $guest->id ?? 0) }}";
  window.location.href = baseUrl; // Reset sempurna
}
</script>


<!-- === FLOATING CART BUTTON === -->
<button class="btn btn-primary shadow-lg position-fixed rounded-circle d-flex align-items-center justify-content-center"
  id="openCartModal"
  data-guest-id="{{ $guest->id ?? '' }}"
  style="bottom:25px; right:25px; width:70px; height:70px; font-size:1.5rem; z-index:1050;">
  <i class="ri-shopping-cart-2-line"></i>
  @if(isset($cartItems) && $cartItems->filter(fn($i)=>is_null($i->pivot->released_at))->count() > 0)
  <span class="position-absolute badge rounded-pill"
    style="top:-5px; right:-5px; font-size:0.8rem; padding:6px 8px;">
    {{ $cartItems->filter(fn($i)=>is_null($i->pivot->released_at))->count() }}
  </span>
  @endif
</button>

<!-- === DAFTAR PRODUK === -->
<div class="row gy-4 mt-3 animate__animated animate__fadeInUp">
  @forelse ($items as $item)
  <div class="col-xl-3 col-lg-4 col-md-6">
    <div class="card shadow-sm">
      <img src="{{ asset('storage/' . $item->image) }}" class="card-img-top"
           alt="{{ $item->name }}" style="height:220px; object-fit:cover; border-radius:1.25rem 1.25rem 0 0;">
      <div class="card-body d-flex flex-column justify-content-between">
        <div>
          <h5 class="fw-semibold mb-2">{{ $item->name }}</h5>
          <p class="small mb-1"><i class="ri-folder-line me-1"></i> Kategori:
            <span class="fw-semibold text-dark">{{ $item->category->name ?? '-' }}</span>
          </p>
          <p class="small mb-0"><i class="ri-barcode-box-line me-1"></i> Stok:
            <span class="{{ $item->stock > 0 ? 'text-success fw-bold' : 'text-danger fw-bold' }}">{{ $item->stock }}</span>
          </p>
        </div>
        <button type="button" class="btn btn-primary mt-3 w-100"
                data-bs-toggle="modal" data-bs-target="#scanModal-{{ $item->id }}"
                {{ $item->stock == 0 ? 'disabled' : '' }}>
          <i class="ri-scan-line me-1"></i> Keluarkan Barang
        </button>
      </div>
    </div>
  </div>

  <!-- === MODAL SCAN ITEM === -->
  <div class="modal fade" id="scanModal-{{ $item->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form action="{{ route('admin.produk.scan', $guest->id ?? 0) }}" method="POST">
          @csrf
          <div class="modal-header">
            <h5 class="modal-title fw-semibold"><i class="ri-scan-line me-2"></i>Scan Barang: {{ $item->name }}</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="guest_id" value="{{ $guest->id ?? '' }}">
            <input type="hidden" name="item_id" value="{{ $item->id }}">
            <div class="mb-3">
              <label class="form-label fw-semibold">Jumlah Barang</label>
              <input type="number" name="quantity" class="form-control form-control-lg rounded-3 border-warning"
                     min="1" max="{{ $item->stock }}" value="1" required>
              <small class="text-muted">Maksimum stok: {{ $item->stock }}</small>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Masukkan / Scan Barcode</label>
              <input type="text" name="barcode" class="form-control form-control-lg rounded-3 border-warning"
                     placeholder="Arahkan scanner ke sini..." required>
              <small class="text-muted">Tekan Enter setelah scan untuk menyimpan data.</small>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-success"><i class="ri-check-line me-1"></i> Simpan</button>
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
              <i class="ri-close-line me-1"></i> Batal
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  @empty
  <div class="col-12">
    <div class="text-center py-5">
      <i class="ri-inbox-line display-1 text-muted"></i>
      <h4 class="text-muted mt-3">Tidak ada produk ditemukan</h4>
      <p class="text-muted">Coba ubah filter pencarian Anda</p>
    </div>
  </div>
  @endforelse
</div>

<!-- === MODAL CART === -->
<div class="modal fade" id="cartModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold"><i class="ri-shopping-cart-line me-2"></i>Keranjang Guest</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light">

                <!-- 🎯 PROGRESS BAR BATAS PENGELUARAN -->
                <div class="p-3 border rounded-3 mb-3 bg-white shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0 fw-semibold text-warning">
                            <i class="ri-calendar-line me-2"></i>Pengeluaran Minggu Ini
                        </h6>
                        <span class="fw-bold text-warning" id="releaseProgressText">0/3 kali</span>
                    </div>

                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-warning"
                             role="progressbar"
                             id="releaseProgressBar"
                             style="width: 0%;"
                             aria-valuenow="0"
                             aria-valuemin="0"
                             aria-valuemax="100">
                        </div>
                    </div>

                    <div class="alert alert-warning alert-dismissible fade show mt-3 py-2 px-3"
                         id="limitWarning"
                         style="display: none;"
                         role="alert">
                        <i class="ri-error-warning-line me-2"></i>
                        <span id="limitWarningText">Guest telah mencapai batas maksimal pengeluaran barang.</span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>

                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Nama Barang</th>
                            <th>Kode</th>
                            <th class="text-center">Jumlah</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="cartTableBody">
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">
                                <i class="ri-information-line me-1"></i>Keranjang kosong
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <form id="releaseForm" action="{{ route('admin.produk.release', $guest->id ?? 0) }}" method="POST">
                    @csrf
                    <button type="button" id="confirmReleaseBtn" class="btn btn-success">
                        <i class="ri-send-plane-line me-1"></i> Keluarkan Semua
                    </button>
                </form>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 🧭 PAGINATION -->
@if ($items instanceof \Illuminate\Pagination\LengthAwarePaginator && $items->hasPages())
  <div class="d-flex justify-content-center mt-4">
    {{ $items->appends(request()->query())->links('pagination::bootstrap-5') }}
  </div>
@endif

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/guest-cart.js') }}"></script>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const releaseForm = document.getElementById("releaseForm");
  const confirmBtn = document.getElementById("confirmReleaseBtn");

  // === Konfirmasi saat klik "Keluarkan Semua" ===
  if (confirmBtn) {
    confirmBtn.addEventListener("click", (e) => {
      e.preventDefault();

      Swal.fire({
        title: "Yakin ingin mengeluarkan semua barang?",
        text: "Setelah ini stok akan langsung berkurang.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#43A047",
        cancelButtonColor: "#d33",
        confirmButtonText: "Ya, keluarkan!",
        cancelButtonText: "Batal"
      }).then((result) => {
        if (result.isConfirmed) {
          releaseForm.submit();
        }
      });
    });
  }

  // === SweetAlert flash message dari session Laravel ===
  @if (session('success'))
    Swal.fire({
      icon: 'success',
      title: 'Berhasil!',
      html: `{!! session('success') !!}`,
      showConfirmButton: false,
      timer: 2500
    });
  @elseif (session('error'))
    Swal.fire({
      icon: 'error',
      title: 'Gagal!',
      html: `{!! session('error') !!}`,
      showConfirmButton: true
    });
  @elseif (session('warning'))
    Swal.fire({
      icon: 'warning',
      title: 'Peringatan!',
      html: `{!! session('warning') !!}`,
      showConfirmButton: true
    });
  @endif
});
</script>
@endpush
