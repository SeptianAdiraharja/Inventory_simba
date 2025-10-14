document.addEventListener("DOMContentLoaded", function () {
  const filterButtons = document.querySelectorAll(".filter-btn");
  const rows = document.querySelectorAll(".cart-item");
  const sectionPegawai = document.querySelector(".section-pegawai");
  const sectionGuest = document.querySelector(".section-guest");

  // =============================
  // 🔹 FILTER DATA
  // =============================
  filterButtons.forEach(btn => {
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      const filter = this.dataset.filter;

      rows.forEach(row => (row.style.display = ""));
      sectionPegawai.style.display = "";
      sectionGuest.style.display = "";

      if (filter === "pegawai") {
        sectionGuest.style.display = "none";
      } else if (filter === "guest") {
        sectionPegawai.style.display = "none";
      } else if (filter === "scanned") {
        rows.forEach(row => {
          if (row.dataset.scanned !== "true") row.style.display = "none";
        });
      } else if (filter === "not-scanned") {
        rows.forEach(row => {
          if (row.dataset.scanned !== "false") row.style.display = "none";
        });
      }
    });
  });

  // =============================
  // 🔹 SCAN BARANG
  // =============================
  document.querySelectorAll(".scan-form").forEach(form => {
    form.addEventListener("submit", async function (e) {
      e.preventDefault();

      const cartId = this.dataset.cartId;
      const barcodeInput = this.querySelector(".barcode-input");
      const resultBox = this.querySelector(".scan-result");
      const barcode = barcodeInput.value.trim();

      if (!barcode) {
        resultBox.innerHTML = `<span class="text-danger">❗ Masukkan kode barang terlebih dahulu.</span>`;
        return;
      }

      resultBox.innerHTML = `<span class="text-info">⏳ Memproses kode <b>${barcode}</b>...</span>`;

      try {
        const response = await fetch(`/admin/itemout/scan/${cartId}`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify({ barcode })
        });

        const data = await response.json();

        if (response.ok && data.success) {
          resultBox.innerHTML = `<span class="text-success">✅ ${data.message}</span>`;
          barcodeInput.value = "";
          barcodeInput.focus();

          const itemRows = document.querySelectorAll(`#collapse${cartId} tbody tr`);
          itemRows.forEach(row => {
            const kodeCell = row.querySelector("td:nth-child(3)");
            if (kodeCell && kodeCell.textContent.trim() === data.item.code) {
              row.querySelector("td:last-child").innerHTML =
                `<span class="badge bg-success">Sudah dipindai</span>`;
            }
          });

          const semuaSudah = Array.from(itemRows).every(row => {
            const statusCell = row.querySelector("td:last-child");
            return statusCell.textContent.includes("Sudah dipindai");
          });

          if (semuaSudah) {
            const targetRow = document.querySelector(`.cart-item[data-type="pegawai"][data-bs-target="#collapse${cartId}"]`);
            if (targetRow) {
              const statusCell = targetRow.querySelector("td:nth-child(3)");
              if (statusCell) {
                statusCell.innerHTML = `<span class="badge bg-success">✅ Sudah dipindai semua</span>`;
              }
              targetRow.dataset.scanned = "true";
            }
            resultBox.innerHTML = `<span class="text-success fw-bold">🎉 Semua barang telah berhasil dipindai! Status diperbarui otomatis.</span>`;
          }
        } else {
          resultBox.innerHTML = `<span class="text-danger">❌ ${data.message || "Gagal menyimpan hasil scan."}</span>`;
        }
      } catch (err) {
        console.error(err);
        resultBox.innerHTML = `<span class="text-danger">⚠️ Terjadi kesalahan koneksi ke server.</span>`;
      }
    });

    const input = form.querySelector(".barcode-input");
    input.addEventListener("keypress", function (e) {
      if (e.key === "Enter") {
        e.preventDefault();
        form.dispatchEvent(new Event("submit"));
      }
    });
  });
});
