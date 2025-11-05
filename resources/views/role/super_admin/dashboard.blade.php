@extends('layouts.index')
@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- ======================== --}}
  {{-- 🧭 MODERN BREADCRUMB --}}
  {{-- ======================== --}}
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap align-items-center justify-content-between animate__animated animate__fadeInDown smooth-fade">
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <i class="bi bi-speedometer2 fs-5" style="color:#FF9800;"></i>
      <a href="{{ route('dashboard') }}" class="breadcrumb-link fw-semibold text-decoration-none" style="color:#FF9800;">
        Dashboard
      </a>
      <span class="text-muted">/</span>
      <span class="text-muted">Ringkasan Statistik Barang</span>
    </div>
    <div class="d-flex align-items-center gap-2">
      <button id="refreshBtn" class="btn btn-sm rounded-pill px-3 py-1 fw-medium shadow-sm hover-glow d-flex align-items-center gap-2"
        style="border:1px solid #FFC300;color:#FF9800;">
        <i class="bi bi-arrow-clockwise me-1"></i>
        <span>Refresh Data</span>
      </button>
    </div>
  </div>

  {{-- ======================== --}}
  {{-- 📊 RINGKASAN GRAFIK + KARTU --}}
  {{-- ======================== --}}
  <div class="row g-4 mb-4 align-items-stretch">
    <!-- Grafik -->
    <div class="col-xl-9 col-md-12">
      <div class="card shadow-sm border-0 rounded-4 h-100 overflow-hidden">
        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap">
          <div>
            <h6 class="text-muted mb-1">Perbandingan Barang Masuk & Keluar</h6>
            <h5 class="fw-bold mb-0 text-dark">Statistik Barang</h5>
          </div>
          <div class="btn-group mt-2 mt-sm-0" id="chartFilterGroup">
            <button class="btn btn-sm rounded-pill px-3" data-period="daily">Harian</button>
            <button class="btn btn-sm rounded-pill px-3 active" data-period="weekly">Mingguan</button>
            <button class="btn btn-sm rounded-pill px-3" data-period="monthly">Bulanan</button>
            <button class="btn btn-sm rounded-pill px-3" data-period="triwulan">Triwulan</button>
            <button class="btn btn-sm rounded-pill px-3" data-period="semester">Semester</button>
            <button class="btn btn-sm rounded-pill px-3" data-period="yearly">Tahunan</button>
          </div>
        </div>

        <div class="card-body p-3" style="height:400px;">
          <div id="chartLoading" class="text-center py-5 d-none">
            <div class="spinner-border" style="color:#FF9800;" role="status"></div>
            <p class="mt-3 text-muted fw-medium">Memuat data terbaru...</p>
          </div>
          <div id="chartWrapper" class="position-relative w-100 h-100">
            <canvas id="overviewChart" style="width:100%;height:100%;"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- Kartu Ringkasan -->
    <div class="col-xl-3 col-md-6">
      <div class="d-flex flex-column gap-3 h-100">
        @php
          $cards = [
            ['title' => 'Barang', 'value' => $item, 'diff' => $itemDiff, 'icon' => 'ri-pie-chart-2-line', 'color' => '#FF9800'],
            ['title' => 'Pemasok', 'value' => $suppliers, 'diff' => $supplierDiff, 'icon' => 'ri-truck-line', 'color' => '#FFC300'],
            ['title' => 'Pengguna', 'value' => $users, 'diff' => $userDiff, 'icon' => 'ri-user-3-line', 'color' => '#FFE000']
          ];
        @endphp

        @foreach ($cards as $c)
          <div class="card shadow-sm border-0 flex-fill position-relative overflow-hidden">
            <div class="card-body">
              <p class="position-absolute top-0 end-0 mt-2 me-3 fw-semibold {{ $c['diff'] >= 0 ? 'text-success' : 'text-danger' }}">
                {{ $c['diff'] >= 0 ? '+' : '' }}{{ $c['diff'] }}%
              </p>
              <div class="d-flex align-items-center">
                <div class="me-3 flex-shrink-0">
                  <div class="d-flex align-items-center justify-content-center rounded-circle"
                       style="background-color:{{ $c['color'] }};width:45px;height:45px;">
                    <i class="{{ $c['icon'] }} fs-5 text-white"></i>
                  </div>
                </div>
                <div>
                  <h6 class="fw-semibold mb-1">{{ $c['title'] }}</h6>
                  <h4 class="fw-bold mb-1">{{ $c['value'] }} <small class="text-muted">Total</small></h4>
                  <small class="text-muted">
                    {{ $c['diff'] > 0 ? 'Bertambah ' . $c['diff'] : ($c['diff'] < 0 ? 'Berkurang ' . abs($c['diff']) : 'Tidak berubah') }} dari kemarin
                  </small>
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  {{-- ======================== --}}
  {{-- 📦 BARANG MASUK / KELUAR / KEDALUWARSA / HABIS --}}
  {{-- ======================== --}}
  <div class="row g-4">
    @php
      $sections = [
        ['title' => 'Barang Masuk', 'icon' => 'ri-box-3-line', 'color' => '#2ecc71', 'badge' => 'bg-success-subtle text-success', 'data' => $itemIns, 'empty' => 'Belum ada data barang masuk'],
        ['title' => 'Barang Keluar', 'icon' => 'ri-logout-box-line', 'color' => '#e74c3c', 'badge' => 'bg-danger-subtle text-danger', 'data' => $itemOuts, 'empty' => 'Belum ada data barang keluar'],
        ['title' => 'Hampir Kedaluwarsa', 'icon' => 'ri-alarm-warning-line', 'color' => '#FFC300', 'badge' => 'bg-warning text-dark', 'data' => $expiredSoon, 'empty' => 'Tidak ada barang hampir kedaluwarsa'],
        ['title' => 'Hampir Habis', 'icon' => 'ri-alert-line', 'color' => '#FF9800', 'badge' => 'bg-danger-subtle text-danger', 'data' => $lowStockItems, 'empty' => 'Tidak ada barang hampir habis'],
      ];
    @endphp

    @foreach($sections as $sec)
      <div class="col-xl-3 col-md-6">
        <div class="card shadow-sm h-100 border-0 rounded-3 smooth-fade">
          <div class="card-body">
            <h5 class="fw-bold mb-3"><i class="{{ $sec['icon'] }} me-1" style="color:{{ $sec['color'] }};"></i> {{ $sec['title'] }}</h5>
            <ul class="list-unstyled mb-0">
              @forelse($sec['data'] as $item)
                <li class="d-flex mb-3 align-items-center pb-2 border-bottom">
                  <div class="flex-grow-1">
                    <h6 class="mb-1 fw-semibold">{{ $item->item->name ?? $item->name }}</h6>
                    <small class="text-muted">
                      @if(isset($item->quantity)) Jumlah: {{ $item->quantity }}<br>@endif
                      @if(isset($item->stock)) Stok tersisa: {{ $item->stock }}<br>@endif
                      {{ isset($item->created_at) ? 'Tanggal: ' . $item->created_at->format('d M Y') : '' }}
                    </small>
                  </div>
                  <span class="badge {{ $sec['badge'] }}">{{ $item->quantity ?? $item->stock }}</span>
                </li>
              @empty
                <li class="text-muted fst-italic">{{ $sec['empty'] }}</li>
              @endforelse
            </ul>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  {{-- ======================== --}}
  {{-- 👥 PENGGUNA TERATAS --}}
  {{-- ======================== --}}
  <div class="col-12 mt-5">
    <div class="card shadow-sm border-0 rounded-3 overflow-hidden smooth-fade">
      <div class="card-header d-flex justify-content-between align-items-center bg-white">
        <h5 class="fw-bold mb-0"><i class="bi bi-handbag-fill me-2" style="color:#FF9800;"></i> 5 Pengguna Paling Sering Mengambil Barang</h5>
        <small class="text-muted">Berdasarkan jumlah pengambilan barang</small>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Pengguna</th>
              <th>Email</th>
              <th>Peran</th>
              <th>Total Pengambilan</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($topUsers as $data)
              <tr>
                <td><h6 class="mb-0">{{ $data->name }}</h6></td>
                <td>{{ $data->email ?? '-' }}</td>
                <td><span class="badge bg-label-warning rounded-pill">{{ ucfirst($data->role ?? 'Guest') }}</span></td>
                <td><span class="fw-semibold text-dark">{{ $data->total_out }}</span></td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<style>
