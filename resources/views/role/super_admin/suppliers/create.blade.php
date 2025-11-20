@extends('layouts.index')
@section('title', 'Tambah Supplier')
@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- ======================== --}}
  {{-- 🧭 BREADCRUMB ORANGE --}}
  {{-- ======================== --}}
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap align-items-center justify-content-between smooth-fade">
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <i class="bi bi-people-fill fs-5" style="color:#FF9800;"></i>
      <a href="{{ route('dashboard') }}" class="breadcrumb-link fw-semibold text-decoration-none" style="color:#FF9800;">
        Dashboard
      </a>
      <span class="text-muted">/</span>
      <a href="{{ route('super_admin.suppliers.index') }}" class="fw-semibold text-decoration-none" style="color:#FFB300;">
        Daftar Supplier
      </a>
      <span class="text-muted">/</span>
      <span class="text-dark fw-semibold">Tambah Supplier</span>
    </div>
  </div>

  {{-- ======================== --}}
  {{-- 🧾 FORM TAMBAH SUPPLIER --}}
  {{-- ======================== --}}
  <div class="card border-0 shadow-sm rounded-4 overflow-hidden smooth-fade">
    <div class="card-header bg-white border-0 py-3 px-4 d-flex flex-wrap justify-content-between align-items-center border-bottom">
      <div class="d-flex align-items-center gap-2">
        <i class="ri-user-add-line fs-5" style="color:#FF9800;"></i>
        <h5 class="fw-bold mb-0" style="color:#FF9800;">Tambah Supplier</h5>
      </div>
      <small class="text-muted text-end ms-auto mt-1 mt-sm-0">Input data supplier baru</small>
    </div>

    <div class="card-body bg-light p-4">
      <form action="{{ route('super_admin.suppliers.store') }}" method="POST" class="needs-validation" novalidate>
        @csrf

        {{-- Nama Supplier --}}
        <div class="mb-4">
          <label for="name" class="form-label fw-semibold text-dark mb-2">Nama Supplier</label>
          <input type="text" id="name" name="name"
                 class="form-control form-control-lg border-0 rounded-3 shadow-sm px-3 py-2"
                 placeholder="Isi nama supplier" required
                 style="background:#fff; border-left:4px solid #FF9800 !important;">
          @error('name')
            <small class="text-danger">{{ $message }}</small>
          @enderror
        </div>

        {{-- Kontak Supplier --}}
        <div class="mb-4">
          <label for="contact" class="form-label fw-semibold text-dark mb-2">Kontak</label>
          <input type="text" id="contact" name="contact"
                 class="form-control form-control-lg border-0 rounded-3 shadow-sm px-3 py-2"
                 placeholder="Isi nomor telepon atau email (opsional)"
                 style="background:#fff; border-left:4px solid #FF9800 !important;">
          @error('contact')
            <small class="text-danger">{{ $message }}</small>
          @enderror
        </div>

        {{-- Tombol Aksi --}}
        <div class="d-flex justify-content-end gap-2 pt-2">
          <button type="submit" class="btn rounded-pill text-white fw-semibold shadow-sm px-4 py-2 hover-glow"
                  style="background-color:#FF9800;">
            <i class="ri-check-line me-1"></i> Simpan
          </button>

          <a href="{{ route('super_admin.suppliers.index') }}"
             class="btn rounded-pill fw-semibold border-2 px-4 py-2 hover-glow-outline"
             style="border-color:#FF9800;color:#FF9800;">
            <i class="ri-arrow-go-back-line me-1"></i> Kembali
          </a>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- 🎨 STYLE --}}
<style>
.smooth-fade{animation:fadeIn .6s ease-in-out;}
@keyframes fadeIn{from{opacity:0;transform:translateY(10px);}to{opacity:1;transform:translateY(0);}}
.hover-glow{transition:all .25s ease;}
.hover-glow:hover{background-color:#FFC107!important;color:#fff!important;box-shadow:0 0 12px rgba(255,152,0,0.4);}
.hover-glow-outline{transition:all .25s ease;}
.hover-glow-outline:hover{background-color:#FF9800!important;color:#fff!important;box-shadow:0 0 10px rgba(255,152,0,0.4);}
</style>
@endsection
