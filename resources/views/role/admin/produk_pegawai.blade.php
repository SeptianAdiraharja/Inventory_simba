@extends('layouts.index')
@section('title', 'Daftar Produk untuk Pegawai')
@section('content')

<style>
  /* ===== VARIABLES & BASE STYLES ===== */
  :root {
    --primary-orange: #FF9800;
    --light-orange: #FFB74D;
    --warning-bg: #fff3cd;
    --danger-bg: #f8d7da;
    --success-color: #43A047;
  }

  body {
    background-color: #f4f6f9;
  }

  /* ===== EMPTY STATE STYLES ===== */
  .empty-state {
    max-width: 400px;
    margin: 0 auto;
    padding: 2rem 0;
  }

  .empty-state i {
    opacity: 0.5;
    margin-bottom: 1rem;
  }

  .empty-state h4 {
    font-weight: 600;
    margin-bottom: 0.5rem;
  }

  .empty-state p {
    font-size: 0.95rem;
    line-height: 1.5;
  }

  /* ===== FILTER SECTION STYLES ===== */
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
    border-color: var(--primary-orange);
    background-color: #FFFBF5;
  }

  .filter-dropdown:focus {
    border-color: var(--primary-orange);
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

  /* ===== BREADCRUMB STYLES ===== */
  .breadcrumb-icon {
    width: 38px;
    height: 38px;
    background: #FFF3E0;
    color: var(--primary-orange);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: 0.3s;
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

  /* ===== CARD PRODUCT STYLES ===== */
  .card {
    border-radius: 1.25rem;
    border: none;
    background: #ffffff;
    transition: all 0.3s ease;
    position: relative;
  }

  .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(255, 152, 0, 0.2);
  }

  .card-body h5 {
    font-size: 1.05rem;
    color: #5d4037;
  }

  /* ===== STOCK LABEL STYLES ===== */
  .stock-label {
    position: absolute;
    top: 10px;
    right: 10px;
    padding: 4px 8px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 600;
    z-index: 2;
  }

  .stock-low {
    background-color: var(--warning-bg);
    color: #856404;
    border: 1px solid #ffeaa7;
  }

  .stock-out {
    background-color: var(--danger-bg);
    color: #721c24;
    border: 1px solid #f5c6cb;
  }

  .card-img-container {
    position: relative;
  }

  .card[data-stock="0"] {
    opacity: 0.7;
    background-color: #f8f9fa;
  }

  /* ===== BUTTON STYLES ===== */
  .btn {
    border-radius: 50px !important;
    transition: all 0.25s ease;
    font-weight: 500;
  }

  .btn-primary {
    background: linear-gradient(90deg, var(--primary-orange), var(--light-orange));
    border: none;
  }

  .btn-primary:hover {
    background: linear-gradient(90deg, #FB8C00, #FFA726);
    box-shadow: 0 4px 12px rgba(255, 152, 0, 0.3);
  }

  .btn-success {
    background: linear-gradient(90deg, var(--success-color), #66BB6A);
    border: none;
  }

  .btn-outline-secondary {
    border: 2px solid var(--primary-orange);
    color: var(--primary-orange);
    font-weight: 600;
  }

  .btn-outline-secondary:hover {
    background-color: #FFF3E0;
    color: var(--primary-orange);
  }

  /* ===== FLOATING CART STYLES ===== */
  #openCartModal {
    background: linear-gradient(90deg, var(--primary-orange), var(--light-orange));
    border: none;
    box-shadow: 0 10px 20px rgba(255, 152, 0, 0.4);
    transition: all 0.3s ease;
    position: relative;
  }

  #openCartModal:hover {
    transform: scale(1.1);
    box-shadow: 0 12px 25px rgba(255, 152, 0, 0.5);
  }

  /* ===== CART BADGE STYLES ===== */
  .cart-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background: linear-gradient(135deg, #e53935, #f44336);
    color: white;
    border-radius: 40%;
    min-width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    font-weight: 700;
    box-shadow: 0 2px 8px rgba(229, 57, 53, 0.4);
    border: 2px solid #ffffff;
    z-index: 1051;
    animation: pulse 2s infinite;
    display: none;
  }

  @keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
  }

  /* ===== MODAL STYLES ===== */
  .modal-content {
    border-radius: 1.25rem;
    border: none;
    overflow: hidden;
  }

  .modal-header {
    background: linear-gradient(90deg, var(--primary-orange), var(--light-orange));
    color: white;
    border-bottom: none;
  }

  .modal-footer {
    border-top: 1px solid #ffe0b2;
    background-color: #fff8e1;
  }

  .table-hover tbody tr:hover {
    background-color: #FFF8E1 !important;
    transition: 0.25s;
  }

  /* ===== ANIMATION STYLES ===== */
  .smooth-fade {
    animation: smoothFade 0.8s ease;
  }

  @keyframes smoothFade {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
  }

  /* ===== TOAST & SNACKBAR STYLES ===== */
  #toast-container {
    z-index: 1060;
  }

  .snackbar {
    visibility: hidden;
    min-width: 250px;
    background-color: #333;
    color: #fff;
    text-align: center;
    border-radius: 8px;
    padding: 16px;
    position: fixed;
    z-index: 1070;
    left: 50%;
    bottom: 30px;
    transform: translateX(-50%);
    font-size: 14px;
  }

  .snackbar.show {
    visibility: visible;
    animation: fadein 0.5s, fadeout 0.5s 2.5s;
  }

  @keyframes fadein {
    from {bottom: 0; opacity: 0;}
    to {bottom: 30px; opacity: 1;}
  }

  @keyframes fadeout {
    from {bottom: 30px; opacity: 1;}
    to {bottom: 0; opacity: 0;}
  }
