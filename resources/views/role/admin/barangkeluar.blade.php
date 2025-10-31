@extends('layouts.index')

@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">
    <div class="card shadow-lg border-0 rounded-4 overflow-hidden">

        <div class="card-body bg-light">
            <h4 class="fw-bold mb-4 text-primary">
                <i class="bi bi-box-arrow-up"></i> Export Data Barang Keluar
            </h4>

            {{-- =============== FILTER DATA =============== --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
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

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const startInput = document.querySelector('input[name="start_date"]');
                            const endInput = document.querySelector('input[name="end_date"]');
                            startInput.addEventListener('change', function() {
                                endInput.min = startInput.value;
                            });
                        });
                    </script>
                </div>
            </div>

            {{-- =============== HASIL FILTER =============== --}}
            @if(isset($items) && $items->count() > 0)
                <div class="card border-0 shadow-sm rounded-4 mb-4">
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
                <div class="alert alert-warning rounded-4 shadow-sm p-3 text-center fw-semibold">
                    <i class="bi bi-exclamation-circle"></i> Tidak ada data barang keluar pada rentang tanggal tersebut.
                </div>
            @endif

            {{-- =============== RIWAYAT EXPORT =============== --}}
            <div class="card border-0 shadow-sm rounded-4 mt-4">
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
