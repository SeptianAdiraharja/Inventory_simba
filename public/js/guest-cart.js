document.addEventListener("DOMContentLoaded", () => {
    // === Fokus & submit otomatis barcode ===
    document.querySelectorAll("input[name='barcode']").forEach((input) => {
        const modal = input.closest('.modal');
        $(modal).on("shown.bs.modal", () => input.focus());

        input.addEventListener("keypress", (e) => {
            if (e.key === "Enter") {
                e.preventDefault();
                const form = input.closest('form');
                form.dispatchEvent(new Event("submit"));
            }
        });
    });

    // === Submit form scan barang ===
    document.querySelectorAll("form[action*='scan']").forEach((form) => {
        form.addEventListener("submit", async (e) => {
            e.preventDefault();
            const url = form.action;
            const btn = form.querySelector("button[type='submit']");
            const modal = bootstrap.Modal.getInstance(form.closest(".modal"));
            const formData = new FormData(form);

            btn.disabled = true;
            btn.innerHTML = "<i class='ri-loader-4-line spin me-1'></i> Menyimpan...";

            try {
                const res = await fetch(url, {
                    method: "POST",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData,
                });
                const data = await res.json();

                if (data.status === "success") {
                    // Tutup modal
                    modal.hide();

                    // Tampilkan pesan sukses
                    Swal.fire({
                        icon: "success",
                        title: "Berhasil!",
                        html: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    });

                    // Update badge jumlah item di tombol cart
                    updateCartBadge();

                    // Reset form
                    form.reset();

                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Gagal!",
                        html: data.message,
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: "error",
                    title: "Kesalahan Koneksi",
                    text: err.message,
                });
            } finally {
                btn.disabled = false;
                btn.innerHTML = "<i class='ri-check-line me-1'></i> Simpan";
            }
        });
    });

    // === Buka Modal Cart ===
    const openCartBtn = document.getElementById("openCartModal");
    const cartTableBody = document.getElementById("cartTableBody");
    const releaseForm = document.getElementById("releaseForm");
    const cartModal = new bootstrap.Modal(document.getElementById("cartModal"));
    const releaseProgressBar = document.getElementById("releaseProgressBar");
    const releaseProgressText = document.getElementById("releaseProgressText");
    const limitWarning = document.getElementById("limitWarning");
    const limitWarningText = document.getElementById("limitWarningText");
    const confirmReleaseBtn = document.getElementById("confirmReleaseBtn");

    if (openCartBtn) {
        openCartBtn.addEventListener("click", () => {
            const guestId = openCartBtn.dataset.guestId;
            if (!guestId) return;

            fetch(`/admin/produk/guest/${guestId}/cart`)
                .then((res) => res.json())
                .then((data) => {
                    // Update progress bar
                    const progressPercentage = (data.releaseCountThisWeek / data.maxReleasePerWeek) * 100;
                    releaseProgressBar.style.width = `${progressPercentage}%`;
                    releaseProgressBar.setAttribute('aria-valuenow', progressPercentage);
                    releaseProgressText.textContent = `${data.releaseCountThisWeek}/${data.maxReleasePerWeek} kali`;

                    // Tampilkan warning jika batas tercapai
                    if (data.isLimitReached) {
                        releaseProgressBar.classList.remove('bg-warning');
                        releaseProgressBar.classList.add('bg-danger');
                        limitWarning.style.display = 'block';
                        limitWarningText.textContent = `Guest telah mencapai batas maksimal pengeluaran barang (${data.maxReleasePerWeek} kali) dalam seminggu.`;
                        confirmReleaseBtn.disabled = true;
                        confirmReleaseBtn.innerHTML = '<i class="ri-error-warning-line me-1"></i> Batas Tercapai';
                    } else {
                        releaseProgressBar.classList.remove('bg-danger');
                        releaseProgressBar.classList.add('bg-warning');
                        limitWarning.style.display = 'none';
                        confirmReleaseBtn.disabled = false;
                        confirmReleaseBtn.innerHTML = '<i class="ri-send-plane-line me-1"></i> Keluarkan Semua';
                    }

                    // Update cart items
                    updateCartTable(data.cartItems);

                    releaseForm.action = `/admin/produk/guest/${guestId}/release`;
                    cartModal.show();
                })
                .catch(err => {
                    console.error('Error loading cart:', err);
                    Swal.fire({
                        icon: "error",
                        title: "Gagal memuat keranjang",
                        text: "Terjadi kesalahan saat memuat data keranjang.",
                    });
                });
        });
    }

    // === Fungsi untuk update cart table ===
    function updateCartTable(cartItems) {
        cartTableBody.innerHTML = "";
        if (cartItems.length > 0) {
            cartItems.forEach((item) => {
                cartTableBody.innerHTML += `
                    <tr data-id="${item.id}">
                        <td>${item.name}</td>
                        <td>${item.code ?? "-"}</td>
                        <td class="text-center">
                            <input
                                type="number"
                                min="1"
                                max="${item.stock}"
                                class="form-control form-control-sm text-center update-qty"
                                value="${item.quantity}"
                                style="width: 80px; margin: 0 auto;"
                            >
                            <small class="text-muted">Stok: ${item.stock}</small>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-danger rounded-pill delete-item">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </td>
                    </tr>`;
            });
        } else {
            cartTableBody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center text-muted py-3">
                        <i class='ri-information-line me-1'></i>Keranjang kosong
                    </td>
                </tr>`;
        }
    }

    // === Fungsi untuk update cart badge ===
    function updateCartBadge() {
        const guestId = openCartBtn.dataset.guestId;
        if (!guestId) return;

        fetch(`/admin/produk/guest/${guestId}/cart`)
            .then((res) => res.json())
            .then((data) => {
                const badge = document.querySelector("#openCartModal .badge");
                const itemCount = data.cartItems.length;

                if (itemCount > 0) {
                    if (badge) {
                        badge.textContent = itemCount;
                    } else {
                        const btn = document.getElementById("openCartModal");
                        const badgeEl = document.createElement("span");
                        badgeEl.className = "position-absolute badge rounded-pill bg-danger";
                        badgeEl.style.cssText = "top:-5px; right:-5px; font-size:0.8rem; padding:6px 8px;";
                        badgeEl.textContent = itemCount;
                        btn.appendChild(badgeEl);
                    }
                } else if (badge) {
                    badge.remove();
                }
            });
    }

    // === Konfirmasi saat klik "Keluarkan Semua" ===
    if (confirmReleaseBtn) {
        confirmReleaseBtn.addEventListener("click", (e) => {
            e.preventDefault();

            const guestId = openCartBtn.dataset.guestId;

            // Cek batas pengeluaran sebelum konfirmasi
            fetch(`/admin/produk/guest/${guestId}/cart`)
                .then((res) => res.json())
                .then((data) => {
                    if (data.isLimitReached) {
                        Swal.fire({
                            icon: "error",
                            title: "Batas Tercapai!",
                            html: `Guest telah mencapai batas maksimal pengeluaran barang (${data.maxReleasePerWeek} kali) dalam seminggu.`,
                            confirmButtonColor: "#d33",
                            confirmButtonText: "Mengerti"
                        });
                        return;
                    }

                    // Validasi stok sebelum release
                    const outOfStockItems = data.cartItems.filter(item => item.quantity > item.stock);
                    if (outOfStockItems.length > 0) {
                        const itemNames = outOfStockItems.map(item =>
                            `${item.name} (butuh: ${item.quantity}, stok: ${item.stock})`
                        ).join('<br>');

                        Swal.fire({
                            icon: "error",
                            title: "Stok Tidak Cukup!",
                            html: `Beberapa barang melebihi stok tersedia:<br>${itemNames}`,
                            confirmButtonColor: "#d33",
                            confirmButtonText: "Mengerti"
                        });
                        return;
                    }

                    // Jika belum mencapai batas dan stok cukup, tampilkan konfirmasi
                    Swal.fire({
                        title: "Yakin ingin mengeluarkan semua barang?",
                        html: `Setelah ini stok akan langsung berkurang dan data akan disimpan permanen.<br>
                               <small class="text-warning">Pengeluaran minggu ini: ${data.releaseCountThisWeek}/${data.maxReleasePerWeek} kali</small>`,
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#43A047",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Ya, keluarkan!",
                        cancelButtonText: "Batal"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            releaseForm.submit();
                        }
                    });
                });
        });
    }

    // === Update Quantity di Cart ===
    cartTableBody.addEventListener("change", async (e) => {
        if (!e.target.classList.contains("update-qty")) return;

        const tr = e.target.closest("tr");
        const itemId = tr.dataset.id;
        const newQty = parseInt(e.target.value);
        const guestId = openCartBtn.dataset.guestId;

        // Validasi client-side
        if (newQty < 1) {
            Swal.fire({
                icon: "error",
                title: "Jumlah tidak valid",
                text: "Jumlah harus minimal 1",
            });
            e.target.value = 1;
            return;
        }

        try {
            const res = await fetch(`/admin/produk/guest/${guestId}/cart/update`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ item_id: itemId, quantity: newQty }),
            });

            const data = await res.json();
            if (data.status === "success") {
                Swal.fire({
                    icon: "success",
                    title: "Jumlah diperbarui",
                    timer: 1000,
                    showConfirmButton: false,
                });

                // Refresh cart table untuk update stok info
                updateCartBadge();
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Gagal!",
                    text: data.message || "Gagal memperbarui jumlah",
                });
                // Reset ke nilai sebelumnya
                updateCartBadge();
            }
        } catch (err) {
            Swal.fire({
                icon: "error",
                title: "Kesalahan Koneksi",
                text: err.message,
            });
            updateCartBadge();
        }
    });

    // === Hapus item dari cart ===
    cartTableBody.addEventListener("click", async (e) => {
        if (!e.target.closest(".delete-item")) return;

        const tr = e.target.closest("tr");
        const itemId = tr.dataset.id;
        const guestId = openCartBtn.dataset.guestId;

        const confirm = await Swal.fire({
            title: "Hapus item ini?",
            text: "Item akan dihapus dari keranjang.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya, hapus!",
            cancelButtonText: "Batal",
            reverseButtons: true,
        });

        if (!confirm.isConfirmed) return;

        try {
            const res = await fetch(`/admin/produk/guest/${guestId}/cart/item/${itemId}`, {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                    "X-Requested-With": "XMLHttpRequest",
                },
            });

            const data = await res.json();
            if (data.status === "success") {
                Swal.fire({
                    icon: "success",
                    title: "Berhasil!",
                    text: data.message,
                    timer: 1200,
                    showConfirmButton: false,
                });
                tr.remove();

                // Update badge
                updateCartBadge();

                if (!cartTableBody.querySelector("tr")) {
                    cartTableBody.innerHTML = `
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">
                                <i class='ri-information-line me-1'></i>Keranjang kosong
                            </td>
                        </tr>`;
                }
            }
        } catch (err) {
            Swal.fire({
                icon: "error",
                title: "Kesalahan!",
                text: err.message,
            });
        }
    });

    // === Floating Cart Button (drag & move) ===
    const cartBtn = document.getElementById("openCartModal");
    if (cartBtn) {
        let offsetX, offsetY, isDragging = false;

        cartBtn.addEventListener("mousedown", (e) => {
            isDragging = true;
            offsetX = e.clientX - cartBtn.getBoundingClientRect().left;
            offsetY = e.clientY - cartBtn.getBoundingClientRect().top;
            cartBtn.style.transition = "none";
        });

        document.addEventListener("mousemove", (e) => {
            if (!isDragging) return;
            const x = e.clientX - offsetX;
            const y = e.clientY - offsetY;
            cartBtn.style.left = x + "px";
            cartBtn.style.top = y + "px";
            cartBtn.style.right = "auto";
            cartBtn.style.bottom = "auto";
        });

        document.addEventListener("mouseup", () => {
            isDragging = false;
            cartBtn.style.transition = "all 0.2s ease";
        });
    }
});