</style>

<!-- ===== BREADCRUMB SECTION ===== -->
<!-- Menampilkan navigasi dan informasi waktu saat ini -->
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
        <li class="breadcrumb-item active fw-semibold text-dark" aria-current="page">
          Daftar Produk Pegawai: {{ $pegawai->name }}
        </li>
      </ol>
    </nav>
  </div>
  <div class="breadcrumb-extra text-end">
    <small class="text-muted"><i class="bi bi-calendar-check me-1"></i>{{ now()->format('d M Y, H:i') }}</small>
  </div>
</div>

<!-- ===== FILTER SECTION ===== -->
<!-- Form untuk filtering dan sorting produk -->
<div class="filter-section smooth-fade">
  <form action="{{ route('admin.pegawai.produk', ['id' => $pegawai->id]) }}" method="GET" id="filterForm">
    <div class="row align-items-center">
      <!-- Bagian kiri (dropdown sort + hidden input) -->
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

          <!-- Hidden inputs untuk mempertahankan parameter pencarian -->
          @if(request('q'))
            <input type="hidden" name="q" value="{{ request('q') }}">
          @endif
          @if(request('kategori'))
            <input type="hidden" name="kategori" value="{{ request('kategori') }}">
          @endif
        </div>
      </div>

      <!-- Bagian kanan (Refresh button) -->
      <div class="col-md-3 d-flex justify-content-end">
        <button type="button" class="btn btn-outline-warning refresh-btn"
                onclick="resetFilters()"
                style="border-radius: 12px; padding: 10px 20px; border: 2px solid #FF9800; color: #FF9800; font-weight: 500; transition: all 0.3s ease;">
          <i class="ri-refresh-line me-1"></i> Refresh
        </button>
      </div>
    </div>
  </form>
</div>

<!-- ===== FLOATING CART BUTTON ===== -->
<!-- Tombol keranjang mengambang dengan badge notifikasi -->
<div class="position-fixed" style="bottom:25px; right:25px; z-index:1050;">
  <button class="btn btn-primary shadow-lg rounded-circle d-flex align-items-center justify-content-center"
    id="openCartModal"
    data-bs-toggle="modal"
    data-bs-target="#cartModal"
    data-pegawai-id="{{ $pegawai->id ?? '' }}"
    style="width:70px; height:70px; font-size:1.5rem;">
    <i class="ri-shopping-cart-2-line"></i>
  </button>
  <!-- Badge untuk menampilkan jumlah item di keranjang -->
  <span class="cart-badge" id="cartBadge">0</span>
</div>

