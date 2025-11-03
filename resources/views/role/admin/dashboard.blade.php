@extends('layouts.index')
@section('content')

<div class="container-fluid py-4">

  <!-- === BREADCRUMB ELEGAN === -->
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap align-items-center justify-content-between animate__animated animate__fadeInDown">
    <h5 class="fw-bold text-dark mb-0 d-flex align-items-center">
      <i class="ri-home-4-line text-primary me-2 fs-5"></i> Dashboard Admin
    </h5>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
          <a href="{{ route('pegawai.dashboard') }}" class="text-decoration-none text-primary fw-medium">
            Dashboard
          </a>
        </li>
        <li class="breadcrumb-item active text-secondary fw-normal" aria-current="page">
          Statistika Permintaan Barang
        </li>
      </ol>
    </nav>
  </div>

  <!-- === RINGKASAN TRANSAKSI === -->
  <div class="mb-4">
    <div class="card border-0 shadow-lg rounded-4 bg-white animate__animated animate__fadeInDown">
      <div class="card-header bg-white border-0 d-flex align-items-center justify-content-between px-4 py-3">
        <h5 class="m-0 text-primary fw-bold d-flex align-items-center">
          <i class="ri-bar-chart-grouped-line me-2 fs-4"></i> Ringkasan Transaksi
        </h5>
        <button class="btn btn-sm btn-light border-primary text-primary fw-semibold rounded-pill shadow-sm px-3 hover-glow" onclick="location.reload()">
          <i class="ri-refresh-line me-1"></i> Muat Ulang
        </button>
      </div>

      <div class="row g-4 justify-content-center px-4 pb-4">
        <x-dashboard-card
          title="Barang Keluar"
          :value="$totalBarangKeluar"
          icon="ri-pie-chart-2-line"
          color="primary"
          link="{{ route('admin.itemout.index') }}"
        />

        <x-dashboard-card
          title="Tamu Terdaftar"
          :value="$totalGuest"
          icon="ri-group-line"
          color="warning"
          link="{{ route('admin.guests.index') }}"
        />

        <x-dashboard-card
          title="Total Permintaan"
          :value="$totalRequest"
          icon="ri-price-tag-3-line"
          color="danger"
          link="{{ route('admin.request') }}"
        />
      </div>
    </div>
  </div>

  <!-- === DAFTAR TERBARU === -->
  <div class="mb-4">
    <div class="card border-0 shadow-lg rounded-4 bg-white animate__animated animate__fadeInUp">
      <div class="card-body row px-4 py-3">
        <div class="col-md-6 border-end-md border-light">
          <x-dashboard-list-card
            title="📦 Barang Keluar Terbaru"
            :items="$latestBarangKeluar"
            type="barang_keluar"
          />
        </div>
        <div class="col-md-6">
          <x-dashboard-list-card
            title="🧾 Permintaan Terbaru"
            :items="$latestRequest"
            type="request"
          />
        </div>
      </div>
    </div>
  </div>

  <!-- === 5 PERMINTAAN TERBANYAK === -->
  <div class="mb-4">
    <div class="card border-0 shadow-lg rounded-4 bg-white animate__animated animate__fadeInUp animate__delay-1s">
      <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center px-4 py-3">
        <h5 class="m-0 text-primary fw-bold">
          <i class="ri-award-line me-2"></i>Top 5 User & Guest dengan Permintaan Terbanyak
        </h5>
        <span class="text-muted small">Data diperbarui otomatis</span>
      </div>

      <div class="card-body px-4 pb-4">
        @forelse($topRequesters as $index => $r)
          <div class="mb-4 rounded-3 p-3 hover-card">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                @if($r['role'] === 'Guest')
                  <i class="ri-user-smile-line text-warning me-2 fs-5" title="Guest"></i>
                @else
                  <i class="ri-user-2-fill text-success me-2 fs-5" title="Pegawai"></i>
                @endif
                <strong>{{ $index + 1 }}. {{ $r['name'] }}</strong>
                <div class="text-muted small">{{ $r['email'] }}</div>
              </div>
              <span class="badge {{ $r['role'] === 'Guest' ? 'bg-warning text-dark' : 'bg-success' }}">
                {{ $r['role'] }}
              </span>
            </div>
            <div class="progress mt-2 rounded-pill" style="height: 10px;">
              <div
                class="progress-bar progress-bar-striped {{ $r['role'] === 'Guest' ? 'bg-warning' : 'bg-success' }}"
                role="progressbar"
                style="width: {{ ($r['total_requests'] / max($topRequesters[0]['total_requests'], 1)) * 100 }}%">
              </div>
            </div>
            <small class="text-muted">{{ $r['total_requests'] }} permintaan</small>
          </div>
        @empty
          <p class="text-center text-muted py-3 mb-0">
            <i class="ri-information-line me-1"></i> Belum ada data permintaan.
          </p>
        @endforelse
      </div>
    </div>
  </div>

  <!-- === IKHTISAR LALU LINTAS === -->
  <div class="mb-5">
    <div class="card border-0 shadow-lg rounded-4 bg-white animate__animated animate__fadeInUp animate__delay-2s">
      <div class="card-header bg-white border-0 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center px-4 py-3">
        <div>
          <h5 class="m-0 text-primary fw-bold">
            <i class="ri-line-chart-line me-2"></i>Ikhtisar Lalu Lintas Barang
          </h5>
          <p class="small text-muted mb-0">Perbandingan barang masuk dan keluar berdasarkan waktu</p>
        </div>
        <div class="btn-group btn-group-sm mt-2 mt-md-0">
          <button class="btn btn-outline-primary hover-scale active" onclick="updateChart('week')">1 Minggu</button>
          <button class="btn btn-outline-primary hover-scale" onclick="updateChart('month')">1 Bulan</button>
          <button class="btn btn-outline-primary hover-scale" onclick="updateChart('year')">1 Tahun</button>
        </div>
      </div>
      <div class="card-body px-4 pb-4">
        <div style="width:100%; height:400px;">
          <canvas id="trafficChart"></canvas>
        </div>
      </div>
    </div>
  </div>

