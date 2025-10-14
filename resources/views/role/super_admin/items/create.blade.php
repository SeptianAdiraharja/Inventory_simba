@extends('layouts.index')

@section('content')
<div class="row mb-6 gy-6">
  <div class="col-xxl">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Tambah Barang</h5>
        <small class="text-body-secondary">tambah input barang baru</small>
      </div>
      <div class="card-body">
        <form action="{{ route('super_admin.items.store') }}" method="POST" enctype="multipart/form-data">
          @csrf

          {{-- Nama Item --}}
          <div class="row mb-4">
            <label class="col-sm-2 col-form-label">Nama Barang</label>
            <div class="col-sm-10">
              <input type="text" name="name" class="form-control" placeholder="Isi Nama Item" required>
              @error('name') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
          </div>

          {{-- Toggle + Input Barcode --}}
          <div class="row mb-4 align-items-center">
            <label class="col-sm-2 col-form-label">Barcode / Kode</label>
            <div class="col-sm-10">
              <div class="card border shadow-sm">
                <div class="card-body py-3">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-semibold">Punya barcode bawaan?</span>
                    <div class="form-check form-switch m-0">
                      <input class="form-check-input" type="checkbox" id="toggleBarcode">
                      <label class="form-check-label" for="toggleBarcode">Isi manual</label>
                    </div>
                  </div>
                  <input type="text" name="code" class="form-control"
                        placeholder="Scan / isi barcode barang"
                        value="{{ old('code') }}" id="barcodeInput" style="display:none;">
                  @error('code') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
              </div>
            </div>
          </div>

          {{-- Kategori --}}
          <div class="row mb-4">
            <label class="col-sm-2 col-form-label">Kategori</label>
            <div class="col-sm-10">
              <select name="category_id" class="form-control" required>
                <option value="">-- Pilih Kategori --</option>
                @foreach($categories as $cat)
                  <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
              </select>
              @error('category_id') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
          </div>

          {{-- Satuan --}}
          <div class="row mb-4">
            <label class="col-sm-2 col-form-label">Satuan Barang</label>
            <div class="col-sm-10">
              <select name="unit_id" class="form-control" required>
                <option value="">-- Pilih Satuan --</option>
                @foreach($units as $unit)
                  <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                @endforeach
              </select>
              @error('unit_id') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
          </div>

          {{-- Supplier --}}
          <div class="row mb-4">
            <label class="col-sm-2 col-form-label">Supplier</label>
            <div class="col-sm-10">
              <select name="supplier_id" class="form-control">
                <option value="">-- Pilih Supplier (opsional) --</option>
                @foreach($suppliers as $sup)
                  <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                @endforeach
              </select>
              @error('supplier_id') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
          </div>

          {{-- Harga --}}
          <div class="mb-4 row">
            <label for="price" class="col-sm-2 col-form-label">Harga</label>
            <div class="col-sm-10">
              <input type="number" name="price" id="price" class="form-control"
                     value="{{ old('price') }}" step="0.01" required>
              @error('price') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
          </div>

          {{-- Gambar --}}
          <div class="mb-4 row">
            <label for="image" class="col-sm-2 col-form-label">Gambar</label>
            <div class="col-sm-10">
             <input type="file" name="image" id="image" class="form-control" accept="image/*">
              <small class="text-muted">Ukuran maksimal 1 MB (format: JPG, PNG, atau JPEG)</small>
              @error('image') <small class="text-danger d-block">{{ $message }}</small> @enderror
            </div>
          </div>

          {{-- Tombol --}}
          <div class="row justify-content-end">
            <div class="col-sm-10">
              <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
              <a href="{{ route('super_admin.items.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('toggleBarcode').addEventListener('change', function () {
    const barcodeInput = document.getElementById('barcodeInput');
    if (this.checked) {
        barcodeInput.style.display = 'block';
    } else {
        barcodeInput.style.display = 'none';
    }
});
</script>

<script>
document.getElementById('image').addEventListener('change', function() {
  const file = this.files[0];
  if (file && file.size > 1 * 1024 * 1024) {
    alert('Ukuran gambar melebihi 1 MB! Silakan pilih gambar lain.');
    this.value = '';
  }
});
</script>

@endsection
