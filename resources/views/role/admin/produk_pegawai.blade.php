@extends('layouts.index')

@section('content')
<div class="container-fluid py-3 animate__animated animate__fadeIn">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">
            <i class="ri-store-2-line me-2"></i> Produk Pegawai: {{ $pegawai->name }}
        </h4>
        <a href="{{ route('admin.pegawai.index') }}" class="btn btn-outline-secondary">
            <i class="ri-arrow-left-line me-1"></i> Kembali
        </a>
    </div>

    {{-- Daftar Produk --}}
    <div class="row gy-4">
        @foreach ($items as $item)
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden" data-item-id="{{ $item->id }}">
                <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}"
                     class="card-img-top" style="height: 180px; object-fit: cover;">
                <div class="card-body">
                    <h5 class="fw-semibold mb-1">{{ $item->name }}</h5>
                    <p class="text-muted small mb-1">
                        <i class="ri-folder-line me-1"></i> Kategori:
                        <span class="fw-semibold">{{ $item->category->name ?? '-' }}</span>
                    </p>
                    <p class="text-muted small mb-2">
                        <i class="ri-archive-line me-1"></i> Stok:
                        <span class="{{ $item->stock > 0 ? 'text-success' : 'text-danger' }}">
                            {{ $item->stock }}
                        </span>
                    </p>
                    <button class="btn btn-sm btn-primary w-100"
                            data-bs-toggle="modal"
                            data-bs-target="#scanModal"
                            data-item-id="{{ $item->id }}"
                            data-item-name="{{ $item->name }}">
                        <i class="ri-scan-line me-1"></i> Scan
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Tombol Cart --}}
    <button class="btn btn-primary rounded-circle shadow-lg position-fixed"
            id="cartButton"
            style="bottom: 20px; right: 25px; width: 60px; height: 60px; z-index: 1050;">
        <i class="ri-shopping-cart-2-line fs-3"></i>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
              id="cartBadge" style="display: none;">0</span>
    </button>
</div>

{{-- Modal Scan --}}
<div class="modal fade" id="scanModal" tabindex="-1" aria-labelledby="scanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Scan Kode Barang</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="scanForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="item_id" id="item_id">

                    <div class="mb-3">
                        <label class="form-label">Nama Barang</label>
                        <input type="text" class="form-control" id="item_name" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kode Barang</label>
                        <input type="text" name="barcode" id="barcode" class="form-control"
                               placeholder="Scan kode barang..." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jumlah</label>
                        <input type="number" name="quantity" id="quantity" min="1"
                               class="form-control" placeholder="Masukkan jumlah" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-3-line me-1"></i> Simpan
                    </button>
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Cart --}}
<div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">Keranjang Pegawai</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div id="cartContent" class="table-responsive text-center text-muted">
                    Memuat data...
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-success" id="saveCartButton">
                    <i class="ri-save-3-line me-1"></i> Simpan ke Item Out
                </button>
                <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- Script --}}
<script>
window.PegawaiApp = {
    id: {{ $pegawai->id }},
    routes: {
        scan: "{{ url('admin/pegawai/' . $pegawai->id . '/scan') }}",
        cart: "{{ url('admin/pegawai/' . $pegawai->id . '/cart') }}",
        saveCart: "{{ url('admin/pegawai/' . $pegawai->id . '/cart/save') }}",
        // deleteItem base - tambahkan /{id} di JS
        deleteItemBase: "{{ url('admin/pegawai/' . $pegawai->id . '/cart/item') }}"
    },
    csrf: "{{ csrf_token() }}"
};
</script>

{{-- pastikan SweetAlert2 & Bootstrap JS sudah dimuat di layout --}}
<script src="{{ asset('js/admin-produk-pegawai.js') }}"></script>


@endsection
