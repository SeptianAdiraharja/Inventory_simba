@extends('layouts.index')
@section('content')
                <div class="row g-4 mb-3 align-items-stretch">

                  <!-- Grafik -->
                  <div class="col-xl-9 col-md-12">
                    <div class="card shadow-sm border-0 rounded-3 h-100 overflow-hidden">
                      <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <div>
                          <h6 class="text-muted mb-1">Ringkasan Barang Masuk dan Barang Keluar</h6>
                          <h5 class="fw-bold mb-0">Statistik Barang</h5>
                        </div>
                      </div>
                      <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                          <span class="text-muted">
                            <i class="bi bi-graph-up-arrow me-2 text-primary"></i> Grafik berdasarkan periode
                          </span>
                          <div class="btn-group" id="chartFilterGroup">
                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3" data-period="daily">Harian</button>
                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3 active" data-period="weekly">Mingguan</button>
                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3" data-period="monthly">Bulanan</button>
                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3" data-period="triwulan">Triwulan</button>
                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3" data-period="semester">Semester</button>
                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3" data-period="yearly">Tahunan</button>
                          </div>
                        </div>
                        <div class="chart-container" style="position: relative; height: 400px; width: 100%;">
                          <canvas id="overviewChart"></canvas>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!-- /Grafik -->

                  <!-- Tiga Kartu -->
                  <div class="col-xl-3 col-md-6">
                    <div class="d-flex flex-column gap-3 h-100">

                      <!-- Total Barang -->
                      <div class="card shadow-sm border-0 flex-fill position-relative overflow-hidden">
                        <div class="card-body">
                          <p class="position-absolute top-0 end-0 mt-2 me-3 fw-semibold {{ $itemDiff >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $itemDiff >= 0 ? '+' : '' }}{{ $itemDiff }}%
                          </p>

                          <div class="d-flex align-items-center">
                            <div class="avatar me-3 flex-shrink-0">
                              <div class="avatar-initial bg-secondary rounded-circle shadow-xs d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                <i class="ri ri-pie-chart-2-line fs-5 text-white"></i>
                              </div>
                            </div>
                            <div>
                              <h6 class="fw-semibold mb-1">Barang</h6>
                              <h4 class="fw-bold mb-1">{{ $item }} <small class="text-muted">Total</small></h4>
                              <small class="text-muted">
                                {{ $itemDiff > 0 ? 'Bertambah ' . $itemDiff : ($itemDiff < 0 ? 'Berkurang ' . abs($itemDiff) : 'Tidak berubah') }} dari kemarin
                              </small>
                            </div>
                          </div>
                          <small class="position-absolute bottom-0 start-0 mb-2 ms-3 text-muted">Jumlah Seluruh Barang</small>
                        </div>
                      </div>
                      <!-- /Total Barang -->

                      <!-- Total Pemasok -->
                      <div class="card shadow-sm border-0 flex-fill position-relative overflow-hidden">
                        <div class="card-body">
                          <p class="position-absolute top-0 end-0 mt-2 me-3 fw-semibold {{ $supplierDiff >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $supplierDiff >= 0 ? '+' : '' }}{{ $supplierDiff }}%
                          </p>

                          <div class="d-flex align-items-center">
                            <div class="avatar me-3 flex-shrink-0">
                              <div class="avatar-initial bg-primary rounded-circle shadow-xs d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                <i class="ri ri-truck-line fs-5 text-white"></i>
                              </div>
                            </div>
                            <div>
                              <h6 class="fw-semibold mb-1">Pemasok</h6>
                              <h4 class="fw-bold mb-1">{{ $suppliers }} <small class="text-muted">Total</small></h4>
                              <small class="text-muted">
                                {{ $supplierDiff > 0 ? 'Bertambah ' . $supplierDiff : ($supplierDiff < 0 ? 'Berkurang ' . abs($supplierDiff) : 'Tidak berubah') }} dari kemarin
                              </small>
                            </div>
                          </div>
                          <small class="position-absolute bottom-0 start-0 mb-2 ms-3 text-muted">Jumlah Seluruh Pemasok</small>
                        </div>
                      </div>
                      <!-- /Total Pemasok -->

                      <!-- Total Pengguna -->
                      <div class="card shadow-sm border-0 flex-fill position-relative overflow-hidden">
                        <div class="card-body">
                          <p class="position-absolute top-0 end-0 mt-2 me-3 fw-semibold {{ $userDiff >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $userDiff >= 0 ? '+' : '' }}{{ $userDiff }}%
                          </p>

                          <div class="d-flex align-items-center">
                            <div class="avatar me-3 flex-shrink-0">
                              <div class="avatar-initial bg-warning rounded-circle shadow-xs d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                <i class="ri ri-user-3-line fs-5 text-white"></i>
                              </div>
                            </div>
                            <div>
                              <h6 class="fw-semibold mb-1">Pengguna</h6>
                              <h4 class="fw-bold mb-1">{{ $users }} <small class="text-muted">Total</small></h4>
                              <small class="text-muted">
                                {{ $userDiff > 0 ? 'Bertambah ' . $userDiff : ($userDiff < 0 ? 'Berkurang ' . abs($userDiff) : 'Tidak berubah') }} dari kemarin
                              </small>
                            </div>
                          </div>
                          <small class="position-absolute bottom-0 start-0 mb-2 ms-3 text-muted">Jumlah Seluruh Pengguna</small>
                        </div>
                      </div>
                      <!-- /Total Pengguna -->

                    </div>
                  </div>
                  <!-- /Tiga Kartu -->
                </div>

                <!-- Barang Masuk / Barang Keluar / Hampir Kedaluwarsa / Hampir Habis -->
                <div class="col-xl-12">
                  <div class="row g-4">

                    {{-- Barang Masuk --}}
                    <div class="col-xl-3 col-md-6">
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
                          @if($lastUpdateItemIn)
                            <small class="text-muted d-block mb-2">Last Update: {{ \Carbon\Carbon::parse($lastUpdateItemIn)->format('d M Y H:i') }}</small>
                          @endif
                          <ul class="list-unstyled mb-0">
                            @forelse($itemIns as $item)
                              <li class="d-flex mb-3 align-items-center pb-2 border-bottom">
                                <div class="flex-grow-1">
                                  <h6 class="mb-1 fw-semibold">{{ $item->item->name }}</h6>
                                  <small class="text-muted">
                                    Jumlah: {{ $item->quantity }} <br>
                                    Tanggal: {{ $item->created_at->format('d M Y') }}
                                  </small>
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
                    <div class="col-xl-3 col-md-6">
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
                    <div class="col-xl-3 col-md-6">
                      <div class="card shadow-sm h-100 border-0 rounded-3">
                        <div class="card-body">
                          <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0">
                              <i class="ri-alarm-warning-line text-warning me-1"></i> Hampir Kedaluwarsa
                            </h5>
                          </div>
                          @if($lastUpdateExpired)
                            <small class="text-muted d-block mb-2">Last Update: {{ \Carbon\Carbon::parse($lastUpdateExpired)->format('d M Y H:i') }}</small>
                          @endif
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
                                  <span class="badge bg-danger">Kedaluwarsa {{ abs($days) }} hari lalu</span>
                                @else
                                  <span class="badge bg-warning text-dark">(Dalam {{ $days }} hari)</span>
                                @endif
                              </li>
                            @empty
                              <li class="text-muted fst-italic">Tidak ada barang hampir kedaluwarsa</li>
                            @endforelse
                          </ul>
                        </div>
                      </div>
                    </div>

                    {{-- Barang Hampir Habis --}}
                    <div class="col-xl-3 col-md-6">
                      <div class="card shadow-sm h-100 border-0 rounded-3">
                        <div class="card-body">
                          <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0">
                              <i class="ri-alert-line text-danger me-1"></i> Hampir Habis
                            </h5>
                          </div>
                          <ul class="list-unstyled mb-0">
                            @forelse($lowStockItems as $item)
                              <li class="d-flex mb-3 align-items-center pb-2 border-bottom">
                                <div class="flex-grow-1">
                                  <h6 class="mb-1 fw-semibold">{{ $item->name }}</h6>
                                  <small class="text-muted">
                                    Stok tersisa: {{ $item->stock }}
                                  </small>
                                </div>
                                <span class="badge bg-danger-subtle text-danger">
                                  {{ $item->stock }}
                                </span>
                              </li>
                            @empty
                              <li class="text-muted fst-italic">Tidak ada barang yang hampir habis</li>
                            @endforelse
                          </ul>
                        </div>
                      </div>
                    </div>

                  </div>
                </div>
                <!-- /Barang Masuk / Barang Keluar / Hampir Kedaluwarsa / Hampir Habis -->

                <!-- 5 Pengguna Teratas -->
                <div class="col-12 mt-5">
                  <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
                    <div class="card-header d-flex justify-content-between align-items-center bg-white">
                      <h5 class="fw-bold mb-0">
                        <i class="bi bi-handbag-fill me-2 text-primary"></i> 5 Pengguna Paling Sering Mengambil Barang
                      </h5>
                      <small class="text-muted">Berdasarkan jumlah permintaan/pengambilan barang</small>
                    </div>

                    <div class="table-responsive">
                      <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                          <tr>
                            <th>Pengguna</th>
                            <th>Email</th>
                            <th>Peran</th>
                            <th>Total Pengambilan</th>
                            <th>Status</th>
                          </tr>
                        </thead>
                        <tbody>
                          @forelse($topUsers as $data)
                            <tr>
                              <td>
                                <div class="d-flex align-items-center">
                                  <div class="avatar avatar-sm me-3">
                                    <img src="{{ $data->user->avatar_url ?? asset('assets/img/avatars/default.png') }}"
                                        alt="Avatar" class="rounded-circle" />
                                  </div>
                                  <div>
                                    <h6 class="mb-0 text-truncate">{{ $data->user->name }}</h6>
                                    <small class="text-muted">{{ '@' . Str::slug($data->user->name, '') }}</small>
                                  </div>
                                </div>
                              </td>
                              <td class="text-truncate">{{ $data->user->email }}</td>
                              <td>
                                <span class="badge bg-label-info rounded-pill">
                                  {{ ucfirst($data->user->role) }}
                                </span>
                              </td>
                              <td>
                                <span class="fw-semibold text-dark">{{ $data->total_out }}</span>
                              </td>
                              <td>
                                @if($data->user->status === 'active')
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
                                Belum terdapat data pengguna yang mengambil barang
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
  },
  triwulan: {
  labels: @json($triwulanLabels),
  masuk: @json($triwulanMasuk),
  keluar: @json($triwulanKeluar)
  },
  semester: {
    labels: @json($semesterLabels),
    masuk: @json($semesterMasuk),
    keluar: @json($semesterKeluar)
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

// Event untuk tombol Harian-Mingguan-Bulanan-Tahunan
document.querySelectorAll('[data-period]').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('[data-period]').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    currentPeriod = btn.getAttribute('data-period');
    updateChart(chartData[currentPeriod]);
  });
});

// Event untuk dropdown (Triwulan & Semester)
document.querySelectorAll('.range-filter').forEach(link => {
  link.addEventListener('click', e => {
    e.preventDefault();
    const range = link.getAttribute('data-range');
    currentPeriod = range;
    updateChart(chartData[range]);
  });
});

// Fungsi update chart
function updateChart(newData) {
  itemChart.data.labels = newData.labels;
  itemChart.data.datasets[0].data = newData.masuk;
  itemChart.data.datasets[1].data = newData.keluar;
  itemChart.update();
}
</script>
@endpush