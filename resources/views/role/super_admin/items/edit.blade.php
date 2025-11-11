@extends('layouts.index')

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
      <span class="fw-semibold text-dark">Edit Barang</span>
    </div>

    <a href="{{ route('super_admin.items.index') }}"
       class="btn rounded-pill btn-sm d-flex align-items-center gap-2 shadow-sm hover-glow"
       style="background-color:#FF9800;color:#fff;">
      <i class="ri-arrow-left-line"></i> Kembali
    </a>
  </div>

  {{-- ======================== --}}
  {{-- 📝 FORM EDIT BARANG --}}
  {{-- ======================== --}}
  <div class="card border-0 shadow-sm rounded-4 smooth-fade">
    <div class="card-header bg-white border-0 d-flex justify-content-between flex-wrap align-items-center">
      <h4 class="fw-bold mb-0" style="color:#FF9800;">
        <i class="ri-pencil-line me-2"></i> Edit Data Barang
      </h4>
      <small class="text-warning fw-semibold">Ubah informasi barang sesuai kebutuhan</small>
    </div>

    <div class="card-body bg-white p-4 rounded-bottom-4">
      <form action="{{ route('super_admin.items.update', $item->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Nama Barang --}}
        <div class="mb-4">
          <label class="form-label fw-semibold text-dark">Nama Barang</label>
          <input type="text" name="name" class="form-control shadow-sm border-0"
                 value="{{ $item->name }}" required
                 style="border-left:4px solid #FF9800 !important;">
          @error('name') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        {{-- Kategori --}}
        <div class="mb-4">
          <label class="form-label fw-semibold text-dark">Kategori</label>
          <select name="category_id" class="form-select shadow-sm border-0"
                  style="border-left:4px solid #FF9800 !important;" required>
            @foreach($categories as $cat)
              <option value="{{ $cat->id }}" {{ $item->category_id == $cat->id ? 'selected' : '' }}>
                {{ $cat->name }}
              </option>
            @endforeach
          </select>
          @error('category_id') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        {{-- Unit --}}
        <div class="mb-4">
          <label class="form-label fw-semibold text-dark">Satuan Barang</label>
          <select name="unit_id" class="form-select shadow-sm border-0"
                  style="border-left:4px solid #FF9800 !important;" required>
            @foreach($units as $unit)
              <option value="{{ $unit->id }}" {{ $item->unit_id == $unit->id ? 'selected' : '' }}>
                {{ $unit->name }}
              </option>
            @endforeach
          </select>
          @error('unit_id') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        {{-- Harga --}}
        <div class="mb-4">
          <label class="form-label fw-semibold text-dark">Harga</label>
          <input type="number" name="price" id="price" step="0.01"
                 class="form-control shadow-sm border-0"
                 value="{{ old('price', $item->price) }}" required
                 style="border-left:4px solid #FF9800 !important;">
          @error('price') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        {{-- Gambar --}}
        <div class="mb-4">
          <label class="form-label fw-semibold text-dark">Gambar (Opsional)</label>
          <input type="file" name="image" class="form-control shadow-sm border-0"
                 accept="image/*" style="border-left:4px solid #FF9800 !important;">
          <small class="text-muted">Ukuran maksimal 1 MB (JPG, PNG, JPEG)</small>
          @error('image') <small class="text-danger d-block">{{ $message }}</small> @enderror

          @if($item->image)
            <div class="mt-3 text-center">
              <p class="fw-semibold text-secondary mb-1">Gambar Saat Ini:</p>
              <img src="{{ asset('storage/' . $item->image) }}" alt="Gambar Item"
                   class="img-fluid rounded shadow-sm border"
                   style="max-width:150px; background:#FFF8E1; padding:5px;">
            </div>
          @endif
        </div>

        {{-- Tombol --}}
        <div class="d-flex justify-content-end gap-2 mt-4">
          <button type="submit"
                  class="btn btn-sm rounded-pill px-4 shadow-sm hover-glow"
                  style="background-color:#FF9800;color:white;">
            <i class="ri-save-3-line me-1"></i> Perbarui
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
