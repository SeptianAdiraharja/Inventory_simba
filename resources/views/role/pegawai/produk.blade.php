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

  /* === Produk Card === */
  .product-card {
    border-radius: 1.25rem;
    border: none;
    background: #ffffff;
    box-shadow: 0 3px 12px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
  }

  .product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(255, 152, 0, 0.15);
  }

  /* === Gambar Produk (dibuat seragam tinggi dan proporsional) === */
  .product-card img {
    border-radius: 1.25rem 1.25rem 0 0;
    width: 100%;
    height: 220px; /* ✅ tinggi seragam */
    object-fit: cover; /* ✅ gambar tetap proporsional */
    object-position: center; /* ✅ fokus di tengah */
    background-color: #f9f9f9; /* warna latar fallback */
  }

  /* === Konten Kartu === */
  .product-card .card-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }

  .product-card h5 {
    font-size: 1.05rem;
    color: #5d4037;
  }

  .product-card p {
    color: #6b7280;
    font-size: 0.9rem;
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

  .btn-outline-secondary {
    border: 2px solid #FF9800;
    color: #FF9800;
    font-weight: 600;
  }

  .btn-outline-secondary:hover {
    background-color: #FFF3E0;
    color: #FF9800;
  }

  /* === Badge stok === */
  .badge-status {
    position: absolute;
    top: 10px;
    left: 0;
    padding: 0.4rem 0.8rem;
    border-radius: 0 6px 6px 0;
    font-size: 0.8rem;
    font-weight: 600;
  }

  /* === Alert === */
  .alert {
    border-radius: 12px;
    border: none;
    box-shadow: 0 2px 10px rgba(255, 152, 0, 0.1);
    font-size: 0.9rem;
  }

  .alert-success {
    background-color: #E8F5E9;
    color: #2e7d32;
  }

  .alert-danger {
    background-color: #FFEBEE;
    color: #c62828;
  }

  .alert-info {
    background-color: #FFF3E0;
    color: #FF9800;
  }

  @media (max-width: 768px) {
    .product-card img {
      height: 180px; /* versi mobile sedikit lebih kecil */
    }
  }
</style>

<!-- 🧭 Breadcrumb -->
<div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3 animate__animated animate__fadeInDown smooth-fade">
  <div class="d-flex align-items-center gap-2">
    <div class="breadcrumb-icon">
      <i class="bi bi-box-seam fs-5"></i>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0 align-items-center">
        <li class="breadcrumb-item">
          <a href="{{ route('pegawai.dashboard') }}" class="text-decoration-none fw-semibold" style="color:#FF9800;">
            Dashboard
          </a>
        </li>
        <li class="breadcrumb-item active fw-semibold text-dark" aria-current="page">
          Daftar Barang
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
<div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
    {{-- Judul --}}
    <div>
      <h4 class="mb-1 fw-bold text-dark">
        <i class="bi bi-grid-3x3-gap-fill me-2 text-warning"></i>Daftar Barang
      </h4>
      <small class="text-muted">Total {{ $items->total() }} produk tersedia</small>
    </div>

    {{-- Filter Dropdown --}}
    <div class="d-flex align-items-center gap-2">
      <label class="text-muted small mb-0">Urutkan:</label>
      <select name="sort"
              id="sortFilter"
              class="form-select form-select-sm shadow-sm"
              style="width: 200px; border-radius: 50px; border: 2px solid #FF9800;"
              onchange="applySortFilter(this.value)">
        <option value="stok_terbanyak" {{ request('sort', 'stok_terbanyak') == 'stok_terbanyak' ? 'selected' : '' }}>
          📦 Stok Terbanyak
        </option>
        <option value="stok_sedikit" {{ request('sort') == 'stok_sedikit' ? 'selected' : '' }}>
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
        <option value="nama_az" {{ request('sort') == 'nama_az' ? 'selected' : '' }}>
          🔤 A → Z
        </option>
        <option value="nama_za" {{ request('sort') == 'nama_za' ? 'selected' : '' }}>
          🔤 Z → A
        </option>
      </select>
    </div>
  </div>
</div>
<!-- 🔔 Flash Message -->
@if (session('success'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

@if (session('error'))
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

@if(isset($search) && $search)
  <div class="alert alert-info border-0 shadow-sm py-2 mb-4">
    <i class="bi bi-search me-2"></i> Hasil pencarian untuk:
    <strong class="text-dark">{{ $search }}</strong>
  </div>
@endif

<!-- 📦 Grid Produk -->
<div class="row gy-4">
  @forelse ($items as $item)
    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
      <div class="card product-card position-relative">
        <div class="position-relative">
          <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}">
          @if ($item->stock == 0)
            <span class="badge-status bg-danger text-white">Habis</span>
          @elseif ($item->stock < 5)
            <span class="badge-status bg-warning text-dark">Menipis</span>
          @endif
        </div>

        <div class="card-body d-flex flex-column justify-content-between">
          <h5 class="fw-semibold mb-2 text-truncate">{{ $item->name }}</h5>
          <p class="small mb-1"><i class="bi bi-tag me-1"></i> Kategori:
            <span class="fw-semibold text-dark">{{ $item->category->name ?? '-' }}</span>
          </p>
          <p class="small mb-3"><i class="bi bi-box me-1"></i> Stok:
            <span class="fw-semibold {{ $item->stock == 0 ? 'text-danger' : 'text-success' }}">{{ $item->stock }}</span>
          </p>

          <form action="{{ route('pegawai.permintaan.create') }}" method="POST" class="mt-auto">
            @csrf
            <input type="hidden" name="items[0][item_id]" value="{{ $item->id }}">
            <div class="d-flex align-items-center justify-content-between">
              <div class="input-group" style="max-width: 110px;">
                <input type="number" name="items[0][quantity]" class="form-control text-center border-warning"
                  value="1" min="1" {{ $item->stock == 0 ? 'disabled' : '' }}>
              </div>
              <button type="submit"
                class="btn btn-sm btn-primary ms-2 d-flex align-items-center shadow-sm"
                {{ $item->stock == 0 ? 'disabled' : '' }}>
                <i class="bi bi-cart-plus me-1"></i> Ajukan
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @empty
    <div class="col-12 text-center py-5">
      <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
      <p class="text-muted mb-0">Tidak ada produk ditemukan.</p>
    </div>
  @endforelse
</div>

@if ($items instanceof \Illuminate\Pagination\LengthAwarePaginator)
  {{-- PAGINATION --}}
  <div class="mt-4 d-flex justify-content-center">
    {{ $items->links('pagination::bootstrap-5') }}
  </div>
</div>
@endif


@endsection
<script>
function applySortFilter(sortValue) {
    const url = new URL(window.location.href);
    url.searchParams.set('sort', sortValue);
    window.location.href = url.toString();
}
</script>