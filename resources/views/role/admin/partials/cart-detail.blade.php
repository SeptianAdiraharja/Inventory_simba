<div class="row detail-content-wrapper p-3" data-cart-id="{{ $cart->id }}">

    {{-- ============================= --}}
    {{-- 🧾 HEADER PERMINTAAN --}}
    {{-- ============================= --}}
    <div class="col-12 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="fw-bold mb-1">
                    Permintaan #{{ $cart->id }} — {{ $cart->user_name }}
                </h5>

                <p class="text-muted small mb-1">
                    Status Cart Utama:
                    <span id="main-status-{{ $cart->id }}"
                          class="badge
                              @if($cart->status == 'pending') bg-warning text-dark
                              @elseif($cart->status == 'rejected') bg-danger
                              @elseif($cart->status == 'approved') bg-success
                              @elseif($cart->status == 'approved_partially') bg-warning text-dark
                              @endif">
                        {{ ucfirst(str_replace('_', ' ', $cart->status)) }}
                    </span>
                </p>

                <p class="text-muted small mb-0">
                    Status Pemrosesan Item:
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

            {{-- TOMBOL SETUJUI SEMUA & TOLAK SEMUA --}}
            <div class="d-flex gap-2">
                @php
                    $isDisabled = in_array($cart->status, ['approved', 'approved_partially', 'rejected']);
                @endphp

                <button class="btn btn-success btn-sm approve-all-btn {{ $isDisabled ? 'disabled opacity-50' : '' }}"
                        data-cart-id="{{ $cart->id }}"
                        @if($isDisabled) disabled aria-disabled="true" @endif>
                    <i class="bi bi-check-circle me-1"></i> Setujui Semua
                </button>

                <button class="btn btn-outline-danger btn-sm reject-all-btn {{ $isDisabled ? 'disabled opacity-50' : '' }}"
                        data-cart-id="{{ $cart->id }}"
                        @if($isDisabled) disabled aria-disabled="true" @endif>
                    <i class="bi bi-x-octagon me-1"></i> Tolak Semua
                </button>
            </div>
        </div>
    </div>

    {{-- ============================= --}}
    {{-- 📋 TABEL ITEM --}}
    {{-- ============================= --}}
    <div class="col-12">
        <table class="table table-sm table-bordered align-middle mb-0">
            <thead class="table-dark text-center">
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Nama Barang</th>
                    <th>Kode</th>
                    <th style="width: 80px;">Jumlah</th>
                    <th style="width: 120px;">Status Item</th>
                    <th style="width: 160px;">Aksi Item</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cartItems as $i => $item)
                @php
                    $itemStock = \App\Models\Item::where('id', $item->item_id)->value('stock') ?? 0;
                    $isStockSufficient = $itemStock >= $item->quantity;
                @endphp
                <tr class="text-center" data-item-id="{{ $item->id }}">
                    <td>{{ $i + 1 }}</td>
                    <td class="text-start">{{ $item->item_name }}</td>
                    <td>{{ $item->item_code }}</td>
                    <td class="fw-semibold">{{ $item->quantity }}</td>

                    {{-- ✅ STATUS ITEM --}}
                    <td class="item-status-cell">
                        <span class="badge
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
                            {{-- Setujui --}}
                            <button type="button"
                                    class="btn btn-success btn-sm d-inline-flex align-items-center item-approve-btn"
                                    data-item-id="{{ $item->id }}"
                                    title="Setujui Item">
                                <i class="bi bi-check-lg me-1"></i> Setujui
                            </button>
                            {{-- Tolak --}}
                            <button type="button"
                                    class="btn btn-outline-danger btn-sm d-inline-flex align-items-center item-reject-btn"
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
                                @if($item->rejection_reason)
                                <br><small class="text-muted fst-italic">Alasan: {{ $item->rejection_reason }}</small>
                                @endif
                            </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-3">
                        Tidak ada item dalam permintaan ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ============================= --}}
    {{-- ⚙️ FOOTER AKSI --}}
    {{-- ============================= --}}
    <div class="col-12 mt-3 d-flex justify-content-end gap-2">
        @php
            // Tombol Batal akan disabled jika semua item sudah diproses (tidak ada yang pending)
            $allItemsProcessed = !$cartItems->contains('status', 'pending');
            $disableSave = in_array($cart->status, ['approved', 'approved_partially', 'rejected']);
        @endphp

        <button type="button"
                class="btn btn-outline-secondary cart-detail-cancel-btn {{ $allItemsProcessed ? 'disabled opacity-50' : '' }}"
                @if($allItemsProcessed) disabled aria-disabled="true" @endif>
            <i class="bi bi-x-circle me-1"></i> Batal
        </button>

        <button type="button"
                class="btn btn-primary cart-detail-save-btn {{ $disableSave ? 'disabled opacity-50' : '' }}"
                @if($disableSave) disabled aria-disabled="true" @endif>
            <i class="bi bi-save me-1"></i> Simpan Perubahan
        </button>
    </div>
</div>