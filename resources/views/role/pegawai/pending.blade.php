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
            {{-- Pesan ketika tidak ada data --}}
            <div class="text-center text-muted py-5">
                <i class="ri-inbox-line fs-1 mb-2 d-block"></i>
                <p class="mb-0">Belum ada permintaan yang pending.</p>
                <small class="text-secondary">Permintaan baru akan muncul di sini setelah diajukan.</small>
            </div>
        @else
            {{-- Tabel daftar permintaan --}}
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
                                <a href="{{ route('pegawai.permintaan.detail', $cart->id) }}"
                                   class="btn btn-sm btn-outline-primary d-flex align-items-center">
                                    <i class="ri-eye-line me-1"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

{{-- Style tambahan agar tabel dan kartu terlihat lebih hidup --}}
<style>
    .table-hover tbody tr:hover {
        background-color: #f9fafc;
        transition: background-color 0.2s ease;
    }
</style>
@endsection
