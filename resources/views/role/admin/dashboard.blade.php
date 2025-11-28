@extends('layouts.index')
@section('title', 'Dashboard Admin')
@section('content')

<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- ==================== BREADCRUMB SECTION ==================== --}}
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3 smooth-fade">
    <div class="d-flex align-items-center flex-wrap gap-2">
      {{-- Breadcrumb Icon --}}
      <div class="breadcrumb-icon d-flex align-items-center justify-content-center rounded-circle"
           style="width:38px;height:38px;background:#FFF3E0;color:#FF9800;">
        <i class="bi bi-house-door-fill fs-5"></i>
      </div>

      {{-- Breadcrumb Navigation --}}
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 align-items-center">
          <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold" style="color:#FF9800;">
              Dashboard
            </a>
          </li>
          <li class="breadcrumb-item active fw-semibold text-dark" aria-current="page">
            Statistika Permintaan Barang
          </li>
        </ol>
      </nav>
    </div>

    {{-- Breadcrumb Extra Info --}}
    <div class="breadcrumb-extra text-end">
      <small class="text-muted">
        <i class="bi bi-calendar-check me-1"></i>{{ now()->format('d M Y, H:i') }}
      </small>
    </div>
  </div>

  {{-- ==================== SUMMARY CARDS SECTION ==================== --}}
  <div class="mb-4">
    <div class="card border-0 shadow-sm rounded-4 bg-white">
      <div class="card-header bg-white border-0 d-flex align-items-center justify-content-between px-4 py-3">
        <h5 class="m-0 fw-bold d-flex align-items-center" style="color:#FF9800;">
          <i class="ri-bar-chart-grouped-line me-2 fs-4"></i> Ringkasan Transaksi
        </h5>
        <button class="btn btn-sm rounded-pill shadow-sm px-3 fw-semibold hover-glow"
                style="background-color:#FF9800;color:white;" onclick="location.reload()">
          <i class="ri-refresh-line me-1"></i> Muat Ulang
        </button>
      </div>

      {{-- Summary Cards Container --}}
      <div class="row g-4 justify-content-center px-4 pb-4">
        {{-- Barang Keluar Card --}}
        <x-dashboard-card
          title="Barang Keluar"
          :value="$totalBarangKeluar"
          icon="ri-pie-chart-2-line"
          color="warning"
          link="{{ route('admin.itemout.index') }}"
        />

        {{-- Tamu Terdaftar Card --}}
        <x-dashboard-card
          title="Tamu Terdaftar"
          :value="$totalGuest"
          icon="ri-group-line"
          color="info"
          link="{{ route('admin.guests.index') }}"
        />

        {{-- Total Permintaan Card --}}
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

  {{-- ==================== LATEST ACTIVITY SECTION ==================== --}}
  <div class="mb-4">
    <div class="card border-0 shadow-sm rounded-4 bg-white">
      <div class="card-body row px-4 py-3">
        {{-- Latest Barang Keluar --}}
        <div class="col-md-6 border-end-md border-light">
          <x-dashboard-list-card
            title="📦 Barang Keluar Terbaru"
            :items="$latestBarangKeluar"
            type="barang_keluar"
          />
        </div>

        {{-- Latest Requests --}}
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

  {{-- ==================== TOP REQUESTERS SECTION ==================== --}}
  <div class="mb-4">
    <div class="card border-0 shadow-sm rounded-4 bg-white">
      <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center px-4 py-3">
        <h5 class="m-0 fw-bold" style="color:#FF9800;">
          <i class="ri-award-line me-2"></i>Top 5 User & Guest dengan Permintaan Terbanyak
        </h5>
        <span class="text-muted small">Data diperbarui otomatis</span>
      </div>

      {{-- Top Requesters List --}}
      <div class="card-body px-4 pb-4">
        @forelse($topRequesters as $index => $requester)
          <div class="mb-4 rounded-3 p-3 hover-card">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                {{-- User Icon Based on Role --}}
                @if($requester['role'] === 'Guest')
                  <i class="ri-user-smile-line text-warning me-2 fs-5"></i>
                @else
                  <i class="ri-user-2-fill text-success me-2 fs-5"></i>
                @endif

                <strong>{{ $index + 1 }}. {{ $requester['name'] }}</strong>
                <div class="text-muted small">{{ $requester['email'] }}</div>
              </div>

              {{-- Role Badge --}}
              <span class="badge rounded-pill px-3 py-2" style="background:#FF9800;color:white;">
                {{ $requester['role'] }}
              </span>
            </div>

            {{-- Progress Bar --}}
            <div class="progress mt-2 rounded-pill" style="height:10px;background:#FFE0B2;">
              <div class="progress-bar progress-bar-striped"
                   role="progressbar"
                   style="width: {{ ($requester['total_requests'] / max($topRequesters[0]['total_requests'], 1)) * 100 }}%;background:#FF9800;">
              </div>
            </div>

            {{-- Request Count --}}
            <small class="text-muted">{{ $requester['total_requests'] }} permintaan</small>
          </div>
        @empty
          {{-- Empty State --}}
          <p class="text-center text-muted py-3 mb-0">
            <i class="ri-information-line me-1"></i> Belum ada data permintaan.
          </p>
        @endforelse
      </div>
    </div>
  </div>

  {{-- ==================== TRAFFIC OVERVIEW SECTION ==================== --}}
  <div class="mb-5">
    <div class="card border-0 shadow-sm rounded-4 bg-white">
      <div class="card-header bg-white border-0 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center px-4 py-3">
        <div>
          <h5 class="m-0 fw-bold" style="color:#FF9800;">
            <i class="ri-line-chart-line me-2"></i>Ikhtisar Lalu Lintas Barang
          </h5>
          <p class="small text-muted mb-0">Perbandingan barang masuk dan keluar berdasarkan waktu</p>
        </div>

        {{-- Time Range Buttons --}}
        <div class="btn-group btn-group-sm mt-2 mt-md-0">
          <button class="btn btn-outline-warning hover-scale active" onclick="updateChart('week')">1 Minggu</button>
          <button class="btn btn-outline-warning hover-scale" onclick="updateChart('month')">1 Bulan</button>
          <button class="btn btn-outline-warning hover-scale" onclick="updateChart('year')">1 Tahun</button>
        </div>
      </div>

      {{-- Chart Container --}}
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
{{-- ==================== CUSTOM STYLES ==================== --}}
<style>
/**
 * GLOBAL STYLES
 */
