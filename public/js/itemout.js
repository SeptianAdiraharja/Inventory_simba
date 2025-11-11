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
  // 🔹 Saat submit form scan - PERBAIKAN DI SINI
  // ======================================================
  document.addEventListener("submit", async (e) => {
    const form = e.target.closest(".scan-form");
    if (!form) return;
    e.preventDefault();

    const cartId = form.dataset.cartId;
    const barcodeInput = form.querySelector(".barcode-input");
    const barcode = barcodeInput.value.trim();
    const saveBtn = form.querySelector(".save-all-scan-btn");

    if (!barcode) {
      Swal.fire("Peringatan", "Masukkan kode barang terlebih dahulu.", "warning");
      return;
    }

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

      // PERBAIKAN: Cek status HTTP response
      if (!res.ok) {
        throw new Error(`HTTP error! status: ${res.status}`);
      }

      const data = await res.json();
      console.log("🔹 Scan result:", data);

      // PERBAIKAN: Cek struktur response yang berbeda
      if (data.success === false || data.error) {
        Swal.fire("Gagal", data.message || data.error || "Scan gagal.", "error");
        barcodeInput.value = "";
        barcodeInput.focus();
        return;
      }

      // PERBAIKAN: Jika berhasil, update UI tanpa menunggu response tertentu
      scannedItems[cartId].add(barcode);

      const rows = form.querySelectorAll("tbody tr");
      let rowFound = false;

      rows.forEach((tr) => {
        const codeCell = tr.querySelector(".item-code");
        if (codeCell && codeCell.textContent.trim() === barcode) {
          rowFound = true;
          const badge = tr.querySelector("td:last-child .badge");
          if (badge) {
            badge.classList.remove("bg-secondary");
            badge.classList.add("bg-success");
            badge.textContent = "Sudah dipindai";
          }

          // Efek sukses singkat
          tr.classList.add("table-success");
          setTimeout(() => tr.classList.remove("table-success"), 1000);
        }
      });

      // PERBAIKAN: Cek apakah semua sudah dipindai
      const allRows = Array.from(rows).filter(r => r.querySelector(".badge"));
      const allScanned = allRows.every(r =>
        r.querySelector(".badge")?.textContent.includes("Sudah dipindai")
      );

      if (allScanned) {
        saveBtn.disabled = false;
        saveBtn.classList.remove("disabled");

        Swal.fire({
          icon: "success",
          title: "Semua Barang Sudah Dipindai",
          text: "Tekan tombol 'Simpan Semua Hasil Scan' untuk menyimpan ke sistem.",
          timer: 3000,
          showConfirmButton: false,
        });
      } else if (rowFound) {
        // PERBAIKAN: Tampilkan pesan sukses hanya jika barang ditemukan
        Swal.fire({
          icon: "success",
          title: "Berhasil!",
          text: `Barang dengan kode ${barcode} berhasil dipindai.`,
          timer: 1500,
          showConfirmButton: false,
        });
      } else {
        // PERBAIKAN: Jika barang tidak ditemukan dalam daftar
        Swal.fire("Peringatan", "Kode barang tidak ditemukan dalam daftar permintaan.", "warning");
      }

      barcodeInput.value = "";
      barcodeInput.focus();

    } catch (err) {
      console.error("❌ Error saat scan:", err);

      // PERBAIKAN: Tampilkan pesan error yang lebih spesifik
      let errorMessage = "Gagal memproses scan. Coba lagi.";

      if (err.message.includes("HTTP error")) {
        errorMessage = "Terjadi masalah koneksi dengan server. Periksa jaringan Anda.";
      } else if (err.message.includes("JSON")) {
        errorMessage = "Response dari server tidak valid.";
      }

      Swal.fire("Error", errorMessage, "error");
      barcodeInput.value = "";
      barcodeInput.focus();
    }
  });

  // ======================================================
  // 🔹 Tombol SIMPAN SEMUA ditekan - PERBAIKAN
  // ======================================================
  document.addEventListener("click", async (e) => {
    const btn = e.target.closest(".save-all-scan-btn");
    if (!btn) return;

    // PERBAIKAN: Cek apakah tombol disabled
    if (btn.disabled) {
      Swal.fire("Peringatan", "Masih ada barang yang belum dipindai.", "warning");
      return;
    }

    const cartId = btn.dataset.cartId;
    const form = document.querySelector(`.scan-form[data-cart-id="${cartId}"]`);

    if (!form) {
      Swal.fire("Error", "Form tidak ditemukan.", "error");
      return;
    }

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

    // ✅ SweetAlert konfirmasi
    Swal.fire({
      title: "Simpan Semua Hasil Scan?",
      text: "Pastikan semua barang sudah benar sebelum disimpan.",
      icon: "question",
      showCancelButton: true,
      confirmButtonText: "Ya, Simpan!",
      cancelButtonText: "Batal",
      backdrop: `rgba(0,0,0,0.35)`,
      didOpen: () => {
        const activeModal = document.querySelector('.modal.show');
        const backdrop = document.querySelector('.modal-backdrop.show');

        if (activeModal) {
          activeModal.style.zIndex = 1040;
          activeModal.classList.add("modal-blur");
        }
        if (backdrop) backdrop.style.zIndex = 1030;
      },
      willClose: () => {
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

        // 🔄 Tampilkan animasi loading
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

        // PERBAIKAN: Cek status response
        if (!res.ok) {
          throw new Error(`HTTP error! status: ${res.status}`);
        }

        const data = await res.json();
        console.log("💾 Release result:", data);

        if (!data.success) {
          Swal.fire("Gagal", data.message || "Gagal menyimpan hasil scan.", "error");
          return;
        }

        delete scannedItems[cartId];

        // ✅ Sukses - DITAMBAHKAN BUTTON OKE
        Swal.fire({
          title: "Berhasil!",
          text: "Semua data berhasil disimpan.",
          icon: "success",
          confirmButtonText: "OKE",
          confirmButtonColor: "#FF9800",
          allowOutsideClick: false,
          allowEscapeKey: false
        }).then(() => {
          // Tutup modal dan refresh halaman
          const modal = bootstrap.Modal.getInstance(document.getElementById(`scanModal${cartId}`));
          if (modal) modal.hide();
          location.reload();
        });

      } catch (err) {
        console.error("❌ Error saat menyimpan:", err);
        Swal.fire("Error", "Gagal menyimpan hasil scan: " + err.message, "error");
      }
    });
  });
});