@extends('layouts.index')

@section('content')

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
    <div class="alert alert-info border-0 shadow-sm py-2">
        <i class="ri-search-line me-2"></i> Hasil pencarian untuk:
        <strong class="text-dark">{{ $search }}</strong>
    </div>
@endif

{{-- 🛒 Grid Produk --}}
<div class="row gy-4">
    @php
        // Urutkan produk berdasarkan ketersediaan stok (stok > 0 di atas)
    $items = $items->sortByDesc(fn($i) => $i->stock > 0)->sortByDesc(fn($i) => $i->stock);
    @endphp
    @forelse ($items as $item)
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
            <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden hover-card position-relative">

                {{-- Gambar Produk --}}
                <div class="position-relative">
                    <img src="{{ asset('storage/' . $item->image) }}"
                        class="card-img-top"
                        alt="{{ $item->name }}"
                        style="height: 200px; object-fit: cover;">

                    {{-- Badge Stok --}}
                    @if ($item->stock == 0)
                        <span class="position-absolute top-0 start-0 bg-danger text-white px-3 py-1 small rounded-end">
                            Habis
                        </span>
                    @elseif ($item->stock < 5)
                        <span class="position-absolute top-0 start-0 bg-warning text-dark px-3 py-1 small rounded-end">
                            Stok Menipis
                        </span>
                    @endif
                </div>

                {{-- Informasi Produk --}}
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title text-dark mb-1 text-truncate">{{ $item->name }}</h5>
                    <p class="card-text text-muted small mb-2">
                        <i class="ri-price-tag-3-line me-1"></i>
                        Kategori: <span class="fw-semibold">{{ $item->category->name ?? '-' }}</span>
                    </p>

                    {{-- Stok --}}
                    <p class="mb-3 small">
                        <i class="ri-archive-line me-1"></i>
                        Stok Tersisa: <span class="fw-semibold">{{ $item->stock }}</span>
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

{{-- 💡 Style tambahan agar kartu terlihat lebih modern --}}
<style>
    .hover-card {
        transition: all 0.2s ease-in-out;
    }
    .hover-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }
</style>
@endsection
