@extends('layouts.index')
@section('content')
                <div class="row gy-6 mb-4">
                  <!-- Grafik -->
                  <div class="col-xl-8 col-md-6">
                    <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
                      <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <div>
                          <h6 class="text-muted mb-1">Ringkasan Barang Masuk dan Barang Keluar</h6>
                          <h5 class="fw-bold mb-0">Statistik Barang</h5>
                        </div>
                        <div class="text-end">
                          <h4 class="fw-bold {{ $growth >= 0 ? 'text-success' : 'text-danger' }} mb-0">
                            {{ number_format($growth, 1) }}%
                          </h4>
                          <small class="text-muted">dibandingkan dengan periode sebelumnya</small>
                        </div>
                      </div>
                      <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                          <span class="text-muted">
                            <i class="bi bi-graph-up-arrow me-2 text-primary"></i> Grafik berdasarkan periode
                          </span>
                          <div class="btn-group" id="chartFilterGroup">
                             <button class="btn btn-sm btn-outline-primary rounded-pill px-3" data-period="daily">
                              Harian
                            </button>
                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3 active" data-period="weekly">
                              Mingguan
                            </button>
                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3" data-period="monthly">
                              Bulanan
                            </button>
                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3" data-period="yearly">
                              Tahunan
                            </button>
                          </div>
                        </div>
                        <div class="chart-container" style="position: relative; height: 400px; width: 100%;">
                          <canvas id="overviewChart"></canvas>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!-- /Grafik -->

                  <!-- Empat Kartu -->
                  <div class="col-xl-4 col-md-6">
                  <div class="row gy-6">
                    <!-- Total Kategori -->
                    <div class="col-sm-6">
                      <div class="card h-100">
                        <div class="card-header d-flex align-items-center justify-content-between">
                          <div class="avatar">
                            <div class="avatar-initial bg-info rounded-circle shadow-xs">
                              <i class="icon-base ri ri-folder-2-line icon-24px"></i>
                            </div>
                          </div>
                        </div>
                        <div class="card-body">
                          <h6 class="mb-1">Kategori</h6>
                          <div class="d-flex flex-wrap mb-1 align-items-center">
                            <h4 class="mb-0 me-2">{{ $categories }}</h4>
                            <p class="{{ $categories >= 0 ? 'text-success' : 'text-danger' }} mb-0">
                              {{ $categories >= 0 ? '+' : '' }}{{ $categories }}%
                            </p>
                          </div>
                          <small>Jumlah Seluruh Kategori</small>
                        </div>
                      </div>
                    </div>
                    <!--/ Total Kategori -->

                    <!-- Total Barang -->
                    <div class="col-sm-6">
                      <div class="card h-100">
                        <div class="card-header d-flex align-items-center justify-content-between">
                          <div class="avatar">
                            <div class="avatar-initial bg-secondary rounded-circle shadow-xs">
                              <i class="icon-base ri ri-pie-chart-2-line icon-24px"></i>
                            </div>
                          </div>
                        </div>
                        <div class="card-body">
                          <h6 class="mb-1">Barang</h6>
                          <div class="d-flex flex-wrap mb-1 align-items-center">
                            <h4 class="mb-0 me-2">{{ $item }}</h4>
                            <p class="{{ $item >= 0 ? 'text-success' : 'text-danger' }} mb-0">
                              {{ $item >= 0 ? '+' : '' }}{{ $item }}%
                            </p>
                          </div>
                          <small>Jumlah Seluruh Barang</small>
                        </div>
                      </div>
                    </div>
                    <!--/ Total Barang -->

                    <!-- Total Pemasok -->
                    <div class="col-sm-6">
                      <div class="card h-100">
                        <div class="card-header d-flex align-items-center justify-content-between">
                          <div class="avatar">
                            <div class="avatar-initial bg-primary rounded-circle shadow-xs">
                              <i class="icon-base ri ri-truck-line icon-24px"></i>
                            </div>
                          </div>
                        </div>
                        <div class="card-body">
                          <h6 class="mb-1">Pemasok</h6>
                          <div class="d-flex flex-wrap mb-1 align-items-center">
                            <h4 class="mb-0 me-2">{{ $suppliers }}</h4>
                            <p class="{{ $suppliers >= 0 ? 'text-success' : 'text-danger' }} mb-0">
                              {{ $suppliers >= 0 ? '+' : '' }}{{ $suppliers }}%
                            </p>
                          </div>
                          <small>Jumlah Seluruh Pemasok</small>
                        </div>
                      </div>
                    </div>
                    <!--/ Total Pemasok -->

                    <!-- Total Pengguna -->
                    <div class="col-sm-6">
                      <div class="card h-100">
                        <div class="card-header d-flex align-items-center justify-content-between">
                          <div class="avatar">
                            <div class="avatar-initial bg-warning rounded-circle shadow-xs">
                              <i class="icon-base ri ri-user-3-line icon-24px"></i>
                            </div>
                          </div>
                        </div>
                        <div class="card-body">
                          <h6 class="mb-1">Pengguna</h6>
                          <div class="d-flex flex-wrap mb-1 align-items-center">
                            <h4 class="mb-0 me-2">{{ $users }}</h4>
                            <p class="{{ $users >= 0 ? 'text-success' : 'text-danger' }} mb-0">
                              {{ $users >= 0 ? '+' : '' }}{{ $users }}%
                            </p>
                          </div>
                          <small>Jumlah Seluruh Pengguna</small>
                        </div>
                      </div>
                    </div>
                    <!--/ Total Pengguna -->
                  </div>
                </div>
                </div>

                <!-- Barang Masuk / Barang Keluar / Hampir Kedaluwarsa -->
                <div class="col-xl-12">
                  <div class="row g-4">

                    {{-- Barang Masuk --}}
                    <div class="col-md-4">
                      <div class="card shadow-sm h-100 border-0 rounded-3">
                        <div class="card-body">
                          <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0">
                              <i class="ri-box-3-line text-success me-1"></i> Barang Masuk
                            </h5>
                            <a class="fw-medium text-decoration-none small" href="{{ route('super_admin.item_ins.index') }}">
                              Lihat Semua
                            </a>
                          </div>
                          <ul class="list-unstyled mb-0">
                            @forelse($itemIns as $item)
                              <li class="d-flex mb-3 align-items-center pb-2 border-bottom">
                                <div class="flex-grow-1">
                                  <h6 class="mb-1 fw-semibold">{{ $item->item->name }}</h6>
                                  <small class="text-muted">Jumlah: {{ $item->quantity }}</small>
                                </div>
                                <span class="badge bg-success-subtle text-success">
                                  +{{ $item->quantity }}
                                </span>
                              </li>
                            @empty
                              <li class="text-muted fst-italic">Belum terdapat data barang masuk</li>
                            @endforelse
                          </ul>
                        </div>
                      </div>
                    </div>

                    {{-- Barang Keluar --}}
                    <div class="col-md-4">
                      <div class="card shadow-sm h-100 border-0 rounded-3">
                        <div class="card-body">
                          <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0">
                              <i class="ri-logout-box-line text-danger me-1"></i> Barang Keluar
                            </h5>
                          </div>
                          <ul class="list-unstyled mb-0">
                            @forelse($itemOuts as $item)
                              <li class="d-flex mb-3 align-items-center pb-2 border-bottom">
                                <div class="flex-grow-1">
                                  <h6 class="mb-1 fw-semibold">{{ $item->item->name }}</h6>
                                  <small class="text-muted">
                                    Jumlah: {{ $item->quantity }} <br>
                                    Tanggal: {{ $item->created_at->format('d M Y') }}
                                  </small>
                                </div>
                                <span class="badge bg-danger-subtle text-danger">
                                  -{{ $item->quantity }}
                                </span>
                              </li>
                            @empty
                              <li class="text-muted fst-italic">Belum terdapat data barang keluar</li>
                            @endforelse
                          </ul>
                        </div>
                      </div>
                    </div>

                    {{-- Barang Hampir Kedaluwarsa --}}
                    <div class="col-md-4">
                      <div class="card shadow-sm h-100 border-0 rounded-3">
                        <div class="card-body">
                          <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0">
                              <i class="ri-alarm-warning-line text-warning me-1"></i> Barang Hampir Kedaluwarsa
                            </h5>
                          </div>
                          <ul class="list-unstyled mb-0">
                            @forelse($expiredSoon as $item)
                              <li class="d-flex mb-3 align-items-center pb-2 border-bottom">
                                <div class="flex-grow-1">
                                  <h6 class="mb-1 fw-semibold">{{ $item->item->name }}</h6>
                                  <small class="text-muted">
                                    Jumlah: {{ $item->quantity }} <br>
                                    Kedaluwarsa: {{ $item->expired_at->format('d M Y') }}
                                  </small>
                                </div>
                                @php
                                  $days = now()->startOfDay()->diffInDays($item->expired_at->startOfDay(), false);
                                @endphp
                                @if($days < 0)
                                  <span class="badge bg-danger">Kedaluwarsa {{ abs($days) }} hari yang lalu</span>
                                @else
                                  <span class="badge bg-warning text-dark">(Dalam {{ $days }} hari)</span>
                                @endif
                              </li>
                            @empty
                              <li class="text-muted fst-italic">Tidak ada barang yang hampir kedaluwarsa</li>
                            @endforelse
                          </ul>
                        </div>
                      </div>
                    </div>

                  </div>
                </div>
                <!-- /Barang Masuk / Barang Keluar / Hampir Kedaluwarsa -->

                {{-- Ekspor --}}
                <div class="card shadow-sm border-0 rounded-3 p-4 mt-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-file-earmark-arrow-down me-2 text-primary"></i> Ekspor Data
                        </h5>
                        <small class="text-muted">Pilih jenis data dan format untuk diekspor</small>
                    </div>

                    <form id="exportForm" method="GET" action="">
                        <div class="row g-4">
                            <!-- Jenis Data -->
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Jenis Data</label>
                                <select class="form-select shadow-sm" name="type" id="type" required>
                                    <option value="" disabled selected>-- Pilih Jenis --</option>
                                    <option value="barang_masuk">Barang Masuk</option>
                                    <option value="barang_keluar">Barang Keluar</option>
                                </select>
                            </div>

                            <!-- Periode -->
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Periode</label>
                                <select class="form-select shadow-sm" name="period" id="period" required>
                                    <option value="" disabled selected>-- Pilih Periode --</option>
                                    <option value="weekly">Minggu</option>
                                    <option value="monthly">Bulan</option>
                                    <option value="yearly">Tahun</option>
                                </select>
                            </div>

                            <!-- Format -->
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Format</label>
                                <select class="form-select shadow-sm" name="format" id="format" required>
                                    <option value="" disabled selected>-- Pilih Format --</option>
                                    <option value="pdf">PDF</option>
                                    <option value="excel">Excel</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-download me-2"></i> Ekspor
                            </button>
                        </div>
                    </form>
                </div>
                {{-- Akhir Ekspor --}}

                <!-- 5 Pengguna Teratas -->
              <div class="col-12">
                <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
                  <div class="card-header d-flex justify-content-between align-items-center bg-white">
                    <h5 class="fw-bold mb-0">
                      <i class="bi bi-people-fill me-2 text-primary"></i> 5 Pengguna Paling Sering Mengeluarkan Barang
                    </h5>
                    <small class="text-muted">Berdasarkan aktivitas pengeluaran barang</small>
                  </div>

                  <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                      <thead class="table-light">
                        <tr>
                          <th>Pengguna</th>
                          <th>Email</th>
                          <th>Peran</th>
                          <th>Total Pengeluaran</th>
                          <th>Status</th>
                        </tr>
                      </thead>
                      <tbody>
                        @forelse($topUsers as $data)
                          <tr>
                            <td>
                              <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-3">
                                  <img src="{{ $data->cart->user->avatar_url ?? asset('assets/img/avatars/default.png') }}"
                                      alt="Avatar" class="rounded-circle" />
                                </div>
                                <div>
                                  <h6 class="mb-0 text-truncate">{{ $data->cart->user->name }}</h6>
                                  <small class="text-muted">{{ '@' . Str::slug($data->cart->user->name, '') }}</small>
                                </div>
                              </div>
                            </td>
                            <td class="text-truncate">{{ $data->cart->user->email }}</td>
                            <td>
                              <span class="badge bg-label-info rounded-pill">
                                {{ ucfirst($data->cart->user->role) }}
                              </span>
                            </td>
                            <td>
                              <span class="fw-semibold text-dark">{{ $data->total_out }}</span>
                            </td>
                            <td>
                              @if($data->cart->user->status === 'active')
                                <span class="badge bg-label-success rounded-pill">Aktif</span>
                              @else
                                <span class="badge bg-label-secondary rounded-pill">Tidak Aktif</span>
                              @endif
                            </td>
                          </tr>
                        @empty
                          <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                              <i class="bi bi-exclamation-circle me-2"></i>
                              Belum terdapat data pengguna yang mengeluarkan barang
                            </td>
                          </tr>
                        @endforelse
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
              <!-- /5 Pengguna Teratas -->
              </div>
              <style>
                #chartFilterGroup .btn {
                  transition: all 0.2s ease-in-out;
                  font-weight: 500;
                }
                #chartFilterGroup .btn:hover {
                  background-color: #750dfd;
                  color: #fff;
                }
                #chartFilterGroup .btn.active {
                  background-color: #7d0dfd;
                  color: #fff;
                  box-shadow: 0 0 8px rgba(207, 222, 245, 0.4);
                }
              </style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const ctx = document.getElementById('overviewChart').getContext('2d');
  const chartData = {
    daily: {
      labels: @json($dailyLabels),
      masuk: @json($dailyMasuk),
      keluar: @json($dailyKeluar)
    },
    weekly: {
      labels: @json($weeklyLabels),
      masuk: @json($weeklyMasuk),
      keluar: @json($weeklyKeluar)
    },
    monthly: {
      labels: @json($monthlyLabels),
      masuk: @json($monthlyMasuk),
      keluar: @json($monthlyKeluar)
    },
    yearly: {
      labels: @json($yearlyLabels),
      masuk: @json($yearlyMasuk),
      keluar: @json($yearlyKeluar)
    }
  };

  let currentPeriod = 'weekly';

 const itemChart = new Chart(ctx, {
  type: 'line',
  data: {
    labels: chartData[currentPeriod].labels,
    datasets: [
      {
        label: 'Barang Masuk',
        data: chartData[currentPeriod].masuk,
        borderColor: 'rgba(111, 66, 193, 1)',
        backgroundColor: 'rgba(111, 66, 193, 0.2)',
        borderWidth: 2,
        fill: true,
        tension: 0.3,
        pointBackgroundColor: 'rgba(111, 66, 193, 1)'
      },
      {
        label: 'Barang Keluar',
        data: chartData[currentPeriod].keluar,
        borderColor: 'rgba(255, 99, 132, 1)',
        backgroundColor: 'rgba(255, 99, 132, 0.2)',
        borderWidth: 2,
        fill: true,
        tension: 0.3,
        pointBackgroundColor: 'rgba(255, 99, 132, 1)'
      }
    ]
  },
  options: {
    responsive: true,
    interaction: { mode: 'index', intersect: false },
    plugins: {
      legend: {
        labels: {
          color: '#444',
          font: { size: 13, weight: 'bold' }
        }
      },
      tooltip: {
        backgroundColor: '#222',
        titleColor: '#fff',
        bodyColor: '#fff',
        padding: 10,
        cornerRadius: 6
      }
    },
    scales: {
      x: {
        ticks: { color: '#6f42c1', font: { size: 12 } },
        grid: { display: false }
      },
      y: {
        beginAtZero: true,
        ticks: { color: '#6f42c1', font: { size: 12 } },
        grid: { color: 'rgba(200,200,200,0.3)', borderDash: [5, 5] }
      }
    }
  }
});


  document.querySelectorAll('[data-period]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('[data-period]').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');

      currentPeriod = btn.getAttribute('data-period');
      const newData = chartData[currentPeriod];

      itemChart.data.labels = newData.labels;
      itemChart.data.datasets[0].data = newData.masuk;
      itemChart.data.datasets[1].data = newData.keluar;

      itemChart.update();
    });
  });
</script>
@endpush