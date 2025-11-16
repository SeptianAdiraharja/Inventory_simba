@extends('layouts.index')
@section('content')

<style>
  body {
    background-color: #f4f6f9;
  }

  /* === Breadcrumb === */
  .breadcrumb-icon {
    width: 38px; height: 38px;
    background: #FFF3E0;
    color: #FF9800;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
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

  /* === Card Produk === */
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

  /* === Label Stok === */
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
    background-color: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
  }

  .stock-out {
    background-color: #f8d7da;
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
  }
  #openCartModal:hover {
    transform: scale(1.1);
    box-shadow: 0 12px 25px rgba(255, 152, 0, 0.5);
  }

  /* === PERBAIKAN: Badge di luar border === */
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

  .table-hover tbody tr:hover {
    background-color: #FFF8E1 !important;
    transition: 0.25s;
  }

  .smooth-fade {
    animation: smoothFade 0.8s ease;
  }
  @keyframes smoothFade {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
  }

  /* === Toast & Snackbar === */
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

<!-- 🧭 Breadcrumb -->
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

<!-- 🛒 Floating Cart Button - DIPERBAIKI -->
<div class="position-fixed" style="bottom:25px; right:25px; z-index:1050;">
  <button class="btn btn-primary shadow-lg rounded-circle d-flex align-items-center justify-content-center"
    id="openCartModal"
    data-bs-toggle="modal"
    data-bs-target="#cartModal"
    data-pegawai-id="{{ $pegawai->id ?? '' }}"
    style="width:70px; height:70px; font-size:1.5rem;">
    <i class="ri-shopping-cart-2-line"></i>
  </button>
  <!-- 🆕 Badge berada di luar tombol -->
  <span class="cart-badge" id="cartBadge">0</span>
</div>

<!-- 📦 Daftar Produk -->
<div class="row gy-4 mt-3 animate__animated animate__fadeInUp">
  @foreach ($items as $item)
  <div class="col-xl-3 col-lg-4 col-md-6">
    <div class="card shadow-sm" data-item-id="{{ $item->id }}" data-stock="{{ $item->stock }}">
      <div class="card-img-container">
        <img src="{{ asset('storage/' . $item->image) }}" class="card-img-top"
             alt="{{ $item->name }}" style="height:220px; object-fit:cover; border-radius:1.25rem 1.25rem 0 0;">

        <!-- 🔥 LABEL STOK -->
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

  <!-- 🔍 Modal Scan Barang -->
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
</div>

<!-- 🧾 Modal Cart -->
<div class="modal fade" id="cartModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-semibold">
          <i class="ri-shopping-cart-line me-2"></i>Keranjang Pegawai: {{ $pegawai->name }}
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
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

<!-- 🔔 Toast Container -->
<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1060;"></div>

<!-- 📱 Snackbar -->
<div id="snackbar" class="snackbar"></div>

<!-- 🧭 Pagination -->
@if ($items->hasPages())
  <div class="d-flex justify-content-center mt-4">
    {{ $items->appends(request()->query())->links('pagination::bootstrap-5') }}
  </div>
@endif

@endsection

@push('scripts')
<script>
// Data global untuk JavaScript
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
<script src="{{ asset('js/admin-produk-pegawai.js') }}"></script>
@endpush