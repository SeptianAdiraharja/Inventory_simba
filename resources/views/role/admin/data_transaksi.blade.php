@extends('layouts.index')   

@section('content')
<div class="container-fluid py-3 animate__animated animate__fadeIn">

    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Data Transaksi Barang Keluar</h5>
        </div>

        <div class="card-body p-0">

            {{-- 🔹 NAV TABS --}}
            <ul class="nav nav-tabs" id="transaksiTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pegawai-tab" data-bs-toggle="tab"
                        data-bs-target="#pegawai" type="button" role="tab" aria-controls="pegawai"
                        aria-selected="true">
                        <i class="bi bi-person-badge me-1"></i> Pegawai
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="guest-tab" data-bs-toggle="tab"
                        data-bs-target="#guest" type="button" role="tab" aria-controls="guest"
                        aria-selected="false">
                        <i class="bi bi-people me-1"></i> Tamu
                    </button>
                </li>
            </ul>

            <div class="tab-content p-3" id="transaksiTabContent">

                {{-- =========================
                    🔸 TAB PEGAWAI
                ========================== --}}
                <div class="tab-pane fade show active" id="pegawai" role="tabpanel"
                    aria-labelledby="pegawai-tab">

                    <table class="table table-hover table-bordered align-middle">
                        <thead class="table-light text-center">
                            <tr>
                                <th style="width: 60px;">No</th>
                                <th>Nama Pegawai</th>
                                <th>Jumlah Barang</th>
                                <th>Tanggal Transaksi</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($finishedCarts as $i => $cart)
                            <tr class="text-center">
                                <td>{{ $i + 1 }}</td>
                                <td class="text-start">{{ $cart->user->name ?? '-' }}</td>
                                <td>{{ $cart->cartItems->count() }}</td>
                                <td>{{ $cart->created_at->format('d M Y H:i') }}</td>
                                <td>
                                    <span class="badge bg-success">{{ ucfirst($cart->status) }}</span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="collapse"
                                        data-bs-target="#pegawai{{ $cart->id }}">
                                        <i class="bi bi-eye"></i> Detail
                                    </button>
                                </td>
                            </tr>

                            {{-- 🔸 Collapse Detail Barang --}}
                            <tr class="collapse bg-light" id="pegawai{{ $cart->id }}">
                                <td colspan="6">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-secondary text-center">
                                            <tr>
                                                <th>#</th>
                                                <th>Nama Barang</th>
                                                <th>Kode</th>
                                                <th>Jumlah</th>
                                                <th>Status</th>
                                                <th>Tanggal Scan</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($cart->cartItems as $j => $item)
                                            <tr class="text-center">
                                                <td>{{ $j + 1 }}</td>
                                                <td class="text-start">{{ $item->item->name ?? '-' }}</td>
                                                <td>{{ $item->item->code ?? '-' }}</td>
                                                <td>{{ $item->quantity }}</td>
                                                <td>
                                                    <span class="badge bg-success">Approved</span>
                                                </td>
                                                <td>{{ $item->scanned_at ? \Carbon\Carbon::parse($item->scanned_at)->format('d M Y H:i') : '-' }}</td>
                                                <td>
                                                    {{-- Tombol Refund --}}
                                                    <button class="btn btn-sm btn-warning"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#refundModal"
                                                        data-item-name="{{ $item->item->name }}"
                                                        data-item-id="{{ $item->item->id }}">
                                                        <i class="bi bi-arrow-counterclockwise"></i> Refund
                                                    </button>

                                                    {{-- Tombol Reject --}}
                                                    <a href="{{ route('admin.rejects.scan') }}"
                                                        class="btn btn-sm btn-danger">
                                                        <i class="bi bi-x-circle"></i> Reject
                                                    </a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">
                                    <i class="bi bi-info-circle me-1"></i> Tidak ada transaksi pegawai yang selesai.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-end">
                        {{ $finishedCarts->links('pagination::bootstrap-5') }}
                    </div>
                </div>

                {{-- =========================
                    🔸 TAB TAMU
                ========================== --}}
                <div class="tab-pane fade" id="guest" role="tabpanel" aria-labelledby="guest-tab">

                    <table class="table table-hover table-bordered align-middle">
                        <thead class="table-light text-center">
                            <tr>
                                <th style="width: 60px;">No</th>
                                <th>Nama Tamu</th>
                                <th>Jumlah Barang</th>
                                <th>Tanggal Transaksi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($guestItemOuts as $i => $guest)
                            <tr class="text-center">
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $guest->name ?? 'Guest' }}</td>
                                <td>{{ $guest->guestCart->guestCartItems->count() ?? 0 }}</td>
                                <td>{{ $guest->created_at->format('d M Y H:i') }}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="collapse"
                                        data-bs-target="#guest{{ $guest->id }}">
                                        <i class="bi bi-eye"></i> Detail
                                    </button>
                                </td>
                            </tr>

                            {{-- 🔸 Collapse Detail Barang Guest --}}
                            <tr class="collapse bg-light" id="guest{{ $guest->id }}">
                                <td colspan="5">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-secondary text-center">
                                            <tr>
                                                <th>#</th>
                                                <th>Nama Barang</th>
                                                <th>Kode</th>
                                                <th>Jumlah</th>
                                                <th>Status</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($guest->guestCart->guestCartItems as $j => $item)
                                            <tr class="text-center">
                                                <td>{{ $j + 1 }}</td>
                                                <td class="text-start">{{ $item->item->name ?? '-' }}</td>
                                                <td>{{ $item->item->code ?? '-' }}</td>
                                                <td>{{ $item->quantity }}</td>
                                                <td><span class="badge bg-success">Sudah Dipindai</span></td>
                                                <td>
                                                    {{-- Refund --}}
                                                    <button class="btn btn-sm btn-warning"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#refundModal"
                                                        data-item-name="{{ $item->item->name }}"
                                                        data-item-id="{{ $item->item->id }}">
                                                        <i class="bi bi-arrow-counterclockwise"></i> Refund
                                                    </button>

                                                    {{-- Reject --}}
                                                    <a href="{{ route('admin.rejects.scan') }}"
                                                        class="btn btn-sm btn-danger">
                                                        <i class="bi bi-x-circle"></i> Reject
                                                    </a>
                                                </td>

                                                {{-- Tombol Edit --}}
                                                <button class="btn btn-sm btn-info"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editModal"
                                                    data-item-id="{{ $item->id }}"
                                                    data-item-name="{{ $item->item->name }}"
                                                    data-qty="{{ $item->quantity }}">
                                                    <i class="bi bi-pencil-square"></i> Edit
                                                </button>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">
                                    <i class="bi bi-info-circle me-1"></i> Tidak ada transaksi tamu yang selesai.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-end">
                        {{ $guestItemOuts->links('pagination::bootstrap-5') }}
                    </div>
                </div>

            </div> {{-- end tab content --}}
        </div>
    </div>
