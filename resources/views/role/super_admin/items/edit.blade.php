@extends('layouts.index')

@section('content')
<div class="row mb-6 gy-6">
  <div class="col-xxl">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Edit Item</h5>
        <small class="text-body-secondary">ubah data item</small>
      </div>
      <div class="card-body">
        <form action="{{ route('super_admin.items.update', $item->id) }}" method="POST" enctype="multipart/form-data">
          @csrf
          @method('PUT')

          <div class="row mb-4">
            <label class="col-sm-2 col-form-label">Nama Item</label>
            <div class="col-sm-10">
              <input type="text" name="name" class="form-control" value="{{ $item->name }}" required>
              @error('name') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
          </div>

          <div class="row mb-4">
            <label class="col-sm-2 col-form-label">Kategori</label>
            <div class="col-sm-10">
              <select name="category_id" class="form-control" required>
                @foreach($categories as $cat)
                  <option value="{{ $cat->id }}" {{ $item->category_id == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                  </option>
                @endforeach
              </select>
              @error('category_id') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
          </div>

          <div class="row mb-4">
            <label class="col-sm-2 col-form-label">Unit</label>
            <div class="col-sm-10">
              <select name="unit_id" class="form-control" required>
                @foreach($units as $unit)
                  <option value="{{ $unit->id }}" {{ $item->unit_id == $unit->id ? 'selected' : '' }}>
                    {{ $unit->name }}
                  </option>
                @endforeach
              </select>
              @error('unit_id') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
          </div>

          <div class="mb-3">
              <label for="price" class="form-label">Harga</label>
              <input type="number" name="price" id="price" class="form-control"
                    value="{{ old('price', $item->price) }}" step="0.01" required>
          </div>

          <div class="form-group">
            <label for="image">Gambar (opsional)</label>
              <input type="file" name="image" class="form-control">
              @if($item->image)
                  <div class="mt-2">
                      <p>Gambar saat ini:</p>
                      <img src="{{ asset('storage/' . $item->image) }}" alt="Gambar Item" width="120">
                  </div>
              @endif
          </div>

          <div class="row justify-content-end">
            <div class="col-sm-10">
              <button type="submit" class="btn btn-primary btn-sm">Perbarui</button>
              <a href="{{ route('super_admin.items.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