.smooth-fade{animation:fadeIn 0.6s ease-in-out;}
@keyframes fadeIn{from{opacity:0;transform:translateY(10px);}to{opacity:1;transform:translateY(0);}}
.hover-glow{transition:all 0.25s ease;}
.hover-glow:hover{background-color:#FF9800!important;color:#fff!important;box-shadow:0 0 12px rgba(255,152,0,0.45);}
#chartFilterGroup .btn{border:1px solid #FFE000;color:#FF9800;background:#fff;transition:all 0.2s;font-weight:500;}
#chartFilterGroup .btn:hover{background-color:#FFC300;color:#fff;}
#chartFilterGroup .btn.active{background-color:#FF9800;color:#fff;box-shadow:0 0 8px rgba(255,152,0,0.35);}
.breadcrumb-link{position:relative;transition:all 0.25s ease;}
.breadcrumb-link::after{content:'';position:absolute;bottom:-2px;left:0;width:0;height:2px;background:#FF9800;transition:width 0.25s ease;}
.breadcrumb-link:hover::after{width:100%;}
</style>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('overviewChart').getContext('2d');
const chartData = {
  daily:{labels:@json($dailyLabels),masuk:@json($dailyMasuk),keluar:@json($dailyKeluar)},
  weekly:{labels:@json($weeklyLabels),masuk:@json($weeklyMasuk),keluar:@json($weeklyKeluar)},
  monthly:{labels:@json($monthlyLabels),masuk:@json($monthlyMasuk),keluar:@json($monthlyKeluar)},
  triwulan:{labels:@json($triwulanLabels),masuk:@json($triwulanMasuk),keluar:@json($triwulanKeluar)},
  semester:{labels:@json($semesterLabels),masuk:@json($semesterMasuk),keluar:@json($semesterKeluar)},
  yearly:{labels:@json($yearlyLabels),masuk:@json($yearlyMasuk),keluar:@json($yearlyKeluar)}
};

let currentPeriod='weekly';
const itemChart=new Chart(ctx,{
  type:'line',
  data:{
    labels:chartData[currentPeriod].labels,
    datasets:[
      {label:'Barang Masuk',data:chartData[currentPeriod].masuk,borderColor:'#FF9800',backgroundColor:'rgba(255,193,7,0.25)',borderWidth:2,fill:true,tension:0.35},
      {label:'Barang Keluar',data:chartData[currentPeriod].keluar,borderColor:'#e74c3c',backgroundColor:'rgba(231,76,60,0.25)',borderWidth:2,fill:true,tension:0.35}
    ]
  },
  options:{
    maintainAspectRatio:false,
    responsive:true,
    interaction:{mode:'index',intersect:false},
    plugins:{legend:{labels:{color:'#444',font:{size:13,weight:'bold'}}}},
    scales:{
      x:{ticks:{color:'#FF9800',font:{size:12}},grid:{display:false}},
      y:{beginAtZero:true,ticks:{color:'#FF9800',font:{size:12}},grid:{color:'rgba(240,200,100,0.3)',borderDash:[5,5]}}
    }
  }
});

document.querySelectorAll('[data-period]').forEach(btn=>{
  btn.addEventListener('click',()=>{
    document.querySelectorAll('[data-period]').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    currentPeriod=btn.getAttribute('data-period');
    updateChart(chartData[currentPeriod]);
  });
});

function updateChart(newData){
  itemChart.data.labels=newData.labels;
  itemChart.data.datasets[0].data=newData.masuk;
  itemChart.data.datasets[1].data=newData.keluar;
  itemChart.update();
}
</script>
@endpush
