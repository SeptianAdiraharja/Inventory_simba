@extends('layouts.index')

@section('content')
<style>
/* =============================
   ✨ UI/UX Styling for Riwayat Permintaan ✨
   ============================= */
body {
    background-color: #f7f9fb !important;
}

/* ===== Breadcrumb ===== */
.breadcrumb-wrapper {
    margin-bottom: 1.8rem;
    background: #ffffff;
    border-radius: 12px;
    padding: 1rem 1.25rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
}

.breadcrumb {
    background: transparent !important;
    margin-bottom: 0;
    padding: 0;
    font-size: 0.92rem;
}

.breadcrumb-item + .breadcrumb-item::before {
    color: #6c757d;
    content: "/";
    padding: 0 0.5rem;
}

.breadcrumb-item a {
    color: #4e73df;
    text-decoration: none;
}

.breadcrumb-item.active {
    color: #6c757d;
    font-weight: 500;
}

.page-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: #1d3557;
    margin: 0;
}

/* ===== Card ===== */
.card {
    border-radius: 16px !important;
    border: none !important;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
    background-color: #fff;
    transition: all 0.25s ease-in-out;
}

.card-header {
    border-bottom: 1px solid #eef1f5 !important;
    padding: 1rem 1.5rem !important;
    background: #fff !important;
}

.card-header h5 {
    font-size: 1.05rem;
    color: #1d3557;
    margin-bottom: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.card-body {
    background: #f8fafc;
}

/* ===== Tabs (Filter) ===== */
.nav-pills .nav-link {
    transition: all 0.25s ease-in-out;
    border-radius: 50px !important;
    font-size: 0.9rem;
    font-weight: 500;
    border: 1px solid #dee2e6;
}

.nav-pills .nav-link:hover {
    background-color: #4e73df !important;
    color: #fff !important;
}

.nav-pills .nav-link.active {
    background-color: #4e73df !important;
    color: #fff !important;
    border-color: #4e73df !important;
}

/* ===== Hover Card (Riwayat Item) ===== */
.hover-card {
    border: 1px solid #f0f0f0;
    border-radius: 14px;
    background: #fff;
    transition: all 0.3s ease;
    padding: 1rem 1.25rem;
}

.hover-card:hover {
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
    transform: translateY(-3px);
}

/* ===== Badge & Button ===== */
.badge {
    font-size: 0.82rem;
    font-weight: 600;
    padding: 0.4rem 0.7rem;
}

.btn {
    border-radius: 10px;
    transition: all 0.25s ease;
    font-size: 0.85rem;
}

.btn:hover {
    transform: scale(1.03);
}

/* ===== Modal ===== */
.modal-content {
    border-radius: 16px !important;
    overflow: hidden;
    border: none !important;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
}

.modal-body {
    background-color: #f8f9fa !important;
}

/* ===== Responsiveness ===== */
@media (max-width: 768px) {
    .card-header {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 0.8rem;
    }

    .hover-card {
        padding: 0.9rem 1rem;
    }

    .badge {
        font-size: 0.75rem;
    }
}
</style>

{{-- 🧭 Breadcrumb --}}
<div class="breadcrumb-wrapper">
    <h4 class="page-title"><i class="bx bx-history me-2 text-primary"></i> Riwayat Permintaan</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('pegawai.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Riwayat Permintaan</li>
        </ol>
    </nav>
</div>

{{-- 📦 Konten Utama --}}
<div class="container-fluid py-3">
    <div class="card rounded-4 overflow-hidden">
        {{-- Header --}}
        <div class="card-header bg-white d-flex align-items-center justify-content-between flex-wrap">
            <h5 class="mb-0 text-primary fw-semibold d-flex align-items-center gap-2">
                <i class="ri-history-line fs-5"></i> Daftar Riwayat Permintaan Barang
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

            <ul class="nav nav-pills gap-2 flex-wrap">
                @foreach($statuses as $key => $data)
                    <li class="nav-item">
                        <a href="{{ route('pegawai.permintaan.history', ['status' => $key]) }}"
                           class="nav-link px-3 py-2 {{ $activeStatus == $key ? 'active' : 'bg-white text-secondary' }}">
                            {{ $data['label'] }}
                            <span class="badge ms-1 rounded-pill {{ $activeStatus == $key ? 'bg-white text-primary' : 'bg-secondary-subtle text-secondary' }}">
                                {{ $data['count'] }}
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Riwayat List --}}
        <div class="card-body">
            @if($carts->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="ri-inbox-line fs-1 mb-2 d-block opacity-75"></i>
                    <p class="mb-0 fs-6">Belum ada permintaan.</p>
                </div>
            @else
                <div class="d-flex flex-column gap-3">
                    @foreach($carts as $cart)
                        <div class="hover-card shadow-sm">
                            <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                                <div>
                                    <h6 class="fw-semibold mb-1 text-dark">{{ $cart->created_at->format('d M Y') }}</h6>
                                    <small class="text-muted"><i class="ri-time-line me-1"></i>{{ $cart->created_at->format('H:i') }} WIB</small>
                                </div>

                                <span class="badge rounded-pill px-3 py-2 fw-semibold
                                    {{ $cart->status == 'approved' ? 'bg-success text-white' :
                                       ($cart->status == 'pending' ? 'bg-warning text-dark' : 'bg-danger text-white') }}">
                                    {{ ucfirst($cart->status) }}
                                </span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                <div class="text-secondary">
                                    <i class="ri-archive-2-line me-1"></i>
                                    <span class="fw-medium">{{ $cart->cart_items_count }} Barang</span>
                                </div>

                                <div class="d-flex gap-2 mt-2 mt-md-0">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-detail px-3" data-id="{{ $cart->id }}">
                                        <i class="bi bi-eye me-1"></i> Detail
                                    </button>

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
    <div class="modal-content">
      <div class="modal-body p-0" id="detailContent">
        <div class="text-center py-5 text-muted" id="loadingState" style="display:none;">
          <i class="ri-loader-4-line ri-spin fs-1 mb-2 d-block opacity-75"></i>
          <p class="mb-0 fs-6">Memuat detail permintaan...</p>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = new bootstrap.Modal(document.getElementById('detailModal'));
    const detailContent = document.getElementById('detailContent');
    const loadingState = document.getElementById('loadingState');

    // SweetAlert konfirmasi Refund & Cancel
    document.querySelectorAll('.refund-form, .cancel-form').forEach(form => {
        form.addEventListener('submit', e => {
            e.preventDefault();
            const isRefund = form.classList.contains('refund-form');
            Swal.fire({
                title: isRefund ? 'Konfirmasi Refund' : 'Konfirmasi Pembatalan',
                text: isRefund ? 'Yakin ingin refund permintaan ini?' : 'Yakin ingin membatalkan permintaan ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: isRefund ? 'Ya, Refund' : 'Ya, Batalkan',
                cancelButtonText: 'Batal',
                confirmButtonColor: isRefund ? '#dc3545' : '#6c757d',
                cancelButtonColor: '#adb5bd',
                reverseButtons: true
            }).then(result => { if (result.isConfirmed) form.submit(); });
        });
    });

    // Buka modal detail
    document.querySelectorAll('.btn-detail').forEach(btn => {
        btn.addEventListener('click', () => {
            loadingState.style.display = 'block';
            detailContent.innerHTML = '';
            modal.show();
            fetch(`/pegawai/permintaan/${btn.dataset.id}/detail`)
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
                        </div>`;
                });
        });
    });
});
</script>
@endpush
@endsection