</div>

@endsection

@section('scripts')
<style>
/* ======== GLOBAL STYLE ======== */
body {
  background: #f5f7fb !important;
}

/* === BREADCRUMB STYLE === */
.breadcrumb {
  background: transparent !important;
  font-size: 0.95rem;
  margin-bottom: 0;
}

.breadcrumb-item + .breadcrumb-item::before {
  content: "/";
  color: #adb5bd;
  margin: 0 0.5rem;
  font-weight: 400;
}

.breadcrumb-item a {
  color: #0056d6;
  transition: all 0.2s ease;
}

.breadcrumb-item a:hover {
  color: #003e9f;
  text-decoration: underline;
}

/* Container styling */
.bg-white.shadow-sm.rounded-4 {
  border: 1px solid #f1f3f5;
  transition: all 0.3s ease;
}

.bg-white.shadow-sm.rounded-4:hover {
  box-shadow: 0 3px 12px rgba(0, 0, 0, 0.05);
  transform: translateY(-2px);
}

/* Hover scaling */
.hover-scale {
  transition: all 0.3s ease-in-out;
}
.hover-scale:hover {
  transform: scale(1.05);
  background-color: #e9f2ff;
}

/* Button glow */
.hover-glow:hover {
  box-shadow: 0 0 10px rgba(59, 130, 246, 0.4);
}

/* Card hover effect */
.hover-card {
  transition: all 0.3s ease;
  background-color: #fafbff;
}
.hover-card:hover {
  background-color: #f1f5ff;
  transform: translateY(-3px);
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
}

/* Smooth progress animation */
.progress-bar {
  transition: width 0.6s ease-in-out;
}

/* Chart container */
canvas {
  transition: all 0.5s ease;
}

/* Card transitions */
.card {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.card:hover {
  transform: translateY(-2px);
}

/* Header style */
.card-header h5 i {
  vertical-align: middle;
}
</style>

<script src="{{ asset('js/dashboard.js') }}"></script>
@endsection
