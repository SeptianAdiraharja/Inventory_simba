@extends('layouts.index')
@section('content')

  @if(isset($search) && $search)
      <div class="alert alert-info">
          Menampilkan hasil pencarian untuk: "<strong>{{ $search }}</strong>"
          <a href="{{ route('admin.pegawai.produk', $pegawai->id) }}" class="float-end">Tampilkan semua</a>
      </div>
  @endif

  {{-- Header --}}
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold text-primary mb-0">
      <i class="ri-store-2-line me-2"></i> Produk Pegawai: {{ $pegawai->name }}
    </h4>
    <a href="{{ route('admin.pegawai.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
      <i class="ri-arrow-left-line me-1"></i> Kembali
    </a>
  </div>

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
  }
  .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(255, 152, 0, 0.2);
  }
  .card-body h5 {
    font-size: 1.05rem;
    color: #5d4037;
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
  }
  #openCartModal:hover {
    transform: scale(1.1);
    box-shadow: 0 12px 25px rgba(255, 152, 0, 0.5);
  }
  #openCartModal .badge {
    background-color: #e53935;
    box-shadow: 0 0 0 2px #fff;
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
        <li class="breadcrumb-item">
          <a href="{{ route('admin.pegawai.index') }}" class="text-decoration-none fw-semibold" style="color:#FF9800;">
            Daftar Pegawai
          </a>
        </li>
        <li class="breadcrumb-item active fw-semibold text-dark" aria-current="page">
          Produk Pegawai: {{ $pegawai->name }}
        </li>
      </ol>
    </nav>
  </div>
  <div class="breadcrumb-extra text-end">
    <small class="text-muted"><i class="bi bi-calendar-check me-1"></i>{{ now()->format('d M Y, H:i') }}</small>
  </div>
</div>

<!-- 🛒 Floating Cart Button -->
<button class="btn btn-primary shadow-lg position-fixed rounded-circle d-flex align-items-center justify-content-center"
  id="openCartModal"
  data-bs-toggle="modal"
  data-bs-target="#cartModal"
  style="bottom:25px; right:25px; width:70px; height:70px; font-size:1.5rem; z-index:1050;">
  <i class="ri-shopping-cart-2-line"></i>
  <span class="position-absolute badge rounded-pill" id="cartBadge"
    style="top:-5px; right:-5px; font-size:0.8rem; padding:6px 8px; display:none;">
    0
  </span>
</button>

<!-- 📦 Daftar Produk -->
<div class="row gy-4 mt-3 animate__animated animate__fadeInUp">
  @foreach ($items as $item)
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
        <button type="button" class="btn btn-primary mt-3 w-100 item-scan-btn"
                data-bs-toggle="modal" data-bs-target="#scanModal-{{ $item->id }}"
                data-item-id="{{ $item->id }}"
                data-item-name="{{ $item->name }}"
                {{ $item->stock == 0 ? 'disabled' : '' }}>
          <i class="ri-scan-line me-1"></i> Keluarkan Barang
        </button>
      </div>
    </div>
  </div>

  <!-- 🔍 Modal Scan Barang -->
  <div class="modal fade" id="scanModal-{{ $item->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form class="scan-form" method="POST" data-pegawai-id="{{ $pegawai->id }}">
          @csrf
          <div class="modal-header">
            <h5 class="modal-title fw-semibold">
              <i class="ri-scan-line me-2"></i>Scan Barang: {{ $item->name }}
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">
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
                     placeholder="Arahkan scanner ke sini..." required autofocus>
              <small class="text-muted">Tekan Enter setelah scan untuk menyimpan data.</small>
            </div>
          </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-success">
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
          <p class="text-center text-muted">Memuat data keranjang...</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" id="saveCartButton">
          <i class="ri-save-line me-1"></i> Simpan Keranjang
        </button>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          <i class="ri-close-line me-1"></i> Tutup
        </button>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Pass data dari Blade ke JavaScript menggunakan URL langsung
    window.PegawaiApp = {
        pegawaiId: {{ $pegawai->id }},
        csrfToken: "{{ csrf_token() }}",
        routes: {
            scan: "/admin/pegawai/{{ $pegawai->id }}/scan",
            cart: "/admin/pegawai/{{ $pegawai->id }}/cart",
            saveCart: "/admin/pegawai/{{ $pegawai->id }}/cart/save",
            deleteItemBase: "/admin/pegawai/{{ $pegawai->id }}/cart/item/"
        }
    };
</script>
<script src="{{ asset('js/admin-produk-pegawai.js') }}"></script>
@endpush