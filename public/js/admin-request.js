document.addEventListener("DOMContentLoaded", () => {
    const pageContainer = document.querySelector(".container-fluid");
    if (!pageContainer) return;

    // Tooltip activation
    const tooltipTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );
    tooltipTriggerList.map((el) => new bootstrap.Tooltip(el));

    const rejectModalEl = document.getElementById("rejectModal");
    const rejectModal = new bootstrap.Modal(rejectModalEl);
    const rejectItemForm = document.getElementById("rejectItemForm");
    let currentItemRow = null;

    // --- 1. Detail toggle (load via AJAX) ---
    document.querySelectorAll(".detail-toggle-btn").forEach((btn) => {
        btn.addEventListener("click", async function () {
            const cartId = this.dataset.cartId;
            // PERBAIKAN: Menggunakan template literal untuk ID
            const detailRow = document.getElementById(`detail-row-${cartId}`);
            const contentContainer = document.getElementById(
                // PERBAIKAN: Menggunakan template literal untuk ID
                `detail-content-${cartId}`
            );

            if (
                !detailRow.classList.contains("show") ||
                contentContainer.getAttribute("data-loaded") !== "true"
            ) {
                contentContainer.innerHTML = `
                    <p class="text-center text-muted m-0 p-3">
                        <div class="spinner-border spinner-border-sm text-primary me-2"></div>Memuat detail...
                    </p>`;
                try {
                    // PERBAIKAN: Menggunakan template literal untuk URL
                    const res = await fetch(`/admin/carts/${cartId}`);
                    const html = await res.text();
                    contentContainer.innerHTML = html;
                    contentContainer.setAttribute("data-loaded", "true");
                    attachItemActionListeners(cartId);
                } catch (error) {
                    // PERBAIKAN: Menggunakan template literal untuk string HTML
                    contentContainer.innerHTML = `<p class="text-danger text-center m-0 p-3">Gagal memuat detail.</p>`;
                    console.error("Error loading cart detail:", error);
                }
            }
        });
    });

    // --- 2. Pasang listener untuk approve/reject per item ---
    function attachItemActionListeners(cartId) {
        // PERBAIKAN: Menggunakan template literal untuk ID
        const detailContent = document.getElementById(
            `detail-content-${cartId}`
        );

        // Approve per item
        detailContent.querySelectorAll(".item-approve-btn").forEach((btn) => {
            btn.addEventListener("click", function (e) {
                e.preventDefault();
                if (confirm("Anda yakin ingin menyetujui item ini?")) {
                    const itemId = this.dataset.itemId;
                    const itemRow = this.closest("tr");
                    updateItemStatus(itemId, "approve", itemRow, this);
                }
            });
        });

        // Reject per item
        detailContent.querySelectorAll(".item-reject-btn").forEach((btn) => {
            btn.addEventListener("click", function () {
                const itemId = this.dataset.itemId;
                currentItemRow = this.closest("tr");
                // PERBAIKAN: Menggunakan template literal untuk URL
                rejectItemForm.action = `/admin/carts/item/${itemId}/reject`;
                rejectItemForm.querySelector('textarea[name="reason"]').value =
                    "";
                rejectModal.show();
            });
        });
    }

    // --- 3. Submit form reject per item ---
    rejectItemForm.addEventListener("submit", async function (e) {
        e.preventDefault();
        const submitButton = this.querySelector('button[type="submit"]');
        const reasonTextarea = this.querySelector('textarea[name="reason"]');
        submitButton.disabled = true;
        // PERBAIKAN: Menggunakan template literal untuk string HTML
        submitButton.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Menolak...`;

        try {
            const res = await fetch(this.action, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]'
                    ).content,
                },
                body: new FormData(this),
            });

            const data = await res.json();

            if (res.ok && data.success) {
                rejectModal.hide();
                updateItemUi(currentItemRow, "rejected", reasonTextarea.value);

                if (data.cart_status_final) {
                    // Tampilkan modal yang sesuai berdasarkan status cart
                    if (data.cart_status === 'approved_partially') {
                        const modal = new bootstrap.Modal(
                            document.getElementById("cartPartiallyApprovedModal")
                        );
                        modal.show();
                    } else {
                        const modal = new bootstrap.Modal(
                            document.getElementById("cartProcessedModal")
                        );
                        modal.show();
                    }

                    document
                        .getElementById("cartProcessedModal")
                        .addEventListener("hidden.bs.modal", () => {
                            location.reload();
                        });
                }
            } else {
                alert("Gagal menolak item: " + (data.message || ""));
            }
        } catch (error) {
            console.error("Reject error:", error);
            alert("Terjadi kesalahan saat menolak item.");
        } finally {
            submitButton.disabled = false;
            submitButton.innerHTML = "Tolak Item";
        }
    });

    // --- 4. Update status per item ---
    async function updateItemStatus(itemId, action, itemRow, button) {
        // PERBAIKAN: Menggunakan template literal untuk URL
        const url = `/admin/carts/item/${itemId}/${action}`;
        const cartId = itemRow
            .closest(".collapse")
            .id.replace("detail-row-", "");
        const originalHtml = button.innerHTML;
        button.disabled = true;
        // PERBAIKAN: Menggunakan template literal untuk string HTML
        button.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span>`;

        try {
            const res = await fetch(url, {
                method: "PATCH",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]'
                    ).content,
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ status: action }),
            });
            const data = await res.json();

            if (res.ok && data.success) {
                updateItemUi(itemRow, action);

                if (data.cart_status_final) {
                    // Tampilkan modal yang sesuai berdasarkan status cart
                    if (data.cart_status === 'approved_partially') {
                        const modal = new bootstrap.Modal(
                            document.getElementById("cartPartiallyApprovedModal")
                        );
                        modal.show();
                    } else {
                        const modal = new bootstrap.Modal(
                            document.getElementById("cartProcessedModal")
                        );
                        modal.show();
                    }

                    document
                        .getElementById("cartProcessedModal")
                        .addEventListener("hidden.bs.modal", () => {
                            location.reload();
                        });
                }
            } else {
                alert("Gagal memperbarui status item.");
            }
        } catch (error) {
            console.error(error);
            alert("Terjadi kesalahan saat memperbarui status item.");
        } finally {
            button.disabled = false;
            button.innerHTML = originalHtml;
        }
    }

    // --- 5. Update tampilan baris item ---
    function updateItemUi(itemRow, status, reason = null) {
        const statusCell = itemRow.querySelector(".item-status-cell");
        const actionCell = itemRow.querySelector(".item-action-cell");
        const reasonCell = itemRow.querySelector(".item-reason-cell");

        const badgeClass = status === "approved" ? "bg-success" : "bg-danger";
        const statusText = status.charAt(0).toUpperCase() + status.slice(1);
        // PERBAIKAN: Menggunakan template literal untuk string HTML
        statusCell.innerHTML = `<span class="badge ${badgeClass}">${statusText}</span>`;

        if (reasonCell) {
            reasonCell.textContent =
                status === "rejected" ? reason || "Ditolak" : "-";
        }

        // PERBAIKAN: Menggunakan template literal untuk string HTML
        actionCell.innerHTML =
            status === "approved"
                ? `<span class="text-success fw-semibold"><i class="bi bi-check2-circle me-1"></i> Approved</span>`
                : `<span class="text-danger fw-semibold"><i class="bi bi-x-octagon me-1"></i> Rejected</span>`;
    }

    // --- 6. Approve / Reject All ---
    document
        .querySelectorAll('form[action*="admin/carts/update"]')
        .forEach((form) => {
            form.addEventListener("submit", async function (e) {
                e.preventDefault();
                const isApproveAll =
                    this.querySelector('input[name="status"]').value ===
                    "approved";
                const confirmed = confirm(
                    isApproveAll
                        ? "Yakin ingin menyetujui SEMUA item?"
                        : "Yakin ingin menolak SEMUA item?"
                );
                if (!confirmed) return;

                const submitButton = this.querySelector(
                    'button[type="submit"]'
                );
                const originalHtml = submitButton.innerHTML;
                submitButton.disabled = true;
                // PERBAIKAN: Menggunakan template literal untuk string HTML
                submitButton.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Memproses...`;

                try {
                    const res = await fetch(this.action, {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector(
                                'meta[name="csrf-token"]'
                            ).content,
                        },
                        body: new FormData(this),
                    });

                    const data = await res.json();

                    if (res.ok && data.success) {
                        if (isApproveAll) {
                            // ✅ Tampilkan modal sukses
                            const modal = new bootstrap.Modal(
                                document.getElementById("cartProcessedModal")
                            );
                            modal.show();

                            // ✅ Reload setelah user tutup modal atau klik tombol "Ke Halaman Scan QR"
                            const modalEl =
                                document.getElementById("cartProcessedModal");
                            modalEl.addEventListener("hidden.bs.modal", () =>
                                location.reload()
                            );
                            modalEl
                                .querySelector('a[href*="itemout"]')
                                .addEventListener("click", () =>
                                    location.reload()
                                );
                        } else {
                            alert("Semua item berhasil ditolak.");
                            location.reload();
                        }
                    } else {
                        alert("Gagal memproses permintaan ini.");
                    }
                } catch (error) {
                    console.error("Approve/Reject All error:", error);
                    alert("Terjadi kesalahan saat memproses.");
                } finally {
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalHtml;
                }
            });
        });
});