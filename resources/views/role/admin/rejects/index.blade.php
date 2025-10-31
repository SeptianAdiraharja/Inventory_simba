@extends('layouts.index')

@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  <!-- 🔶 CARD UTAMA -->
  <div class="card border-0 shadow-lg rounded-4 overflow-hidden bg-white">

    <!-- 🔹 HEADER -->
    <div class="card-header bg-gradient d-flex flex-wrap justify-content-between align-items-center py-3 px-4"
         style="background: linear-gradient(90deg, #f8b400, #ffd369);">
      <h5 class="mb-0 fw-semibold text-dark d-flex align-items-center">
        <i class="ri-close-circle-line me-2 fs-4 text-danger"></i>Data Barang Rusak / Reject
      </h5>

      <!-- 🔽 FILTER -->
      <form method="GET" action="{{ route('admin.rejects.index') }}" class="d-flex align-items-center gap-2">
        <label for="condition" class="form-label text-dark mb-0 fw-semibold">Filter:</label>
        <select id="condition" name="condition"
                class="form-select form-select-sm border-0 shadow-sm text-dark rounded-pill px-3"
                style="min-width: 200px; background-color: #fff;" onchange="this.form.submit()">
          <option value="all" {{ $selectedCondition === 'all' ? 'selected' : '' }}>Semua Kondisi</option>
          <option value="rusak ringan" {{ $selectedCondition === 'rusak ringan' ? 'selected' : '' }}>Rusak Ringan</option>
          <option value="rusak berat" {{ $selectedCondition === 'rusak berat' ? 'selected' : '' }}>Rusak Berat</option>
          <option value="tidak bisa digunakan" {{ $selectedCondition === 'tidak bisa digunakan' ? 'selected' : '' }}>Tidak Bisa Digunakan</option>
        </select>
      </form>
    </div>

    <!-- 🔹 BODY -->
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table align-middle table-hover mb-0">
          <thead class="text-center text-uppercase small fw-semibold"
                 style="background-color: #f1f5f9; color: #444;">
            <tr>
              <th width="50">#</th>
              <th>Nama Barang</th>
              <th>Asal Item</th>
              <th>Jumlah</th>
              <th>Kondisi</th>
              <th>Deskripsi</th>
              <th>Tanggal Input</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($rejects as $reject)
            <tr class="text-center border-bottom">
              <td class="text-secondary">{{ $loop->iteration }}</td>
              <td class="fw-semibold text-dark">{{ $reject->name }}</td>
              <td class="text-secondary">{{ $reject->item->name ?? '-' }}</td>
              <td class="fw-semibold text-dark">{{ $reject->quantity }}</td>
              <td>
                @php
                  $color = match($reject->condition) {
                    'rusak berat' => 'danger',
                    'rusak ringan' => 'warning',
                    'tidak bisa digunakan' => 'secondary',
                    default => 'light',
                  };
                @endphp
                <span class="badge bg-{{ $color }} text-capitalize px-3 py-2 shadow-sm">
                  {{ $reject->condition }}
                </span>
              </td>
              <td class="text-muted text-start ps-3">{{ $reject->description ?? '-' }}</td>
              <td class="text-secondary">{{ $reject->created_at->format('d M Y H:i') }}</td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center py-5">
                <i class="ri-inbox-2-line fs-3 text-muted d-block mb-2"></i>
                <span class="text-muted">Belum ada data barang rusak.</span>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <!-- 🔹 FOOTER -->
    @if ($rejects->count() > 0)
    <div class="card-footer bg-light text-end small text-secondary py-2 px-4 border-0">
      Total: <strong class="text-dark">{{ $rejects->count() }}</strong> data barang rusak
    </div>
    @endif
  </div>
</div>
@endsection
