document.addEventListener("DOMContentLoaded", () => {
    // baca data dari window (diset oleh Blade)
    const pegawaiId = window.PegawaiApp?.id ?? null;
    const csrf = window.PegawaiApp?.csrf ?? "";

    const routes = {
        scan:
            window.PegawaiApp?.routes?.scan ??
            `/admin/pegawai/${pegawaiId}/scan`,
        cart:
            window.PegawaiApp?.routes?.cart ??
            `/admin/pegawai/${pegawaiId}/cart`,
        saveCart:
            window.PegawaiApp?.routes?.saveCart ??
            `/admin/pegawai/${pegawaiId}/cart/save`,
        deleteItem: (id) =>
            window.PegawaiApp?.routes?.deleteItemBase
                ? window.PegawaiApp.routes.deleteItemBase.replace("ITEM_ID", id)
                : `/admin/pegawai/${pegawaiId}/cart/item/${id}`,
    };

    console.log("Routes loaded:", routes); // Debug log

    const cartButton = document.getElementById("openCartModal");
    const cartBadge = document.getElementById("cartBadge");
    const cartModalEl = document.getElementById("cartModal");
    const cartModal = cartModalEl ? new bootstrap.Modal(cartModalEl) : null;
    const cartContent = document.getElementById("cartContent");
    const saveBtn = document.getElementById("saveCartButton");

    // helper Swal
    function showToast(icon, title, timer = 2000) {
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
        } else {
            console[icon === "error" ? "error" : "log"](title);
        }
    }

    function showAlert(icon, title, text = "") {
        if (window.Swal) {
            Swal.fire({ icon, title, text });
        } else {
            alert(title + (text ? "\n" + text : ""));
        }
    }

    // =========================================================
    // 🔍 HANDLE SCAN FORM SUBMIT
    // =========================================================
    document.querySelectorAll(".scan-form").forEach((form) => {
        form.addEventListener("submit", async (e) => {
            e.preventDefault();
            const submitBtn = form.querySelector(".submit-btn");
            const modalEl = form.closest(".modal");
            const modal = bootstrap.Modal.getInstance(modalEl);

            if (submitBtn) submitBtn.disabled = true;
            submitBtn.innerHTML =
                '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

            const formData = new FormData(form);
            const barcodeVal = (formData.get("barcode") || "")
                .toString()
                .trim();
            formData.set("barcode", barcodeVal);

            try {
                console.log("Sending scan request to:", routes.scan); // Debug log

                const res = await fetch(routes.scan, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": csrf,
                        Accept: "application/json",
                    },
                    body: formData,
                });

                const json = await res.json();
                console.log("Scan response:", json); // Debug log

                if (res.ok && json.success) {
                    // hide modal
                    if (modal) modal.hide();

                    // update UI
                    updateCartBadge();
                    loadCart(); // Refresh cart content

                    // kurangi stok di card (pure UI)
                    const itemId = formData.get("item_id");
                    const quantity = parseInt(formData.get("quantity")) || 1;
                    updateItemStock(itemId, quantity);

                    // tampilkan cart modal
                    if (cartModal) {
                        cartModal.show();
                    }

                    showToast(
                        "success",
                        "Barang berhasil ditambahkan ke keranjang!"
                    );

                    // Reset form
                    form.reset();
                    form.querySelector(".quantity-input").value = 1;
                } else {
                    const errorMsg = json.message || "Barcode tidak cocok!";
                    showAlert("error", "Gagal", errorMsg);
                }
            } catch (err) {
                console.error("Scan error:", err);
                showAlert("error", "Terjadi Kesalahan", "Silakan coba lagi.");
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML =
                        '<i class="ri-check-line me-1"></i> Simpan';
                }
            }
        });
    });

    // =========================================================
    // 📦 UPDATE ITEM STOCK UI
    // =========================================================
    function updateItemStock(itemId, quantity) {
        const card = document.querySelector(`[data-item-id="${itemId}"]`);
        if (card) {
            const stokEl = card.querySelector(".text-success, .text-danger");
            const stockMaxEl = card.querySelector(".stock-max");
            const scanBtn = card.querySelector(".scan-btn");

            if (stokEl && stockMaxEl) {
                let stok = parseInt(stokEl.textContent.trim()) || 0;
                stok = Math.max(0, stok - quantity);

                stokEl.textContent = stok;
                stockMaxEl.textContent = stok;

                // Update classes
                stokEl.classList.toggle("text-danger", stok <= 0);
                stokEl.classList.toggle("text-success", stok > 0);

                // Disable button if stock is 0
                if (scanBtn) {
                    scanBtn.disabled = stok <= 0;
                    if (stok <= 0) {
                        scanBtn.textContent = "Stok Habis";
                        scanBtn.classList.remove("btn-primary");
                        scanBtn.classList.add("btn-secondary");
                    }
                }
            }
        }
    }

    // =========================================================
    // 💾 SAVE CART TO ITEM OUT
    // =========================================================
    if (saveBtn) {
        saveBtn.addEventListener("click", async (e) => {
            e.preventDefault();

            if (!window.Swal) {
                if (
                    !confirm(
                        "Yakin simpan? Semua data di keranjang akan dipindahkan ke Item Out!"
                    )
                )
                    return;
            } else {
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
            }

            saveBtn.disabled = true;
            saveBtn.innerHTML =
                '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

            try {
                const res = await fetch(routes.saveCart, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": csrf,
                        Accept: "application/json",
                    },
                });

                const json = await res.json();

                if (res.ok && json.success) {
                    showToast(
                        "success",
                        json.message || "Data berhasil disimpan ke Item Out!"
                    );

                    if (cartModal) cartModal.hide();
                    updateCartBadge();

                    // Refresh cart content
                    if (cartContent) {
                        cartContent.innerHTML = `
                            <div class="text-center text-success py-4">
                                <i class="ri-checkbox-circle-line display-4"></i>
                                <p class="mt-3 fw-semibold">${
                                    json.message || "Data berhasil disimpan!"
                                }</p>
                            </div>
                        `;
                    }

                    // Refresh page after delay to update stocks
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showAlert(
                        "error",
                        "Gagal",
                        json.message || "Data gagal disimpan."
                    );
                }
            } catch (err) {
                console.error(err);
                showAlert("error", "Terjadi Kesalahan", "Silakan coba lagi.");
            } finally {
                saveBtn.disabled = false;
                saveBtn.innerHTML =
                    '<i class="ri-send-plane-line me-1"></i> Simpan Keranjang';
            }
        });
    }

    // =========================================================
    // 🛒 CART MANAGEMENT
    // =========================================================

    // Tombol buka cart
    if (cartButton) {
        cartButton.addEventListener("click", () => {
            loadCart();
        });
    }

    // Update badge cart
    async function updateCartBadge() {
        try {
            const res = await fetch(routes.cart, {
                headers: { "X-Requested-With": "XMLHttpRequest" },
            });
            const json = await res.json();
            const count = json.data?.items?.length || 0;

            if (cartBadge) {
                cartBadge.textContent = count;
                cartBadge.style.display = count > 0 ? "inline-block" : "none";
            }
        } catch (err) {
            console.error("updateCartBadge error", err);
        }
    }

    // Load cart content
    async function loadCart() {
        try {
            console.log("Loading cart from:", routes.cart); // Debug log

            const res = await fetch(routes.cart, {
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    Accept: "application/json",
                },
            });

            const json = await res.json();
            console.log("Cart response:", json); // Debug log

            if (res.ok && json.success) {
                updateCartUI(json.data.items);

                // Tampilkan informasi limit
                if (json.data.has_reached_limit) {
                    showToast("warning", json.data.limit_message);
                    if (saveBtn) {
                        saveBtn.disabled = true;
                        saveBtn.innerHTML =
                            '<i class="ri-alert-line me-1"></i> Batas Mingguan Tercapai';
                    }
                } else {
                    if (saveBtn) {
                        saveBtn.disabled = false;
                        saveBtn.innerHTML =
                            '<i class="ri-send-plane-line me-1"></i> Simpan Keranjang';
                    }

                    // Tampilkan info jumlah permintaan
                    if (json.data.weekly_request_count > 0) {
                        console.log(
                            `Pegawai telah membuat ${
                                json.data.weekly_request_count
                            } permintaan minggu ini. Sisa: ${
                                5 - json.data.weekly_request_count
                            } permintaan.`
                        );
                    }
                }
            } else {
                console.error("Failed to load cart:", json);
                showToast("error", "Gagal memuat keranjang");
            }
        } catch (err) {
            console.error("Error loading cart:", err);
            showToast("error", "Gagal memuat keranjang");
        }
    }

    // =========================================================
    // 📋 UPDATE CART UI
    // =========================================================
    function updateCartUI(items) {
        const cartTableBody = document.getElementById("cartTableBody");
        if (!cartTableBody) return;

        console.log("Updating cart UI with items:", items); // Debug log

        if (!items || items.length === 0) {
            cartTableBody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center text-muted py-3">
                        <i class="ri-information-line me-1"></i>Keranjang kosong
                    </td>
                </tr>
            `;
            return;
        }

        let html = "";
        items.forEach((item) => {
            html += `
                <tr data-item-id="${item.cart_item_id}">
                    <td class="align-middle">
                        <div class="d-flex align-items-center">
                            ${
                                item.item?.image
                                    ? `<img src="/storage/${item.item.image}" alt="${item.item.name}"
                                    class="rounded me-3" style="width: 40px; height: 40px; object-fit: cover;">`
                                    : ""
                            }
                            <div>
                                <div class="fw-semibold">${
                                    item.item?.name || "Unknown Item"
                                }</div>
                                <small class="text-muted">Status:
                                    <span class="badge ${
                                        item.status === "scanned"
                                            ? "bg-warning"
                                            : "bg-secondary"
                                    }">
                                        ${item.status}
                                    </span>
                                </small>
                            </div>
                        </div>
                    </td>
                    <td class="align-middle">
                        <code>${item.item?.code || "-"}</code>
                    </td>
                    <td class="align-middle text-center">
                        <span class="fw-bold">${item.quantity}</span>
                    </td>
                    <td class="align-middle text-center">
                        <button type="button" class="btn btn-outline-danger btn-sm delete-item"
                                data-id="${item.cart_item_id}">
                            <i class="ri-delete-bin-line me-1"></i> Hapus
                        </button>
                    </td>
                </tr>
            `;
        });

        cartTableBody.innerHTML = html;

        // Update cart badge
        updateCartBadgeCount(items.length);

        // Re-attach delete handlers
        attachDeleteHandlers();
    }

    // =========================================================
    // 🔢 UPDATE CART BADGE COUNT
    // =========================================================
    function updateCartBadgeCount(count) {
        if (cartBadge) {
            cartBadge.textContent = count;
            cartBadge.style.display = count > 0 ? "inline-block" : "none";
        }
    }

    // =========================================================
    // 🛒 UPDATE CART BADGE (dari server)
    // =========================================================
    async function updateCartBadge() {
        try {
            const res = await fetch(routes.cart, {
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "Accept": "application/json"
                },
            });
            const json = await res.json();
            const count = json.success ? (json.data?.items?.length || 0) : 0;

            updateCartBadgeCount(count);
        } catch (err) {
            console.error("updateCartBadge error", err);
        }
    }

    // =========================================================
    // 🗑️ ATTACH DELETE HANDLERS
    // =========================================================
    function attachDeleteHandlers() {
        const deleteButtons = document.querySelectorAll(".delete-item");

        deleteButtons.forEach((btn) => {
            // Hapus event listener lama untuk menghindari duplikasi
            btn.replaceWith(btn.cloneNode(true));
        });

        // Attach event listener ke button yang baru
        document.querySelectorAll(".delete-item").forEach((btn) => {
            btn.addEventListener("click", async () => {
                let confirmed = true;

                if (window.Swal) {
                    const r = await Swal.fire({
                        icon: "warning",
                        title: "Hapus item?",
                        text: "Item ini akan dihapus dari keranjang.",
                        showCancelButton: true,
                        confirmButtonText: "Ya, hapus",
                        cancelButtonText: "Batal",
                        reverseButtons: true,
                    });
                    confirmed = r.isConfirmed;
                } else {
                    confirmed = confirm("Hapus item?");
                }

                if (!confirmed) return;

                try {
                    const itemId = btn.dataset.id;
                    console.log('Deleting item:', itemId); // Debug log

                    const res = await fetch(routes.deleteItem(itemId), {
                        method: "DELETE",
                        headers: {
                            "X-CSRF-TOKEN": csrf,
                            "Accept": "application/json",
                        },
                    });

                    const result = await res.json();
                    console.log('Delete response:', result); // Debug log

                    if (result.success) {
                        // Remove from UI
                        const row = btn.closest("tr");
                        if (row) {
                            row.remove();
                        }

                        // Update badge and reload cart if empty
                        updateCartBadge();
                        loadCart(); // Reload untuk update UI yang konsisten

                        showToast("success", "Item berhasil dihapus dari keranjang!");
                    } else {
                        showAlert("error", "Gagal", result.message || "Tidak dapat menghapus item.");
                    }
                } catch (err) {
                    console.error('Delete error:', err);
                    showAlert("error", "Terjadi Kesalahan", "Silakan coba lagi.");
                }
            });
        });
    }

    // =========================================================
    // 🎯 INITIAL LOAD - Perbaikan
    // =========================================================

    // Initialize cart badge on page load
    updateCartBadge();

    // Load cart when modal opens
    if (cartModalEl) {
        cartModalEl.addEventListener('show.bs.modal', () => {
            loadCart();
        });
    }


    // Auto-focus barcode input when modal opens
    document.querySelectorAll(".modal").forEach((modalEl) => {
        modalEl.addEventListener("shown.bs.modal", function () {
            const barcodeInput = this.querySelector(".barcode-input");
            if (barcodeInput) {
                barcodeInput.focus();
            }
        });
    });

    // Handle barcode input enter key
    document.querySelectorAll(".barcode-input").forEach((input) => {
        input.addEventListener("keypress", function (e) {
            if (e.key === "Enter") {
                e.preventDefault();
                const form = this.closest(".scan-form");
                if (form) {
                    form.dispatchEvent(new Event("submit"));
                }
            }
        });
    });
});