<!-- ===== PRODUCT LIST SECTION ===== -->
<div class="row gy-4 mt-3 animate__animated animate__fadeInUp">
  {{-- Empty State ketika tidak ada produk --}}
  @if($items->isEmpty())
    <div class="col-12 text-center py-5">
      <div class="empty-state">
        <i class="ri-inbox-line display-1 text-muted"></i>
        <h4 class="mt-3 text-muted">
          @if(request('sort') == 'stok_menipis')
            🎉 Tidak Ada Barang dengan Stok Menipis
          @elseif(request('q'))
            Tidak ada barang yang cocok dengan pencarian "{{ request('q') }}"
          @else
            Belum ada barang yang tersedia
          @endif
        </h4>
        <p class="text-muted mb-4">
          @if(request('sort') == 'stok_menipis')
            Yeay! Semua barang memiliki stok yang cukup saat ini.
          @else
            Silakan coba dengan kriteria pencarian yang berbeda.
          @endif
        </p>

        {{-- Tombol reset filter --}}
        @if(request('sort') || request('q') || request('kategori'))
          <a href="{{ route('admin.pegawai.produk', ['id' => $pegawai->id]) }}"
             class="btn btn-primary">
            <i class="ri-refresh-line me-1"></i> Tampilkan Semua Barang
          </a>
        @endif
      </div>
    </div>
  @else
    {{-- Tampilkan items produk --}}
    @foreach ($items as $item)
    <div class="col-xl-3 col-lg-4 col-md-6">
      <!-- Card Produk -->
      <div class="card shadow-sm" data-item-id="{{ $item->id }}" data-stock="{{ $item->stock }}">
        <div class="card-img-container">
          <img src="{{ asset('storage/' . $item->image) }}" class="card-img-top"
               alt="{{ $item->name }}" style="height:220px; object-fit:cover; border-radius:1.25rem 1.25rem 0 0;">

          <!-- Label Status Stok -->
          @if($item->stock == 0)
            <span class="stock-label stock-out">
              <i class="ri-error-warning-line me-1"></i>Barang Habis
            </span>
          @elseif($item->stock <= 5)
            <span class="stock-label stock-low">
              <i class="ri-alert-line me-1"></i>Hampir Habis
            </span>
          @endif
        </div>

        <div class="card-body d-flex flex-column justify-content-between">
          <div>
            <h5 class="fw-semibold mb-2">{{ $item->name }}</h5>
            <p class="small mb-1"><i class="ri-folder-line me-1"></i> Kategori:
              <span class="fw-semibold text-dark">{{ $item->category->name ?? '-' }}</span>
            </p>
            <p class="small mb-0">
              <i class="ri-barcode-box-line me-1"></i> Stok:
              <span class="fw-bold
                @if($item->stock == 0) text-danger
                @elseif($item->stock <= 5) text-warning
                @else text-success
                @endif">
                {{ $item->stock }}
              </span>
            </p>
          </div>

          {{-- Tombol Aksi --}}
          @if($item->stock > 0)
            <button type="button" class="btn btn-primary mt-3 w-100 scan-btn"
                    data-bs-toggle="modal" data-bs-target="#scanModal-{{ $item->id }}"
                    data-item-id="{{ $item->id }}"
                    data-item-name="{{ $item->name }}">
              <i class="ri-scan-line me-1"></i> Keluarkan Barang
            </button>
          @else
            <button type="button" class="btn btn-secondary mt-3 w-100" disabled>
              <i class="ri-close-line me-1"></i> Stok Habis
            </button>
          @endif
        </div>
      </div>
    </div>

    <!-- ===== MODAL SCAN BARANG ===== -->
    @if($item->stock > 0)
    <div class="modal fade" id="scanModal-{{ $item->id }}" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <form class="scan-form" method="POST" action="{{ route('admin.pegawai.scan', ['id' => $pegawai->id ?? 0]) }}" data-item-id="{{ $item->id }}">
            @csrf
            <div class="modal-header">
              <h5 class="modal-title fw-semibold">
                <i class="ri-scan-line me-2"></i>Scan Barang: {{ $item->name }}
                @if($item->stock <= 5)
                  <span class="badge bg-warning ms-2">Stok Menipis</span>
                @endif
              </h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
              <input type="hidden" name="pegawai_id" value="{{ $pegawai->id ?? '' }}">
              <input type="hidden" name="item_id" value="{{ $item->id }}">

              <!-- Input Jumlah Barang -->
              <div class="mb-3">
                <label class="form-label fw-semibold">Jumlah Barang</label>
                <input type="number" name="quantity" class="form-control form-control-lg rounded-3 border-warning quantity-input"
                       min="1" max="{{ $item->stock }}" value="1" required>
                <small class="text-muted">Maksimum stok: <span class="stock-max">{{ $item->stock }}</span></small>
                @if($item->stock <= 5)
                  <small class="text-warning d-block mt-1">
                    <i class="ri-alert-line me-1"></i>Stok barang ini hampir habis!
                  </small>
                @endif
              </div>

              <!-- Input Barcode -->
              <div class="mb-3">
                <label class="form-label fw-semibold">Masukkan / Scan Barcode</label>
                <input type="text" name="barcode" class="form-control form-control-lg rounded-3 border-warning barcode-input"
                       placeholder="Arahkan scanner ke sini..." required autofocus>
                <small class="text-muted">Tekan Enter setelah scan untuk menyimpan data.</small>
              </div>
            </div>

            <div class="modal-footer">
              <button type="submit" class="btn btn-success submit-btn">
                <i class="ri-check-line me-1"></i> Simpan
              </button>
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                <i class="ri-close-line me-1"></i> Batal
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    @endif
    @endforeach
  @endif
