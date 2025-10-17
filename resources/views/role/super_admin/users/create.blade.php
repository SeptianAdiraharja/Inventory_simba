@extends('layouts.index')

@section('content')
<div class="row mb-6 gy-6">
  <div class="col-xxl">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Tambah Akun</h5>
        <small class="text-body-secondary">tambah input akun baru</small>
      </div>
      <div class="card-body">
        <form action="{{ route('super_admin.users.store') }}" method="POST">
          @csrf

          <div class="row mb-4">
            <label class="col-sm-2 col-form-label">Nama</label>
            <div class="col-sm-10">
              <input type="text" name="name" class="form-control" placeholder="Isi nama" required>
              @error('name') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
          </div>

          <div class="row mb-4">
            <label class="col-sm-2 col-form-label">Email</label>
            <div class="col-sm-10">
              <input type="email" name="email" class="form-control" placeholder="Isi email" required>
              @error('email') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
          </div>

          <div class="row mb-4">
            <label class="col-sm-2 col-form-label">Password</label>
            <div class="col-sm-10">
              <input type="password" name="password" class="form-control" placeholder="Isi password" required>
              @error('password') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
          </div>

          <div class="row mb-4">
            <label class="col-sm-2 col-form-label">Konfirmasi Password</label>
            <div class="col-sm-10">
              <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password" required>
            </div>
          </div>

          <div class="row mb-4">
            <label class="col-sm-2 col-form-label">Peran</label>
            <div class="col-sm-10">
              <select name="role" class="form-select" required>
                <option value="pegawai">Pegawai</option>
                <option value="admin">Admin</option>
              </select>
              @error('role') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
          </div>

          <div class="row justify-content-end">
            <div class="col-sm-10">
              <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
              <a href="{{ route('super_admin.users.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
