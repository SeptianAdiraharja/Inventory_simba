document.addEventListener("DOMContentLoaded", () => {
    // =========================================================
    // 🧩 Menyimpan perubahan sementara (belum disimpan ke DB)
    // =========================================================
    const pendingChanges = {};

    // =========================================================
    // 🎨 NOTIFICATION HELPERS
    // =========================================================

    // Toast Bootstrap-style (pojok kanan atas)
    function showToast(message, type = "info") {
        // Buat container toast jika belum ada
        let container = document.getElementById("toast-container");
        if (!container) {
            container = document.createElement("div");
            container.id = "toast-container";
            container.className = "toast-container position-fixed top-0 end-0 p-3";
            container.style.zIndex = "9999";
            document.body.appendChild(container);
        }

        const icon =
            type === "success"
                ? "bi-check-circle-fill text-success"
                : type === "error"
                ? "bi-x-circle-fill text-danger"
                : "bi-info-circle-fill text-primary";

        const toast = document.createElement("div");
        toast.className = "toast align-items-center border-0 shadow-sm fade show mb-2";
        toast.style.background = "#fff";
        toast.innerHTML = `
            <div class="d-flex align-items-center p-3">
                <i class="bi ${icon} fs-5 me-2"></i>
                <div class="flex-grow-1">${message}</div>
                <button type="button" class="btn-close ms-3" data-bs-dismiss="toast"></button>
            </div>
        `;
        container.appendChild(toast);

        // Auto remove toast setelah 5 detik
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 5000);
    }

    // Snackbar (bawah tengah layar)
    function showSnackbar(message) {
        let snackbar = document.getElementById("snackbar");
        if (!snackbar) {
            snackbar = document.createElement("div");
            snackbar.id = "snackbar";
            snackbar.style.cssText = `
                visibility: hidden;
                min-width: 250px;
                margin-left: -125px;
                background-color: #333;
                color: #fff;
                text-align: center;
                border-radius: 2px;
                padding: 16px;
                position: fixed;
                z-index: 9999;
                left: 50%;
                bottom: 30px;
                font-size: 17px;
            `;
            document.body.appendChild(snackbar);
        }

        snackbar.textContent = message;
        snackbar.style.visibility = "visible";
        setTimeout(() => {
            snackbar.style.visibility = "hidden";
        }, 4000);
    }

    // =========================================================
    // 🔄 Fungsi update tampilan status item
    // =========================================================
    const updateItemUI = (itemRow, newStatus, temporary = true, reason = null) => {
        const badge = itemRow.querySelector(".item-status-cell .badge");
        const actionCell = itemRow.querySelector(".item-action-cell");

        if (badge) {
            badge.className = "badge";
            badge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);

            // Atur warna badge
            if (newStatus === "approved") badge.classList.add("bg-success");
            else if (newStatus === "rejected") badge.classList.add("bg-danger");
            else badge.classList.add("bg-warning", "text-dark");
        }

        if (actionCell) {
            if (newStatus === "approved") {
                actionCell.innerHTML = temporary
                    ? `<span class="text-success fw-semibold"><i class="bi bi-check-circle me-1"></i> Approved (Belum Disimpan)</span>`
                    : `<span class="text-success fw-semibold"><i class="bi bi-check-circle me-1"></i> Approved</span>`;
            } else if (newStatus === "rejected") {
                let reasonText = reason ? `<br><small class="text-muted fst-italic">Alasan: ${reason}</small>` : '';
                actionCell.innerHTML = temporary
                    ? `<span class="text-danger fw-semibold"><i class="bi bi-x-octagon me-1"></i> Rejected (Belum Disimpan)</span>${reasonText}`
                    : `<span class="text-danger fw-semibold"><i class="bi bi-x-octagon me-1"></i> Rejected</span>${reasonText}`;
            }
        }
    };

    // =========================================================
    // 📦 Klik tombol "Lihat Semua Barang" - FIXED VERSION
    // =========================================================
    function initializeDetailButtons() {
        document.querySelectorAll(".detail-toggle-btn").forEach((btn) => {
            // Hapus event listener lama untuk menghindari duplikasi
            btn.removeEventListener("click", handleDetailToggle);
            // Tambah event listener baru
            btn.addEventListener("click", handleDetailToggle);
        });
    }

    async function handleDetailToggle(e) {
        e.preventDefault();
        const btn = e.target.closest(".detail-toggle-btn");
        if (!btn) return;

        const cartId = btn.dataset.cartId;
        const container = document.getElementById(`detail-content-${cartId}`);
        const icon = btn.querySelector("i");

        if (!container) {
            console.error(`Container #detail-content-${cartId} tidak ditemukan`);
            showToast("Gagal memuat detail: Container tidak ditemukan", "error");
            return;
        }

        const isShown = container.classList.contains("show");

        // Tutup semua detail lainnya
        document.querySelectorAll(".detail-content-wrapper").forEach((el) => {
            if (el.id !== `detail-content-${cartId}`) {
                el.classList.remove("show");
            }
        });

        if (isShown) {
            // Tutup detail yang sedang terbuka
            container.classList.remove("show");
            if (icon) icon.className = "bi bi-chevron-down";
            return;
        }

        // Buka detail
        if (icon) icon.className = "bi bi-chevron-up";
        container.classList.add("show");

        // Load data jika belum diload
        if (container.dataset.loaded !== "true") {
            try {
                container.innerHTML = `
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2">Memuat data...</p>
                    </div>`;

                const response = await fetch(`/admin/carts/${cartId}`);

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.text();
                container.innerHTML = data;
                container.dataset.loaded = "true";

                // Re-initialize event listeners untuk elemen yang baru dimuat
                initializeEventListenersForDetail(container);

            } catch (error) {
                console.error("Gagal memuat detail:", error);
                container.innerHTML = `
                    <div class="text-center py-4">
                        <div class="text-danger">
                            <i class="bi bi-exclamation-triangle display-4"></i>
                            <p class="mt-2 fw-semibold">Gagal memuat data detail</p>
                            <small class="text-muted">${error.message}</small>
                        </div>
                    </div>`;
                showToast("Gagal memuat detail permintaan.", "error");
            }
        }
    }

    // =========================================================
    // 🔧 INITIALIZE EVENT LISTENERS UNTUK DETAIL YANG BARU DIMUAT
    // =========================================================
    function initializeEventListenersForDetail(container) {
        // Tombol approve item
        container.querySelectorAll(".item-approve-btn").forEach(btn => {
            btn.addEventListener("click", handleApproveItem);
        });

        // Tombol reject item
        container.querySelectorAll(".item-reject-btn").forEach(btn => {
            btn.addEventListener("click", handleRejectItem);
        });

        // Tombol simpan perubahan
        const saveBtn = container.querySelector(".cart-detail-save-btn");
        if (saveBtn) {
            saveBtn.addEventListener("click", handleSaveChanges);
        }

        // Tombol batal
        const cancelBtn = container.querySelector(".cart-detail-cancel-btn");
        if (cancelBtn) {
            cancelBtn.addEventListener("click", handleCancelChanges);
        }

        // Tombol approve all
        const approveAllBtn = container.querySelector(".approve-all-btn");
        if (approveAllBtn) {
            approveAllBtn.addEventListener("click", handleApproveAll);
        }

        // Tombol reject all
        const rejectAllBtn = container.querySelector(".reject-all-btn");
        if (rejectAllBtn) {
            rejectAllBtn.addEventListener("click", handleRejectAll);
        }
    }

    // =========================================================
    // 🎯 EVENT HANDLERS
    // =========================================================
    function handleApproveItem(e) {
        const btn = e.target.closest(".item-approve-btn");
        if (!btn) return;

        const itemId = btn.dataset.itemId;
        const itemRow = btn.closest("tr");
        const container = btn.closest(".detail-content-wrapper");
        const cartId = container.dataset.cartId;
        const newStatus = "approved";

        if (!pendingChanges[cartId]) pendingChanges[cartId] = {};
        pendingChanges[cartId][itemId] = { status: newStatus, reason: null };

        updateItemUI(itemRow, newStatus, true);
        showSnackbar(`Item disetujui (belum disimpan)`);
    }

    function handleRejectItem(e) {
        const rejectBtn = e.target.closest(".item-reject-btn");
        if (!rejectBtn) return;

        e.preventDefault();
        const itemId = rejectBtn.dataset.itemId;
        const container = rejectBtn.closest(".detail-content-wrapper");
        const cartId = container.dataset.cartId;

        // Dapatkan informasi stok dari data atribut
        const itemRow = rejectBtn.closest('tr');
        const itemName = itemRow.querySelector('td:nth-child(2)').textContent;
        const quantity = parseInt(itemRow.querySelector('td:nth-child(4)').textContent);

        const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
        const form = document.getElementById('rejectItemForm');

        // Atur data pada form modal untuk kasus Reject Item Satuan
        form.dataset.isBulk = 'false';
        form.dataset.cartId = cartId;
        form.dataset.itemId = itemId;
        form.dataset.itemName = itemName;
        form.dataset.quantity = quantity;

        // Ganti judul modal
        document.querySelector('#rejectModal .modal-title').innerHTML =
            `<i class="bi bi-x-circle me-2"></i> Alasan Penolakan Barang - ${itemName}`;
        document.querySelector('#rejectModal button[type="submit"]').textContent = 'Tolak Barang';

        // Kosongkan textarea dan tampilkan modal
        form.elements['reason'].value = '';
        modal.show();
    }

    async function handleSaveChanges(e) {
        const saveBtn = e.target.closest(".cart-detail-save-btn");
        if (!saveBtn) return;

        const container = saveBtn.closest(".detail-content-wrapper");
        const cartId = container.dataset.cartId;

        if (!pendingChanges[cartId] || Object.keys(pendingChanges[cartId]).length === 0) {
            showToast("Tidak ada perubahan untuk disimpan.", "info");
            return;
        }

        // Ubah format pendingChanges agar backend bisa memprosesnya
        const changesToSend = {};
        for (const itemId in pendingChanges[cartId]) {
            changesToSend[itemId] = {
                status: pendingChanges[cartId][itemId].status,
                reason: pendingChanges[cartId][itemId].reason || null
            };
        }

        // Log perubahan yang akan dikirim
        console.log('Perubahan yang akan disimpan:', {
            cartId: cartId,
            changes: changesToSend
        });

        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

        try {
            const res = await fetch(`/admin/carts/${cartId}/bulk-update`, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                    "Content-Type": "application/json",
                    Accept: "application/json",
                },
                body: JSON.stringify({ changes: changesToSend }),
            });

            const data = await res.json();
            if (!data.success) throw new Error(data.message);

            showToast("✅ Perubahan berhasil disimpan", "success");
            delete pendingChanges[cartId];

            // Update badge utama
            const mainBadge = document.getElementById(`main-status-${cartId}`);
            if (mainBadge) {
                mainBadge.textContent = data.cart_status.replace("_", " ");
                mainBadge.className = "badge";
                if (data.cart_status === "approved")
                    mainBadge.classList.add("bg-success");
                else if (data.cart_status === "approved_partially")
                    mainBadge.classList.add("bg-warning", "text-dark");
                else if (data.cart_status === "rejected")
                    mainBadge.classList.add("bg-danger");
            }

            // Reload detail untuk mendapatkan data terbaru
            container.dataset.loaded = "false";
            const resDetail = await fetch(`/admin/carts/${cartId}`);
            container.innerHTML = await resDetail.text();
            container.dataset.loaded = "true";
            initializeEventListenersForDetail(container);

        } catch (err) {
            console.error('Error saat menyimpan perubahan:', err);
            showToast("❌ Gagal menyimpan: " + err.message, "error");
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="bi bi-save me-1"></i> Simpan Perubahan';
        }
    }

    async function handleCancelChanges(e) {
        const cancelBtn = e.target.closest(".cart-detail-cancel-btn");
        if (!cancelBtn) return;

        const container = cancelBtn.closest(".detail-content-wrapper");
        const cartId = container.dataset.cartId;

        delete pendingChanges[cartId];
        container.dataset.loaded = "false";

        const res = await fetch(`/admin/carts/${cartId}`);
        container.innerHTML = await res.text();
        container.dataset.loaded = "true";
        initializeEventListenersForDetail(container);

        showSnackbar("Perubahan dibatalkan.");
    }

    async function handleApproveAll(e) {
        const approveAll = e.target.closest(".approve-all-btn");
        if (!approveAll) return;

        e.preventDefault();
        const btn = approveAll;
        const cartId = btn.dataset.cartId;
        const newStatus = "approved";
        const confirmMsg = `Yakin ingin menyetujui semua barang di permintaan ini?`;

        if (!confirm(confirmMsg)) return;

        try {
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Proses...`;
            btn.classList.add("disabled");

            const res = await fetch(`/admin/carts/${cartId}/bulk-update`, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                    "Content-Type": "application/json",
                    Accept: "application/json",
                },
                body: JSON.stringify({ status: newStatus }),
            });

            const data = await res.json();
            if (!data.success) throw new Error(data.message);

            showToast(`Semua item berhasil ${newStatus}.`, "success");

            // Update badge utama
            const mainBadge = document.getElementById(`main-status-${cartId}`);
            if (mainBadge) {
                mainBadge.textContent = newStatus;
                mainBadge.className = "badge bg-success";
            }

            // Jika detail sedang terbuka, reload detail-nya
            const container = document.getElementById(`detail-content-${cartId}`);
            if (container && container.classList.contains("show")) {
                container.dataset.loaded = "false";
                const resDetail = await fetch(`/admin/carts/${cartId}`);
                container.innerHTML = await resDetail.text();
                container.dataset.loaded = "true";
                initializeEventListenersForDetail(container);
            }
        } catch (err) {
            console.error(err);
            showToast("Gagal memproses semua item: " + err.message, "error");
        } finally {
            btn.classList.remove("disabled");
            btn.innerHTML = `<i class="bi bi-check-circle me-2"></i> Setujui Semua`;
        }
    }

    function handleRejectAll(e) {
        const rejectAll = e.target.closest(".reject-all-btn");
        if (!rejectAll) return;

        e.preventDefault();
        const btn = rejectAll;
        const cartId = btn.dataset.cartId;
        const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
        const form = document.getElementById('rejectItemForm');

        // Atur data pada form modal untuk kasus Reject All
        form.dataset.isBulk = 'true';
        form.dataset.cartId = cartId;
        form.dataset.itemId = '';

        // Ganti judul modal
        document.querySelector('#rejectModal .modal-title').innerHTML =
            '<i class="bi bi-x-circle me-2"></i> Alasan Penolakan Semua Barang';
        document.querySelector('#rejectModal button[type="submit"]').textContent = 'Tolak Semua Barang';

        // Tampilkan modal
        modal.show();
    }

    // =========================================================
    // 🚀 INITIALIZATION
    // =========================================================
    function initializeAll() {
        // Initialize detail buttons
        initializeDetailButtons();

        // Initialize modal form submit handler
        document.getElementById('rejectItemForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const reason = form.elements['reason'].value;
            const cartId = form.dataset.cartId;
            const isBulk = form.dataset.isBulk === 'true';
            const modalElement = document.getElementById('rejectModal');
            const modal = bootstrap.Modal.getInstance(modalElement);
            const submitBtn = form.querySelector('button[type="submit"]');

            if (reason.trim() === '') {
                showToast('Alasan penolakan wajib diisi.', 'error');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Proses...`;

            if (isBulk) {
                // --- LOGIKA REJECT ALL ---
                try {
                    const res = await fetch(`/admin/carts/${cartId}/bulk-update`, {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                            "Content-Type": "application/json",
                            Accept: "application/json",
                        },
                        body: JSON.stringify({ status: "rejected", reason: reason }),
                    });

                    const data = await res.json();
                    if (!data.success) throw new Error(data.message);

                    showToast(`Semua item berhasil ditolak.`, "success");

                    // Update badge utama
                    const mainBadge = document.getElementById(`main-status-${cartId}`);
                    if (mainBadge) {
                        mainBadge.textContent = "Rejected";
                        mainBadge.className = "badge bg-danger";
                    }

                    // Jika detail sedang terbuka, reload detail-nya
                    const container = document.getElementById(`detail-content-${cartId}`);
                    if (container && container.classList.contains("show")) {
                        container.dataset.loaded = "false";
                        const resDetail = await fetch(`/admin/carts/${cartId}`);
                        container.innerHTML = await resDetail.text();
                        container.dataset.loaded = "true";
                        initializeEventListenersForDetail(container);
                    }

                } catch (err) {
                    console.error(err);
                    showToast("Gagal menolak semua item: " + err.message, "error");
                } finally {
                    modal.hide();
                    submitBtn.disabled = false;
                    submitBtn.textContent = isBulk ? 'Tolak Semua Barang' : 'Tolak Barang';
                }
            } else {
                // --- LOGIKA REJECT SATUAN (Temporary Change) ---
                const itemId = form.dataset.itemId;
                const newStatus = 'rejected';

                if (!pendingChanges[cartId]) pendingChanges[cartId] = {};
                pendingChanges[cartId][itemId] = { status: newStatus, reason: reason };

                const itemRow = document.querySelector(`.detail-content-wrapper[data-cart-id="${cartId}"] tr[data-item-id="${itemId}"]`);
                if (itemRow) {
                    updateItemUI(itemRow, newStatus, true, reason);
                }

                showSnackbar(`Item ditolak (belum disimpan).`);
                modal.hide();
                submitBtn.disabled = false;
                submitBtn.textContent = 'Tolak Barang';
            }
        });
    }

    // Initialize semua ketika DOM siap
    initializeAll();
});