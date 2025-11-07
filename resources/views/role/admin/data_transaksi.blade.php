@extends('layouts.index')

@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

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
                  style="color:#FF9800;">🧍‍♂️ Tamu</button>
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
                            <button class="btn btn-sm btn-outline-danger rounded-pill me-1 btn-reject fw-semibold smooth-btn"
                                    data-item-code="{{ $item->item->code }}">
                              ❌ Reject
                            </button>
                            <button class="btn btn-sm btn-outline-info rounded-pill fw-semibold smooth-btn"
                                    data-bs-toggle="modal" data-bs-target="#editModal"
                                    data-cart-item-id="{{ $item->id }}" data-item-id="{{ $item->item->id }}"
                                    data-qty="{{ $item->quantity }}">
                              ✏️ Edit
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
                            <button class="btn btn-sm btn-outline-danger rounded-pill me-1 btn-reject fw-semibold smooth-btn"
                                    data-item-code="{{ $item->item->code }}">
                              ❌ Reject
                            </button>
                            <button class="btn btn-sm btn-outline-info rounded-pill fw-semibold smooth-btn"
                                    data-bs-toggle="modal" data-bs-target="#editModalGuest"
                                    data-guest-cart-item-id="{{ $item->id }}" data-item-id="{{ $item->item->id }}"
                                    data-qty="{{ $item->quantity }}">
                              ✏️ Edit
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

{{-- MODAL REFUND --}}
<div class="modal fade" id="refundModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <form action="{{ route('admin.pegawai.refund') }}" method="POST">
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
            <label class="form-label fw-semibold">Jumlah Refund</label>
            <input type="number" name="qty" id="refundQty" class="form-control rounded-3" min="1" required>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="submit" class="btn text-white rounded-pill fw-semibold px-3" style="background-color:#FF9800;">
            ✅ Proses
          </button>
          <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

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

.btn[disabled] { opacity: 0.6 !important; cursor: not-allowed !important; }

@media (max-width: 768px) {
  .table { font-size: 0.9rem; }
  .btn { font-size: 0.8rem; }
}
</style>
@endpush
