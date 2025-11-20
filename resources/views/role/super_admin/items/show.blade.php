@extends('layouts.index')
@section('title', 'Tampil Barang')
@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- ======================== --}}
  {{-- 🧭 BREADCRUMB --}}
  {{-- ======================== --}}
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3 smooth-fade">
    <div class="d-flex align-items-center flex-wrap gap-2">
      <i class="ri-archive-2-line fs-5" style="color:#FF9800;"></i>
      <a href="{{ route('dashboard') }}" class="breadcrumb-link fw-semibold text-decoration-none" style="color:#FF9800;">Dashboard</a>
      <span class="text-muted">/</span>
      <a href="{{ route('super_admin.items.index') }}" class="fw-semibold text-decoration-none" style="color:#FFB300;">Daftar Barang</a>
      <span class="text-muted">/</span>
      <span class="fw-semibold text-dark">{{ $item->name }}</span>
    </div>

    <a href="{{ route('super_admin.items.index') }}"
       class="btn rounded-pill btn-sm d-flex align-items-center gap-2 shadow-sm hover-glow"
       style="background-color:#FF9800;color:#fff;">
      <i class="ri-arrow-left-line"></i> Kembali
    </a>
  </div>

  {{-- ======================== --}}
  {{-- 📦 DETAIL BARANG --}}
  {{-- ======================== --}}
  <div class="card border-0 shadow-sm rounded-4 smooth-fade overflow-hidden">
    <div class="card-header bg-gradient border-0 text-white d-flex flex-wrap justify-content-between align-items-center"
         style="background:linear-gradient(90deg,#FF9800,#FFB300);">
      <div>
        <h4 class="fw-bold mb-0"><i class="ri-cube-line me-2"></i>{{ $item->name }}</h4>
        <small class="fw-medium">Dashboard barang dan status kedaluwarsa</small>
      </div>
      <span class="badge bg-white text-warning px-3 py-2 rounded-pill shadow-sm">
        <i class="ri-price-tag-3-line me-1"></i> {{ $item->category->name ?? 'Tanpa Kategori' }}
      </span>
    </div>

    <div class="card-body bg-light p-4">

      {{-- 🔍 FILTER SUPPLIER --}}
      <form method="GET" class="mb-4">
        <div class="row g-3 align-items-end">
          <div class="col-md-4">
            <label for="supplier_id" class="form-label fw-semibold text-dark">Filter Supplier</label>
            <select name="supplier_id" id="supplier_id" class="form-select border-0 shadow-sm"
                    onchange="this.form.submit()"
                    style="border-left:4px solid #FF9800 !important;">
              <option value="">Semua Supplier</option>
              @foreach ($suppliers as $supplier)
                <option value="{{ $supplier->id }}" {{ $supplierId == $supplier->id ? 'selected' : '' }}>
                  {{ $supplier->name }}
                </option>
              @endforeach
            </select>
          </div>
        </div>
      </form>

      {{-- 💡 INFORMASI --}}
      <div class="alert shadow-sm border-0 rounded-3 py-2 px-3 d-flex align-items-center"
           style="background:#FFF9E6;border-left:5px solid #FF9800;">
        <i class="ri-information-line fs-5 me-2 text-warning"></i>
        <div class="fw-semibold text-dark">
          Menampilkan data untuk supplier:
          <span style="color:#FF9800;">{{ $suppliers->firstWhere('id', $supplierId)?->name ?? 'Semua Supplier' }}</span>
        </div>
      </div>

      {{-- 🧾 RINGKASAN BARANG --}}
      <div class="row g-4 my-4">
        <div class="col-md-4">
          <div class="card border-0 shadow-sm h-100 rounded-4 text-center p-3" style="background:#FFF3E0;">
            <div class="icon mb-3">
              <i class="ri-stack-fill fs-2 text-warning"></i>
            </div>
            <h6 class="fw-semibold text-dark">Stok Barang</h6>
            <h4 class="fw-bold text-dark">{{ $item->stock }}</h4>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card border-0 shadow-sm h-100 rounded-4 text-center p-3" style="background:#E8F5E9;">
            <div class="icon mb-3">
              <i class="ri-checkbox-circle-line fs-2 text-success"></i>
            </div>
            <h6 class="fw-semibold text-dark">Belum Expired</h6>
            <h4 class="fw-bold text-success">{{ $nonExpiredCount }}</h4>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card border-0 shadow-sm h-100 rounded-4 text-center p-3" style="background:#FFEBEE;">
            <div class="icon mb-3">
              <i class="ri-error-warning-line fs-2 text-danger"></i>
            </div>
            <h6 class="fw-semibold text-dark">Sudah Expired</h6>
            <h4 class="fw-bold text-danger">{{ $expiredCount }}</h4>
          </div>
        </div>
      </div>

      {{-- 📊 TABEL DATA --}}
      <div class="table-responsive mt-4">
        <table class="table table-hover align-middle text-center mb-0">
          <thead style="background:#FFF3CD;">
            <tr class="fw-semibold text-dark">
              <th>Status</th>
              <th>Jumlah Barang</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="fw-semibold text-success"><i class="bi bi-check-circle-fill me-2"></i>Belum Expired</td>
              <td class="fw-bold">{{ $nonExpiredCount }}</td>
            </tr>
            <tr>
              <td class="fw-semibold text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>Sudah Expired</td>
              <td class="fw-bold">{{ $expiredCount }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      {{-- 🧾 DETAIL TAMBAHAN --}}
      <div class="mt-5">
        <h5 class="fw-bold mb-3 text-dark"><i class="ri-information-line text-warning me-2"></i>Informasi Barang</h5>
        <div class="row">
          <div class="col-md-6">
            <p><strong>Kategori:</strong> {{ $item->category->name ?? '-' }}</p>
            <p><strong>Satuan:</strong> {{ $item->unit->name ?? '-' }}</p>
            <p><strong>Harga:</strong> Rp {{ number_format($item->price, 0, ',', '.') }}</p>
          </div>
          <div class="col-md-6">
            <p><strong>Dibuat:</strong> {{ $item->created_at? $item->created_at->format('d M Y'):'-' }}</p>
            <p><strong>Barcode:</strong> {{ $item->code ?? '-' }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- 🎨 STYLE TAMBAHAN --}}
<style>
.smooth-fade { animation: fadeIn .6s ease-in-out; }
@keyframes fadeIn { from {opacity:0;transform:translateY(10px);} to {opacity:1;transform:translateY(0);} }

.bg-gradient { background:linear-gradient(90deg,#FF9800,#FFB300); }
.table-hover tbody tr:hover { background-color:#FFF9E6 !important; transition:all 0.3s ease; }

.hover-glow { transition:all 0.25s ease; }
.hover-glow:hover { background-color:#FFC107!important; color:#fff!important; box-shadow:0 0 12px rgba(255,152,0,0.4); }

.breadcrumb-link::after { content:''; position:absolute; bottom:-2px; left:0; width:0; height:2px; background:#FF9800; transition:width .25s ease; }
.breadcrumb-link:hover::after { width:100%; }

.card h4, .card h6 { transition: transform .2s ease; }
.card:hover h4 { transform: scale(1.05); }

@media(max-width:768px){
  .card-header { flex-direction:column; align-items:flex-start; gap:0.5rem; }
  .btn, .form-select { font-size:0.9rem; width:100%; }
}
</style>
@endsection
