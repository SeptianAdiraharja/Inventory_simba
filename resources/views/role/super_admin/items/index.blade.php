@extends('layouts.index')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <h5 class="mb-0">Daftar Barang</h5>
        <div class="d-flex align-items-center gap-2">
            {{-- Form Filter --}}
           <form id="filterForm" method="GET" action="{{ route('super_admin.items.index') }}"
                class="d-flex align-items-center gap-2 mb-0">

                {{-- Filter Tanggal --}}
                <div class="input-group input-group-sm rounded-pill shadow-sm" style="overflow: hidden; max-width: 180px;">
                    <span class="input-group-text bg-white border-0">
                        <i class="ri-calendar-event-line text-primary"></i>
                    </span>
                    <input type="date" name="date"
                        class="form-control form-control-sm border-0"
                        value="{{ request('date') }}"
                        onchange="document.getElementById('filterForm').submit()">
                </div>

                {{-- Sortir Stok --}}
                <div class="input-group input-group-sm rounded-pill shadow-sm" style="overflow: hidden; max-width: 160px;">
                    <span class="input-group-text bg-white border-0">
                        <i class="ri-bar-chart-2-line text-success"></i>
                    </span>
                    <select name="sort_stock" class="form-select form-select-sm border-0"
                            onchange="document.getElementById('filterForm').submit()">
                        <option value="">Urutkan</option>
                        <option value="desc" {{ request('sort_stock') == 'desc' ? 'selected' : '' }}>Terbanyak</option>
                        <option value="asc" {{ request('sort_stock') == 'asc' ? 'selected' : '' }}>Tersedikit</option>
                    </select>
                </div>

                {{-- Reset Filter --}}
                @if(request('date') || request('sort_stock'))
                    <a href="{{ route('super_admin.items.index') }}"
                    class="btn btn-sm btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                    style="width: 34px; height: 34px;"
                    title="Reset Filter">
                        <i class="ri-refresh-line text-muted"></i>
                    </a>
                @endif
            </form>

            {{-- Tombol Tambah --}}
            <a href="{{ route('super_admin.items.create') }}" class="btn btn-sm btn-primary">
                <i class="ri ri-add-line me-1"></i> Tambah
            </a>
        </div>
    </div>

    <div class="table-responsive text-nowrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Kategori</th>
                    <th>Satuan Barang</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse($items as $item)
                <tr>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->category->name ?? '-' }}</td>
                    <td>{{ $item->unit->name ?? '-' }}</td>
                    <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td>{{ $item->stock }}</td>
                    <td>
                        <div class="dropdown">
                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow shadow-none" data-bs-toggle="dropdown">
                            <i class="ri-more-2-line icon-18px"></i>
                        </button>
                        <div class="dropdown-menu">

                            {{-- Detail (Modal) --}}
                            <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#detailModal{{ $item->id }}">
                                <i class="ri-file-list-3-line icon-18px me-1"></i> Detail
                            </a>

                            {{-- Show (Halaman Detail) --}}
                            <a class="dropdown-item" href="{{ route('super_admin.items.show', $item->id) }}">
                                <i class="ri-eye-line icon-18px me-1"></i> Show
                            </a>
                        </div>
                    </div>

                    {{-- Modal Detail --}}
                   <div class="modal fade" id="detailModal{{ $item->id }}" tabindex="-1" aria-labelledby="detailModalLabel{{ $item->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                        
                        {{-- Header --}}
                        <div class="modal-header bg-primary text-white py-2">
                            <h5 class="modal-title fw-semibold" id="detailModalLabel{{ $item->id }}">
                            <i class="ri-archive-line me-2"></i> Detail Barang — {{ $item->name }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>

                        {{-- Body --}}
                        <div class="modal-body bg-light-subtle">
                            <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong>Kategori:</strong> {{ $item->category->name ?? '-' }}</p>
                                <p><strong>Satuan:</strong> {{ $item->unit->name ?? '-' }}</p>
                                <p><strong>Supplier:</strong> {{ $item->supplier->name ?? '-' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Harga:</strong> Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                                <p><strong>Stok:</strong> {{ $item->stock }}</p>
                                <p><strong>Kode:</strong> {{ $item->code ?? '-' }}</p>
                            </div>
                            </div>
                            <hr>
                            <h6 class="fw-bold text-secondary mb-3">
                            <i class="ri-barcode-line me-2"></i> Barcode
                            </h6>
                            <div class="text-center">
                            @if($item->barcode_png_base64)
                                <img src="{{ $item->barcode_png_base64 }}" 
                                    alt="barcode" 
                                    class="img-fluid border rounded p-2 bg-white shadow-sm" 
                                    style="max-width: 200px;">
                                <p class="mt-2 small text-muted">{{ $item->code }}</p>
                            @else
                                <p class="text-muted">Barcode tidak tersedia</p>
                            @endif
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="modal-footer d-flex justify-content-between flex-wrap bg-white border-0 pt-3">
                            
                            {{-- Form Print Barcode --}}
                            <form action="{{ route('super_admin.items.barcode.pdf', $item->id) }}" method="GET" target="_blank"
                                class="d-flex align-items-center gap-2 flex-wrap mb-2 mb-md-0">

                            {{-- Jumlah Cetak --}}
                            <div class="input-group input-group-sm" style="width: 130px;">
                                <span class="input-group-text bg-white border-end-0 px-2">
                                <i class="ri-hashtag text-primary fs-6"></i>
                                </span>
                                <input type="number" 
                                    name="jumlah" 
                                    min="1" 
                                    value="1"
                                    class="form-control form-control-sm border-start-0 text-center fw-semibold"
                                    placeholder="Qty"
                                    style="max-width: 100px; min-width: 80px;">
                            </div>

                            {{-- Tombol Cetak --}}
                            <button type="submit" 
                                    class="btn btn-sm btn-outline-primary rounded-3 d-flex align-items-center px-3">
                                <i class="ri-printer-line me-1"></i> Cetak
                            </button>
                            </form>

                            {{-- Tombol Aksi Lainnya --}}
                            <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('super_admin.items.edit', $item->id) }}" 
                                class="btn btn-sm btn-outline-warning d-flex align-items-center rounded-3">
                                <i class="ri-pencil-line me-1"></i> Edit
                            </a>

                            <form action="{{ route('super_admin.items.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin hapus item ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="btn btn-sm btn-outline-danger d-flex align-items-center rounded-3">
                                <i class="ri-delete-bin-6-line me-1"></i> Hapus
                                </button>
                            </form>
                            </div>
                        </div>
                        </div>
                    </div>
                    </div>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        <i class="ri-information-line me-1"></i> Belum Ada Data
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