</div>

{{-- 🔸 Modal Refund --}}
<div class="modal fade" id="refundModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('admin.pegawai.refund') }}" method="POST">
                @csrf
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="bi bi-arrow-counterclockwise me-2"></i> Refund Barang
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="item_id" id="refundItemId">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Barang</label>
                        <input type="text" class="form-control" id="refundItemName" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Scan Barcode</label>
                        <input type="text" name="barcode" class="form-control" placeholder="Scan barcode barang..." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jumlah Refund</label>
                        <input type="number" name="qty" class="form-control" min="1" placeholder="Masukkan jumlah" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check-circle me-1"></i> Proses Refund
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 🔸 Modal Edit --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('admin.pegawai.updateItem') }}" method="POST">
                @csrf
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2"></i> Edit Barang Transaksi
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="cart_item_id" id="editItemId">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pilih Barang</label>
                        <select class="form-select" name="item_id" id="editItemSelect">
                            <option value="">-- Pilih Barang --</option>
                            @foreach($items as $itm)
                                <option value="{{ $itm->id }}">{{ $itm->name }} ({{ $itm->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jumlah (Qty)</label>
                        <input type="number" class="form-control" name="qty" id="editQty" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info text-white">
                        <i class="bi bi-check-circle me-1"></i> Simpan Perubahan
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const refundModal = document.getElementById('refundModal');
    refundModal.addEventListener('show.bs.modal', function(event){
        const button = event.relatedTarget;
        const itemName = button.getAttribute('data-item-name');
        const itemId = button.getAttribute('data-item-id');

        document.getElementById('refundItemName').value = itemName;
        document.getElementById('refundItemId').value = itemId;
    });
});
</script>
@endpush
