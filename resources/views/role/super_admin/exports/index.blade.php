@extends('layouts.index')
@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- ======================== --}}
  {{-- 🧭 MODERN BREADCRUMB --}}
  {{-- ======================== --}}
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap align-items-center justify-content-between smooth-fade">
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <i class="bi bi-file-earmark-arrow-down fs-5 text-primary"></i>
      <a href="{{ route('dashboard') }}" class="breadcrumb-link fw-semibold text-primary text-decoration-none">
        Dashboard
      </a>
      <span class="text-muted">/</span>
      <span class="text-muted">Export Data Barang</span>
    </div>
    <a href="{{ route('super_admin.kop_surat.index') }}" class="btn btn-sm btn-secondary rounded-pill d-flex align-items-center gap-2 shadow-sm hover-glow">
      <i class="bi bi-envelope-paper"></i> Kelola Kop Surat
    </a>
  </div>

  {{-- 🔹 Filter Form --}}
  <div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-light fw-semibold">
      <i class="bi bi-funnel"></i> Filter Data
    </div>
    <div class="card-body bg-white">
      <form action="{{ route('super_admin.export.index') }}" method="GET">
        <div class="row g-3 align-items-end">
          <div class="col-md-3">
            <label for="start_date" class="form-label fw-semibold">Tanggal Mulai</label>
            <input type="date" name="start_date" id="start_date"
                   value="{{ request('start_date') }}" class="form-control shadow-sm" required>
          </div>

          <div class="col-md-3">
            <label for="period" class="form-label fw-semibold">Periode</label>
            <select name="period" id="period" class="form-select shadow-sm" required>
              <option value="">-- Periode --</option>
              <option value="weekly"  {{ request('period')=='weekly'  ? 'selected' : '' }}>1 Minggu</option>
              <option value="monthly" {{ request('period')=='monthly' ? 'selected' : '' }}>1 Bulan</option>
              <option value="yearly"  {{ request('period')=='yearly'  ? 'selected' : '' }}>1 Tahun</option>
            </select>
          </div>

          <div class="col-md-3">
            <label for="type" class="form-label fw-semibold">Jenis Data</label>
            <select name="type" id="type" class="form-select shadow-sm">
              <option value="masuk"  {{ request('type')=='masuk'  ? 'selected' : '' }}>Barang Masuk</option>
              <option value="keluar" {{ request('type')=='keluar' ? 'selected' : '' }}>Barang Keluar</option>
              <option value="reject" {{ request('type')=='reject' ? 'selected' : '' }}>Barang Reject</option>
            </select>
          </div>

          <div class="col-12 col-md-3 mt-3 mt-md-0 text-end">
            <button type="submit" class="btn btn-primary w-100 shadow-sm">
              <i class="bi bi-search"></i> Tampilkan
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  {{-- 🔹 Pilihan Kop Surat --}}
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

  {{-- 🔹 Tabel Data --}}
  @if(isset($items) && count($items) > 0)
  <div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
      <h6 class="mb-0 fw-semibold">
        <i class="bi bi-table"></i> Data
        <span class="text-muted">({{ count($items) }} data)</span>
      </h6>
      <div class="btn-group">
        <a href="{{ route('super_admin.export.download', array_merge(request()->query(), ['format' => 'excel'])) }}"
           class="btn btn-success btn-sm shadow-sm">
          <i class="bi bi-file-earmark-excel"></i> Excel
        </a>
        <a href="{{ route('super_admin.export.download', array_merge(request()->query(), ['format' => 'pdf'])) }}"
           class="btn btn-danger btn-sm shadow-sm">
          <i class="bi bi-file-earmark-pdf"></i> PDF
        </a>
      </div>
    </div>

    <div class="card-body table-responsive bg-white">
      <table class="table table-bordered table-hover align-middle text-center">
        <thead class="table-primary">
          <tr>
            <th>No</th>
            <th>Nama Barang</th>
            @if(request('type') == 'masuk')
              <th>Supplier</th><th>Tanggal Masuk</th><th>Jumlah</th><th>Satuan</th><th>Harga Satuan</th><th>Total Harga</th>
            @elseif(request('type') == 'keluar')
              <th>Role</th><th>Dikeluarkan Oleh</th><th>Penerima</th><th>Tanggal Keluar</th><th>Jumlah</th><th>Satuan</th><th>Harga Satuan</th><th>Total Harga</th>
            @elseif(request('type') == 'reject')
              <th>Status</th><th>Tanggal Reject</th><th>Jumlah</th><th>Harga Satuan</th><th>Total Harga</th>
            @endif
          </tr>
        </thead>
        <tbody>
          @foreach($items as $i => $row)
          <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $row->item->name ?? '-' }}</td>
            @if(request('type') == 'masuk')
              <td>{{ $row->supplier->name ?? '-' }}</td>
              <td>{{ optional($row->created_at)->format('d-m-Y H:i') }}</td>
              <td>{{ $row->quantity }}</td>
              <td>{{ $row->item->unit->name ?? '-' }}</td>
              <td>Rp {{ number_format($row->item->price,0,',','.') }}</td>
              <td>Rp {{ number_format($row->total_price,0,',','.') }}</td>
            @elseif(request('type') == 'keluar')
              <td>{{ $row->role }}</td>
              <td>{{ $row->dikeluarkan }}</td>
              <td>{{ $row->penerima }}</td>
              <td>{{ \Carbon\Carbon::parse($row->created_at)->format('d-m-Y H:i') }}</td>
              <td>{{ $row->quantity }}</td>
              <td>{{ $row->item->unit->name ?? '-' }}</td>
              <td>Rp {{ number_format($row->item->price,0,',','.') }}</td>
              <td>Rp {{ number_format($row->total_price,0,',','.') }}</td>
            @elseif(request('type') == 'reject')
              <td>{{ $row->role }}</td>
              <td>{{ optional($row->created_at)->format('d-m-Y H:i') }}</td>
              <td>{{ $row->quantity }}</td>
              <td>Rp {{ number_format($row->item->price,0,',','.') }}</td>
              <td>Rp {{ number_format($row->total_price,0,',','.') }}</td>
            @endif
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  @elseif(request()->has('start_date'))
  <div class="alert alert-warning shadow-sm">
    <i class="bi bi-exclamation-triangle"></i> Tidak ada data ditemukan untuk periode ini.
  </div>
  @endif

