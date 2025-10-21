document.addEventListener("DOMContentLoaded", () => {
    // baca data dari window (diset oleh Blade)
    const pegawaiId = window.PegawaiApp?.id ?? null;
    const csrf = window.PegawaiApp?.csrf ?? '';

    const routes = {
        scan: window.PegawaiApp?.routes?.scan ?? `/admin/pegawai/${pegawaiId}/scan`,
        cart: window.PegawaiApp?.routes?.cart ?? `/admin/pegawai/${pegawaiId}/cart`,
        saveCart: window.PegawaiApp?.routes?.saveCart ?? `/admin/pegawai/${pegawaiId}/cart/save`,
        deleteItem: (id) => (window.PegawaiApp?.routes?.deleteItemBase ? `${window.PegawaiApp.routes.deleteItemBase}/${id}` : `/admin/pegawai/${pegawaiId}/cart/item/${id}`)
    };

    const cartButton = document.getElementById('cartButton');
    const cartBadge = document.getElementById('cartBadge');
    const cartModalEl = document.getElementById('cartModal');
    const cartModal = cartModalEl ? new bootstrap.Modal(cartModalEl) : null;
    const scanModalEl = document.getElementById('scanModal');
    const scanForm = document.getElementById('scanForm');
    const cartContent = document.getElementById('cartContent');

    // helper Swal
    function showToast(icon, title, timer = 2000) {
        if (window.Swal) {
            Swal.fire({ toast: true, position: 'top-end', icon, title, showConfirmButton: false, timer, timerProgressBar: true });
        } else {
            console[icon === 'error' ? 'error' : 'log'](title);
        }
    }
    function showAlert(icon, title, text = '') {
        if (window.Swal) {
            Swal.fire({ icon, title, text });
        } else {
            alert(title + (text ? '\n' + text : ''));
        }
    }

    // safety checks
    if (!scanForm) {
        console.warn('scanForm not found on page');
        return;
    }

    // modal show - isi fields
    if (scanModalEl) {
        scanModalEl.addEventListener('show.bs.modal', e => {
            const btn = e.relatedTarget;
            if (!btn) return;
            document.getElementById('item_id').value = btn.dataset.itemId ?? '';
            document.getElementById('item_name').value = btn.dataset.itemName ?? '';
            document.getElementById('barcode').value = '';
            document.getElementById('quantity').value = 1;
        });
    }

    // submit scanForm (AJAX)
    scanForm.addEventListener('submit', async e => {
        e.preventDefault();
        const submitBtn = scanForm.querySelector('[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

        const formData = new FormData(scanForm);
        const barcodeVal = (formData.get('barcode') || '').toString().trim();
        formData.set('barcode', barcodeVal);

        try {
            const res = await fetch(routes.scan, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: formData
            });

            const json = await res.json();

            if (res.ok && json.success) {
                // hide modal
                if (scanModalEl) bootstrap.Modal.getInstance(scanModalEl).hide();

                // update UI
                updateCartBadge();

                // kurangi stok di card (pure UI)
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

                // tampilkan cart
                loadCart();
                if (cartModal) cartModal.show();

                showToast('success', json.message || 'Barang ditambahkan ke keranjang!');
            } else {
                showAlert('error', 'Gagal', json.message || 'Barcode tidak cocok!');
            }
        } catch (err) {
            console.error(err);
            showAlert('error', 'Terjadi Kesalahan', 'Silakan coba lagi.');
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
    });

    // simpan cart ke item_out
    const saveBtn = document.getElementById('saveCartButton');
    if (saveBtn) {
        saveBtn.addEventListener('click', async (e) => {
            e.preventDefault();

            if (!window.Swal) {
                // fallback confirm
                if (!confirm('Yakin simpan? Semua data di keranjang akan dipindahkan ke Item Out!')) return;
            } else {
                const result = await Swal.fire({
                    icon: 'question',
                    title: 'Yakin simpan?',
                    text: 'Semua data di keranjang akan dipindahkan ke Item Out!',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, simpan',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                });
                if (!result.isConfirmed) return;
            }

            saveBtn.disabled = true;
            try {
                const res = await fetch(routes.saveCart, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
                });
                const json = await res.json();
                if (res.ok && json.success) {
                    showToast('success', json.message || 'Data disimpan ke Item Out!');
                    if (cartModal) cartModal.hide();
                    updateCartBadge();
                    if (cartContent) cartContent.innerHTML = `<p class="text-center text-muted">Keranjang kosong.</p>`;
                } else {
                    showAlert('error', 'Gagal', json.message || 'Data gagal disimpan.');
                }
            } catch (err) {
                console.error(err);
                showAlert('error', 'Terjadi Kesalahan', 'Silakan coba lagi.');
            } finally {
                saveBtn.disabled = false;
            }
        });
    }

    // tombol lihat cart
    if (cartButton) {
        cartButton.addEventListener('click', () => {
            if (cartModal) cartModal.show();
            loadCart();
        });
    }

    // update badge
    async function updateCartBadge() {
        try {
            const res = await fetch(routes.cart, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const json = await res.json();
            const count = json.data?.items?.length || 0;
            if (cartBadge) {
                cartBadge.textContent = count;
                cartBadge.style.display = count > 0 ? 'inline-block' : 'none';
            }
        } catch (err) {
            console.error('updateCartBadge error', err);
        }
    }

    // load cart content
    async function loadCart() {
        if (cartContent) cartContent.innerHTML = '<p class="text-center text-muted">Memuat data...</p>';
        try {
            const res = await fetch(routes.cart, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const json = await res.json();
            const items = json.data?.items || [];

            if (!items.length) {
                if (cartContent) cartContent.innerHTML = `<p class="text-center text-muted">Keranjang kosong.</p>`;
                updateCartBadge();
                return;
            }

            let html = `
                <table class="table table-bordered align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th>No</th><th>Nama Barang</th><th>Kode</th><th>Jumlah</th><th>Status</th><th>Aksi</th>
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
            if (cartContent) cartContent.innerHTML = html;

            // attach delete handlers
            cartContent.querySelectorAll('.delete-item').forEach(btn => {
                btn.addEventListener('click', async () => {
                    let confirmed = true;
                    if (window.Swal) {
                        const r = await Swal.fire({
                            icon: 'warning',
                            title: 'Hapus item?',
                            text: 'Item ini akan dihapus dari keranjang.',
                            showCancelButton: true,
                            confirmButtonText: 'Ya, hapus',
                            cancelButtonText: 'Batal',
                            reverseButtons: true
                        });
                        confirmed = r.isConfirmed;
                    } else {
                        confirmed = confirm('Hapus item?');
                    }
                    if (!confirmed) return;

                    try {
                        const res = await fetch(routes.deleteItem(btn.dataset.id), {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                        });
                        const r = await res.json();
                        if (r.success) {
                            btn.closest('tr').remove();
                            updateCartBadge();
                            showToast('success', 'Item dihapus!');
                            if (!cartContent.querySelector('tbody tr')) cartContent.innerHTML = `<p class="text-center text-muted">Keranjang kosong.</p>`;
                        } else {
                            showAlert('error', 'Gagal', r.message || 'Tidak dapat menghapus item.');
                        }
                    } catch (err) {
                        console.error(err);
                        showAlert('error', 'Terjadi Kesalahan', 'Silakan coba lagi.');
                    }
                });
            });

            updateCartBadge();
        } catch (err) {
            console.error(err);
            if (cartContent) cartContent.innerHTML = `<p class="text-danger text-center">Gagal memuat data.</p>`;
        }
    }

    // initial
    updateCartBadge();
});
