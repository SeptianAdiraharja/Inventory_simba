@extends('layouts.index')

@section('content')
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
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
                        <tr class="collapse bg-light" id="collapse{{ $cart->id }}">
                            <td colspan="5">
                                <div class="p-3">
                                    <div class="p-4 border-bottom bg-light">
                                        @if($cart->status === 'pending')
                                            <form action="{{ route('pegawai.permintaan.cancel', $cart->id) }}" method="POST" class="cancel-form">
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
                                            <th style="width: 75px;">Gambar</th>
                                            <th>Nama Produk</th>
                                            <th style="width:200px;">Kategori</th>
                                            <th style="width: 80px;">Jumlah</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($cart->cartItems as $j => $item)
                                            <tr>
                                            <td class="text-center">{{ $j+1 }}</td>
                                            <td class="text-center"><img src="{{ asset('storage/' . $item->item->image) }}"
                                                    class="rounded me-3 shadow-sm"
                                                    style="width: 75px; height: 75px; object-fit: cover;">
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

<style>
    .table-hover tbody tr:hover {
        background-color: #f9fafc;
        transition: background-color 0.2s ease;
    }
</style>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const forms_cancel = document.querySelectorAll('.cancel-form');
    forms_cancel.forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault(); // cegah submit langsung

            Swal.fire({
                title: 'Konfirmasi',
                text: 'Yakin ingin cancel permintaan ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, cancel!',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit(); // submit form kalau user setuju
                }
            });
        });
    });
});
</script>
@endpush
@endsection
