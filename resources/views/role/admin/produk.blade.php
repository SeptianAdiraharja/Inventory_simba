@extends('layouts.index')
@section('content')

<style>
  /* === Gaya Umum yang Lebih Lembut dan Ramah Mata === */
  body {
    background-color: #f9fafb;
  }

  .card {
    border-radius: 1.25rem;
    transition: all 0.25s ease-in-out;
  }
  .card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
  }

  .card-body h5 {
    font-size: 1.05rem;
  }

  /* === Floating Cart yang Bisa Digeser === */
  #openCartModal {
    cursor: grab;
    transition: all 0.2s ease;
  }
  #openCartModal:active {
    cursor: grabbing;
  }

  /* === Modal Style === */
  .modal-content {
    border-radius: 1.25rem;
  }
  .modal-header {
    border-bottom: 1px solid #eee;
  }
  .modal-footer {
    border-top: 1px solid #eee;
  }

  /* === Tombol === */
  .btn {
    font-size: 0.95rem;
    border-radius: 50px !important;
  }

  .btn-primary {
    background-color: #2563eb;
    border-color: #2563eb;
  }

  .btn-primary:hover {
    background-color: #1e3a8a;
    border-color: #1e3a8a;
  }

  /* === Badge Notif Cart === */
  #openCartModal .badge {
    box-shadow: 0 0 0 2px #fff;
  }
</style>

<!-- === FLOATING CART BUTTON === -->
<button
  class="btn btn-primary shadow-lg position-fixed rounded-circle d-flex align-items-center justify-content-center"
  id="openCartModal"
  data-guest-id="{{ $guest->id ?? '' }}"
  style="
    bottom: 25px;
    right: 25px;
    width: 70px;
    height: 70px;
    font-size: 1.5rem;
    z-index: 1050;
    overflow:visible;
  ">
  <i class="ri-shopping-cart-2-line"></i>
  @if(isset($cartItems) && $cartItems->filter(fn($i) => is_null($i->pivot->released_at))->count() > 0)
    <span
      class="position-absolute badge rounded-pill bg-danger"
      style="top: -5px; right: -5px; font-size: 0.8rem; padding: 6px 8px;">
      {{ $cartItems->filter(fn($i) => is_null($i->pivot->released_at))->count() }}
    </span>
  @endif
</button>


<!-- === DAFTAR PRODUK === -->
<div class="row gy-4 mt-3">
  @foreach ($items as $item)
  <div class="col-xl-3 col-lg-4 col-md-6">
    <div class="card border-0 shadow-sm">
      <img
        src="{{ asset('storage/' . $item->image) }}"
        class="card-img-top"
        alt="{{ $item->name }}"
        style="height: 220px; object-fit: cover;">
      <div class="card-body d-flex flex-column justify-content-between">
        <div>
          <h5 class="fw-semibold mb-2">{{ $item->name }}</h5>
          <p class="text-muted small mb-1">
            <i class="ri-folder-line me-1"></i> Kategori:
            <span class="fw-semibold">{{ $item->category->name ?? '-' }}</span>
          </p>
          <p class="text-muted small">
            <i class="ri-barcode-box-line me-1"></i> Stok:
            <span class="{{ $item->stock > 0 ? 'text-success' : 'text-danger' }}">
              {{ $item->stock }}
            </span>
          </p>
        </div>

        <button
          type="button"
          class="btn btn-sm btn-primary mt-3 w-100"
          data-bs-toggle="modal"
          data-bs-target="#scanModal-{{ $item->id }}"
          {{ $item->stock == 0 ? 'disabled' : '' }}>
          <i class="ri-scan-line me-1"></i> Keluarkan Barang
        </button>
      </div>
    </div>
  </div>

  <!-- === MODAL SCAN ITEM === -->
  <div class="modal fade" id="scanModal-{{ $item->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form id="form-{{ $item->id }}" action="{{ route('admin.produk.scan', $guest->id ?? 0) }}" method="POST">
          @csrf
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title"><i class="ri-scan-line me-2"></i>Scan Barang: {{ $item->name }}</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="guest_id" value="{{ $guest->id ?? '' }}">
            <input type="hidden" name="item_id" value="{{ $item->id }}">

            <div class="mb-3">
              <label class="form-label fw-semibold">Jumlah Barang</label>
              <input type="number" name="quantity" class="form-control form-control-lg" min="1" max="{{ $item->stock }}" value="1" required>
              <small class="text-muted">Maksimum stok: {{ $item->stock }}</small>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Masukkan / Scan Barcode</label>
              <input id="barcode-{{ $item->id }}" type="text" name="barcode" class="form-control form-control-lg" placeholder="Arahkan scanner ke sini..." required>
              <small class="text-muted">Tekan Enter setelah scan untuk menyimpan data.</small>
            </div>
          </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-success">
              <i class="ri-check-line me-1"></i> Simpan
            </button>
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
              <i class="ri-close-line me-1"></i> Batal
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  @endforeach
</div>

<!-- === MODAL CART === -->
<div class="modal fade" id="cartModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-light">
        <h5 class="modal-title fw-semibold"><i class="ri-shopping-cart-line me-2"></i>Keranjang Guest</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>Nama Barang</th>
              <th>Kode</th>
              <th class="text-center">Jumlah</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody id="cartTableBody">
            <tr>
              <td colspan="4" class="text-center text-muted py-3">
                <i class="ri-information-line me-1"></i>Keranjang kosong
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <form id="releaseForm" method="POST">@csrf
          <button type="submit" class="btn btn-success"><i class="ri-send-plane-line me-1"></i> Keluarkan Semua</button>
        </form>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          <i class="ri-close-line me-1"></i> Tutup
        </button>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/guest-cart.js') }}"></script>
@endpush
