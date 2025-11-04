@extends('layouts.index')

@section('content')
<style>
    /* =============================
       ✨ UI/UX Styling for Produk Page ✨
       ============================= */
    body {
        background-color: #f7f9fb !important;
    }

    /* ===== Modern Breadcrumb ===== */
    .breadcrumb-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(78, 115, 223, 0.1);
        color: #4e73df;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .breadcrumb a {
        color: #4e73df;
        text-decoration: none;
        font-weight: 500;
    }

    .breadcrumb-item + .breadcrumb-item::before {
        color: #6c757d;
        content: "/";
        padding: 0 0.5rem;
    }

    .breadcrumb-item.active {
        color: #1e293b;
        font-weight: 600;
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

    .alert {
        border: none;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        font-size: 0.9rem;
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
        .product-card h5 {
            font-size: 0.95rem;
        }
    }
</style>

{{-- ======================== --}}
{{-- 🧭 MODERN BREADCRUMB --}}
{{-- ======================== --}}
<div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3 animate__animated animate__fadeInDown smooth-fade">
    {{-- Left Section --}}
    <div class="d-flex align-items-center flex-wrap gap-2">
        <div class="breadcrumb-icon bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center rounded-circle"
             style="width:40px; height:40px;">
            <i class="bi bi-cart fs-5"></i>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 align-items-center">
                <li class="breadcrumb-item">
                    <a href="{{ route('pegawai.dashboard') }}" class="text-decoration-none text-primary fw-semibold">
                        Dashboard
                    </a>
                </li>
                <li class="breadcrumb-item active fw-semibold text-dark" aria-current="page">
                    Daftar Barang
                </li>
            </ol>
        </nav>
    </div>

    {{-- Right Section (Date) --}}
    <div class="text-end small text-muted">
        <i class="bi bi-calendar-check me-1"></i>{{ now()->format('d M Y, H:i') }}
    </div>
</div>

{{-- 🔔 Flash Message --}}
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- 🔍 Hasil Pencarian --}}
@if(isset($search) && $search)
    <div class="alert alert-info border-0 shadow-sm py-2 mb-4">
        <i class="bi bi-search me-2"></i> Hasil pencarian untuk:
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
                        <i class="bi bi-tag me-1"></i> Kategori:
                        <span class="fw-semibold">{{ $item->category->name ?? '-' }}</span>
                    </p>

                    <p class="small mb-3">
                        <i class="bi bi-box me-1"></i> Stok Tersisa:
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
                                <i class="bi bi-cart-plus me-1"></i> Ajukan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5">
            <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
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
