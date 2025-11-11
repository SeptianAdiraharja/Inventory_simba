@extends('layouts.index')

@section('content')
<div class="row detail-content-wrapper p-4 rounded-4 bg-light shadow-sm" data-cart-id="{{ $cart->id }}">

    {{-- HEADER --}}
    <div class="col-12 mb-4 border-bottom pb-3">
        <h5 class="fw-bold text-primary mb-1">
            <i class="bi bi-clipboard-check me-2 text-primary"></i>
            Permintaan #{{ $cart->id }} — <span class="text-dark">{{ $cart->user_name }}</span>
        </h5>

        <p class="text-muted small mb-2">
            <strong class="text-secondary">Status Cart Utama:</strong>
            <span id="main-status-{{ $cart->id }}"
                class="badge rounded-pill px-3 py-2 shadow-sm
                    @if($cart->status == 'pending') bg-warning text-dark
                    @elseif($cart->status == 'rejected') bg-danger
                    @elseif($cart->status == 'approved') bg-success
                    @elseif($cart->status == 'approved_partially') bg-warning text-dark
                    @endif">
                {{ ucfirst(str_replace('_', ' ', $cart->status)) }}
            </span>
        </p>

        <p class="text-muted small mb-0">
            <strong class="text-secondary">Status Pemrosesan Item:</strong>
            <span class="fw-semibold">
                @if($scan_status == 'Selesai')
                    <i class="bi bi-check-all text-success me-1"></i>
                    <span class="text-success">Selesai (Semua item telah diproses)</span>
                @elseif($scan_status == 'Sebagian')
                    <i class="bi bi-hourglass-split text-warning me-1"></i>
                    <span class="text-warning">Sebagian diproses</span>
                @else
                    <i class="bi bi-x-circle text-danger me-1"></i>
                    <span class="text-danger">Belum diproses</span>
                @endif
            </span>
        </p>
    </div>

    {{-- TABEL ITEM --}}
    <div class="col-12">
        <div class="table-responsive rounded-4 shadow-sm">
            <table class="table table-hover table-bordered align-middle mb-0 bg-white">
                <thead class="bg-primary text-white text-center small text-uppercase">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Nama Barang</th>
                        <th>Kode</th>
                        <th style="width: 80px;">Jumlah</th>
                        <th style="width: 120px;">Status Item</th>
                        <th style="width: 180px;">Aksi Item</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cartItems as $i => $item)
                        <tr class="text-center" data-item-id="{{ $item->id }}">
                            <td class="fw-semibold">{{ $i + 1 }}</td>
                            <td class="text-start fw-semibold text-dark">{{ $item->item_name }}</td>
                            <td class="text-muted">{{ $item->item_code }}</td>
                            <td class="fw-semibold">{{ $item->quantity }}</td>

                            <td class="item-status-cell">
                                <span class="badge rounded-pill px-3 py-2 shadow-sm
                                    @if($item->status == 'pending') bg-warning text-dark
                                    @elseif($item->status == 'approved') bg-success
                                    @elseif($item->status == 'rejected') bg-danger
                                    @endif">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>

                            <td class="item-action-cell">
                                @if($item->status == 'pending')
                                    <button type="button"
                                            class="btn btn-success btn-sm rounded-pill px-3 d-inline-flex align-items-center item-approve-btn shadow-sm"
                                            data-item-id="{{ $item->id }}">
                                        <i class="bi bi-check-lg me-1"></i> Setujui
                                    </button>

                                    <button type="button"
                                            class="btn btn-outline-danger btn-sm rounded-pill px-3 d-inline-flex align-items-center item-reject-btn shadow-sm"
                                            data-item-id="{{ $item->id }}"
                                            data-item-name="{{ $item->item_name }}">
                                        <i class="bi bi-x-lg me-1"></i> Tolak
                                    </button>

                                @elseif($item->status == 'approved')
                                    <span class="text-success fw-semibold">
                                        <i class="bi bi-check-circle me-1"></i> Approved
                                    </span>
                                @elseif($item->status == 'rejected')
                                    <span class="text-danger fw-semibold">
                                        <i class="bi bi-x-octagon me-1"></i> Rejected
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-5 d-block mb-1"></i>
                                Tidak ada item dalam permintaan ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- FOOTER --}}
    <div class="col-12 mt-4 d-flex justify-content-end gap-3 border-top pt-3">
        <button type="button" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm cart-detail-cancel-btn">
            <i class="bi bi-x-circle me-1"></i> Batal
        </button>

        @php
            $disableSave = in_array($cart->status, ['approved', 'approved_partially', 'rejected']);
        @endphp

        <button type="button"
                class="btn btn-primary rounded-pill px-4 shadow-sm cart-detail-save-btn {{ $disableSave ? 'disabled opacity-50' : '' }}"
                @if($disableSave) disabled aria-disabled="true" @endif>
            <i class="bi bi-save me-1"></i> Simpan Perubahan
        </button>
    </div>
