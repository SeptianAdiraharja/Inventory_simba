/**
 * File: admin-request.js (FINAL FIXED VERSION)
 * Deskripsi:
 *  - Menangani interaksi halaman request admin:
 *    detail item, approve/reject per item, approve/reject all, dan update status cart otomatis.
 */

document.addEventListener("DOMContentLoaded", () => {
    const rejectModalEl = document.getElementById("rejectModal");
    const rejectModal = new bootstrap.Modal(rejectModalEl);
    const rejectItemForm = document.getElementById("rejectItemForm");
    let currentItemRow = null;

    const getCsrf = () =>
        document.querySelector('meta[name="csrf-token"]').content;
    const showModal = (id) =>
        new bootstrap.Modal(document.getElementById(id)).show();

    const setLoading = (button, text = "Memproses...") => {
        button.disabled = true;
        button.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> ${text}`;
    };

    const unsetLoading = (button, originalHtml) => {
        button.disabled = false;
        button.innerHTML = originalHtml;
    };

    /* ==========================
       1️⃣  Load Detail Cart
    ========================== */
    document.querySelectorAll(".detail-toggle-btn").forEach((btn) => {
        btn.addEventListener("click", async () => {
            const cartId = btn.dataset.cartId;
            const detailRow = document.getElementById(`detail-row-${cartId}`);
            const content = document.getElementById(`detail-content-${cartId}`);

            if (
                !detailRow.classList.contains("show") ||
                content.dataset.loaded !== "true"
            ) {
                content.innerHTML = `
                    <p class="text-center text-muted m-0 p-3">
                        <div class="spinner-border spinner-border-sm text-primary me-2"></div>Memuat detail...
                    </p>`;

                try {
                    const res = await fetch(`/admin/carts/${cartId}`);
                    content.innerHTML = await res.text();
                    content.dataset.loaded = "true";
                    attachItemActionListeners(cartId);
                } catch (err) {
                    console.error("Error loading cart detail:", err);
                    content.innerHTML = `<p class="text-danger text-center m-0 p-3">Gagal memuat detail.</p>`;
                }
            }
        });
    });

    /* ==========================
       2️⃣  Listener Item Action
    ========================== */
    function attachItemActionListeners(cartId) {
        const content = document.getElementById(`detail-content-${cartId}`);

        // Approve per item
        content.querySelectorAll(".item-approve-btn").forEach((btn) => {
            btn.addEventListener("click", (e) => {
                e.preventDefault();
                if (confirm("Yakin ingin menyetujui item ini?")) {
                    updateItemStatus(
                        btn.dataset.itemId,
                        "approved",
                        btn.closest("tr"),
                        btn
                    );
                }
            });
        });

        // Reject per item
        content.querySelectorAll(".item-reject-btn").forEach((btn) => {
            btn.addEventListener("click", () => {
                currentItemRow = btn.closest("tr");
                rejectItemForm.action = `/admin/carts/item/${btn.dataset.itemId}/reject`;
                rejectItemForm.querySelector("textarea[name='reason']").value =
                    "";
                rejectModal.show();
            });
        });
    }

    /* ==========================
       3️⃣  Reject Item Modal
    ========================== */
    rejectItemForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        const submitBtn = rejectItemForm.querySelector('button[type="submit"]');
        const reason = rejectItemForm
            .querySelector('textarea[name="reason"]')
            .value.trim();

        if (!reason) {
            alert("Harap isi alasan penolakan!");
            return;
        }

        setLoading(submitBtn, "Menolak...");
        try {
            const formData = new FormData(rejectItemForm);
            formData.append("_method", "PATCH");
            formData.append("reason", reason);

            const res = await fetch(rejectItemForm.action, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": getCsrf(),
                    Accept: "application/json",
                },
                body: formData,
            });

            const data = await res.json().catch(() => ({}));

            if (res.ok && data.success) {
                rejectModal.hide();
                updateItemUi(currentItemRow, "rejected", reason);
                handleCartStatusModal(data);
            } else {
                alert(
                    "Gagal menolak item: " + (data?.message || "Server error")
                );
            }
        } catch (err) {
            console.error("Reject error:", err);
            alert("Terjadi kesalahan saat menolak item.");
        } finally {
            unsetLoading(submitBtn, "Tolak Item");
        }
    });

    /* ==========================
       4️⃣  Update Item Status
    ========================== */
    async function updateItemStatus(itemId, status, itemRow, button) {
        const action = status === "approved" ? "approve" : "reject"; // ✅ sesuaikan ke route Laravel
        const url = `/admin/carts/item/${itemId}/${action}`;
        const originalHtml = button.innerHTML;
        setLoading(button);

        try {
            const res = await fetch(url, {
                method: "PATCH",
                headers: {
                    "X-CSRF-TOKEN": getCsrf(),
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ status }),
            });

            const data = await res.json();
            if (res.ok && data.success) {
                updateItemUi(itemRow, status);
                handleCartStatusModal(data);
            } else {
                alert("Gagal memperbarui status item.");
            }
        } catch (err) {
            console.error(err);
            alert("Terjadi kesalahan saat memperbarui status item.");
        } finally {
            unsetLoading(button, originalHtml);
        }
    }

    /* ==========================
       5️⃣  Update Tampilan Item
    ========================== */
    function updateItemUi(row, status, reason = null) {
        const statusCell = row.querySelector(".item-status-cell");
        const actionCell = row.querySelector(".item-action-cell");
        const reasonCell = row.querySelector(".item-reason-cell");

        const isApproved = status === "approved";
        const badge = isApproved ? "bg-success" : "bg-danger";
        const text = isApproved ? "Approved" : "Rejected";

        statusCell.innerHTML = `<span class="badge ${badge}">${text}</span>`;
        if (reasonCell)
            reasonCell.textContent = !isApproved ? reason || "Ditolak" : "-";
        actionCell.innerHTML = `
            <span class="fw-semibold text-${isApproved ? "success" : "danger"}">
                <i class="bi ${
                    isApproved ? "bi-check2-circle" : "bi-x-octagon"
                } me-1"></i>${text}
            </span>`;
    }

    /* ==========================
       6️⃣  Handle Modal Status Cart
    ========================== */
    function handleCartStatusModal(data) {
        // tampilkan hanya jika semua item sudah diproses
        if (!data.cart_status_final) return;

        const modalId =
            data.cart_status === "approved"
                ? "cartProcessedModal"
                : data.cart_status === "approved_partially"
                ? "cartPartiallyApprovedModal"
                : null;

        if (modalId) {
            showModal(modalId);
            const modalEl = document.getElementById(modalId);
            modalEl.addEventListener("hidden.bs.modal", () =>
                location.reload()
            );
        }
    }

    /* ==========================
       7️⃣  Approve / Reject All
    ========================== */
    document.querySelectorAll(".approve-all-form").forEach((form) => {
        form.addEventListener("submit", async (e) => {
            e.preventDefault();

            const isApproveAll =
                form.querySelector('input[name="status"]').value === "approved";
            const confirmText = isApproveAll
                ? "Yakin ingin menyetujui SEMUA item?"
                : "Yakin ingin menolak SEMUA item?";
            if (!confirm(confirmText)) return;

            const submitBtn = form.querySelector('button[type="submit"]');
            const originalHtml = submitBtn.innerHTML;
            setLoading(submitBtn);

            try {
                const res = await fetch(form.action, {
                    method: "POST",
                    headers: { "X-CSRF-TOKEN": getCsrf() },
                    body: new FormData(form),
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    if (isApproveAll) {
                        showModal("cartProcessedModal");
                    } else {
                        alert("Semua item berhasil ditolak.");
                        location.reload();
                    }
                } else {
                    alert("Gagal memproses permintaan ini.");
                }
            } catch (err) {
                console.error("Approve/Reject All error:", err);
                alert("Terjadi kesalahan saat memproses.");
            } finally {
                unsetLoading(submitBtn, originalHtml);
            }
        });
    });
});
