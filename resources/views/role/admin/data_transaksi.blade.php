@extends('layouts.index')

@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3 px-4">
            <h4 class="mb-0 fw-bold"><i class="bi bi-box-seam me-2"></i>Data Transaksi Barang Keluar</h4>
        </div>

        <div class="card-body p-0">

            {{-- 🔹 NAV TABS --}}
            <ul class="nav nav-tabs fs-5 fw-semibold px-3" id="transaksiTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active py-2 px-4" id="pegawai-tab" data-bs-toggle="tab"
                        data-bs-target="#pegawai" type="button" role="tab" aria-controls="pegawai"
                        aria-selected="true">
                        <i class="bi bi-person-badge me-1"></i> Pegawai
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-2 px-4" id="guest-tab" data-bs-toggle="tab"
                        data-bs-target="#guest" type="button" role="tab" aria-controls="guest"
                        aria-selected="false">
                        <i class="bi bi-people me-1"></i> Tamu
                    </button>
                </li>
            </ul>

            <div class="tab-content p-4" id="transaksiTabContent">

                {{-- =========================
                    🔸 TAB PEGAWAI
                ========================== --}}
                <div class="tab-pane fade show active" id="pegawai" role="tabpanel" aria-labelledby="pegawai-tab">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle text-center">
                            <thead class="table-primary">
                                <tr>
                                    <th style="width: 60px;">No</th>
                                    <th>Nama Pegawai</th>
                                    <th>Jumlah Barang</th>
                                    <th>Tanggal Transaksi</th>
                                    <th>Status</th>
                                    <th style="width: 200px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($finishedCarts as $i => $cart)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td class="text-start">{{ $cart->user->name ?? '-' }}</td>
                                    <td>{{ $cart->cartItems->count() }}</td>
                                    <td>{{ $cart->created_at->format('d M Y H:i') }}</td>
                                    <td>
                                        <span class="badge bg-success px-3 py-2">{{ ucfirst($cart->status) }}</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary rounded-3 px-3" data-bs-toggle="collapse"
                                            data-bs-target="#pegawai{{ $cart->id }}">
                                            <i class="bi bi-eye"></i> Detail
                                        </button>
                                    </td>
                                </tr>

                                {{-- 🔸 Collapse Detail Barang --}}
                                <tr class="collapse bg-light" id="pegawai{{ $cart->id }}">
                                    <td colspan="6">
                                        <table class="table table-sm table-bordered mb-0 text-center">
                                            <thead class="table-secondary">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Nama Barang</th>
                                                    <th>Kode</th>
                                                    <th>Jumlah</th>
                                                    <th>Status</th>
                                                    <th>Tanggal Scan</th>
                                                    <th style="width: 220px;">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($cart->cartItems as $j => $item)
                                                <tr>
                                                    <td>{{ $j + 1 }}</td>
                                                    <td class="text-start">{{ $item->item->name ?? '-' }}</td>
                                                    <td>{{ $item->item->code ?? '-' }}</td>
                                                    <td>{{ $item->quantity }}</td>
                                                    <td><span class="badge bg-success">Approved</span></td>
                                                    <td>{{ $item->scanned_at ? \Carbon\Carbon::parse($item->scanned_at)->format('d M Y H:i') : '-' }}</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-warning me-1"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#refundModal"
                                                            data-item-name="{{ $item->item->name }}"
                                                            data-item-id="{{ $item->item->id }}"
                                                            data-item-code="{{ $item->item->code }}"
                                                            data-max-qty="{{ $item->quantity }}">
                                                            <i class="bi bi-arrow-counterclockwise"></i> Refund
                                                        </button>

                                                        <a href="{{ route('admin.rejects.scan') }}"
                                                            class="btn btn-sm btn-danger me-1">
                                                            <i class="bi bi-x-circle"></i> Reject
                                                        </a>

                                                         <button class="btn btn-sm btn-info text-white"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editModal"
                                                            data-cart-item-id="{{ $item->id }}" {{-- ✅ ID dari cart_items --}}
                                                            data-item-id="{{ $item->item->id }}" {{-- ✅ ID dari items --}}
                                                            data-item-name="{{ $item->item->name }}"
                                                            data-qty="{{ $item->quantity }}">
                                                            <i class="bi bi-pencil-square"></i> Edit
                                                        </button>
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
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        {{ $finishedCarts->links('pagination::bootstrap-5') }}
                    </div>
                </div>

                {{-- =========================
                    🔸 TAB TAMU
                ========================== --}}
                <div class="tab-pane fade" id="guest" role="tabpanel" aria-labelledby="guest-tab">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle text-center">
                            <thead class="table-primary">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Tamu</th>
                                    <th>Jumlah Barang</th>
                                    <th>Tanggal Transaksi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($guestItemOuts as $i => $guest)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $guest->name ?? 'Guest' }}</td>
                                    <td>{{ $guest->guestCart->guestCartItems->count() ?? 0 }}</td>
                                    <td>{{ $guest->created_at->format('d M Y H:i') }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary rounded-3 px-3"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#guest{{ $guest->id }}">
                                            <i class="bi bi-eye"></i> Detail
                                        </button>
                                    </td>
                                </tr>

                                {{-- 🔸 Collapse Detail Barang Guest --}}
                                <tr class="collapse bg-light" id="guest{{ $guest->id }}">
                                    <td colspan="5">
                                        <table class="table table-sm table-bordered mb-0 text-center">
                                            <thead class="table-secondary">
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
                                                <tr>
                                                    <td>{{ $j + 1 }}</td>
                                                    <td class="text-start">{{ $item->item->name ?? '-' }}</td>
                                                    <td>{{ $item->item->code ?? '-' }}</td>
                                                    <td>{{ $item->quantity }}</td>
                                                    <td><span class="badge bg-success">Sudah Dipindai</span></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-warning me-1"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#refundModal"
                                                            data-item-name="{{ $item->item->name }}"
                                                            data-item-id="{{ $item->item->id }}"
                                                            data-item-code="{{ $item->item->code }}"
                                                            data-max-qty="{{ $item->quantity }}">
                                                            <i class="bi bi-arrow-counterclockwise"></i> Refund
                                                        </button>
                                                        <a href="{{ route('admin.rejects.scan') }}"
                                                            class="btn btn-sm btn-danger me-1">
                                                            <i class="bi bi-x-circle"></i> Reject
                                                        </a>
                                                         <button class="btn btn-sm btn-info text-white"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editModal"
                                                            data-cart-item-id="{{ $item->id }}" {{-- ✅ ID dari cart_items --}}
                                                            data-item-id="{{ $item->item->id }}" {{-- ✅ ID dari items --}}
                                                            data-item-name="{{ $item->item->name }}"
                                                            data-qty="{{ $item->quantity }}">
                                                            <i class="bi bi-pencil-square"></i> Edit
                                                        </button>
                                                    </td>
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
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        {{ $guestItemOuts->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 🔸 Modal Refund --}}
<div class="modal fade" id="refundModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="{{ route('admin.pegawai.refund') }}" method="POST">
                @csrf
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold"><i class="bi bi-arrow-counterclockwise me-2"></i>Refund Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="item_id" id="refundItemId">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Barang</label>
                        <input type="text" class="form-control" id="refundItemName" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Scan code</label>
                        <input type="text" name="code" class="form-control" placeholder="Scan code..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jumlah Refund</label>
                        <input type="number" name="qty" class="form-control" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-warning fw-bold"><i class="bi bi-check-circle me-1"></i> Proses</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i> Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 🔸 Modal Edit --}}
{{-- 🔹 Modal Edit Barang --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Barang Transaksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('admin.pegawai.updateItem') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="cart_item_id" id="editItemId">

                    {{-- 🔹 Pilih Barang --}}
                    <div class="mb-3">
                        <label for="editItemSelect" class="form-label fw-semibold">Pilih Barang</label>
                        <select name="item_id" id="editItemSelect" class="form-select" required>
                            <option value="">-- Pilih Barang --</option>
                            @foreach ($items as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 🔹 Scan code --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Scan code Barang</label>
                        <input type="text" name="code" id="editcode" class="form-control"
                               placeholder="Scan code..." required autofocus>
                    </div>

                    {{-- 🔹 Jumlah --}}
                    <div class="mb-3">
                        <label for="editQty" class="form-label fw-semibold">Jumlah</label>
                        <input type="number" name="qty" id="editQty" class="form-control" min="1" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-info text-white fw-semibold">
                        <i class="bi bi-check-circle me-1"></i> Simpan Perubahan
                    </button>
                    <button type="button" class="btn btn-secondary fw-semibold" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ======================================================
    // 🟢 Modal Refund
    // ======================================================
    const refundModal = document.getElementById('refundModal');
    if (refundModal) {
        refundModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const itemName = button.getAttribute('data-item-name');
            const itemId = button.getAttribute('data-item-id');
            document.getElementById('refundItemName').value = itemName;
            document.getElementById('refundItemId').value = itemId;
        });
    }

    const editModal = document.getElementById('editModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const cartItemId = button.getAttribute('data-cart-item-id');
            const itemId = button.getAttribute('data-item-id');
            const qty = button.getAttribute('data-qty');

            document.getElementById('editItemId').value = cartItemId;
            document.getElementById('editQty').value = qty;
            document.getElementById('editItemSelect').value = itemId;
            document.getElementById('editcode').value = ''; // Kosongkan input code tiap buka modal
        });
    }

    // ======================================================
    // 🟣 Dropdown Barang (Select2) di Modal Edit
    // ======================================================
    function formatItemOption (state) {
        if (!state.id) return state.text;
        const imageUrl = $(state.element).data('image');
        const text = state.text || '';
        if (imageUrl) {
            return $(`<span><img src="${imageUrl}" class="me-2 rounded" style="width:50px;height:50px;object-fit:cover;">${text}</span>`);
        }
        return text;
    }

    $('#editModal').on('shown.bs.modal', function () {
        $('#editItemSelect').select2({
            dropdownParent: $('#editModal'),
            templateResult: formatItemOption,
            templateSelection: formatItemOption,
            width: '100%'
        });
    });

    // ======================================================
    // 💬 SweetAlert2 Notifikasi
    // ======================================================
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session('success') }}',
            showConfirmButton: false,
            timer: 2000
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: '{{ session('error') }}',
            confirmButtonColor: '#d33'
        });
    @endif
});
</script>
@endpush
