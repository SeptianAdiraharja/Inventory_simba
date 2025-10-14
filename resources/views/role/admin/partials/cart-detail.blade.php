<div class="row">
    <div class="col-12 mb-3">
        <h5 class="fw-bold mb-1">Permintaan #{{ $cart->id }} - {{ $cart->user_name }}</h5>
        <p class="text-muted small mb-1">Status Cart Utama:
            <span id="main-status-{{ $cart->id }}" class="badge
                @if($cart->status == 'pending') bg-warning text-dark
                @elseif($cart->status == 'rejected') bg-danger
                @elseif($cart->status == 'approved') bg-success
                @endif">
                {{ ucfirst($cart->status) }}
            </span>
        </p>
        <p class="text-muted small mb-0">Status Pemrosesan Item:
            <span class="fw-semibold">
                @if($scan_status == 'Selesai')
                    <i class="bi bi-check-all text-success me-1"></i> Selesai (Semua item telah diproses)
                @elseif($scan_status == 'Sebagian')
                    <i class="bi bi-hourglass-split text-warning me-1"></i> Sebagian diproses
                @else
                    <i class="bi bi-x-circle text-danger me-1"></i> Belum diproses
                @endif
            </span>
        </p>
    </div>

    <div class="col-12">
        <table class="table table-sm table-bordered align-middle mb-0">
            <thead class="table-dark text-center">
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Nama Barang</th>
                    <th>Kode</th>
                    <th style="width: 80px;">Jumlah</th>
                    <th style="width: 120px;">Status Item</th>
                    <th style="width: 150px;">Aksi Item</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cartItems as $i => $item)
                    <tr class="text-center" data-item-id="{{ $item->id }}">
                        <td>{{ $i + 1 }}</td>
                        <td class="text-start">{{ $item->item_name }}</td>
                        <td>{{ $item->item_code }}</td>
                        <td class="fw-semibold">{{ $item->quantity }}</td>

                        {{-- ✅ Status Item --}}
                        <td class="item-status-cell">
                            <span class="badge
                                @if($item->status == 'pending') bg-warning text-dark
                                @elseif($item->status == 'approved') bg-success
                                @elseif($item->status == 'rejected') bg-danger
                                @endif">
                                {{ ucfirst($item->status) }}
                            </span>
                        </td>

                        {{-- ✅ Aksi Item --}}
                        <td class="item-action-cell">
                            @if($item->status == 'pending')
                               {{-- Approve --}}
                            <button
                                type="button"
                                class="btn btn-success btn-sm d-inline-flex align-items-center item-approve-btn"
                                data-item-id="{{ $item->id }}"
                                title="Setujui Item"
                            >
                                <i class="bi bi-check-lg me-1"></i> Approve
                            </button>

                            {{-- Reject --}}
                            <button
                                type="button"
                                class="btn btn-outline-danger btn-sm d-inline-flex align-items-center item-reject-btn"
                                data-item-id="{{ $item->id }}"
                                title="Tolak Item"
                            >
                                <i class="bi bi-x-lg me-1"></i> Reject
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
                        <td colspan="6" class="text-center text-muted">
                            Tidak ada item dalam permintaan ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
