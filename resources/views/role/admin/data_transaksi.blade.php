@extends('layouts.index')

@section('content')
<div class="container-fluid py-4 animate_animated animate_fadeIn">

  {{-- 🧭 MODERN BREADCRUMB --}}
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3 smooth-fade">
    <div class="d-flex align-items-center flex-wrap gap-2">
      <div class="d-flex align-items-center justify-content-center rounded-circle"
           style="width:38px;height:38px;background:#FFF3E0;color:#FF9800;">
        <i class="bi bi-box-seam fs-5"></i>
      </div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}" class="fw-semibold text-decoration-none" style="color:#FF9800;">Dashboard</a>
          </li>
          <li class="breadcrumb-item active fw-semibold text-dark" aria-current="page">Data Transaksi Barang Keluar</li>
        </ol>
      </nav>
    </div>
    <div class="d-flex align-items-center text-muted small">
      <i class="bi bi-calendar-check me-2"></i>
      <span>{{ now()->format('d M Y, H:i') }}</span>
    </div>
  </div>

  {{-- 📦 CARD UTAMA --}}
  <div class="card shadow-lg border-0 rounded-4 smooth-card">
    <div class="card-header text-white py-3 px-4 d-flex justify-content-between align-items-center"
         style="background: linear-gradient(90deg, #FF9800, #FFB74D);">
      <h4 class="mb-0 fw-bold d-flex align-items-center">
        <i class="bi bi-box-seam me-2 text-white"></i> Data Transaksi Barang Keluar
      </h4>
    </div>

    <div class="card-body p-0">
      {{-- TAB NAVIGATION --}}
      <ul class="nav nav-tabs fs-5 fw-semibold px-3" id="transaksiTab" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active py-2 px-4" id="pegawai-tab" data-bs-toggle="tab"
                  data-bs-target="#pegawai" type="button" role="tab" aria-selected="true"
                  style="color:#FF9800;">👔 Pegawai</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link py-2 px-4" id="guest-tab" data-bs-toggle="tab"
                  data-bs-target="#guest" type="button" role="tab"
                  style="color:#FF9800;">🧍‍♂ Tamu</button>
        </li>
      </ul>

      {{-- TAB CONTENT --}}
      <div class="tab-content p-4" id="transaksiTabContent">

        {{-- 🔸 PEGAWAI --}}
        <div class="tab-pane fade show active" id="pegawai" role="tabpanel">
          <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle text-center">
              <thead style="background-color:#FFF3E0;color:#5d4037;">
                <tr>
                  <th>No</th>
                  <th>Nama Pegawai</th>
                  <th>Jumlah Barang</th>
                  <th>Tanggal Transaksi</th>
                  <th>Status</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                @forelse($finishedCarts as $i => $cart)
                <tr>
                  <td>{{ $i + 1 }}</td>
                  <td class="text-start fw-semibold">{{ $cart->user->name ?? '-' }}</td>
                  <td>{{ $cart->cartItems->count() }}</td>
                  <td>{{ $cart->created_at->format('d M Y H:i') }}</td>
                  <td><span class="badge rounded-pill bg-success px-3 py-2">Selesai</span></td>
                  <td>
                    <button class="btn btn-sm rounded-pill text-white px-3 smooth-btn"
                            style="background-color:#FF9800;"
                            data-bs-toggle="collapse" data-bs-target="#pegawai{{ $cart->id }}">
                      <i class="bi bi-eye"></i> Detail
                    </button>
                  </td>
                </tr>

                {{-- DETAIL --}}
                <tr class="collapse bg-light" id="pegawai{{ $cart->id }}">
                  <td colspan="6">
                    <table class="table table-sm table-bordered mb-0 text-center align-middle">
                      <thead style="background-color:#FFF8E1;">
                        <tr>
                          <th>#</th>
                          <th>Nama Barang</th>
                          <th>Kode</th>
                          <th>Jumlah</th>
                          <th>Status</th>
                          <th>Tanggal Scan</th>
                          <th>Aksi</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($cart->cartItems as $j => $item)
                        <tr>
                          <td>{{ $j + 1 }}</td>
                          <td class="text-start">{{ $item->item->name ?? '-' }}</td>
                          <td>{{ $item->item->code ?? '-' }}</td>
                          <td>{{ $item->quantity }}</td>
                          <td><span class="badge bg-success">Approved</span></td>
                          <td>{{ $item->scanned_at ? \Carbon\Carbon::parse($item->scanned_at)->format('d M Y H:i') : '-' }}</td>
                          <td>
                            <button class="btn btn-sm btn-outline-warning rounded-pill me-1 fw-semibold smooth-btn"
                                    data-bs-toggle="modal" data-bs-target="#refundModal"
                                    data-cart-item-id="{{ $item->id }}" data-item-name="{{ $item->item->name }}"
                                    data-item-id="{{ $item->item->id }}" data-max-qty="{{ $item->quantity }}">
                              🔄 Refund
                            </button>
                            <button class="btn btn-sm btn-outline-info rounded-pill fw-semibold smooth-btn"
                                    data-bs-toggle="modal" data-bs-target="#editModal"
                                    data-cart-item-id="{{ $item->id }}" data-item-id="{{ $item->item->id }}"
                                    data-qty="{{ $item->quantity }}">
                              ✏ Edit
                            </button>
                          </td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-3"><i class="bi bi-info-circle me-1"></i> Tidak ada transaksi pegawai.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
          <div class="d-flex justify-content-end mt-3">
            {{ $finishedCarts->links('pagination::bootstrap-5') }}
          </div>
        </div>

        {{-- 🔸 TAMU --}}
        <div class="tab-pane fade" id="guest" role="tabpanel">
          <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle text-center">
              <thead style="background-color:#FFF3E0;color:#5d4037;">
                <tr>
                  <th>No</th>
                  <th>Nama Tamu</th>
                  <th>Jumlah Barang</th>
                  <th>Tanggal Transaksi</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                @forelse($guestItemOuts as $i => $guest)
                <tr>
                  <td>{{ $i + 1 }}</td>
                  <td class="fw-semibold">{{ $guest->name ?? 'Guest' }}</td>
                  <td>{{ $guest->guestCart->guestCartItems->count() ?? 0 }}</td>
                  <td>{{ $guest->created_at->format('d M Y H:i') }}</td>
                  <td>
                    <button class="btn btn-sm rounded-pill text-white px-3 smooth-btn"
                            style="background-color:#FF9800;"
                            data-bs-toggle="collapse" data-bs-target="#guest{{ $guest->id }}">
                      <i class="bi bi-eye"></i> Detail
                    </button>
                  </td>
                </tr>

                {{-- DETAIL TAMU --}}
                <tr class="collapse bg-light" id="guest{{ $guest->id }}">
                  <td colspan="5">
                    <table class="table table-sm table-bordered mb-0 text-center">
                      <thead style="background-color:#FFF8E1;">
                        <tr>
                          <th>#</th>
                          <th>Nama Barang</th>
                          <th>Kode</th>
                          <th>Jumlah</th>
                          <th>Status</th>
                          <th>Aksi</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($guest->guestCart->guestCartItems as $j => $item)
                        <tr>
                          <td>{{ $j + 1 }}</td>
                          <td class="text-start">{{ $item->item->name ?? '-' }}</td>
                          <td>{{ $item->item->code ?? '-' }}</td>
                          <td>{{ $item->quantity }}</td>
                          <td><span class="badge bg-success">Sudah Dipindai</span></td>
                          <td>
                            <button class="btn btn-sm btn-outline-warning rounded-pill me-1 fw-semibold smooth-btn"
                                    data-bs-toggle="modal" data-bs-target="#refundModalGuest"
                                    data-cart-item-id="{{ $item->id }}" data-item-name="{{ $item->item->name }}"
                                    data-item-id="{{ $item->item->id }}" data-max-qty="{{ $item->quantity }}">
                              🔄 Refund
                            </button>
                            <button class="btn btn-sm btn-outline-info rounded-pill fw-semibold smooth-btn"
                                    data-bs-toggle="modal" data-bs-target="#editModalGuest"
                                    data-guest-cart-item-id="{{ $item->id }}" data-item-id="{{ $item->item->id }}"
                                    data-qty="{{ $item->quantity }}">
                              ✏ Edit
                            </button>
                          </td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-3"><i class="bi bi-info-circle me-1"></i> Tidak ada transaksi tamu.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
          <div class="d-flex justify-content-end mt-3">
            {{ $guestItemOuts->links('pagination::bootstrap-5') }}
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ===================== --}}
{{-- MODAL REFUND PEGAWAI --}}
{{-- ===================== --}}
<div class="modal fade" id="refundModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <form id="refundFormPegawai" action="{{ route('admin.pegawai.refund') }}" method="POST">
        @csrf
        <div class="modal-header text-white" style="background:linear-gradient(90deg, #FF9800, #FFB74D);">
          <h5 class="modal-title fw-bold"><i class="bi bi-arrow-counterclockwise me-2"></i> Refund Barang</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="cart_item_id" id="refundCartItemId">
          <input type="hidden" name="item_id" id="refundItemId">
          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Barang</label>
            <input type="text" class="form-control rounded-3" id="refundItemName" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Jumlah Refund <span class="text-danger">*</span></label>
            <input type="number" name="qty" id="refundQty" class="form-control rounded-3" min="1" required
                   oninput="validateRefundQty(this)">
            <div class="form-text">
              <span class="text-muted">Maksimal refund: </span>
              <span id="maxQty" class="fw-bold text-warning">0</span>
              <span id="qtyError" class="text-danger small d-none">❌ Jumlah refund melebihi batas maksimal</span>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Kode Barang (Scan) <span class="text-danger">*</span></label>
            <input type="text" name="code" class="form-control rounded-3" placeholder="Scan barcode barang" required>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="submit" id="submitRefundPegawai" class="btn text-white rounded-pill fw-semibold px-3"
                  style="background-color:#FF9800;">
            ✅ Proses Refund
          </button>
          <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- MODAL REFUND GUEST --}}
