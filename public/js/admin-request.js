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
        const container = document.getElementById("toast-container");
        const icon =
            type === "success"
                ? "bi-check-circle-fill text-success"
                : type === "error"
                ? "bi-x-circle-fill text-danger"
                : "bi-info-circle-fill text-primary";

        const toast = document.createElement("div");
        toast.className =
            "toast align-items-center border-0 shadow-sm fade show mb-2";
        toast.style.background = "#fff";
        toast.innerHTML = `
            <div class="d-flex align-items-center p-3">
                <i class="bi ${icon} fs-5 me-2"></i>
                <div class="flex-grow-1">${message}</div>
                <button type="button" class="btn-close ms-3" data-bs-dismiss="toast"></button>
            </div>
        `;
        container.appendChild(toast);
        // Menggunakan Bootstrap Toast API jika tersedia, jika tidak pakai setTimeout
        try {
            const bootstrapToast = new bootstrap.Toast(toast, { delay: 3000 });
            bootstrapToast.show();
            toast.addEventListener('hidden.bs.toast', () => toast.remove());
        } catch (e) {
            setTimeout(() => toast.classList.remove("show"), 3000);
            setTimeout(() => toast.remove(), 3500);
        }
    }

    // Snackbar (bawah tengah layar)
    function showSnackbar(message) {
        const snackbar = document.getElementById("snackbar");
        snackbar.textContent = message;
        snackbar.className = "show";
        setTimeout(
            () => (snackbar.className = snackbar.className.replace("show", "")),
            4000
        );
    }

    // =========================================================
    // 🔄 Fungsi update tampilan status item
    // =========================================================
    const updateItemUI = (itemRow, newStatus, temporary = true, reason = null) => {
        const badge = itemRow.querySelector(".item-status-cell .badge");
        const actionCell = itemRow.querySelector(".item-action-cell");

        badge.className = "badge";
        badge.textContent =
            newStatus.charAt(0).toUpperCase() + newStatus.slice(1);

        // Atur warna badge
        if (newStatus === "approved") badge.classList.add("bg-success");
        else if (newStatus === "rejected") badge.classList.add("bg-danger");
        else badge.classList.add("bg-warning", "text-dark");

        // Atur isi sel aksi
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
    };


    // =========================================================
    // ✅ APPROVE ALL (langsung update ke DB) - TIDAK BERUBAH
    // =========================================================
    document.addEventListener("click", async (e) => {
        const approveAll = e.target.closest(".approve-all-btn");
        if (!approveAll) return;

        e.preventDefault();
        const btn = approveAll;
        const cartId = btn.dataset.cartId;
        const newStatus = "approved";
        const confirmMsg = `Yakin ingin menyetujui semua barang di permintaan ini?`;

        if (!confirm(confirmMsg)) return;

        try {
            // Tampilkan loading kecil
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Proses...`;
            btn.classList.add("disabled");

            const res = await fetch(`/admin/carts/${cartId}/bulk-update`, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]'
                    ).content,
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
            const container = document.getElementById(
                `detail-content-${cartId}`
            );
            if (container && container.classList.contains("show")) {
                container.dataset.loaded = "false";
                const resDetail = await fetch(`/admin/carts/${cartId}`);
                container.innerHTML = await resDetail.text();
                container.dataset.loaded = "true";
            }
        } catch (err) {
            console.error(err);
            showToast("Gagal memproses semua item: " + err.message, "error");
        } finally {
            btn.classList.remove("disabled");
            btn.innerHTML = `<i class="bi bi-check-circle me-2"></i> Setujui Semua`;
        }
    });

    // =========================================================
    // ❌ REJECT ALL (Memicu Modal)
    // =========================================================
    document.addEventListener("click", (e) => {
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
        form.dataset.itemId = ''; // Kosongkan

        // Ganti judul modal
        document.querySelector('#rejectModal .modal-title').innerHTML =
            '<i class="bi bi-x-circle me-2"></i> Alasan Penolakan Semua Barang';
        document.querySelector('#rejectModal .btn-danger').textContent = 'Tolak Semua Barang';

        // Tampilkan modal
        modal.show();
    });

    // =========================================================
    // ⚡ Klik Reject Item Satuan (Memicu Modal)
    // =========================================================
    document.addEventListener("click", (e) => {
        const rejectBtn = e.target.closest(".item-reject-btn");
        if (!rejectBtn) return;

        e.preventDefault();
        const itemId = rejectBtn.dataset.itemId;
        const container = rejectBtn.closest(".detail-content-wrapper");
        const cartId = container.dataset.cartId;

        const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
        const form = document.getElementById('rejectItemForm');

        // Atur data pada form modal untuk kasus Reject Item Satuan
        form.dataset.isBulk = 'false';
        form.dataset.cartId = cartId;
        form.dataset.itemId = itemId;

        // Ganti judul modal
        document.querySelector('#rejectModal .modal-title').innerHTML =
            '<i class="bi bi-x-circle me-2"></i> Alasan Penolakan Barang';
        document.querySelector('#rejectModal .btn-danger').textContent = 'Tolak Barang';

        // Kosongkan textarea dan tampilkan modal
        form.elements['reason'].value = '';
        modal.show();
    });

    // =========================================================
    // 📝 SUBMIT FORM MODAL PENOLAKAN
    // =========================================================
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
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]'
                        ).content,
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
                }

            } catch (err) {
                console.error(err);
                showToast("Gagal menolak semua item: " + err.message, "error");
            } finally {
                modal.hide();
                submitBtn.disabled = false;
                submitBtn.textContent = 'Tolak Semua Barang';
            }

        } else {
            // --- LOGIKA REJECT SATUAN (Temporary Change) ---
            const itemId = form.dataset.itemId;
            const newStatus = 'rejected';

            if (!pendingChanges[cartId]) pendingChanges[cartId] = {};
            pendingChanges[cartId][itemId] = { status: newStatus, reason: reason }; // Simpan reason juga!

            const itemRow = document.querySelector(`.detail-content-wrapper[data-cart-id="${cartId}"] tr[data-item-id="${itemId}"]`);
            if (itemRow) {
                updateItemUI(itemRow, newStatus, true, reason);
            }

            showSnackbar(`Item ditolak (belum disimpan).`);
            modal.hide();

            // Kembalikan tombol ke keadaan semula
            submitBtn.disabled = false;
            submitBtn.textContent = 'Tolak Barang';
        }
    });


    // =========================================================
    // 📦 Klik tombol "Detail (Lihat Barang)" - TIDAK BERUBAH
    // =========================================================
    document.querySelectorAll(".detail-toggle-btn").forEach((btn) => {
        btn.addEventListener("click", async (e) => {
            e.preventDefault();
            const cartId = btn.dataset.cartId;
            const container = document.getElementById(
                `detail-content-${cartId}`
            );
            const icon = btn.querySelector("i");

            const isShown = container.classList.contains("show");
            document
                .querySelectorAll(".detail-content-wrapper")
                .forEach((el) => el.classList.remove("show"));

            if (isShown) {
                icon.className = "bi bi-chevron-down";
                return;
            }

            icon.className = "bi bi-chevron-up";
            container.classList.add("show");

            if (container.dataset.loaded !== "true") {
                try {
                    container.innerHTML = `
                        <div class="text-center py-3">
                            <div class="spinner-border text-primary" role="status"></div>
                        </div>`;
                    const res = await fetch(`/admin/carts/${cartId}`);
                    if (!res.ok) throw new Error(`HTTP ${res.status}`);
                    container.innerHTML = await res.text();
                    container.dataset.loaded = "true";
                } catch (err) {
                    console.error("Gagal memuat detail:", err);
                    container.innerHTML = `<p class="text-danger text-center m-0">⚠️ Gagal memuat data detail.</p>`;
                    showToast("Gagal memuat detail permintaan.", "error");
                }
            }
        });
    });

    // =========================================================
    // ⚡ Klik Approve Item Satuan (tanpa kirim ke DB) - BERUBAH SEDIKIT
    // =========================================================
    document.addEventListener("click", (e) => {
        const approveBtn = e.target.closest(".item-approve-btn");

        if (approveBtn) {
            const btn = approveBtn;
            const itemId = btn.dataset.itemId;
            const itemRow = btn.closest("tr");
            const container = btn.closest(".detail-content-wrapper");
            const cartId = container.dataset.cartId;
            const newStatus = "approved";

            if (!pendingChanges[cartId]) pendingChanges[cartId] = {};
            pendingChanges[cartId][itemId] = { status: newStatus, reason: null }; // Hapus reason jika ada

            updateItemUI(itemRow, newStatus, true);

            showSnackbar(`Item disetujui (belum disimpan)`);
        }
    });

    // =========================================================
    // 💾 Klik "Simpan Perubahan" → kirim semua pendingChanges - BERUBAH UNTUK MENGIRIM REASON
    // =========================================================
    document.addEventListener("click", async (e) => {
        const saveBtn = e.target.closest(".cart-detail-save-btn");
        if (!saveBtn) return;

        const container = saveBtn.closest(".detail-content-wrapper");
        const cartId = container.dataset.cartId;

        if (
            !pendingChanges[cartId] ||
            Object.keys(pendingChanges[cartId]).length === 0
        ) {
            showToast("Tidak ada perubahan untuk disimpan.", "info");
            return;
        }

        // Ubah format pendingChanges agar backend bisa memprosesnya (item_id: {status: 'x', reason: 'y'})
        // dan pastikan hanya mengirim yang perlu diupdate.
        const changesToSend = {};
        for (const itemId in pendingChanges[cartId]) {
            // Asumsi backend hanya perlu status dan reason
            changesToSend[itemId] = {
                status: pendingChanges[cartId][itemId].status,
                reason: pendingChanges[cartId][itemId].reason || null
            };
        }

        saveBtn.disabled = true;
        saveBtn.innerHTML =
            '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

        try {
            const res = await fetch(`/admin/carts/${cartId}/bulk-update`, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]'
                    ).content,
                    "Content-Type": "application/json",
                    Accept: "application/json",
                },
                body: JSON.stringify({ changes: changesToSend }),
            });

            const data = await res.json();
            if (!data.success) throw new Error(data.message);

            showToast(
                "✅ Perubahan berhasil disimpan, Silahkan lanjutkan ke Halaman Scan QR",
                "success"
            );
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

            // Update UI item (hapus label belum disimpan)
            const rows = container.querySelectorAll("tr[data-item-id]");
            rows.forEach((r) => {
                const itemId = r.dataset.itemId;
                if (data.items && data.items[itemId]) {
                    const status = data.items[itemId].status;
                    const reason = data.items[itemId].reason || null;
                    updateItemUI(r, status, false, reason);
                }
            });
        } catch (err) {
            showToast("❌ Gagal menyimpan: " + err.message, "error");
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML =
                '<i class="bi bi-save me-1"></i> Simpan Perubahan';
        }
    });

    // =========================================================
    // ❌ Klik "Batal" → reload detail - TIDAK BERUBAH
    // =========================================================
    document.addEventListener("click", async (e) => {
        const cancelBtn = e.target.closest(".cart-detail-cancel-btn");
        if (!cancelBtn) return;

        const container = cancelBtn.closest(".detail-content-wrapper");
        const cartId = container.dataset.cartId;

        delete pendingChanges[cartId];
        container.dataset.loaded = "false";

        // Asumsi `bootstrap` sudah global
        const res = await fetch(`/admin/carts/${cartId}`);
        container.innerHTML = await res.text();
        container.dataset.loaded = "true";

        showSnackbar("Perubahan dibatalkan.");
    });

    // Inisialisasi Bootstrap (agar modal bisa dipanggil)
    // Cek apakah Bootstrap sudah dimuat atau tidak.
    if (typeof bootstrap === 'undefined') {
        console.warn("Bootstrap JS mungkin belum dimuat. Fungsi modal mungkin tidak bekerja.");
    }

});