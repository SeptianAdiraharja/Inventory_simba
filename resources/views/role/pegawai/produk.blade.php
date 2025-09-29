@extends('layouts.index')
@section('content')

{{-- Flash Message --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- Hasil Pencarian --}}
<div class="row mb-3">
    @if(isset($search) && $search)
        <h5>Hasil pencarian untuk: <strong>{{ $search }}</strong></h5>
    @endif
</div>

{{-- Grid Produk --}}
<div class="row gy-4">
    @forelse ($items as $item)
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
            <div class="card h-100 shadow-sm border-0">
                {{-- Gambar Produk --}}
                <img src="{{ asset('storage/'. $item->image) }}"
                     class="card-img-top"
                     alt="{{ $item->name }}"
                     style="height: 200px; object-fit: cover;">

                <div class="card-body d-flex flex-column">
                    {{-- Nama & Kategori --}}
                    <h5 class="card-title mb-1">{{ $item->name }}</h5>
                    <p class="card-text text-muted mb-2">
                        Kategori: <span class="fw-semibold">{{ $item->category->name ?? '-' }}</span>
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
                                       value="1" min="1"
                                       max="{{ $item->stock }}"
                                       {{ $item->stock == 0 ? 'disabled' : '' }}>
                            </div>
                            <button type="submit"
                                    class="btn btn-sm btn-primary ms-2"
                                    {{ $item->stock == 0 ? 'disabled' : '' }}>
                                <i class="ri-shopping-cart-2-line"></i> Ajukan
                            </button>
                        </div>
                    </form>

                    {{-- Stok --}}
                    <small class="mt-2 text-muted">
                        Stok: {{ $item->stock }}
                    </small>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center">
            <p class="text-muted">Tidak ada produk ditemukan.</p>
        </div>
    @endforelse
</div>

{{-- Pagination --}}
<div class="mt-4 d-flex justify-content-center">
    @if ($items instanceof \Illuminate\Pagination\LengthAwarePaginator || $items instanceof \Illuminate\Pagination\Paginator)
        {{ $items->links() }}
    @endif
</div>


@endsection
