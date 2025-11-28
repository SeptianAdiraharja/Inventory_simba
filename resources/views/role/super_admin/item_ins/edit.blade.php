@extends('layouts.index')
@section('title', 'Edit Barang Masuk')
@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- 🧭 BREADCRUMB --}}
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap align-items-center justify-content-between smooth-fade">
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <i class="bi bi-pencil-square fs-5" style="color:#FF9800;"></i>
      <a href="{{ route('dashboard') }}" class="breadcrumb-link fw-semibold text-decoration-none" style="color:#FF9800;">
        Dashboard
      </a>
      <span class="text-muted">/</span>
      <a href="{{ route('super_admin.item_ins.index') }}" class="fw-semibold text-decoration-none" style="color:#FFB300;">
        Barang Masuk
      </a>
      <span class="text-muted">/</span>
      <span class="fw-semibold text-dark">Edit</span>
    </div>
  </div>

  {{-- ✏️ FORM EDIT --}}
  <div class="card border-0 shadow-sm rounded-4 smooth-fade">
    <div class="card-header bg-white border-0 d-flex justify-content-between flex-wrap align-items-center">
      <h4 class="fw-bold mb-0" style="color:#FF9800;">
        <i class="ri-edit-line me-2"></i> Edit Barang Masuk
      </h4>
      <small class="text-warning fw-semibold">Perbarui data sesuai kebutuhan</small>
    </div>

    <div class="card-body bg-white p-4 rounded-bottom-4">
      <form action="{{ route('super_admin.item_ins.update', $item_in->id) }}" 
      method="POST"
      x-data="{
        useExpired: {{ $item_in->expired_at ? 'true' : 'false' }},
        useTanggalMasuk: {{ $item_in->tanggal_masuk ? 'true' : 'false' }}
      }">
        @csrf
        @method('PUT')

        {{-- Item --}}
        <div class="mb-4">
          <label class="form-label fw-semibold text-dark">Barang</label>
          <select name="item_id" class="form-select shadow-sm border-0"
                  style="border-left:4px solid #FF9800 !important;" required>
            @foreach($items as $item)
              <option value="{{ $item->id }}" {{ $item_in->item_id == $item->id ? 'selected' : '' }}>
                {{ $item->name }}
              </option>
            @endforeach
          </select>
        </div>

        {{-- Supplier --}}
        <div class="mb-4">
          <label class="form-label fw-semibold text-dark">Supplier</label>
          <select name="supplier_id" class="form-select shadow-sm border-0"
                  style="border-left:4px solid #FF9800 !important;" required>
            @foreach($suppliers as $supplier)
              <option value="{{ $supplier->id }}" {{ $item_in->supplier_id == $supplier->id ? 'selected' : '' }}>
                {{ $supplier->name }}
              </option>
            @endforeach
          </select>
        </div>

        {{-- Jumlah --}}
        <div class="mb-4">
          <label class="form-label fw-semibold text-dark">Jumlah</label>
          <input type="number" name="quantity" value="{{ $item_in->quantity }}"
                 class="form-control shadow-sm border-0"
                 style="border-left:4px solid #FF9800 !important;" required>
        </div>

         {{-- TanggalMasuk --}}
        <div class="mb-4">
          <label class="form-label fw-semibold text-dark">Tanggal Masuk</label>
          <div x-show="useTanggalMasuk" x-transition>
            <input type="date" name="tanggal_masuk" id="tanggal_masuk"
                   value="{{ $item_in->tanggal_masuk ? $item_in->tanggal_masuk->format('Y-m-d') : '' }}"
                   class="form-control shadow-sm border-0"
                   style="border-left:4px solid #FF9800 !important;"
                   x-bind:required="useTanggalMasuk" x-bind:disabled="!useTanggalMasuk">
          </div>
          <div class="form-check form-switch mt-2">
            <input class="form-check-input" type="checkbox" id="tonggleTanggalMasuk" x-model="useTanggalMasuk">
            <label class="form-check-label text-muted" for="tonggleTanggalMasuk">Gunakan tanggal Masuk</label>
          </div>
        </div>

        {{-- Expired --}}
        <div class="mb-4">
          <label class="form-label fw-semibold text-dark">Tanggal Kedaluwarsa</label>
          <div x-show="useExpired" x-transition>
            <input type="date" name="expired_at" id="expired_at"
                   min="{{ \Carbon\Carbon::today()->toDateString() }}"
                   value="{{ $item_in->expired_at ? $item_in->expired_at->format('Y-m-d') : '' }}"
                   class="form-control shadow-sm border-0"
                   style="border-left:4px solid #FF9800 !important;"
                   x-bind:required="useExpired" x-bind:disabled="!useExpired">
          </div>
          <div class="form-check form-switch mt-2">
            <input class="form-check-input" type="checkbox" id="toggleExpired" x-model="useExpired">
            <label class="form-check-label text-muted" for="toggleExpired">Gunakan tanggal kedaluwarsa</label>
          </div>
        </div>

        {{-- Tombol --}}
        <div class="d-flex justify-content-end gap-2 mt-4">
          <button type="submit"
                  class="btn btn-sm rounded-pill px-4 shadow-sm hover-glow"
                  style="background-color:#FF9800;color:white;">
            <i class="ri-save-3-line me-1"></i> Perbarui
          </button>
          <a href="{{ route('super_admin.item_ins.index') }}"
             class="btn btn-sm rounded-pill px-4"
             style="background-color:#FFF3E0;color:#FF9800;border:1px solid #FFB74D;">
            <i class="ri-arrow-go-back-line me-1"></i> Kembali
          </a>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- 🌈 STYLE --}}
<style>
.smooth-fade { animation: fadeIn 0.6s ease-in-out; }
@keyframes fadeIn { from {opacity:0;transform:translateY(10px);} to {opacity:1;transform:translateY(0);} }
.form-control:focus, .form-select:focus {
  border-color: #FF9800 !important;
  box-shadow: 0 0 0 3px rgba(255,152,0,0.25);
}
.hover-glow:hover {
  background-color: #FFC107 !important;
  box-shadow: 0 0 12px rgba(255,152,0,0.4);
}
.breadcrumb-link::after {
  content: ''; position: absolute; bottom: -2px; left: 0;
  width: 0; height: 2px; background: #FF9800; transition: width 0.25s ease;
}
.breadcrumb-link:hover::after { width: 100%; }
</style>
@endsection