<div class="modal fade" id="refundModalGuest" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <form id="refundFormGuest" action="{{ route('admin.guest.refund') }}" method="POST">
        @csrf
        <div class="modal-header text-white" style="background:linear-gradient(90deg, #FF9800, #FFB74D);">
          <h5 class="modal-title fw-bold"><i class="bi bi-arrow-counterclockwise me-2"></i> Refund Barang Tamu</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="guest_cart_item_id" id="refundGuestCartItemId">
          <input type="hidden" name="item_id" id="refundGuestItemId">
          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Barang</label>
            <input type="text" class="form-control rounded-3" id="refundGuestItemName" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Jumlah Refund <span class="text-danger">*</span></label>
            <input type="number" name="qty" id="refundGuestQty" class="form-control rounded-3" min="1" required
                   oninput="validateRefundGuestQty(this)">
            <div class="form-text">
              <span class="text-muted">Maksimal refund: </span>
              <span id="maxGuestQty" class="fw-bold text-warning">0</span>
              <span id="guestQtyError" class="text-danger small d-none">❌ Jumlah refund melebihi batas maksimal</span>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Kode Barang (Scan) <span class="text-danger">*</span></label>
            <input type="text" name="code" class="form-control rounded-3" placeholder="Scan barcode barang" required>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="submit" id="submitRefundGuest" class="btn text-white rounded-pill fw-semibold px-3"
                  style="background-color:#FF9800;">
            ✅ Proses Refund
          </button>
          <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- MODAL EDIT PEGAWAI --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <form action="{{ route('admin.pegawai.updateItem') }}" method="POST">
        @csrf
        <div class="modal-header text-white" style="background:linear-gradient(90deg, #FF9800, #FFB74D);">
          <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i> Edit Barang</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="cart_item_id" id="editCartItemId">
         <div class="mb-3">
              <label class="form-label fw-semibold">Pilih Barang</label>
              <select name="item_id" class="form-select select2-item-search rounded-3" id="editItemId" required>
                  <option value="">-- Pilih Barang --</option>
                  @foreach($items as $item)
                      <option value="{{ $item->id }}" data-code="{{ $item->code }}">{{ $item->name }} ({{ $item->code }})</option>
                  @endforeach
              </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Jumlah</label>
            <input type="number" name="qty" class="form-control rounded-3" id="editQty" min="1" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Kode Barang (Scan)</label>
            <input type="text" name="code" class="form-control rounded-3" placeholder="Scan barcode barang" required>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="submit" class="btn text-white rounded-pill fw-semibold px-3" style="background-color:#FF9800;">
            💾 Simpan Perubahan
          </button>
          <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- MODAL EDIT GUEST --}}
