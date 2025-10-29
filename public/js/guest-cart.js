document.addEventListener("DOMContentLoaded", () => {
    // === Fokus dan submit otomatis barcode ===
    document.querySelectorAll("input[id^='barcode-']").forEach((input) => {
        const modalId = input.id.replace("barcode-", "");
        $(`#scanModal-${modalId}`).on("shown.bs.modal", () => input.focus());
        input.addEventListener("keypress", (e) => {
            if (e.key === "Enter") {
                e.preventDefault();
                document
                    .getElementById(`form-${modalId}`)
                    .dispatchEvent(new Event("submit"));
            }
        });
    });

    // === Submit form scan barang ===
    document.querySelectorAll("form[id^='form-']").forEach((form) => {
        form.addEventListener("submit", async (e) => {
            e.preventDefault();
            const url = form.action;
            const btn = form.querySelector("button[type='submit']");
            const modal = bootstrap.Modal.getInstance(form.closest(".modal"));
            const formData = new FormData(form);

            btn.disabled = true;
            btn.innerHTML =
                "<i class='ri-loader-4-line spin me-1'></i> Menyimpan...";

            try {
                const res = await fetch(url, {
                    method: "POST",
                    headers: { "X-Requested-With": "XMLHttpRequest" },
                    body: formData,
                });
                const data = await res.json();

                if (data.status === "success") {
                    Swal.fire({
                        icon: "success",
                        title: "Berhasil!",
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false,
                    });
                    modal.hide();
                    setTimeout(() => window.location.reload(), 1600);
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Gagal!",
                        text: data.message || "Terjadi kesalahan.",
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

    if (openCartBtn) {
        openCartBtn.addEventListener("click", () => {
            const guestId = openCartBtn.dataset.guestId;
            if (!guestId) return;

            fetch(`/admin/produk/guest/${guestId}/cart`)
                .then((res) => res.json())
                .then((data) => {
                    cartTableBody.innerHTML = "";
                    if (data.cartItems.length > 0) {
                        data.cartItems.forEach((item) => {
                            cartTableBody.innerHTML += `
                              <tr data-id="${item.id}">
                                <td>${item.name}</td>
                                <td>${item.code ?? "-"}</td>
                                <td class="text-center">
                                  <input
                                    type="number"
                                    min="1"
                                    class="form-control form-control-sm text-center update-qty"
                                    value="${item.quantity}"
                                    style="width: 80px; margin: 0 auto;"
                                  >
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
                            <tr><td colspan="4" class="text-center text-muted py-3">
                              <i class='ri-information-line me-1'></i>Keranjang kosong
                            </td></tr>`;
                    }

                    releaseForm.action = `/admin/produk/guest/${guestId}/release`;
                    cartModal.show();
                });
        });
    }

    // === Update Quantity di Cart ===
    cartTableBody.addEventListener("change", async (e) => {
        if (!e.target.classList.contains("update-qty")) return;

        const tr = e.target.closest("tr");
        const itemId = tr.dataset.id;
        const newQty = e.target.value;
        const guestId = openCartBtn.dataset.guestId;

        try {
            const res = await fetch(
                `/admin/produk/guest/${guestId}/cart/update`,
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]'
                        ).content,
                    },
                    body: JSON.stringify({ item_id: itemId, quantity: newQty }),
                }
            );

            const data = await res.json();
            if (data.status === "success") {
                Swal.fire({
                    icon: "success",
                    title: "Jumlah diperbarui",
                    timer: 1000,
                    showConfirmButton: false,
                });
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Gagal!",
                    text: data.message || "Gagal memperbarui jumlah",
                });
            }
        } catch (err) {
            Swal.fire({
                icon: "error",
                title: "Kesalahan Koneksi",
                text: err.message,
            });
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
                if (!cartTableBody.querySelector("tr")) {
                    cartTableBody.innerHTML = `
                        <tr>
                          <td colspan="4" class="text-center text-muted py-3">
                            <i class='ri-information-line me-1'></i>Keranjang kosong
                          </td>
                        </tr>`;
                }
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Gagal!",
                    text: data.message || "Gagal menghapus item",
                });
            }
        } catch (err) {
            Swal.fire({
                icon: "error",
                title: "Kesalahan!",
                text: err.message,
            });
        }
    });

    // === Release semua barang ===
    releaseForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        const url = releaseForm.action;
        const csrf = releaseForm.querySelector('input[name="_token"]').value;

        const confirm = await Swal.fire({
            title: "Keluarkan Semua Barang?",
            text: "Pastikan data sudah benar.",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Ya, keluarkan!",
            cancelButtonText: "Batal",
            reverseButtons: true,
        });

        if (!confirm.isConfirmed) return;

        try {
            const res = await fetch(url, {
                method: "POST",
                headers: { "X-CSRF-TOKEN": csrf },
            });
            if (!res.ok) throw new Error("Gagal memproses permintaan");
            Swal.fire({
                icon: "success",
                title: "Berhasil!",
                text: "Semua barang berhasil dikeluarkan.",
                timer: 1500,
                showConfirmButton: false,
            });
            setTimeout(() => window.location.reload(), 1600);
        } catch (err) {
            Swal.fire({
                icon: "error",
                title: "Gagal!",
                text: "Terjadi kesalahan: " + err.message,
            });
        }
    });
});
