/**
 * Admin Produk Pegawai JavaScript
 * Handle scan barang, cart management, dan item operations untuk pegawai
 */

class ProdukPegawaiManager {
    constructor() {
        this.pegawaiId = window.PegawaiApp?.pegawaiId;
        this.csrf = window.PegawaiApp?.csrfToken;
        this.routes = window.PegawaiApp?.routes;

        this.cartBadge = document.getElementById('cartBadge');
        this.cartModalEl = document.getElementById('cartModal');
        this.cartModal = this.cartModalEl ? new bootstrap.Modal(this.cartModalEl) : null;
        this.cartContent = document.getElementById('cartContent');
        this.saveBtn = document.getElementById('saveCartButton');

        // Tambahkan variabel untuk track limit
        this.hasReachedLimit = false;
        this.weeklyCount = 0;

        this.init();
    }

    init() {
        this.bindEvents();
        this.updateCartBadge();
        this.checkLimit(); // Cek limit saat inisialisasi
    }

    bindEvents() {
        // Scan form events
        this.bindScanForms();

        // Cart events
        this.bindCartEvents();

        // Auto-focus barcode input when modal opens
        this.bindModalEvents();
    }

    bindScanForms() {
        document.querySelectorAll('.scan-form').forEach(form => {
            form.addEventListener('submit', (e) => this.handleScanSubmit(e));
        });

        // Tambahkan event listener untuk tombol scan
        document.querySelectorAll('.item-scan-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.handleScanButtonClick(e));
        });
    }

    // ✅ NEW: Handle klik tombol scan untuk cek limit
    async handleScanButtonClick(e) {
        // Cek limit terlebih dahulu sebelum membuka modal
        await this.checkLimit();

        if (this.hasReachedLimit) {
            e.preventDefault(); // Mencegah modal terbuka
            this.showLimitAlert();
            return;
        }

        // Jika belum mencapai limit, biarkan modal terbuka seperti biasa
    }

    bindCartEvents() {
        // Open cart modal
        document.getElementById('openCartModal').addEventListener('click', () => {
            if (this.cartModal) {
                this.cartModal.show();
                this.loadCart();
            }
        });

        // Save cart
        if (this.saveBtn) {
            this.saveBtn.addEventListener('click', (e) => this.handleSaveCart(e));
        }
    }

    bindModalEvents() {
        // Auto-focus on barcode input when modal opens
        document.querySelectorAll('.modal').forEach(modalEl => {
            modalEl.addEventListener('shown.bs.modal', function () {
                const barcodeInput = this.querySelector('input[name="barcode"]');
                if (barcodeInput) {
                    barcodeInput.focus();
                }
            });
        });
    }

    async handleScanSubmit(e) {
        e.preventDefault();

        // ✅ Cek limit sebelum proses scan
        await this.checkLimit();
        if (this.hasReachedLimit) {
            this.showLimitAlert();

            // Tutup modal jika sudah mencapai limit
            const modal = bootstrap.Modal.getInstance(e.target.closest('.modal'));
            if (modal) {
                modal.hide();
            }
            return;
        }

        const form = e.target;
        const submitBtn = form.querySelector('[type="submit"]');
        const originalText = submitBtn.innerHTML;

        // Disable button dan show loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i> Menyimpan...';

        try {
            const formData = new FormData(form);

            const response = await fetch(this.routes.scan, {
                method: "POST",
                body: formData,
                headers: {
                    "X-CSRF-TOKEN": this.csrf,
                    "Accept": "application/json"
                }
            });

            const result = await response.json();

            if (response.ok && result.success) {
                // Tutup modal
                const modal = bootstrap.Modal.getInstance(form.closest('.modal'));
                modal.hide();

                // Update UI
                this.updateCartBadge();
                this.loadCart();

                this.showToast('success', result.message || 'Barang berhasil ditambahkan ke keranjang!');

                // Update stok di UI
                this.updateItemStock(form);

            } else {
                throw new Error(result.message || 'Gagal menyimpan barang');
            }

        } catch (error) {
            console.error('Error:', error);
            this.showAlert('error', 'Gagal!', error.message || 'Terjadi kesalahan saat menyimpan barang.');
        } finally {
            // Reset button state
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    }

    // ✅ NEW: Function untuk cek limit
    async checkLimit() {
        try {
            const response = await fetch(this.routes.cart, {
                headers: { "X-Requested-With": "XMLHttpRequest" },
            });

            const result = await response.json();

            if (result.success) {
                this.hasReachedLimit = result.data?.has_reached_limit === true;
                this.weeklyCount = result.data?.weekly_request_count ?? 0;

                // Disable semua tombol scan jika mencapai limit
                this.toggleScanButtons(!this.hasReachedLimit);

                return this.hasReachedLimit;
            }
        } catch (error) {
            console.error('checkLimit error:', error);
        }
        return false;
    }

    // ✅ NEW: Function untuk toggle tombol scan
    toggleScanButtons(enabled) {
        document.querySelectorAll('.item-scan-btn').forEach(btn => {
            const originalDisabled = btn.getAttribute('data-original-disabled') === 'true';
            if (!enabled && !originalDisabled) {
                btn.disabled = true;
                btn.setAttribute('data-original-disabled', 'false');
                btn.classList.add('disabled');
            } else if (enabled && btn.getAttribute('data-original-disabled') === 'false') {
                btn.disabled = originalDisabled;
                btn.classList.remove('disabled');
            }
        });
    }

    // ✅ NEW: Function untuk menampilkan alert limit
    showLimitAlert() {
        Swal.fire({
            icon: 'warning',
            title: 'Batas Limit Tercapai!',
            html: `
                <div class="text-center">
                    <i class="ri-error-warning-line ri-2x text-warning mb-3"></i>
                    <p class="mb-2"><strong>Pegawai ini telah mencapai batas maksimal pengajuan mingguan!</strong></p>
                    <p class="text-muted">Limit: ${this.weeklyCount}/5 permintaan per minggu</p>
                    <small class="text-info">Tunggu hingga minggu depan untuk melakukan pengajuan baru.</small>
                </div>
            `,
            confirmButtonText: 'Mengerti',
            confirmButtonColor: '#FF9800',
            customClass: {
                popup: 'rounded-4',
                confirmButton: 'btn btn-primary rounded-pill px-4'
            }
        });
    }

    updateItemStock(form) {
        const itemId = form.querySelector('input[name="item_id"]').value;
        const itemCard = document.querySelector(`[data-item-id="${itemId}"]`);
        if (itemCard) {
            const stokEl = itemCard.querySelector('.text-success, .text-danger');
            if (stokEl) {
                let stok = parseInt(stokEl.textContent.trim()) || 0;
                const qty = parseInt(form.querySelector('input[name="quantity"]').value) || 0;
                stok = Math.max(0, stok - qty);
                stokEl.textContent = stok;
                stokEl.classList.toggle('text-danger', stok <= 0);
                stokEl.classList.toggle('text-success', stok > 0);

                // Disable button jika stok habis
                const scanBtn = itemCard.querySelector('.item-scan-btn');
                if (scanBtn && stok <= 0) {
                    scanBtn.disabled = true;
                    scanBtn.setAttribute('data-original-disabled', 'true');
                }
            }
        }
    }

    async handleSaveCart(e) {
        e.preventDefault();

        // ✅ Cek limit sebelum save
        await this.checkLimit();
        if (this.hasReachedLimit) {
            this.showLimitAlert();
            return;
        }

        const result = await Swal.fire({
            icon: "question",
            title: "Yakin simpan?",
            text: "Semua data di keranjang akan dipindahkan ke Item Out!",
            showCancelButton: true,
            confirmButtonText: "Ya, simpan",
            cancelButtonText: "Batal",
            reverseButtons: true,
        });

        if (!result.isConfirmed) return;

        this.saveBtn.disabled = true;
        const originalText = this.saveBtn.innerHTML;
        this.saveBtn.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i> Menyimpan...';

        try {
            const response = await fetch(this.routes.saveCart, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": this.csrf,
                    "Accept": "application/json",
                },
            });

            const result = await response.json();

            if (response.ok && result.success) {
                this.showToast('success', result.message || 'Data berhasil disimpan!');
                if (this.cartModal) this.cartModal.hide();
                this.updateCartBadge();
                this.loadCart();

                // ✅ Update limit status setelah save
                this.checkLimit();
            } else {
                throw new Error(result.message || 'Data gagal disimpan.');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('error', 'Gagal!', error.message || 'Terjadi kesalahan saat menyimpan.');
        } finally {
            this.saveBtn.disabled = false;
            this.saveBtn.innerHTML = originalText;
        }
    }

    async updateCartBadge() {
        try {
            const response = await fetch(this.routes.cart, {
                headers: { "X-Requested-With": "XMLHttpRequest" },
            });

            const result = await response.json();

            if (result.success) {
                const count = result.data?.items?.length || 0;
                if (this.cartBadge) {
                    this.cartBadge.textContent = count;
                    this.cartBadge.style.display = count > 0 ? 'inline-block' : 'none';
                }

                // ✅ Update limit status
                this.hasReachedLimit = result.data?.has_reached_limit === true;
                this.weeklyCount = result.data?.weekly_request_count ?? 0;
                this.toggleScanButtons(!this.hasReachedLimit);
            }
        } catch (error) {
            console.error('updateCartBadge error:', error);
        }
    }

    async loadCart() {
        if (this.cartContent) {
            this.cartContent.innerHTML = '<p class="text-center text-muted">Memuat data...</p>';
        }

        try {
            const response = await fetch(this.routes.cart, {
                headers: { "X-Requested-With": "XMLHttpRequest" },
            });

            const result = await response.json();

            if (result.success) {
                this.renderCartContent(result.data);
            } else {
                throw new Error(result.message || 'Gagal memuat data keranjang');
            }
        } catch (error) {
            console.error('loadCart error:', error);
            this.renderCartError();
        }
    }

    renderCartContent(data) {
        const items = data?.items || [];
        const weeklyCount = data?.weekly_request_count ?? 0;
        const reachedLimit = data?.has_reached_limit === true;

        // ✅ Update status limit
        this.hasReachedLimit = reachedLimit;
        this.weeklyCount = weeklyCount;
        this.toggleScanButtons(!reachedLimit);

        if (reachedLimit) {
            this.cartContent.innerHTML = `
                <div class="alert alert-warning text-center fw-bold" role="alert">
                    <i class="ri-error-warning-line me-2"></i>
                    🚫 Batas Limit Mingguan Tercapai (${weeklyCount}/5)
                </div>
                <p class="text-center text-secondary mb-0">
                    Tidak dapat menambahkan permintaan baru hingga minggu depan.
                </p>
            `;

            if (this.saveBtn) {
                this.saveBtn.disabled = true;
                this.saveBtn.classList.add('btn-secondary');
                this.saveBtn.classList.remove('btn-success');
                this.saveBtn.innerHTML = '<i class="ri-error-warning-line me-1"></i> Tidak Bisa Simpan (Limit)';
            }
            return;
        }

        if (items.length === 0) {
            this.cartContent.innerHTML = '<p class="text-center text-muted">Keranjang kosong.</p>';

            if (this.saveBtn) {
                this.saveBtn.disabled = true;
                this.saveBtn.classList.add('btn-secondary');
                this.saveBtn.classList.remove('btn-success');
                this.saveBtn.innerHTML = '<i class="ri-save-line me-1"></i> Simpan Keranjang';
            }
            return;
        }

        let html = `
            <div class="table-responsive">
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

        items.forEach((item, index) => {
            html += `
                <tr data-id="${item.cart_item_id}">
                    <td>${index + 1}</td>
                    <td class="text-start">${item.item?.name || '-'}</td>
                    <td>${item.item?.code || '-'}</td>
                    <td>${item.quantity}</td>
                    <td>
                        <span class="badge bg-success">${item.status || 'scanned'}</span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-danger delete-item" data-id="${item.cart_item_id}">
                            <i class="ri-delete-bin-line"></i> Hapus
                        </button>
                    </td>
                </tr>
            `;
        });

        html += `</tbody></table></div>`;
        this.cartContent.innerHTML = html;

        if (this.saveBtn) {
            this.saveBtn.disabled = false;
            this.saveBtn.classList.remove('btn-secondary');
            this.saveBtn.classList.add('btn-success');
            this.saveBtn.innerHTML = '<i class="ri-save-line me-1"></i> Simpan Keranjang';
        }

        this.bindDeleteEvents();
    }

    renderCartError() {
        this.cartContent.innerHTML = '<p class="text-danger text-center">Gagal memuat data keranjang.</p>';
    }

    bindDeleteEvents() {
        this.cartContent.querySelectorAll('.delete-item').forEach(btn => {
            btn.addEventListener('click', (e) => this.handleDeleteItem(e));
        });
    }

    async handleDeleteItem(e) {
        const btn = e.target.closest('.delete-item');
        const itemId = btn.dataset.id;

        const result = await Swal.fire({
            icon: 'warning',
            title: 'Hapus item?',
            text: 'Item ini akan dihapus dari keranjang.',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true,
        });

        if (!result.isConfirmed) return;

        try {
            const deleteUrl = this.routes.deleteItemBase + itemId;
            const response = await fetch(deleteUrl, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': this.csrf,
                    'Accept': 'application/json',
                },
            });

            const result = await response.json();

            if (result.success) {
                btn.closest('tr').remove();
                this.updateCartBadge();
                this.showToast('success', 'Item berhasil dihapus!');

                // Jika tidak ada item lagi
                if (!this.cartContent.querySelector('tbody tr')) {
                    this.cartContent.innerHTML = '<p class="text-center text-muted">Keranjang kosong.</p>';
                    if (this.saveBtn) {
                        this.saveBtn.disabled = true;
                        this.saveBtn.classList.add('btn-secondary');
                    }
                }
            } else {
                throw new Error(result.message || 'Gagal menghapus item');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('error', 'Gagal!', error.message || 'Terjadi kesalahan saat menghapus item.');
        }
    }

    // Helper functions
    showToast(icon, title, timer = 2000) {
        if (window.Swal) {
            Swal.fire({
                toast: true,
                position: "top-end",
                icon,
                title,
                showConfirmButton: false,
                timer,
                timerProgressBar: true,
            });
        }
    }

    showAlert(icon, title, text = "") {
        if (window.Swal) {
            Swal.fire({ icon, title, text });
        } else {
            alert(title + (text ? "\n" + text : ""));
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new ProdukPegawaiManager();
});