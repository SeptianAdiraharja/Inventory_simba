@extends('layouts.index')

@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- ===================== --}}
  {{-- 🧭 BREADCRUMB --}}
  {{-- ===================== --}}
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3 smooth-fade">
    <div class="d-flex align-items-center flex-wrap gap-2">
      <div class="d-flex align-items-center justify-content-center rounded-circle"
           style="width:38px;height:38px;background:#FFF3E0;color:#FF9800;">
        <i class="bi bi-upc-scan fs-5"></i>
      </div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}" class="fw-semibold text-decoration-none" style="color:#FF9800;">
              Dashboard
            </a>
          </li>
          <li class="breadcrumb-item active fw-semibold text-dark" aria-current="page">
            Scan Barang Rusak / Reject
          </li>
        </ol>
      </nav>
    </div>
    <div class="d-flex align-items-center text-muted small">
      <i class="bi bi-calendar-check me-2"></i>
      <span>{{ now()->format('d M Y, H:i') }}</span>
    </div>
  </div>

  {{-- ===================== --}}
  {{-- 🔶 FORM SCAN --}}
  {{-- ===================== --}}
  <div class="card shadow-lg border-0 rounded-4 mb-4 overflow-hidden smooth-card">
    <div class="card-header text-white py-3 px-4"
         style="background: linear-gradient(90deg, #FF9800, #FFB74D);">
      <h6 class="mb-0 fw-semibold d-flex align-items-center">
        <i class="bi bi-upc-scan me-2 text-white"></i> Form Scan Barang Rusak
      </h6>
    </div>

    <div class="card-body bg-light">
      <form id="scanForm" autocomplete="off">
        @csrf
        <div class="row g-4 align-items-end">
          <div class="col-md-4">
            <label class="form-label fw-semibold text-secondary">Scan Barcode Barang</label>
            <input type="text" id="barcode" name="barcode"
                   class="form-control form-control-lg border-2 shadow-sm rounded-3"
                   style="border-color:#FF9800;"
                   placeholder="🔍 Arahkan scanner ke sini..." autofocus>
          </div>

          <div class="col-md-2">
            <label class="form-label fw-semibold text-secondary">Jumlah Rusak</label>
            <input type="number" id="quantity" name="quantity"
                   class="form-control shadow-sm border-0 rounded-3"
                   min="1" value="1">
          </div>

          <div class="col-md-3">
            <label class="form-label fw-semibold text-secondary">Kondisi</label>
            <select id="condition" name="condition" class="form-select shadow-sm border-0 rounded-3">
              <option value="rusak ringan">Rusak Ringan</option>
              <option value="rusak berat">Rusak Berat</option>
              <option value="tidak bisa digunakan">Tidak Bisa Digunakan</option>
            </select>
          </div>

          <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn text-white btn-lg w-100 shadow-sm rounded-pill smooth-btn"
                    style="background-color:#FF9800;">
              <i class="bi bi-plus-circle me-2"></i> Tambah ke Daftar
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  {{-- ===================== --}}
  {{-- 📋 TABEL BARANG RUSAK --}}
  {{-- ===================== --}}
  <div class="card shadow-lg border-0 rounded-4 overflow-hidden smooth-card">
    <div class="card-header text-white fw-semibold py-3 px-4 d-flex justify-content-between align-items-center"
         style="background: linear-gradient(90deg, #FF9800, #FFB74D);">
      <div><i class="bi bi-list-ul me-2"></i> Daftar Barang Rusak (Belum Disimpan)</div>
      <button id="saveAllBtn" class="btn btn-light btn-sm text-orange fw-semibold px-3 rounded-pill shadow-sm"
              style="color:#FF9800;" disabled>
        <i class="bi bi-save2 me-1"></i> Simpan Semua
      </button>
    </div>

    <div class="card-body p-0 bg-white">
      <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle" id="rejectTable">
          <thead class="text-center fw-semibold text-uppercase small"
                 style="background-color:#FFF3E0; color:#5d4037;">
            <tr>
              <th width="60">No</th>
              <th>Nama Barang</th>
              <th>Kode</th>
              <th width="80">Jumlah</th>
              <th>Kondisi</th>
              <th>Deskripsi (Wajib)</th>
              <th width="100">Aksi</th>
            </tr>
          </thead>
          <tbody id="rejectTableBody" class="text-center">
            <tr>
              <td colspan="7" class="text-muted py-4">
                <i class="bi bi-inbox fs-4 d-block mb-2"></i> Belum ada data.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
  body { background-color: #fffaf4 !important; }

  .smooth-fade { animation: fadeDown .7s ease-in-out; }
  @keyframes fadeDown { from { opacity:0; transform:translateY(-10px);} to {opacity:1; transform:translateY(0);} }

  tr.fade-in { animation: fadeIn .5s ease-in; }
  @keyframes fadeIn { from {opacity:0;transform:translateY(-5px);} to {opacity:1;transform:translateY(0);} }

  .smooth-card { transition: all 0.3s ease; }
  .smooth-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(255, 152, 0, 0.25);
  }

  .smooth-btn { transition: all 0.25s ease-in-out; }
  .smooth-btn:hover {
    transform: scale(1.05);
    background-color: #fb8c00 !important;
    box-shadow: 0 4px 10px rgba(255, 152, 0, 0.3);
  }

  .table-hover tbody tr:hover {
    background-color: #FFF8E1 !important;
    transition: 0.3s ease;
  }

  .form-control:focus, .form-select:focus {
    border-color: #FF9800 !important;
    box-shadow: 0 0 0 0.2rem rgba(255, 152, 0, 0.25) !important;
  }

  .btn[disabled] {
    opacity: 0.5 !important;
    cursor: not-allowed !important;
  }

  .badge, .btn, input, select, table {
    border-radius: 0.5rem !important;
  }

  @media (max-width: 768px) {
    .breadcrumb-extra { display: none; }
    .card-header h6 { font-size: 1rem; }
    .table { font-size: 0.9rem; }
  }
</style>
@endpush

@push('scripts')
{{-- 🔧 Script dari versi sebelumnya tetap sama --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  // (Semua fungsi JS tetap sama seperti kode asli Abang)
</script>
@endpush
