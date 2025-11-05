@extends('layouts.index')

@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- ======================== --}}
  {{-- 🧭 MODERN BREADCRUMB --}}
  {{-- ======================== --}}
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3 animate__animated animate__fadeInDown smooth-fade">
    {{-- Left Section --}}
    <div class="d-flex align-items-center flex-wrap gap-2">
      <div class="breadcrumb-icon d-flex align-items-center justify-content-center rounded-circle"
           style="width:40px; height:40px; background-color:#FFF59D; color:#FF9800;">
        <i class="bi bi-speedometer2 fs-5"></i>
      </div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 align-items-center">
          <li class="breadcrumb-item">
            <a href="{{ route('pegawai.dashboard') }}" class="text-decoration-none fw-semibold" style="color:#FF9800;">
              Dashboard
            </a>
          </li>
          <li class="breadcrumb-item active fw-semibold text-dark" aria-current="page">
            Statistik Permintaan Barang
          </li>
        </ol>
      </nav>
    </div>

    {{-- Right Section (Date) --}}
    <div class="text-end small text-muted">
      <i class="bi bi-calendar-check me-1"></i>{{ now()->format('d M Y, H:i') }}
    </div>
  </div>

  {{-- ======================== --}}
  {{-- 📊 STATISTIK PERMINTAAN --}}
  {{-- ======================== --}}
  <div class="row gy-4 mb-4">
    <div class="col-xl-12">
      <div class="card border-0 shadow-lg rounded-4 animate__animated animate__fadeInUp smooth-fade">
        <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between flex-wrap align-items-center gap-3">
          <h5 class="fw-semibold mb-0 d-flex align-items-center" style="color:#FF9800;">
            <i class="bi bi-graph-up-arrow me-2"></i>Statistik Permintaan Barang
          </h5>

          {{-- Filter --}}
          <form method="GET" action="{{ route('pegawai.dashboard') }}" class="d-flex flex-wrap gap-2">
            <input type="hidden" name="range" id="rangeInput">
            <button type="submit" onclick="setRange('week')"
              class="btn btn-sm {{ $range == 'week' ? 'btn-orange-active' : 'btn-outline-orange' }}">
              1 Minggu
            </button>
            <button type="submit" onclick="setRange('month')"
              class="btn btn-sm {{ $range == 'month' ? 'btn-orange-active' : 'btn-outline-orange' }}">
              1 Bulan
            </button>
            <button type="submit" onclick="setRange('year')"
              class="btn btn-sm {{ $range == 'year' ? 'btn-orange-active' : 'btn-outline-orange' }}">
              1 Tahun
            </button>
          </form>
        </div>

        <div class="card-body" style="height: 360px;">
          <canvas id="weeklyOverviewChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  {{-- ======================== --}}
  {{-- 🧍 AKTIVITAS PENGGUNA --}}
  {{-- ======================== --}}
  <div class="row gy-4">
    <div class="col-12">
      <div class="card shadow-sm border-0 rounded-4 overflow-hidden animate__animated animate__fadeInUp smooth-fade">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
          <h5 class="fw-semibold mb-0 d-flex align-items-center" style="color:#FF9800;">
            <i class="bi bi-person-lines-fill me-2" style="color:#FFC107;"></i> Aktivitas Permintaan Pengguna
          </h5>
        </div>

        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead style="background-color:#FFF59D;">
              <tr class="text-secondary">
                <th>User</th>
                <th>Email</th>
                <th>Role</th>
                <th class="text-center">Total Request</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($users as $user)
              <tr class="hover-row">
                <td>
                  <div class="d-flex align-items-center">
                    <img src="{{ asset('assets/img/avatars/1.png') }}" alt="Avatar"
                         class="rounded-circle me-3 border border-light shadow-sm" width="44" height="44">
                    <div>
                      <h6 class="fw-semibold mb-0">{{ $user->name }}</h6>
                      <small class="text-muted">#{{ $user->id }}</small>
                    </div>
                  </div>
                </td>
                <td>{{ $user->email }}</td>
                <td>{{ ucfirst($user->role) }}</td>
                <td class="text-center">
                  <span class="badge fw-semibold px-3 py-2" style="background-color:#FFEB3B; color:#FF9800;">
                    {{ $user->total_request }}
                  </span>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="4" class="text-center text-muted py-4">
                  <i class="bi bi-info-circle me-1"></i> Belum ada data pengguna dengan request.
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

</div>

{{-- ======================== --}}
{{-- 📈 CHART.JS --}}
{{-- ======================== --}}
<script>
  const ctx = document.getElementById('weeklyOverviewChart');

  function setRange(value) {
    document.getElementById('rangeInput').value = value;
  }

  new Chart(ctx, {
    type: 'line',
    data: {
      labels: @json($history['labels']),
      datasets: [{
        label: 'Total Request',
        data: @json($history['data']),
        borderColor: '#FF9800',
        backgroundColor: 'rgba(255, 193, 7, 0.25)',
        fill: true,
        tension: 0.4,
        pointBackgroundColor: '#fff',
        pointBorderColor: '#FF9800',
        pointRadius: 4,
        pointHoverRadius: 6,
        borderWidth: 2,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        title: {
          display: true,
          text: 'Grafik Permintaan Barang',
          color: '#FF9800',
          font: { size: 16, weight: 'bold' },
          padding: { top: 10, bottom: 20 }
        },
        tooltip: {
          backgroundColor: '#fff',
          titleColor: '#333',
          bodyColor: '#333',
          borderColor: '#FFC107',
          borderWidth: 1,
          callbacks: {
            label: (context) => `${context.formattedValue} request`
          }
        }
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { color: '#6c757d', font: { size: 12 } }
        },
        y: {
          beginAtZero: true,
          grid: { color: '#FFF9C4' },
          ticks: {
            stepSize: 10,
            color: '#6c757d',
            font: { size: 12 }
          }
        }
      }
    }
  });
</script>

{{-- ======================== --}}
{{-- 🌈 STYLE SMOOTH --}}
{{-- ======================== --}}
@push('styles')
<style>
  /* ANIMASI */
  .smooth-fade { animation: fadeDown 0.7s ease-in-out; }
  @keyframes fadeDown { from { opacity: 0; transform: translateY(-10px);} to {opacity:1; transform:translateY(0);} }

  /* HOVER */
  .hover-row:hover {
    background-color: #FFFDE7 !important;
    transition: all 0.25s ease;
  }

  .breadcrumb-icon:hover {
    transform: scale(1.1);
    background-color: #FFC107 !important;
    color: white !important;
    transition: 0.3s ease;
  }

  /* === BUTTON ORANGE THEME === */
  .btn-orange-active {
    background-color: #FF9800 !important;
    color: #fff !important;
    border: 1px solid #FF9800 !important;
    box-shadow: 0 2px 6px rgba(255, 152, 0, 0.3);
    transition: 0.3s ease;
  }
  .btn-orange-active:hover,
  .btn-orange-active:focus {
    background-color: #FB8C00 !important;
    color: #fff !important;
  }

  .btn-outline-orange {
    border: 1px solid #FF9800 !important;
    color: #FF9800 !important;
    background-color: #fff !important;
    transition: 0.3s ease;
  }
  .btn-outline-orange:hover,
  .btn-outline-orange:focus,
  .btn-outline-orange.active {
    background-color: #FF9800 !important;
    color: #fff !important;
  }

  /* RESPONSIVE */
  @media (max-width: 768px) {
    .breadcrumb-extra { display: none; }
    h5.fw-semibold { font-size: 1rem; }
  }
</style>
@endpush
@endsection
