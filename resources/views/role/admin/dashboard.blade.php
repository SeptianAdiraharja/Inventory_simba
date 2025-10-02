@extends('layouts.index')
@section('content')

<!-- Transaksi -->
<div class="col-xl-12 mb-5">
  <div class="card h-100 shadow-sm animate__animated animate__fadeInDown animate__faster">
    <div class="card-header d-flex align-items-center justify-content-between">
      <h5 class="card-title m-0 me-2">Transaksi</h5>
      <div class="dropdown">
        <button class="btn text-body-secondary p-0" type="button" id="transactionID" data-bs-toggle="dropdown">
          <i class="ri-more-2-line icon-24px"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="transactionID">
          <a class="dropdown-item" href="javascript:void(0);">Muat Ulang</a>
          <a class="dropdown-item" href="javascript:void(0);">Bagikan</a>
          <a class="dropdown-item" href="javascript:void(0);">Perbarui</a>
        </div>
      </div>
    </div>
    <div class="row g-4 justify-content-center text-center">
      <x-dashboard-card
        class="animate__animated animate__fadeInLeft animate__delay-1s"
        title="Barang Keluar"
        :value="$totalBarangKeluar"
        icon="ri-pie-chart-2-line"
        color="primary"
        link="{{ route('admin.itemout.index') }}"
      />

      <x-dashboard-card
        class="animate__animated animate__fadeInUp animate__delay-2s"
        title="Tamu"
        :value="$totalGuest"
        icon="ri-group-line"
        color="warning"
        link="{{ route('admin.guests.index') }}"
      />

      <x-dashboard-card
        class="animate__animated animate__fadeInRight animate__delay-3s"
        title="Permintaan"
        :value="$totalRequest"
        icon="ri-price-tag-3-line"
        color="danger"
        link="{{ route('admin.request') }}"
      />
    </div>
  </div>
</div>
<!--/ Transaksi -->

<div class="col-xl-12 mt-5">
  <div class="card animate__animated animate__zoomIn">
    <div class="card-body row">
      <!-- Barang Keluar -->
      <div class="col-md-6 pe-md-4 fade-in-card">
        <x-dashboard-list-card title="Barang Keluar" :items="$latestBarangKeluar" type="barang_keluar"/>
      </div>

      <!-- Permintaan -->
      <div class="col-md-6 ps-md-4 border-start fade-in-card animate__delay-1s">
        <x-dashboard-list-card title="Permintaan" :items="$latestRequest" type="request"/>
      </div>
    </div>
  </div>
</div>


<!-- 5 Permintaan Terbanyak -->
<div class="col-12 mt-5">
  <div class="card overflow-hidden shadow-sm animate__animated animate__fadeInUp animate__slower">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0">5 Permintaan Terbanyak</h5>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-sm mb-0 table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>No</th>
              <th>Pengguna</th>
              <th>Email</th>
              <th>Peran</th>
              <th>Jumlah</th>
            </tr>
          </thead>
          <tbody>
            @forelse($topRequesters as $index => $requester)
              <tr class="animate__animated animate__fadeInUp animate__faster">
                <td>{{ $index + 1 }}</td>
                <td>{{ $requester['name'] }}</td>
                <td>{{ $requester['email'] }}</td>
                <td>{{ $requester['role'] }}</td>
                <td><span class="badge bg-primary">{{ $requester['total_requests'] }}</span></td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center">Tidak terdapat data permintaan.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<!--/ 5 Permintaan Terbanyak -->

<!-- Ikhtisar Lalu Lintas -->
<div class="col-xl-12 mt-5">
  <div class="card h-100 shadow-sm animate__animated animate__fadeInUp animate__slow">
    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
      <div>
        <h5 class="card-title m-0">Lalu Lintas</h5>
        <p class="small mb-0">Barang Masuk dan Barang Keluar</p>
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
<!--/ Ikhtisar Lalu Lintas -->

@endsection

@section('scripts')
<style>
/* Animasi tombol */
.hover-scale {
  transition: transform 0.3s ease, background 0.3s ease;
}
.hover-scale:hover {
  transform: scale(1.05);
}
.fade-in-card {
  opacity: 0;
  transform: translateY(20px);
  animation: fadeInUpCard 0.8s ease forwards;
}
@keyframes fadeInUpCard {
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>

<!-- Muat berkas JS dari public/js -->
<script src="{{ asset('js/dashboard.js') }}"></script>
@endsection
