document.addEventListener("DOMContentLoaded", () => {
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
        setTimeout(() => toast.classList.remove("show"), 3000);
        setTimeout(() => toast.remove(), 3500);
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
    // 🧩 Menyimpan perubahan sementara (belum disimpan ke DB)
    // =========================================================
    const pendingChanges = {};

    // =========================================================
    // 📦 Klik tombol "Detail (Lihat Barang)"
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
    // 🔄 Fungsi update tampilan status item
    // =========================================================
    const updateItemUI = (itemRow, newStatus, temporary = true) => {
        const badge = itemRow.querySelector(".item-status-cell .badge");
        const actionCell = itemRow.querySelector(".item-action-cell");

        badge.className = "badge";
        badge.textContent =
            newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
        if (newStatus === "approved") badge.classList.add("bg-success");
        else if (newStatus === "rejected") badge.classList.add("bg-danger");
        else badge.classList.add("bg-warning", "text-dark");

        if (newStatus === "approved") {
            actionCell.innerHTML = temporary
                ? `<span class="text-success fw-semibold"><i class="bi bi-check-circle me-1"></i> Approved (Belum Disimpan)</span>`
                : `<span class="text-success fw-semibold"><i class="bi bi-check-circle me-1"></i> Approved</span>`;
        } else if (newStatus === "rejected") {
            actionCell.innerHTML = temporary
                ? `<span class="text-danger fw-semibold"><i class="bi bi-x-octagon me-1"></i> Rejected (Belum Disimpan)</span>`
                : `<span class="text-danger fw-semibold"><i class="bi bi-x-octagon me-1"></i> Rejected</span>`;
        }
    };

    // =========================================================
    // ⚡ Klik Approve / Reject (tanpa kirim ke DB)
    // =========================================================
    document.addEventListener("click", (e) => {
        const approveBtn = e.target.closest(".item-approve-btn");
        const rejectBtn = e.target.closest(".item-reject-btn");

        if (approveBtn || rejectBtn) {
            const isApprove = !!approveBtn;
            const btn = isApprove ? approveBtn : rejectBtn;
            const itemId = btn.dataset.itemId;
            const itemRow = btn.closest("tr");
            const container = btn.closest(".detail-content-wrapper");
            const cartId = container.dataset.cartId;
            const newStatus = isApprove ? "approved" : "rejected";

            if (!pendingChanges[cartId]) pendingChanges[cartId] = {};
            pendingChanges[cartId][itemId] = newStatus;

            updateItemUI(itemRow, newStatus, true);

            showSnackbar(
                `Item ${
                    newStatus === "approved" ? "disetujui" : "ditolak"
                } (belum disimpan)`
            );
        }
    });

    // =========================================================
    // 💾 Klik "Simpan Perubahan" → kirim semua pendingChanges
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
                body: JSON.stringify({ changes: pendingChanges[cartId] }),
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
                    updateItemUI(r, data.items[itemId].status, false);
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
    // ❌ Klik "Batal" → reload detail
    // =========================================================
    document.addEventListener("click", async (e) => {
        const cancelBtn = e.target.closest(".cart-detail-cancel-btn");
        if (!cancelBtn) return;

        const container = cancelBtn.closest(".detail-content-wrapper");
        const cartId = container.dataset.cartId;

        delete pendingChanges[cartId];
        container.dataset.loaded = "false";

        const res = await fetch(`/admin/carts/${cartId}`);
        container.innerHTML = await res.text();
        container.dataset.loaded = "true";

        showSnackbar("Perubahan dibatalkan.");
    });
});
