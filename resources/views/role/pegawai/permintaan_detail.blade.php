<div class="card border-0 shadow-sm rounded-4 overflow-hidden animate__animated animate__fadeIn">
    {{-- Header --}}
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center flex-wrap py-3 px-4">
        <h5 class="mb-0 text-orange fw-semibold d-flex align-items-center gap-2">
            <i class="ri-file-list-line fs-5"></i> Detail Permintaan
        </h5>

        {{-- Status Badge Dinamis --}}
        <span class="badge rounded-pill px-3 py-2 fw-semibold fs-6
            {{ $cart->status === 'pending' ? 'bg-warning text-dark' :
               ($cart->status === 'approved' ? 'bg-orange text-white' :
               ($cart->status === 'rejected' ? 'bg-danger text-white' : 'bg-secondary text-white')) }}">
            <i class="{{ $cart->status === 'pending' ? 'ri-time-line' :
                       ($cart->status === 'approved' ? 'ri-check-line' :
                       ($cart->status === 'rejected' ? 'ri-close-line' : 'ri-question-line')) }} me-1"></i>
            {{ ucfirst($cart->status) }}
        </span>
    </div>

    {{-- Informasi Utama --}}
    <div class="p-4 border-bottom bg-light">
        <div class="row g-4">
            <div class="col-md-6">
                <p class="mb-1 text-muted small">Tanggal Permintaan</p>
                <h6 class="mb-0 fw-semibold text-dark">
                    {{ $cart->created_at->format('d M Y, H:i') }} WIB
                </h6>
            </div>
            <div class="col-md-6">
                <p class="mb-1 text-muted small">Jumlah Item</p>
                <h6 class="mb-0 fw-semibold text-dark">
                    {{ $cart->cartItems->count() }} Produk
                </h6>
            </div>
        </div>
    </div>

    {{-- Tabel Produk --}}
    <div class="table-responsive text-nowrap bg-white">
        @if(!$cart || $cart->cartItems->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="ri-inbox-line fs-1 mb-2 d-block opacity-75"></i>
                <p class="mb-1 fs-6">Tidak ada produk dalam permintaan ini.</p>
                <small class="text-secondary">Permintaan kosong atau telah dihapus.</small>
            </div>
        @else
            <table class="table align-middle mb-0">
                <thead class="table-orange text-white">
                    <tr class="fw-semibold">
                        <th class="ps-4">Produk</th>
                        <th class="text-center" style="width: 15%;">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cart->cartItems as $item)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="me-3 flex-shrink-0">
                                        <img src="{{ asset('storage/' . $item->item->image) }}"
                                             alt="{{ $item->item->name }}"
                                             class="rounded shadow-sm border border-light"
                                             style="width: 60px; height: 60px; object-fit: cover;">
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-semibold text-dark text-truncate">
                                            {{ $item->item->name }}
                                        </h6>
                                        <small class="text-muted">
                                            <i class="ri-price-tag-3-line me-1"></i>
                                            Kategori: {{ $item->item->category->name ?? '-' }}
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center fw-semibold text-dark">
                                {{ $item->quantity }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

{{-- ===== Style Tambahan (Tema Oranye Modern) ===== --}}
<style>
    :root {
        --orange-main: #ff7f32;
        --orange-soft: #ffe6d1;
    }

    .text-orange {
        color: var(--orange-main) !important;
    }

    .bg-orange {
        background-color: var(--orange-main) !important;
    }

    .card {
        background-color: #fff;
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 6px 18px rgba(255, 127, 50, 0.15);
    }

    .table-orange {
        background-color: var(--orange-main) !important;
    }

    .table thead th {
        font-size: 0.92rem;
        vertical-align: middle;
    }

    .table tbody tr:hover {
        background-color: var(--orange-soft) !important;
        transition: background-color 0.25s ease;
    }

    .badge {
        font-size: 0.85rem;
        letter-spacing: 0.3px;
    }

    .animate__animated {
        animation-duration: 0.3s;
    }

    @media (max-width: 768px) {
        .card-header {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 0.8rem;
        }

        .table img {
            width: 50px !important;
            height: 50px !important;
        }
    }
</style>
