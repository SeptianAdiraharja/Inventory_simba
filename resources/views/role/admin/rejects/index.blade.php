@extends('layouts.index')

@section('content')
<div class="container-fluid py-4">
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
      <h5 class="mb-0"><i class="ri-close-circle-line me-2"></i>Data Barang Rusak / Reject</h5>

      <!-- 🔽 Filter Dropdown -->
      <form method="GET" action="{{ route('admin.rejects.index') }}" class="d-flex align-items-center gap-2">
        <select name="condition" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="all" {{ $selectedCondition === 'all' ? 'selected' : '' }}>Semua Kondisi</option>
          <option value="rusak ringan" {{ $selectedCondition === 'rusak ringan' ? 'selected' : '' }}>Rusak Ringan</option>
          <option value="rusak berat" {{ $selectedCondition === 'rusak berat' ? 'selected' : '' }}>Rusak Berat</option>
          <option value="tidak bisa digunakan" {{ $selectedCondition === 'tidak bisa digunakan' ? 'selected' : '' }}>Tidak Bisa Digunakan</option>
        </select>
      </form>
    </div>

    <div class="card-body p-2">
      <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
          <thead class="table-dark">
            <tr>
              <th>#</th>
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
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $reject->name }}</td>
              <td>{{ $reject->item->name ?? '-' }}</td>
              <td>{{ $reject->quantity }}</td>
              <td>
                <span class="badge
                  @if($reject->condition === 'rusak berat') bg-danger
                  @elseif($reject->condition === 'rusak ringan') bg-warning
                  @else bg-secondary @endif">
                  {{ ucfirst($reject->condition) }}
                </span>
              </td>
              <td>{{ $reject->description ?? '-' }}</td>
              <td>{{ $reject->created_at->format('d M Y H:i') }}</td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center text-muted">Belum ada data barang rusak.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
