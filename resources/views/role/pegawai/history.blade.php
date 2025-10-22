@extends('layouts.index')

@section('content')
<div class="container-fluid">
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0 text-primary fw-semibold">
                <i class="ri-history-line me-2"></i> Riwayat Permintaan
            </h5>
        </div>

        {{-- Filter Tabs --}}
        <div class="card-body border-bottom pb-2">
            @php
                $statuses = [
                    'all' => ['label' => 'Semua', 'count' => $statusCounts['all']],
                    'pending' => ['label' => 'Pending', 'count' => $statusCounts['pending']],
                    'approved' => ['label' => 'Approved', 'count' => $statusCounts['approved']],
                    'rejected' => ['label' => 'Rejected', 'count' => $statusCounts['rejected']],
                ];
                $activeStatus = request('status') ?? 'all';
            @endphp

            <ul class="nav nav-pills gap-2 flex-wrap">
                @foreach($statuses as $key => $data)
                    <li class="nav-item">
                        <a href="{{ route('pegawai.permintaan.history', ['status' => $key]) }}"
                           class="nav-link fw-semibold {{ $activeStatus == $key ? 'active bg-primary text-white' : 'bg-light text-secondary' }}">
                            {{ $data['label'] }}
                            <span class="badge {{ $activeStatus == $key ? 'bg-white text-primary' : 'bg-secondary-subtle text-secondary' }}">
                                {{ $data['count'] }}
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="card-body bg-light">
            @if($carts->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="ri-inbox-line fs-1 mb-2 d-block"></i>
                    <p class="mb-0">Belum ada permintaan.</p>
                </div>
            @else
                <div class="d-flex flex-column gap-3">
                    @foreach($carts as $cart)
                        <div class="bg-white rounded-4 shadow-sm p-3 border hover-card">
                            <div class="d-flex justify-content-between align-items-start flex-wrap">
                                <div>
                                    <h6 class="mb-1 fw-semibold text-dark">
                                        {{ $cart->created_at->format('d M Y') }}
                                    </h6>
                                    <small class="text-muted">
                                        <i class="ri-time-line me-1"></i>{{ $cart->created_at->format('H:i') }} WIB
                                    </small>
                                </div>

                                <span class="badge
                                    {{ $cart->status == 'approved' ? 'bg-success text-white' :
                                       ($cart->status == 'pending' ? 'bg-warning text-dark' : 'bg-danger text-white') }}
                                    rounded-pill px-3 py-2 fw-semibold">
                                    {{ ucfirst($cart->status) }}
                                </span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center flex-wrap mt-3">
                                <div>
                                    <i class="ri-archive-2-line text-secondary me-1"></i>
                                    <span class="fw-medium">{{ $cart->cart_items_count }} Barang</span>
                                </div>

                                <div class="d-flex gap-2 mt-2 mt-md-0">
                                    <a href="{{ route('pegawai.permintaan.detail', $cart->id) }}"
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="ri-eye-line me-1"></i> Detail
                                    </a>

                                    {{-- Tombol Dinamis --}}
                                    {{-- @if($cart->status != 'active' && $cart->status != 'rejected') --}}
                                        {{-- <form action="{{ route('pegawai.permintaan.refund', $cart->id) }}" method="POST" class="refund-form">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="ri-refund-line me-1"></i> Refund
                                            </button>
                                        </form> --}}
                                    {{-- @endif --}}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">
                    {{ $carts->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.hover-card:hover {
    box-shadow: 0 4px 14px rgba(0,0,0,0.08);
    transform: translateY(-2px);
    transition: all 0.25s ease;
}
</style>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('.refund-form');

    forms.forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault(); // cegah submit langsung

            Swal.fire({
                title: 'Yakin ingin Refund permintaan ini?',
                text: 'Tindakan ini tidak dapat dibatalkan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, refund!',
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
