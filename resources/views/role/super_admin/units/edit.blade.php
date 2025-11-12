@extends('layouts.index')

@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- ======================== --}}
  {{-- 🧭 BREADCRUMB MODERN ORANGE --}}
  {{-- ======================== --}}
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex align-items-center justify-content-between smooth-fade flex-wrap">
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <i class="bi bi-rulers fs-5" style="color:#FF9800;"></i>
      <a href="{{ route('dashboard') }}" class="breadcrumb-link fw-semibold text-decoration-none" style="color:#FF9800;">
        Dashboard
      </a>
      <span class="text-muted">/</span>
      <a href="{{ route('super_admin.units.index') }}" class="fw-semibold text-decoration-none" style="color:#FFB300;">
        Daftar Satuan Barang
      </a>
      <span class="text-muted">/</span>
      <span class="text-dark fw-semibold">Edit Satuan Barang</span>
    </div>
  </div>

  {{-- ======================== --}}
  {{-- ✏️ FORM EDIT SATUAN --}}
  {{-- ======================== --}}
  <div class="card shadow-sm border-0 rounded-4 smooth-fade">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 px-4 border-bottom">
      <h5 class="fw-bold mb-0 d-flex align-items-center gap-2" style="color:#FF9800;">
        <i class="ri ri-edit-line"></i> Edit Satuan Barang
      </h5>
      <small class="text-muted">Ubah data satuan barang sesuai kebutuhan</small>
    </div>

    <div class="card-body px-4 py-4">
      <form action="{{ route('super_admin.units.update', $unit->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row mb-4 align-items-center">
          <label class="col-sm-3 col-form-label fw-semibold text-dark">Nama Satuan Barang</label>
          <div class="col-sm-9">
            <input type="text" name="name" class="form-control rounded-3 shadow-sm border-1"
                   style="border-color:#FFD54F;" placeholder="Masukkan nama satuan barang"
                   value="{{ $unit->name }}" required>
            @error('name')
              <small class="text-danger">{{ $message }}</small>
            @enderror
          </div>
        </div>

        <div class="row justify-content-end">
          <div class="col-sm-9 d-flex gap-2">
            <button type="submit" class="btn btn-sm rounded-pill text-white px-4 shadow-sm hover-glow"
                    style="background-color:#FF9800;">
              <i class="ri-check-line me-1"></i> Perbarui
            </button>
            <a href="{{ route('super_admin.units.index') }}"
               class="btn btn-sm btn-outline-warning rounded-pill px-4 fw-medium shadow-sm hover-glow-outline">
              <i class="ri-arrow-go-back-line me-1"></i> Kembali
            </a>
          </div>
        </div>
      </form>
    </div>
  </div>

</div>

{{-- ======================== --}}
{{-- 🎨 STYLE TAMBAHAN (TEMA ORANGE) --}}
{{-- ======================== --}}
<style>
.smooth-fade {
  animation: fadeIn 0.6s ease-in-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

/* ===== Hover Glow ===== */
.hover-glow {
  transition: all 0.25s ease;
}
.hover-glow:hover {
  background-color: #FFC300 !important;
  color: #fff !important;
  box-shadow: 0 0 12px rgba(255,152,0,0.4);
}

/* ===== Outline Glow ===== */
.hover-glow-outline {
  transition: all 0.25s ease;
  border-color: #FFB300 !important;
  color: #FF9800 !important;
}
.hover-glow-outline:hover {
  background-color: #FF9800 !important;
  color: #fff !important;
  box-shadow: 0 0 12px rgba(255,152,0,0.4);
}

/* ===== Input Focus ===== */
input:focus {
  border-color: #FF9800 !important;
  box-shadow: 0 0 0 3px rgba(255,193,7,0.25) !important;
}

/* ===== Breadcrumb ===== */
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

/* ===== Responsif ===== */
@media (max-width:768px){
  .col-form-label{font-size:0.9rem;}
  .btn{font-size:0.85rem;}
}
</style>
@endsection