<div class="modal fade" id="editModalGuest" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <form action="{{ route('admin.guest.updateItem') }}" method="POST">
        @csrf
        <div class="modal-header text-white" style="background:linear-gradient(90deg, #FF9800, #FFB74D);">
          <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i> Edit Barang Tamu</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="guest_cart_item_id" id="editGuestCartItemId">
          <div class="mb-3">
              <label class="form-label fw-semibold">Pilih Barang</label>
              <select name="item_id" class="form-select select2-item-search rounded-3" id="editGuestItemId" required>
                  <option value="">-- Pilih Barang --</option>
                  @foreach($items as $item)
                      <option value="{{ $item->id }}" data-code="{{ $item->code }}">{{ $item->name }} ({{ $item->code }})</option>
                  @endforeach
              </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Jumlah</label>
            <input type="number" name="qty" class="form-control rounded-3" id="editGuestQty" min="1" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Kode Barang (Scan)</label>
            <input type="text" name="code" class="form-control rounded-3" placeholder="Scan barcode barang" required>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="submit" class="btn text-white rounded-pill fw-semibold px-3" style="background-color:#FF9800;">
            💾 Simpan Perubahan
          </button>
          <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
// Inisialisasi Select2
function initializeSelect2() {
    $('.select2-item-search').select2({
        placeholder: "Cari barang...",
        allowClear: true,
        width: '100%',
        dropdownParent: $('.modal-content'),
        language: {
            noResults: function() {
                return "Barang tidak ditemukan";
            },
            searching: function() {
                return "Mencari...";
            }
        }
    });
}

