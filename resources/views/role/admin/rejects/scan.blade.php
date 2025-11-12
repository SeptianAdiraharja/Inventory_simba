@extends('layouts.index')

@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- ===================== --}}
  {{-- 🧭 BREADCRUMB --}}
  {{-- ===================== --}}
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3 smooth-fade">
    <div class="d-flex align-items-center flex-wrap gap-2">
      <div class="d-flex align-items-center justify-content-center rounded-circle"
           style="width:38px;height:38px;background:#FFF3E0;color:#FF9800;">
        <i class="bi bi-upc-scan fs-5"></i>
      </div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}" class="fw-semibold text-decoration-none" style="color:#FF9800;">
              Dashboard
            </a>
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
  <div class="card shadow-lg border-0 rounded-4 mb-4 overflow-hidden smooth-card">
    <div class="card-header text-white py-3 px-4"
         style="background: linear-gradient(90deg, #FF9800, #FFB74D);">
      <h6 class="mb-0 fw-semibold d-flex align-items-center">
        <i class="bi bi-upc-scan me-2 text-white"></i> Form Scan Barang Rusak
      </h6>
    </div>

    <div class="card-body bg-light">
      <form id="scanForm" autocomplete="off">
        @csrf
        <div class="row g-4 align-items-end">
          <div class="col-md-4">
            <label class="form-label fw-semibold text-secondary">Scan Barcode Barang</label>
            <input type="text" id="barcode" name="barcode"
                   class="form-control form-control-lg border-2 shadow-sm rounded-3"
                   style="border-color:#FF9800;"
                   placeholder="🔍 Arahkan scanner ke sini..." autofocus>
          </div>

          <div class="col-md-2">
            <label class="form-label fw-semibold text-secondary">Jumlah Rusak</label>
            <input type="number" id="quantity" name="quantity"
                   class="form-control shadow-sm border-0 rounded-3"
                   min="1" value="1">
          </div>

          <div class="col-md-3">
            <label class="form-label fw-semibold text-secondary">Kondisi</label>
            <select id="condition" name="condition" class="form-select shadow-sm border-0 rounded-3">
              <option value="rusak ringan">Rusak Ringan</option>
              <option value="rusak berat">Rusak Berat</option>
              <option value="tidak bisa digunakan">Tidak Bisa Digunakan</option>
            </select>
          </div>

          <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn text-white btn-lg w-100 shadow-sm rounded-pill smooth-btn"
                    style="background-color:#FF9800;">
              <i class="bi bi-plus-circle me-2"></i> Tambah ke Daftar
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  {{-- ===================== --}}
  {{-- 📋 TABEL BARANG RUSAK --}}
  {{-- ===================== --}}
  <div class="card shadow-lg border-0 rounded-4 overflow-hidden smooth-card">
      <div class="card-header text-white fw-semibold py-3 px-4 d-flex justify-content-between align-items-center"
          style="background: linear-gradient(90deg, #FF9800, #FFB74D);">
          <div><i class="bi bi-list-ul me-2"></i> Daftar Barang Rusak (Belum Disimpan)</div>
          <button id="saveAllBtn" class="btn btn-light btn-sm text-orange fw-semibold px-3 rounded-pill shadow-sm"
                  style="color:#FF9800;" disabled>
              <i class="bi bi-save2 me-1"></i> Simpan Semua
          </button>
      </div>

      <div class="card-body p-0 bg-white">
          {{-- Tempat untuk alert --}}
          <div id="alertPlaceholder"></div>

          <div class="table-responsive">
              <table class="table table-hover mb-0 align-middle" id="rejectTable">
                  <thead class="text-center fw-semibold text-uppercase small"
                        style="background-color:#FFF3E0; color:#5d4037;">
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
  body { background-color: #fffaf4 !important; }

  .smooth-fade { animation: fadeDown .7s ease-in-out; }
  @keyframes fadeDown { from { opacity:0; transform:translateY(-10px);} to {opacity:1; transform:translateY(0);} }

  tr.fade-in { animation: fadeIn .5s ease-in; }
  @keyframes fadeIn { from {opacity:0;transform:translateY(-5px);} to {opacity:1;transform:translateY(0);} }

  .smooth-card { transition: all 0.3s ease; }
  .smooth-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(255, 152, 0, 0.25);
  }

  .smooth-btn { transition: all 0.25s ease-in-out; }
  .smooth-btn:hover {
    transform: scale(1.05);
    background-color: #fb8c00 !important;
    box-shadow: 0 4px 10px rgba(255, 152, 0, 0.3);
  }

  .table-hover tbody tr:hover {
    background-color: #FFF8E1 !important;
    transition: 0.3s ease;
  }

  .form-control:focus, .form-select:focus {
    border-color: #FF9800 !important;
    box-shadow: 0 0 0 0.2rem rgba(255, 152, 0, 0.25) !important;
  }

  .btn[disabled] {
    opacity: 0.5 !important;
    cursor: not-allowed !important;
  }

  .badge, .btn, input, select, table {
    border-radius: 0.5rem !important;
  }

  @media (max-width: 768px) {
    .breadcrumb-extra { display: none; }
    .card-header h6 { font-size: 1rem; }
    .table { font-size: 0.9rem; }
  }
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
                showAlert(result.message, "error");
            } else {
                // Hapus pesan "Belum ada data" jika ada
                if (rejectBody.querySelector("td.text-muted")) {
                    rejectBody.innerHTML = "";
                }

                // Tambah item ke array
                scannedItems.push({
                    barcode: barcode,
                    name: result.item.name,
                    code: result.item.code,
                    quantity: parseInt(quantity),
                    condition: condition,
                    description: ""
                });

                // Buat row baru
                const newRow = `
                    <tr data-barcode="${barcode}" class="fade-in">
                        <td class="text-center">${counter++}</td>
                        <td>${result.item.name}</td>
                        <td class="text-center">${result.item.code}</td>
                        <td class="text-center">${quantity}</td>
                        <td class="text-center">
                            <span class="badge ${getConditionBadge(condition)}">${condition}</span>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm description-input"
                                   placeholder="Isi deskripsi kerusakan..." required
                                   data-barcode="${barcode}">
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-btn">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;

                rejectBody.insertAdjacentHTML("beforeend", newRow);
                barcodeInput.value = "";
                barcodeInput.focus();
                saveAllBtn.disabled = false;

                showAlert("Barang berhasil ditambahkan ke daftar!", "success");
            }
        } catch (err) {
            console.error("Error:", err);
            showAlert("Gagal memeriksa barang. Coba lagi.", "error");
        }
    });

    // Hapus item dari daftar
    rejectBody.addEventListener("click", (e) => {
        if (e.target.closest(".remove-btn")) {
            const row = e.target.closest("tr");
            const barcode = row.dataset.barcode;

            // Hapus dari array
            scannedItems = scannedItems.filter(i => i.barcode !== barcode);

            // Hapus dari DOM
            row.remove();

            // Reset nomor urut
            resetRowNumbers();

            // Jika tidak ada data, tampilkan pesan kosong
            if (scannedItems.length === 0) {
                rejectBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-muted py-4">
                            <i class="bi bi-inbox fs-4 d-block mb-2"></i> Belum ada data.
                        </td>
                    </tr>
                `;
                saveAllBtn.disabled = true;
            }

            showAlert("Barang berhasil dihapus dari daftar.", "info");
        }
    });

    // Update description ketika user mengetik
    rejectBody.addEventListener("input", (e) => {
        if (e.target.classList.contains("description-input")) {
            const barcode = e.target.dataset.barcode;
            const item = scannedItems.find(i => i.barcode === barcode);
            if (item) {
                item.description = e.target.value.trim();
            }
        }
    });

    // Simpan semua ke DB
    saveAllBtn.addEventListener("click", async () => {
        // Validasi deskripsi
        let valid = true;
        const descInputs = document.querySelectorAll(".description-input");

        descInputs.forEach((input) => {
            const value = input.value.trim();
            if (!value) {
                input.classList.add("is-invalid");
                valid = false;
            } else {
                input.classList.remove("is-invalid");
            }
        });

        if (!valid) {
            showAlert("Harap isi semua deskripsi kerusakan sebelum menyimpan!", "warning");
            return;
        }

        // Validasi stok
        let stockValid = true;
        let stockErrorMessages = [];

        for (const item of scannedItems) {
            try {
                const res = await fetch(`/admin/rejects/check/${item.barcode}`);
                const result = await res.json();

                if (result.success && result.item.stock < item.quantity) {
                    stockValid = false;
                    stockErrorMessages.push(
                        `Stok ${result.item.name} tidak mencukupi. Tersedia: ${result.item.stock}, Diminta: ${item.quantity}`
                    );
                }
            } catch (err) {
                console.error("Error checking stock:", err);
            }
        }

        if (!stockValid) {
            showAlert(stockErrorMessages.join('<br>'), "error");
            return;
        }

        // Konfirmasi sebelum menyimpan
        Swal.fire({
            title: 'Simpan Data Barang Rusak?',
            text: `Anda akan menyimpan ${scannedItems.length} barang ke data reject`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#FF9800',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Simpan!',
            cancelButtonText: 'Batal'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    saveAllBtn.disabled = true;
                    saveAllBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Menyimpan...';

                    const response = await fetch('{{ route("admin.rejects.process") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            items: scannedItems
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: result.message,
                            icon: 'success',
                            confirmButtonColor: '#FF9800'
                        });

                        // Reset semua data
                        scannedItems = [];
                        rejectBody.innerHTML = `
                            <tr>
                                <td colspan="7" class="text-muted py-4">
                                    <i class="bi bi-inbox fs-4 d-block mb-2"></i> Belum ada data.
                                </td>
                            </tr>
                        `;
                        saveAllBtn.disabled = true;
                        saveAllBtn.innerHTML = '<i class="bi bi-save2 me-1"></i> Simpan Semua';
                        counter = 1;
                    } else {
                        throw new Error(result.message || 'Gagal menyimpan data');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showAlert(error.message || 'Gagal menyimpan data ke server.', 'error');

                    saveAllBtn.disabled = false;
                    saveAllBtn.innerHTML = '<i class="bi bi-save2 me-1"></i> Simpan Semua';
                }
            }
        });
    });

    // Fungsi reset nomor urut
    function resetRowNumbers() {
        const rows = rejectBody.querySelectorAll('tr');
        rows.forEach((row, index) => {
            const numberCell = row.querySelector('td:first-child');
            if (numberCell) {
                numberCell.textContent = index + 1;
            }
        });
        counter = rows.length + 1;
    }

    // Fungsi untuk badge kondisi
    function getConditionBadge(condition) {
        const badges = {
            'rusak ringan': 'bg-warning text-dark',
            'rusak berat': 'bg-danger',
            'tidak bisa digunakan': 'bg-dark'
        };
        return badges[condition] || 'bg-secondary';
    }

    // Alert helper
    function showAlert(message, type = "info") {
        // Hapus alert sebelumnya
        const existingAlert = document.querySelector('#alertPlaceholder .alert');
        if (existingAlert) {
            existingAlert.remove();
        }

        const alertBox = document.createElement("div");
        alertBox.className = `alert alert-${type} alert-dismissible fade show m-3`;
        alertBox.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.getElementById("alertPlaceholder").prepend(alertBox);

        // Auto remove alert setelah 5 detik
        setTimeout(() => {
            if (alertBox.parentElement) {
                alertBox.remove();
            }
        }, 5000);
    }
});
</script>
@endpush
