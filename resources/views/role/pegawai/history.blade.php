@extends('layouts.index')

@section('content')
<div class="container-fluid py-3">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        {{-- Header --}}
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 text-primary fw-semibold d-flex align-items-center">
                <i class="ri-history-line me-2 fs-5"></i> Riwayat Permintaan
            </h5>
        </div>

        {{-- Filter Tabs --}}
        <div class="card-body border-bottom bg-light py-3">
            @php
                $statuses = [
                    'all' => ['label' => 'Semua', 'count' => $statusCounts['all']],
                    'pending' => ['label' => 'Pending', 'count' => $statusCounts['pending']],
                    'approved' => ['label' => 'Approved', 'count' => $statusCounts['approved']],
                    'rejected' => ['label' => 'Rejected', 'count' => $statusCounts['rejected']],
                ];
                $activeStatus = request('status') ?? 'all';
            @endphp

            <ul class="nav nav-pills gap-2 flex-wrap justify-content-start">
                @foreach($statuses as $key => $data)
                    <li class="nav-item">
                        <a href="{{ route('pegawai.permintaan.history', ['status' => $key]) }}"
                           class="nav-link fw-semibold px-3 py-2 rounded-pill {{ $activeStatus == $key ? 'active bg-primary text-white shadow-sm' : 'bg-white border text-secondary' }}">
                            {{ $data['label'] }}
                            <span class="badge ms-1 rounded-pill {{ $activeStatus == $key ? 'bg-white text-primary' : 'bg-secondary-subtle text-secondary' }}">
                                {{ $data['count'] }}
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Content --}}
        <div class="card-body bg-light">
            @if($carts->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="ri-inbox-line fs-1 mb-2 d-block opacity-75"></i>
                    <p class="mb-0 fs-6">Belum ada permintaan.</p>
                </div>
            @else
                <div class="d-flex flex-column gap-3">
                    @foreach($carts as $cart)
                        <div class="bg-white rounded-4 shadow-sm p-3 border hover-card">
                            {{-- Header Card --}}
                            <div class="d-flex justify-content-between align-items-start flex-wrap">
                                <div>
                                    <h6 class="mb-1 fw-semibold text-dark">{{ $cart->created_at->format('d M Y') }}</h6>
                                    <small class="text-muted">
                                        <i class="ri-time-line me-1"></i>{{ $cart->created_at->format('H:i') }} WIB
                                    </small>
                                </div>

                                <span class="badge rounded-pill px-3 py-2 fw-semibold fs-6
                                    {{ $cart->status == 'approved' ? 'bg-success text-white' :
                                       ($cart->status == 'pending' ? 'bg-warning text-dark' : 'bg-danger text-white') }}">
                                    {{ ucfirst($cart->status) }}
                                </span>
                            </div>

                            {{-- Info Barang & Tombol --}}
                            <div class="d-flex justify-content-between align-items-center flex-wrap mt-3">
                                <div class="text-secondary">
                                    <i class="ri-archive-2-line me-1"></i>
                                    <span class="fw-medium">{{ $cart->cart_items_count }} Barang</span>
                                </div>

                                <div class="d-flex gap-2 mt-2 mt-md-0">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary btn-detail px-3"
                                        data-id="{{ $cart->id }}">
                                        <i class="bi bi-eye me-1"></i> Detail
                                    </button>

                                    {{-- Tombol Dinamis --}}
                                    @if($cart->status == 'approved')
                                        <form action="{{ route('pegawai.permintaan.refund', $cart->id) }}" method="POST" class="refund-form">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-danger btn-sm px-3">
                                                <i class="ri-refund-line me-1"></i> Refund
                                            </button>
                                        </form>
                                    @endif
                                    @if($cart->status === 'pending')
                                        <form action="{{ route('pegawai.permintaan.cancel', $cart->id) }}" method="POST" class="cancel-form">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-secondary btn-sm px-3">
                                                <i class="ri-close-line me-1"></i> Batal
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
               <div class="mt-4 d-flex justify-content-center">
                    {{ $carts->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal Detail --}}
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow rounded-4">
      <div class="modal-body p-0" id="detailContent">
        {{-- Placeholder loading --}}
        <div class="text-center py-5 text-muted" id="loadingState" style="display:none;">
          <i class="ri-loader-4-line ri-spin fs-1 mb-2 d-block opacity-75"></i>
          <p class="mb-0 fs-6">Memuat detail permintaan...</p>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Style --}}
<style>
.hover-card {
    transition: all 0.3s ease;
    border: 1px solid #f0f0f0;
}
.hover-card:hover {
    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    transform: translateY(-3px);
}
.nav-link {
    transition: all 0.2s ease-in-out;
}
.nav-link:hover {
    background-color: #0d6efd !important;
    color: #fff !important;
}
.badge {
    font-size: 0.8rem;
    font-weight: 600;
}
.btn {
    border-radius: 10px;
    transition: all 0.25s ease;
}
.btn:hover {
    transform: scale(1.03);
}
.card {
    background-color: #ffffff;
}
body {
    background-color: #f7f8fa;
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const forms_refund = document.querySelectorAll('.refund-form');
    const forms_cancel = document.querySelectorAll('.cancel-form');
    const buttons = document.querySelectorAll('.btn-detail');
    const modal = new bootstrap.Modal(document.getElementById('detailModal'));
    const detailContent = document.getElementById('detailContent');
    const loadingState = document.getElementById('loadingState');

    // === REFUND CONFIRM ===
    forms_refund.forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Konfirmasi',
                text: 'Yakin ingin refund permintaan ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, refund',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                reverseButtons: true
            }).then(result => { if (result.isConfirmed) form.submit(); });
        });
    });

    // === CANCEL CONFIRM ===
    forms_cancel.forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Konfirmasi',
                text: 'Yakin ingin membatalkan permintaan ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, batalkan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#6c757d',
                cancelButtonColor: '#adb5bd',
                reverseButtons: true
            }).then(result => { if (result.isConfirmed) form.submit(); });
        });
    });

    // === DETAIL MODAL ===
    buttons.forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
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
