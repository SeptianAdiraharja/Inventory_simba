@extends('layouts.index')
@section('content')

<!-- === STATISTIK TRANSAKSI === -->
<div class="col-xl-12 mb-5">
  <div class="card shadow-sm border-0 animate__animated animate__fadeInDown">
    <div class="card-header d-flex align-items-center justify-content-between bg-light">
      <h5 class="card-title m-0 text-primary">
        <i class="ri-bar-chart-grouped-line me-2"></i>Ringkasan Transaksi
      </h5>
      <button class="btn btn-sm btn-outline-primary" onclick="location.reload()">
        <i class="ri-refresh-line me-1"></i> Muat Ulang
      </button>
    </div>

    <div class="row g-4 justify-content-center text-center mt-3 px-3">
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
<!-- /STATISTIK TRANSAKSI -->


<!-- === DAFTAR TERBARU === -->
<div class="col-xl-12 mt-5">
  <div class="card border-0 shadow-sm animate__animated animate__fadeInUp">
    <div class="card-body row">
      <!-- Barang Keluar -->
      <div class="col-md-6 pe-md-4 border-end">
        <x-dashboard-list-card
          title="📦 Barang Keluar Terbaru"
          :items="$latestBarangKeluar"
          type="barang_keluar"
        />
      </div>

      <!-- Permintaan -->
      <div class="col-md-6 ps-md-4">
        <x-dashboard-list-card
          title="🧾 Permintaan Terbaru"
          :items="$latestRequest"
          type="request"
        />
      </div>
    </div>
  </div>
</div>
<!-- /DAFTAR TERBARU -->


<!-- === 5 PERMINTAAN TERBANYAK === -->
<div class="col-12 mt-5">
  <div class="card border-0 shadow-sm animate__animated animate__fadeInUp animate__delay-1s">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0 text-primary">
        <i class="ri-award-line me-2"></i> 5 User dan Guest dengan Permintaan Terbanyak
      </h5>
      <span class="text-muted small">Data diperbarui otomatis</span>
    </div>

    <div class="card-body">
      @forelse($topRequesters as $index => $r)
        <div class="mb-3">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              @if($r['role'] === 'Guest')
                <i class="ri-user-smile-line text-warning me-2 fs-5" title="Guest"></i>
              @else
                <i class="ri-user-2-fill text-success me-2 fs-5" title="Pegawai"></i>
              @endif
              <strong>{{ $index + 1 }}. {{ $r['name'] }}</strong>
              <span class="text-muted small d-block">{{ $r['email'] }}</span>
            </div>
            <span class="badge {{ $r['role'] === 'Guest' ? 'bg-warning text-dark' : 'bg-success' }}">
              {{ $r['role'] }}
            </span>
          </div>
          <!-- Progress bar -->
          <div class="progress mt-2" style="height: 8px;">
            <div
              class="progress-bar {{ $r['role'] === 'Guest' ? 'bg-warning' : 'bg-success' }}"
              role="progressbar"
              style="width: {{ ($r['total_requests'] / max($topRequesters[0]['total_requests'], 1)) * 100 }}%"
              aria-valuenow="{{ $r['total_requests'] }}"
              aria-valuemin="0"
              aria-valuemax="{{ $topRequesters[0]['total_requests'] }}"
            ></div>
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
<!-- /5 PERMINTAAN TERBANYAK -->


<!-- === IKHTISAR LALU LINTAS === -->
<div class="col-xl-12 mt-5">
  <div class="card shadow-sm border-0 animate__animated animate__fadeInUp animate__delay-2s">
    <div class="card-header bg-light d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
      <div>
        <h5 class="card-title m-0 text-primary">
          <i class="ri-line-chart-line me-2"></i>Ikhtisar Lalu Lintas Barang
        </h5>
        <p class="small text-muted mb-0">Perbandingan barang masuk dan keluar berdasarkan waktu</p>
      </div>
      <div class="btn-group btn-group-sm">
        <button class="btn btn-outline-primary hover-scale" onclick="updateChart('week')">1 Minggu</button>
        <button class="btn btn-outline-primary hover-scale" onclick="updateChart('month')">1 Bulan</button>
        <button class="btn btn-outline-primary hover-scale" onclick="updateChart('year')">1 Tahun</button>
      </div>
    </div>
    <div class="card-body">
      <div style="width:100%; height:400px;">
        <canvas id="trafficChart"></canvas>
      </div>
    </div>
  </div>
</div>
<!-- /IKHTISAR LALU LINTAS -->

@endsection


@section('scripts')
<style>
.hover-scale {
  transition: transform 0.3s ease, background 0.3s ease;
}
.hover-scale:hover {
  transform: scale(1.08);
}

.progress-bar {
  transition: width 0.5s ease;
}

.card-title i {
  vertical-align: middle;
}

.table-hover tbody tr:hover {
  background-color: #f8f9fa;
}
</style>

<script src="{{ asset('js/dashboard.js') }}"></script>
@endsection
