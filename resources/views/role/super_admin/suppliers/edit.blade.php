@extends('layouts.index')

@section('content')
<div class="row mb-6 gy-6">
  <div class="col-xxl">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Edit Supplier</h5>
        <small class="text-body-secondary"> ubah data supplier</small>
      </div>
      <div class="card-body">
        <form action="{{ route('super_admin.suppliers.update', $supplier->id) }}" method="POST">
          @csrf
          @method('PUT')

          <div class="row mb-4">
            <label class="col-sm-2 col-form-label">Nama Supplier</label>
            <div class="col-sm-10">
              <input type="text" name="name" class="form-control" value="{{ $supplier->name }}" required>
              @error('name') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
          </div>

          <div class="row mb-4">
            <label class="col-sm-2 col-form-label">Kontak</label>
            <div class="col-sm-10">
              <input type="text" name="contact" class="form-control" value="{{ $supplier->contact }}">
              @error('contact') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
          </div>

          <div class="row justify-content-end">
            <div class="col-sm-10">
              <button type="submit" class="btn btn-primary btn-sm">Perbarui</button>
              <a href="{{ route('super_admin.suppliers.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
