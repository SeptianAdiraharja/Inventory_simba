@extends('layouts.index')

@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- 🧭 BREADCRUMB --}}
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap align-items-center justify-content-between smooth-fade">
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <i class="bi bi-person-plus-fill fs-5" style="color:#FF9800;"></i>
      <a href="{{ route('super_admin.dashboard') }}" class="breadcrumb-link fw-semibold text-decoration-none" style="color:#FF9800;">
        Dashboard
      </a>
      <span class="text-muted">/</span>
      <a href="{{ route('super_admin.users.index') }}" class="fw-semibold text-decoration-none" style="color:#FFB300;">
        Daftar Pengguna
      </a>
      <span class="text-muted">/</span>
      <span class="fw-semibold text-dark">Tambah Akun</span>
    </div>
  </div>

  {{-- 🧑‍💻 FORM TAMBAH AKUN --}}
  <div class="card border-0 shadow-sm rounded-4 smooth-fade">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
      <h4 class="fw-bold mb-0" style="color:#FF9800;">
        <i class="ri-user-add-line me-2"></i> Tambah Akun Baru
      </h4>
      <small class="text-warning fw-semibold">Isi data akun dengan lengkap</small>
    </div>

    <div class="card-body p-4 bg-white rounded-bottom-4">
      <form action="{{ route('super_admin.users.store') }}" method="POST">
        @csrf

        {{-- Nama --}}
        <div class="mb-4">
          <label class="form-label fw-semibold text-dark">Nama</label>
          <input type="text" name="name" class="form-control border-0 shadow-sm"
                 placeholder="Masukkan nama pengguna"
                 style="border-left:4px solid #FF9800 !important;" required>
          @error('name') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        {{-- Email --}}
        <div class="mb-4">
          <label class="form-label fw-semibold text-dark">Email</label>
          <input type="email" name="email" class="form-control border-0 shadow-sm"
                 placeholder="Masukkan email pengguna"
                 style="border-left:4px solid #FF9800 !important;" required>
          @error('email') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        {{-- Password --}}
        <div class="mb-4">
          <label class="form-label fw-semibold text-dark">Kata Sandi</label>
          <input type="password" name="password" class="form-control border-0 shadow-sm"
                 placeholder="Masukkan kata sandi"
                 style="border-left:4px solid #FF9800 !important;" required>
          @error('password') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        {{-- Konfirmasi Password --}}
        <div class="mb-4">
          <label class="form-label fw-semibold text-dark">Konfirmasi Kata Sandi</label>
          <input type="password" name="password_confirmation" class="form-control border-0 shadow-sm"
                 placeholder="Ulangi kata sandi"
                 style="border-left:4px solid #FF9800 !important;" required>
        </div>

        {{-- Role --}}
        <div class="mb-4">
          <label class="form-label fw-semibold text-dark">Peran</label>
          <select name="role" class="form-select border-0 shadow-sm"
                  style="border-left:4px solid #FF9800 !important;" required>
            <option value="pegawai">Pegawai</option>
            <option value="admin">Admin</option>
          </select>
        </div>

        {{-- Tombol --}}
        <div class="d-flex justify-content-end gap-2 mt-4">
          <button type="submit" class="btn btn-sm rounded-pill px-4 hover-glow shadow-sm"
                  style="background-color:#FF9800;color:white;">
            <i class="ri-save-3-line me-1"></i> Simpan
          </button>
          <a href="{{ route('super_admin.users.index') }}" class="btn btn-sm rounded-pill px-4"
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
  color: #fff !important;
  box-shadow: 0 0 10px rgba(255,152,0,0.4);
}
.breadcrumb-link::after {
  content:'';position:absolute;bottom:-2px;left:0;width:0;height:2px;
  background:#FF9800;transition:width 0.25s ease;
}
.breadcrumb-link:hover::after{width:100%;}
</style>
@endsection