</div>

{{-- ============================= --}}
{{-- 🧩 MODAL ALASAN PENOLAKAN --}}
{{-- ============================= --}}
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="rejectForm" method="POST" class="modal-content shadow-lg border-0 rounded-4">
            @csrf
            @method('PUT')
            <div class="modal-header bg-danger text-white rounded-top-4">
                <h5 class="modal-title fw-semibold" id="rejectModalLabel">
                    <i class="bi bi-x-circle me-2"></i> Tolak Item
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="item_id" id="rejectItemId">
                <p class="text-muted mb-2">Berikan alasan penolakan untuk item berikut:</p>
                <p class="fw-semibold text-dark" id="rejectItemName"></p>

                <div class="form-group mt-3">
                    <label for="rejectReason" class="form-label fw-semibold text-secondary">Alasan Penolakan</label>
                    <textarea name="reason" id="rejectReason" class="form-control rounded-3" rows="3" placeholder="Tuliskan alasan..." required></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i> Batal
                </button>
                <button type="submit" class="btn btn-danger rounded-pill px-3">
                    <i class="bi bi-send me-1"></i> Kirim
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const rejectButtons = document.querySelectorAll('.item-reject-btn');
    const rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));
    const rejectForm = document.getElementById('rejectForm');
    const rejectItemIdInput = document.getElementById('rejectItemId');
    const rejectItemName = document.getElementById('rejectItemName');

    // Klik tombol tolak → tampilkan modal
    rejectButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const itemId = this.getAttribute('data-item-id');
            const itemName = this.getAttribute('data-item-name');
            rejectItemIdInput.value = itemId;
            rejectItemName.textContent = itemName;

            // Set action form dinamis (pastikan route sesuai dengan route update item-mu)
            rejectForm.action = `/admin/request/item/${itemId}/reject`;

            rejectModal.show();
        });
    });

    // Submit modal (AJAX optional, bisa juga normal form)
    rejectForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const actionUrl = this.action;

        fetch(actionUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                rejectModal.hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Item Ditolak',
                    text: 'Item berhasil ditolak dengan alasan yang diberikan.',
                    timer: 1800,
                    showConfirmButton: false
                });
                // Optional: update status di tabel tanpa reload
                const row = document.querySelector(`[data-item-id="${formData.get('item_id')}"]`);
                if (row) {
                    const statusCell = row.querySelector('.item-status-cell');
                    const actionCell = row.querySelector('.item-action-cell');
                    statusCell.innerHTML = `<span class="badge rounded-pill bg-danger px-3 py-2 shadow-sm">Rejected</span>`;
                    actionCell.innerHTML = `<span class="text-danger fw-semibold"><i class="bi bi-x-octagon me-1"></i> Rejected</span>`;
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: data.message || 'Terjadi kesalahan saat menolak item.'
                });
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Terjadi kesalahan koneksi atau server.'
            });
        });
    });
});
</script>
@endpush
