@extends('layouts.index')

@section('content')
<div class="container py-4">

  <h4 class="fw-bold text-danger mb-4 d-flex align-items-center">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>Scan Barang Rusak / Reject
  </h4>

  <!-- FORM SCAN CEPAT -->
  <div class="card shadow-sm border-danger mb-4">
    <div class="card-body">
      <form id="scanForm" autocomplete="off">
        @csrf
        <div class="row g-3 align-items-end">
          <div class="col-md-4">
            <label class="form-label fw-semibold">Scan Barcode Barang</label>
            <input type="text" id="barcode" name="barcode" class="form-control form-control-lg border-danger"
              placeholder="Arahkan scanner ke sini..." autofocus>
          </div>

          <div class="col-md-2">
            <label class="form-label fw-semibold">Jumlah Rusak</label>
            <input type="number" id="quantity" name="quantity" class="form-control" min="1" value="1">
          </div>

          <div class="col-md-3">
            <label class="form-label fw-semibold">Kondisi</label>
            <select id="condition" name="condition" class="form-select">
              <option value="rusak ringan">Rusak Ringan</option>
              <option value="rusak berat">Rusak Berat</option>
              <option value="tidak bisa digunakan">Tidak Bisa Digunakan</option>
            </select>
          </div>

          <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-danger w-100">
              <i class="bi bi-plus-circle me-1"></i> Tambah ke Daftar
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- TABEL HASIL SCAN (sementara) -->
  <div class="card shadow-sm">
    <div class="card-header bg-danger text-white fw-semibold d-flex justify-content-between align-items-center">
      <div><i class="bi bi-list-ul me-2"></i>Daftar Barang Rusak (Belum Disimpan)</div>
      <button id="saveAllBtn" class="btn btn-light btn-sm text-danger fw-semibold" disabled>
        <i class="bi bi-save2 me-1"></i> Simpan Semua
      </button>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0 align-middle" id="rejectTable">
          <thead class="table-danger text-center">
            <tr>
              <th width="60">No</th>
              <th>Nama Barang</th>
              <th>Kode</th>
              <th width="80">Jumlah</th>
              <th>Kondisi</th>
              <th>Deskripsi (Harus Diisi)</th>
              <th width="100">Aksi</th>
            </tr>
          </thead>
          <tbody id="rejectTableBody">
            <tr><td colspan="7" class="text-center text-muted py-3">Belum ada data.</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div id="alertPlaceholder" class="mt-3"></div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("scanForm");
  const barcodeInput = document.getElementById("barcode");
  const rejectBody = document.getElementById("rejectTableBody");
  const saveAllBtn = document.getElementById("saveAllBtn");
  let counter = 1;
  let scannedItems = [];

  barcodeInput.focus();

  // Submit scan form (tambahkan ke daftar sementara)
  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const barcode = barcodeInput.value.trim();
    if (!barcode) return;

    const quantity = document.getElementById("quantity").value;
    const condition = document.getElementById("condition").value;

    // Cek apakah sudah ada barang dengan barcode ini di daftar
    if (scannedItems.find(i => i.barcode === barcode)) {
      showAlert("Barang dengan kode ini sudah ada di daftar!", "warning");
      barcodeInput.value = "";
      barcodeInput.focus();
      return;
    }

    try {
      const res = await fetch(`/admin/rejects/check/${barcode}`);
      const result = await res.json();

      if (!result.success) {
        showAlert(result.message, "danger");
      } else {
        if (rejectBody.querySelector("td.text-muted")) rejectBody.innerHTML = "";

        scannedItems.push({
          barcode,
          name: result.item.name,
          code: result.item.code,
          quantity,
          condition,
          description: ""
        });

        const newRow = `
          <tr data-barcode="${barcode}">
            <td class="text-center">${counter++}</td>
            <td>${result.item.name}</td>
            <td class="text-center">${result.item.code}</td>
            <td class="text-center">${quantity}</td>
            <td class="text-center">${condition}</td>
            <td>
              <input type="text" class="form-control form-control-sm description-input" placeholder="Isi deskripsi..." required>
            </td>
            <td class="text-center">
              <button class="btn btn-sm btn-outline-danger remove-btn"><i class="bi bi-trash"></i></button>
            </td>
          </tr>
        `;
        rejectBody.insertAdjacentHTML("beforeend", newRow);
        barcodeInput.value = "";
        barcodeInput.focus();
        saveAllBtn.disabled = false;
      }
    } catch (err) {
      console.error(err);
      showAlert("Gagal memeriksa barang. Coba lagi.", "danger");
    }
  });

  // Hapus item dari daftar
  rejectBody.addEventListener("click", (e) => {
    if (e.target.closest(".remove-btn")) {
      const row = e.target.closest("tr");
      const barcode = row.dataset.barcode;
      scannedItems = scannedItems.filter(i => i.barcode !== barcode);
      row.remove();
      if (scannedItems.length === 0) {
        rejectBody.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-3">Belum ada data.</td></tr>`;
        saveAllBtn.disabled = true;
      }
    }
  });

  // Simpan semua ke DB
  saveAllBtn.addEventListener("click", async () => {
    // ambil deskripsi dari input
    const descInputs = document.querySelectorAll(".description-input");
    let valid = true;

    descInputs.forEach((input, index) => {
      const value = input.value.trim();
      if (!value) {
        input.classList.add("is-invalid");
        valid = false;
      } else {
        input.classList.remove("is-invalid");
        scannedItems[index].description = value;
      }
    });

    if (!valid) {
      showAlert("Harap isi semua deskripsi sebelum menyimpan!", "warning");
      return;
    }

    try {
      const res = await fetch(`{{ route('admin.rejects.process') }}`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json"
        },
        body: JSON.stringify({
          _token: '{{ csrf_token() }}',
          items: scannedItems
        })
      });

      const result = await res.json();
      if (result.success) {
        showAlert(result.message, "success");
        scannedItems = [];
        rejectBody.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-3">Belum ada data.</td></tr>`;
        saveAllBtn.disabled = true;
        counter = 1;
      } else {
        showAlert(result.message, "danger");
      }
    } catch (err) {
      showAlert("Gagal menyimpan data ke server.", "danger");
    }
  });

  // Alert helper
  function showAlert(message, type = "info") {
    const alertBox = document.createElement("div");
    alertBox.className = `alert alert-${type} alert-dismissible fade show mt-2`;
    alertBox.innerHTML = `
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.getElementById("alertPlaceholder").append(alertBox);
  }
});
</script>
@endpush
