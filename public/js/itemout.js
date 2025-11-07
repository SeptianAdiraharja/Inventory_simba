document.addEventListener("DOMContentLoaded", () => {
  console.log("📦 ItemOut Scanner Loaded");

  const scannedItems = {};

  // ======================================================
  // 🔹 Saat modal dibuka → reset & disable tombol simpan
  // ======================================================
  document.addEventListener("show.bs.modal", (e) => {
    const modal = e.target;
    const form = modal.querySelector(".scan-form");
    if (!form) return;

    const cartId = form.dataset.cartId;
    const saveBtn = form.querySelector(".save-all-scan-btn");

    if (!scannedItems[cartId]) scannedItems[cartId] = new Set();

    // Reset tombol saat modal dibuka
    saveBtn.disabled = true;
    saveBtn.classList.add("disabled");

    console.log(`🟢 Modal untuk Cart #${cartId} dibuka`);
  });

  // ======================================================
  // 🔹 Saat submit form scan
  // ======================================================
  document.addEventListener("submit", async (e) => {
    const form = e.target.closest(".scan-form");
    if (!form) return;
    e.preventDefault();

    const cartId = form.dataset.cartId;
    const barcodeInput = form.querySelector(".barcode-input");
    const barcode = barcodeInput.value.trim();
    const resultBox = form.querySelector(".scan-result");
    const saveBtn = form.querySelector(".save-all-scan-btn");

    if (!barcode) return;

    try {
      const url = `/admin/itemout/scan/${cartId}`;
      const res = await fetch(url, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ barcode }),
      });

      const data = await res.json();
      console.log("🔹 Scan result:", data);

      if (!data.success) {
        Swal.fire("Gagal", data.message || "Scan gagal.", "error");
        barcodeInput.value = "";
        barcodeInput.focus();
        return;
      }

      scannedItems[cartId].add(barcode);

      const rows = form.querySelectorAll("tbody tr");
      let row = null;
      rows.forEach((tr) => {
        const codeCell = tr.querySelector(".item-code");
        if (codeCell && codeCell.textContent.trim() === barcode) row = tr;
      });

      if (row) {
        const badge = row.querySelector("td:last-child .badge");
        if (badge) {
          badge.classList.remove("bg-secondary");
          badge.classList.add("bg-success");
          badge.textContent = "Sudah dipindai";
        }

        // Efek sukses singkat
        row.classList.add("table-success");
        setTimeout(() => row.classList.remove("table-success"), 1000);

        const allRows = Array.from(rows).filter(r => r.querySelector(".badge"));
        const allScanned = allRows.every(r =>
          r.querySelector(".badge")?.textContent.includes("Sudah dipindai")
        );

        if (allScanned) {
          saveBtn.disabled = false;
          saveBtn.classList.remove("disabled");

          Swal.fire({
            icon: "info",
            title: "Semua Barang Sudah Dipindai",
            text: "Tekan tombol 'Simpan Semua Hasil Scan' untuk menyimpan ke sistem.",
            timer: 2000,
            showConfirmButton: false,
          });
        }
      }

      resultBox.textContent = data.message;
      resultBox.classList.remove("text-danger");
      resultBox.classList.add("text-success");

      barcodeInput.value = "";
      barcodeInput.focus();

    } catch (err) {
      console.error("❌ Error saat scan:", err);
      Swal.fire("Error", "Gagal memproses scan. Coba lagi.", "error");
      barcodeInput.value = "";
      barcodeInput.focus();
    }
  });

  // ======================================================
  // 🔹 Tombol SIMPAN SEMUA ditekan
  // ======================================================
  document.addEventListener("click", async (e) => {
    const btn = e.target.closest(".save-all-scan-btn");
    if (!btn) return;

    const cartId = btn.dataset.cartId;
    const form = document.querySelector(`.scan-form[data-cart-id="${cartId}"]`);
    const rows = form.querySelectorAll("tbody tr");
    const validRows = Array.from(rows).filter(r => r.querySelector(".badge"));

    const allScanned = validRows.every(r => {
      const badge = r.querySelector(".badge");
      return badge && badge.textContent.includes("Sudah dipindai");
    });

    if (!allScanned) {
      Swal.fire("Belum Lengkap!", "Masih ada barang yang belum dipindai.", "warning");
      return;
    }

    // ✅ SweetAlert muncul di atas modal scan
    Swal.fire({
      title: "Simpan Semua Hasil Scan?",
      text: "Pastikan semua barang sudah benar sebelum disimpan.",
      icon: "question",
      showCancelButton: true,
      confirmButtonText: "Ya, Simpan!",
      cancelButtonText: "Batal",
      backdrop: `rgba(0,0,0,0.35)`,
      didOpen: () => {
        // Turunkan modal & backdrop agar SweetAlert bisa diklik
        const activeModal = document.querySelector('.modal.show');
        const backdrop = document.querySelector('.modal-backdrop.show');

        if (activeModal) {
          activeModal.style.zIndex = 1040;
          activeModal.classList.add("modal-blur");
        }
        if (backdrop) backdrop.style.zIndex = 1030;
      },
      willClose: () => {
        // Kembalikan posisi modal
        const activeModal = document.querySelector('.modal.show');
        const backdrop = document.querySelector('.modal-backdrop.show');

        if (activeModal) {
          activeModal.style.zIndex = 1050;
          activeModal.classList.remove("modal-blur");
        }
        if (backdrop) backdrop.style.zIndex = 1040;
      },
    }).then(async (result) => {
      if (!result.isConfirmed) return;

      try {
        const items = [];
        validRows.forEach((r) => {
          const id = r.dataset.itemId;
          const qtyCell = r.querySelector(".item-qty");
          const qty = qtyCell ? parseInt(qtyCell.textContent.trim()) || 1 : 1;
          if (id) items.push({ id: parseInt(id), quantity: qty });
        });

        // 🔄 Tampilkan animasi loading (SweetAlert progress)
        let timerInterval;
        Swal.fire({
          title: "Menyimpan Data...",
          html: "<b>0%</b> selesai",
          timerProgressBar: true,
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
            const b = Swal.getHtmlContainer().querySelector("b");
            let progress = 0;
            timerInterval = setInterval(() => {
              progress = Math.min(progress + 5, 100);
              b.textContent = progress + "%";
            }, 100);
          },
          willClose: () => {
            clearInterval(timerInterval);
          }
        });

        // Kirim request ke backend
        const res = await fetch(`/admin/itemout/release/${cartId}`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
          },
          body: JSON.stringify({ items }),
        });

        const data = await res.json();
        console.log("💾 Release result:", data);

        if (!data.success) {
          Swal.fire("Gagal", data.message || "Gagal menyimpan hasil scan.", "error");
          return;
        }

        delete scannedItems[cartId];

        // ✅ Efek sukses dengan progress animasi
        Swal.fire({
          title: "Berhasil!",
          html: `
            <div style="margin-top:10px;">
              <div style="height:10px; background:#e0e0e0; border-radius:5px;">
                <div id="success-bar" style="width:0%; height:10px; background:#4CAF50; border-radius:5px;"></div>
              </div>
              <p style="margin-top:12px;">Menyimpan ke sistem...</p>
            </div>
          `,
          icon: "success",
          showConfirmButton: false,
          timer: 1600,
          didOpen: () => {
            const bar = document.getElementById("success-bar");
            let width = 0;
            const animate = setInterval(() => {
              width += 10;
              bar.style.width = width + "%";
              if (width >= 100) clearInterval(animate);
            }, 100);
          }
        }).then(() => location.reload());

      } catch (err) {
        console.error("❌ Error saat menyimpan:", err);
        Swal.fire("Error", "Gagal menyimpan hasil scan.", "error");
      }
    });
  });
});
