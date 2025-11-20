@extends('layouts.index')
@section('title', 'Tambah Data Barang')
@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- ======================== --}}
  {{-- 🧭 BREADCRUMB ORANGE --}}
  {{-- ======================== --}}
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap align-items-center justify-content-between smooth-fade">
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <i class="ri-archive-2-line fs-5" style="color:#FF9800;"></i>
      <a href="{{ route('dashboard') }}" class="breadcrumb-link fw-semibold text-decoration-none" style="color:#FF9800;">
        Dashboard
      </a>
      <span class="text-muted">/</span>
      <a href="{{ route('super_admin.items.index') }}" class="fw-semibold text-decoration-none" style="color:#FFB300;">
        Daftar Barang
      </a>
      <span class="text-muted">/</span>
      <span class="fw-semibold text-dark">Tambah Barang</span>
    </div>

    <a href="{{ route('super_admin.items.index') }}"
       class="btn rounded-pill btn-sm d-flex align-items-center gap-2 shadow-sm hover-glow"
       style="background-color:#FF9800;color:#fff;">
      <i class="ri-arrow-left-line"></i> Kembali
    </a>
  </div>

  {{-- ======================== --}}
  {{-- 📦 FORM TAMBAH BARANG --}}
  {{-- ======================== --}}
  <div class="card border-0 shadow-sm rounded-4 smooth-fade">
    <div class="card-header bg-white border-0 d-flex justify-content-between flex-wrap align-items-center">
      <h4 class="fw-bold mb-0" style="color:#FF9800;">
        <i class="ri-add-circle-line me-2"></i> Tambah Barang Baru
      </h4>
      <small class="text-warning fw-semibold">Lengkapi data dengan benar</small>
    </div>

    <div class="card-body bg-white p-4 rounded-bottom-4">
      <form action="{{ route('super_admin.items.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Nama Barang --}}
        <div class="mb-4">
          <label class="form-label fw-semibold text-dark">Nama Barang</label>
          <input type="text" name="name" class="form-control shadow-sm border-0"
                 placeholder="Isi nama barang" required
                 style="border-left:4px solid #FF9800 !important;">
          @error('name') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        {{-- Barcode --}}
        <div class="mb-4">
          <label class="form-label fw-semibold text-dark">Barcode / Kode</label>
          <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body py-3">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fw-semibold text-secondary">Punya barcode bawaan?</span>
                <div class="form-check form-switch m-0">
                  <input class="form-check-input" type="checkbox" id="toggleBarcode">
                  <label class="form-check-label text-muted" for="toggleBarcode">Isi manual</label>
                </div>
              </div>
              <input type="text" name="code" id="barcodeInput"
                     class="form-control shadow-sm border-0"
                     placeholder="Scan / isi barcode barang"
                     value="{{ old('code') }}" style="display:none;border-left:4px solid #FF9800 !important;">
              @error('code') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
          </div>
        </div>

        {{-- Kategori --}}
        <div class="mb-4">
          <label class="form-label fw-semibold text-dark">Kategori</label>
          <select name="category_id" class="form-select shadow-sm border-0"
                  style="border-left:4px solid #FF9800 !important;" required>
            <option value="">-- Pilih Kategori --</option>
            @foreach($categories as $cat)
              <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
          </select>
          @error('category_id') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        {{-- Satuan --}}
        <div class="mb-4">
          <label class="form-label fw-semibold text-dark">Satuan Barang</label>
          <select name="unit_id" class="form-select shadow-sm border-0"
                  style="border-left:4px solid #FF9800 !important;" required>
            <option value="">-- Pilih Satuan --</option>
            @foreach($units as $unit)
              <option value="{{ $unit->id }}">{{ $unit->name }}</option>
            @endforeach
          </select>
          @error('unit_id') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        {{-- Harga --}}
        <div class="mb-4">
          <label class="form-label fw-semibold text-dark">Harga</label>
          <input type="number" name="price" id="price" step="0.01"
                 class="form-control shadow-sm border-0"
                 placeholder="Masukkan harga barang"
                 style="border-left:4px solid #FF9800 !important;" required>
          @error('price') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        {{-- Gambar --}}
        <div class="mb-4">
          <label class="form-label fw-semibold text-dark">Gambar</label>
          <input type="file" name="image" id="image"
                 class="form-control shadow-sm border-0"
                 accept="image/*" style="border-left:4px solid #FF9800 !important;">
          <small class="text-muted">Ukuran maksimal 1 MB (JPG, PNG, JPEG)</small>
          @error('image') <small class="text-danger d-block">{{ $message }}</small> @enderror
        </div>

        {{-- Tombol --}}
        <div class="d-flex justify-content-end gap-2 mt-4">
          <button type="submit"
                  class="btn btn-sm rounded-pill px-4 shadow-sm hover-glow"
                  style="background-color:#FF9800;color:white;">
            <i class="ri-save-3-line me-1"></i> Simpan
          </button>
          <a href="{{ route('super_admin.items.index') }}"
             class="btn btn-sm rounded-pill px-4"
             style="background-color:#FFF3E0;color:#FF9800;border:1px solid #FFB74D;">
            <i class="ri-arrow-go-back-line me-1"></i> Kembali
          </a>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ======================== --}}
{{-- 🌈 SCRIPT TAMBAHAN --}}
{{-- ======================== --}}
<script>
document.getElementById('toggleBarcode').addEventListener('change', function () {
  const barcodeInput = document.getElementById('barcodeInput');
  barcodeInput.style.display = this.checked ? 'block' : 'none';
});

document.getElementById('image').addEventListener('change', function() {
  const file = this.files[0];
  if (file && file.size > 1 * 1024 * 1024) {
    alert('Ukuran gambar melebihi 1 MB! Silakan pilih gambar lain.');
    this.value = '';
  }
});
</script>

{{-- ======================== --}}
{{-- 🎨 STYLE TAMBAHAN --}}
{{-- ======================== --}}
<style>
.smooth-fade { animation: fadeIn 0.6s ease-in-out; }
@keyframes fadeIn { from {opacity:0;transform:translateY(10px);} to {opacity:1;transform:translateY(0);} }

.form-control:focus, .form-select:focus {
  border-color: #FF9800 !important;
  box-shadow: 0 0 0 3px rgba(255,152,0,0.25);
}

.hover-glow {
  transition: all 0.25s ease;
}
.hover-glow:hover {
  background-color: #FFC107 !important;
  box-shadow: 0 0 12px rgba(255,152,0,0.4);
}

.card {
  border-radius: 1rem !important;
  transition: all 0.3s ease;
}
.card:hover {
  box-shadow: 0 6px 18px rgba(0,0,0,0.08) !important;
}

.breadcrumb-link {
  position: relative;
  transition: all 0.25s ease;
}
.breadcrumb-link::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  width: 0;
  height: 2px;
  background: #FF9800;
  transition: width 0.25s ease;
}
.breadcrumb-link:hover::after {
  width: 100%;
}

@media (max-width: 768px) {
  .card-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
  .btn {
    font-size: 0.9rem;
  }
}
</style>
@endsection
