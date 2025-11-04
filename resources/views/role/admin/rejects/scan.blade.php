@extends('layouts.index')

@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- ===================== --}}
  {{-- 🧭 BREADCRUMB --}}
  {{-- ===================== --}}
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3 animate__animated animate__fadeInDown smooth-fade">
    <div class="d-flex align-items-center flex-wrap gap-2">
      <div class="bg-danger bg-opacity-10 text-danger rounded-circle p-2 d-flex align-items-center justify-content-center" style="width:38px;height:38px;">
        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
      </div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}" class="fw-semibold text-danger text-decoration-none">Dashboard</a>
          </li>
          <li class="breadcrumb-item active fw-semibold text-dark" aria-current="page">
            Scan Barang Rusak / Reject
          </li>
        </ol>
      </nav>
    </div>
    <div class="d-flex align-items-center text-muted small">
      <i class="bi bi-calendar-check me-2"></i>
      <span>{{ now()->format('d M Y, H:i') }}</span>
    </div>
  </div>

  {{-- ===================== --}}
  {{-- 🔶 FORM SCAN --}}
  {{-- ===================== --}}
  <div class="card shadow-lg border-0 rounded-4 mb-4 overflow-hidden">
    <div class="card-header text-white py-3 px-4"
         style="background: linear-gradient(90deg, #ff4d4d, #ff7676);">
      <h6 class="mb-0 fw-semibold d-flex align-items-center">
        <i class="bi bi-upc-scan me-2"></i>Form Scan Barang Rusak
      </h6>
    </div>

    <div class="card-body bg-light">
      <form id="scanForm" autocomplete="off">
        @csrf
        <div class="row g-4 align-items-end">
          <div class="col-md-4">
            <label class="form-label fw-semibold text-secondary">Scan Barcode Barang</label>
            <input type="text" id="barcode" name="barcode"
                   class="form-control form-control-lg border-2 border-danger shadow-sm"
                   placeholder="Arahkan scanner ke sini..." autofocus>
          </div>

          <div class="col-md-2">
            <label class="form-label fw-semibold text-secondary">Jumlah Rusak</label>
            <input type="number" id="quantity" name="quantity"
                   class="form-control shadow-sm border-0"
                   min="1" value="1">
          </div>

          <div class="col-md-3">
            <label class="form-label fw-semibold text-secondary">Kondisi</label>
            <select id="condition" name="condition" class="form-select shadow-sm border-0">
              <option value="rusak ringan">Rusak Ringan</option>
              <option value="rusak berat">Rusak Berat</option>
              <option value="tidak bisa digunakan">Tidak Bisa Digunakan</option>
            </select>
          </div>

          <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-danger btn-lg w-100 shadow-sm rounded-3">
              <i class="bi bi-plus-circle me-2"></i>Tambah ke Daftar
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  {{-- ===================== --}}
  {{-- 📋 TABEL BARANG RUSAK --}}
  {{-- ===================== --}}
  <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
    <div class="card-header bg-danger text-white fw-semibold py-3 px-4 d-flex justify-content-between align-items-center">
      <div><i class="bi bi-list-ul me-2"></i>Daftar Barang Rusak (Belum Disimpan)</div>
      <button id="saveAllBtn" class="btn btn-light btn-sm text-danger fw-semibold px-3 rounded-pill shadow-sm" disabled>
        <i class="bi bi-save2 me-1"></i> Simpan Semua
      </button>
    </div>

    <div class="card-body p-0 bg-white">
      <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle" id="rejectTable" style="border-collapse: separate; border-spacing: 0;">
          <thead class="table-danger text-center text-uppercase small">
            <tr>
              <th width="60">No</th>
              <th>Nama Barang</th>
              <th>Kode</th>
              <th width="80">Jumlah</th>
              <th>Kondisi</th>
              <th>Deskripsi (Wajib)</th>
              <th width="100">Aksi</th>
            </tr>
          </thead>
          <tbody id="rejectTableBody" class="text-center">
            <tr>
              <td colspan="7" class="text-muted py-4">
                <i class="bi bi-inbox fs-4 d-block mb-2"></i> Belum ada data.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
