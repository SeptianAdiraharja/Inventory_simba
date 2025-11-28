@extends('layouts.index')
@section('title', 'Export Data Barang Keluar')
@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- ======================== --}}
  {{-- 🧭 BREADCRUMB NAVIGATION --}}
  {{-- ======================== --}}
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3 animate__animated animate__fadeInDown smooth-fade">
    <div class="d-flex align-items-center gap-2">
      <div class="breadcrumb-icon d-flex align-items-center justify-content-center rounded-circle"
           style="width:38px;height:38px;background:#FFF3E0;color:#FF9800;">
        <i class="bi bi-house-door-fill fs-5"></i>
      </div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 align-items-center">
          <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold" style="color:#FF9800;">
              Dashboard
            </a>
          </li>
          <li class="breadcrumb-item active fw-semibold text-dark" aria-current="page">
            Export Data Barang Keluar
          </li>
        </ol>
      </nav>
    </div>

    <div class="breadcrumb-extra text-end">
      <small class="text-muted">
        <i class="bi bi-calendar-check me-1"></i>{{ now()->format('d M Y, H:i') }}
      </small>
    </div>
  </div>

  {{-- ======================== --}}
  {{-- 📦 MAIN CONTENT AREA --}}
  {{-- ======================== --}}
  <div class="card shadow-lg border-0 rounded-4 overflow-hidden animate__animated animate__fadeInUp">
    <div class="card-body bg-light">
      <h4 class="fw-bold mb-4" style="color:#FF9800;">
        <i class="bi bi-box-arrow-up"></i> Export Data Barang Keluar
      </h4>

      {{-- =============== DATA FILTER SECTION =============== --}}
      <div class="card border-0 shadow-sm rounded-4 mb-4 animate__animated animate__fadeInUp">
        <div class="card-body bg-white p-4 rounded-4">
          <h6 class="fw-semibold text-secondary mb-3">
            <i class="bi bi-funnel"></i> Filter Data
          </h6>
          <form method="GET" action="{{ route('admin.export.out') }}" id="filter-form" class="row g-3 align-items-end">
            <div class="col-md-3">
              <label class="form-label fw-semibold">Tanggal Mulai</label>
              <input type="date" name="start_date" value="{{ $startDate ?? '' }}" class="form-control shadow-sm rounded-3 border-warning" required>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Tanggal Akhir</label>
              <input type="date" name="end_date" value="{{ $endDate ?? '' }}" class="form-control shadow-sm rounded-3 border-warning" required>
            </div>
            <div class="col-md-3">
              <button type="submit" class="btn text-white w-100 rounded-3 fw-semibold shadow-sm"
                      style="background-color:#FF9800;">
                <i class="bi bi-search"></i> Tampilkan
              </button>
            </div>
          </form>
        </div>
      </div>

      {{-- =============== LETTERHEAD SELECTION =============== --}}
      @if(isset($items) && count($items) > 0)
        <div class="card shadow-sm border-0 mb-4">
          <div class="card-header bg-light fw-semibold">
            <i class="bi bi-envelope-paper"></i> Pilih Kop Surat
          </div>
          <div class="card-body bg-white p-4">
            <div class="row g-3">
              <div class="col-12">
                <label for="kop_surat" class="form-label fw-semibold">Pilih Kop Surat</label>
                <select name="kop_surat" id="kop_surat" class="form-select shadow-sm">
                  <option value="">-- Pilih Kop Surat --</option>
                  @foreach($kopSurat as $kop)
                    <option value="{{ $kop->id }}"
                            data-logo="{{ asset('storage/'.$kop->logo) }}"
                            data-instansi="{{ $kop->nama_instansi }}"
                            data-unit="{{ $kop->nama_unit }}"
                            data-alamat="{{ $kop->alamat }}"
                            data-telepon="{{ $kop->telepon }}"
                            data-email="{{ $kop->email }}"
                            data-website="{{ $kop->website }}"
                            data-kota="{{ $kop->kota }}">
                      {{ $kop->nama_instansi }} - {{ $kop->nama_unit }}
                    </option>
                  @endforeach
                </select>
              </div>

              {{-- Letterhead Preview Area --}}
              <div class="col-12">
                <div id="kop_preview_full"
                    class="border rounded p-4 mt-3 bg-white text-center text-muted"
                    style="min-height:180px; display:flex; align-items:center; justify-content:center;">
                  <em>Pilih kop surat untuk melihat preview lengkap</em>
                </div>
              </div>
            </div>
          </div>
        </div>
      @endif

      {{-- =============== FILTER RESULTS =============== --}}
      @if(isset($items) && $items->count() > 0)
      <div class="card border-0 shadow-sm rounded-4 mb-4 animate__animated animate__fadeInUp">
        <div class="card-body bg-white p-4 rounded-4">
          <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h6 class="fw-semibold text-secondary mb-0">
              <i class="bi bi-list-check"></i> Data Barang Keluar
              <span class="text-muted">({{ $startDate }} s/d {{ $endDate }})</span>
            </h6>
            <div>
              {{-- EXCEL EXPORT BUTTON --}}
              <a href="#"
                class="btn text-white btn-sm rounded-3 shadow-sm me-2 export-excel"
                style="background-color:#4CAF50;"
                data-start-date="{{ $startDate }}"
                data-end-date="{{ $endDate }}"
                data-route="{{ route('admin.barang_keluar.excel') }}">
                  <i class="bi bi-file-earmark-excel"></i> Excel
              </a>

              {{-- PDF EXPORT BUTTON --}}
              <form method="GET" action="{{ route('admin.barang_keluar.pdf') }}" class="d-inline" id="pdf-form">
                  <input type="hidden" name="start_date" value="{{ $startDate }}">
                  <input type="hidden" name="end_date" value="{{ $endDate }}">
                  <input type="hidden" name="kop_surat" id="kop_surat_hidden">
                  <button type="submit" class="btn text-white btn-sm rounded-3 shadow-sm"
                          style="background-color:#FF9800;" id="pdf-btn">
                      <i class="bi bi-file-earmark-pdf"></i> PDF
                  </button>
              </form>
            </div>
          </div>

          {{-- DATA TABLE --}}
          <div class="table-responsive">
              <table class="table table-bordered align-middle text-center mb-0 table-hover">
                  <thead style="background:#FFF3E0;" class="fw-semibold">
                      <tr class="text-secondary">
                          <th>NO</th>
                          <th>NAMA BARANG</th>
                          <th>PENERIMA</th>
                          <th>ROLE</th>
                          <th>TANGGAL KELUAR</th>
                          <th>JUMLAH</th>
                      </tr>
                  </thead>
                  <tbody>
                      @foreach ($items as $i => $itemOut)
                      @php
                          // Initialize variables
                          $namaBarang = '';
                          $pengambil = '';
                          $jenis = '';

                          /**
                           * Determine data source and extract information
                           * Check if data comes from Item_out or Guest_carts_item
                           */
                          if (isset($itemOut->type)) {
                              if ($itemOut->type === 'pegawai') {
                                  // Data from employee
                                  $namaBarang = $itemOut->item->name ?? 'Barang Dihapus';
                                  $pengambil = $itemOut->cart->user->name ?? 'Tamu/Non-User';
                                  $jenis = 'Pegawai';
                              } else {
                                  // Data from guest
                                  $namaBarang = $itemOut->item->name ?? 'Barang Dihapus';
                                  $pengambil = $itemOut->guestCart->guest->name ?? 'Tamu';
                                  $jenis = 'Tamu';
                              }
                          } else {
                              // Fallback for compatibility
                              $namaBarang = $itemOut->item->name ?? 'Barang Dihapus';
                              $pengambil = $itemOut->cart->user->name ??
                                          ($itemOut->guestCart->guest->name ?? 'Tamu/Non-User');
                              $jenis = isset($itemOut->cart) ? 'Pegawai' : 'Tamu';
                          }
                      @endphp
                      <tr>
                          <td class="fw-semibold text-muted">{{ $i + 1 }}</td>
                          <td class="fw-semibold text-dark">{{ $namaBarang }}</td>
                          <td>{{ $pengambil }}</td>
                          <td>
                            <span class="badge {{ $jenis === 'Pegawai' ? 'bg-primary' : 'bg-success' }}">
                              {{ $jenis }}
                            </span>
                          </td>
                          <td>{{ \Carbon\Carbon::parse($itemOut->released_at ?? $itemOut->created_at)->format('d-m-Y H:i') }}</td>
                          <td class="fw-bold text-warning">{{ $itemOut->quantity }}</td>
                      </tr>
                      @endforeach
                  </tbody>
              </table>
          </div>
        </div>
      </div>
      @elseif(request()->has('start_date') && request()->has('end_date'))
      {{-- NO DATA MESSAGE --}}
      <div class="alert alert-warning border-0 rounded-4 shadow-sm p-3 text-center fw-semibold animate__animated animate__fadeIn">
        <i class="bi bi-exclamation-circle me-1"></i> Tidak ada data barang keluar pada rentang tanggal tersebut.
      </div>
      @endif
    </div>
  </div>
</div>

@endsection

{{-- ======================== --}}
{{-- 📜 JAVASCRIPT SECTION --}}
{{-- ======================== --}}
@push('scripts')
<script src="{{ asset('js/export-barang-keluar.js') }}"></script>
@endpush


{{-- ======================== --}}
{{-- 🎨 CUSTOM STYLES --}}
{{-- ======================== --}}
@push('styles')
<style>
  /* Global Styles */
  body {
    background-color: #fffaf4;
  }

  /* Breadcrumb Styling */
  .breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: #ffb74d;
    margin: 0 6px;
  }

  .breadcrumb-icon {
    transition: 0.3s ease;
  }

  .breadcrumb-icon:hover {
    transform: scale(1.1);
    background-color: #ffecb3;
  }

  /* Animation Classes */
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

  /* Table Styling */
  .table-hover tbody tr:hover {
    background-color: #fff3e0 !important;
    transition: 0.25s ease;
  }

  /* Button Interactions */
  .btn:hover {
    transform: scale(1.03);
    transition: 0.2s ease-in-out;
  }

  /* Responsive Design */
  @media (max-width:768px){
    .breadcrumb-extra{display:none;}
    h4{font-size:1.1rem;}
    table{font-size:0.9rem;}
  }
</style>
@endpush