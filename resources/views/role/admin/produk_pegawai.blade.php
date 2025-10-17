@extends('layouts.index')

@section('content')
<div class="container-fluid py-3 animate__animated animate__fadeIn">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">
            <i class="ri-store-2-line me-2"></i> Produk Pegawai: {{ $pegawai->name }}
        </h4>
        <a href="{{ route('admin.pegawai.index') }}" class="btn btn-outline-secondary">
            <i class="ri-arrow-left-line me-1"></i> Kembali
        </a>
    </div>

    {{-- Daftar Produk --}}
    <div class="row gy-4">
        @foreach ($items as $item)
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden" data-item-id="{{ $item->id }}">
                <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}"
                     class="card-img-top" style="height: 180px; object-fit: cover;">
                <div class="card-body">
                    <h5 class="fw-semibold mb-1">{{ $item->name }}</h5>
                    <p class="text-muted small mb-1">
                        <i class="ri-folder-line me-1"></i> Kategori:
                        <span class="fw-semibold">{{ $item->category->name ?? '-' }}</span>
                    </p>
                    <p class="text-muted small mb-2">
                        <i class="ri-archive-line me-1"></i> Stok:
                        <span class="{{ $item->stock > 0 ? 'text-success' : 'text-danger' }}">
                            {{ $item->stock }}
                        </span>
                    </p>
                    <button class="btn btn-sm btn-primary w-100"
                            data-bs-toggle="modal"
                            data-bs-target="#scanModal"
                            data-item-id="{{ $item->id }}"
                            data-item-name="{{ $item->name }}">
                        <i class="ri-scan-line me-1"></i> Scan
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Tombol Cart --}}
    <button class="btn btn-primary rounded-circle shadow-lg position-fixed"
            id="cartButton"
            style="bottom: 20px; right: 25px; width: 60px; height: 60px; z-index: 1050;">
        <i class="ri-shopping-cart-2-line fs-3"></i>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
              id="cartBadge" style="display: none;">0</span>
    </button>
</div>

{{-- Modal Scan --}}
<div class="modal fade" id="scanModal" tabindex="-1" aria-labelledby="scanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Scan Kode Barang</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="scanForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="item_id" id="item_id">

                    <div class="mb-3">
                        <label class="form-label">Nama Barang</label>
                        <input type="text" class="form-control" id="item_name" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kode Barang</label>
                        <input type="text" name="barcode" id="barcode" class="form-control"
                               placeholder="Scan kode barang..." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jumlah</label>
                        <input type="number" name="quantity" id="quantity" min="1"
                               class="form-control" placeholder="Masukkan jumlah" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-3-line me-1"></i> Simpan
                    </button>
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Cart --}}
<div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Keranjang Pegawai</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div id="cartContent" class="table-responsive text-center text-muted">
                    Memuat data...
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-success" id="saveCartButton">
                    <i class="ri-save-3-line me-1"></i> Simpan ke Item Out
                </button>
                <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- Script --}}
