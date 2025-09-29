document.addEventListener("DOMContentLoaded", function () {
  const modals = document.querySelectorAll(".open-modal");

  modals.forEach(button => {
    button.addEventListener("click", function () {
      const type = this.dataset.type;
      const modalElement = document.getElementById(`modal-${type}`);
      const modal = new bootstrap.Modal(modalElement);
      const contentContainer = document.getElementById(`modal-content-${type}`);

      // Tampilkan loading dulu
      contentContainer.innerHTML = `<div class="text-center py-4 text-muted">Memuat data...</div>`;

      // Muat data awal via AJAX
      loadModalData(`/admin/dashboard/modal/${type}`, contentContainer, type);

      // Tampilkan modal
      modal.show();
    });
  });

  /**
   * Fungsi untuk memuat data via AJAX dan mengganti isi modal sepenuhnya
   */
  function loadModalData(url, container, type) {
  fetch(url)
    .then(async (res) => {
      const text = await res.text(); // ambil text mentah
      try {
        const data = JSON.parse(text); // coba parse JSON
        container.innerHTML = data.html;
        attachPagination(container, type);
      } catch (e) {
        console.error("Response bukan JSON:", text);
        container.innerHTML = `<div class="text-danger text-center py-4">Gagal memuat data. Periksa console.</div>`;
      }
    })
    .catch(err => {
      console.error(err);
      container.innerHTML = `<div class="text-danger text-center py-4">Gagal memuat data (Fetch error).</div>`;
    });
}

  /**
   * Fungsi untuk pagination AJAX
   */
  function attachPagination(container, type) {
    container.querySelectorAll(".pagination a").forEach(link => {
      link.addEventListener("click", function (e) {
        e.preventDefault();
        const url = this.getAttribute("href");

        // Tampilkan loading selama fetch
        container.innerHTML = `<div class="text-center py-4 text-muted">Memuat data...</div>`;

        // Panggil ulang fungsi loadModalData
        loadModalData(url, container, type);
      });
    });
  }
});
