@extends('layouts.index')
@section('title', 'Permintaan Pending')
@section('content')
<style>
    /* =============================
       🍊 Soft Orange Accent (Minimal & Clean)
       ============================= */
    body {
        background-color: #ffffff !important; /* putih bersih */
    }

    /* ===== Modern Breadcrumb ===== */
    .breadcrumb-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(255, 153, 0, 0.1); /* oranye lembut */
        color: #fb8c00; /* oranye soft, bukan neon */
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .breadcrumb a {
        color: #fb8c00;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s ease;
    }

    .breadcrumb a:hover {
        color: #f57c00;
    }

    .breadcrumb-item + .breadcrumb-item::before {
        color: #d1d5db;
        content: "/";
        padding: 0 0.5rem;
    }

    .breadcrumb-item.active {
        color: #374151;
        font-weight: 600;
    }

    /* ===== Card & Table ===== */
    .card {
        border-radius: 16px !important;
        border: none !important;
        background-color: #fff;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.04);
    }

    .card-header {
        border-bottom: 1px solid #f2f2f2 !important;
        background: #fff !important;
        padding: 1rem 1.5rem !important;
    }

    .card-header h5 {
        font-size: 1.05rem;
        color: #fb8c00; /* oranye aksen halus */
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .badge.bg-warning-subtle {
        background-color: #fff6e5 !important;
        color: #fb8c00 !important;
        border: 1px solid #ffe0b2 !important;
    }

    /* ===== Table ===== */
    .table thead th {
        background-color: #fafafa !important;
        color: #5f6368;
        font-weight: 600;
        border-bottom: 2px solid #f3f4f6 !important;
    }

    .table-hover tbody tr:hover {
        background-color: #fffdf7 !important;
    }

    .collapse td {
        background-color: #f9fafb !important;
    }

    .table-bordered th,
    .table-bordered td {
        border-color: #f1f1f1 !important;
    }

    /* ===== Buttons ===== */
    .btn-outline-primary {
        border-color: #fb8c00 !important;
        color: #fb8c00 !important;
        border-radius: 8px !important;
        transition: all 0.2s ease-in-out;
    }

    .btn-outline-primary:hover {
        background-color: #fb8c00 !important;
        color: #fff !important;
    }

    .btn-outline-danger {
        border-color: #e57373 !important;
        color: #e57373 !important;
        border-radius: 8px !important;
        transition: all 0.2s ease-in-out;
    }

    .btn-outline-danger:hover {
        background-color: #e57373 !important;
        color: #fff !important;
    }

    /* ===== Status Badge ===== */
    .badge.bg-warning {
        background-color: #ffb74d !important; /* soft amber */
        color: #fff !important;
        border-radius: 50px;
        font-weight: 600;
    }

    /* ===== Text Styles ===== */
    .text-primary {
        color: #fb8c00 !important;
    }

    .text-secondary {
        color: #6b7280 !important;
    }

    .text-muted small {
        color: #9e9e9e !important;
    }

    /* ===== Collapse Card Detail ===== */
    .collapse .bg-white {
        border: 1px solid #f1f1f1 !important;
    }

    @media (max-width: 768px) {
        .card-header {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 0.8rem;
        }
    }
</style>

{{-- ======================== --}}
{{-- 🧭 MODERN BREADCRUMB --}}
{{-- ======================== --}}
<div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3 animate__animated animate__fadeInDown smooth-fade">
    <div class="d-flex align-items-center flex-wrap gap-2">
        <div class="breadcrumb-icon d-flex align-items-center justify-content-center">
            <i class="bi bi-clock fs-5"></i>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 align-items-center">
                <li class="breadcrumb-item">
                    <a href="{{ route('pegawai.dashboard') }}" class="fw-semibold">Dashboard</a>
                </li>
                <li class="breadcrumb-item active fw-semibold text-dark" aria-current="page">
                    Permintaan Pending
                </li>
            </ol>
        </nav>
    </div>

    <div class="text-end small text-muted">
        <i class="bi bi-calendar-check me-1"></i>{{ now()->format('d M Y, H:i') }}
    </div>
</div>

{{-- 🧾 Konten Utama --}}
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <h5 class="mb-0">
            <i class="bi bi-hourglass-split me-2"></i> Permintaan Pending
        </h5>
        <span class="badge bg-warning-subtle fw-semibold px-3 py-2">
            {{ $carts->count() }} Pending
        </span>
    </div>

    <div class="table-responsive text-nowrap">
        @if($carts->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="bi bi-inbox fs-1 mb-2 d-block"></i>
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
                                <i class="bi bi-calendar-event me-1 text-secondary"></i>
                                {{ $cart->created_at->format('d M Y') }}
                                <br>
                                <small class="text-muted">{{ $cart->created_at->format('H:i') }} WIB</small>
                            </td>
                            <td>
                                <i class="bi bi-box-seam me-1 text-secondary"></i>
                                {{ $cart->cart_items_count }} Barang
                            </td>
                            <td>
                                <span class="badge bg-warning px-3 py-2">
                                    <i class="bi bi-hourglass-split me-1"></i> Pending
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

                        {{-- Detail Section --}}
                        <tr class="collapse bg-light" id="collapse{{ $cart->id }}">
                            <td colspan="5">
                                <div class="p-3">
                                    <div class="p-3 bg-white rounded shadow-sm mb-3">
                                        @if($cart->status === 'pending')
                                            <form action="{{ route('pegawai.permintaan.cancel', $cart->id) }}"
                                                  method="POST"
                                                  class="cancel-form mb-3">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    <i class="bi bi-x-circle me-1"></i> Batalkan Permintaan
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
                                                <th>No</th>
                                                <th>Gambar</th>
                                                <th>Nama Produk</th>
                                                <th>Kategori</th>
                                                <th>Jumlah</th>
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
@endsection
