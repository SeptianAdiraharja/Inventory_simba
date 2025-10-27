document.addEventListener("DOMContentLoaded", () => {
    console.log("📦 ItemOut Scanner Loaded");

    const scannedItems = {}; // Menyimpan hasil scan sementara per cart (dalam bentuk barcode)

    // ======================================================
    // 🔹 Saat modal dibuka → reset & disable tombol simpan
    // ======================================================
    document.addEventListener("show.bs.modal", (e) => {
        const modal = e.target;
        // Cari form di dalam modal
        const form = modal.querySelector(".scan-form");
        if (!form) return;

        const cartId = form.dataset.cartId;
        const saveBtn = form.querySelector(".save-all-scan-btn");

        // Pastikan Set untuk cartId ini ada
        if (!scannedItems[cartId]) scannedItems[cartId] = new Set();
        // Reset Set saat modal dibuka (opsional, tergantung alur bisnis)
        // Jika *hanya* item yang di-scan di sesi ini yang ingin dikirim, uncomment baris bawah:
        // scannedItems[cartId] = new Set();

        saveBtn.disabled = true;
        saveBtn.classList.add("disabled");

        console.log(`🟢 Modal untuk Cart #${cartId} dibuka`);
    });

    // ======================================================
    // 🔹 Saat submit form scan
    // ======================================================
    document.addEventListener("submit", async (e) => {
        // Gunakan closest agar lebih robust
        const form = e.target.closest(".scan-form");
        if (!form) return;
        e.preventDefault();

        const cartId = form.dataset.cartId;
        const barcodeInput = form.querySelector(".barcode-input");
        // Ambil nilai barcode dan bersihkan
        const barcode = barcodeInput.value.trim();
        const resultBox = form.querySelector(".scan-result");
        const saveBtn = form.querySelector(".save-all-scan-btn");

        if (!barcode) return;

        try {
            // URL endpoint scan
            const url = `/admin/itemout/scan/${cartId}`;
            const res = await fetch(url, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]'
                    ).content,
                },
                body: JSON.stringify({ barcode }),
            });

            const data = await res.json();
            console.log("🔹 Scan result:", data);

            if (!data.success) {
                // Tampilkan pesan error dari backend atau default
                Swal.fire("Gagal", data.message || "Scan gagal.", "error");
                // Reset input dan fokuskan kembali
                barcodeInput.value = "";
                barcodeInput.focus();
                return;
            }

            // Tambahkan barcode yang berhasil di-scan ke Set
            scannedItems[cartId].add(barcode);

            // ✅ Update baris item
            let row = null;
            // Ambil semua baris di tbody yang ada di form/modal
            const rows = form.querySelectorAll("tbody tr");

            // Cari baris yang kode itemnya cocok dengan barcode yang di-scan
            rows.forEach((tr) => {
                // Gunakan tr.dataset.itemCode jika item code disimpan di data attribute,
                // jika tidak, gunakan cell content seperti di bawah:
                const codeCell = tr.querySelector(".item-code");
                if (codeCell && codeCell.textContent.trim() === barcode) {
                    row = tr;
                }
            });

            if (row) {
                // Update badge menjadi "Sudah dipindai"
                const badge = row.querySelector("td:last-child .badge");
                if (badge) {
                    badge.classList.remove("bg-secondary");
                    badge.classList.add("bg-success");
                    badge.textContent = "Sudah dipindai";
                }

                // Efek visual sukses
                row.classList.add("table-success");
                setTimeout(() => row.classList.remove("table-success"), 1200);

                // 🔹 Cek ulang semua baris apakah sudah selesai di-scan
                // Filter hanya baris yang memiliki badge untuk pengecekan
                const allRowsWithBadge = Array.from(rows).filter(r => r.querySelector(".badge"));
                const allScannedNow = allRowsWithBadge.length > 0 && Array.from(allRowsWithBadge).every((r) =>
                    r
                        .querySelector(".badge")
                        ?.textContent.includes("Sudah dipindai")
                );

                console.log("🧩 Cek semua dipindai:", allScannedNow);

                if (allScannedNow) {
                    // Aktifkan tombol simpan
                    saveBtn.disabled = false;
                    saveBtn.classList.remove("disabled");

                    Swal.fire({
                        icon: "info",
                        title: "Semua Barang Sudah Dipindai",
                        text: "Tekan tombol 'Simpan Semua Hasil Scan' untuk menyimpan ke sistem.",
                        timer: 2500,
                        showConfirmButton: false,
                    });
                }
            } else {
                 // Kasus: Scan berhasil di backend, tapi baris item tidak ditemukan di tabel modal
                 console.warn(`Item dengan barcode ${barcode} berhasil di-scan, tetapi baris tidak ditemukan di tabel.`);
            }

            // Tampilkan pesan sukses scan
            resultBox.textContent = data.message;
            resultBox.classList.remove("text-danger");
            resultBox.classList.add("text-success");

            // Reset input dan fokuskan kembali
            barcodeInput.value = "";
            barcodeInput.focus();

        } catch (err) {
            console.error("❌ Error saat scan:", err);
            // Reset input dan fokuskan kembali
            barcodeInput.value = "";
            barcodeInput.focus();
            Swal.fire("Error", "Gagal memproses scan. Coba lagi.", "error");
        }
    });

    // ======================================================
    // 🔹 Tombol SIMPAN SEMUA HASIL SCAN ditekan
    // ======================================================
    document.addEventListener("click", async (e) => {
        // Hanya tangani klik pada tombol .save-all-scan-btn
        const btn = e.target.closest(".save-all-scan-btn");
        if (!btn) return;

        const cartId = btn.dataset.cartId;
        // Cari form/modal yang terkait dengan cartId ini
        const form = document.querySelector(
            `.scan-form[data-cart-id="${cartId}"]`
        );

        // **Perbaikan di sini:**
        // Ambil SEMUA baris dari tbody di dalam form/modal saat ini
        const rows = form ? form.querySelectorAll("tbody tr") : [];

        // Filter hanya baris yang punya badge (yaitu item yang valid)
        const validRows = Array.from(rows).filter((r) =>
            r.querySelector(".badge")
        );

        // Cek apakah semua item yang valid sudah dipindai (badge-nya sukses)
        const allScanned = validRows.length > 0 && validRows.every((r) => {
            const badge = r.querySelector(".badge");
            // Pastikan badge ada dan teksnya sesuai
            return badge && badge.textContent.trim().includes("Sudah dipindai");
        });

        // ✅ Debugging log detail
        console.log("🧩 Total rows ditemukan (di modal):", rows.length);
        console.log("🧩 Rows valid dengan badge:", validRows.length);
        validRows.forEach((r, i) => {
             console.log(i + 1, r.querySelector(".badge")?.textContent.trim());
        });
        console.log("✅ Semua sudah dipindai?", allScanned);


        if (!allScanned) {
            Swal.fire(
                "Belum Lengkap!",
                "Masih ada barang yang belum dipindai. Pastikan semua item memiliki status 'Sudah dipindai'.",
                "warning"
            );
            return;
        }

        Swal.fire({
            title: "Simpan Semua Hasil Scan?",
            text: "Pastikan semua barang sudah benar sebelum disimpan.",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Ya, Simpan!",
            cancelButtonText: "Batal",
        }).then(async (result) => {
            if (!result.isConfirmed) return;

            try {
                const items = [];
                // **Perbaikan di sini:**
                // Iterasi hanya pada validRows (item yang ada di tabel)
                validRows.forEach((r) => {
                    // Asumsi ID item disimpan di data-item-id pada tag <tr>
                    const id = r.dataset.itemId;

                    // Pastikan sel jumlah ditemukan
                    const qtyCell = r.querySelector(".item-qty");
                    // Gunakan data.quantity dari response scan jika ada, atau ambil dari cell.
                    // Jika cell item-qty berisi total kuantitas, ambil dari sana.
                    // Jika tidak, asumsikan 1 jika ID ada.
                    const qty = qtyCell
                        ? parseInt(qtyCell.textContent.trim()) || 1
                        : 1;

                    // Hanya masukkan item yang memiliki ID (jika ID tidak ada, baris itu mungkin header/footer)
                    if (id) {
                       items.push({ id: parseInt(id), quantity: qty });
                    }
                });

                // Cek payload yang akan dikirim (untuk debugging)
                console.log("Payload ke Release Endpoint:", { items });

                // Endpoint untuk me-*release* (mengeluarkan) item
                const res = await fetch(`/admin/itemout/release/${cartId}`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]'
                        ).content,
                    },
                    body: JSON.stringify({ items }),
                });

                const data = await res.json();
                console.log("💾 Release result:", data);

                if (!data.success) {
                    Swal.fire(
                        "Gagal",
                        data.message || "Gagal menyimpan hasil scan.",
                        "error"
                    );
                    return;
                }

                // Clear state setelah sukses
                delete scannedItems[cartId];

                Swal.fire({
                    icon: "success",
                    title: "Berhasil!",
                    text: "Semua hasil scan berhasil disimpan dan barang dikeluarkan.",
                    timer: 2000,
                    showConfirmButton: false,
                }).then(() => location.reload()); // Reload halaman setelah sukses
            } catch (err) {
                console.error("❌ Error saat menyimpan:", err);
                Swal.fire("Error", "Gagal menyimpan hasil scan.", "error");
            }
        });
    });
});