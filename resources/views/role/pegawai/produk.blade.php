@extends('layouts.index')

@section('content')
<style>
    /* =============================
       ✨ UI/UX Styling for Produk Page ✨
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

    /* ===== Card Produk ===== */
    .product-card {
        border: none;
        border-radius: 14px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        transition: all 0.25s ease-in-out;
        background-color: #fff;
    }

    .product-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
    }

    .product-card img {
        height: 200px;
        width: 100%;
        object-fit: cover;
        border-bottom: 1px solid #f1f1f1;
    }

    .product-card .card-body {
        display: flex;
        flex-direction: column;
        padding: 1rem 1.25rem;
    }

    .product-card h5 {
        font-size: 1rem;
        font-weight: 600;
        color: #212529;
    }

    .product-card p {
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }

    .badge-status {
        position: absolute;
        top: 10px;
        left: 0;
        padding: 0.4rem 0.8rem;
        border-radius: 0 6px 6px 0;
        font-size: 0.8rem;
        font-weight: 600;
    }

    /* ===== Flash Message ===== */
    .alert {
        border: none;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        font-size: 0.9rem;
    }

    /* ===== Responsiveness ===== */
    @media (max-width: 992px) {
        .breadcrumb-wrapper {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
    }

    @media (max-width: 768px) {
        .product-card img {
            height: 180px;
        }

        .btn-sm {
            width: 100%;
        }
    }

    @media (max-width: 576px) {
        .page-title {
            font-size: 1.05rem;
        }

        .product-card h5 {
            font-size: 0.95rem;
        }
    }
</style>

{{-- 🧭 Breadcrumb --}}
<div class="breadcrumb-wrapper">
    <h4 class="page-title"><i class="bx bx-store me-2 text-primary"></i> Daftar Barang</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('pegawai.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Daftar Barang</li>
        </ol>
    </nav>
</div>

{{-- 🔔 Flash Message --}}
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="ri-check-line me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="ri-error-warning-line me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- 🔍 Hasil Pencarian --}}
@if(isset($search) && $search)
    <div class="alert alert-info border-0 shadow-sm py-2 mb-4">
        <i class="ri-search-line me-2"></i> Hasil pencarian untuk:
        <strong class="text-dark">{{ $search }}</strong>
    </div>
@endif

{{-- 🛒 Grid Produk --}}
<div class="row gy-4">
    @php
        // Urutkan produk berdasarkan stok
        $items = $items->sortByDesc(fn($i) => $i->stock > 0)->sortByDesc(fn($i) => $i->stock);
    @endphp

    @forelse ($items as $item)
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
            <div class="card product-card position-relative">

                {{-- Gambar Produk --}}
                <div class="position-relative">
                    <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}">

                    {{-- Badge Stok --}}
                    @if ($item->stock == 0)
                        <span class="badge-status bg-danger text-white">Habis</span>
                    @elseif ($item->stock < 5)
                        <span class="badge-status bg-warning text-dark">Stok Menipis</span>
                    @endif
                </div>

                {{-- Informasi Produk --}}
                <div class="card-body">
                    <h5 class="card-title text-truncate">{{ $item->name }}</h5>
                    <p class="text-muted small mb-1">
                        <i class="ri-price-tag-3-line me-1"></i> Kategori:
                        <span class="fw-semibold">{{ $item->category->name ?? '-' }}</span>
                    </p>

                    <p class="small mb-3">
                        <i class="ri-archive-line me-1"></i> Stok Tersisa:
                        <span class="fw-semibold">{{ $item->stock }}</span>
                    </p>

                    {{-- Form Permintaan --}}
                    <form action="{{ route('pegawai.permintaan.create') }}" method="POST" class="mt-auto">
                        @csrf
                        <input type="hidden" name="items[0][item_id]" value="{{ $item->id }}">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="input-group" style="max-width: 120px;">
                                <input type="number"
                                    name="items[0][quantity]"
                                    class="form-control text-center"
                                    value="1"
                                    min="1"
                                    {{ $item->stock == 0 ? 'disabled' : '' }}>
                            </div>

                            <button type="submit"
                                class="btn btn-sm btn-primary ms-2 d-flex align-items-center"
                                {{ $item->stock == 0 ? 'disabled' : '' }}>
                                <i class="ri-shopping-cart-2-line me-1"></i> Ajukan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5">
            <i class="ri-inbox-line fs-1 text-muted d-block mb-2"></i>
            <p class="text-muted mb-0">Tidak ada produk ditemukan.</p>
        </div>
    @endforelse
</div>

{{-- 📄 Pagination --}}
@if ($items instanceof \Illuminate\Pagination\LengthAwarePaginator || $items instanceof \Illuminate\Pagination\Paginator)
    <div class="mt-4 d-flex justify-content-center">
        {{ $items->links() }}
    </div>
@endif
@endsection
