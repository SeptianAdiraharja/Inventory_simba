@extends('layouts.index')

@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">
  <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

    <!-- 🔹 Header -->
    <div class="card-header bg-warning text-white d-flex flex-wrap justify-content-between align-items-center py-3 px-4">
      <h5 class="mb-0 fw-semibold">
        <i class="ri-close-circle-line me-2"></i>Data Barang Rusak / Reject
      </h5>

      <!-- 🔽 Filter Dropdown -->
      <form method="GET" action="{{ route('admin.rejects.index') }}" class="d-flex align-items-center gap-2">
        <label for="condition" class="form-label text-dark mb-0 fw-light fw-semibold">Filter:</label>
        <select id="condition" name="condition" class="form-select form-select-sm border-0 shadow-sm text-dark"
                style="min-width: 180px;" onchange="this.form.submit()">
          <option value="all" {{ $selectedCondition === 'all' ? 'selected' : '' }}>Semua Kondisi</option>
          <option value="rusak ringan" {{ $selectedCondition === 'rusak ringan' ? 'selected' : '' }}>Rusak Ringan</option>
          <option value="rusak berat" {{ $selectedCondition === 'rusak berat' ? 'selected' : '' }}>Rusak Berat</option>
          <option value="tidak bisa digunakan" {{ $selectedCondition === 'tidak bisa digunakan' ? 'selected' : '' }}>Tidak Bisa Digunakan</option>
        </select>
      </form>
    </div>

    <!-- 🔹 Table Body -->
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table align-middle table-hover mb-0">
          <thead class="bg-light text-secondary small text-uppercase">
            <tr>
              <th class="text-center" width="50">#</th>
              <th>Nama Barang</th>
              <th>Asal Item</th>
              <th class="text-center">Jumlah</th>
              <th class="text-center">Kondisi</th>
              <th>Deskripsi</th>
              <th class="text-center">Tanggal Input</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($rejects as $reject)
            <tr>
              <td class="text-center">{{ $loop->iteration }}</td>
              <td class="fw-semibold text-dark">{{ $reject->name }}</td>
              <td>{{ $reject->item->name ?? '-' }}</td>
              <td class="text-center">{{ $reject->quantity }}</td>
              <td class="text-center">
                @php
                  $color = match($reject->condition) {
                    'rusak berat' => 'danger',
                    'rusak ringan' => 'warning',
                    'tidak bisa digunakan' => 'secondary',
                    default => 'light',
                  };
                @endphp
                <span class="badge bg-{{ $color }} px-3 py-2 text-capitalize shadow-sm">
                  {{ $reject->condition }}
                </span>
              </td>
              <td class="text-muted">{{ $reject->description ?? '-' }}</td>
              <td class="text-center text-secondary">{{ $reject->created_at->format('d M Y H:i') }}</td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center py-4 text-muted">
                <i class="ri-inbox-2-line fs-4 d-block mb-1"></i>
                Belum ada data barang rusak.
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <!-- 🔹 Footer Info -->
    @if ($rejects->count() > 0)
    <div class="card-footer bg-danger text-end small text-muted py-2 px-4">
      Total: <strong>{{ $rejects->count() }}</strong> data barang rusak
    </div>
    @endif
  </div>
</div>
@endsection
