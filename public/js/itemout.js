// =========================================================
// 📦 ITEM OUT (SCAN & RELEASE) HANDLER — FIXED VERSION
// =========================================================

document.addEventListener("DOMContentLoaded", function () {
    console.log("📦 ItemOut Scanner Loaded (Fixed)");

    const forms = document.querySelectorAll(".scan-form");
    const scannedItems = {};

    forms.forEach((form) => {
        const cartId = form.dataset.cartId;
        const input = form.querySelector(".barcode-input");
        const resultBox = form.querySelector(".scan-result");
        const saveScanBtn = form.querySelector(".save-scan-btn");
        const saveAllBtn = form.querySelector(".save-all-scan-btn");
        scannedItems[cartId] = [];

        // =========================================================
        // 🧩 Cegah form reload saat tekan Enter
        // =========================================================
        form.addEventListener("submit", (e) => e.preventDefault());

        // =========================================================
        // 🔹 SCAN BARCODE / QR — trigger Enter
        // =========================================================
        input.addEventListener("keydown", async function (e) {
            if (e.key !== "Enter") return;
            e.preventDefault();

            const barcode = input.value.trim();
            if (!barcode) return;

            try {
                const response = await fetch(`/admin/itemout/scan/${cartId}`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]'
                        ).content,
                    },
                    body: JSON.stringify({ barcode }),
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    resultBox.innerHTML = `<span class="text-danger">❌ ${
                        data.message || "Kode barang tidak sesuai."
                    }</span>`;
                    input.value = "";
                    return;
                }

                // 🔹 Update tampilan tabel
                const rows = form.querySelectorAll("tbody tr");
                let matchedRow = null;
                rows.forEach((row) => {
                    const code = row
                        .querySelector(".item-code")
                        ?.textContent.trim();
                    const statusCell = row.querySelector("td:last-child");
                    if (code === data.item.code) {
                        statusCell.innerHTML = `<span class="badge bg-success">Sudah dipindai</span>`;
                        matchedRow = row;
                    }
                });

                // 🔹 Simpan hasil ke memori
                scannedItems[cartId].push({
                    id: data.item.id,
                    code: data.item.code,
                    quantity: data.item.quantity ?? 1,
                });

                resultBox.innerHTML = `<span class="text-success">✅ ${data.message}</span>`;
            } catch (err) {
                console.error(err);
                resultBox.innerHTML = `<span class="text-danger">⚠️ Gagal koneksi ke server.</span>`;
            }

            input.value = "";
        });

        // =========================================================
        // 💾 SIMPAN HASIL SCAN SATU PER SATU
        // =========================================================
        // 💾 SIMPAN HASIL SCAN SATU PER SATU (VERSI FIX)
        if (saveScanBtn) {
            saveScanBtn.addEventListener("click", async function () {
                const barcode = input.value.trim();
                if (!barcode) {
                    showToast(
                        "Masukkan kode barang sebelum menyimpan.",
                        "error"
                    );
                    return;
                }

                // Cek apakah sudah ada di scannedItems agar tidak dobel
                const alreadyScanned = scannedItems[cartId].some(
                    (item) => item.code === barcode
                );
                if (alreadyScanned) {
                    showToast("Barang ini sudah dipindai sebelumnya.", "error");
                    input.value = "";
                    return;
                }

                try {
                    const response = await fetch(
                        `/admin/itemout/scan/${cartId}`,
                        {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": document.querySelector(
                                    'meta[name="csrf-token"]'
                                ).content,
                            },
                            body: JSON.stringify({ barcode }),
                        }
                    );

                    const data = await response.json();
                    if (!data.success) {
                        showToast(
                            data.message || "Kode barang tidak sesuai.",
                            "error"
                        );
                        input.value = "";
                        return;
                    }

                    // Update tampilan
                    const rows = form.querySelectorAll("tbody tr");
                    rows.forEach((row) => {
                        const code = row
                            .querySelector(".item-code")
                            ?.textContent.trim();
                        const statusCell = row.querySelector("td:last-child");
                        if (code === data.item.code) {
                            statusCell.innerHTML = `<span class="badge bg-success">Sudah dipindai</span>`;
                        }
                    });

                    // Tambahkan ke memori sementara
                    scannedItems[cartId].push({
                        id: data.item.id,
                        code: data.item.code,
                        quantity: data.item.quantity ?? 1,
                    });

                    showToast("✅ Barang berhasil dipindai!", "success");
                    input.value = "";
                } catch (err) {
                    console.error(err);
                    showToast("⚠️ Gagal menghubungi server.", "error");
                }
            });
        }

        // =========================================================
        // 💾 SIMPAN SEMUA HASIL SCAN (FINAL)
        // =========================================================
        if (saveAllBtn) {
            saveAllBtn.addEventListener("click", async function () {
                if (
                    !scannedItems[cartId] ||
                    scannedItems[cartId].length === 0
                ) {
                    showToast(
                        "Belum ada hasil scan yang siap disimpan.",
                        "error"
                    );
                    return;
                }

                if (!confirm("Yakin ingin menyimpan semua hasil pemindaian?"))
                    return;

                try {
                    const response = await fetch(
                        `/admin/itemout/release/${cartId}`,
                        {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": document.querySelector(
                                    'meta[name="csrf-token"]'
                                ).content,
                            },
                            body: JSON.stringify({
                                items: scannedItems[cartId],
                            }),
                        }
                    );

                    const data = await response.json();

                    if (data.success) {
                        showToast(
                            "✅ Semua hasil scan berhasil disimpan!",
                            "success"
                        );
                        resultBox.innerHTML = `<span class="text-success">Semua hasil scan telah disimpan.</span>`;
                        scannedItems[cartId] = [];
                    } else {
                        showToast(
                            data.message || "Gagal menyimpan hasil scan.",
                            "error"
                        );
                    }
                } catch (error) {
                    console.error(error);
                    showToast(
                        "⚠️ Terjadi kesalahan saat menyimpan data.",
                        "error"
                    );
                }
            });
        }
    });

    // =========================================================
    // 🎨 TOAST NOTIFICATION
    // =========================================================
    function showToast(message, type = "info") {
        const container =
            document.getElementById("toast-container") ||
            createToastContainer();
        const toast = document.createElement("div");
        toast.className = `toast align-items-center text-bg-${
            type === "success"
                ? "success"
                : type === "error"
                ? "danger"
                : "primary"
        } border-0 show mb-2`;
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        container.appendChild(toast);
        setTimeout(() => toast.remove(), 4000);
    }

    function createToastContainer() {
        const container = document.createElement("div");
        container.id = "toast-container";
        container.className = "toast-container position-fixed top-0 end-0 p-3";
        document.body.appendChild(container);
        return container;
    }
});
