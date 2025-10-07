@extends('layouts.index')

@section('content')
<div class="row mb-6 gy-6">
  <div class="col-xxl">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Edit Satuan Barang</h5>
        <small class="text-body-secondary">ubah data satuan barang</small>
      </div>
      <div class="card-body">
        <form action="{{ route('super_admin.units.update', $unit->id) }}" method="POST">
          @csrf
          @method('PUT')

          <div class="row mb-4">
            <label class="col-sm-2 col-form-label">Nama Satuan Barang</label>
            <div class="col-sm-10">
              <input type="text" name="name" class="form-control" value="{{ $unit->name }}" required>
              @error('name') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
          </div>

          <div class="row justify-content-end">
            <div class="col-sm-10">
              <button type="submit" class="btn btn-primary btn-sm">Perbarui</button>
              <a href="{{ route('super_admin.units.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
