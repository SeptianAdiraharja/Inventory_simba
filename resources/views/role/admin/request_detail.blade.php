@extends('layouts.index')

@section('content')
<div class="row detail-content-wrapper p-4 rounded-4 bg-light shadow-sm" data-cart-id="{{ $cart->id }}">

    {{-- ============================= --}}
    {{-- 🧾 HEADER PERMINTAAN --}}
    {{-- ============================= --}}
    <div class="col-12 mb-4 border-bottom pb-3">
        <h5 class="fw-bold text-primary mb-1">
            <i class="bi bi-clipboard-check me-2 text-primary"></i>
            Permintaan #{{ $cart->id }} — <span class="text-dark">{{ $cart->user_name }}</span>
        </h5>

        <p class="text-muted small mb-2">
            <strong class="text-secondary">Status Cart Utama:</strong>
            <span id="main-status-{{ $cart->id }}"
                class="badge rounded-pill px-3 py-2 shadow-sm
                    @if($cart->status == 'pending') bg-warning text-dark
                    @elseif($cart->status == 'rejected') bg-danger
                    @elseif($cart->status == 'approved') bg-success
                    @elseif($cart->status == 'approved_partially') bg-warning text-dark
                    @endif">
                {{ ucfirst(str_replace('_', ' ', $cart->status)) }}
            </span>
        </p>

        <p class="text-muted small mb-0">
            <strong class="text-secondary">Status Pemrosesan Item:</strong>
            <span class="fw-semibold">
                @if($scan_status == 'Selesai')
                    <i class="bi bi-check-all text-success me-1"></i>
                    <span class="text-success">Selesai (Semua item telah diproses)</span>
                @elseif($scan_status == 'Sebagian')
                    <i class="bi bi-hourglass-split text-warning me-1"></i>
                    <span class="text-warning">Sebagian diproses</span>
                @else
                    <i class="bi bi-x-circle text-danger me-1"></i>
                    <span class="text-danger">Belum diproses</span>
                @endif
            </span>
        </p>
    </div>

    {{-- ============================= --}}
    {{-- 📋 TABEL ITEM --}}
    {{-- ============================= --}}
    <div class="col-12">
        <div class="table-responsive rounded-4 shadow-sm">
            <table class="table table-hover table-bordered align-middle mb-0 bg-white">
                <thead class="bg-primary text-white text-center small text-uppercase">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Nama Barang</th>
                        <th>Kode</th>
                        <th style="width: 80px;">Jumlah</th>
                        <th style="width: 120px;">Status Item</th>
                        <th style="width: 180px;">Aksi Item</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($cartItems as $i => $item)
                        <tr class="text-center" data-item-id="{{ $item->id }}">
                            <td class="fw-semibold">{{ $i + 1 }}</td>
                            <td class="text-start fw-semibold text-dark">{{ $item->item_name }}</td>
                            <td class="text-muted">{{ $item->item_code }}</td>
                            <td class="fw-semibold">{{ $item->quantity }}</td>

                            {{-- ✅ STATUS ITEM --}}
                            <td class="item-status-cell">
                                <span class="badge rounded-pill px-3 py-2 shadow-sm
                                    @if($item->status == 'pending') bg-warning text-dark
                                    @elseif($item->status == 'approved') bg-success
                                    @elseif($item->status == 'rejected') bg-danger
                                    @endif">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>

                            {{-- ✅ AKSI ITEM --}}
                            <td class="item-action-cell">
                                @if($item->status == 'pending')
                                    <button type="button"
                                            class="btn btn-success btn-sm rounded-pill px-3 d-inline-flex align-items-center item-approve-btn shadow-sm"
                                            data-item-id="{{ $item->id }}"
                                            title="Setujui Item">
                                        <i class="bi bi-check-lg me-1"></i> Setujui
                                    </button>

                                    <button type="button"
                                            class="btn btn-outline-danger btn-sm rounded-pill px-3 d-inline-flex align-items-center item-reject-btn shadow-sm"
                                            data-item-id="{{ $item->id }}"
                                            title="Tolak Item">
                                        <i class="bi bi-x-lg me-1"></i> Tolak
                                    </button>

                                @elseif($item->status == 'approved')
                                    <span class="text-success fw-semibold">
                                        <i class="bi bi-check-circle me-1"></i> Approved
                                    </span>

                                @elseif($item->status == 'rejected')
                                    <span class="text-danger fw-semibold">
                                        <i class="bi bi-x-octagon me-1"></i> Rejected
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-5 d-block mb-1"></i>
                                Tidak ada item dalam permintaan ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ============================= --}}
    {{-- ⚙️ FOOTER AKSI --}}
    {{-- ============================= --}}
    <div class="col-12 mt-4 d-flex justify-content-end gap-3 border-top pt-3">
        <button type="button" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm cart-detail-cancel-btn">
            <i class="bi bi-x-circle me-1"></i> Batal
        </button>

        @php
            $disableSave = in_array($cart->status, ['approved', 'approved_partially', 'rejected']);
        @endphp

        <button type="button"
                class="btn btn-primary rounded-pill px-4 shadow-sm cart-detail-save-btn {{ $disableSave ? 'disabled opacity-50' : '' }}"
                @if($disableSave) disabled aria-disabled="true" @endif>
            <i class="bi bi-save me-1"></i> Simpan Perubahan
        </button>
    </div>
</div>

@endsection
