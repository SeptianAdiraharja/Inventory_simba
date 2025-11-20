@extends('layouts.index')
@section('title', 'Daftar Barang')
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
      <button type="button" class="btn btn-sm rounded-pill d-flex align-items-center gap-2 shadow-sm hover-glow"
              style="background-color:#FFB300;color:#fff;" data-bs-toggle="modal" data-bs-target="#importModal">
        <i class="bi bi-upload fs-6"></i> Import Data
      </button>

      <a href="{{ route('super_admin.items.create') }}"
         class="btn btn-sm rounded-pill d-flex align-items-center gap-2 shadow-sm hover-glow"
         style="background-color:#FF9800;color:#fff;">
        <i class="ri ri-add-line fs-5"></i> Tambah Barang
      </a>
    </div>
  </div>

  {{-- ALERT --}}
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
      <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
      <i class="bi bi-exclamation-triangle me-1"></i>{{ $errors->first() }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  {{-- ======================== --}}
  {{-- 📦 FILTER --}}
  {{-- ======================== --}}
  <div class="card shadow-sm border-0 rounded-4 smooth-fade mb-5">
    <div class="card-header bg-white border-0 pb-0">
      <h4 class="fw-bold mb-3" style="color:#FF9800;"><i class="ri-archive-2-line me-2"></i> Daftar Barang</h4>

      <form id="filterForm" method="GET" action="{{ route('super_admin.items.index') }}" class="row g-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label small text-muted mb-1">Dari Tanggal</label>
          <input type="date" name="date_from" id="dateFrom"
            class="form-control form-control-sm border-0 shadow-sm"
            value="{{ request('date_from') }}" style="border-left:4px solid #FF9800;">
        </div>

        <div class="col-md-3">
          <label class="form-label small text-muted mb-1">Sampai Tanggal</label>
          <input type="date" name="date_to" id="dateTo"
            class="form-control form-control-sm border-0 shadow-sm"
            value="{{ request('date_to') }}" style="border-left:4px solid #FF9800;">
        </div>

        <div class="col-md-3">
          <label class="form-label small text-muted mb-1">Urutkan Stok</label>
          <select name="sort_stock" id="sortStock" class="form-select form-select-sm border-0 shadow-sm"
                  style="border-left:4px solid #FF9800;">
            <option value="">Semua</option>
            <option value="desc" {{ request('sort_stock')=='desc'?'selected':'' }}>Paling Banyak</option>
            <option value="asc" {{ request('sort_stock')=='asc'?'selected':'' }}>Paling Sedikit</option>
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label small text-muted mb-1">Cari Barang</label>
          <input type="text" name="search" id="autoSearchInput"
            class="form-control form-control-sm border-0 shadow-sm"
            placeholder="Cari..." value="{{ request('search') }}"
            style="border-left:4px solid #FF9800;">
        </div>

        <div class="col-md-1 text-md-end">
          @if(request()->anyFilled(['date_from','date_to','sort_stock','search']))
          <a href="{{ route('super_admin.items.index') }}"
             class="btn btn-sm btn-outline-warning w-100 rounded-pill">
            <i class="ri-refresh-line"></i>
          </a>
          @endif
        </div>
      </form>
    </div>

    {{-- ======================== --}}
    {{-- 📋 TABEL --}}
    {{-- ======================== --}}
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
              <th>Dibuat</th>
              <th>Aksi</th>
            </tr>
          </thead>

          <tbody>
            @forelse($items as $item)
            <tr class="text-center table-row-hover">
              <td class="text-start fw-semibold">{{ $item->name }}</td>
              <td>{{ $item->category->name ?? '-' }}</td>
              <td>{{ $item->unit->name ?? '-' }}</td>
              <td>Rp {{ number_format($item->price,0,',','.') }}</td>
              <td>{{ $item->stock }}</td>
              <td>{{ $item->created_at? $item->created_at->format('d M Y'):'-' }}</td>

              <td>
                <div class="dropdown position-static">
                  <button class="btn btn-sm p-0 shadow-none" data-bs-toggle="dropdown">
                    <i class="ri-more-2-fill fs-5 text-muted"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 p-1">

                    <li>
                      <a class="dropdown-item d-flex align-items-center"
                        data-bs-toggle="modal" data-bs-target="#detailModal{{ $item->id }}">
                        <i class="ri-eye-line me-2 text-warning"></i> Detail
                      </a>
                    </li>

                    <li>
                      <a class="dropdown-item d-flex align-items-center"
                        href="{{ route('super_admin.items.show',$item->id) }}">
                        <i class="ri-eye-line me-2 text-primary"></i> Lihat
                      </a>
                    </li>

                  </ul>
                </div>
              </td>
            </tr>

            {{-- ======================== --}}
            {{-- 🪄 MODAL DETAIL --}}
            {{-- ======================== --}}
            <div class="modal fade" id="detailModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

                  <div class="modal-header text-white py-2"
                    style="background:linear-gradient(90deg,#FF9800,#FFB300);">
                    <h5 class="modal-title fw-semibold">
                      <i class="ri-archive-line me-2"></i> Detail Barang — {{ $item->name }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white"
                      data-bs-dismiss="modal"></button>
                  </div>

                  <div class="modal-body bg-light">

                    <div class="row">
                      <div class="col-md-6">
                        <p><strong>Kategori:</strong> {{ $item->category->name ?? '-' }}</p>
                        <p><strong>Satuan:</strong> {{ $item->unit->name ?? '-' }}</p>
                        <p><strong>Supplier:</strong> {{ $item->supplier->name ?? '-' }}</p>
                      </div>

                      <div class="col-md-6">
                        <p><strong>Harga:</strong> Rp {{ number_format($item->price) }}</p>
                        <p><strong>Stok:</strong> {{ $item->stock }}</p>
                        <p><strong>Dibuat:</strong> {{ $item->created_at->format('d M Y') }}</p>
                      </div>
                    </div>

                    <hr>

                    <h6 class="fw-bold mb-2"><i class="ri-barcode-line me-1"></i> Barcode</h6>

                    <div class="text-center">
                      @if($item->barcode_png_base64)
                        <img src="{{ $item->barcode_png_base64 }}"
                             class="img-fluid p-2 bg-white shadow-sm border rounded"
                             style="max-width:230px;">
                        <p class="text-muted mt-2 small">{{ $item->code }}</p>
                      @else
                        <p class="text-muted">Barcode tidak tersedia.</p>
                      @endif
                    </div>

                    <hr>

                    <h6 class="fw-bold mb-1">Deskripsi</h6>
                    <p class="text-muted">{{ $item->description ?? 'Tidak ada deskripsi.' }}</p>

                  </div>
                  {{-- ======================== --}}
                  {{-- FOOTER MODAL (CETAK, EDIT, DELETE) --}}
                  {{-- ======================== --}}
                  <div class="modal-footer bg-white border-0 d-flex flex-wrap justify-content-between">

                    {{-- FORM CETAK BARCODE --}}
                    <form action="{{ route('super_admin.items.barcode.pdf', $item->id) }}"
                          method="GET" target="_blank"
                          class="d-flex align-items-center gap-2 flex-wrap">

                      <div class="input-group input-group-sm" style="width:130px;">
                        <span class="input-group-text bg-white border-end-0 px-2">
                          <i class="ri-hashtag text-warning fs-6"></i>
                        </span>
                        <input type="number" name="jumlah" min="1" value="1"
                               class="form-control form-control-sm border-start-0 text-center fw-semibold"
                               placeholder="Qty">
                      </div>

                      <button type="submit"
                        class="btn btn-sm btn-outline-warning rounded-3 d-flex align-items-center px-3">
                        <i class="ri-printer-line me-1"></i> Cetak
                      </button>
                    </form>

                    {{-- EDIT / HAPUS --}}
                    <div class="d-flex align-items-center gap-2">

                      {{-- EDIT --}}
                      <a href="{{ route('super_admin.items.edit', $item->id) }}"
                         class="btn btn-sm btn-outline-warning rounded-3 d-flex align-items-center px-3">
                        <i class="ri-pencil-line me-1"></i> Edit
                      </a>

                      {{-- DELETE --}}
                      <form action="{{ route('super_admin.items.destroy', $item->id) }}" method="POST"
                            onsubmit="return confirm('Yakin ingin hapus barang ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                          class="btn btn-sm btn-outline-danger rounded-3 d-flex align-items-center px-3">
                          <i class="ri-delete-bin-6-line me-1"></i> Hapus
                        </button>
                      </form>
                    </div>

                  </div> {{-- END MODAL FOOTER --}}

                </div>
              </div>
            </div>
            {{-- END MODAL DETAIL --}}

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

      {{-- ======================== --}}
      {{-- PAGINATION --}}
      {{-- ======================== --}}
      <div class="p-3 d-flex justify-content-center">
        {{ $items->onEachSide(1)->links('pagination::bootstrap-5') }}
      </div>

    </div>
  </div>
</div>

{{-- ======================== --}}
{{-- MODAL IMPORT --}}
{{-- ======================== --}}
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 shadow-lg border-0">

      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="importModalLabel" style="color:#FF9800;">
          <i class="bi bi-upload"></i> Import Data Barang
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form action="{{ route('super_admin.items.import') }}"
            method="POST"
            enctype="multipart/form-data"
            id="itemImportForm">
        @csrf

        <div class="modal-body px-4">
          <p class="text-muted mb-2">Unggah file Excel (.xls atau .xlsx) sesuai format template.</p>

          <div class="mb-3">
            <label class="form-label fw-semibold">Pilih File Excel</label>
            <input type="file"
                   name="file"
                   class="form-control rounded-pill"
                   accept=".xls,.xlsx"
                   required>
          </div>

          <a href="{{ route('super_admin.items.template') }}"
             class="text-decoration-none small" style="color:#FF9800;">
             <i class="bi bi-file-earmark-excel"></i> Download Template Import
          </a>
        </div>

        <div class="modal-footer border-0 d-flex justify-content-end gap-2">
          <button type="button" class="btn btn-outline-secondary rounded-pill px-4"
                  data-bs-dismiss="modal">
            Batal
          </button>

          <button type="submit" class="btn rounded-pill px-4 shadow-sm"
                  style="background:#FF9800;color:#fff;">
            <i class="bi bi-check-circle me-1"></i> Import Sekarang
          </button>
        </div>
      </form>

    </div>
  </div>
</div>

<style>
html, body { background:#f8f9fb !important; }

/* Hilangkan scroll horizontal */
.table-responsive {
  overflow-x: hidden !important;
}

.table {
  width: 100% !important;
  table-layout: auto !important;
}

/* Optimasi teks panjang */
.text-truncate {
  white-space: nowrap !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
  max-width: 200px;
}

.card-body {
  overflow-x: hidden !important;
}

/* Pagination Orange */
.pagination .page-link {
  color:#FF9800; border:1px solid #FFCC80;
}
.pagination .page-link:hover {
  background:#FFE0B2; color:#E68900;
}
.pagination .active .page-link {
  background:#FF9800; border-color:#FF9800; color:white;
}
.pagination .page-item.disabled .page-link {
  color:#FFCC80;
}

.smooth-fade{animation:fadeIn .6s ease;}
.table-row-hover:hover{background:#FFF9E6!important;transform:translateX(3px);}
.hover-glow:hover{background:#FFC107!important;box-shadow:0 0 12px rgba(255,152,0,.4);}
.dropdown-item:hover{background:#FFF3E0;color:#FF9800;}
</style>

<script>
document.getElementById('itemImportForm').addEventListener('submit', function (e) {
    const fileInput = document.querySelector('input[name="file"]');
    const allowedExtensions = ['xls', 'xlsx'];

    if (fileInput.files.length === 0) {
        alert("Harap pilih file Excel terlebih dahulu.");
        e.preventDefault();
        return;
    }

    const fileName = fileInput.files[0].name;
    const fileExtension = fileName.split('.').pop().toLowerCase();

    if (!allowedExtensions.includes(fileExtension)) {
        alert("Format file tidak valid. Harus .xls atau .xlsx");
        e.preventDefault();
    }
});

// Auto filter script
document.addEventListener('DOMContentLoaded',()=>{
  const f=document.getElementById('filterForm'),
        s=document.getElementById('autoSearchInput'),
        d1=document.getElementById('dateFrom'),
        d2=document.getElementById('dateTo'),
        st=document.getElementById('sortStock');
  let t=null;
  function go(){
    const a=d1.value,b=d2.value;
    if((a&&b)||(!a&&!b)){ clearTimeout(t); t=setTimeout(()=>f.submit(),500); }
  }
  s.addEventListener('input',go);
  d1.addEventListener('change',go);
  d2.addEventListener('change',go);
  st.addEventListener('change',go);
});
</script>
@endsection