body {
  background: #fffaf4 !important;
}

/**
 * BREADCRUMB STYLES
 */
.breadcrumb-item + .breadcrumb-item::before {
  content: "›";
  color: #ffb74d;
  margin: 0 6px;
}

.breadcrumb-icon:hover {
  transform: scale(1.1);
  background-color: #ffecb3;
}

/**
 * ANIMATIONS
 */
.smooth-fade {
  animation: smoothFade 0.8s ease;
}

@keyframes smoothFade {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/**
 * INTERACTION EFFECTS
 */
.hover-scale {
  transition: all 0.3s ease-in-out;
}

.hover-scale:hover {
  transform: scale(1.05);
  background-color: #fff3e0;
}

.hover-glow:hover {
  box-shadow: 0 0 12px rgba(255, 152, 0, 0.4);
}

/**
 * CARD STYLES
 */
.hover-card {
  transition: all 0.3s ease;
  background-color: #fffdf9;
  border-left: 4px solid #ffcc80;
}

.hover-card:hover {
  background-color: #fff8e1;
  transform: translateY(-3px);
  box-shadow: 0 4px 12px rgba(255, 152, 0, 0.15);
}

/**
 * PROGRESS BAR
 */
.progress-bar {
  transition: width 0.6s ease-in-out;
}

/**
 * CHART BUTTONS
 */
.btn-outline-warning.active {
  background-color: #FF9800 !important;
  color: white !important;
  border-color: #FF9800 !important;
}

/**
 * RESPONSIVE STYLES
 */
@media (max-width: 768px) {
  .breadcrumb-extra {
    display: none;
  }

  h5 {
    font-size: 1rem;
  }
}
</style>

{{-- ==================== JAVASCRIPT ==================== --}}
<script src="{{ asset('js/dashboard.js') }}"></script>
@endsection