@extends('layouts.index')
@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- 🧭 BREADCRUMB --}}
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex justify-content-between align-items-center">
    <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
      <i class="bi bi-envelope-plus" style="color:#FF9800;"></i> Tambah Kop Surat
    </h4>
    <a href="{{ route('super_admin.kop_surat.index') }}" class="btn btn-light rounded-pill border shadow-sm">
      <i class="bi bi-arrow-left"></i> Kembali
    </a>
  </div>

  {{-- 🧾 FORM --}}
  <div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
      <form action="{{ route('super_admin.kop_surat.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label fw-semibold">Nama Instansi</label>
            <input type="text" name="nama_instansi" class="form-control border-0 shadow-sm"
                   style="border-left:4px solid #FF9800;" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-semibold">Nama Unit / Lembaga</label>
            <input type="text" name="nama_unit" class="form-control border-0 shadow-sm"
                   style="border-left:4px solid #FF9800;" required>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Alamat</label>
          <textarea name="alamat" rows="2" class="form-control border-0 shadow-sm"
                    style="border-left:4px solid #FF9800;"></textarea>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label fw-semibold">Telepon</label>
            <input type="text" name="telepon" class="form-control border-0 shadow-sm"
                   style="border-left:4px solid #FF9800;">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-semibold">Kota</label>
            <input type="text" name="kota" class="form-control border-0 shadow-sm"
                   style="border-left:4px solid #FF9800;">
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label fw-semibold">Website</label>
            <input type="text" name="website" class="form-control border-0 shadow-sm"
                   style="border-left:4px solid #FF9800;">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-semibold">Email</label>
            <input type="email" name="email" class="form-control border-0 shadow-sm"
                   style="border-left:4px solid #FF9800;">
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Logo (opsional)</label>
          <input type="file" name="logo" class="form-control border-0 shadow-sm"
                 accept="image/*" style="border-left:4px solid #FF9800;">
        </div>

        <div class="text-end">
          <button type="submit" class="btn rounded-pill hover-glow px-4"
                  style="background-color:#FF9800;color:white;">
            <i class="bi bi-save"></i> Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
.hover-glow:hover { background:#FFC107 !important; box-shadow:0 0 10px rgba(255,152,0,0.4); }
.form-control:focus { border-color:#FF9800 !important; box-shadow:0 0 0 3px rgba(255,152,0,0.25); }
</style>
@endsection
