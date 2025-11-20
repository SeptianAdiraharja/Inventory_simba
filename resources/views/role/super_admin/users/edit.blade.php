@extends('layouts.index')
@section('title', 'Edit Akun Pengguna')
@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- 🧭 BREADCRUMB --}}
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap align-items-center justify-content-between smooth-fade">
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <i class="bi bi-pencil-square fs-5" style="color:#FF9800;"></i>
      <a href="{{ route('super_admin.dashboard') }}" class="breadcrumb-link fw-semibold text-decoration-none" style="color:#FF9800;">
        Dashboard
      </a>
      <span class="text-muted">/</span>
      <a href="{{ route('super_admin.users.index') }}" class="fw-semibold text-decoration-none" style="color:#FFB300;">
        Daftar Pengguna
      </a>
      <span class="text-muted">/</span>
      <span class="fw-semibold text-dark">Edit Akun</span>
    </div>
  </div>

  {{-- 🧑‍💻 FORM EDIT --}}
  <div class="card border-0 shadow-sm rounded-4 smooth-fade">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
      <h4 class="fw-bold mb-0" style="color:#FF9800;">
        <i class="ri-user-settings-line me-2"></i> Edit Akun Pengguna
      </h4>
      <small class="text-warning fw-semibold">Perbarui data akun sesuai kebutuhan</small>
    </div>

    <div class="card-body p-4 bg-white rounded-bottom-4">
      <form action="{{ route('super_admin.users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Nama --}}
        <div class="mb-4">
          <label class="form-label fw-semibold text-dark">Nama</label>
          <input type="text" name="name" class="form-control border-0 shadow-sm"
                 style="border-left:4px solid #FF9800 !important;"
                 value="{{ $user->name }}" required>
        </div>

        {{-- Email --}}
        <div class="mb-4">
          <label class="form-label fw-semibold text-dark">Email</label>
          <input type="email" name="email" class="form-control border-0 shadow-sm"
                 style="border-left:4px solid #FF9800 !important;"
                 value="{{ $user->email }}" required>
        </div>

        {{-- Password --}}
        <div class="mb-4">
          <label class="form-label fw-semibold text-dark">Kata Sandi (Opsional)</label>
          <input type="password" name="password" class="form-control border-0 shadow-sm"
                 placeholder="Isi jika ingin mengganti password"
                 style="border-left:4px solid #FF9800 !important;">
        </div>

        {{-- Konfirmasi Password --}}
        <div class="mb-4">
          <label class="form-label fw-semibold text-dark">Konfirmasi Kata Sandi</label>
          <input type="password" name="password_confirmation" class="form-control border-0 shadow-sm"
                 placeholder="Ulangi password baru"
                 style="border-left:4px solid #FF9800 !important;">
        </div>

        {{-- Role --}}
        <div class="mb-4">
          <label class="form-label fw-semibold text-dark">Peran</label>
          <select name="role" class="form-select border-0 shadow-sm"
                  style="border-left:4px solid #FF9800 !important;" required>
            <option value="pegawai" {{ $user->role == 'pegawai' ? 'selected' : '' }}>Pegawai</option>
            <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
          </select>
        </div>

        {{-- Kategori --}}
        <div class="mb-4">
            <label class="form-label fw-semibold text-dark">Kategori</label>

            <div class="p-3 border rounded-3 shadow-sm kategori-columns"
                style="border-left:4px solid #FF9800 !important; background:#fff;">

                @foreach($categories as $category)
                    <div class="form-check mb-2">
                        <input class="form-check-input"
                              type="checkbox"
                              id="cat{{ $category->id }}"
                              name="categories[]"
                              value="{{ $category->id }}"
                              {{ $user->categories->contains($category->id) ? 'checked' : '' }}>

                        <label class="form-check-label" for="cat{{ $category->id }}">
                            {{ $category->name }}
                        </label>
                    </div>
                @endforeach

            </div>

            @error('categories')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        {{-- Style khusus kategori --}}
        <style>
            .kategori-columns {
                columns: 3;               /* 3 baris ke bawah, sisanya pindah ke samping */
                -webkit-columns: 3;
                -moz-columns: 3;
            }

            .kategori-columns .form-check {
                break-inside: avoid;      /* biar checkbox tidak kepotong kolom */
            }

            .kategori-columns {
                column-gap: 30px;         /* jarak antar kolom */
            }
        </style>

        {{-- Tombol --}}
        <div class="d-flex justify-content-end gap-2 mt-4">
          <button type="submit" class="btn btn-sm rounded-pill px-4 hover-glow shadow-sm"
                  style="background-color:#FF9800;color:white;">
            <i class="ri-save-3-line me-1"></i> Perbarui
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

{{-- 🌈 STYLE TAMBAHAN --}}
<style>
.smooth-fade { animation: fadeIn 0.6s ease-in-out; }
.form-control:focus, .form-select:focus {
  border-color: #FF9800 !important;
  box-shadow: 0 0 0 3px rgba(255,152,0,0.25);
}
.hover-glow:hover {
  background-color: #FFC107 !important;
  box-shadow: 0 0 12px rgba(255,152,0,0.4);
}
.breadcrumb-link::after {
  content:'';position:absolute;bottom:-2px;left:0;width:0;height:2px;
  background:#FF9800;transition:width 0.25s ease;
}
.breadcrumb-link:hover::after{width:100%;}
</style>
@endsection