</div>

{{-- 💅 Style --}}
<style>
.smooth-fade {
  animation: fadeIn 0.6s ease-in-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
.hover-glow {
  transition: all 0.25s ease;
}
.hover-glow:hover {
  background-color: #7d0dfd !important;
  color: #fff !important;
  box-shadow: 0 0 10px rgba(125, 13, 253, 0.3);
}
.breadcrumb-link {
  position: relative;
  transition: all 0.25s ease;
}
.breadcrumb-link::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  width: 0;
  height: 2px;
  background: #7d0dfd;
  transition: width 0.25s ease;
}
.breadcrumb-link:hover::after {
  width: 100%;
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const kopSelect = document.getElementById('kop_surat');
  const previewDiv = document.getElementById('kop_preview_full');
  const excelBtn = document.querySelector('a[href*="format=excel"]');
  const pdfBtn = document.querySelector('a[href*="format=pdf"]');

  if (kopSelect) kopSelect.selectedIndex = 0;

  kopSelect?.addEventListener('change', () => {
    const opt = kopSelect.options[kopSelect.selectedIndex];
    if (!opt.value) {
      previewDiv.innerHTML = `<em>Pilih kop surat untuk melihat preview lengkap</em>`;
      return;
    }

    previewDiv.innerHTML = `
      <table style="width:100%; border:none;">
        <tr>
          <td style="width:120px; text-align:center;">
            <img src="${opt.dataset.logo}" style="width:90px; height:100px; object-fit:contain;">
          </td>
          <td style="text-align:center; vertical-align:middle; line-height:1.5;">
            <div style="font-size:14px; font-weight:600;">${opt.dataset.instansi.toUpperCase()}</div>
            <div style="font-size:18px; font-weight:900; margin-top:4px;">${opt.dataset.unit.toUpperCase()}</div>
            <div style="font-size:13px; margin-top:4px;">
              ${opt.dataset.alamat}<br>
              Telepon: ${opt.dataset.telepon} | Email: ${opt.dataset.email}<br>
              ${opt.dataset.website ? `Website: ${opt.dataset.website}<br>` : ''}
              ${opt.dataset.kota}
            </div>
          </td>
          <td style="width:120px;"></td>
        </tr>
      </table>
    `;
    updateExportLinks();
  });

  function updateExportLinks() {
    const kopId = kopSelect?.value;
    [excelBtn, pdfBtn].forEach(btn => {
      if (!btn) return;
      const url = new URL(btn.href);
      kopId ? url.searchParams.set('kop_surat', kopId) : url.searchParams.delete('kop_surat');
      btn.href = url.toString();
    });
  }
});
</script>
@endpush
@endsection
