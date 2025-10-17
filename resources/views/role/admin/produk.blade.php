  @extends('layouts.index')
  @section('content')

  <!-- === ICON CART === -->
  <div class="d-flex justify-content-end mb-3">
    <button
      class="btn btn-outline-primary position-relative"
      id="openCartModal"
      data-guest-id="{{ $guest->id ?? '' }}">
      <i class="ri-shopping-cart-2-line fs-3"></i>
      @if(isset($cartItems) && $cartItems->count() > 0)
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
          {{ $cartItems->count() }}
        </span>
      @endif
    </button>
  </div>

  <!-- === DAFTAR PRODUK === -->
  <div class="row gy-4">
    @foreach ($items as $item)
    <div class="col-xl-3 col-lg-4 col-md-6">
      <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden">
        <img
          src="{{ asset('storage/' . $item->image) }}"
          class="card-img-top"
          alt="{{ $item->name }}"
          style="height: 200px; object-fit: cover;"
        >
        <div class="card-body d-flex flex-column justify-content-between">
          <div>
            <h5 class="card-title fw-semibold mb-1">{{ $item->name }}</h5>
            <p class="text-muted small mb-2">
              <i class="ri-folder-line me-1"></i>Kategori:
              <span class="fw-semibold">{{ $item->category->name ?? '-' }}</span>
            </p>
            <p class="text-muted small mb-1">
              <i class="ri-barcode-box-line me-1"></i>Stok:
              <span class="{{ $item->stock > 0 ? 'text-success' : 'text-danger' }}">
                {{ $item->stock }}
              </span>
            </p>
          </div>

          <button
            type="button"
            class="btn btn-sm btn-primary mt-2 w-100"
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
        <div class="modal-content border-0 shadow">
          <form id="form-{{ $item->id }}" action="{{ route('admin.produk.scan', $guest->id ?? 0) }}" method="POST">
            @csrf
            <div class="modal-header bg-primary text-white">
              <h5 class="modal-title">
                <i class="ri-scan-line me-2"></i>Scan Barang: {{ $item->name }}
              </h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
              <input type="hidden" name="guest_id" value="{{ $guest->id ?? '' }}">
              <input type="hidden" name="item_id" value="{{ $item->id }}">

              <!-- 🔢 Input jumlah (stok yang dikeluarkan) -->
              <div class="mb-3">
                <label class="form-label fw-semibold">Jumlah Barang</label>
                <input
                  type="number"
                  name="quantity"
                  class="form-control"
                  min="1"
                  max="{{ $item->stock }}"
                  value="1"
                  required>
                <small class="text-muted d-block mt-1">
                  Maksimum stok: {{ $item->stock }}
                </small>
              </div>

              <!-- 📷 Input barcode -->
              <div class="mb-3">
                <label class="form-label fw-semibold">
                  Masukkan / Scan Barcode
                </label>
                <input
                  id="barcode-{{ $item->id }}"
                  type="text"
                  name="barcode"
                  class="form-control"
                  placeholder="Arahkan scanner ke sini..."
                  required>
                <small class="text-muted d-block mt-1">
                  Tekan Enter setelah scan untuk menyimpan Data.
                </small>
              </div>
            </div>

            <div class="modal-footer">
              <button type="submit" class="btn btn-success">
                <i class="ri-check-line me-1"></i> Simpan
              </button>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
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
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-light">
          <h5 class="modal-title fw-semibold">
            <i class="ri-shopping-cart-line me-2"></i>Keranjang Guest
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>Nama Barang</th>
                <th>Kode</th>
                <th class="text-center">Jumlah</th>
              </tr>
            </thead>
            <tbody id="cartTableBody">
              <tr>
                <td colspan="3" class="text-center text-muted py-3">
                  <i class="ri-information-line me-1"></i>Keranjang kosong
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="modal-footer">
          <form id="releaseForm" method="POST">
            @csrf
            <button type="submit" class="btn btn-success">
              <i class="ri-send-plane-line me-1"></i> Keluarkan Semua
            </button>
          </form>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="ri-close-line me-1"></i> Tutup
          </button>
        </div>
      </div>
    </div>
  </div>

  @endsection

  @push('scripts')
  <script>
  document.addEventListener("DOMContentLoaded", function() {
    // === Fokus input barcode otomatis ===
    @foreach ($items as $item)
    const input{{ $item->id }} = document.getElementById("barcode-{{ $item->id }}");
    $('#scanModal-{{ $item->id }}').on('shown.bs.modal', function () {
      input{{ $item->id }}.focus();
    });
    input{{ $item->id }}.addEventListener("keypress", function(e) {
      if (e.key === "Enter") {
        e.preventDefault();
        document.getElementById("form-{{ $item->id }}").submit();
      }
    });
    @endforeach

    // === Modal Cart ===
    const openCartBtn = document.getElementById("openCartModal");
    const cartTableBody = document.getElementById("cartTableBody");
    const releaseForm = document.getElementById("releaseForm");
    const cartModalEl = document.getElementById("cartModal");
    const cartModal = new bootstrap.Modal(cartModalEl);

    if(openCartBtn){
      openCartBtn.addEventListener("click", function(){
        const guestId = this.dataset.guestId;
        if(!guestId) return;

        fetch(`/admin/produk/guest/${guestId}/cart`)
          .then(res => res.json())
          .then(data => {
            cartTableBody.innerHTML = "";
            if(data.cartItems.length > 0){
              data.cartItems.forEach(item => {
                cartTableBody.innerHTML += `
                  <tr>
                    <td>${item.name}</td>
                    <td>${item.code ?? '-'}</td>
                    <td class="text-center">${item.quantity}</td>
                  </tr>
                `;
              });
            } else {
              cartTableBody.innerHTML = `
                <tr><td colspan="3" class="text-center text-muted py-3">
                  <i class='ri-information-line me-1'></i>Keranjang kosong
                </td></tr>`;
            }
            releaseForm.action = `/admin/produk/guest/${guestId}/release`;
            cartModal.show();
          });
      });
    }

    // === Keluarkan Semua ===
    releaseForm.addEventListener("submit", function(e){
      e.preventDefault();
      const url = this.action;

      fetch(url, {
        method: "POST",
        headers: {
          "X-CSRF-TOKEN": this.querySelector('input[name="_token"]').value
        }
      })
      .then(res => {
        if (!res.ok) throw new Error("Gagal memproses permintaan");
        return res.text();
      })
      .then(() => {
        cartTableBody.innerHTML = `
          <tr><td colspan="3" class="text-center text-muted py-3">
            <i class='ri-check-line text-success me-1'></i> Semua barang berhasil dikeluarkan.
          </td></tr>`;
        const badge = openCartBtn.querySelector(".badge");
        if (badge) badge.remove();
        setTimeout(() => cartModal.hide(), 1200);
      })
      .catch(err => {
        alert("Terjadi kesalahan: " + err.message);
      });
    });
  });
  </script>
  @endpush
