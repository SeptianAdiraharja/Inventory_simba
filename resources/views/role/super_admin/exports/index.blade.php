@extends('layouts.index')

@section('content')
<div class="container mt-4">
    <h4 class="mb-4">📦 Export Data Barang</h4>

    {{-- Filter Form --}}
    <form action="{{ route('super_admin.export.index') }}" method="GET" class="mb-4">
         <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="start_date" class="form-label">Tanggal Mulai</label>
                <input type="date" name="start_date" id="start_date"
                    value="{{ request('start_date') }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label">Tanggal Akhir</label>
                <input type="date" name="end_date" id="end_date"
                    value="{{ request('end_date') }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label for="type" class="form-label">Jenis Data</label>
                <select name="type" id="type" class="form-select">
                    <option value="masuk" {{ request('type')=='masuk' ? 'selected' : '' }}>Barang Masuk</option>
                    <option value="keluar" {{ request('type')=='keluar' ? 'selected' : '' }}>Barang Keluar</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="format" class="form-label">Format</label>
                <select name="format" id="format" class="form-select">
                    <option value="excel" {{ request('format')=='excel' ? 'selected' : '' }}>Excel</option>
                    <option value="pdf" {{ request('format')=='pdf' ? 'selected' : '' }}>PDF</option>
                </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-search"></i> Tampilkan
                    </button>
                </div>
        </div>
    </form>


    {{-- Preview Data --}}
    @if(isset($items) && count($items) > 0)
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light d-flex justify-content-between">
                <h5 class="mb-0">
                    Preview Data {{ request('type')=='masuk' ? 'Barang Masuk' : 'Barang Keluar' }}
                    ({{ count($items) }} records)
                    @isset($period)
                        - Periode: {{ ucfirst($period) }}
                    @elseif(isset($startDate) && isset($endDate))
                        - {{ $startDate }} s/d {{ $endDate }}
                    @endif
                </h5>
                <div>
                    <a href="{{ route('super_admin.export.download', [
                        'start_date' => request('start_date'),
                        'end_date'   => request('end_date'),
                        'type'       => request('type'),
                        'format'     => request('format','excel')
                    ]) }}"
                    class="btn btn-{{ request('format')=='pdf' ? 'danger' : 'success' }} btn-sm">
                        <i class="bi bi-file-earmark-{{ request('format')=='pdf' ? 'pdf' : 'excel' }}"></i>
                        Export {{ strtoupper(request('format','excel')) }}
                    </a>
                    <a href="{{ route('super_admin.export.download', [
                        'start_date' => request('start_date'),
                        'end_date'   => request('end_date'),
                        'type'       => request('type'),
                        'format'     => 'pdf'
                    ]) }}" class="btn btn-danger btn-sm">
                        <i class="bi bi-file-earmark-pdf"></i> PDF
                    </a>
                </div>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-sm align-middle">
                    <thead class="table-secondary">
                        <tr>
                            <th>#</th>
                            <th>Nama Barang</th>
                            <th>Qty</th>
                            <th>Harga</th>
                            <th>Total</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $i => $row)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>{{ $row->item->name }}</td>
                            <td>{{ $row->quantity }}</td>
                            <td>Rp {{ number_format($row->item->price,0,',','.') }}</td>
                            <td>Rp {{ number_format($row->total_price,0,',','.') }}</td>
                            <td>{{ $row->created_at->format('d-m-Y H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @elseif(request()->has('start_date'))
        <div class="alert alert-warning">⚠️ Tidak ada data ditemukan untuk periode ini.</div>
    @endif


    {{-- Log Export --}}
    <div class="card shadow-sm">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Riwayat Export</h5>
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-funnel-fill"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item {{ !request('filter_format') ? 'active' : '' }}"
                        href="{{ route('super_admin.export.index') }}">
                            Semua
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item {{ request('filter_format')=='excel' ? 'active' : '' }}"
                        href="{{ request()->fullUrlWithQuery(['filter_format' => 'excel']) }}">
                            Excel
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item {{ request('filter_format')=='pdf' ? 'active' : '' }}"
                        href="{{ request()->fullUrlWithQuery(['filter_format' => 'pdf']) }}">
                            PDF
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-sm table-striped">
                <thead class="table-secondary">
                    <tr>
                        <th>No</th>
                        <th>Format</th>
                        <th>File</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($logs as $i => $log)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>{{ strtoupper($log->format) }}</td>
                            <td>{{ $log->file_path }}</td>
                            <td>{{ $log->created_at->format('d-m-Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Belum ada riwayat export.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
