@extends('layouts.index')

@section('content')
<div class="container-fluid py-3 animate__animated animate__fadeIn">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3">
                <i class="bi bi-box-arrow-up"></i> Export Data Barang Keluar
            </h5>

            {{-- Filter Data (Menggunakan satu form untuk filter, tombol export di bawah) --}}
            <div class="card bg-light border-0 mb-4">
                <div class="card-body">
                    <h6 class="fw-semibold text-secondary mb-3">
                        <i class="bi bi-funnel"></i> Filter Data
                    </h6>
                    <form method="GET" action="{{ route('admin.export.out') }}" id="filter-form" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Tanggal Mulai</label>
                            <input type="date" name="start_date" value="{{ $startDate ?? '' }}" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Tanggal Akhir</label>
                            <input type="date" name="end_date" value="{{ $endDate ?? '' }}" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
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

            ---

            {{-- Hasil Filter (Preview Table dengan tampilan baru) --}}
            @if(isset($items) && $items->count() > 0)
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body">
                        {{-- Header dan Tombol Export seperti di gambar --}}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-semibold text-secondary mb-0">
                                <i class="bi bi-list-check"></i> Data Barang Keluar ({{ $startDate }} s/d {{ $endDate }})
                            </h6>
                            {{-- Tombol Export (Pastikan route 'admin.export.out.download' tersedia) --}}
                            <div>
                                <a
                                    href="{{ route('admin.barang_keluar.excel', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                                    class="btn btn-success btn-sm me-2"
                                    target="_blank"
                                >
                                    <i class="bi bi-file-earmark-excel"></i> Excel
                                </a>
                                <a
                                    href="{{ route('admin.barang_keluar.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                                    class="btn btn-primary btn-sm"
                                    target="_blank"
                                >
                                    <i class="bi bi-file-earmark-pdf"></i> PDF
                                </a>
                            </div>
                        </div>

                        <div class="table-responsive">
                            {{-- Modifikasi Table Header dan Body --}}
                            <table class="table table-bordered align-middle text-center">
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
                                        <td>{{ $i + 1 }}</td>
                                        {{-- itemOut adalah record dari item_outs --}}
                                        <td>{{ $itemOut->item->name ?? 'Barang Dihapus' }}</td>
                                        <td>{{ $itemOut->quantity }}</td>
                                        <td>{{ \Carbon\Carbon::parse($itemOut->released_at ?? $itemOut->created_at)->format('d-m-Y H:i') }}</td>
                                        {{-- Menampilkan nama pengambil/pengguna dari relasi cart -> user --}}
                                        <td>{{ $itemOut->cart->user->name ?? 'Tamu/Non-User' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            @if(isset($items) && $items->count() > 0)
                {{-- tampilkan tabel (sudah di atas) --}}
            @elseif(request()->has('start_date') && request()->has('end_date'))
                <div class="alert alert-warning">
                    Tidak ada data barang keluar pada rentang tanggal tersebut.
                </div>
            @endif


            ---

            {{-- Riwayat Export (Tidak Berubah) --}}
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-semibold text-secondary mb-0">
                            <i class="bi bi-clock-history"></i> Riwayat Export
                        </h6>
                        <form action="{{ route('admin.export.out.clear') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="bi bi-trash"></i> Bersihkan Riwayat
                            </button>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle table-bordered text-center">
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
                                    <td>{{ strtoupper($export->format) }}</td>
                                    <td>{{ $export->filename }}</td>
                                    <td>{{ \Carbon\Carbon::parse($export->created_at)->format('d/m/Y H:i') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-muted">Belum ada riwayat export.</td>
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