@extends('layouts.index')
@section('content')
<div class="card shadow-sm border-0">

    {{--  Judul Halaman --}}
    <div class="card-header bg-white border-0 pb-0">
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
            <h4 class="fw-bold text-primary mb-0">
                <i class="ri-archive-2-line me-2"></i> Daftar Barang Masuk
            </h4>
            <a href="{{ route('super_admin.item_ins.create') }}" class="btn btn-sm btn-primary">
                <i class="ri-add-line me-1"></i> Tambah Barang
            </a>
        </div>

        {{--  Filter & Pencarian --}}
        <form method="GET" action="{{ route('super_admin.item_ins.index') }}" class="row g-2 align-items-center">

        {{--  Range tanggal --}}
        <div class="col-md-3 col-sm-6">
            <input type="date" name="start_date" class="form-control form-control-sm"
                value="{{ request('start_date') }}">
        </div>

        <div class="col-md-3 col-sm-6">
            <input type="date" name="end_date" class="form-control form-control-sm"
                value="{{ request('end_date') }}">
        </div>

        {{--  Search --}}
        <div class="col-md-4 col-sm-8">
            <input type="text" name="search" class="form-control form-control-sm"
                placeholder="Cari nama barang / supplier..."
                value="{{ request('search') }}">
        </div>

        {{--  Tombol --}}
        <div class="col-md-2 col-sm-4">
            <button type="submit" class="btn btn-sm btn-secondary w-100">
                <i class="ri-search-line me-1"></i> Cari
            </button>
        </div>

        {{--  Reset --}}
        @if(request('start_date') || request('end_date') || request('search'))
            <div class="col-12 text-end mt-2">
                <a href="{{ route('super_admin.item_ins.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="ri-refresh-line me-1"></i> Reset
                </a>
            </div>
        @endif
    </form>
    </div>

    {{--  Tabel Data --}}
    <div class="card-body pt-2">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light text-center">
                    <tr>
                        <th>Barang</th>
                        <th>Jumlah</th>
                        <th>Supplier</th>
                        <th>Expired</th>
                        <th>Status</th>
                        <th>Dibuat Oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items_in as $row)
                        @php
                            $isExpired = $row->expired_at ? $row->expired_at->isPast() : null;
                            $statusText = $isExpired === null ? 'Tidak Berlaku' : ($isExpired ? 'Expired' : 'Aktif');
                            $statusClass = $isExpired === null ? 'bg-secondary'
                                          : ($isExpired ? 'bg-danger' : 'bg-success');
                        @endphp
                        <tr class="text-center">
                            <td class="text-start fw-semibold">{{ $row->item->name ?? '-' }}</td>
                            <td>{{ $row->quantity }}</td>
                            <td>{{ $row->supplier->name ?? '-' }}</td>
                            <td>{{ $row->expired_at ? $row->expired_at->format('d M Y') : '-' }}</td>
                            <td><span class="badge {{ $statusClass }}">{{ $statusText }}</span></td>
                            <td>{{ $row->creator->name ?? '-' }}</td>
                            <td>
                                <div class="dropdown">
                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow shadow-none" data-bs-toggle="dropdown">
                                        <i class="ri-more-2-fill"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('super_admin.item_ins.edit', $row->id) }}">
                                                <i class="ri-pencil-line me-2"></i> Edit
                                            </a>
                                        </li>
                                        <li>
                                            <form action="{{ route('super_admin.item_ins.destroy', $row->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Yakin hapus data ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="ri-delete-bin-6-line me-2"></i> Hapus
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="ri-information-line me-1"></i> Belum ada data barang masuk.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