</div>

<!-- ===== MODAL CART ===== -->
<!-- Modal untuk menampilkan keranjang barang pegawai -->
<div class="modal fade" id="cartModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-semibold">
          <i class="ri-shopping-cart-line me-2"></i>Keranjang Pegawai: {{ $pegawai->name }}
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <!-- Progress Bar Pengeluaran Mingguan -->
      <div class="px-4 pt-3 border-bottom">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <small class="fw-semibold text-dark">Progress Pengeluaran Minggu Ini:</small>
          <small class="fw-bold" id="progressText">0/5</small>
        </div>
        <div class="progress mb-3" style="height: 10px; border-radius: 10px;">
          <div id="progressBar" class="progress-bar"
               role="progressbar"
               style="border-radius: 10px; transition: all 0.5s ease;"
               aria-valuenow="0" aria-valuemin="0" aria-valuemax="5"></div>
        </div>
        <small class="text-muted d-block mb-2">
          <i class="ri-information-line me-1"></i>
          <span id="progressMessage">Setiap pegawai dapat mengeluarkan barang maksimal 5 kali per minggu</span>
        </small>
      </div>

      <!-- Content Keranjang -->
      <div class="modal-body bg-light">
        <div id="cartContent">
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
      </div>

      <!-- Footer Modal -->
      <div class="modal-footer">
        <button type="button" class="btn btn-success" id="saveCartButton">
          <i class="ri-send-plane-line me-1"></i> Simpan Keranjang
        </button>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          <i class="ri-close-line me-1"></i> Tutup
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ===== TOAST CONTAINER ===== -->
<!-- Container untuk menampilkan notifikasi toast -->
<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1060;"></div>

<!-- ===== SNACKBAR ===== -->
<!-- Element untuk menampilkan snackbar notifications -->
<div id="snackbar" class="snackbar"></div>

<!-- ===== PAGINATION ===== -->
@if ($items->hasPages())
  <div class="d-flex justify-content-center mt-4">
    {{ $items->appends(request()->query())->links('pagination::bootstrap-5') }}
  </div>
@endif

@endsection

@push('scripts')
<!-- ===== JAVASCRIPT CONFIGURATION ===== -->
<script>
/**
 * Konfigurasi global untuk aplikasi
 * Menyimpan data yang diperlukan untuk operasi JavaScript
 */
window.PegawaiApp = {
    id: {{ $pegawai->id }},
    csrf: "{{ csrf_token() }}",
    routes: {
        scan: "{{ route('admin.pegawai.scan', ['id' => $pegawai->id]) }}",
        cart: "{{ route('admin.pegawai.cart', ['id' => $pegawai->id]) }}",
        saveCart: "{{ route('admin.pegawai.cart.save', ['id' => $pegawai->id]) }}",
        deleteItemBase: "{{ route('admin.pegawai.cart.item.destroy', ['pegawai' => $pegawai->id, 'id' => 'ITEM_ID']) }}"
    }
};
</script>

<!-- ===== EXTERNAL JAVASCRIPT ===== -->
<script src="{{ asset('js/admin-produk-pegawai.js') }}"></script>
@endpush