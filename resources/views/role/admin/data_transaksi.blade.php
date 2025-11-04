@extends('layouts.index')

@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- 🧭 MODERN BREADCRUMB --}}
  <div class="bg-white shadow-sm rounded-4 px-4 py-3 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3 animate__animated animate__fadeInDown smooth-fade">
    <div class="d-flex align-items-center flex-wrap gap-2">
      <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width:38px;height:38px;">
        <i class="bi bi-house-door-fill fs-5"></i>
      </div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}" class="fw-semibold text-primary text-decoration-none">Dashboard</a>
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
  <div class="card shadow-lg border-0 rounded-4">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3 px-4">
      <h4 class="mb-0 fw-bold"><i class="bi bi-box-seam me-2"></i>Data Transaksi Barang Keluar</h4>
    </div>

    <div class="card-body p-0">
      {{-- TAB --}}
      <ul class="nav nav-tabs fs-5 fw-semibold px-3" id="transaksiTab" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active py-2 px-4" id="pegawai-tab" data-bs-toggle="tab" data-bs-target="#pegawai" type="button" role="tab" aria-controls="pegawai" aria-selected="true">
            <i class="bi bi-person-badge me-1"></i> Pegawai
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link py-2 px-4" id="guest-tab" data-bs-toggle="tab" data-bs-target="#guest" type="button" role="tab" aria-controls="guest" aria-selected="false">
            <i class="bi bi-people me-1"></i> Tamu
          </button>
        </li>
      </ul>

      {{-- TAB CONTENT --}}
      <div class="tab-content p-4" id="transaksiTabContent">

        {{-- ========================= --}}
        {{-- 🔸 TAB PEGAWAI --}}
        {{-- ========================= --}}
        <div class="tab-pane fade show active" id="pegawai" role="tabpanel" aria-labelledby="pegawai-tab">
          <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle text-center">
              <thead class="table-primary">
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
                  <td class="text-start">{{ $cart->user->name ?? '-' }}</td>
                  <td>{{ $cart->cartItems->count() }}</td>
                  <td>{{ $cart->created_at->format('d M Y H:i') }}</td>
                  <td><span class="badge bg-success px-3 py-2">{{ ucfirst($cart->status) }}</span></td>
                  <td>
                    <button class="btn btn-sm btn-primary rounded-3 px-3" data-bs-toggle="collapse" data-bs-target="#pegawai{{ $cart->id }}">
                      <i class="bi bi-eye"></i> Detail
                    </button>
                  </td>
                </tr>

                {{-- DETAIL --}}
                <tr class="collapse bg-light" id="pegawai{{ $cart->id }}">
                  <td colspan="6">
                    <table class="table table-sm table-bordered mb-0 text-center">
                      <thead class="table-secondary">
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
                            {{-- Refund --}}
                            <button class="btn btn-sm btn-warning me-1" data-bs-toggle="modal" data-bs-target="#refundModal"
                              data-cart-item-id="{{ $item->id }}" data-item-name="{{ $item->item->name }}"
                              data-item-id="{{ $item->item->id }}" data-max-qty="{{ $item->quantity }}">
                              <i class="bi bi-arrow-counterclockwise"></i> Refund
                            </button>

                            {{-- Reject --}}
                            <button class="btn btn-sm btn-danger me-1 btn-reject" data-item-code="{{ $item->item->code }}">
                              <i class="bi bi-x-circle"></i> Reject
                            </button>

                            {{-- Edit --}}
                            <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#editModal"
                              data-cart-item-id="{{ $item->id }}" data-item-id="{{ $item->item->id }}" data-qty="{{ $item->quantity }}">
                              <i class="bi bi-pencil-square"></i> Edit
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

        {{-- ========================= --}}
        {{-- 🔸 TAB TAMU --}}
        {{-- ========================= --}}
        <div class="tab-pane fade" id="guest" role="tabpanel" aria-labelledby="guest-tab">
          <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle text-center">
              <thead class="table-primary">
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
                  <td>{{ $guest->name ?? 'Guest' }}</td>
                  <td>{{ $guest->guestCart->guestCartItems->count() ?? 0 }}</td>
                  <td>{{ $guest->created_at->format('d M Y H:i') }}</td>
                  <td>
                    <button class="btn btn-sm btn-primary rounded-3 px-3" data-bs-toggle="collapse" data-bs-target="#guest{{ $guest->id }}">
                      <i class="bi bi-eye"></i> Detail
                    </button>
                  </td>
                </tr>

                {{-- DETAIL TAMU --}}
                <tr class="collapse bg-light" id="guest{{ $guest->id }}">
                  <td colspan="5">
                    <table class="table table-sm table-bordered mb-0 text-center">
                      <thead class="table-secondary">
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
                            <button class="btn btn-sm btn-warning me-1" data-bs-toggle="modal"
                              data-bs-target="#refundModalGuest" data-cart-item-id="{{ $item->id }}"
                              data-item-name="{{ $item->item->name }}" data-item-id="{{ $item->item->id }}"
                              data-max-qty="{{ $item->quantity }}">
                              <i class="bi bi-arrow-counterclockwise"></i> Refund
                            </button>
                            <button class="btn btn-sm btn-danger me-1 btn-reject" data-item-code="{{ $item->item->code }}">
                              <i class="bi bi-x-circle"></i> Reject
                            </button>
                            <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal"
                              data-bs-target="#editModalGuest" data-guest-cart-item-id="{{ $item->id }}"
                              data-item-id="{{ $item->item->id }}" data-qty="{{ $item->quantity }}">
                              <i class="bi bi-pencil-square"></i> Edit
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
{{-- MODAL REFUND --}}
{{-- ===================== --}}
<div class="modal fade" id="refundModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <form action="{{ route('admin.pegawai.refund') }}" method="POST">
        @csrf
        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title fw-bold"><i class="bi bi-arrow-counterclockwise me-2"></i>Refund Barang</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="cart_item_id" id="refundCartItemId">
          <input type="hidden" name="item_id" id="refundItemId">
          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Barang</label>
            <input type="text" class="form-control" id="refundItemName" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Jumlah Refund</label>
            <input type="number" name="qty" id="refundQty" class="form-control" min="1" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-warning fw-bold"><i class="bi bi-check-circle me-1"></i> Proses</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i> Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

  // 🔹 Modal Refund Pegawai
  const refundModal = document.getElementById('refundModal');
  if (refundModal) {
    refundModal.addEventListener('show.bs.modal', function (event) {
      const btn = event.relatedTarget;
      document.getElementById('refundCartItemId').value = btn.getAttribute('data-cart-item-id');
      document.getElementById('refundItemId').value = btn.getAttribute('data-item-id');
      document.getElementById('refundItemName').value = btn.getAttribute('data-item-name');
      document.getElementById('refundQty').setAttribute('max', btn.getAttribute('data-max-qty'));
    });
  }

  // 🔹 Edit Modal Pegawai
  const editModal = document.getElementById('editModal');
  if (editModal) {
    editModal.addEventListener('show.bs.modal', function (event) {
      const btn = event.relatedTarget;
      editModal.querySelector('#editItemId').value = btn.getAttribute('data-cart-item-id');
      editModal.querySelector('#editItemSelect').value = btn.getAttribute('data-item-id');
      editModal.querySelector('#editQty').value = btn.getAttribute('data-qty');
      editModal.querySelector('#editcode').value = '';
    });
  }

  // 🔹 Tombol Reject (konfirmasi)
  document.querySelectorAll('.btn-reject').forEach(btn => {
    btn.addEventListener('click', e => {
      const code = btn.dataset.itemCode;
      Swal.fire({
        title: "Tolak Barang?",
        text: `Yakin ingin menandai barang ${code} sebagai reject?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Ya, tolak!",
        cancelButtonText: "Batal"
      }).then(result => {
        if (result.isConfirmed) {
          window.location.href = "{{ route('admin.rejects.scan') }}";
        }
      });
    });
  });

});
</script>
@endpush
