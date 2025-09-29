@extends('layouts.index')

@section('content')
<div class="row mb-6 gy-6">
  <div class="col-xxl">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Tambah Barang Masuk</h5>
        <small class="text-body-secondary">Form input item masuk baru</small>
      </div>
      <div class="card-body">
        <form action="{{ route('super_admin.item_ins.store') }}" method="POST" x-data="{ useExpired: true }">
          @csrf

          <div class="row mb-4">
            <label class="col-sm-2 col-form-label">Item</label>
            <div class="col-sm-10">
              <select name="item_id" class="form-control" required>
                <option value="">-- Pilih Item --</option>
                @foreach($items as $item)
                  <option value="{{ $item->id }}">{{ $item->name }}</option>
                @endforeach
              </select>
              @error('item_id') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
          </div>

          <div class="row mb-4">
            <label class="col-sm-2 col-form-label">Supplier</label>
            <div class="col-sm-10">
              <select name="supplier_id" class="form-control" required>
                <option value="">-- Pilih Supplier --</option>
                @foreach($suppliers as $supplier)
                  <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                @endforeach
              </select>
              @error('supplier_id') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
          </div>

          <div class="row mb-4">
            <label class="col-sm-2 col-form-label">Jumlah</label>
            <div class="col-sm-10">
              <input type="number" name="quantity" class="form-control" placeholder="Isi jumlah" required>
              @error('quantity') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
          </div>

          <!-- Input Tanggal Kedaluwarsa dengan toggle -->
          <div class="row mb-3 align-items-center">
              <label class="col-sm-2 col-form-label text-muted fw-semibold">
                  Tanggal Kedaluwarsa
              </label>
              <div class="col-sm-10">
                  <!-- Input hanya muncul jika toggle ON -->
                  <div x-show="useExpired" x-transition>
                      <input
                          type="date"
                          name="expired_at"
                          id="expired_at"
                          min="{{ \Carbon\Carbon::today()->toDateString() }}"
                          value="{{ old('expired_at', isset($item_in) && $item_in->expired_at ? \Carbon\Carbon::parse($item_in->expired_at)->format('Y-m-d') : '') }}"
                          class="form-control @error('expired_at') is-invalid @enderror"
                          x-bind:required="useExpired"
                      >
                      @error('expired_at')
                          <div class="text-danger small mt-1">{{ $message }}</div>
                      @enderror
                  </div>

                  <!-- Switch ON/OFF -->
                  <div class="form-check form-switch mt-2">
                      <input class="form-check-input" type="checkbox" id="toggleExpired" x-model="useExpired">
                      <label class="form-check-label" for="toggleExpired">
                          Gunakan tanggal kedaluwarsa
                      </label>
                  </div>
              </div>
          </div>

          <div class="row justify-content-end">
            <div class="col-sm-10">
              <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
              <a href="{{ route('super_admin.item_ins.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
