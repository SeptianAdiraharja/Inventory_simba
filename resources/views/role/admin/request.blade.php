@extends('layouts.index')

@section('content')
<div class="container-fluid py-4 animate__animated animate__fadeIn">

  {{-- ===================== --}}
  {{-- 📦 DAFTAR PERMINTAAN --}}
  {{-- ===================== --}}
  <div class="card shadow-sm border-0 rounded-3">
    <div class="card-header bg-primary border-0 d-flex justify-content-between align-items-center px-4 py-3">
      <h5 class="m-0 text-primary fw-semibold text-white">
        <i class="bi bi-list-check me-2"></i> Daftar Permintaan Barang
      </h5>
      <button class="btn btn-sm btn-outline-light rounded-pill px-3 text-white" onclick="location.reload()">
        <i class="bi bi-arrow-clockwise me-1"></i> Muat Ulang
      </button>
    </div>

    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead class="bg-light text-center align-middle border-bottom">
          <tr class="text-secondary small">
            <th style="width: 50px;">No</th>
            <th>Nama</th>
            <th>Email</th>
            <th>Status</th>
            <th>Jumlah Barang</th>
            <th style="width: 150px;">Aksi</th>
          </tr>
        </thead>

        <tbody>
          @forelse($requests as $index => $req)
          {{-- === ROW UTAMA === --}}
          <tr id="cart-row-{{ $req->cart_id }}">
            <td class="text-center text-muted">{{ $requests->firstItem() + $index }}</td>

            <td>
              <strong class="text-dark">{{ $req->name }}</strong><br>
              <small class="text-muted">
                Diajukan: {{ \Carbon\Carbon::parse($req->created_at)->format('d M Y H:i') }}
              </small>
            </td>

            <td class="text-muted">{{ $req->email }}</td>

            <td class="text-center">
              <span id="main-status-{{ $req->cart_id }}"
                class="badge rounded-pill px-3 py-2
                  @if($req->status == 'pending') bg-warning text-dark
                  @elseif($req->status == 'rejected') bg-danger
                  @elseif($req->status == 'approved') bg-success
                  @elseif($req->status == 'approved_partially') bg-warning text-dark
                  @endif">
                {{ ucfirst(str_replace('_', ' ', $req->status)) }}
              </span>
            </td>

            <td class="text-center fw-semibold text-dark">
              {{ $req->total_quantity }}
            </td>

            <td class="text-center">
              <div class="btn-group">
                <button class="btn btn-sm btn-outline-primary rounded-pill dropdown-toggle"
                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bi bi-gear me-1"></i> Opsi
                </button>

                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                  <li>
                    <a class="dropdown-item detail-toggle-btn" href="#"
                       data-cart-id="{{ $req->cart_id }}">
                      <i class="bi bi-eye me-2 text-primary"></i> Lihat Semua Barang
                    </a>
                  </li>

                  @php
                    $isDisabled = in_array($req->status, ['approved', 'approved_partially', 'rejected']);
                  @endphp

                  <li>
                    <a class="dropdown-item approve-all-btn text-success {{ $isDisabled ? 'disabled opacity-50' : '' }}"
                       href="#"
                       data-cart-id="{{ $req->cart_id }}"
                       @if($isDisabled) tabindex="-1" aria-disabled="true" @endif>
                      <i class="bi bi-check-circle me-2"></i> Setujui Semua
                    </a>
                  </li>

                  <li>
                    <a class="dropdown-item reject-all-btn text-danger {{ $isDisabled ? 'disabled opacity-50' : '' }}"
                       href="#"
                       data-cart-id="{{ $req->cart_id }}"
                       @if($isDisabled) tabindex="-1" aria-disabled="true" @endif>
                      <i class="bi bi-x-octagon me-2"></i> Tolak Semua
                    </a>
                  </li>
                </ul>
              </div>
            </td>
          </tr>

          {{-- === ROW DETAIL (AJAX CONTAINER) === --}}
          <tr class="collapse-row">
            <td colspan="7" class="p-0">
              <div id="detail-content-{{ $req->cart_id }}"
                   class="detail-content-wrapper collapse bg-light border-top"
                   data-cart-id="{{ $req->cart_id }}" data-loaded="false">
                <p class="text-center text-muted m-0 p-3">
                  Klik “Lihat Semua Barang” untuk membuka detail.
                </p>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7" class="text-center py-5">
              <div class="text-muted">
                <i class="bi bi-inbox display-6 d-block mb-2"></i>
                <p class="mb-1 fw-semibold">Belum ada permintaan dengan status ini.</p>
                <small>Coba ubah filter untuk melihat data lainnya.</small>
              </div>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- PAGINATION --}}
  <div class="mt-4 d-flex justify-content-center">
    {{ $requests->links('pagination::bootstrap-5') }}
  </div>
</div>


{{-- ===================== --}}
{{-- 🟥 MODAL: TOLAK BARANG --}}
{{-- ===================== --}}
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow rounded-3">
      <div class="modal-header bg-danger text-white rounded-top-3">
        <h5 class="modal-title fw-semibold">
          <i class="bi bi-x-circle me-2"></i> Alasan Penolakan Barang
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <form id="rejectItemForm" method="POST"
            data-is-bulk="false"
            data-cart-id=""
            data-item-id="">
        @csrf

        <div class="modal-body">
          <label class="form-label fw-semibold text-secondary">Tuliskan alasan penolakan:</label>
          <textarea name="reason" class="form-control rounded-3" rows="3"
                    placeholder="Contoh: Barang tidak tersedia, data tidak valid..." required></textarea>
        </div>

        <div class="modal-footer bg-light border-top-0 rounded-bottom-3">
          <button type="button" class="btn btn-secondary rounded-pill px-3 mt-5" data-bs-dismiss="modal">
            Batal
          </button>
          <button type="submit" class="btn btn-danger rounded-pill px-3 mt-5">
            Tolak Barang
          </button>
        </div>
      </form>
    </div>
  </div>
</div>


{{-- ===================== --}}
{{-- 📜 SCRIPT --}}
{{-- ===================== --}}
@push('scripts')
<script src="{{ asset('js/admin-request.js') }}"></script>
@endpush

{{-- TOAST & SNACKBAR --}}
<div id="toast-container" class="position-fixed top-0 end-0 p-3" style="z-index:1080;"></div>
<div id="snackbar"></div>

<style>
/* Snackbar */
#snackbar {
  visibility: hidden;
  min-width: 280px;
  background-color: #323232;
  color: #fff;
  text-align: center;
  border-radius: 6px;
  padding: 12px 18px;
  position: fixed;
  z-index: 1080;
  left: 50%;
  bottom: 30px;
  transform: translateX(-50%);
  font-size: 15px;
  opacity: 0;
  transition: opacity 0.3s, bottom 0.3s;
}
#snackbar.show {
  visibility: visible;
  opacity: 1;
  bottom: 50px;
}

/* Table enhancements */
.table th {
  font-weight: 600;
  color: #5f6368;
}
.table td {
  vertical-align: middle;
  font-size: 15px;
}
.table-hover tbody tr:hover {
  background-color: #f8fafc !important;
}

/* Dropdown */
.dropdown-menu a {
  font-size: 14px;
  padding: 8px 14px;
}
.dropdown-menu a:hover {
  background-color: #eef5ff;
  color: #0d6efd;
}

/* Animasi hover ringan */
.btn-outline-primary:hover {
  background-color: #0d6efd;
  color: #fff;
  transition: 0.3s;
}
</style>
@endsection
