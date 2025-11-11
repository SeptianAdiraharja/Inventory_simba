@extends('layouts.index')
@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- ======================== --}}
  {{-- 🧭 BREADCRUMB ORANGE --}}
  {{-- ======================== --}}
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap align-items-center justify-content-between smooth-fade">
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <i class="bi bi-box-seam fs-5" style="color:#FF9800;"></i>
      <a href="{{ route('dashboard') }}" class="breadcrumb-link fw-semibold text-decoration-none" style="color:#FF9800;">
        Dashboard
      </a>
      <span class="text-muted">/</span>
      <span class="text-dark fw-semibold">Daftar Barang</span>
    </div>

    <a href="{{ route('super_admin.items.create') }}"
       class="btn btn-sm rounded-pill d-flex align-items-center gap-2 shadow-sm hover-glow"
       style="background-color:#FF9800;color:#fff;">
      <i class="ri ri-add-line fs-5"></i> Tambah Barang
    </a>
  </div>

  {{-- ======================== --}}
  {{-- 📦 DAFTAR BARANG --}}
  {{-- ======================== --}}
  <div class="card shadow-sm border-0 rounded-4 smooth-fade">
    <div class="card-header bg-white border-0 pb-0">
      <h4 class="fw-bold mb-3" style="color:#FF9800;"><i class="ri-archive-2-line me-2"></i> Daftar Barang</h4>

      {{-- 🔍 Filter, Range, & Search --}}
      <form id="filterForm" method="GET" action="{{ route('super_admin.items.index') }}" class="row g-2 align-items-center">
        <div class="col-md-3 col-sm-6">
          <input type="date" name="date_from" id="dateFrom" class="form-control form-control-sm border-0 shadow-sm"
                 style="border-left:4px solid #FF9800 !important;" value="{{ request('date_from') }}">
        </div>
        <div class="col-md-3 col-sm-6">
          <input type="date" name="date_to" id="dateTo" class="form-control form-control-sm border-0 shadow-sm"
                 style="border-left:4px solid #FF9800 !important;" value="{{ request('date_to') }}">
        </div>
        <div class="col-md-3 col-sm-6">
          <select name="sort_stock" id="sortStock" class="form-select form-select-sm border-0 shadow-sm"
                  style="border-left:4px solid #FF9800 !important;">
            <option value="">Urutkan Stok</option>
            <option value="desc" {{ request('sort_stock') == 'desc' ? 'selected' : '' }}>Paling Banyak</option>
            <option value="asc" {{ request('sort_stock') == 'asc' ? 'selected' : '' }}>Paling Sedikit</option>
          </select>
        </div>
        <div class="col-md-2 col-sm-6">
          <input type="text" name="search" id="autoSearchInput" class="form-control form-control-sm border-0 shadow-sm"
                 style="border-left:4px solid #FF9800 !important;"
                 placeholder="Cari nama / kategori..." value="{{ request('search') }}">
        </div>
        <div class="col-md-1 text-md-end">
          @if(request('date_from') || request('date_to') || request('sort_stock') || request('search'))
          <a href="{{ route('super_admin.items.index') }}" class="btn btn-sm btn-outline-warning w-100">
            <i class="ri-refresh-line me-1"></i> Reset
          </a>
          @endif
        </div>
      </form>
    </div>

    {{-- 📋 TABEL DATA --}}
    <div class="card-body pt-3">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light text-center">
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
              <td>{{ $item->created_at ? $item->created_at->format('d M Y') : '-' }}</td>
              <td>
                <div class="dropdown">
                  <button type="button" class="btn p-0 dropdown-toggle hide-arrow shadow-none" data-bs-toggle="dropdown">
                    <i class="ri-more-2-fill text-muted"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                    <li>
                      <a class="dropdown-item d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#detailModal{{ $item->id }}">
                        <i class="ri-file-list-3-line me-2 text-warning"></i> Detail
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item d-flex align-items-center" href="{{ route('super_admin.items.show', $item->id) }}">
                        <i class="ri-eye-line me-2 text-primary"></i> Lihat
                      </a>
                    </li>
                  </ul>
                </div>
              </td>
            </tr>

            {{-- 🪄 MODAL DETAIL --}}
            <div class="modal fade" id="detailModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                  <div class="modal-header text-white py-2" style="background-color:#FF9800;">
                    <h5 class="modal-title fw-semibold">
                      <i class="ri-archive-line me-2"></i> Detail Barang — {{ $item->name }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                  </div>

                  <div class="modal-body bg-light-subtle">
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

                  <div class="modal-footer bg-white border-0 pt-3 d-flex justify-content-between flex-wrap">
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
              <td colspan="7" class="text-center py-4 text-muted"><i class="ri-information-line me-1"></i> Belum ada data barang.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

{{-- 🌈 STYLE TAMBAHAN --}}
<style>
.smooth-fade{animation:fadeIn .6s ease-in-out;}
@keyframes fadeIn{from{opacity:0;transform:translateY(10px);}to{opacity:1;transform:translateY(0);}}
.table-row-hover{transition:background-color .2s ease,transform .15s ease;}
.table-row-hover:hover{background-color:#FFF9E6!important;transform:translateX(3px);}
.hover-glow{transition:all .25s ease;}
.hover-glow:hover{background-color:#FFC107!important;color:#fff!important;box-shadow:0 0 12px rgba(255,152,0,0.4);}
.breadcrumb-link{position:relative;transition:all .25s ease;}
.breadcrumb-link::after{content:'';position:absolute;bottom:-2px;left:0;width:0;height:2px;background:#FF9800;transition:width .25s ease;}
.breadcrumb-link:hover::after{width:100%;}
.modal-header{background:linear-gradient(90deg,#FF9800,#FFB300);}
</style>

{{-- ⚡ SCRIPT AUTO FILTER --}}
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
