@extends('layouts.index')

@section('content')
<div class="container mt-4">

    {{-- 🔹 Judul Halaman --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold text-primary mb-0">
            <i class="bi bi-box-seam"></i> Export Data Barang
        </h4>
        <small class="text-muted">Kelola laporan barang masuk & keluar</small>
    </div>
    <a href="{{ route('super_admin.export.index') }}" class="btn btn-outline-secondary shadow-sm">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

{{-- 🔹 Filter Form --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-primary text-white">
        <strong><i class="bi bi-funnel"></i> Filter Data</strong>
    </div>
    <div class="card-body bg-light">
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
                        <option value="masuk" {{ request('type')=='masuk' ? 'selected' : '' }}>Barang Masuk</option>
                        <option value="keluar" {{ request('type')=='keluar' ? 'selected' : '' }}>Barang Keluar</option>
                    </select>
                </div>

                <div class="col-md-3 text-end">
                    <button type="submit" class="btn btn-primary w-100 shadow-sm">
                        <i class="bi bi-search"></i> Tampilkan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- 🔹 Preview Data --}}
@if(isset($items) && count($items) > 0)
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold">
                <i class="bi bi-table"></i> Data 
                {{ request('type') == 'masuk' ? 'Barang Masuk' : 'Barang Keluar' }}
                <span class="text-muted">({{ count($items) }} data)</span>
            </h6>
            <div class="btn-group">
                <a href="{{ route('super_admin.export.download', [
                    'start_date' => request('start_date'),
                    'period'     => request('period'),
                    'type'       => request('type'),
                    'format'     => 'excel'
                ]) }}" class="btn btn-success btn-sm shadow-sm">
                    <i class="bi bi-file-earmark-excel"></i> Excel
                </a>
                <a href="{{ route('super_admin.export.download', [
                    'start_date' => request('start_date'),
                    'period'     => request('period'),
                    'type'       => request('type'),
                    'format'     => 'pdf'
                ]) }}" class="btn btn-danger btn-sm shadow-sm">
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                </a>
            </div>
        </div>

        {{-- 🔹 Tabel Data --}}
        <div class="card-body table-responsive bg-white">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-primary text-center">
                    <tr>
                        <th>No</th>
                        <th>Nama Barang</th>
                        <th>Jumlah</th>
                        <th>Satuan</th>
                        <th>Harga Satuan</th>
                        <th>Total</th>
                        @if(request('type') == 'masuk')
                            <th>Supplier</th>
                            <th>Tanggal Masuk</th>
                        @else
                            <th>Role</th>
                            <th>Dikeluarkan Oleh</th>
                            <th>Penerima</th>
                            <th>Tanggal Keluar</th>
                        @endif
                    </tr>
                </thead>

                <tbody>
                    @foreach($items as $i => $row)
                        @php
                            $role = $row->role ?? ($row->guestCart ? 'Guest' : 'Pegawai');
                            $dikeluarkanOleh = $row->approver->name ?? 'Petugas Gudang';
                            $penerima = '-';
                            if (!empty($row->cart?->user?->name)) {
                                $penerima = $row->cart->user->name;
                            } elseif (!empty($row->guestCart?->guest?->name)) {
                                $penerima = $row->guestCart->guest->name;
                            } elseif (!empty($row->user?->name)) {
                                $penerima = $row->user->name;
                            } elseif (!empty($row->guest?->name)) {
                                $penerima = $row->guest->name;
                            }
                        @endphp
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td>{{ $row->item->name ?? '-' }}</td>
                            <td class="text-center">{{ $row->quantity ?? 0 }}</td>
                            <td class="text-center">{{ $row->item->unit->name ?? '-' }}</td>
                            <td class="text-end">Rp {{ number_format($row->item->price ?? 0, 0, ',', '.') }}</td>
                            <td class="text-end fw-semibold">Rp {{ number_format($row->total_price ?? 0, 0, ',', '.') }}</td>

                            @if(request('type') == 'masuk')
                                <td>{{ $row->supplier->name ?? '-' }}</td>
                                <td class="text-center">{{ optional($row->created_at)->format('d-m-Y H:i') }}</td>
                            @else
                                <td class="text-center">{{ $role }}</td>
                                <td>{{ $dikeluarkanOleh }}</td>
                                <td>{{ $penerima }}</td>
                                <td class="text-center">{{ optional($row->created_at)->format('d-m-Y H:i') }}</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>

                {{-- 🔹 Total Keseluruhan --}}
                <tfoot class="table-light fw-semibold">
                    <tr>
                        <td colspan="{{ request('type')=='masuk' ? 6 : 8 }}" class="text-end">TOTAL</td>
                        <td class="text-end">
                            Rp {{ number_format($items->sum('total_price') ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@elseif(request()->has('start_date'))
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i> Tidak ada data ditemukan untuk periode ini.
    </div>
@endif

{{-- 🔹 Riwayat Export --}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-clock-history"></i> Riwayat Export</h6>
        <form action="{{ route('super_admin.export.clear') }}" method="POST"
            onsubmit="return confirm('Apakah Anda yakin ingin menghapus semua riwayat export?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger">
                <i class="bi bi-trash"></i> Bersihkan Riwayat
            </button>
        </form>
    </div>
    <div class="card-body table-responsive bg-white">
        <table class="table table-striped align-middle text-center">
            <thead class="table-secondary">
                <tr>
                    <th>No</th>
                    <th>Format</th>
                    <th>Jenis</th>
                    <th>Tanggal Export</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $i => $log)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>
                            <span class="badge {{ $log->format == 'excel' ? 'bg-success' : 'bg-danger' }}">
                                {{ strtoupper($log->format) }}
                            </span>
                        </td>
                        <td>{{ strtoupper($log->data_type ?? '-') }}</td>
                        <td>{{ $log->created_at->format('d-m-Y H:i') }}</td>
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
@endsection