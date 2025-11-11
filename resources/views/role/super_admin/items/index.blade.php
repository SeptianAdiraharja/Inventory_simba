@extends('layouts.index')

@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- ======================== --}}
  {{-- 🧭 BREADCRUMB --}}
  {{-- ======================== --}}
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3 smooth-fade">
    <div class="d-flex align-items-center flex-wrap gap-2">
      <i class="bi bi-box-seam fs-5" style="color:#FF9800;"></i>
      <a href="{{ route('dashboard') }}" class="breadcrumb-link fw-semibold text-decoration-none" style="color:#FF9800;">Dashboard</a>
      <span class="text-muted">/</span>
      <span class="fw-semibold text-dark">Daftar Barang</span>
    </div>

    <div class="d-flex align-items-center gap-2">
      <!-- Tombol Import Excel -->
      <button type="button" class="btn btn-sm rounded-pill d-flex align-items-center gap-2 shadow-sm hover-glow"
              style="background-color:#FFB300;color:#fff;" data-bs-toggle="modal" data-bs-target="#importModal">
        <i class="bi bi-upload fs-6"></i> Import Data
      </button>

      <!-- Tombol Tambah Barang -->
      <a href="{{ route('super_admin.items.create') }}"
         class="btn btn-sm rounded-pill d-flex align-items-center gap-2 shadow-sm hover-glow"
         style="background-color:#FF9800;color:#fff;">
        <i class="ri ri-add-line fs-5"></i> Tambah Barang
      </a>
    </div>
  </div>

  {{-- ✅ ALERTS --}}
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="bi bi-exclamation-triangle me-1"></i>{{ $errors->first() }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  {{-- ======================== --}}
  {{-- 📦 FILTER DAN TABEL --}}
  {{-- ======================== --}}
  <div class="card shadow-sm border-0 rounded-4 smooth-fade mb-5">
    <div class="card-header bg-white border-0 pb-0">
      <h4 class="fw-bold mb-3" style="color:#FF9800;"><i class="ri-archive-2-line me-2"></i> Daftar Barang</h4>

      {{-- 🔍 Filter & Search --}}
      <form id="filterForm" method="GET" action="{{ route('super_admin.items.index') }}" class="row g-3 align-items-end">
        <div class="col-md-3 col-sm-6">
          <label class="form-label text-muted small mb-1">Dari Tanggal</label>
          <input type="date" name="date_from" id="dateFrom" class="form-control form-control-sm border-0 shadow-sm"
                 value="{{ request('date_from') }}" style="border-left:4px solid #FF9800 !important;">
        </div>
        <div class="col-md-3 col-sm-6">
          <label class="form-label text-muted small mb-1">Sampai Tanggal</label>
          <input type="date" name="date_to" id="dateTo" class="form-control form-control-sm border-0 shadow-sm"
                 value="{{ request('date_to') }}" style="border-left:4px solid #FF9800 !important;">
        </div>
        <div class="col-md-3 col-sm-6">
          <label class="form-label text-muted small mb-1">Urutkan Stok</label>
          <select name="sort_stock" id="sortStock" class="form-select form-select-sm border-0 shadow-sm"
                  style="border-left:4px solid #FF9800 !important;">
            <option value="">Semua</option>
            <option value="desc" {{ request('sort_stock') == 'desc' ? 'selected' : '' }}>Paling Banyak</option>
            <option value="asc" {{ request('sort_stock') == 'asc' ? 'selected' : '' }}>Paling Sedikit</option>
          </select>
        </div>
        <div class="col-md-2 col-sm-6">
          <label class="form-label text-muted small mb-1">Cari Barang / Kategori</label>
          <input type="text" name="search" id="autoSearchInput" class="form-control form-control-sm border-0 shadow-sm"
                 placeholder="Cari..." value="{{ request('search') }}"
                 style="border-left:4px solid #FF9800 !important;">
        </div>
        <div class="col-md-1 text-md-end text-center">
          @if(request('date_from') || request('date_to') || request('sort_stock') || request('search'))
          <a href="{{ route('super_admin.items.index') }}" class="btn btn-sm btn-outline-warning w-100 rounded-pill">
            <i class="ri-refresh-line"></i>
          </a>
          @endif
        </div>
      </form>
    </div>

    {{-- 📋 TABEL DATA --}}
    <div class="card-body pt-3">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="text-center" style="background:#FFF3E0;">
            <tr>
              <th>Nama</th>
              <th>Kategori</th>
              <th>Satuan</th>
              <th>Harga</th>
              <th>Stok</th>
              <th>Tanggal Dibuat</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($items as $item)
            <tr class="text-center table-row-hover">
              <td class="text-start fw-semibold">{{ $item->name }}</td>
              <td>{{ $item->category->name ?? '-' }}</td>
              <td>{{ $item->unit->name ?? '-' }}</td>
              <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
              <td>{{ $item->stock }}</td>
              <td>{{ $item->created_at? $item->created_at->format('d M Y'):'-' }}</td>
              <td>
                <div class="dropdown position-static">
                  <button class="btn btn-sm p-0 shadow-none" data-bs-toggle="dropdown">
                    <i class="ri-more-2-fill text-muted fs-5"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 p-1">
                    <li>
                      <a class="dropdown-item d-flex align-items-center rounded-3"
                         data-bs-toggle="modal" data-bs-target="#detailModal{{ $item->id }}">
                        <i class="ri-file-list-3-line me-2 text-warning"></i> Detail
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item d-flex align-items-center rounded-3"
                         href="{{ route('super_admin.items.show', $item->id) }}">
                        <i class="ri-eye-line me-2 text-primary"></i> Lihat
                      </a>
                    </li>
                  </ul>
                </div>
              </td>
            </tr>

            {{-- 🪄 MODAL DETAIL --}}
            <div class="modal fade" id="detailModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                  <div class="modal-header text-white py-2" style="background:linear-gradient(90deg,#FF9800,#FFB300);">
                    <h5 class="modal-title fw-semibold">
                      <i class="ri-archive-line me-2"></i> Detail Barang — {{ $item->name }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body bg-light">
                    <div class="row mb-3">
                      <div class="col-md-6">
                        <p><strong>Kategori:</strong> {{ $item->category->name ?? '-' }}</p>
                        <p><strong>Satuan:</strong> {{ $item->unit->name ?? '-' }}</p>
                        <p><strong>Supplier:</strong> {{ $item->supplier->name ?? '-' }}</p>
                      </div>
                      <div class="col-md-6">
                        <p><strong>Harga:</strong> Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                        <p><strong>Stok:</strong> {{ $item->stock }}</p>
                        <p><strong>Dibuat:</strong> {{ $item->created_at? $item->created_at->format('d M Y'):'-' }}</p>
                      </div>
                    </div>

                    <hr>
                    <h6 class="fw-bold text-secondary mb-3"><i class="ri-barcode-line me-2"></i> Barcode</h6>
                    <div class="text-center">
                      @if($item->barcode_png_base64)
                        <img src="{{ $item->barcode_png_base64 }}" alt="barcode"
                             class="img-fluid border rounded p-2 bg-white shadow-sm" style="max-width:200px;">
                        <p class="mt-2 small text-muted">{{ $item->code }}</p>
                      @else
                        <p class="text-muted">Barcode tidak tersedia</p>
                      @endif
                    </div>
                  </div>

                  <div class="modal-footer bg-white border-0 pt-3 d-flex flex-wrap justify-content-between gap-2">
                    <form action="{{ route('super_admin.items.barcode.pdf', $item->id) }}" method="GET" target="_blank"
                          class="d-flex align-items-center gap-2 flex-wrap mb-2 mb-md-0">
                      <div class="input-group input-group-sm" style="width:130px;">
                        <span class="input-group-text bg-white border-end-0 px-2">
                          <i class="ri-hashtag text-warning fs-6"></i>
                        </span>
                        <input type="number" name="jumlah" min="1" value="1"
                               class="form-control form-control-sm border-start-0 text-center fw-semibold" placeholder="Qty">
                      </div>
                      <button type="submit" class="btn btn-sm btn-outline-warning rounded-3 d-flex align-items-center px-3">
                        <i class="ri-printer-line me-1"></i> Cetak
                      </button>
                    </form>

                    <div class="d-flex align-items-center gap-2">
                      <a href="{{ route('super_admin.items.edit', $item->id) }}"
                         class="btn btn-sm btn-outline-warning d-flex align-items-center rounded-3">
                        <i class="ri-pencil-line me-1"></i> Edit
                      </a>
                      <form action="{{ route('super_admin.items.destroy', $item->id) }}" method="POST"
                            onsubmit="return confirm('Yakin hapus item ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger d-flex align-items-center rounded-3">
                          <i class="ri-delete-bin-6-line me-1"></i> Hapus
                        </button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            @empty
            <tr>
              <td colspan="7" class="text-center py-4 text-muted">
                <i class="ri-information-line me-1"></i> Belum ada data barang.
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

{{-- ✅ MODAL IMPORT --}}
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header text-white" style="background:linear-gradient(90deg,#FF9800,#FF9300);">
        <h5 class="modal-title" id="importModalLabel"><i class="bi bi-file-earmark-excel me-2"></i>Import Data Barang</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="{{ route('super_admin.items.import') }}" method="POST" enctype="multipart/form-data">
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
            <br><code>name, code, category_id, stock, price, expired_at, supplier_id, unit_id, created_by, image</code>
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

{{-- 🌈 STYLE TAMBAHAN --}}
<style>
  html, body {background-color: #f8f9fb !important;}
  .smooth-fade{animation:fadeIn .6s ease-in-out;}
  @keyframes fadeIn{from{opacity:0;transform:translateY(10px);}to{opacity:1;transform:translateY(0);}}
  .table-row-hover:hover{background-color:#FFF9E6!important;transform:translateX(3px);}
  .hover-glow:hover{background-color:#FFC107!important;box-shadow:0 0 12px rgba(255,152,0,0.4);}
  .dropdown-item:hover{background-color:#FFF3E0;color:#FF9800;}
</style>

{{-- ⚡ AUTO FILTER --}}
<script>
document.addEventListener('DOMContentLoaded',function(){
  const form=document.getElementById('filterForm');
  const searchInput=document.getElementById('autoSearchInput');
  const dateFrom=document.getElementById('dateFrom');
  const dateTo=document.getElementById('dateTo');
  const sortStock=document.getElementById('sortStock');
  let timer=null;
  function autoSubmit(){
    const from=dateFrom.value,to=dateTo.value;
    if((from&&to)||(!from&&!to)){
      clearTimeout(timer);
      timer=setTimeout(()=>form.submit(),500);
    }
  }
  searchInput.addEventListener('input',autoSubmit);
  dateFrom.addEventListener('change',autoSubmit);
  dateTo.addEventListener('change',autoSubmit);
  sortStock.addEventListener('change',autoSubmit);
});
</script>
@endsection
