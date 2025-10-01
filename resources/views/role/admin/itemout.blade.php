@extends('layouts.index')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Daftar Permintaan Pegawai</h4>
    <div class="dropdown">
        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-funnel"></i> Filter
        </button>
        <ul class="dropdown-menu" aria-labelledby="filterDropdown">
            <li><a class="dropdown-item filter-btn" data-filter="all" href="#">Semua</a></li>
            <li><a class="dropdown-item filter-btn" data-filter="scanned" href="#">Sudah di-scan semua</a></li>
            <li><a class="dropdown-item filter-btn" data-filter="not-scanned" href="#">Belum di-scan semua</a></li>
        </ul>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th style="width: 50px;" class="text-center">No</th>
                <th>Nama User</th>
                <th class="text-center">Status Scan</th>
                <th style="width: 160px;" class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($approvedItems as $i => $cart)
                {{-- Baris utama --}}
                <tr class="cart-item align-middle" data-scanned="{{ $cart->all_scanned ? 'true' : 'false' }}">
                    <td class="text-center">{{ $i+1 }}</td>
                    <td>{{ $cart->user->name ?? 'Guest' }}</td>
                    <td class="text-center">
                        @if($cart->all_scanned)
                            <span class="badge bg-success small">Sudah di-scan semua</span>
                        @else
                            <span class="badge bg-secondary small">Belum scan semua</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-primary"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapse{{ $cart->id }}"
                                aria-expanded="false"
                                aria-controls="collapse{{ $cart->id }}">
                            <i class="bi bi-eye"></i> Detail
                        </button>
                    </td>
                </tr>

                {{-- Baris detail --}}
                <tr class="collapse" id="collapse{{ $cart->id }}">
                    <td colspan="4">
                        <div class="card shadow-sm border-0">
                            <div class="card-body">
                                <div class="d-flex gap-2 mb-3">
                                    <button class="btn btn-sm btn-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#scanModal{{ $cart->id }}">
                                        <i class="bi bi-qr-code-scan"></i> Scan Barang
                                    </button>
                                    <a href="{{ route('admin.itemout.struk', $cart->id) }}"
                                    target="_blank"
                                    class="btn btn-sm btn-danger">
                                        <i class="bi bi-file-earmark-pdf"></i> Cetak PDF
                                    </a>
                                </div>

                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:50px;" class="text-center">No</th>
                                            <th>Nama Item</th>
                                            <th>Kode</th>
                                            <th style="width:80px;" class="text-center">Jumlah</th>
                                            <th class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($cart->cartItems as $j => $item)
                                            <tr>
                                                <td class="text-center">{{ $j+1 }}</td>
                                                <td>{{ $item->item->name }}</td>
                                                <td>{{ $item->item->code }}</td>
                                                <td class="text-center">{{ $item->quantity }}</td>
                                                <td class="text-center">
                                                    @if($item->scanned_at)
                                                        <span class="badge bg-success small">Sudah di-scan</span>
                                                    @else
                                                        <span class="badge bg-secondary small">Belum di-scan</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- =============================== --}}
{{-- PAGINATION --}}
{{-- =============================== --}}
<div class="d-flex justify-content-center mt-3">
    {{ $approvedItems->links() }}
</div>

{{-- =============================== --}}
{{-- MODALS --}}
{{-- =============================== --}}
@foreach($approvedItems as $cart)
<div class="modal fade" id="scanModal{{ $cart->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Scan Barang - {{ $cart->user->name ?? 'Guest' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label for="barcodeInput{{ $cart->id }}" class="form-label">Input Barcode</label>
                    <input type="text" id="barcodeInput{{ $cart->id }}"
                        class="form-control" placeholder="Scan atau masukkan kode barang">
                </div>

                <table class="table table-sm table-bordered" id="scanTable{{ $cart->id }}">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">No</th>
                            <th>Nama Barang</th>
                            <th>Kode</th>
                            <th class="text-center">Jumlah</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Akan diisi JS setelah scan --}}
                    </tbody>
                </table>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-success" id="releaseBtn{{ $cart->id }}">Keluarkan Barang</button>
            </div>
        </div>
    </div>
</div>
@endforeach

@endsection

<script>
document.addEventListener("DOMContentLoaded", function () {
    const filterButtons = document.querySelectorAll(".filter-btn");
    const rows = document.querySelectorAll(".cart-item");

    filterButtons.forEach(btn => {
        btn.addEventListener("click", function (e) {
            e.preventDefault();
            const filter = this.getAttribute("data-filter");

            rows.forEach(row => {
                const isScanned = row.getAttribute("data-scanned") === "true";

                if (filter === "all") {
                    row.style.display = "";
                } else if (filter === "scanned" && isScanned) {
                    row.style.display = "";
                } else if (filter === "not-scanned" && !isScanned) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        });
    });
});
</script>

