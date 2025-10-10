@extends('layouts.index')

@section('content')
<div class="container mt-4">
    <h4 class="mb-4">
        <i class="bi bi-box-seam"></i> Export Data Barang
    </h4>

    {{-- Form Filter --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <strong><i class="bi bi-funnel"></i> Filter Data</strong>
        </div>
        <div class="card-body">
            <form action="{{ route('super_admin.export.index') }}" method="GET">
                <div class="row g-3 align-items-end">
                    {{-- Tanggal Mulai --}}
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Tanggal Mulai</label>
                        <input type="date" name="start_date" id="start_date"
                            value="{{ request('start_date') }}" class="form-control" required>
                    </div>

                    {{-- Tanggal Akhir --}}
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">Tanggal Akhir</label>
                        <select name="end_date" id="end_date" class="form-select" required>
                            <option value="">-- Pilih Tanggal Akhir --</option>
                        </select>
                    </div>

                    {{-- Jenis Data --}}
                    <div class="col-md-2">
                        <label for="type" class="form-label">Jenis Data</label>
                        <select name="type" id="type" class="form-select">
                            <option value="masuk" {{ request('type')=='masuk' ? 'selected' : '' }}>Barang Masuk</option>
                            <option value="keluar" {{ request('type')=='keluar' ? 'selected' : '' }}>Barang Keluar</option>
                        </select>
                    </div>

                    {{-- Tombol --}}
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Tampilkan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Preview Data --}}
    @if(isset($items) && count($items) > 0)
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-table"></i> Data 
                    {{ request('type')=='masuk' ? 'Barang Masuk' : 'Barang Keluar' }}
                    ({{ count($items) }} data)
                </h6>
                <div class="btn-group">
                    <a href="{{ route('super_admin.export.download', [
                        'start_date' => request('start_date'),
                        'end_date'   => request('end_date'),
                        'type'       => request('type'),
                        'format'     => 'excel'
                    ]) }}" class="btn btn-success btn-sm me-2">
                        <i class="bi bi-file-earmark-excel"></i> Excel
                    </a>
                    <a href="{{ route('super_admin.export.download', [
                        'start_date' => request('start_date'),
                        'end_date'   => request('end_date'),
                        'type'       => request('type'),
                        'format'     => 'pdf'
                    ]) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-file-earmark-pdf"></i> PDF
                    </a>
                </div>
            </div>

            {{-- Tabel Data --}}
            <div class="card-body table-responsive">
                <table class="table table-bordered table-hover table-sm align-middle">
                    <thead class="table-secondary">
                        <tr class="text-center">
                            <th>No</th>
                            <th>Nama Barang</th>
                            @if(request('type') == 'masuk')
                                <th>Supplier</th>
                                <th>Tanggal Masuk</th>
                            @else
                                <th>Dikeluarkan Oleh</th>
                                <th>Tanggal Keluar</th>
                            @endif
                            <th>Jumlah</th>
                            <th>Satuan Barang</th>
                            <th>Harga Satuan</th>
                            <th>Total Harga</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $i => $row)
                            <tr>
                                <td class="text-center">{{ $i+1 }}</td>
                                <td>{{ $row->item->name }}</td>
                                
                                {{-- Supplier atau User --}}
                                @if(request('type') == 'masuk')
                                    <td>{{ $row->supplier->name ?? '-' }}</td>
                                @else
                                    <td>{{ $row->user->name ?? '-' }}</td>
                                @endif

                                {{-- Tanggal --}}
                                <td>{{ $row->created_at->format('d-m-Y H:i') }}</td>

                                {{-- Jumlah & Satuan --}}
                                <td class="text-center">{{ $row->quantity }}</td>
                                <td class="text-center">{{ $row->item->unit->name ?? '-' }}</td>

                                {{-- Harga --}}
                                <td>Rp {{ number_format($row->item->price, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($row->total_price, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @elseif(request()->has('start_date'))
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i> Tidak ada data ditemukan untuk periode ini.
        </div>
    @endif

    {{-- Riwayat Export --}}
    <div class="card shadow-sm">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="bi bi-clock-history"></i> Riwayat Export</h6>
            <form action="{{ route('super_admin.export.clear') }}" method="POST"
                onsubmit="return confirm('Apakah Anda yakin ingin menghapus semua riwayat export?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger">
                    <i class="bi bi-trash"></i> Bersihkan Riwayat
                </button>
            </form>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-sm table-striped align-middle">
                <thead class="table-secondary">
                    <tr class="text-center">
                        <th>No</th>
                        <th>Format</th>
                        <th>Nama File</th>
                        <th>Tanggal Export</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $i => $log)
                        <tr>
                            <td class="text-center">{{ $i+1 }}</td>
                            <td class="text-center">
                                <span class="badge {{ $log->format == 'excel' ? 'bg-success' : 'bg-danger' }}">
                                    {{ strtoupper($log->format) }}
                                </span>
                            </td>
                            <td>{{ $log->file_path }}</td>
                            <td>{{ $log->created_at->format('d-m-Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">Belum ada riwayat export.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Script untuk generate tanggal akhir --}}
<script>
document.addEventListener("DOMContentLoaded", function () {
    const startDateInput = document.getElementById("start_date");
    const endDateSelect = document.getElementById("end_date");

    startDateInput.addEventListener("change", function () {
        endDateSelect.innerHTML = '<option value="">-- Pilih Tanggal Akhir --</option>';

        if (!this.value) return;

        let startDate = new Date(this.value);

        for (let i = 0; i < 7; i++) {
            let optionDate = new Date(startDate);
            optionDate.setDate(startDate.getDate() + i);

            let yyyy = optionDate.getFullYear();
            let mm = String(optionDate.getMonth() + 1).padStart(2, '0');
            let dd = String(optionDate.getDate()).padStart(2, '0');

            let formatted = `${yyyy}-${mm}-${dd}`;
            let opt = document.createElement("option");
            opt.value = formatted;
            opt.textContent = `${dd}-${mm}-${yyyy}`;

            if ("{{ request('end_date') }}" === formatted) {
                opt.selected = true;
            }

            endDateSelect.appendChild(opt);
        }
    });

    if (startDateInput.value) {
        startDateInput.dispatchEvent(new Event("change"));
    }
});
</script>
@endsection