.smooth-fade { animation: fadeDown .7s ease-in-out; }
@keyframes fadeDown { from { opacity:0; transform:translateY(-10px);} to {opacity:1; transform:translateY(0);} }
tr.fade-in { animation: fadeIn .5s ease-in; }
@keyframes fadeIn { from {opacity:0;transform:translateY(-5px);} to {opacity:1;transform:translateY(0);} }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("scanForm");
  const barcodeInput = document.getElementById("barcode");
  const rejectBody = document.getElementById("rejectTableBody");
  const saveAllBtn = document.getElementById("saveAllBtn");
  let counter = 1;
  let scannedItems = [];

  barcodeInput.focus();

  // Tambah data hasil scan
  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const barcode = barcodeInput.value.trim();
    if (!barcode) return;

    const quantity = document.getElementById("quantity").value;
    const condition = document.getElementById("condition").value;

    if (scannedItems.find(i => i.barcode === barcode)) {
      Swal.fire("Duplikat!", "Barang dengan kode ini sudah ditambahkan.", "warning");
      barcodeInput.value = "";
      barcodeInput.focus();
      return;
    }

    try {
      const res = await fetch(`/admin/rejects/check/${barcode}`);
      const result = await res.json();

      if (!result.success) {
        Swal.fire("Gagal!", result.message, "error");
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

        const newRow = document.createElement("tr");
        newRow.classList.add("fade-in");
        newRow.dataset.barcode = barcode;
        newRow.innerHTML = `
          <td>${counter++}</td>
          <td class="fw-semibold">${result.item.name}</td>
          <td>${result.item.code}</td>
          <td>${quantity}</td>
          <td class="text-capitalize">${condition}</td>
          <td><input type="text" class="form-control form-control-sm description-input" placeholder="Isi deskripsi..." required></td>
          <td><button class="btn btn-sm btn-outline-danger remove-btn"><i class="bi bi-trash"></i></button></td>
        `;
        rejectBody.appendChild(newRow);
        barcodeInput.value = "";
        barcodeInput.focus();
        saveAllBtn.disabled = false;
      }
    } catch {
      Swal.fire("Error!", "Gagal memeriksa barang. Coba lagi.", "error");
    }
  });

  // Hapus item
  rejectBody.addEventListener("click", (e) => {
    if (e.target.closest(".remove-btn")) {
      const row = e.target.closest("tr");
      const barcode = row.dataset.barcode;
      scannedItems = scannedItems.filter(i => i.barcode !== barcode);
      row.remove();
      if (scannedItems.length === 0) {
        rejectBody.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-4"><i class='bi bi-inbox'></i> Belum ada data.</td></tr>`;
        saveAllBtn.disabled = true;
      }
    }
  });

  // Simpan semua data
  saveAllBtn.addEventListener("click", async () => {
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
      Swal.fire("Perhatian!", "Isi semua deskripsi sebelum menyimpan!", "warning");
      return;
    }

    try {
      const res = await fetch(`{{ route('admin.rejects.process') }}`, {
        method: "POST",
        headers: { "Content-Type": "application/json", "Accept": "application/json" },
        body: JSON.stringify({
          _token: '{{ csrf_token() }}',
          items: scannedItems
        })
      });

      const result = await res.json();
      if (result.success) {
        Swal.fire("Berhasil!", result.message, "success");
        scannedItems = [];
        rejectBody.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-4"><i class='bi bi-inbox'></i> Belum ada data.</td></tr>`;
        saveAllBtn.disabled = true;
        counter = 1;
      } else {
        Swal.fire("Gagal!", result.message, "error");
      }
    } catch {
      Swal.fire("Error!", "Gagal menyimpan data ke server.", "error");
    }
  });
});
</script>
@endpush
