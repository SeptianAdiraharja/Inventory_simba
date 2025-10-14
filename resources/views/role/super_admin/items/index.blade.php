@extends('layouts.index')
@section('content')
<div class="card shadow-sm border-0">
    {{-- 🏷️ Judul Halaman --}}
    <div class="card-header bg-white border-0 pb-0">
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
            <h4 class="fw-bold text-primary mb-0">
                <i class="ri-archive-2-line me-2"></i> Daftar Barang
            </h4>
            <a href="{{ route('super_admin.items.create') }}" class="btn btn-sm btn-primary">
                <i class="ri-add-line me-1"></i> Tambah Barang
            </a>
        </div>

        {{-- 🔍 Filter, Range, & Search --}}
        <form id="filterForm" method="GET" action="{{ route('super_admin.items.index') }}" class="row g-2 align-items-center">

        {{--  Tanggal Mulai --}}
        <div class="col-md-3 col-sm-6">
            <input type="date" name="date_from" class="form-control form-control-sm"
                value="{{ request('date_from') }}">
        </div>

        {{--  Tanggal Selesai --}}
        <div class="col-md-3 col-sm-6">
            <input type="date" name="date_to" class="form-control form-control-sm"
                value="{{ request('date_to') }}">
        </div>

        {{--  Urutkan stok --}}
        <div class="col-md-3 col-sm-6">
            <select name="sort_stock" class="form-select form-select-sm">
                <option value="">Urutkan Stok</option>
                <option value="desc" {{ request('sort_stock') == 'desc' ? 'selected' : '' }}>Paling Banyak</option>
                <option value="asc" {{ request('sort_stock') == 'asc' ? 'selected' : '' }}>Paling Sedikit</option>
            </select>
        </div>

        {{--  Pencarian --}}
        <div class="col-md-2 col-sm-6">
            <input type="text" name="search" class="form-control form-control-sm"
                placeholder="Cari nama barang / kategori..."
                value="{{ request('search') }}">
        </div>

        {{--  Tombol Cari --}}
        <div class="col-md-1 text-md-end">
            <button type="submit" class="btn btn-sm btn-secondary w-100">
                <i class="ri-search-line"></i>
            </button>
        </div>

        {{--  Reset --}}
        @if(request('date_from') || request('date_to') || request('sort_stock') || request('search'))
            <div class="col-12 text-end mt-2">
                <a href="{{ route('super_admin.items.index') }}" class="btn btn-sm btn-outline-secondary">
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
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th>Satuan</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Tanggal Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr class="text-center">
                        <td class="text-start fw-semibold">{{ $item->name }}</td>
                        <td>{{ $item->category->name ?? '-' }}</td>
                        <td>{{ $item->unit->name ?? '-' }}</td>
                        <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                        <td>{{ $item->stock }}</td>
                        <td>{{ $item->created_at ? $item->created_at->format('d M Y') : '-' }}</td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow shadow-none" data-bs-toggle="dropdown">
                                    <i class="ri-more-2-fill"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li>
                                        <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#detailModal{{ $item->id }}">
                                            <i class="ri-file-list-3-line me-2"></i> Detail
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('super_admin.items.show', $item->id) }}">
                                            <i class="ri-eye-line me-2"></i> Show
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>

                    {{--  Modal Detail --}}
                    <div class="modal fade" id="detailModal{{ $item->id }}" tabindex="-1" aria-labelledby="detailModalLabel{{ $item->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                                <div class="modal-header bg-primary text-white py-2">
                                    <h5 class="modal-title fw-semibold" id="detailModalLabel{{ $item->id }}">
                                        <i class="ri-archive-line me-2"></i> Detail Barang — {{ $item->name }}
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>

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
                                            <p><strong>Dibuat:</strong> {{ $item->created_at ? $item->created_at->format('d M Y') : '-' }}</p>
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
                                            <p class="mt-2 small text-muted">{{ $item->name }}</p>
                                        @else
                                            <p class="text-muted">Barcode tidak tersedia</p>
                                        @endif
                                    </div>
                                </div>

                                <div class="modal-footer d-flex justify-content-between flex-wrap bg-white border-0 pt-3">
                                    {{-- Cetak Barcode --}}
                                    <form action="{{ route('super_admin.items.barcode.pdf', $item->id) }}" method="GET" target="_blank"
                                        class="d-flex align-items-center gap-2 flex-wrap mb-2 mb-md-0">
                                        <div class="input-group input-group-sm" style="width: 130px;">
                                            <span class="input-group-text bg-white border-end-0 px-2">
                                                <i class="ri-hashtag text-primary fs-6"></i>
                                            </span>
                                            <input type="number" name="jumlah" min="1" value="1"
                                                   class="form-control form-control-sm border-start-0 text-center fw-semibold"
                                                   placeholder="Qty">
                                        </div>
                                        <button type="submit" class="btn btn-sm btn-outline-primary rounded-3 d-flex align-items-center px-3">
                                            <i class="ri-printer-line me-1"></i> Cetak
                                        </button>
                                    </form>

                                    {{-- Aksi Lain --}}
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
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="ri-information-line me-1"></i> Belum ada data barang.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
