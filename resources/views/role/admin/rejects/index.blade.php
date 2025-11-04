@extends('layouts.index')

@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">


  @if(isset($search) && $search)
      <div class="alert alert-info">
          Menampilkan hasil pencarian untuk: "<strong>{{ $search }}</strong>"
          <a href="{{ route('admin.rejects.index') }}" class="float-end">Tampilkan semua</a>
      </div>
  @endif

  {{-- =============================== --}}
  {{-- 🧭 BREADCRUMB + HEADER --}}
  {{-- =============================== --}}
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3 animate__animated animate__fadeInDown smooth-fade">
    <div class="d-flex align-items-center flex-wrap gap-2">
      <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-2 d-flex align-items-center justify-content-center"
           style="width:38px;height:38px;">
        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
      </div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}" class="fw-semibold text-warning text-decoration-none">
              Dashboard
            </a>
          </li>
          <li class="breadcrumb-item active fw-semibold text-dark" aria-current="page">
            Data Barang Rusak / Reject
          </li>
        </ol>
      </nav>
    </div>
    <div class="d-flex align-items-center text-muted small">
      <i class="bi bi-calendar-check me-2"></i>
      <span>{{ now()->format('d M Y, H:i') }}</span>
    </div>
  </div>

  {{-- =============================== --}}
  {{-- 📦 CARD UTAMA --}}
  {{-- =============================== --}}
  <div class="card border-0 shadow-lg rounded-4 overflow-hidden bg-white">

    {{-- 🔹 HEADER --}}
    <div class="card-header bg-gradient d-flex flex-wrap justify-content-between align-items-center py-3 px-4 gap-3"
         style="background: linear-gradient(90deg, #f8b400, #ffd369);">
      <h5 class="mb-0 fw-semibold text-dark d-flex align-items-center">
        <i class="ri-close-circle-line me-2 fs-4 text-danger"></i>Data Barang Rusak / Reject
      </h5>

      <form method="GET" action="{{ route('admin.rejects.index') }}" class="d-flex align-items-center gap-2">
        <label for="condition" class="form-label text-dark mb-0 fw-semibold">Filter:</label>
        <select id="condition" name="condition"
                class="form-select form-select-sm border-0 shadow-sm text-dark rounded-pill px-3"
                style="min-width: 200px; background-color: #fff;"
                onchange="this.form.submit()">
          <option value="all" {{ $selectedCondition === 'all' ? 'selected' : '' }}>Semua Kondisi</option>
          <option value="rusak ringan" {{ $selectedCondition === 'rusak ringan' ? 'selected' : '' }}>Rusak Ringan</option>
          <option value="rusak berat" {{ $selectedCondition === 'rusak berat' ? 'selected' : '' }}>Rusak Berat</option>
          <option value="tidak bisa digunakan" {{ $selectedCondition === 'tidak bisa digunakan' ? 'selected' : '' }}>Tidak Bisa Digunakan</option>
        </select>

        <!-- 🔁 TOMBOL REFRESH -->
        <a href="{{ route('admin.rejects.index') }}" class="btn btn-light border-0 shadow-sm rounded-circle"
           title="Refresh Data" style="width: 36px; height: 36px;">
          <i class="ri-refresh-line text-primary fs-5"></i>
        </a>
      </form>
    </div>

    {{-- 🔹 BODY --}}
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table align-middle table-hover mb-0">
          <thead class="text-center text-uppercase small fw-semibold"
                 style="background-color: #f1f5f9; color: #444;">
            <tr>
              <th width="50">#</th>
              <th>Nama Barang</th>
              <th>Asal Item</th>
              <th>Jumlah</th>
              <th>Kondisi</th>
              <th>Deskripsi</th>
              <th>Tanggal Input</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($rejects as $reject)
            <tr class="text-center border-bottom">
              <td class="text-secondary">{{ $loop->iteration }}</td>
              <td class="fw-semibold text-dark">{{ $reject->name }}</td>
              <td class="text-secondary">{{ $reject->item->name ?? '-' }}</td>
              <td class="fw-semibold text-dark">{{ $reject->quantity }}</td>
              <td>
                @php
                  $color = match($reject->condition) {
                    'rusak berat' => 'danger',
                    'rusak ringan' => 'warning',
                    'tidak bisa digunakan' => 'secondary',
                    default => 'light',
                  };
                @endphp
                <span class="badge bg-{{ $color }} text-capitalize px-3 py-2 shadow-sm">
                  {{ $reject->condition }}
                </span>
              </td>
              <td class="text-muted text-start ps-3">{{ $reject->description ?? '-' }}</td>
              <td class="text-secondary">{{ $reject->created_at->format('d M Y H:i') }}</td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center py-5">
                <i class="ri-inbox-2-line fs-3 text-muted d-block mb-2"></i>
                <span class="text-muted">Belum ada data barang rusak.</span>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- 🔹 FOOTER --}}
    @if ($rejects->count() > 0)
    <div class="card-footer bg-light text-end small text-secondary py-2 px-4 border-0">
      Total: <strong class="text-dark">{{ $rejects->count() }}</strong> data barang rusak
    </div>
    @endif
  </div>
</div>
@endsection

@push('styles')
<style>
.smooth-fade {
  animation: fadeDown 0.7s ease-in-out;
}
@keyframes fadeDown {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}
.table-hover tbody tr:hover {
  background-color: #fff9e6;
  transition: 0.2s;
}
.form-select:focus {
  box-shadow: 0 0 0 0.2rem rgba(255,193,7,0.25) !important;
}
</style>
@endpush