@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", () => {
    const pegawaiId = {{ $pegawai->id }};
    const cartButton = document.getElementById('cartButton');
    const cartBadge = document.getElementById('cartBadge');
    const cartModal = new bootstrap.Modal(document.getElementById('cartModal'));
    const scanModalEl = document.getElementById('scanModal');
    const scanForm = document.getElementById('scanForm');
    const cartContent = document.getElementById('cartContent');

    const routes = {
        scan: `{{ url('admin/pegawai') }}/${pegawaiId}/scan`,
        cart: `{{ url('admin/pegawai') }}/${pegawaiId}/cart`,
        saveCart: `{{ url('admin/pegawai') }}/${pegawaiId}/cart/save`,
        deleteItem: (id) => `{{ url('admin/pegawai') }}/${pegawaiId}/cart/item/${id}`,
    };

    const csrf = '{{ csrf_token() }}';

    // isi modal scan
    scanModalEl.addEventListener('show.bs.modal', e => {
        const btn = e.relatedTarget;
        document.getElementById('item_id').value = btn.dataset.itemId;
        document.getElementById('item_name').value = btn.dataset.itemName;
        document.getElementById('barcode').value = '';
        document.getElementById('quantity').value = 1;
    });

    // simpan scan ke cart (AJAX)
    scanForm.addEventListener('submit', async e => {
        e.preventDefault();
        const submitBtn = scanForm.querySelector('[type="submit"]');
        submitBtn.disabled = true;

        const formData = new FormData(scanForm);
        // Trim barcode sebelum dikirim
        formData.set('barcode', formData.get('barcode')?.toString().trim());

        try {
            const res = await fetch(routes.scan, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: formData
            });

            const json = await res.json();

            if (res.ok && json.success) {
                // sukses
                bootstrap.Modal.getInstance(scanModalEl).hide();
                updateCartBadge();

                // Kurangi stok di Blade tanpa reload
                const itemId = document.getElementById('item_id').value;
                const card = document.querySelector(`[data-item-id="${itemId}"]`);
                if (card) {
                    const stokEl = card.querySelector('.text-success, .text-danger');
                    if (stokEl) {
                        let stok = parseInt(stokEl.textContent.trim()) || 0;
                        const qty = parseInt(document.getElementById('quantity').value) || 0;
                        stok = Math.max(0, stok - qty);
                        stokEl.textContent = stok;
                        stokEl.classList.toggle('text-danger', stok <= 0);
                        stokEl.classList.toggle('text-success', stok > 0);
                    }
                }

                // tampilkan cart hanya untuk item yg baru saja ditambahkan
                loadCart(json.data?.cart_id ? null : document.getElementById('item_id').value);
                cartModal.show();
                alert(json.message || 'Barang ditambahkan ke keranjang.');
            } else {
                // tampilkan pesan error dari server (validation / mismatch)
                const message = json.message || 'Gagal menambahkan barang.';
                alert(message);
            }
        } catch (err) {
            console.error(err);
            alert('Terjadi kesalahan saat menyimpan.');
        } finally {
            submitBtn.disabled = false;
        }
    });

    // tombol simpan cart ke item out
    document.getElementById('saveCartButton').addEventListener('click', async (e) => {
        e.preventDefault();
        if (!confirm('Simpan semua data ke Item Out?')) return;
        const btn = e.currentTarget;
        btn.disabled = true;

        try {
            const res = await fetch(routes.saveCart, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                }
            });
            const json = await res.json();

            if (res.ok && json.success) {
                alert(json.message || 'Data berhasil disimpan ke Item Out!');
                cartModal.hide();
                updateCartBadge();
                cartContent.innerHTML = `<p class="text-center text-muted">Keranjang kosong.</p>`;
            } else {
                alert(json.message || 'Gagal menyimpan data.');
            }
        } catch (err) {
            console.error(err);
            alert('Terjadi kesalahan saat menyimpan.');
        } finally {
            btn.disabled = false;
        }
    });

    // tampilkan cart
    cartButton.addEventListener('click', () => {
        cartModal.show();
        loadCart();
    });

    // fungsi untuk update badge cart
    async function updateCartBadge() {
        try {
            const res = await fetch(routes.cart, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const json = await res.json();
            const count = json.data?.items?.length || 0;
            cartBadge.textContent = count;
            cartBadge.style.display = count > 0 ? 'inline-block' : 'none';
        } catch (err) {
            console.error('updateCartBadge error', err);
        }
    }

    // fungsi untuk menampilkan isi cart
    async function loadCart(itemId = null) {
        cartContent.innerHTML = '<p class="text-center text-muted">Memuat data...</p>';
        let url = routes.cart;
        if (itemId) url += `?item_id=${itemId}`;

        try {
            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const json = await res.json();

            const items = json.data?.items || [];
            if (!items.length) {
                cartContent.innerHTML = `<p class="text-center text-muted">Keranjang kosong.</p>`;
                updateCartBadge();
                return;
            }

            let html = `
                <table class="table table-bordered align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Kode</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            items.forEach((it, i) => {
                html += `
                    <tr data-id="${it.cart_item_id}">
                        <td>${i + 1}</td>
                        <td class="text-start">${it.item?.name || '-'}</td>
                        <td>${it.item?.code || '-'}</td>
                        <td>${it.quantity}</td>
                        <td>${it.status || 'pending'}</td>
                        <td>
                            <button class="btn btn-sm btn-danger delete-item" data-id="${it.cart_item_id}">
                                <i class="ri-delete-bin-line"></i> Hapus
                            </button>
                        </td>
                    </tr>
                `;
            });

            html += '</tbody></table>';
            cartContent.innerHTML = html;

            // event hapus item
            cartContent.querySelectorAll('.delete-item').forEach(btn => {
                btn.addEventListener('click', async () => {
                    if (!confirm('Hapus item ini?')) return;
                    try {
                        const res = await fetch(routes.deleteItem(btn.dataset.id), {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                        });
                        const r = await res.json();
                        if (r.success) {
                            btn.closest('tr').remove();
                            updateCartBadge();
                            if (!cartContent.querySelector('tbody tr'))
                                cartContent.innerHTML = `<p class="text-center text-muted">Keranjang kosong.</p>`;
                        } else {
                            alert(r.message || 'Gagal menghapus item');
                        }
                    } catch (err) {
                        console.error(err);
                        alert('Terjadi kesalahan saat menghapus item.');
                    }
                });
            });

            updateCartBadge();
        } catch (err) {
            console.error(err);
            cartContent.innerHTML = `<p class="text-danger text-center">Gagal memuat data.</p>`;
        }
    }

    // initial badge load
    updateCartBadge();
});
</script>

@endpush
@endsection
