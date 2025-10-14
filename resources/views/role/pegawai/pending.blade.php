@extends('layouts.index')

@section('content')
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-primary fw-semibold">
            <i class="ri-time-line me-2"></i> Permintaan Pending
        </h5>
        <span class="badge bg-warning-subtle text-warning fw-semibold px-3 py-2">
            {{ $carts->count() }} Pending
        </span>
    </div>

    <div class="table-responsive text-nowrap">
        @if($carts->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="ri-inbox-line fs-1 mb-2 d-block"></i>
                <p class="mb-0">Belum ada permintaan yang pending.</p>
                <small class="text-secondary">Permintaan baru akan muncul di sini setelah diajukan.</small>
            </div>
        @else
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 5%;">#</th>
                        <th style="width: 20%;">Tanggal</th>
                        <th style="width: 25%;">Jumlah Barang</th>
                        <th style="width: 20%;">Status</th>
                        <th style="width: 15%;">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($carts as $cart)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>
                                <i class="ri-calendar-line me-1 text-secondary"></i>
                                {{ $cart->created_at->format('d M Y') }}
                                <br>
                                <small class="text-muted">{{ $cart->created_at->format('H:i') }} WIB</small>
                            </td>
                            <td>
                                <i class="ri-archive-2-line me-1 text-secondary"></i>
                                {{ $cart->cart_items_count }} Barang
                            </td>
                            <td>
                                <span class="badge bg-warning text-dark rounded-pill px-3 py-2">
                                    <i class="ri-time-line me-1"></i> Pending
                                </span>
                            </td>
                            <td>
                                <button 
                                    class="btn btn-sm btn-outline-primary d-flex align-items-center btn-detail"
                                    data-id="{{ $cart->id }}">
                                    <i class="ri-eye-line me-1"></i> Detail
                                </button>
                                @if($cart->status === 'pending')
                                    <form action="{{ route('pegawai.permintaan.cancel', $cart->id) }}" method="POST" class="cancel-form">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                                            <i class="ri-close-line me-1"></i> Batal
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

{{-- Modal Detail --}}
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow">
      <div class="modal-body p-0" id="detailContent">
        <div class="text-center py-5 text-muted" id="loadingState" style="display:none;">
          <i class="ri-loader-4-line ri-spin fs-1 mb-2 d-block"></i>
          <p>Memuat detail permintaan...</p>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
    .table-hover tbody tr:hover {
        background-color: #f9fafc;
        transition: background-color 0.2s ease;
    }
</style>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const forms_cancel = document.querySelectorAll('.cancel-form');
    const buttons = document.querySelectorAll('.btn-detail');
    const modal = new bootstrap.Modal(document.getElementById('detailModal'));
    const detailContent = document.getElementById('detailContent');
    const loadingState = document.getElementById('loadingState');
    forms_cancel.forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault(); // cegah submit langsung

            Swal.fire({
                title: 'Konfirmasi',
                text: 'Yakin ingin cancel permintaan ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, cancel!',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit(); // submit form kalau user setuju
                }
            });
        });
    });
    buttons.forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            loadingState.style.display = 'block';
            detailContent.innerHTML = '';
            modal.show();

            fetch(`/pegawai/permintaan/${id}/detail`)
                .then(res => res.text())
                .then(html => {
                    loadingState.style.display = 'none';
                    detailContent.innerHTML = html;
                })
                .catch(() => {
                    loadingState.style.display = 'none';
                    detailContent.innerHTML = `
                        <div class="text-center text-danger py-5">
                            <i class="ri-error-warning-line fs-1 mb-2 d-block"></i>
                            <p>Gagal memuat data.</p>
                        </div>
                    `;
                });
        });
    });
});
</script>
@endpush
@endsection
