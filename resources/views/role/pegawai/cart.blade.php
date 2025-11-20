@extends('layouts.detail')
@section('title', 'Detail Keranjang')
@section('content')
<div class="card border-0 shadow-sm rounded-3">

    {{-- Header --}}
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-primary fw-semibold">
            <i class="ri-shopping-cart-line me-2"></i> Detail Keranjang
        </h5>
        <span class="badge bg-info-subtle text-info fw-semibold">
            {{ $cart->cartItems->count() ?? 0 }} Item
        </span>
    </div>

    {{-- Isi Konten --}}
    <div class="table-responsive text-nowrap">

        {{-- Jika keranjang kosong --}}
        @if(!$cart || $cart->cartItems->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="ri-inbox-line fs-1 mb-2 d-block"></i>
                <p class="mb-0">Keranjang kamu kosong.</p>
                <small class="text-secondary">Tambahkan produk untuk melanjutkan pengajuan.</small>
            </div>
        @else
            @php
                $isLimitReached = $countThisWeek >= 5;
                $progress = ($countThisWeek / 5) * 100;
            @endphp

            {{-- Info pengajuan minggu ini --}}
            <div class="p-4 border-bottom">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0 fw-semibold text-secondary">
                        <i class="ri-calendar-line me-2"></i>Pengajuan Minggu Ini
                    </h6>
                    <span class="fw-bold text-primary">{{ $countThisWeek }}/5 kali</span>
                </div>

                {{-- Progress Bar --}}
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar {{ $isLimitReached ? 'bg-danger' : 'bg-primary' }}"
                         role="progressbar"
                         style="width: {{ $progress }}%;"
                         aria-valuenow="{{ $progress }}"
                         aria-valuemin="0"
                         aria-valuemax="100"></div>
                </div>

                {{-- Pesan peringatan --}}
                @if($isLimitReached)
                    <div class="alert alert-danger alert-dismissible fade show mt-3 py-2 px-3" role="alert">
                        <i class="ri-error-warning-line me-2"></i>
                        Anda telah mencapai batas maksimal 5 pengajuan minggu ini.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
            </div>

            {{-- Daftar produk di keranjang --}}
            <table class="table table-hover align-middle mb-0">
                <thead class="table">
                    <tr>
                        <th>Produk</th>
                        <th class="text-center" style="width: 20%;">Jumlah</th>
                        <th class="text-center" style="width: 10%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cart->cartItems as $item)
                        <tr data-item-id="{{ $item->id }}">
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ asset('storage/' . $item->item->image) }}"
                                         class="rounded me-3 shadow-sm"
                                         style="width: 55px; height: 55px; object-fit: cover;"
                                         alt="{{ $item->item->name }}">
                                    <div>
                                        <h6 class="mb-0 text-truncate">{{ $item->item->name }}</h6>
                                        <small class="text-muted">Kategori: {{ $item->item->category->name ?? '-' }}</small>
                                    </div>
                                </div>
                            </td>

                            {{-- Input jumlah --}}
                            <td class="text-center">
                                <div class="d-inline-flex align-items-center gap-2 position-relative">
                                    <button class="btn btn-sm btn-outline-secondary btn-minus" data-id="{{ $item->id }}">
                                        <i class="ri-subtract-line"></i>
                                    </button>

                                    <input type="number"
                                           class="form-control text-center quantity-input"
                                           value="{{ $item->quantity }}"
                                           min="1"
                                           style="width: 70px;"
                                           data-id="{{ $item->id }}">

                                    <button class="btn btn-sm btn-outline-secondary btn-plus" data-id="{{ $item->id }}">
                                        <i class="ri-add-line"></i>
                                    </button>

                                    {{-- Centang --}}
                                    <i class="ri-check-line text-success fs-5 position-absolute checkmark"
                                       style="right:-20px; opacity:0; transition:0.3s;"></i>
                                </div>
                            </td>

                            {{-- Aksi hapus --}}
                            <td class="text-center">
                                <form action="{{ route('pegawai.cart.destroy', $item->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="btn btn-sm btn-outline-danger d-inline-flex align-items-center"
                                            onclick="return confirm('Yakin ingin menghapus produk ini dari keranjang?')">
                                        <i class="ri-delete-bin-6-line me-1"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Footer --}}
            <div class="card-footer bg-white border-top text-end">
                @if($isLimitReached)
                    <button type="button" class="btn btn-secondary" disabled>
                        <i class="ri-error-warning-line me-1"></i> Batas Pengajuan Tercapai
                    </button>
                @else
                    <form action="{{ route('pegawai.permintaan.submit', $cart->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary d-inline-flex align-items-center"
                                onclick="return confirm('Yakin ingin mengajukan permintaan peminjaman ini?')">
                            <i class="ri-send-plane-line me-1"></i> Ajukan Permintaan
                        </button>
                    </form>
                @endif
            </div>
        @endif
    </div>
</div>

{{-- Style tambahan --}}
<style>
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        transition: background-color 0.2s ease;
    }

    .quantity-input {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 0.3rem;
    }

    .quantity-input:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 2px rgba(78,115,223,0.15);
    }

    .btn-minus, .btn-plus {
        border-radius: 8px;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-minus:hover, .btn-plus:hover {
        background-color: #e7f1ff;
        color: #0d6efd;
    }

    .checkmark.active {
        opacity: 1 !important;
        transform: scale(1.1);
    }
</style>

{{-- Script interaksi tambah/kurang jumlah (REALTIME) --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = '{{ csrf_token() }}';

    document.querySelectorAll('.btn-plus, .btn-minus').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const input = document.querySelector(`.quantity-input[data-id="${id}"]`);
            const checkmark = this.parentElement.querySelector('.checkmark');
            let value = parseInt(input.value);

            if (this.classList.contains('btn-plus')) value++;
            if (this.classList.contains('btn-minus') && value > 1) value--;

            input.value = value;

            // animasi centang
            checkmark.classList.add('active');
            setTimeout(() => checkmark.classList.remove('active'), 700);

            // === AJAX Update ke Server ===
            fetch(`/pegawai/cart/update/${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ quantity: value })
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    alert('Gagal memperbarui jumlah.');
                }
            })
            .catch(err => console.error(err));
        });
    });
});
</script>
@endsection
