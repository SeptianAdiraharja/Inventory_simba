@extends('layouts.index')

@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- ======================== --}}
  {{-- 🧭 BREADCRUMB MODERN --}}
  {{-- ======================== --}}
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3 animate__animated animate__fadeInDown smooth-fade">
    <div class="d-flex align-items-center gap-2">
      <div class="breadcrumb-icon bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center rounded-circle" style="width:38px;height:38px;">
        <i class="bi bi-house-door-fill fs-5"></i>
      </div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 align-items-center">
          <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}" class="text-decoration-none text-primary fw-semibold">
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
  {{-- 📦 KONTEN UTAMA --}}
  {{-- ======================== --}}
  <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
    <div class="card-body bg-light">
      <h4 class="fw-bold mb-4 text-primary">
        <i class="bi bi-box-arrow-up"></i> Export Data Barang Keluar
      </h4>

      {{-- =============== FILTER DATA =============== --}}
      <div class="card border-0 shadow-sm rounded-4 mb-4 animate__animated animate__fadeInUp">
        <div class="card-body bg-white p-4 rounded-4">
          <h6 class="fw-semibold text-secondary mb-3">
            <i class="bi bi-funnel"></i> Filter Data
          </h6>
          <form method="GET" action="{{ route('admin.export.out') }}" id="filter-form" class="row g-3 align-items-end">
            <div class="col-md-3">
              <label class="form-label fw-semibold">Tanggal Mulai</label>
              <input type="date" name="start_date" value="{{ $startDate ?? '' }}" class="form-control shadow-sm rounded-3" required>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Tanggal Akhir</label>
              <input type="date" name="end_date" value="{{ $endDate ?? '' }}" class="form-control shadow-sm rounded-3" required>
            </div>
            <div class="col-md-3">
              <button type="submit" class="btn btn-primary w-100 rounded-3 fw-semibold shadow-sm">
                <i class="bi bi-search"></i> Tampilkan
              </button>
            </div>
          </form>
        </div>
      </div>

      {{-- =============== HASIL FILTER =============== --}}
      @if(isset($items) && $items->count() > 0)
      <div class="card border-0 shadow-sm rounded-4 mb-4 animate__animated animate__fadeInUp">
        <div class="card-body bg-white p-4 rounded-4">
          <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h6 class="fw-semibold text-secondary mb-0">
              <i class="bi bi-list-check"></i> Data Barang Keluar
              <span class="text-muted">({{ $startDate }} s/d {{ $endDate }})</span>
            </h6>
            <div>
              <a href="{{ route('admin.barang_keluar.excel', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                 class="btn btn-success btn-sm rounded-3 shadow-sm me-2" target="_blank">
                <i class="bi bi-file-earmark-excel"></i> Excel
              </a>
              <a href="{{ route('admin.barang_keluar.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                 class="btn btn-primary btn-sm rounded-3 shadow-sm" target="_blank">
                <i class="bi bi-file-earmark-pdf"></i> PDF
              </a>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-bordered align-middle text-center mb-0">
              <thead class="table-primary">
                <tr>
                  <th>NO</th>
                  <th>NAMA BARANG</th>
                  <th>JUMLAH</th>
                  <th>TANGGAL KELUAR</th>
                  <th>PENGAMBIL</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($items as $i => $itemOut)
                <tr>
                  <td class="fw-semibold text-secondary">{{ $i + 1 }}</td>
                  <td class="fw-semibold text-dark">{{ $itemOut->item->name ?? 'Barang Dihapus' }}</td>
                  <td class="text-primary fw-bold">{{ $itemOut->quantity }}</td>
                  <td>{{ \Carbon\Carbon::parse($itemOut->released_at ?? $itemOut->created_at)->format('d-m-Y H:i') }}</td>
                  <td>{{ $itemOut->cart->user->name ?? 'Tamu/Non-User' }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
      @elseif(request()->has('start_date') && request()->has('end_date'))
      <div class="alert alert-warning rounded-4 shadow-sm p-3 text-center fw-semibold animate__animated animate__fadeIn">
        <i class="bi bi-exclamation-circle"></i> Tidak ada data barang keluar pada rentang tanggal tersebut.
      </div>
      @endif

      {{-- =============== RIWAYAT EXPORT =============== --}}
      <div class="card border-0 shadow-sm rounded-4 mt-4 animate__animated animate__fadeInUp">
        <div class="card-body bg-white p-4 rounded-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-semibold text-secondary mb-0">
              <i class="bi bi-clock-history"></i> Riwayat Export
            </h6>
            <form action="{{ route('admin.export.out.clear') }}" method="POST">
              @csrf
              <button type="submit" class="btn btn-danger btn-sm rounded-3 shadow-sm">
                <i class="bi bi-trash"></i> Bersihkan Riwayat
              </button>
            </form>
          </div>

          <div class="table-responsive">
            <table class="table align-middle table-bordered text-center mb-0">
              <thead class="table-light">
                <tr>
                  <th>NO</th>
                  <th>FORMAT</th>
                  <th>NAMA FILE</th>
                  <th>TANGGAL EXPORT</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($exports as $index => $export)
                <tr>
                  <td>{{ $index + 1 }}</td>
                  <td><span class="badge bg-primary px-3 py-2">{{ strtoupper($export->format) }}</span></td>
                  <td class="fw-medium text-dark">{{ $export->filename }}</td>
                  <td class="text-muted">{{ \Carbon\Carbon::parse($export->created_at)->format('d/m/Y H:i') }}</td>
                </tr>
                @empty
                <tr>
                  <td colspan="4" class="text-muted py-3">
                    <i class="bi bi-info-circle"></i> Belum ada riwayat export.
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
</div>

@endsection

@push('styles')
<style>
  body {
    background-color: #f4f6f9;
  }

  .breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: #6c757d;
    margin: 0 6px;
  }

  .smooth-fade {
    animation: smoothFade 0.8s ease;
  }

  @keyframes smoothFade {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .breadcrumb-icon {
    transition: 0.3s ease;
  }

  .breadcrumb-icon:hover {
    transform: scale(1.1);
    background-color: #e8f0fe;
  }

  .table-hover tbody tr:hover {
    background-color: #f0f8ff;
    transition: 0.2s ease;
  }

  .btn {
    transition: 0.2s ease;
  }

  .btn:hover {
    opacity: 0.9;
  }

  @media (max-width: 768px) {
    .breadcrumb-extra { display: none; }
    h4.fw-bold { font-size: 1.1rem; }
    table { font-size: 0.9rem; }
    .btn-sm { padding: 0.35rem 0.75rem; }
  }
</style>
@endpush
