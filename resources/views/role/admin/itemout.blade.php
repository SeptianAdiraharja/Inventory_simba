@extends('layouts.index')
@section('content')

@if(request('q'))
  <div class="alert alert-warning border-0 rounded-3 shadow-sm mb-3">
    <i class="bi bi-search me-2"></i> Hasil pencarian untuk: <strong>{{ request('q') }}</strong>
  </div>
@endif

<div class="container-fluid py-4 animate__animated animate__fadeIn">
    <!-- ======================== -->
    <!-- HEADER & BREADCRUMB -->
    <!-- ======================== -->
    <div class="d-flex flex-column gap-3 mb-4">
        {{-- 🧾 HEADER --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <h4 class="fw-bold" style="color:#FF9800;">
                <i class="bi bi-box-seam me-2"></i> Daftar Permintaan Pegawai
            </h4>
        </div>

        {{-- 🧭 BREADCRUMB --}}
        <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3 smooth-fade">
            <div class="d-flex align-items-center flex-wrap gap-2">
                <div class="breadcrumb-icon d-flex align-items-center justify-content-center rounded-circle"
                     style="width:38px;height:38px;background:#FFF3E0;color:#FF9800;">
                    <i class="bi bi-house-door-fill fs-5"></i>
                </div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 align-items-center">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold" style="color:#FF9800;">
                                Dashboard
                            </a>
                        </li>
                        <li class="breadcrumb-item active fw-semibold text-dark" aria-current="page">
                            Daftar Permintaan Pegawai
                        </li>
                    </ol>
                </nav>
            </div>
        </div>

        {{-- 📋 DAFTAR PEGAWAI --}}
        <div class="section-pegawai">
            <div class="card shadow-sm border-0 rounded-4 animate__animated animate__fadeInUp">
                <div class="card-header text-white d-flex flex-wrap justify-content-between align-items-center gap-2 rounded-top-4"
                    style="background-color:#FF9800;">
                    <h5 class="mb-0 fw-semibold text-white">
                    <i class="bi bi-person-badge me-2 text-white"></i>Permintaan dari Pegawai
                    </h5>
                    <small class="text-white opacity-75">Menampilkan data pegawai dengan status approved</small>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0 bg-white">
                    <thead style="background:#FFF3E0;" class="text-center fw-semibold">
                        <tr class="text-secondary">
                        <th style="width: 50px;">No</th>
                        <th>Nama Pengguna</th>
                        <th>Status Pemindaian</th>
                        <th style="width: 160px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($approvedItems as $i => $cart)
                        <tr>
                            <td class="text-center text-muted fw-semibold">{{ $approvedItems->firstItem() + $i }}</td>
                            <td>
                            <strong class="text-dark">{{ $cart->user->name ?? 'Guest' }}</strong><br>
                            <small class="text-muted d-block mb-1">
                                <i class="bi bi-calendar-event me-1"></i>{{ $cart->created_at->format('d M Y H:i') }}
                            </small>
                            <span class="badge fw-semibold px-3 py-2 rounded-pill"
                                    style="background:#FFECB3;color:#FF6F00;">
                                <i class="bi bi-box-seam me-1"></i>{{ $cart->cartItems->count() }} Barang Belum Dipindai
                            </span>
                            </td>
                            <td class="text-center">
                            @if($cart->all_scanned)
                                <span class="badge bg-success px-3 py-2 fs-6">✅ Sudah dipindai semua</span>
                            @else
                                <span class="badge bg-secondary px-3 py-2 fs-6">⏳ Belum dipindai semua</span>
                            @endif
                            </td>
                            <td class="text-center">
                            <button class="btn btn-sm rounded-pill px-3 fw-semibold text-white shadow-sm"
                                    style="background-color:#FF9800;"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#collapse{{ $cart->id }}">
                                <i class="bi bi-eye me-1"></i> Detail
                            </button>
                            </td>
                        </tr>

                        {{-- DETAIL ITEM --}}
                        <tr class="collapse bg-light" id="collapse{{ $cart->id }}">
                            <td colspan="4">
                            <div class="p-3">
                                <button class="btn rounded-pill text-white mb-3 px-3 py-2 shadow-sm"
                                        style="background-color:#FF9800;"
                                        data-bs-toggle="modal"
                                        data-bs-target="#scanModal{{ $cart->id }}">
                                <i class="bi bi-qr-code-scan me-1"></i> Pindai Barang
                                </button>

                                <table class="table table-sm table-bordered mb-0">
                                <thead style="background:#FFF8E1;" class="text-center">
                                    <tr>
                                    <th>No</th>
                                    <th>Nama Barang</th>
                                    <th>Kode</th>
                                    <th>Jumlah</th>
                                    <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cart->cartItems as $j => $item)
                                    <tr>
                                        <td class="text-center">{{ $j+1 }}</td>
                                        <td>{{ $item->item->name }}</td>
                                        <td class="text-warning fw-semibold">{{ $item->item->code }}</td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td class="text-center">
                                        @if($item->scanned_at)
                                            <span class="badge bg-success px-3 py-2">Sudah dipindai</span>
                                        @else
                                            <span class="badge bg-secondary px-3 py-2">Belum dipindai</span>
                                        @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                </table>
                            </div>

                            {{-- ✅ MODAL SCAN BARANG --}}
                            <div class="modal fade" id="scanModal{{ $cart->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 shadow-lg rounded-4">
                                    <div class="modal-header text-white rounded-top-4" style="background-color:#FF9800;">
                                    <h5 class="modal-title">
                                        <i class="bi bi-qr-code-scan me-2"></i>Pindai Barang - {{ $cart->user->name ?? 'Guest' }}
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body p-3">
                                    <form class="scan-form" data-cart-id="{{ $cart->id }}">
                                        <div class="row mb-3 align-items-center">
                                        <div class="col-md-8 mb-2 mb-md-0">
                                            <input type="text" class="form-control barcode-input rounded-pill px-3 py-2 border-2"
                                                placeholder="🔍 Scan atau ketik kode barang..." style="border-color:#FF9800;">
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <button type="submit" class="btn text-white rounded-pill px-4 py-2"
                                                    style="background-color:#FF9800;">
                                            Simpan Hasil Scan
                                            </button>
                                        </div>
                                        </div>
                                    </form>

                                    <div class="table-responsive border rounded">
                                        <table class="table table-sm table-hover mb-0">
                                        <thead style="background:#FFF8E1;" class="text-center">
                                            <tr>
                                            <th>No</th>
                                            <th>Nama Barang</th>
                                            <th>Kode</th>
                                            <th>Jumlah</th>
                                            <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($cart->cartItems as $j => $item)
                                            <tr data-item-id="{{ $item->item->id }}">
                                                <td class="text-center">{{ $j+1 }}</td>
                                                <td>{{ $item->item->name }}</td>
                                                <td class="item-code text-warning fw-semibold">{{ $item->item->code }}</td>
                                                <td class="text-center item-qty">{{ $item->quantity }}</td>
                                                <td class="text-center">
                                                @if($item->scanned_at)
                                                    <span class="badge bg-success px-3 py-2">Sudah dipindai</span>
                                                @else
                                                    <span class="badge bg-secondary px-3 py-2">Belum dipindai</span>
                                                @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        </table>
                                    </div>
                                    </div>

                                    <div class="modal-footer border-0 bg-light rounded-bottom-4">
                                    <button type="button" class="btn btn-light border rounded-pill px-4"
                                            data-bs-dismiss="modal" style="border-color:#FF9800;color:#FF9800;">
                                        <i class="bi bi-x-circle me-1"></i> Tutup
                                    </button>
                                    <button type="button"
                                            class="btn text-white rounded-pill px-4 save-all-scan-btn disabled"
                                            style="background-color:#FF9800;"
                                            data-cart-id="{{ $cart->id }}"
                                            disabled>
                                        Simpan Semua
                                    </button>
                                    </div>
                                </div>
                                </div>
                            </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">
                            <i class="bi bi-info-circle me-1"></i> Tidak ada data permintaan pegawai.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
body { background-color: #fffaf4; }
.table-hover tbody tr:hover { background-color: #fff3e0 !important; transition: 0.25s ease; }
.btn:hover { transform: scale(1.03); transition: 0.2s ease-in-out; }
.modal-content:hover { box-shadow: 0 0 15px rgba(255,152,0,0.25); }

/* ✅ SweetAlert fix agar bisa diklik di atas modal */
.swal2-container {
  z-index: 30000 !important;
  pointer-events: auto !important;
}

.modal-backdrop.show {
  z-index: 1040 !important;
  opacity: 0.5 !important;
}

/* ✨ Blur efek modal belakang */
.modal-blur {
  filter: blur(2px);
  transition: all 0.2s ease;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
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
    const saveBtn = modal.querySelector(".save-all-scan-btn");

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

    const modal = form.closest('.modal');
    const cartId = form.dataset.cartId;
    const barcodeInput = form.querySelector(".barcode-input");
    const barcode = barcodeInput.value.trim();
    const saveBtn = modal.querySelector(".save-all-scan-btn");

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
      const rows = modal.querySelectorAll("tbody tr");
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

    const modal = btn.closest('.modal');
    const cartId = btn.dataset.cartId;
    const form = modal.querySelector(`.scan-form[data-cart-id="${cartId}"]`);
    const rows = modal.querySelectorAll("tbody tr");
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
</script>
@endpush
