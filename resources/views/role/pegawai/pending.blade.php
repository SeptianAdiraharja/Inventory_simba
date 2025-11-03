@extends('layouts.index')

@section('content')
<style>
    /* =============================
       ✨ UI/UX Styling for Permintaan Pending ✨
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

    /* ===== Card & Table ===== */
    .card {
        border-radius: 16px !important;
        border: none !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
        background-color: #fff;
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

    .table {
        font-size: 0.95rem;
    }

    .table thead th {
        background-color: #f8f9fc !important;
        color: #495057;
        font-weight: 600;
        border-bottom: 2px solid #e2e6ea !important;
    }

    .table-hover tbody tr:hover {
        background-color: #f9fafc !important;
        transition: background-color 0.2s ease;
    }

    .collapse td {
        background-color: #f8faff !important;
    }

    .btn-outline-primary {
        border-color: #b0c4ff !important;
        color: #4e73df !important;
        border-radius: 8px !important;
    }

    .btn-outline-primary:hover {
        background-color: #4e73df !important;
        color: white !important;
    }

    .btn-outline-danger {
        border-radius: 8px !important;
    }

    /* ===== Responsiveness ===== */
    @media (max-width: 768px) {
        .card-header {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 0.8rem;
        }
    }
</style>

{{-- 🧭 Breadcrumb --}}
<div class="breadcrumb-wrapper">
    <h4 class="page-title"><i class="bx bx-time-five me-2 text-primary"></i> Permintaan Pending</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('pegawai.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Permintaan Pending</li>
        </ol>
    </nav>
</div>

{{-- 🧾 Konten Utama --}}
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center flex-wrap">
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

                        {{-- Collapse Detail --}}
                        <tr class="collapse bg-light" id="collapse{{ $cart->id }}">
                            <td colspan="5">
                                <div class="p-3">
                                    <div class="p-3 bg-white border rounded shadow-sm mb-3">
                                        @if($cart->status === 'pending')
                                            <form action="{{ route('pegawai.permintaan.cancel', $cart->id) }}"
                                                  method="POST"
                                                  class="cancel-form mb-3">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    <i class="ri-close-line me-1"></i> Batalkan Permintaan
                                                </button>
                                            </form>
                                        @endif

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <p class="mb-1 text-muted small">Tanggal Permintaan</p>
                                                <h6 class="mb-0">{{ $cart->created_at->format('d M Y, H:i') }} WIB</h6>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-1 text-muted small">Jumlah Item</p>
                                                <h6 class="mb-0">{{ $cart->cartItems->count() }} Produk</h6>
                                            </div>
                                        </div>
                                    </div>

                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-light">
                                            <tr class="text-center">
                                                <th style="width:50px;">No</th>
                                                <th style="width:75px;">Gambar</th>
                                                <th>Nama Produk</th>
                                                <th style="width:200px;">Kategori</th>
                                                <th style="width:80px;">Jumlah</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($cart->cartItems as $j => $item)
                                                <tr>
                                                    <td class="text-center">{{ $j + 1 }}</td>
                                                    <td class="text-center">
                                                        <img src="{{ asset('storage/' . $item->item->image) }}"
                                                             class="rounded shadow-sm"
                                                             style="width: 70px; height: 70px; object-fit: cover;">
                                                    </td>
                                                    <td>{{ $item->item->name }}</td>
                                                    <td class="text-center">{{ $item->item->category->name ?? '-' }}</td>
                                                    <td class="text-center">{{ $item->quantity }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

{{-- SweetAlert Confirm --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.cancel-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Konfirmasi Pembatalan',
                text: 'Yakin ingin membatalkan permintaan ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Batalkan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) form.submit();
            });
        });
    });
});
</script>
@endpush
@endsection
