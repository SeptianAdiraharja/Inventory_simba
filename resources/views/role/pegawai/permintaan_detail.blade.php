@extends('layouts.detail')

@section('content')
<div class="card border-0 shadow-sm rounded-3">
    {{-- Header --}}
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-primary fw-semibold">
            <i class="ri-file-list-line me-2"></i> Detail Permintaan
        </h5>

        {{-- Status Badge --}}
        <span class="badge rounded-pill px-3 py-2 fw-semibold
            {{ $cart->status === 'pending' ? 'bg-warning text-dark' :
               ($cart->status === 'approved' ? 'bg-success text-white' :
               ($cart->status === 'rejected' ? 'bg-danger text-white' : 'bg-secondary text-white')) }}">
            <i class="
                {{ $cart->status === 'pending' ? 'ri-time-line' :
                   ($cart->status === 'approved' ? 'ri-check-line' :
                   ($cart->status === 'rejected' ? 'ri-close-line' : 'ri-question-line')) }}
                me-1"></i>
            {{ ucfirst($cart->status) }}
        </span>
    </div>

    {{-- Detail Data --}}
    <div class="p-4 border-bottom bg-light">
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

    {{-- Tabel Produk --}}
    <div class="table-responsive text-nowrap">
        @if(!$cart || $cart->cartItems->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="ri-inbox-line fs-1 mb-2 d-block"></i>
                <p class="mb-0">Tidak ada produk dalam permintaan ini.</p>
                <small class="text-secondary">Permintaan kosong atau telah dihapus.</small>
            </div>
        @else
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Produk</th>
                        <th class="text-center" style="width: 15%;">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cart->cartItems as $item)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ asset('storage/' . $item->item->image) }}"
                                         class="rounded me-3 shadow-sm"
                                         style="width: 55px; height: 55px; object-fit: cover;">
                                    <div>
                                        <h6 class="mb-0 text-truncate">{{ $item->item->name }}</h6>
                                        <small class="text-muted">
                                            Kategori: {{ $item->item->category->name ?? '-' }}
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center fw-semibold">
                                {{ $item->quantity }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Tombol Kembali --}}
    <div class="card-footer bg-white border-top text-end">
        <a href="{{ route('pegawai.permintaan.pending') }}" class="btn btn-outline-primary d-inline-flex align-items-center">
            <i class="ri-arrow-left-line me-1"></i> Kembali ke Daftar Pending
        </a>
    </div>
</div>

{{-- Style tambahan agar tampilan lebih halus --}}
<style>
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        transition: background-color 0.2s ease;
    }
</style>
@endsection