// Fungsi validasi quantity refund untuk pegawai
function validateRefundQty(input) {
    const maxQty = parseInt(input.max);
    const currentQty = parseInt(input.value);
    const qtyError = document.getElementById('qtyError');
    const submitBtn = document.getElementById('submitRefundPegawai');

    if (currentQty > maxQty) {
        qtyError.classList.remove('d-none');
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.6';
    } else {
        qtyError.classList.add('d-none');
        submitBtn.disabled = false;
        submitBtn.style.opacity = '1';
    }
}

// Fungsi validasi quantity refund untuk guest
function validateRefundGuestQty(input) {
    const maxQty = parseInt(input.max);
    const currentQty = parseInt(input.value);
    const qtyError = document.getElementById('guestQtyError');
    const submitBtn = document.getElementById('submitRefundGuest');

    if (currentQty > maxQty) {
        qtyError.classList.remove('d-none');
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.6';
    } else {
        qtyError.classList.add('d-none');
        submitBtn.disabled = false;
        submitBtn.style.opacity = '1';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi Select2 saat dokumen ready
    initializeSelect2();

    // SweetAlert untuk pesan sukses/gagal dari session
    @if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '{{ session('success') }}',
        confirmButtonText: 'Oke',
        confirmButtonColor: '#FF9800',
        background: '#fffaf4',
        iconColor: '#4CAF50'
    });
    @endif

    @if(session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: '{{ session('error') }}',
        confirmButtonText: 'Oke',
        confirmButtonColor: '#FF9800',
        background: '#fffaf4',
        iconColor: '#f44336'
    });
    @endif

    const modals = ['refundModal', 'refundModalGuest', 'editModal', 'editModalGuest'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.addEventListener('hidden.bs.modal', function() {
                const form = this.querySelector('form');
                if (form) {
                    form.reset();
                    // Reset Select2
                    $(this).find('.select2-item-search').val('').trigger('change');

                    // Reset validation states
                    const qtyError = document.getElementById('qtyError');
                    const guestQtyError = document.getElementById('guestQtyError');
                    if (qtyError) qtyError.classList.add('d-none');
                    if (guestQtyError) guestQtyError.classList.add('d-none');

                    // Enable submit buttons
                    const submitBtns = form.querySelectorAll('button[type="submit"]');
                    submitBtns.forEach(btn => {
                        btn.disabled = false;
                        btn.style.opacity = '1';
                    });
                }
            });

            // Re-inisialisasi Select2 ketika modal dibuka
            modal.addEventListener('show.bs.modal', function() {
                setTimeout(() => {
                    $(this).find('.select2-item-search').select2({
                        placeholder: "Cari barang...",
                        allowClear: true,
                        width: '100%',
                        dropdownParent: $(this).find('.modal-content'),
                        language: {
                            noResults: function() {
                                return "Barang tidak ditemukan";
                            },
                            searching: function() {
                                return "Mencari...";
                            }
                        }
                    });
                }, 100);
            });
        }
    });

    // Refund Modal Pegawai
    const refundModal = document.getElementById('refundModal');
    if (refundModal) {
        refundModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const cartItemId = button.getAttribute('data-cart-item-id');
            const itemId = button.getAttribute('data-item-id');
            const itemName = button.getAttribute('data-item-name');
            const maxQty = button.getAttribute('data-max-qty');

            document.getElementById('refundCartItemId').value = cartItemId;
            document.getElementById('refundItemId').value = itemId;
            document.getElementById('refundItemName').value = itemName;
            document.getElementById('refundQty').max = maxQty;
            document.getElementById('refundQty').value = 1;
            document.getElementById('maxQty').textContent = maxQty;

            // Reset validation state
            document.getElementById('qtyError').classList.add('d-none');
            document.getElementById('submitRefundPegawai').disabled = false;
            document.getElementById('submitRefundPegawai').style.opacity = '1';
        });
    }

    // Refund Modal Guest
    const refundModalGuest = document.getElementById('refundModalGuest');
    if (refundModalGuest) {
        refundModalGuest.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const cartItemId = button.getAttribute('data-cart-item-id');
            const itemId = button.getAttribute('data-item-id');
            const itemName = button.getAttribute('data-item-name');
            const maxQty = button.getAttribute('data-max-qty');

            document.getElementById('refundGuestCartItemId').value = cartItemId;
            document.getElementById('refundGuestItemId').value = itemId;
            document.getElementById('refundGuestItemName').value = itemName;
            document.getElementById('refundGuestQty').max = maxQty;
            document.getElementById('refundGuestQty').value = 1;
            document.getElementById('maxGuestQty').textContent = maxQty;

            // Reset validation state
            document.getElementById('guestQtyError').classList.add('d-none');
            document.getElementById('submitRefundGuest').disabled = false;
            document.getElementById('submitRefundGuest').style.opacity = '1';
        });
    }

    // Edit Modal Pegawai
    const editModal = document.getElementById('editModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const cartItemId = button.getAttribute('data-cart-item-id');
            const itemId = button.getAttribute('data-item-id');
            const qty = button.getAttribute('data-qty');

            document.getElementById('editCartItemId').value = cartItemId;
            document.getElementById('editQty').value = qty;

            // Set nilai Select2 setelah modal terbuka
            setTimeout(() => {
                $('#editItemId').val(itemId).trigger('change');
            }, 100);
        });
    }

    // Edit Modal Guest
    const editModalGuest = document.getElementById('editModalGuest');
    if (editModalGuest) {
        editModalGuest.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const guestCartItemId = button.getAttribute('data-guest-cart-item-id');
            const itemId = button.getAttribute('data-item-id');
            const qty = button.getAttribute('data-qty');

            document.getElementById('editGuestCartItemId').value = guestCartItemId;
            document.getElementById('editGuestQty').value = qty;

            // Set nilai Select2 setelah modal terbuka
            setTimeout(() => {
                $('#editGuestItemId').val(itemId).trigger('change');
            }, 100);
        });
    }

    // SweetAlert untuk konfirmasi refund dan edit
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const formType = this.getAttribute('action');

            if (formType && (formType.includes('refund') || formType.includes('update'))) {
                e.preventDefault();

                const actionType = formType.includes('refund') ? 'refund' : 'edit';
                const itemName = this.querySelector('input[readonly]')?.value ||
                               this.querySelector('.select2-item-search option:selected')?.text ||
                               'barang';

                Swal.fire({
                    title: Konfirmasi ${actionType === 'refund' ? 'Refund' : 'Edit'},
                    text: Apakah Anda yakin ingin ${actionType === 'refund' ? 'melakukan refund pada' : 'mengedit'} ${itemName}?,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Lanjutkan',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#FF9800',
                    cancelButtonColor: '#6c757d',
                    background: '#fffaf4'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Tampilkan loading
                        Swal.fire({
                            title: 'Memproses...',
                            text: 'Sedang memproses permintaan Anda',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Submit form setelah konfirmasi
                        this.submit();
                    }
                });
            }
        });
    });

    // SweetAlert untuk error validasi form
    document.querySelectorAll('input, select').forEach(input => {
        input.addEventListener('invalid', function(e) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Data Tidak Lengkap',
                text: 'Harap lengkapi semua field yang wajib diisi',
                confirmButtonText: 'Oke',
                confirmButtonColor: '#FF9800',
                background: '#fffaf4'
            });
        });
    });
});
</script>
@endpush

