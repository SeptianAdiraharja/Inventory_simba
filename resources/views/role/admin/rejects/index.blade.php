@extends('layouts.index')

@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- 🔍 Notifikasi Pencarian --}}
  @if(isset($search) && $search)
    <div class="alert alert-warning border-0 shadow-sm rounded-3 mb-3">
      <i class="bi bi-search me-2"></i> Menampilkan hasil pencarian untuk:
      <strong>{{ $search }}</strong>
      <a href="{{ route('admin.rejects.index') }}" class="float-end text-decoration-none fw-semibold text-dark">
        <i class="bi bi-arrow-repeat me-1"></i> Tampilkan semua
      </a>
    </div>
  @endif

  {{-- 🧭 Breadcrumb Modern --}}
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3 smooth-fade">
    <div class="d-flex align-items-center flex-wrap gap-2">
      <div class="breadcrumb-icon d-flex align-items-center justify-content-center rounded-circle"
           style="width:38px;height:38px;background:#FFF3E0;color:#FF9800;">
        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
      </div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 align-items-center">
          <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold" style="color:#FF9800;">
              Dashboard
            </a>
          </li>
          <li class="breadcrumb-item active fw-semibold text-dark" aria-current="page">
            Data Barang Rusak / Reject
          </li>
        </ol>
      </nav>
    </div>
    <div class="text-muted small d-flex align-items-center">
      <i class="bi bi-calendar-check me-2"></i>{{ now()->format('d M Y, H:i') }}
    </div>
  </div>

  {{-- 📦 CARD UTAMA --}}
  <div class="card border-0 shadow-lg rounded-4 overflow-hidden bg-white smooth-card">
    {{-- HEADER --}}
    <div class="card-header text-white py-3 px-4 d-flex flex-wrap justify-content-between align-items-center"
         style="background: linear-gradient(90deg, #FF9800, #FFB74D);">
      <h5 class="mb-0 fw-semibold d-flex align-items-center">
        <i class="bi bi-tools me-2 text-white"></i> Data Barang Rusak / Reject
      </h5>

      {{-- FILTER --}}
      <form method="GET" action="{{ route('admin.rejects.index') }}" class="d-flex align-items-center gap-2">
        <label for="condition" class="form-label text-white mb-0 fw-semibold">Filter:</label>
        <select id="condition" name="condition"
                class="form-select form-select-sm border-0 shadow-sm rounded-pill px-3 fw-semibold"
                style="min-width: 200px; color:#5d4037; background-color:#FFF8E1;"
                onchange="this.form.submit()">
          <option value="all" {{ $selectedCondition === 'all' ? 'selected' : '' }}>Semua Kondisi</option>
          <option value="rusak ringan" {{ $selectedCondition === 'rusak ringan' ? 'selected' : '' }}>Rusak Ringan</option>
          <option value="rusak berat" {{ $selectedCondition === 'rusak berat' ? 'selected' : '' }}>Rusak Berat</option>
          <option value="tidak bisa digunakan" {{ $selectedCondition === 'tidak bisa digunakan' ? 'selected' : '' }}>Tidak Bisa Digunakan</option>
        </select>

        <a href="{{ route('admin.rejects.index') }}" class="btn btn-light shadow-sm border-0 rounded-circle smooth-btn"
           title="Refresh Data" style="width:38px; height:38px; color:#FF9800;">
          <i class="bi bi-arrow-clockwise fs-5"></i>
        </a>
      </form>
    </div>

    {{-- BODY --}}
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table align-middle table-hover mb-0 text-center">
          <thead style="background-color:#FFF3E0; color:#5d4037;" class="fw-semibold text-uppercase small">
            <tr>
              <th width="50">#</th>
              <th>Nama Barang</th>
              <th>Jumlah</th>
              <th>Kondisi</th>
              <th>Deskripsi</th>
              <th>Tanggal Input</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($rejects as $reject)
              <tr class="table-row-smooth">
                <td class="fw-semibold text-secondary">{{ $loop->iteration }}</td>
                <td class="fw-semibold text-dark">{{ $reject->name }}</td>
                <td class="fw-semibold text-dark">{{ $reject->quantity }}</td>
                <td>
                  @php
                    $color = match($reject->condition) {
                      'rusak berat' => '#F44336',
                      'rusak ringan' => '#FFB300',
                      'tidak bisa digunakan' => '#9E9E9E',
                      default => '#BDBDBD',
                    };
                  @endphp
                  <span class="badge rounded-pill px-3 py-2 shadow-sm text-white"
                        style="background-color: {{ $color }};">
                    {{ ucfirst($reject->condition) }}
                  </span>
                </td>
                <td class="text-start ps-3 text-muted">{{ $reject->description ?? '-' }}</td>
                <td class="text-secondary">{{ $reject->created_at->format('d M Y H:i') }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center py-5">
                  <i class="bi bi-inbox fs-3 text-muted d-block mb-2"></i>
                  <span class="text-muted">Belum ada data barang rusak.</span>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- FOOTER --}}
    @if ($rejects->count() > 0)
      <div class="card-footer bg-light text-end small text-secondary py-3 px-4 border-0">
        Total: <strong class="text-dark">{{ $rejects->count() }}</strong> data barang rusak
      </div>
    @endif
  </div>
</div>

@endsection

@push('styles')
<style>
  body {
    background-color: #fffaf4 !important;
  }

  /* Smooth Animations */
  .smooth-fade { animation: fadeInDown 0.8s ease; }
  @keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
  }

  /* Card Hover Effect */
  .smooth-card {
    transition: all 0.3s ease;
  }
  .smooth-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(255, 152, 0, 0.25);
  }

  /* Table Row Hover */
  .table-row-smooth {
    transition: all 0.25s ease;
  }
  .table-row-smooth:hover {
    background-color: #FFF8E1 !important;
    transform: scale(1.01);
  }

  /* Buttons */
  .smooth-btn {
    transition: all 0.3s ease-in-out;
  }
  .smooth-btn:hover {
    transform: scale(1.1);
    background-color: #FF9800 !important;
    color: white !important;
    box-shadow: 0 4px 10px rgba(255, 152, 0, 0.3);
  }

  /* Select Box */
  .form-select:focus {
    border-color: #FF9800;
    box-shadow: 0 0 0 0.2rem rgba(255, 152, 0, 0.25);
  }

  /* Table Header */
  thead th {
    font-weight: 600;
    letter-spacing: 0.3px;
  }

  /* Badge Style */
  .badge {
    font-size: 0.85rem;
    font-weight: 600;
  }

  /* Responsiveness */
  @media (max-width: 768px) {
    .breadcrumb-extra { display: none; }
    .card-header h5 { font-size: 1rem; }
    .table { font-size: 0.9rem; }
  }
</style>
@endpush
