@extends('layouts.index')
@section('title', 'Tambah Barang Masuk')
@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- ======================== --}}
  {{-- 🧭 BREADCRUMB ORANGE --}}
  {{-- ======================== --}}
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap align-items-center justify-content-between smooth-fade">
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <i class="bi bi-box-arrow-in-down fs-5" style="color:#FF9800;"></i>
      <a href="{{ route('dashboard') }}" class="breadcrumb-link fw-semibold text-decoration-none" style="color:#FF9800;">
        Dashboard
      </a>
      <span class="text-muted">/</span>
      <a href="{{ route('super_admin.item_ins.index') }}" class="fw-semibold text-decoration-none" style="color:#FFB300;">
        Barang Masuk
      </a>
      <span class="text-muted">/</span>
      <span class="fw-semibold text-dark">Tambah</span>
    </div>
  </div>

  {{-- ======================== --}}
  {{-- 📦 FORM TAMBAH BARANG MASUK --}}
  {{-- ======================== --}}
  <div class="card border-0 shadow-sm rounded-4 smooth-fade">
    <div class="card-header bg-white border-0 d-flex justify-content-between flex-wrap align-items-center">
      <h4 class="fw-bold mb-0" style="color:#FF9800;">
        <i class="ri-add-line me-2"></i> Tambah Barang Masuk
      </h4>
      <small class="text-warning fw-semibold">Isi data barang masuk dengan benar</small>
    </div>

    <div class="card-body bg-white p-4 rounded-bottom-4">
      <form action="{{ route('super_admin.item_ins.store') }}" method="POST" x-data="{ useTanggalMasuk: true, useExpired: true }">
        @csrf

        {{-- Item --}}
        <div class="mb-4">
          <label class="form-label fw-semibold text-dark">Barang</label>
          <select name="item_id" class="form-select shadow-sm border-0 select2"
                  style="border-left:4px solid #FF9800 !important;" required>
            <option value="">-- Pilih Barang --</option>
            @foreach($items as $item)
              <option value="{{ $item->id }}">{{ $item->name }}</option>
            @endforeach
          </select>
          @error('item_id') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        {{-- Supplier --}}
        <div class="mb-4">
          <label class="form-label fw-semibold text-dark">Supplier</label>
          <select name="supplier_id" class="form-select shadow-sm border-0 select2"
                  style="border-left:4px solid #FF9800 !important;" required>
            <option value="">-- Pilih Supplier --</option>
            @foreach($suppliers as $supplier)
              <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
            @endforeach
          </select>
          @error('supplier_id') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        {{-- Jumlah --}}
        <div class="mb-4">
          <label class="form-label fw-semibold text-dark">Jumlah</label>
          <input type="number" name="quantity" class="form-control shadow-sm border-0"
                 placeholder="Isi jumlah barang" required
                 style="border-left:4px solid #FF9800 !important;">
          @error('quantity') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        {{-- tanggal masuk --}}
         <div class="mb-4">
          <label class="form-label fw-semibold text-dark">Tanggal Masuk</label>
          <div x-show="useTanggalMasuk" x-transition>
            <input type="date" name="tanggal_masuk" id="tanggal_masuk"
                   value="{{ old('tanggal_masuk') }}"
                   class="form-control shadow-sm border-0"
                   style="border-left:4px solid #FF9800 !important;"
                   x-bind:required="useTanggalMasuk">
          </div>
          <div class="form-check form-switch mt-2">
            <input class="form-check-input" type="checkbox" id="toggleTanggalMasuk" x-model="useTanggalMasuk">
            <label class="form-check-label text-muted" for="toggleTanggalMasuk">
              Gunakan tanggal Masuk
            </label>
          </div>
          @error('tanggal_masuk') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        {{-- Tanggal Expired --}}
        <div class="mb-4">
          <label class="form-label fw-semibold text-dark">Tanggal Kedaluwarsa</label>
          <div x-show="useExpired" x-transition>
            <input type="date" name="expired_at" id="expired_at"
                   min="{{ \Carbon\Carbon::today()->toDateString() }}"
                   value="{{ old('expired_at') }}"
                   class="form-control shadow-sm border-0"
                   style="border-left:4px solid #FF9800 !important;"
                   x-bind:required="useExpired">
          </div>
          <div class="form-check form-switch mt-2">
            <input class="form-check-input" type="checkbox" id="toggleExpired" x-model="useExpired">
            <label class="form-check-label text-muted" for="toggleExpired">
              Gunakan tanggal kedaluwarsa
            </label>
          </div>
          @error('expired_at') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        {{-- Tombol --}}
        <div class="d-flex justify-content-end gap-2 mt-4">
          <button type="submit"
                  class="btn btn-sm rounded-pill px-4 shadow-sm hover-glow"
                  style="background-color:#FF9800;color:white;">
            <i class="ri-save-3-line me-1"></i> Simpan
          </button>
          <a href="{{ route('super_admin.item_ins.index') }}"
             class="btn btn-sm rounded-pill px-4"
             style="background-color:#FFF3E0;color:#FF9800;border:1px solid #FFB74D;">
            <i class="ri-arrow-go-back-line me-1"></i> Kembali
          </a>
        </div>

      </form>
    </div>
  </div>
</div>

{{-- 🎨 STYLE TAMBAHAN --}}
<style>
.smooth-fade { animation: fadeIn 0.6s ease-in-out; }
@keyframes fadeIn { from {opacity:0;transform:translateY(10px);} to {opacity:1;transform:translateY(0);} }

.form-control:focus, .form-select:focus {
  border-color: #FF9800 !important;
  box-shadow: 0 0 0 3px rgba(255,152,0,0.25);
}
.hover-glow:hover {
  background-color: #FFC107 !important;
  box-shadow: 0 0 12px rgba(255,152,0,0.4);
}

.breadcrumb-link { position: relative; transition: all 0.25s ease; }
.breadcrumb-link::after {
  content: ''; position: absolute; bottom: -2px; left: 0; width: 0; height: 2px; background: #FF9800;
  transition: width 0.25s ease;
}
.breadcrumb-link:hover::after { width: 100%; }

/* =============================== */
/* 🎯 FIX SELECT2 – BIKIN SAMA */
/* =============================== */
.select2-container--default .select2-selection--single {
    height: 47px !important;
    padding: 8px 12px !important;
    border: none !important;
    border-radius: .375rem !important;
    box-shadow: 0 1px 3px rgba(0,0,0,0.12) !important;
    border-left: 4px solid #FF9800 !important;
    display: flex !important;
    align-items: center !important;
}

.select2-container--default .select2-selection--single:focus,
.select2-container--default.select2-container--focus .select2-selection--single {
    outline: none !important;
    box-shadow: 0 0 0 3px rgba(255,152,0,0.25) !important;
    border-left: 4px solid #FF9800 !important;
}

.select2-container--default .select2-selection__arrow {
    height: 100% !important;
    right: 10px !important;
}

.select2-container .select2-selection--single .select2-selection__rendered {
    padding-left: 0 !important;
    color: #333 !important;
    font-size: 14px !important;
    line-height: 45px !important;
}
</style>

{{-- ======================== --}}
{{-- 📌 SELECT2 --}}
{{-- ======================== --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
  $(document).ready(function() {
      $('.select2').select2({
          width: '100%',
          minimumResultsForSearch: 0,
      });
  });
</script>

@endsection