@push('styles')
<style>
body { background-color: #fffaf4 !important; }

.smooth-fade { animation: fadeDown .7s ease-in-out; }
@keyframes fadeDown { from {opacity:0;transform:translateY(-10px);} to {opacity:1;transform:translateY(0);} }

.smooth-card { transition: all 0.3s ease; }
.smooth-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(255,152,0,0.25); }

.smooth-btn { transition: all 0.25s ease-in-out; }
.smooth-btn:hover {
  transform: scale(1.05);
  box-shadow: 0 4px 10px rgba(255,152,0,0.3);
}

.table-hover tbody tr:hover {
  background-color: #FFF8E1 !important;
  transition: 0.3s ease;
}

.badge { font-size: 0.85rem; font-weight: 600; }

/* Tambahan untuk modal */
.modal-backdrop {
    background-color: rgba(0, 0, 0, 0.5);
}

/* Select2 Custom Styling */
.select2-container--default .select2-selection--single {
    border: 1px solid #ced4da;
    border-radius: 0.5rem;
    height: 45px;
    padding: 0.375rem 0.75rem;
    background-color: #fff;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 35px;
    color: #495057;
    padding-left: 0;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 43px;
}

.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #FF9800;
    color: white;
}

.select2-container--default .select2-search--dropdown .select2-search__field {
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    padding: 0.375rem 0.75rem;
}

.select2-container--default .select2-results__option[aria-selected=true] {
    background-color: #FFF3E0;
    color: #5d4037;
}

.select2-dropdown {
    border: 1px solid #ced4da;
    border-radius: 0.5rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.modal-content {
    border: none;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

@media (max-width: 768px) {
  .table { font-size: 0.9rem; }
  .btn { font-size: 0.8rem; }
  .modal-dialog {
      margin: 1rem;
  }
}
</style>
@endpush
