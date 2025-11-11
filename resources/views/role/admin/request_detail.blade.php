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

    <div class="btn-group" role="group" aria-label="Basic example">
        <button type="button" class="btn btn-secondary">Left</button>
        <button type="button" class="btn btn-secondary">Middle</button>
        <button type="button" class="btn btn-secondary">Right</button>
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
                                <span class="badge rounded-pill px-3 py-2 shadow-sm status-badge
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
        <button type="button"
                class="btn btn-outline-secondary rounded-pill px-4 shadow-sm cart-detail-cancel-btn
                    @if($scan_status == 'Selesai') disabled @endif"
                @if($scan_status == 'Selesai') disabled @endif>
            <i class="bi bi-x-circle me-1"></i> Batal
        </button>

        {{-- Tombol Simpan dengan Spinner --}}
        <button type="button"
                class="btn btn-primary rounded-pill px-4 shadow-sm cart-detail-save-btn d-inline-flex align-items-center gap-2
                    @if($scan_status == 'Selesai') disabled @endif"
                @if($scan_status == 'Selesai') disabled @endif>
            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
            <i class="bi bi-save"></i>
            <span>
                @if($scan_status == 'Selesai')
                    Semua Item Telah Diproses
                @else
                    Simpan Perubahan
                @endif
            </span>
        </button>
    </div>
</div>

{{-- ============================= --}}
{{-- 💡 SCRIPT UNTUK SIMPAN SEBAGIAN DENGAN SPINNER --}}
{{-- ============================= --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const saveBtn = document.querySelector('.cart-detail-save-btn');
        const cancelBtn = document.querySelector('.cart-detail-cancel-btn');
        const spinner = saveBtn.querySelector('.spinner-border');
        const btnText = saveBtn.querySelector('span:last-child');

        // ✅ Fungsi untuk mengecek apakah semua item sudah diproses
        function allItemsProcessed() {
            const rows = document.querySelectorAll('tr[data-item-id]');
            return Array.from(rows).every(row => {
                const status = row.querySelector('.status-badge')?.textContent.trim().toLowerCase();
                return status === 'approved' || status === 'rejected';
            });
        }

        // ✅ Fungsi untuk update state tombol
        function updateButtonStates() {
            const allProcessed = allItemsProcessed();

            if (allProcessed) {
                // Disable tombol Simpan
                saveBtn.disabled = true;
                saveBtn.classList.add('disabled');
                btnText.textContent = 'Semua Item Telah Diproses';

                // Disable tombol Batal
                cancelBtn.disabled = true;
                cancelBtn.classList.add('disabled');
                cancelBtn.classList.remove('btn-outline-secondary');
                cancelBtn.classList.add('btn-secondary');
            } else {
                // Enable tombol Simpan
                saveBtn.disabled = false;
                saveBtn.classList.remove('disabled');
                btnText.textContent = 'Simpan Perubahan';

                // Enable tombol Batal
                cancelBtn.disabled = false;
                cancelBtn.classList.remove('disabled');
                cancelBtn.classList.remove('btn-secondary');
                cancelBtn.classList.add('btn-outline-secondary');
            }
        }

        // ✅ Event handler untuk tombol Batal
        cancelBtn.addEventListener('click', function(e) {
            if (this.disabled) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }

            // Logika untuk tombol batal (jika ada)
            console.log('Tombol batal ditekan');
            // window.history.back(); // atau logika lainnya
        });

        // ✅ Jika semua item sudah diproses → disable tombol sejak awal
        updateButtonStates();

        // ✅ Event klik untuk simpan perubahan
        saveBtn.addEventListener('click', function() {
            if (this.disabled) return;

            const cartId = document.querySelector('.detail-content-wrapper').dataset.cartId;
            const updatedItems = [];

            document.querySelectorAll('tr[data-item-id]').forEach(row => {
                const id = row.dataset.itemId;
                const status = row.querySelector('.status-badge')?.textContent.trim().toLowerCase();
                if (status !== 'pending') {
                    updatedItems.push({ id, status });
                }
            });

            if (updatedItems.length === 0) {
                alert('Tidak ada perubahan yang perlu disimpan.');
                return;
            }

            // Aktifkan spinner dan disable tombol sementara
            saveBtn.disabled = true;
            cancelBtn.disabled = true;
            spinner.classList.remove('d-none');
            btnText.textContent = 'Menyimpan...';

            fetch(`/cart/${cartId}/save-partial`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ items: updatedItems })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    btnText.textContent = 'Tersimpan!';
                    saveBtn.classList.replace('btn-primary', 'btn-success');
                    setTimeout(() => {
                        btnText.textContent = 'Simpan Perubahan';
                        saveBtn.classList.replace('btn-success', 'btn-primary');
                    }, 2000);
                } else {
                    alert('Gagal menyimpan perubahan.');
                }
            })
            .catch(() => alert('Terjadi kesalahan server.'))
            .finally(() => {
                spinner.classList.add('d-none');

                // Update state tombol berdasarkan kondisi terkini
                updateButtonStates();
            });
        });

        // ✅ Event klik approve/reject item
        document.querySelectorAll('.item-approve-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const row = this.closest('tr');
                const badge = row.querySelector('.status-badge');
                badge.textContent = 'Approved';
                badge.className = 'badge rounded-pill px-3 py-2 shadow-sm status-badge bg-success';

                // Update state tombol setelah perubahan
                updateButtonStates();
            });
        });

        document.querySelectorAll('.item-reject-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const row = this.closest('tr');
                const badge = row.querySelector('.status-badge');
                badge.textContent = 'Rejected';
                badge.className = 'badge rounded-pill px-3 py-2 shadow-sm status-badge bg-danger';

                // Update state tombol setelah perubahan
                updateButtonStates();
            });
        });
    });
</script>
@endsection