@extends('layouts.index')

@section('content')
<div class="row mb-6 gy-6">
  <div class="col-xxl">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Tambah Kategori</h5>
        <small class="text-body-secondary">Tambah kategori baru</small>
      </div>
      <div class="card-body">
        <form action="{{ route('super_admin.categories.store') }}" method="POST">
          @csrf
          <div class="row mb-4">
            <label class="col-sm-2 col-form-label">Nama Kategori</label>
            <div class="col-sm-10">
              <input type="text" name="name" class="form-control" placeholder="Isi Kategori" required>
              @error('name') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
          </div>

          <div class="row justify-content-end">
            <div class="col-sm-10">
              <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
              <a href="{{ route('super_admin.categories.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
